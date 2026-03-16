<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../controllers/Order.php';
require_once __DIR__ . '/../controllers/Product.php';

$productController = new ProductController();

$roomOptions = ['100','200','300','400','500','600','700','800','900','1000'];
$userId = (int)$_SESSION['user_id'];

$successMessage = null;
$errorMessage   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $room  = $_POST['room'] ?? '';
    $note  = trim($_POST['note'] ?? '');
    $qtys  = $_POST['qty'] ?? [];

    $items = [];
    foreach ($qtys as $productId => $qty) {
        if ((int)$qty > 0) {
            $items[] = ['product_id' => (int)$productId, 'qty' => (int)$qty];
        }
    }

    if (empty($items)) {
        $errorMessage = 'Select at least one product first.';
    } else {
        try {
            createCustomerOrder($userId, $room, $note, $items);
            $successMessage = 'Your order has been placed and is now incoming!';
        } catch (\Throwable $e) {
            $errorMessage = 'Failed to place order: ' . $e->getMessage();
        }
    }
}

$page = (int) ($_GET['page'] ?? 1);

$data = getProducts($page , 6);
$products = $data['data'];
$totalPages = $data['totalPages'];

$insights = getCustomerInsights($userId);

$pageTitle = 'Cafetria System | Customer Menu';
$basePath = '..';
$pageKey = 'customer-menu';
$pageRole = 'customer';
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

    <?php if ($successMessage): ?>
      <div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-4 text-sm font-semibold text-emerald-700"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
      <div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-4 text-sm font-semibold text-red-700"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <form id="order-form" method="POST">
      <input type="hidden" name="create_order" value="1">
      <section class="grid gap-6 lg:grid-cols-[1.3fr,0.7fr] lg:items-start">

        <!-- Left: Menu -->
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
                  <select name="room" id="room-select" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    <?php foreach ($roomOptions as $room): ?>
                      <option value="<?= htmlspecialchars($room) ?>">Room <?= htmlspecialchars($room) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div>
                  <label for="global-note" class="mb-2 block text-sm font-semibold text-slate-700">Order note</label>
                  <input name="note" id="global-note" type="text" placeholder="Example: 1 Tea Extra Sugar"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
                </div>
              </div>
            </div>
            <div class="mt-4">
              <label for="product-search" class="mb-2 block text-sm font-semibold text-slate-700">Search products</label>
              <input id="product-search" type="search" placeholder="Search by product name or category"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
            </div>

            <div id="menu-grid" class="mt-6 grid gap-4 md:grid-cols-2">
              <?php if (empty($products)): ?>
                <div class="md:col-span-2 rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">No products available.</div>
              <?php else: ?>
                <?php foreach ($products as $product): ?>
                  <article class="card-hover rounded-[1.75rem] border border-slate-200 bg-white p-4"
                    data-product-id="<?= (int)$product['id'] ?>"
                    data-product-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                    data-product-price="<?= (float)$product['price'] ?>"
                    data-product-category="<?= htmlspecialchars($product['category'] ?? '', ENT_QUOTES) ?>">
                    <div class="flex gap-4">
                      <?php if ($product['image_path']): ?>
                        <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="h-24 w-24 rounded-[1.25rem] object-cover shadow-soft" />
                      <?php endif; ?>
                      <div class="flex-1">
                        <div class="flex items-start justify-between gap-3">
                          <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400"><?= htmlspecialchars($product['category'] ?? '') ?></p>
                            <h3 class="mt-1 text-xl font-bold text-slate-900"><?= htmlspecialchars($product['name']) ?></h3>
                          </div>
                          <div class="rounded-2xl bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700"><?= number_format($product['price'], 0) ?> LE</div>
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-4">
                          <div class="inline-flex items-center rounded-2xl border border-slate-200 bg-slate-50 p-1">
                            <button type="button" data-action="decrease" data-product-id="<?= (int)$product['id'] ?>" class="h-10 w-10 rounded-xl text-lg font-bold text-slate-700 transition hover:bg-white">−</button>
                            <span data-qty-display class="inline-flex min-w-10 justify-center px-2 text-sm font-semibold text-slate-900">0</span>
                            <button type="button" data-action="increase" data-product-id="<?= (int)$product['id'] ?>" class="h-10 w-10 rounded-xl text-lg font-bold text-slate-700 transition hover:bg-white">+</button>
                          </div>
                          <div class="text-right text-xs text-slate-500">Customize notes per item</div>
                        </div>
                      </div>
                    </div>
                    <div class="mt-4">
                      <label class="mb-2 block text-sm font-semibold text-slate-700">Item note</label>
                      <input data-note-input="true" data-product-id="<?= (int)$product['id'] ?>" type="text"
                        placeholder="Example: Extra sugar / no ice"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100" />
                    </div>
                  </article>
                <?php endforeach; ?>
                <div id="menu-grid-empty" style="display:none" class="md:col-span-2 rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">No products match your search.</div>
              <?php endif; ?>
            </div>
              <div class="mt-4 flex gap-2">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <a
                href="?page=<?php echo $i; ?>"
                class="px-3 py-1 rounded border <?php echo $i == $page ? 'bg-slate-900 text-white' : 'bg-white'; ?>">
                <?php echo $i; ?>
              </a>
            <?php endfor; ?>
          </div>
        </div>
      </div>

        <!-- Right: Cart + insights -->
        <aside class="space-y-6 lg:sticky lg:top-6">
          <div class="rounded-[2rem] border border-white/70 bg-slate-900 p-5 text-white shadow-soft md:p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-300">Current basket</p>
            <h2 class="mt-2 text-2xl font-bold">Order summary</h2>
            <div id="cart-items" class="mt-6 space-y-3">
              <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300">No items selected yet.</div>
            </div>
            <div class="mt-6 rounded-2xl bg-white/10 p-4">
              <div class="flex items-center justify-between text-sm text-slate-300"><span>Room</span><span id="summary-room">-</span></div>
              <div class="mt-3 flex items-center justify-between text-sm text-slate-300"><span>Items</span><span id="summary-items-count">0</span></div>
              <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-4 text-lg font-semibold text-white"><span>Total</span><span id="summary-total">0 LE</span></div>
            </div>
            <button id="place-order-btn" type="button" class="mt-6 w-full rounded-2xl bg-white px-4 py-3.5 text-sm font-semibold text-slate-900 transition hover:-translate-y-0.5 hover:bg-slate-100">Confirm order</button>
          </div>

          <div class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-pine-500">Quick insights</p>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
              <div class="rounded-3xl bg-emerald-50 p-4">
                <p class="text-sm text-emerald-700">Pending orders</p>
                <p class="mt-2 text-3xl font-bold text-emerald-900"><?= $insights['pending_count'] ?></p>
              </div>
              <div class="rounded-3xl bg-amber-50 p-4">
                <p class="text-sm text-amber-700">This month spend</p>
                <p class="mt-2 text-3xl font-bold text-amber-900"><?= number_format($insights['month_spend'], 0) ?> LE</p>
              </div>
            </div>
          </div>
          
        </aside>
      </section>
    </form>
  </div>
</main>
<?php
$pageScript = 'assets/js/components/customer-menu-cart.js';
require __DIR__ . '/../includes/page-end.php';
