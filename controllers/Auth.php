<?php
require __DIR__ . '/../includes/bootstrap.php';
require_once '../src/Support/Database.php';

use Cafetria\Support\Database;

$db = Database::connection();

function verifyAndUpgradePassword(PDO $db, array $user, string $password): bool {
    $stored = (string)($user['password_hash'] ?? '');

    if ($stored === '') {
        return false;
    }

    $isValid = password_verify($password, $stored);

    // Backward compatibility for legacy rows that stored raw passwords.
    if (!$isValid && hash_equals($stored, $password)) {
        $isValid = true;
    }

    if (!$isValid) {
        return false;
    }

    if (!str_starts_with($stored, '$2y$') || password_needs_rehash($stored, PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $update = $db->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
        $update->execute([$newHash, (int)$user['id']]);
    }

    return true;
}







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

                if ($user && verifyAndUpgradePassword($db, $user, $password)) {
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
