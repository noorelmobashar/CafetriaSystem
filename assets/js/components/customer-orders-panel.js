import { cancelOrder, customerStatus, getCurrentUser, persistState, state } from '../core/store.js';
import { currency, formatDateInput, formatDateTime } from '../core/utils.js';
import { showToast } from './shared.js';

export function initCustomerOrdersPage() {
  const user = getCurrentUser();
  if (!user) return;
  renderOrders(user);
  bindActions(user);
}

function getFilteredOrders(user) {
  return state.data.orders
    .filter((order) => order.userId === user.id)
    .filter((order) => {
      const day = formatDateInput(order.createdAt);
      if (state.filters.customerFrom && day < state.filters.customerFrom) return false;
      if (state.filters.customerTo && day > state.filters.customerTo) return false;
      return true;
    })
    .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
}

function renderOrders(user) {
  const container = document.getElementById('customer-orders');
  document.getElementById('customer-date-from').value = state.filters.customerFrom;
  document.getElementById('customer-date-to').value = state.filters.customerTo;

  const orders = getFilteredOrders(user);
  container.innerHTML = orders.length
    ? orders.map((order) => {
      const status = customerStatus(order);
      const canCancel = order.status === 'incoming' || order.status === 'processing';
      return `
        <article class='rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5'>
          <div class='flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between'>
            <div class='space-y-3'>
              <div class='flex flex-wrap items-center gap-3'>
                <h3 class='text-lg font-bold text-slate-900'>Order #${order.id.slice(-6).toUpperCase()}</h3>
                <span class='status-pill ${status.className}'>${status.label}</span>
              </div>
              <p class='text-sm text-slate-500'>${formatDateTime(order.createdAt)} • ${order.room}</p>
              <div class='flex flex-wrap gap-2'>
                ${order.items.map((item) => `<span class='rounded-full bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm'>${item.qty} × ${item.name}</span>`).join('')}
              </div>
              ${order.note ? `<p class='text-sm text-slate-600'><span class='font-semibold text-slate-900'>Note:</span> ${order.note}</p>` : ''}
            </div>
            <div class='flex min-w-[12rem] flex-col gap-3 lg:items-end'>
              <p class='text-2xl font-bold text-slate-900'>${currency(order.total)}</p>
              ${canCancel ? `<button data-cancel-order-id='${order.id}' class='rounded-2xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-rose-700'>Cancel order</button>` : ''}
            </div>
          </div>
        </article>
      `;
    }).join('')
    : "<div class='rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500'>No orders found for the selected date range.</div>";
}

function bindActions(user) {
  document.getElementById('customer-date-from').onchange = (event) => {
    state.filters.customerFrom = event.target.value;
    persistState();
    renderOrders(user);
    bindActions(user);
  };

  document.getElementById('customer-date-to').onchange = (event) => {
    state.filters.customerTo = event.target.value;
    persistState();
    renderOrders(user);
    bindActions(user);
  };

  document.getElementById('clear-order-filters').onclick = () => {
    state.filters.customerFrom = '';
    state.filters.customerTo = '';
    persistState();
    renderOrders(user);
    bindActions(user);
  };

  document.getElementById('customer-orders').onclick = (event) => {
    const button = event.target.closest('[data-cancel-order-id]');
    if (!button) return;
    const order = cancelOrder(button.dataset.cancelOrderId);
    if (!order) return;
    showToast('Order canceled successfully.', 'success');
    renderOrders(user);
    bindActions(user);
  };
}
