<?php
session_start();
if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] !== 'admin')){
    header('Location:'.($_SESSION['user_role'] === 'customer' ? '../customer/menu.php' : '../index.php'));
    exit;
}
$pageTitle = 'Cafetria System | Admin Dashboard';
$basePath = '..';
$pageKey = 'admin-dashboard';
$pageRole = 'admin';
$pageScript = 'assets/js/pages/admin-dashboard.js';
$currentPage = 'dashboard';
$headerBadge = 'Administrator';
$headerTitle = 'Cafeteria control center';
$headerSubtitle = 'Monitor the four-stage pipeline from one clear operational board.';
require __DIR__ . '/../includes/page-start.php';
?>
<main class="relative min-h-screen px-4 py-6 md:px-6 lg:px-8">
  <div class="mx-auto max-w-7xl space-y-6">
    <?php require __DIR__ . '/../includes/dashboard-header.php'; ?>
    <?php require __DIR__ . '/../includes/admin-nav.php'; ?>

    <section class="space-y-6">
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur"><p class="text-sm text-slate-500">Incoming</p><p id="stat-incoming" class="mt-2 text-3xl font-bold text-slate-900">0</p></div>
        <div class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur"><p class="text-sm text-slate-500">Processing</p><p id="stat-processing" class="mt-2 text-3xl font-bold text-slate-900">0</p></div>
        <div class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur"><p class="text-sm text-slate-500">Out for delivery</p><p id="stat-delivery" class="mt-2 text-3xl font-bold text-slate-900">0</p></div>
        <div class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur"><p class="text-sm text-slate-500">Done</p><p id="stat-done" class="mt-2 text-3xl font-bold text-slate-900">0</p></div>
      </div>

      <div class="grid gap-6 xl:grid-cols-4">
        <div class="kanban-column rounded-[2rem] border border-sky-100 bg-white/85 p-4 shadow-soft backdrop-blur"><div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-bold text-slate-900">Incoming</h2><span id="incoming-count" class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">0</span></div><div id="incoming-list" class="space-y-4"></div></div>
        <div class="kanban-column rounded-[2rem] border border-amber-100 bg-white/85 p-4 shadow-soft backdrop-blur"><div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-bold text-slate-900">Processing</h2><span id="processing-count" class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">0</span></div><div id="processing-list" class="space-y-4"></div></div>
        <div class="kanban-column rounded-[2rem] border border-violet-100 bg-white/85 p-4 shadow-soft backdrop-blur"><div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-bold text-slate-900">Out for Delivery</h2><span id="delivery-count" class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700">0</span></div><div id="delivery-list" class="space-y-4"></div></div>
        <div class="kanban-column rounded-[2rem] border border-emerald-100 bg-white/85 p-4 shadow-soft backdrop-blur"><div class="mb-4 flex items-center justify-between"><h2 class="text-lg font-bold text-slate-900">Done</h2><span id="done-count" class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">0</span></div><div id="done-list" class="space-y-4"></div></div>
      </div>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/page-end.php'; ?>
