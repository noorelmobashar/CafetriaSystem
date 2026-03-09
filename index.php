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
        Workplace Cafeteria Service
      </div>

      <div class="space-y-5">
        <p class="text-sm font-semibold uppercase tracking-[0.35em] text-brand-600">Cafetria System</p>
        <h1 class="max-w-2xl text-4xl font-extrabold leading-tight text-slate-900 md:text-6xl">Order management, delivery tracking, and cafeteria operations in one secure workspace.</h1>
        <p class="max-w-2xl text-lg leading-8 text-slate-600">Employees can place and track orders with full visibility, while cafeteria administrators manage products, users, fulfillment, and financial checks through dedicated operational screens.</p>
      </div>

      <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-soft backdrop-blur">
          <p class="text-sm text-slate-500">Order visibility</p>
          <p class="mt-2 text-2xl font-bold text-slate-900">Live status</p>
          <p class="mt-2 text-sm text-slate-500">Track every order from submission to completion</p>
        </div>
        <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-soft backdrop-blur">
          <p class="text-sm text-slate-500">Operational control</p>
          <p class="mt-2 text-2xl font-bold text-slate-900">Admin workflow</p>
          <p class="mt-2 text-sm text-slate-500">Manage products, users, manual orders, and checks</p>
        </div>
        <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-soft backdrop-blur">
          <p class="text-sm text-slate-500">Authorized access</p>
          <p class="mt-2 text-2xl font-bold text-slate-900">Role-based</p>
          <p class="mt-2 text-sm text-slate-500">Separate access for employees and cafeteria administrators</p>
        </div>
      </div>
    </section>

    <section class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-glow backdrop-blur md:p-8">
      <div class="mb-8 flex items-center justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand-600">Welcome back</p>
          <h2 class="mt-2 text-3xl font-bold text-slate-900">Sign in</h2>
        </div>
        <div class="rounded-2xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-500">Authorized Access</div>
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

      <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
        Access is restricted to registered employees and cafeteria administrators. If you do not have valid credentials, please contact the cafeteria administration desk.
      </div>

    </section>
  </div>
</main>
<?php require __DIR__ . '/includes/page-end.php'; ?>
