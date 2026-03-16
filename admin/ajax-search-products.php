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
$page = (int)($_GET['page'] ?? 1);
$perPage = 5; // Match the perPage value from manual-order.php

$data = searchProducts($query, $page, $perPage);

echo json_encode([
    'success' => true,
    'products' => $data['data'],
    'totalPages' => $data['totalPages'],
    'currentPage' => $page
]);
?>
