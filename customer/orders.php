<?php
session_start();
if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] !== 'customer')){
    header('Location:'.($_SESSION['user_role'] === 'admin' ? '../admin/index.php' : '../index.php'));
    exit;
}
require_once __DIR__ . '/../controllers/Order.php';

$userId = (int)$_SESSION['user_id'];

$successMessage = null;
$errorMessage   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $orderId = (int)$_POST['cancel_order_id'];
    if (cancelCustomerOrder($orderId, $userId)) {
        $successMessage = 'Order canceled successfully.';
    } else {
        $errorMessage = 'Could not cancel this order.';
    }
}

$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo   = trim($_GET['date_to']   ?? '');
$orders   = getCustomerOrders($userId, $dateFrom, $dateTo);

$statusMeta = [
    'incoming'         => ['label' => 'Incoming',        'pill' => 'status-incoming'],
    'processing'       => ['label' => 'Processing',       'pill' => 'status-processing'],
    'out for delivery' => ['label' => 'Out for Delivery', 'pill' => 'status-out-for-delivery'],
    'done'             => ['label' => 'Done',             'pill' => 'status-done'],
];

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

    <?php if ($successMessage): ?>
      <div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-4 text-sm font-semibold text-emerald-700"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
      <div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-4 text-sm font-semibold text-red-700"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <section class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.25em] text-brand-600">History</p>
          <h2 class="mt-2 text-2xl font-bold text-slate-900">My Orders</h2>
        </div>
        <div class="rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-600">Real-time order states</div>
      </div>

      <form method="GET" class="mt-5 grid gap-3 md:grid-cols-[1fr,1fr,auto]">
        <div>
          <label for="date-from" class="mb-2 block text-sm font-semibold text-slate-700">From</label>
          <input id="date-from" name="date_from" type="date" value="<?= htmlspecialchars($dateFrom) ?>"
            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
        </div>
        <div>
          <label for="date-to" class="mb-2 block text-sm font-semibold text-slate-700">To</label>
          <input id="date-to" name="date_to" type="date" value="<?= htmlspecialchars($dateTo) ?>"
            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
        </div>
        <div class="flex items-end gap-2">
          <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Filter</button>
          <?php if ($dateFrom !== '' || $dateTo !== ''): ?>
            <a href="orders.php" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Clear</a>
          <?php endif; ?>
        </div>
      </form>

      <div class="mt-6 space-y-4">
        <?php if (empty($orders)): ?>
          <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">No orders found for the selected date range.</div>
        <?php else: ?>
          <?php foreach ($orders as $order):
            $meta      = $statusMeta[$order['status']] ?? ['label' => ucfirst($order['status']), 'pill' => 'bg-slate-100 text-slate-700'];
            $canCancel = in_array($order['status'], ['incoming', 'processing'], true);
          ?>
            <article class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5">
              <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                  <div class="flex flex-wrap items-center gap-3">
                    <h3 class="text-lg font-bold text-slate-900">Order #<?= htmlspecialchars((string)$order['id']) ?></h3>
                    <span class="status-pill <?= htmlspecialchars($meta['pill']) ?>"><?= htmlspecialchars($meta['label']) ?></span>
                  </div>
                  <p class="text-sm text-slate-500">
                    <?= htmlspecialchars(date('d M Y, H:i', strtotime($order['created_at']))) ?>
                    &bull; Room <?= htmlspecialchars($order['room_snapshot']) ?>
                  </p>
                  <?php if (!empty($order['items'])): ?>
                    <div class="flex flex-wrap gap-2">
                      <?php foreach ($order['items'] as $item): ?>
                        <span class="rounded-full bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm">
                          <?= (int)$item['quantity'] ?> &times; <?= htmlspecialchars($item['product_name']) ?>
                        </span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($order['notes'])): ?>
                    <p class="text-sm text-slate-600"><span class="font-semibold text-slate-900">Note:</span> <?= htmlspecialchars($order['notes']) ?></p>
                  <?php endif; ?>
                </div>
                <div class="flex min-w-[12rem] flex-col gap-3 lg:items-end">
                  <p class="text-2xl font-bold text-slate-900"><?= number_format((float)$order['total_amount'], 0) ?> LE</p>
                  <?php if ($canCancel): ?>
                    <form method="POST">
                      <input type="hidden" name="cancel_order_id" value="<?= (int)$order['id'] ?>">
                      <button type="submit" class="rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-rose-700">Cancel order</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/page-end.php'; ?>
