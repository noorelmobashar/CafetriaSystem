<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Cafetria System | Products';
$basePath = '..';
$pageKey = 'admin-products';
$pageRole = 'admin';
$currentPage = 'products';
$headerBadge = 'Administrator';
$headerTitle = 'Manage products';
$headerSubtitle = 'Keep the cafeteria catalog clean, visual, and easy to update.';
require __DIR__ . '/../includes/page-start.php';
require_once __DIR__ . '/../controllers/Product.php';

$productController = new ProductController();

$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

?>
<main class="relative min-h-screen px-4 py-6 md:px-6 lg:px-8">
  <div class="mx-auto max-w-7xl space-y-6">
    <?php require __DIR__ . '/../includes/dashboard-header.php'; ?>
    <?php require __DIR__ . '/../includes/admin-nav.php'; ?>

    <?php if ($successMessage): ?>
      <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
      <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <section class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><p class="text-sm font-semibold uppercase tracking-[0.25em] text-cafe-500">Inventory</p><h2 class="mt-2 text-2xl font-bold text-slate-900">Products</h2></div>
        <a href="add-product.php" class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Add product</a>
      </div>

      <?php
      // TODO 1: Load your products before this section.
      // Example idea:
      // - require your product controller file
      // - call a function like getAllProducts()
      // - store result in $products as an array
      //
      // TODO 2: Ensure each product has what this table needs:
      // id, name, category (or category name), price, available, image_path.

      $products = $productController->index();

      ?>

      <div class="mt-6 overflow-hidden rounded-[1.5rem] border border-slate-200">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
              <tr>
                <th class="px-4 py-3 font-semibold">Product</th>
                <th class="px-4 py-3 font-semibold">Category</th>
                <th class="px-4 py-3 font-semibold">Price</th>
                <th class="px-4 py-3 font-semibold">Available</th>
                <th class="px-4 py-3 font-semibold">Image</th>
                <th class="px-4 py-3 font-semibold">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
              <?php
              if(!empty($products)) {
                foreach ($products as $product):
              ?>
              <tr>
                <td class="px-4 py-3"><?php echo htmlspecialchars($product['name']); ?></td>
                <td class="px-4 py-3"><?php echo htmlspecialchars($product['category']); ?></td>
                <td class="px-4 py-3"><?php echo htmlspecialchars($product['price']); ?> LE</td>
                <td class="px-4 py-3"><?php echo $product['available'] ? 'Yes' : 'No'; ?></td>
                <td class="px-4 py-3"><?php echo $product['image_path'] ? '<img src="' . $product['image_path'] . '" alt="' . $product['name'] . '">' : 'No image'; ?></td>
                <td class="px-4 py-3">
                  <div class="flex flex-wrap gap-2">
                    <a
                      href="edit-product.php?id=<?php echo (int)$product['id']; ?>"
                      class="inline-flex items-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-800"
                    >
                      Edit
                    </a>
                    <form method="POST" action="delete-product.php" class="js-delete-form">
                      <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                      <button
                        type="button"
                        data-open-delete-modal="1"
                        data-product-name="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                        class="inline-flex items-center rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100"
                      >
                        Delete
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
             <?php endforeach; ?>
             <?php } else { ?>
              <tr>
                <td class="px-4 py-3" colspan="7">No products found</td>
              </tr>
             <?php } ?>
             
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</main>

<div id="delete-modal-overlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
  <div class="w-full max-w-sm rounded-2xl bg-white p-5">
    <h3 class="text-lg font-bold text-slate-900">Delete product?</h3>
    <p id="delete-modal-message" class="mt-2 text-sm text-slate-600">Are you sure?</p>
    <div class="mt-5 flex justify-end gap-2">
      <button id="delete-modal-cancel" type="button" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700">Cancel</button>
      <button id="delete-modal-confirm" type="button" class="rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white">Delete</button>
    </div>
  </div>
</div>

<script>
  (function () {
    const overlay = document.getElementById('delete-modal-overlay');
    const cancelButton = document.getElementById('delete-modal-cancel');
    const confirmButton = document.getElementById('delete-modal-confirm');
    const message = document.getElementById('delete-modal-message');
    let pendingForm = null;

    function openModal() { overlay.classList.remove('hidden'); overlay.classList.add('flex'); }
    function closeModal() { overlay.classList.add('hidden'); overlay.classList.remove('flex'); pendingForm = null; }

    document.querySelectorAll('[data-open-delete-modal]').forEach((button) => {
      button.addEventListener('click', function () {
        pendingForm = this.closest('form');
        const productName = this.getAttribute('data-product-name') || 'this product';
        message.textContent = 'Delete "' + productName + '"?';
        openModal();
      });
    });

    cancelButton.addEventListener('click', closeModal);
    overlay.addEventListener('click', function (event) {
      if (event.target === overlay) {
        closeModal();
      }
    });

    confirmButton.addEventListener('click', function () {
      if (pendingForm) {
        pendingForm.submit();
      }
    });
  })();
</script>
<?php require __DIR__ . '/../includes/page-end.php'; ?>
