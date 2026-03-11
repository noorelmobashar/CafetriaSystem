<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Cafetria System | Checks';
$basePath = '..';
$pageKey = 'admin-checks';
$pageRole = 'admin';
$pageScript = 'assets/js/pages/admin-checks.js';
$currentPage = 'checks';
$headerBadge = 'Administrator';
$headerTitle = 'Audit cafeteria checks';
$headerSubtitle = 'Filter by user and date range to understand spending clearly.';
require __DIR__ . '/../includes/page-start.php';
?>
<main class="relative min-h-screen px-4 py-6 md:px-6 lg:px-8">
  <div class="mx-auto max-w-7xl space-y-6">
    <?php require __DIR__ . '/../includes/dashboard-header.php'; ?>
    <?php require __DIR__ . '/../includes/admin-nav.php'; ?>

    <section class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6">
      <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div><p class="text-sm font-semibold uppercase tracking-[0.25em] text-cafe-500">Audit spending</p><h2 class="mt-2 text-2xl font-bold text-slate-900">Checks</h2></div>
        <div class="grid gap-3 md:grid-cols-4 xl:min-w-[58rem]">
          <div><label for="checks-user" class="mb-2 block text-sm font-semibold text-slate-700">User</label><select id="checks-user" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100"></select></div>
          <div><label for="checks-from" class="mb-2 block text-sm font-semibold text-slate-700">From</label><input id="checks-from" type="date" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" /></div>
          <div><label for="checks-to" class="mb-2 block text-sm font-semibold text-slate-700">To</label><input id="checks-to" type="date" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" /></div>
          <div class="flex items-end"><button id="reset-checks-btn" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</button></div>
        </div>
      </div>
      <div class="mt-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-[1.5rem] bg-slate-900 p-5 text-white"><p class="text-sm text-slate-300">Total amount</p><p id="checks-total" class="mt-2 text-3xl font-bold">0 LE</p></div>
        <div class="rounded-[1.5rem] bg-brand-50 p-5"><p class="text-sm text-brand-700">Orders counted</p><p id="checks-orders-count" class="mt-2 text-3xl font-bold text-brand-900">0</p></div>
        <div class="rounded-[1.5rem] bg-cafe-100 p-5"><p class="text-sm text-cafe-600">Average check</p><p id="checks-average" class="mt-2 text-3xl font-bold text-cafe-600">0 LE</p></div>
      </div>
      <div id="checks-table" class="mt-6 overflow-hidden rounded-[1.5rem] border border-slate-200"></div>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/page-end.php'; ?>
