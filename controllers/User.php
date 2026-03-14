<?php
require_once __DIR__ . '/../includes/bootstrap.php';

function getCustomerUsers(): array {
    $stmt = db()->query("SELECT id, name FROM users WHERE role = 'customer' ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
