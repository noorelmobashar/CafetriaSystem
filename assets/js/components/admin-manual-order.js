import { createManualOrder, getCartEntries, getCartTotal, getCurrentUser, getCustomerUsers, getRooms, persistState, state } from '../core/store.js';
import { currency } from '../core/utils.js';
import { showToast } from './shared.js';

export function initAdminManualOrderPage() {
  renderManualOrder();
  bindManualOrderActions();
}

function renderManualOrder() {
  const users = getCustomerUsers();
  const userSelect = document.getElementById('manual-user');
  const roomSelect = document.getElementById('manual-room');
  const noteInput = document.getElementById('manual-note');
  const productsGrid = document.getElementById('manual-products-grid');

  if (!state.manualCart.userId) {
    state.manualCart.userId = users[0]?.id || '';
  }

  const selectedUser = users.find((user) => user.id === state.manualCart.userId) || users[0];
  if (!state.manualCart.room) {
    state.manualCart.room = selectedUser?.roomNo || '';
  }

  userSelect.innerHTML = users
    .map((user) => `<option value='${user.id}' ${user.id === state.manualCart.userId ? 'selected' : ''}>${user.name}</option>`)
    .join('');

  roomSelect.innerHTML = [...new Set([selectedUser?.roomNo, ...getRooms()].filter(Boolean))]
    .map((room) => `<option value='${room}' ${room === state.manualCart.room ? 'selected' : ''}>${room}</option>`)
    .join('');

  noteInput.value = state.manualCart.note || '';

  productsGrid.innerHTML = state.data.products
    .map((product) => {
      const item = state.manualCart.items[product.id] || { qty: 0, note: '' };
      return `
        <article class='rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4'>
          <div class='flex gap-4'>
            <img src='${product.image}' alt='${product.name}' class='h-20 w-20 rounded-2xl object-cover' />
            <div class='flex-1'>
              <div class='flex items-start justify-between gap-3'>
                <div><p class='text-xs font-semibold uppercase tracking-[0.22em] text-slate-400'>${product.category}</p><h3 class='mt-1 text-lg font-bold text-slate-900'>${product.name}</h3></div>
                <div class='rounded-2xl bg-white px-3 py-2 text-sm font-semibold text-slate-700'>${currency(product.price)}</div>
              </div>
              <div class='mt-4 inline-flex items-center rounded-2xl border border-slate-200 bg-white p-1'>
                <button data-manual-action='decrease' data-product-id='${product.id}' class='h-10 w-10 rounded-xl text-lg font-bold text-slate-700 transition hover:bg-slate-50'>−</button>
                <span class='inline-flex min-w-10 justify-center px-2 text-sm font-semibold text-slate-900'>${item.qty}</span>
                <button data-manual-action='increase' data-product-id='${product.id}' class='h-10 w-10 rounded-xl text-lg font-bold text-slate-700 transition hover:bg-slate-50'>+</button>
              </div>
            </div>
          </div>
        </article>
      `;
    })
    .join('');

  const entries = getCartEntries(state.manualCart);
  document.getElementById('manual-cart-items').innerHTML = entries.length
    ? entries.map((item) => `
      <div class='flex items-center justify-between gap-3 rounded-2xl bg-white/10 p-4'>
        <div><p class='font-semibold'>${item.name}</p><p class='text-sm text-slate-300'>${item.qty} × ${currency(item.price)}</p></div>
        <div class='font-semibold'>${currency(item.qty * item.price)}</div>
      </div>
    `).join('')
    : "<div class='rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300'>No products selected.</div>";

  document.getElementById('manual-total').textContent = currency(getCartTotal(state.manualCart));
}

function bindManualOrderActions() {
  const users = getCustomerUsers();

  document.getElementById('manual-user').onchange = (event) => {
    state.manualCart.userId = event.target.value;
    const nextUser = users.find((user) => user.id === event.target.value);
    state.manualCart.room = nextUser?.roomNo || state.manualCart.room;
    persistState();
    renderManualOrder();
    bindManualOrderActions();
  };

  document.getElementById('manual-room').onchange = (event) => {
    state.manualCart.room = event.target.value;
    persistState();
  };

  document.getElementById('manual-note').oninput = (event) => {
    state.manualCart.note = event.target.value;
    persistState();
  };

  document.getElementById('manual-products-grid').onclick = (event) => {
    const button = event.target.closest('[data-manual-action]');
    if (!button) return;
    const productId = button.dataset.productId;
    const item = state.manualCart.items[productId] || { qty: 0, note: '' };
    if (button.dataset.manualAction === 'increase') item.qty += 1;
    if (button.dataset.manualAction === 'decrease') item.qty = Math.max(0, item.qty - 1);
    state.manualCart.items[productId] = item;
    persistState();
    renderManualOrder();
    bindManualOrderActions();
  };

  document.getElementById('assign-manual-order-btn').onclick = () => {
    const selectedUser = users.find((user) => user.id === state.manualCart.userId);
    if (!selectedUser) {
      showToast('Please select a user.', 'warning');
      return;
    }
    if (!getCartEntries(state.manualCart).length) {
      showToast('Select at least one product first.', 'warning');
      return;
    }
    createManualOrder(getCurrentUser(), selectedUser);
    renderManualOrder();
    bindManualOrderActions();
    showToast('Manual order assigned to user account.', 'success');
  };
}
