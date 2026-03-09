<?php $pageScript = $pageScript ?? null; ?>
  <div id="toast-container" class="pointer-events-none fixed bottom-4 right-4 z-50 space-y-3"></div>

  <div id="modal-overlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-950/40 p-4 backdrop-blur-sm">
    <div class="w-full max-w-2xl rounded-[2rem] border border-white/70 bg-white p-6 shadow-soft md:p-8">
      <div class="mb-6 flex items-start justify-between gap-4">
        <div>
          <p id="modal-kicker" class="text-sm font-semibold uppercase tracking-[0.25em] text-brand-600"></p>
          <h3 id="modal-title" class="mt-2 text-2xl font-bold text-slate-900"></h3>
        </div>
        <button id="close-modal-btn" class="rounded-2xl bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-200">Close</button>
      </div>
      <form id="modal-form" class="space-y-4"></form>
    </div>
  </div>

<?php if ($pageScript): ?>
  <script type="module" src="<?php echo htmlspecialchars(asset_url($basePath, $pageScript), ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php endif; ?>
</body>
</html>
