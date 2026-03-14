<?php
require __DIR__ . '/../includes/bootstrap.php';
require_once '../src/Support/Database.php';

use Cafetria\Support\Database;

$db = Database::connection();







if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = trim($_POST['role'] ?? 'customer');

        if (!empty($email) && !empty($password)) {
            try {
                $stmt = $db->prepare("
                    SELECT id, name, email, password_hash, role, profile_pic 
                    FROM users 
                    WHERE email = ?
                    LIMIT 1
                ");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['name'];



                    header('Location: ' . ($user['role'] === 'admin' ? '../admin/index.php' : '../customer/menu.php'));
                    exit;
                } else {
                    $_SESSION['error'] = "invalid credentials";
                    header('Location:../index.php');
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "An error occurred while processing your request. Please try again later.";
                header('Location: ../index.php');
                exit;
            }
        }else {
            $_SESSION['error'] = "Email and password are required.";
            header('Location: ../index.php');
            exit;
        }
    }

    if (isset($_POST['logout'])) {
        $_SESSION = [];
        session_destroy();
        header('Location: ../index.php');
        exit;
    }
}







$pageTitle = 'Cafetria System | Login';
$basePath = '.';
$pageKey = 'login';
$pageRole = 'guest';
require __DIR__ . '/../includes/page-start.php';
