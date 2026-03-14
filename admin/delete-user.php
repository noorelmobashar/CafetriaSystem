<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../controllers/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid user id.';
    header('Location: users.php');
    exit;
}

try {
    $userController = new UserController();
    $user = $userController->show($id);

    if (!$user || ($user['role'] ?? '') !== 'customer') {
        $_SESSION['error_message'] = 'User not found.';
        header('Location: users.php');
        exit;
    }

    $deleted = $userController->destroy($id);
    $_SESSION['success_message'] = $deleted ? 'User deleted successfully.' : 'User not found.';
} catch (Throwable $exception) {
    $_SESSION['error_message'] = 'Failed to delete user.';
}

header('Location: users.php');
exit;
