<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../controllers/Order.php';
require_once __DIR__ . '/../controllers/User.php';
require_once __DIR__ . '/../controllers/Product.php';
require_once __DIR__ . '/../controllers/User.php';
// Room options from orders.room_snapshot enum
$roomOptions = ['100','200','300','400','500','600','700','800','900','1000'];

// Load customer users
$userController = new UserController();
$customerUsers = $userController->index();

$successMessage = null;
$errorMessage   = null;

// Handle order creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $userId = (int)($_POST['user_id'] ?? 0);
    $room   = $_POST['room'] ?? '';
    $note   = trim($_POST['note'] ?? '');
    $qtys   = $_POST['qty'] ?? [];

    $items = [];
    foreach ($qtys as $productId => $qty) {
        if ((int)$qty > 0) {
            $items[] = ['product_id' => (int)$productId, 'qty' => (int)$qty];
        }
    }

    if (!$userId) {
        $errorMessage = 'Please select a user.';
    } elseif (empty($items)) {
        $errorMessage = 'Select at least one product first.';
    } else {
        try {
            createManualOrder($userId, $room, $note, $items);
            $successMessage = 'Manual order assigned to user account.';
        } catch (\Throwable $e) {
            $errorMessage = 'Failed to create order: ' . $e->getMessage();
        }
    }
}

// Search query
$searchQuery = trim($_POST['product_search'] ?? '');
$products = searchProducts($searchQuery);

// Preserve submitted quantities across render (after search or error)
$submittedQtys = $_POST['qty'] ?? [];

$pageTitle = 'Cafetria System | Manual Order';
$basePath = '..';
$pageKey = 'admin-manual-order';
$pageRole = 'admin';
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

    <?php if ($successMessage): ?>
      <div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-4 text-sm font-semibold text-emerald-700"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
      <div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-4 text-sm font-semibold text-red-700"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <form method="POST">
      <section class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6">
        <div class="grid gap-6 xl:grid-cols-[0.9fr,1.1fr]">

          <!-- Left: Order details -->
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-pine-500">Assign bills</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-900">Manual Order</h2>
            <div class="mt-6 space-y-4">

              <div>
                <label for="manual-user" class="mb-2 block text-sm font-semibold text-slate-700">Select user</label>
                <select name="user_id" id="manual-user" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                  <?php foreach ($customerUsers as $u): ?>
                    <option value="<?= (int)$u['id'] ?>" <?= (isset($_POST['user_id']) && (int)$_POST['user_id'] === (int)$u['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($u['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label for="manual-room" class="mb-2 block text-sm font-semibold text-slate-700">Room</label>
                <select name="room" id="manual-room" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                  <?php foreach ($roomOptions as $room): ?>
                    <option value="<?= htmlspecialchars($room) ?>" <?= (($_POST['room'] ?? '') === $room) ? 'selected' : '' ?>>
                      Room <?= htmlspecialchars($room) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label for="manual-note" class="mb-2 block text-sm font-semibold text-slate-700">Note</label>
                <input name="note" id="manual-note" type="text" placeholder="Optional order note"
                  value="<?= htmlspecialchars($_POST['note'] ?? '') ?>"
                  class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
              </div>

              <button type="submit" name="create_order" value="1" class="w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Add to user</button>
            </div>
          </div>

          <!-- Right: Product search + grid -->
          <div>
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-bold text-slate-900">Select products</h3>
              <div class="rounded-2xl bg-slate-100 px-4 py-2 text-sm text-slate-600">Assigned as Incoming</div>
            </div>

            <div class="mt-4">
              <input name="product_search" type="search" placeholder="Search by product name or category (real-time)"
                value="<?= htmlspecialchars($searchQuery) ?>"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
            </div>

            <div id="products-grid" class="mt-6 grid gap-4 md:grid-cols-2">
              <?php if (empty($products)): ?>
                <div class="md:col-span-2 rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">No products match your search.</div>
              <?php else: ?>
                <?php foreach ($products as $product): ?>
                  <?php $qty = (int)($submittedQtys[$product['id']] ?? 0); ?>
                  <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4"
                    data-product-id="<?= (int)$product['id'] ?>"
                    data-product-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                    data-product-price="<?= (float)$product['price'] ?>">
                    <div class="flex gap-4">
                      <?php if ($product['image_path']): ?>
                        <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="h-20 w-20 rounded-2xl object-cover" />
                      <?php endif; ?>
                      <div class="flex-1">
                        <div class="flex items-start justify-between gap-3">
                          <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400"><?= htmlspecialchars($product['category'] ?? '') ?></p>
                            <h3 class="mt-1 text-lg font-bold text-slate-900"><?= htmlspecialchars($product['name']) ?></h3>
                          </div>
                          <div class="rounded-2xl bg-white px-3 py-2 text-sm font-semibold text-slate-700"><?= number_format($product['price'], 0) ?> LE</div>
                        </div>
                        <div class="mt-4">
                          <label class="text-xs text-slate-500 mb-1 block">Quantity</label>
                          <input type="number" name="qty[<?= (int)$product['id'] ?>]"
                            value="<?= $qty ?>" min="0"
                            class="w-24 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
                        </div>
                      </div>
                    </div>
                  </article>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <!-- Cart summary -->
            <?php
              $cartTotal = 0.0;
              $cartLines = [];
              foreach ($submittedQtys as $pid => $qty) {
                  if ((int)$qty <= 0) continue;
                  foreach ($products as $p) {
                      if ((int)$p['id'] === (int)$pid) {
                          $lineTotal = (float)$p['price'] * (int)$qty;
                          $cartTotal += $lineTotal;
                          $cartLines[] = ['name' => $p['name'], 'qty' => (int)$qty, 'price' => (float)$p['price'], 'total' => $lineTotal];
                          break;
                      }
                  }
              }
            ?>
            <div class="mt-6 rounded-[1.5rem] bg-slate-900 p-5 text-white">
              <div id="js-cart-lines" class="space-y-3">
                <?php if (empty($cartLines)): ?>
                  <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300">No products selected.</div>
                <?php else: ?>
                  <?php foreach ($cartLines as $line): ?>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-white/10 p-4">
                      <div>
                        <p class="font-semibold"><?= htmlspecialchars($line['name']) ?></p>
                        <p class="text-sm text-slate-300"><?= $line['qty'] ?> × <?= number_format($line['price'], 0) ?> LE</p>
                      </div>
                      <div class="font-semibold"><?= number_format($line['total'], 0) ?> LE</div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
              <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-4 text-lg font-semibold">
                <span>Total</span>
                <span id="js-cart-total"><?= number_format($cartTotal, 0) ?> LE</span>
              </div>
            </div>

          </div>
        </div>
      </section>
    </form>

  </div>
</main>
<?php
$pageScript = 'assets/js/components/manual-order-cart.js';
require __DIR__ . '/../includes/page-end.php';
?>
