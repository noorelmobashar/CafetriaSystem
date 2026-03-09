<?php
$headerBadge = $headerBadge ?? 'Workspace';
$headerTitle = $headerTitle ?? 'Dashboard';
$headerSubtitle = $headerSubtitle ?? '';
?>
<header class="rounded-[2rem] border border-white/70 bg-white/80 p-4 shadow-soft backdrop-blur md:p-6">
  <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
    <div class="flex items-center gap-4">
      <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-xl font-bold text-white">C</div>
      <div>
        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-brand-600"><?php echo htmlspecialchars($headerBadge, ENT_QUOTES, 'UTF-8'); ?></p>
        <h1 class="text-2xl font-bold text-slate-900 md:text-3xl"><?php echo htmlspecialchars($headerTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="mt-1 text-sm text-slate-500"><?php echo htmlspecialchars($headerSubtitle, ENT_QUOTES, 'UTF-8'); ?></p>
      </div>
    </div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
      <div class="rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-600">Logged in as <span id="shell-user-name" class="font-semibold text-slate-900">...</span></div>
      <button id="logout-btn" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Logout</button>
    </div>
  </div>
</header>
