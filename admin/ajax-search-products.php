<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../controllers/Product.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$products = searchProducts($query);

echo json_encode([
    'success' => true,
    'products' => $products
]);
?>
