<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Cafetria System | Products';
$basePath = '..';
$pageKey = 'admin-products';
$pageRole = 'admin';
$pageScript = 'assets/js/pages/admin-products.js';
$currentPage = 'products';
$headerBadge = 'Administrator';
$headerTitle = 'Manage products';
$headerSubtitle = 'Keep the cafeteria catalog clean, visual, and easy to update.';
require __DIR__ . '/../includes/page-start.php';
?>
<main class="relative min-h-screen px-4 py-6 md:px-6 lg:px-8">
  <div class="mx-auto max-w-7xl space-y-6">
    <?php require __DIR__ . '/../includes/dashboard-header.php'; ?>
    <?php require __DIR__ . '/../includes/admin-nav.php'; ?>

    <section class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><p class="text-sm font-semibold uppercase tracking-[0.25em] text-cafe-500">Inventory</p><h2 class="mt-2 text-2xl font-bold text-slate-900">Products</h2></div>
        <button id="add-product-btn" class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Add product</button>
      </div>
      <div id="products-table" class="mt-6 overflow-hidden rounded-[1.5rem] border border-slate-200"></div>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/page-end.php'; ?>
