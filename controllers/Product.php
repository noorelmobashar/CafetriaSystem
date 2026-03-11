<?php
    function searchProducts(string $query): array {
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
}?>