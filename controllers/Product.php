<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../controllers/Utilities.php';

class ProductController
{
    public function search(string $query): array
    {
        $db = db();
        $like = '%' . $query . '%';
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.price, p.image_path, c.name AS category
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.available = 1
            AND (? = '' OR p.name LIKE ? OR c.name LIKE ?)
            ORDER BY c.name, p.name
        ");
        $stmt->execute([$query, $like, $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function index(): array
    {
        $db = db();
        $stmt = $db->prepare("
            SELECT p.*, c.name AS category
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY c.name, p.name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function show(int $id): ?array
    {
        $db = db();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return $product ?: null;
    }

    public function store(array $data): bool
    {
        $db = db();
        $imageController = new ImageController();
        $imagePath = $this->uploadProductImage($imageController, $data['image_path'] ?? null);

        $stmt = $db->prepare("
            INSERT INTO products (name, price, category_id, image_path, available, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $data['name'],
            $data['price'],
            $data['category_id'],
            $imagePath,
            $data['available'],
        ]);

        return $stmt->rowCount() > 0;
    }

    public function update(int $id, array $data): bool
    {
        $existingProduct = $this->show($id);

        if (!$existingProduct) {
            return false;
        }

        $imageController = new ImageController();
        $uploadedImage = $this->uploadProductImage($imageController, $data['image_path'] ?? null);
        $imagePath = $uploadedImage ?? $existingProduct['image_path'];
        $oldImagePath = (string)($existingProduct['image_path'] ?? '');

        $db = db();
        $stmt = $db->prepare("
            UPDATE products
            SET name = ?, price = ?, category_id = ?, image_path = ?, available = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $updated = $stmt->execute([
            $data['name'],
            $data['price'],
            $data['category_id'],
            $imagePath,
            $data['available'],
            $id,
        ]);

        if ($updated && $uploadedImage !== null && $oldImagePath !== '' && $oldImagePath !== $uploadedImage) {
            $this->deleteProductImage($oldImagePath);
        }

        return $updated;
    }

    public function destroy(int $id): bool
    {
        $existingProduct = $this->show($id);

        if (!$existingProduct) {
            return false;
        }

        $db = db();
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $deleted = $stmt->rowCount() > 0;

        if ($deleted) {
            $this->deleteProductImage((string)($existingProduct['image_path'] ?? ''));
        }

        return $deleted;
    }

    private function uploadProductImage(ImageController $imageController, mixed $file): ?string
    {
        if (!is_array($file) || !isset($file['error'])) {
            return null;
        }

        return $imageController->uploadImage($file);
    }

    private function deleteProductImage(string $relativePath): void
    {
        if (!str_starts_with($relativePath, 'uploads/') || str_contains($relativePath, '..')) {
            return;
        }

        $fullPath = dirname(__DIR__) . '/' . $relativePath;

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}

// ── Procedural helpers (used by admin/customer pages with pagination) ──────────

function searchProductsAll(string $query = ''): array
{
    $db = db();
    $like = '%' . $query . '%';
    $stmt = $db->prepare("
        SELECT p.id, p.name, p.price, p.image_path, c.name AS category
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.available = 1
        AND (? = '' OR p.name LIKE ? OR c.name LIKE ?)
        ORDER BY c.name, p.name
    ");
    $stmt->execute([$query, $like, $like]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// function searchProducts(string $query, int $page = 1, int $perPage = 10): array
// {
//     $like = '%' . $query . '%';
//     $stmt = "
//         SELECT p.id, p.name, p.price, p.image_path, c.name AS category
//         FROM products p
//         LEFT JOIN categories c ON p.category_id = c.id
//         WHERE p.available = 1
//         AND (? = '' OR p.name LIKE ? OR c.name LIKE ?)
//         ORDER BY c.name, p.name
//     ";
//     return paginate($stmt, $page, $perPage, [$query, $like, $like]);
// }



function getProducts(int $page = 1, int $perPage = 10): array
{
    $query = "
        SELECT p.*, c.name AS category
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY c.name, p.name
    ";
    return paginate($query, $page, $perPage);
}

function getProduct(int $id): ?array
{
    $stmt = db()->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function createProduct(string $name, float $price, ?int $categoryId, ?string $imagePath, bool $available = true): bool
{
    $stmt = db()->prepare("
        INSERT INTO products (name, price, category_id, image_path, available, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([$name, $price, $categoryId, $imagePath, $available]);
    return $stmt->rowCount() > 0;
}

function deleteProduct(int $id): bool
{
    $stmt = db()->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

function updateProduct(int $id, array $data): bool
{
    $stmt = db()->prepare("
        UPDATE products
        SET name = ?, price = ?, category_id = ?, image_path = ?, available = ?, updated_at = NOW()
        WHERE id = ?
    ");
    return $stmt->execute([$data['name'], $data['price'], $data['category_id'], $data['image_path'], $data['available'], $id]);
}
