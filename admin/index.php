<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Cafetria System | Admin Dashboard';
$basePath = '..';
$pageKey = 'admin-dashboard';
$pageRole = 'admin';
$currentPage = 'dashboard';
$headerBadge = 'Administrator';
$headerTitle = 'Cafeteria control center';
$headerSubtitle = 'Monitor the four-stage pipeline from one clear operational board.';
require_once __DIR__ . '/../controllers/Order.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['advance_order_id'])) {
    advanceOrderStatus((int)$_POST['advance_order_id']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
$orders = getAllOrders();
require __DIR__ . '/../includes/page-start.php';
?>
<main class="relative min-h-screen px-4 py-6 md:px-6 lg:px-8">
  <div class="mx-auto max-w-7xl space-y-6">
    <?php require __DIR__ . '/../includes/dashboard-header.php'; ?>
    <?php require __DIR__ . '/../includes/admin-nav.php'; ?>

    <?php
    $columns = [
      'incoming'         => ['label' => 'Incoming',        'border' => 'border-sky-100',     'badge' => 'bg-sky-100 text-sky-700',          'pill' => 'status-incoming',           'next' => 'processing',       'action' => 'Start processing'],
      'processing'       => ['label' => 'Processing',       'border' => 'border-amber-100',   'badge' => 'bg-amber-100 text-amber-700',      'pill' => 'status-processing',         'next' => 'out for delivery', 'action' => 'Send to delivery'],
      'out for delivery' => ['label' => 'Out for Delivery', 'border' => 'border-violet-100',  'badge' => 'bg-violet-100 text-violet-700',    'pill' => 'status-out-for-delivery',   'next' => 'done',             'action' => 'Mark as done'],
      'done'             => ['label' => 'Done',             'border' => 'border-emerald-100', 'badge' => 'bg-emerald-100 text-emerald-700',  'pill' => 'status-done',               'next' => null,               'action' => null],
    ];
    ?>
    <section class="space-y-6">
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <?php foreach ($columns as $key => $col): ?>
          <div class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur">
            <p class="text-sm text-slate-500"><?= htmlspecialchars($col['label']) ?></p>
            <p class="mt-2 text-3xl font-bold text-slate-900"><?= count($orders[$key] ?? []) ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="grid gap-6 xl:grid-cols-4">
        <?php foreach ($columns as $key => $col): ?>
          <div class="kanban-column rounded-[2rem] border <?= $col['border'] ?> bg-white/85 p-4 shadow-soft backdrop-blur">
            <div class="mb-4 flex items-center justify-between">
              <h2 class="text-lg font-bold text-slate-900"><?= htmlspecialchars($col['label']) ?></h2>
              <span class="rounded-full <?= $col['badge'] ?> px-3 py-1 text-xs font-semibold"><?= count($orders[$key] ?? []) ?></span>
            </div>
            <div class="space-y-4">
              <?php if (empty($orders[$key])): ?>
                <div class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">No orders in this stage.</div>
              <?php else: ?>
                <?php foreach ($orders[$key] as $order): ?>
                  <article class="card-hover rounded-[1.5rem] border border-slate-200 bg-white p-4">
                    <div class="flex items-start justify-between gap-3">
                      <div>
                        <h3 class="text-base font-bold text-slate-900"><?= htmlspecialchars($order['user_name'] ?? 'Guest') ?></h3>
                        <p class="mt-1 text-sm text-slate-500">Room <?= htmlspecialchars($order['room_snapshot'] ?? '') ?> &bull; <?= htmlspecialchars(date('d M Y, H:i', strtotime($order['created_at']))) ?></p>
                      </div>
                      <span class="status-pill <?= $col['pill'] ?>"><?= htmlspecialchars($col['label']) ?></span>
                    </div>
                    <div class="mt-4 space-y-2 text-sm text-slate-600">
                      <?php foreach ($order['items'] as $item): ?>
                        <div class="flex items-center justify-between gap-3">
                          <span><?= (int)$item['quantity'] ?> &times; <?= htmlspecialchars($item['product_name'] ?? '') ?></span>
                          <span class="font-semibold text-slate-800"><?= number_format($item['quantity'] * $item['unit_price'], 0) ?> LE</span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <?php if (!empty($order['notes'])): ?>
                      <p class="mt-4 rounded-2xl bg-slate-50 px-3 py-2 text-sm text-slate-600"><?= htmlspecialchars($order['notes']) ?></p>
                    <?php endif; ?>
                    <div class="mt-4 flex items-center justify-between">
                      <p class="text-lg font-bold text-slate-900"><?= number_format($order['total_amount'], 0) ?> LE</p>
                      <?php if ($col['next']): ?>
                        <form method="POST">
                          <input type="hidden" name="advance_order_id" value="<?= (int)$order['id'] ?>">
                          <button type="submit" class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"><?= htmlspecialchars($col['action']) ?></button>
                        </form>
                      <?php else: ?>
                        <span class="rounded-2xl bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">Completed</span>
                      <?php endif; ?>
                    </div>
                  </article>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/page-end.php'; ?>
