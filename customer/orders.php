<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Cafetria System | My Orders';
$basePath = '..';
$pageKey = 'customer-orders';
$pageRole = 'customer';
$currentPage = 'orders';
$headerBadge = 'Customer Portal';
$headerTitle = 'Track every order';
$headerSubtitle = 'Review status updates, filter by date, and cancel active requests.';
require __DIR__ . '/../includes/page-start.php';
?>
<main class="relative min-h-screen px-4 py-6 md:px-6 lg:px-8">
  <div class="mx-auto max-w-7xl space-y-6">
    <?php require __DIR__ . '/../includes/dashboard-header.php'; ?>
    <?php require __DIR__ . '/../includes/customer-nav.php'; ?>

    <section class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.25em] text-brand-600">History</p>
          <h2 class="mt-2 text-2xl font-bold text-slate-900">My Orders</h2>
        </div>
        <div class="rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-600">Real-time order states</div>
      </div>
      <div class="mt-5 grid gap-3 md:grid-cols-[1fr,1fr,auto]">
        <div>
          <label for="customer-date-from" class="mb-2 block text-sm font-semibold text-slate-700">From</label>
          <input id="customer-date-from" type="date" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
        </div>
        <div>
          <label for="customer-date-to" class="mb-2 block text-sm font-semibold text-slate-700">To</label>
          <input id="customer-date-to" type="date" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
        </div>
        <div class="flex items-end">
          <button id="clear-order-filters" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Clear filters</button>
        </div>
      </div>
      <div id="customer-orders" class="mt-6 space-y-4"></div>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/page-end.php'; ?>
