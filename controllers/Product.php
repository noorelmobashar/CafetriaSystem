<?php
    function searchProducts(string $query , int $page = 1, int $perPage = 10): array {
        $db = db();
        $like = '%' . $query . '%';
        $stmt ="
            SELECT p.id, p.name, p.price, p.image_path, c.name AS category
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.available = 1
            AND (? = '' OR p.name LIKE ? OR c.name LIKE ?)
            ORDER BY c.name, p.name
        ";

        return paginate($stmt, $page, $perPage , [$query, $like, $like]);
      
}


//use paginate
// function getProducts(): array {
//     $db = db();
//     $stmt = $db->prepare("
//         SELECT p.*, c.name AS category
//         FROM products p
//         LEFT JOIN categories c ON p.category_id = c.id
//         ORDER BY c.name, p.name
//     ");
//     $stmt->execute();
//     return $stmt->fetchAll(PDO::FETCH_ASSOC);
// }

function getProducts($page = 1, $perPage = 10)
{
    $query = "
        SELECT p.*, c.name AS category
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY c.name, p.name
    ";

    return paginate($query, $page, $perPage);
}

function getProduct(int $id): ?array {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createProduct(string $name, float $price, ?int $categoryId, ?string $imagePath, bool $available = true): bool {
    $db = db();
    $stmt = $db->prepare("
        INSERT INTO products (name, price, category_id, image_path, available, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([$name, $price, $categoryId, $imagePath, $available]);
    return $stmt->rowCount() > 0;
}

function deleteProduct(int $id): bool {
    $db = db();
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

function updateProduct(int $id, array $data): bool {
    $db = db();
    $stmt = $db->prepare("
        UPDATE products
        SET name = ?, price = ?, category_id = ?, image_path = ?, available = ?, updated_at = NOW()
        WHERE id = ?
    ");
    return $stmt->execute([$data['name'], $data['price'], $data['category_id'], $data['image_path'], $data['available'], $id]);
}




?>

