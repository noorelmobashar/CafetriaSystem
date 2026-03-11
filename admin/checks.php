<?php
session_start();
if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] !== 'admin')){
    header('Location:'.($_SESSION['user_role'] === 'customer' ? '../customer/menu.php' : '../index.php'));
    exit;
}
require_once __DIR__ . '/../controllers/Order.php';
require_once __DIR__ . '/../controllers/User.php';

// Filters from GET
$filterUserId   = (isset($_GET['user_id']) && $_GET['user_id'] !== 'all') ? (int)$_GET['user_id'] : null;
$filterDateFrom = trim($_GET['date_from'] ?? '');
$filterDateTo   = trim($_GET['date_to']   ?? '');

$customerUsers = getCustomerUsers();
$aggregations  = getChecksAggregations($filterUserId, $filterDateFrom, $filterDateTo);
$orders        = getOrdersByFilters($filterUserId, $filterDateFrom, $filterDateTo);

$statusMeta = [
    'incoming'         => ['label' => 'Incoming',         'pill' => 'status-incoming'],
    'processing'       => ['label' => 'Processing',        'pill' => 'status-processing'],
    'out for delivery' => ['label' => 'Out for Delivery',  'pill' => 'status-out-for-delivery'],
    'done'             => ['label' => 'Done',              'pill' => 'status-done'],
    'canceled'         => ['label' => 'Canceled',          'pill' => 'bg-slate-200 text-slate-700'],
];

$pageTitle = 'Cafetria System | Checks';
$basePath = '..';
$pageKey = 'admin-checks';
$pageRole = 'admin';
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

      <!-- Filters -->
      <form method="GET" action="" class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cafe-500">Audit spending</p>
          <h2 class="mt-2 text-2xl font-bold text-slate-900">Checks</h2>
        </div>
        <div class="grid gap-3 md:grid-cols-4 xl:min-w-[58rem]">
          <div>
            <label for="checks-user" class="mb-2 block text-sm font-semibold text-slate-700">User</label>
            <select name="user_id" id="checks-user" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
              <option value="all" <?= $filterUserId === null ? 'selected' : '' ?>>All users</option>
              <?php foreach ($customerUsers as $u): ?>
                <option value="<?= (int)$u['id'] ?>" <?= $filterUserId === (int)$u['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($u['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="checks-from" class="mb-2 block text-sm font-semibold text-slate-700">From</label>
            <input type="date" name="date_from" id="checks-from" value="<?= htmlspecialchars($filterDateFrom) ?>"
              class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
          </div>
          <div>
            <label for="checks-to" class="mb-2 block text-sm font-semibold text-slate-700">To</label>
            <input type="date" name="date_to" id="checks-to" value="<?= htmlspecialchars($filterDateTo) ?>"
              class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
          </div>
          <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Filter</button>
            <a href="checks.php" class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
          </div>
        </div>
      </form>

      <!-- Aggregations -->
      <div class="mt-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-[1.5rem] bg-slate-900 p-5 text-white">
          <p class="text-sm text-slate-300">Total amount</p>
          <p class="mt-2 text-3xl font-bold"><?= number_format((float)$aggregations['total_amount'], 0) ?> LE</p>
        </div>
        <div class="rounded-[1.5rem] bg-brand-50 p-5">
          <p class="text-sm text-brand-700">Orders counted</p>
          <p class="mt-2 text-3xl font-bold text-brand-900"><?= (int)$aggregations['orders_count'] ?></p>
        </div>
        <div class="rounded-[1.5rem] bg-cafe-100 p-5">
          <p class="text-sm text-cafe-600">Average check</p>
          <p class="mt-2 text-3xl font-bold text-cafe-600"><?= number_format((float)$aggregations['average_amount'], 0) ?> LE</p>
        </div>
      </div>

      <!-- Orders table -->
      <div class="mt-6 table-shell custom-scrollbar">
        <table>
          <thead>
            <tr>
              <th>User</th>
              <th>Date</th>
              <th>Status</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
              <tr><td colspan="4"><div class="p-8 text-center text-sm text-slate-500">No matching checks found.</div></td></tr>
            <?php else: ?>
              <?php foreach ($orders as $order): ?>
                <?php $meta = $statusMeta[$order['status']] ?? ['label' => $order['status'], 'pill' => 'bg-slate-200 text-slate-700']; ?>
                <tr>
                  <td>
                    <div>
                      <p class="font-semibold text-slate-900"><?= htmlspecialchars($order['user_name'] ?? 'Unknown') ?></p>
                      <p class="text-sm text-slate-500">Room <?= htmlspecialchars($order['room_snapshot'] ?? '') ?></p>
                    </div>
                  </td>
                  <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($order['created_at']))) ?></td>
                  <td><span class="status-pill <?= $meta['pill'] ?>"><?= htmlspecialchars($meta['label']) ?></span></td>
                  <td><?= number_format((float)$order['total_amount'], 0) ?> LE</td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/page-end.php'; ?>
