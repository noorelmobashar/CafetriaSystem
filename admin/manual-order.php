<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Cafetria System | Manual Order';
$basePath = '..';
$pageKey = 'admin-manual-order';
$pageRole = 'admin';
$pageScript = 'assets/js/pages/admin-manual-order.js';
$currentPage = 'manual-order';
$headerBadge = 'Administrator';
$headerTitle = 'Assign manual orders';
$headerSubtitle = 'Create bills for employees and push them straight into the pipeline.';
require __DIR__ . '/../includes/page-start.php';
?>
<main class="relative min-h-screen px-4 py-6 md:px-6 lg:px-8">
  <div class="mx-auto max-w-7xl space-y-6">
    <?php require __DIR__ . '/../includes/dashboard-header.php'; ?>
    <?php require __DIR__ . '/../includes/admin-nav.php'; ?>

    <section class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6">
      <div class="grid gap-6 xl:grid-cols-[0.9fr,1.1fr]">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.25em] text-pine-500">Assign bills</p>
          <h2 class="mt-2 text-2xl font-bold text-slate-900">Manual Order</h2>
          <div class="mt-6 space-y-4">
            <div><label for="manual-user" class="mb-2 block text-sm font-semibold text-slate-700">Select user</label><select id="manual-user" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100"></select></div>
            <div><label for="manual-room" class="mb-2 block text-sm font-semibold text-slate-700">Room</label><select id="manual-room" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100"></select></div>
            <div><label for="manual-note" class="mb-2 block text-sm font-semibold text-slate-700">Note</label><input id="manual-note" type="text" placeholder="Optional order note" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" /></div>
            <button id="assign-manual-order-btn" class="w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Add to user</button>
          </div>
        </div>
        <div>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Select products</h3>
            <div class="rounded-2xl bg-slate-100 px-4 py-2 text-sm text-slate-600">Assigned as Incoming</div>
          </div>
          <div class="mt-4">
            <label for="manual-product-search" class="mb-2 block text-sm font-semibold text-slate-700">Search products</label>
            <input id="manual-product-search" type="search" placeholder="Search by product name or category" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
          </div>
          <div id="manual-products-grid" class="mt-6 grid gap-4 md:grid-cols-2"></div>
          <div class="mt-6 rounded-[1.5rem] bg-slate-900 p-5 text-white">
            <div id="manual-cart-items" class="space-y-3"></div>
            <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-4 text-lg font-semibold"><span>Total</span><span id="manual-total">0 LE</span></div>
          </div>
        </div>
      </div>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/page-end.php'; ?>
