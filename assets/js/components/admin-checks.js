import { getCustomerUsers, persistState, state } from '../core/store.js';
import { currency, formatDateInput, formatDateTime, statusMeta } from '../core/utils.js';
import { tableShell } from './shared.js';

export function initAdminChecksPage() {
  renderChecks();
  bindChecksActions();
}

function getFilteredOrders() {
  return state.data.orders
    .filter((order) => order.status !== 'canceled')
    .filter((order) => (state.filters.checksUser === 'all' ? true : order.userId === state.filters.checksUser))
    .filter((order) => {
      const day = formatDateInput(order.createdAt);
      if (state.filters.checksFrom && day < state.filters.checksFrom) return false;
      if (state.filters.checksTo && day > state.filters.checksTo) return false;
      return true;
    })
    .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
}

function renderChecks() {
  const userSelect = document.getElementById('checks-user');
  const fromInput = document.getElementById('checks-from');
  const toInput = document.getElementById('checks-to');

  userSelect.innerHTML = [`<option value='all'>All users</option>`]
    .concat(getCustomerUsers().map((user) => `<option value='${user.id}' ${state.filters.checksUser === user.id ? 'selected' : ''}>${user.name}</option>`))
    .join('');

  fromInput.value = state.filters.checksFrom;
  toInput.value = state.filters.checksTo;

  const orders = getFilteredOrders();
  const totalAmount = orders.reduce((sum, order) => sum + order.total, 0);
  document.getElementById('checks-total').textContent = currency(totalAmount);
  document.getElementById('checks-orders-count').textContent = String(orders.length);
  document.getElementById('checks-average').textContent = currency(orders.length ? totalAmount / orders.length : 0);

  const head = `<tr><th>User</th><th>Date</th><th>Status</th><th>Source</th><th>Total</th></tr>`;
  const body = orders.length
    ? orders.map((order) => {
      const status = statusMeta[order.status];
      return `
        <tr>
          <td><div><p class='font-semibold text-slate-900'>${order.userName}</p><p class='text-sm text-slate-500'>${order.room}</p></div></td>
          <td>${formatDateTime(order.createdAt)}</td>
          <td><span class='status-pill ${status.className}'>${status.label}</span></td>
          <td>${order.source === 'manual' ? 'Manual Order' : 'Customer Portal'}</td>
          <td>${currency(order.total)}</td>
        </tr>
      `;
    }).join('')
    : "<tr><td colspan='5'><div class='p-8 text-center text-sm text-slate-500'>No matching checks found.</div></td></tr>";

  document.getElementById('checks-table').innerHTML = tableShell(head, body);
}

function bindChecksActions() {
  document.getElementById('checks-user').onchange = (event) => {
    state.filters.checksUser = event.target.value;
    persistState();
    renderChecks();
    bindChecksActions();
  };

  document.getElementById('checks-from').onchange = (event) => {
    state.filters.checksFrom = event.target.value;
    persistState();
    renderChecks();
    bindChecksActions();
  };

  document.getElementById('checks-to').onchange = (event) => {
    state.filters.checksTo = event.target.value;
    persistState();
    renderChecks();
    bindChecksActions();
  };

  document.getElementById('reset-checks-btn').onclick = () => {
    state.filters.checksUser = 'all';
    state.filters.checksFrom = '';
    state.filters.checksTo = '';
    persistState();
    renderChecks();
    bindChecksActions();
  };
}
