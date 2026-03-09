<?php
$pageTitle = 'Cafetria System | Customer Menu';
$basePath = '..';
$pageKey = 'customer-menu';
$pageRole = 'customer';
$pageScript = 'assets/js/pages/customer-menu.js';
$currentPage = 'menu';
$headerBadge = 'Customer Portal';
$headerTitle = 'Order drinks in seconds';
$headerSubtitle = 'Customize your order and confirm it from a focused page.';
require __DIR__ . '/../includes/page-start.php';
?>
<main class="relative min-h-screen px-4 py-6 md:px-6 lg:px-8">
  <div class="mx-auto max-w-7xl space-y-6">
    <?php require __DIR__ . '/../includes/dashboard-header.php'; ?>
    <?php require __DIR__ . '/../includes/customer-nav.php'; ?>

    <section class="grid gap-6 xl:grid-cols-[1.3fr,0.7fr]">
      <div class="space-y-6">
        <div class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6">
          <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cafe-500">Order your favorites</p>
              <h2 class="mt-2 text-2xl font-bold text-slate-900">Menu & customization</h2>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
              <div>
                <label for="room-select" class="mb-2 block text-sm font-semibold text-slate-700">Current room</label>
                <select id="room-select" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100"></select>
              </div>
              <div>
                <label for="global-note" class="mb-2 block text-sm font-semibold text-slate-700">Order note</label>
                <input id="global-note" type="text" placeholder="Example: 1 Tea Extra Sugar" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
              </div>
            </div>
          </div>
          <div class="mt-4">
            <label for="product-search" class="mb-2 block text-sm font-semibold text-slate-700">Search products</label>
            <input id="product-search" type="search" placeholder="Search by product name or category" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
          </div>
          <div id="menu-grid" class="mt-6 grid gap-4 md:grid-cols-2"></div>
        </div>
      </div>

      <aside class="space-y-6">
        <div class="rounded-[2rem] border border-white/70 bg-slate-900 p-5 text-white shadow-soft md:p-6">
          <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-300">Current basket</p>
          <h2 class="mt-2 text-2xl font-bold">Order summary</h2>
          <div id="cart-items" class="mt-6 space-y-3"></div>
          <div class="mt-6 rounded-2xl bg-white/10 p-4">
            <div class="flex items-center justify-between text-sm text-slate-300"><span>Room</span><span id="summary-room">-</span></div>
            <div class="mt-3 flex items-center justify-between text-sm text-slate-300"><span>Items</span><span id="summary-items-count">0</span></div>
            <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-4 text-lg font-semibold text-white"><span>Total</span><span id="summary-total">0 LE</span></div>
          </div>
          <button id="place-order-btn" class="mt-6 w-full rounded-2xl bg-white px-4 py-3.5 text-sm font-semibold text-slate-900 transition hover:-translate-y-0.5 hover:bg-slate-100">Confirm order</button>
        </div>

        <div class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6">
          <p class="text-sm font-semibold uppercase tracking-[0.25em] text-pine-500">Quick insights</p>
          <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
            <div class="rounded-3xl bg-emerald-50 p-4"><p class="text-sm text-emerald-700">Pending orders</p><p id="customer-pending-count" class="mt-2 text-3xl font-bold text-emerald-900">0</p></div>
            <div class="rounded-3xl bg-amber-50 p-4"><p class="text-sm text-amber-700">This month spend</p><p id="customer-spend" class="mt-2 text-3xl font-bold text-amber-900">0 LE</p></div>
          </div>
        </div>
      </aside>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/page-end.php'; ?>
