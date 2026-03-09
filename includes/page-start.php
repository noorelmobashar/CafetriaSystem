<?php
require_once __DIR__ . '/bootstrap.php';

$pageTitle = $pageTitle ?? 'Cafetria System';
$basePath = $basePath ?? '.';
$bodyClass = $bodyClass ?? 'min-h-screen bg-slate-100 text-slate-800';
$pageKey = $pageKey ?? 'page';
$pageRole = $pageRole ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <script src="<?php echo htmlspecialchars(asset_url($basePath, 'assets/js/config/tailwind-config.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?php echo htmlspecialchars(asset_url($basePath, 'assets/css/main.css'), ENT_QUOTES, 'UTF-8'); ?>" />
</head>
<body class="<?php echo htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8'); ?>" data-base-path="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>" data-page="<?php echo htmlspecialchars($pageKey, ENT_QUOTES, 'UTF-8'); ?>" data-role="<?php echo htmlspecialchars($pageRole, ENT_QUOTES, 'UTF-8'); ?>">
  <div class="fixed inset-0 -z-10 bg-slate-100">
    <div class="absolute inset-0 bg-mesh"></div>
    <div class="absolute left-0 top-0 h-72 w-72 rounded-full bg-brand-100 blur-3xl opacity-70"></div>
    <div class="absolute right-0 top-24 h-72 w-72 rounded-full bg-cafe-200 blur-3xl opacity-80"></div>
    <div class="absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-emerald-100 blur-3xl opacity-70"></div>
  </div>
