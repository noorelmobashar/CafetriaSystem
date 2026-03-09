<?php $currentPage = $currentPage ?? 'menu'; ?>
<nav class="rounded-[2rem] border border-white/70 bg-white/85 p-3 shadow-soft backdrop-blur">
  <div class="flex flex-wrap gap-2">
    <a href="menu.php" class="rounded-2xl px-4 py-3 text-sm font-semibold transition <?php echo is_active_page($currentPage, 'menu'); ?>">Menu</a>
    <a href="orders.php" class="rounded-2xl px-4 py-3 text-sm font-semibold transition <?php echo is_active_page($currentPage, 'orders'); ?>">My Orders</a>
  </div>
</nav>
