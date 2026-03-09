import { getCurrentUser, persistState, state } from './store.js';
import { renderLoginView } from './ui/auth.js';
import { renderCustomerView } from './ui/customer.js';
import { renderAdminView } from './ui/admin.js';
import { bindModalShell, showToast } from './ui/shared.js';

const app = document.getElementById('app');

function renderDashboardShell(user) {
  app.innerHTML = `
    <section class='min-h-screen px-4 py-6 md:px-6 lg:px-8'>
      <div class='mx-auto max-w-7xl space-y-6'>
        <header class='rounded-[2rem] border border-white/70 bg-white/80 p-4 shadow-soft backdrop-blur md:p-6'>
          <div class='flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between'>
            <div class='flex items-center gap-4'>
              <div class='flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-xl font-bold text-white'>C</div>
              <div>
                <p class='text-sm font-semibold uppercase tracking-[0.28em] text-brand-600'>${user.role === 'admin' ? 'Administrator' : 'Customer Portal'}</p>
                <h1 class='text-2xl font-bold text-slate-900 md:text-3xl'>${user.role === 'admin' ? 'Cafeteria control center' : 'Order drinks in seconds'}</h1>
                <p class='mt-1 text-sm text-slate-500'>${
                  user.role === 'admin'
                    ? 'Manage products, users, manual orders, and cafeteria checks from one place.'
                    : 'Customize your order, track delivery, and review your order history.'
                }</p>
              </div>
            </div>
            <div class='flex flex-col gap-3 sm:flex-row sm:items-center'>
              <div class='rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-600'>Logged in as <span class='font-semibold text-slate-900'>${user.name}</span></div>
              <button id='logout-btn' class='rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50'>Logout</button>
            </div>
          </div>
        </header>

        <div id='dashboard-view'></div>
      </div>
    </section>
  `;

  app.querySelector('#logout-btn').onclick = () => {
    state.session = null;
    persistState();
    renderApp();
    showToast('You have been logged out.', 'default');
  };

  bindModalShell();

  const viewRoot = app.querySelector('#dashboard-view');
  if (user.role === 'admin') {
    renderAdminView(viewRoot, renderApp);
    return;
  }

  renderCustomerView(viewRoot, renderApp);
}

export function renderApp() {
  if (!state.session) {
    renderLoginView(app, renderApp);
    return;
  }

  const user = getCurrentUser();
  if (!user) {
    state.session = null;
    persistState();
    renderLoginView(app, renderApp);
    return;
  }

  renderDashboardShell(user);
}

document.addEventListener('DOMContentLoaded', renderApp);
