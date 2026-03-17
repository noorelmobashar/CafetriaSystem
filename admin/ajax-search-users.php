<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../controllers/User.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 5;

$userController = new UserController();
$data = $query === ''
    ? $userController->index($page, $perPage)
    : $userController->search($query, $page, $perPage);

echo json_encode([
    'success' => true,
    'users' => $data['data'],
    'totalPages' => $data['totalPages'],
    'currentPage' => $page,
]);
