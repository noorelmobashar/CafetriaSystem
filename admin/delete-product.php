<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../controllers/Product.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid product id.';
    header('Location: products.php');
    exit;
}

try {
    $deleted = deleteProduct($id);
    $_SESSION['success_message'] = $deleted ? 'Product deleted successfully.' : 'Product not found.';
} catch (Throwable $exception) {
    $_SESSION['error_message'] = 'Failed to delete product.';
}

header('Location: products.php');
exit;
