<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../controllers/Product.php';
$db = db();
$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = null;
unset($_SESSION['success_message']);

$form = [
    'name' => '',
    'price' => '',
    'category_id' => '',
    'available' => '1',
    'image_path' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_product'])) {
    $form['name'] = trim((string)($_POST['name'] ?? ''));
    $form['price'] = trim((string)($_POST['price'] ?? ''));
    $form['category_id'] = trim((string)($_POST['category_id'] ?? ''));
    $form['available'] = isset($_POST['available']) ? '1' : '0';
    $form['image_path'] = trim((string)($_POST['image_path'] ?? ''));

    if ($form['name'] === '') {
        $errorMessage = 'Product name is required.';
    } elseif ($form['price'] === '' || !is_numeric($form['price']) || (float)$form['price'] < 0) {
        $errorMessage = 'Price must be a valid non-negative number.';
    } else {
        try {
            $categoryId = $form['category_id'] === '' ? null : (int)$form['category_id'];
            $imagePath = $form['image_path'] === '' ? null : $form['image_path'];
            createProduct($form['name'], (float)$form['price'], $categoryId, $imagePath, (bool)$form['available']);
            $_SESSION['success_message'] = 'Product added successfully.';
            header('Location: add-product.php');
            exit;
        } catch (Throwable $exception) {
            $errorMessage = 'Failed to save product. Please try again.';
        }
    }
}

$categories = $db->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Cafetria System | Add Product';
$basePath = '..';
$pageKey = 'admin-add-product';
$pageRole = 'admin';
$currentPage = 'products';
$headerBadge = 'Administrator';
$headerTitle = 'Add product';
$headerSubtitle = 'Create a new item in the cafeteria catalog.';
require __DIR__ . '/../includes/page-start.php';
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
      <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cafe-500">Inventory</p>
          <h2 class="mt-2 text-2xl font-bold text-slate-900">Add Product</h2>
        </div>
        <a href="products.php" class="rounded-2xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">Back to products</a>
      </div>

      <form method="POST" class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
          <label for="product-name" class="mb-2 block text-sm font-semibold text-slate-700">Product name</label>
          <input id="product-name" name="name" type="text" value="<?= htmlspecialchars($form['name']) ?>" placeholder="Latte" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
        </div>

        <div>
          <label for="product-price" class="mb-2 block text-sm font-semibold text-slate-700">Price (LE)</label>
          <input id="product-price" name="price" type="number" min="0" step="0.01" value="<?= htmlspecialchars($form['price']) ?>" placeholder="25" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
        </div>

        <div>
          <label for="product-category" class="mb-2 block text-sm font-semibold text-slate-700">Category</label>
          <select id="product-category" name="category_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
            <option value="">No category</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= (int)$category['id'] ?>" <?= $form['category_id'] === (string)$category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="md:col-span-2">
          <label for="product-image" class="mb-2 block text-sm font-semibold text-slate-700">Image path (optional)</label>
          <input id="product-image" name="image_path" type="text" value="<?= htmlspecialchars($form['image_path']) ?>" placeholder="assets/img/products/latte.jpg" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
        </div>

        <div class="md:col-span-2">
          <label class="inline-flex items-center gap-3 text-sm font-semibold text-slate-700">
            <input name="available" type="checkbox" value="1" <?= $form['available'] === '1' ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-brand-500" />
            Available for ordering
          </label>
        </div>

        <div class="md:col-span-2">
          <button type="submit" name="create_product" value="1" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Save Product</button>
        </div>
      </form>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/page-end.php'; ?>
