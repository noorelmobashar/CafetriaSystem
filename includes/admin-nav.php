<?php $currentPage = $currentPage ?? 'dashboard'; ?>
<nav class="rounded-[2rem] border border-white/70 bg-white/85 p-3 shadow-soft backdrop-blur">
  <div class="flex flex-wrap gap-2">
    <a href="index.php" class="rounded-2xl px-4 py-3 text-sm font-semibold transition <?php echo is_active_page($currentPage, 'dashboard'); ?>">Home</a>
    <a href="products.php" class="rounded-2xl px-4 py-3 text-sm font-semibold transition <?php echo is_active_page($currentPage, 'products'); ?>">Products</a>
    <a href="users.php" class="rounded-2xl px-4 py-3 text-sm font-semibold transition <?php echo is_active_page($currentPage, 'users'); ?>">Users</a>
    <a href="manual-order.php" class="rounded-2xl px-4 py-3 text-sm font-semibold transition <?php echo is_active_page($currentPage, 'manual-order'); ?>">Manual Order</a>
    <a href="checks.php" class="rounded-2xl px-4 py-3 text-sm font-semibold transition <?php echo is_active_page($currentPage, 'checks'); ?>">Checks</a>
  </div>
</nav>
