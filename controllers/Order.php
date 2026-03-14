<?php
require_once __DIR__ . '/../includes/bootstrap.php';

function getAllOrders() {
    $db = db();

    $stmt = $db->query("
        SELECT o.id, o.status, o.total_amount, o.room_snapshot, o.notes, o.created_at,
               u.name AS user_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at ASC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        return ['incoming' => [], 'processing' => [], 'out for delivery' => [], 'done' => []];
    }

    $ids = implode(',', array_map('intval', array_column($rows, 'id')));
    $itemStmt = $db->query("
        SELECT oi.order_id, oi.quantity, oi.unit_price, p.name AS product_name
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id IN ($ids)
    ");
    $allItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    $itemsByOrder = [];
    foreach ($allItems as $item) {
        $itemsByOrder[$item['order_id']][] = $item;
    }

    $grouped = ['incoming' => [], 'processing' => [], 'out for delivery' => [], 'done' => []];
    foreach ($rows as $order) {
        $order['items'] = $itemsByOrder[$order['id']] ?? [];
        $grouped[$order['status']][] = $order;
    }

    return $grouped;
}

function advanceOrderStatus(int $orderId): bool {
    $db = db();
    $next = [
        'incoming'         => 'processing',
        'processing'       => 'out for delivery',
        'out for delivery' => 'done',
    ];
    $stmt = $db->prepare('SELECT status FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order || !isset($next[$order['status']])) return false;
    $stmt = $db->prepare('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$next[$order['status']], $orderId]);
    return true;
}
//For admin dashboard
function createManualOrder(int $userId, string $room, string $note, array $items): int {
    $db = db();

    // Fetch prices from DB — never trust frontend values
    $productIds = array_map('intval', array_column($items, 'product_id'));
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $db->prepare("SELECT id, price FROM products WHERE id IN ($placeholders) AND available = 1");
    $stmt->execute($productIds);
    $prices = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $total = 0.0;
    $validatedItems = [];
    foreach ($items as $item) {
        $pid = (int)$item['product_id'];
        $qty = max(1, (int)$item['qty']);
        if (!isset($prices[$pid])) continue;
        $unitPrice = (float)$prices[$pid];
        $total += $unitPrice * $qty;
        $validatedItems[] = ['product_id' => $pid, 'qty' => $qty, 'unit_price' => $unitPrice];
    }

    if (empty($validatedItems)) {
        throw new \InvalidArgumentException('No valid products in order.');
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("
            INSERT INTO orders (user_id, status, total_amount, room_snapshot, notes, created_at, updated_at)
            VALUES (?, 'incoming', ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$userId, $total, $room, $note]);
        $orderId = (int)$db->lastInsertId();

        $stmt = $db->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price)
            VALUES (?, ?, ?, ?)
        ");
        foreach ($validatedItems as $item) {
            $stmt->execute([$orderId, $item['product_id'], $item['qty'], $item['unit_price']]);
        }

        $db->commit();
        return $orderId;
    } catch (\Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

//For admin dashboard
function getChecksAggregations(?int $userId, string $dateFrom, string $dateTo): array {
    $db = db();
    $where  = ["status != 'canceled'"];
    $params = [];
    if ($userId !== null) { $where[] = 'user_id = ?';           $params[] = $userId;   }
    if ($dateFrom !== '') { $where[] = 'DATE(created_at) >= ?'; $params[] = $dateFrom; }
    if ($dateTo   !== '') { $where[] = 'DATE(created_at) <= ?'; $params[] = $dateTo;   }
    $stmt = $db->prepare(
        "SELECT COALESCE(SUM(total_amount), 0) AS total_amount,
                COUNT(*)                       AS orders_count,
                COALESCE(AVG(total_amount), 0) AS average_amount
         FROM orders WHERE " . implode(' AND ', $where)
    );
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    //For admin dashboard
    function getOrdersByFilters(?int $userId, string $dateFrom, string $dateTo): array {
        $db = db();
    $where  = ["o.status != 'canceled'"];
    $params = [];
    if ($userId !== null) { $where[] = 'o.user_id = ?';           $params[] = $userId;   }
    if ($dateFrom !== '') { $where[] = 'DATE(o.created_at) >= ?'; $params[] = $dateFrom; }
    if ($dateTo   !== '') { $where[] = 'DATE(o.created_at) <= ?'; $params[] = $dateTo;   }
    $stmt = $db->prepare(
        "SELECT o.id, o.status, o.total_amount, o.room_snapshot, o.created_at,
                u.name AS user_name
         FROM orders o
         LEFT JOIN users u ON o.user_id = u.id
         WHERE " . implode(' AND ', $where) . "
         ORDER BY o.created_at DESC"
         );
         $stmt->execute($params);
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }


     // In Customre Dashboard
    function createCustomerOrder(int $userId, string $room, string $note, array $items): int {
    return createManualOrder($userId, $room, $note, $items);
}
function getCustomerOrders(int $userId, string $dateFrom, string $dateTo): array {
    $db     = db();
    $where  = ['o.user_id = ?'];
    $params = [$userId];
    if ($dateFrom !== '') { $where[] = 'DATE(o.created_at) >= ?'; $params[] = $dateFrom; }
    if ($dateTo   !== '') { $where[] = 'DATE(o.created_at) <= ?'; $params[] = $dateTo;   }

    $stmt = $db->prepare(
        "SELECT o.id, o.status, o.total_amount, o.room_snapshot, o.notes, o.created_at
         FROM orders o
         WHERE " . implode(' AND ', $where) . "
         ORDER BY o.created_at DESC"
    );
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($orders)) return [];

    $ids      = implode(',', array_map('intval', array_column($orders, 'id')));
    $itemStmt = $db->query(
        "SELECT oi.order_id, oi.quantity, p.name AS product_name
         FROM order_items oi
         LEFT JOIN products p ON oi.product_id = p.id
         WHERE oi.order_id IN ($ids)"
    );
    $itemsByOrder = [];
    foreach ($itemStmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $itemsByOrder[$item['order_id']][] = $item;
    }
    foreach ($orders as &$order) {
        $order['items'] = $itemsByOrder[$order['id']] ?? [];
    }
    unset($order);
    return $orders;
}

    
     function getCustomerInsights(int $userId): array {
         $stmt = db()->prepare("
         SELECT
         SUM(status IN ('incoming', 'processing'))                        AS pending_count,
         SUM(CASE WHEN status != 'canceled'
         AND YEAR(created_at)  = YEAR(CURDATE())
         AND MONTH(created_at) = MONTH(CURDATE())
         THEN total_amount ELSE 0 END)                           AS month_spend
             FROM orders
             WHERE user_id = ?
             ");
         $stmt->execute([$userId]);
         $row = $stmt->fetch(PDO::FETCH_ASSOC);
         return [
             'pending_count' => (int)($row['pending_count'] ?? 0),
             'month_spend'   => (float)($row['month_spend']   ?? 0.0),
                 ];
     }
    function cancelCustomerOrder(int $orderId, int $userId): bool {
        $db   = db();
        $stmt = $db->prepare('SELECT status, user_id FROM orders WHERE id = ?');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order || (int)$order['user_id'] !== $userId) return false;
        if (!in_array($order['status'], ['incoming', 'processing'], true)) return false;
        $db->prepare('DELETE FROM order_items WHERE order_id = ?')->execute([$orderId]);
        $db->prepare('DELETE FROM orders WHERE id = ?')->execute([$orderId]);
        return true;
    }