<?php
$pageTitle = 'Cafetria System | Users';
$basePath = '..';
$pageKey = 'admin-users';
$pageRole = 'admin';
$pageScript = 'assets/js/pages/admin-users.js';
$currentPage = 'users';
$headerBadge = 'Administrator';
$headerTitle = 'Manage employees';
$headerSubtitle = 'Maintain user profiles, rooms, extensions, and credentials in one place.';
require __DIR__ . '/../includes/page-start.php';
?>
<main class="relative min-h-screen px-4 py-6 md:px-6 lg:px-8">
  <div class="mx-auto max-w-7xl space-y-6">
    <?php require __DIR__ . '/../includes/dashboard-header.php'; ?>
    <?php require __DIR__ . '/../includes/admin-nav.php'; ?>

    <section class="rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><p class="text-sm font-semibold uppercase tracking-[0.25em] text-brand-600">Employee base</p><h2 class="mt-2 text-2xl font-bold text-slate-900">Users</h2></div>
        <button id="add-user-btn" class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Add user</button>
      </div>
      <div id="users-table" class="mt-6 overflow-hidden rounded-[1.5rem] border border-slate-200"></div>
    </section>
  </div>
</main>
<?php require __DIR__ . '/../includes/page-end.php'; ?>
