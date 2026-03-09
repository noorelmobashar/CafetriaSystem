<?php
$pageTitle = 'Cafetria System | Login';
$basePath = '.';
$pageKey = 'login';
$pageRole = 'guest';
$pageScript = 'assets/js/pages/login.js';
require __DIR__ . '/includes/page-start.php';
?>
<main class="relative min-h-screen px-4 py-10 md:px-6 lg:px-8">
  <div class="mx-auto grid max-w-7xl items-center gap-8 lg:grid-cols-[1.1fr,0.9fr] lg:gap-12">
    <section class="space-y-8">
      <div class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/70 px-4 py-2 text-sm font-medium text-slate-600 shadow-soft backdrop-blur">
        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
        Smart Cafeteria Experience
      </div>

      <div class="space-y-5">
        <p class="text-sm font-semibold uppercase tracking-[0.35em] text-brand-600">Cafetria System</p>
        <h1 class="max-w-2xl text-4xl font-extrabold leading-tight text-slate-900 md:text-6xl">Fast ordering, clear tracking, and an admin flow built for busy offices.</h1>
        <p class="max-w-2xl text-lg leading-8 text-slate-600">A modern cafeteria frontend prepared for PHP pages, shared collaboration, and future backend integration.</p>
      </div>

      <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-soft backdrop-blur">
          <p class="text-sm text-slate-500">Multi-page ready</p>
          <p class="mt-2 text-2xl font-bold text-slate-900">PHP-friendly</p>
          <p class="mt-2 text-sm text-slate-500">Separate pages for customer and admin modules</p>
        </div>
        <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-soft backdrop-blur">
          <p class="text-sm text-slate-500">Team workflow</p>
          <p class="mt-2 text-2xl font-bold text-slate-900">Modular</p>
          <p class="mt-2 text-sm text-slate-500">Shared includes, components, and page scripts</p>
        </div>
        <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-soft backdrop-blur">
          <p class="text-sm text-slate-500">Experience</p>
          <p class="mt-2 text-2xl font-bold text-slate-900">Comfort-first</p>
          <p class="mt-2 text-sm text-slate-500">Responsive and easy on the eye</p>
        </div>
      </div>
    </section>

    <section class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-glow backdrop-blur md:p-8">
      <div class="mb-8 flex items-center justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-600">Welcome back</p>
          <h2 class="mt-2 text-3xl font-bold text-slate-900">Sign in</h2>
        </div>
        <div class="rounded-2xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-500">Demo Ready</div>
      </div>

      <form id="login-form" class="space-y-5">
        <div>
          <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
          <input id="email" type="email" required placeholder="employee@company.com" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-slate-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
        </div>
        <div>
          <div class="mb-2 flex items-center justify-between">
            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
            <button type="button" id="forgot-password" class="text-sm font-medium text-brand-600 transition hover:text-brand-700">Forget Password?</button>
          </div>
          <input id="password" type="password" required placeholder="••••••••" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-slate-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
        </div>
        <div>
          <p class="mb-3 text-sm font-semibold text-slate-700">Login as</p>
          <div class="grid grid-cols-2 gap-3">
            <button type="button" data-role="customer" class="role-card rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left transition">
              <span class="block text-sm font-semibold">Customer</span>
              <span class="mt-1 block text-xs text-slate-500">Employees placing orders</span>
            </button>
            <button type="button" data-role="admin" class="role-card rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left transition">
              <span class="block text-sm font-semibold">Admin</span>
              <span class="mt-1 block text-xs text-slate-500">Manage products, users, and checks</span>
            </button>
          </div>
        </div>
        <button type="submit" class="w-full rounded-2xl bg-slate-900 px-4 py-3.5 text-sm font-semibold text-white shadow-soft transition hover:-translate-y-0.5 hover:bg-slate-800">Access dashboard</button>
      </form>

      <div class="mt-6 rounded-2xl bg-slate-50 p-4">
        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Demo accounts</p>
        <div class="mt-3 grid gap-3 text-sm text-slate-600">
          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">Customer: <span class="font-semibold text-slate-900">employee@company.com</span></div>
          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">Admin: <span class="font-semibold text-slate-900">admin@company.com</span></div>
        </div>
      </div>
    </section>
  </div>
</main>
<?php require __DIR__ . '/includes/page-end.php'; ?>
