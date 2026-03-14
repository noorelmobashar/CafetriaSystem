<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from CLI.\n");
    exit(1);
}

$seedDataPath = root_path('database/seeds/data.php');
if (!is_file($seedDataPath)) {
    fwrite(STDERR, "Seed data file not found at database/seeds/data.php\n");
    exit(1);
}

$argv = $_SERVER['argv'] ?? [];
$force = in_array('--force', $argv, true);
$appEnv = strtolower((string) env('APP_ENV', 'production'));
$allowedEnvs = ['local', 'development', 'dev', 'testing', 'test'];

if (!$force && !in_array($appEnv, $allowedEnvs, true)) {
    fwrite(STDERR, "Refusing to run outside local/testing environments. Use --force to override.\n");
    exit(1);
}

/** @var array<string, mixed> $seed */
$seed = require $seedDataPath;
$db = db();

$stats = [
    'categories' => 0,
    'products' => 0,
    'users' => 0,
    'orders' => 0,
    'order_items' => 0,
];

try {
    $db->beginTransaction();

    $categoryIds = [];
    foreach (($seed['categories'] ?? []) as $category) {
        $categoryId = upsertCategory($db, (string) ($category['name'] ?? ''));
        if ($categoryId > 0) {
            $categoryIds[(string) $category['name']] = $categoryId;
            $stats['categories']++;
        }
    }

    $productIds = [];
    foreach (($seed['products'] ?? []) as $product) {
        $categoryName = (string) ($product['category'] ?? '');
        $categoryId = $categoryIds[$categoryName] ?? null;

        $productId = upsertProduct(
            $db,
            (string) ($product['name'] ?? ''),
            (float) ($product['price'] ?? 0),
            $categoryId,
            isset($product['image_path']) ? (string) $product['image_path'] : null,
            (bool) ($product['available'] ?? true)
        );

        if ($productId > 0) {
            $productIds[(string) $product['name']] = $productId;
            $stats['products']++;
        }
    }

    $userIds = [];
    foreach (($seed['users'] ?? []) as $user) {
        $userId = upsertUser(
            $db,
            (string) ($user['name'] ?? ''),
            (string) ($user['email'] ?? ''),
            (string) ($user['password_hash'] ?? ''),
            (string) ($user['role'] ?? 'customer'),
            isset($user['profile_pic']) ? (string) $user['profile_pic'] : null
        );

        if ($userId > 0) {
            $userIds[(string) $user['email']] = $userId;
            $stats['users']++;
        }
    }

    foreach (($seed['orders'] ?? []) as $order) {
        $userEmail = (string) ($order['user_email'] ?? '');
        $userId = $userIds[$userEmail] ?? null;

        if ($userId === null) {
            continue;
        }

        $orderId = findSeedOrder(
            $db,
            $userId,
            (string) ($order['notes'] ?? '')
        );

        if ($orderId === null) {
            $validatedItems = [];
            $totalAmount = 0.0;

            foreach (($order['items'] ?? []) as $item) {
                $productName = (string) ($item['product'] ?? '');
                $quantity = max(1, (int) ($item['quantity'] ?? 1));
                $productId = $productIds[$productName] ?? null;

                if ($productId === null) {
                    continue;
                }

                $price = fetchProductPrice($db, $productId);
                $totalAmount += $price * $quantity;
                $validatedItems[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                ];
            }

            if ($validatedItems === []) {
                continue;
            }

            $orderId = insertOrder(
                $db,
                $userId,
                (string) ($order['status'] ?? 'incoming'),
                $totalAmount,
                (string) ($order['room_snapshot'] ?? '100'),
                (string) ($order['notes'] ?? 'SEED: order')
            );
            $stats['orders']++;

            foreach ($validatedItems as $item) {
                insertOrderItem($db, $orderId, (int) $item['product_id'], (int) $item['quantity'], (float) $item['unit_price']);
                $stats['order_items']++;
            }
        }
    }

    $db->commit();

    fwrite(STDOUT, "Database seeding completed.\n");
    fwrite(STDOUT, sprintf("Categories processed: %d\n", $stats['categories']));
    fwrite(STDOUT, sprintf("Products processed:   %d\n", $stats['products']));
    fwrite(STDOUT, sprintf("Users processed:      %d\n", $stats['users']));
    fwrite(STDOUT, sprintf("Orders inserted:      %d\n", $stats['orders']));
    fwrite(STDOUT, sprintf("Order items inserted: %d\n", $stats['order_items']));
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    fwrite(STDERR, "Seeding failed: " . $exception->getMessage() . "\n");
    exit(1);
}

function upsertCategory(PDO $db, string $name): int
{
    if ($name === '') {
        return 0;
    }

    $select = $db->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
    $select->execute([$name]);
    $existingId = $select->fetchColumn();

    if ($existingId !== false) {
        return (int) $existingId;
    }

    $insert = $db->prepare('INSERT INTO categories (name) VALUES (?)');
    $insert->execute([$name]);

    return (int) $db->lastInsertId();
}

function upsertProduct(PDO $db, string $name, float $price, ?int $categoryId, ?string $imagePath, bool $available): int
{
    if ($name === '') {
        return 0;
    }

    $select = $db->prepare('SELECT id FROM products WHERE name = ? LIMIT 1');
    $select->execute([$name]);
    $existingId = $select->fetchColumn();

    if ($existingId !== false) {
        $update = $db->prepare('UPDATE products SET price = ?, category_id = ?, image_path = ?, available = ?, updated_at = NOW() WHERE id = ?');
        $update->execute([$price, $categoryId, $imagePath, $available ? 1 : 0, (int) $existingId]);

        return (int) $existingId;
    }

    $insert = $db->prepare('INSERT INTO products (name, price, category_id, image_path, available, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
    $insert->execute([$name, $price, $categoryId, $imagePath, $available ? 1 : 0]);

    return (int) $db->lastInsertId();
}

function upsertUser(PDO $db, string $name, string $email, string $passwordInput, string $role, ?string $profilePic): int
{
    if ($name === '' || $email === '' || $passwordInput === '') {
        return 0;
    }

    $passwordHash = resolveSeedPasswordHash($passwordInput);

    $select = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $select->execute([$email]);
    $existingId = $select->fetchColumn();

    if ($existingId !== false) {
        $update = $db->prepare('UPDATE users SET name = ?, password_hash = ?, role = ?, profile_pic = ?, updated_at = NOW() WHERE id = ?');
        $update->execute([$name, $passwordHash, $role, $profilePic, (int) $existingId]);

        return (int) $existingId;
    }

    $insert = $db->prepare('INSERT INTO users (name, email, password_hash, role, profile_pic, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
    $insert->execute([$name, $email, $passwordHash, $role, $profilePic]);

    return (int) $db->lastInsertId();
}

function resolveSeedPasswordHash(string $passwordInput): string
{
    $passwordInfo = password_get_info($passwordInput);
    $isAlreadyHashed = ($passwordInfo['algo'] ?? null) !== null;

    if ($isAlreadyHashed) {
        return $passwordInput;
    }

    return password_hash($passwordInput, PASSWORD_DEFAULT);
}

function findSeedOrder(PDO $db, int $userId, string $notes): ?int
{
    $select = $db->prepare('SELECT id FROM orders WHERE user_id = ? AND notes = ? LIMIT 1');
    $select->execute([$userId, $notes]);
    $existingId = $select->fetchColumn();

    if ($existingId === false) {
        return null;
    }

    return (int) $existingId;
}

function fetchProductPrice(PDO $db, int $productId): float
{
    $select = $db->prepare('SELECT price FROM products WHERE id = ? LIMIT 1');
    $select->execute([$productId]);
    $price = $select->fetchColumn();

    if ($price === false) {
        throw new RuntimeException('Product price not found for product id ' . $productId);
    }

    return (float) $price;
}

function insertOrder(PDO $db, int $userId, string $status, float $totalAmount, string $roomSnapshot, string $notes): int
{
    $insert = $db->prepare(
        'INSERT INTO orders (user_id, status, total_amount, room_snapshot, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
    );
    $insert->execute([$userId, $status, $totalAmount, $roomSnapshot, $notes]);

    return (int) $db->lastInsertId();
}

function insertOrderItem(PDO $db, int $orderId, int $productId, int $quantity, float $unitPrice): void
{
    $insert = $db->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
    $insert->execute([$orderId, $productId, $quantity, $unitPrice]);
}
