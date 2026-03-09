import { createCustomerOrder, getCartEntries, getCartTotal, getCurrentUser, getRooms, persistState, state } from '../core/store.js';
import { currency } from '../core/utils.js';
import { showToast } from './shared.js';

export function initCustomerMenuPage() {
  const user = getCurrentUser();
  if (!user) return;

  renderMenu();
  renderSummary(user);
  bindActions(user);
}

function renderMenu() {
  const roomSelect = document.getElementById('room-select');
  const globalNote = document.getElementById('global-note');
  const menuGrid = document.getElementById('menu-grid');

  if (!state.customerCart.room) {
    state.customerCart.room = getCurrentUser()?.roomNo || getRooms()[0] || '';
  }

  roomSelect.innerHTML = getRooms()
    .map((room) => `<option value='${room}' ${room === state.customerCart.room ? 'selected' : ''}>${room}</option>`)
    .join('');

  globalNote.value = state.customerCart.note || '';

  menuGrid.innerHTML = state.data.products
    .map((product) => {
      const item = state.customerCart.items[product.id] || { qty: 0, note: '' };
      return `
        <article class='card-hover rounded-[1.75rem] border border-slate-200 bg-white p-4'>
          <div class='flex gap-4'>
            <img src='${product.image}' alt='${product.name}' class='h-24 w-24 rounded-[1.25rem] object-cover shadow-soft' />
            <div class='flex-1'>
              <div class='flex items-start justify-between gap-3'>
                <div>
                  <p class='text-xs font-semibold uppercase tracking-[0.22em] text-slate-400'>${product.category}</p>
                  <h3 class='mt-1 text-xl font-bold text-slate-900'>${product.name}</h3>
                </div>
                <div class='rounded-2xl bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700'>${currency(product.price)}</div>
              </div>
              <div class='mt-4 flex items-center justify-between gap-4'>
                <div class='inline-flex items-center rounded-2xl border border-slate-200 bg-slate-50 p-1'>
                  <button data-action='decrease' data-product-id='${product.id}' class='h-10 w-10 rounded-xl text-lg font-bold text-slate-700 transition hover:bg-white'>−</button>
                  <span class='inline-flex min-w-10 justify-center px-2 text-sm font-semibold text-slate-900'>${item.qty}</span>
                  <button data-action='increase' data-product-id='${product.id}' class='h-10 w-10 rounded-xl text-lg font-bold text-slate-700 transition hover:bg-white'>+</button>
                </div>
                <div class='text-right text-xs text-slate-500'>Customize notes per item</div>
              </div>
            </div>
          </div>
          <div class='mt-4'>
            <label class='mb-2 block text-sm font-semibold text-slate-700'>Item note</label>
            <input data-note-input='true' data-product-id='${product.id}' type='text' value='${item.note || ''}' placeholder='Example: Extra sugar / no ice' class='w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100' />
          </div>
        </article>
      `;
    })
    .join('');
}

function renderSummary(user) {
  const cartItems = document.getElementById('cart-items');
  const entries = getCartEntries(state.customerCart);
  const total = getCartTotal(state.customerCart);
  const myOrders = state.data.orders.filter((order) => order.userId === user.id && order.status !== 'canceled');
  const currentMonth = new Date().getMonth();
  const currentYear = new Date().getFullYear();

  cartItems.innerHTML = entries.length
    ? entries.map((item) => `
      <div class='rounded-2xl bg-white/10 p-4'>
        <div class='flex items-center justify-between gap-3'>
          <div>
            <p class='font-semibold text-white'>${item.name}</p>
            <p class='mt-1 text-sm text-slate-300'>${item.qty} × ${currency(item.price)}</p>
            ${item.note ? `<p class='mt-2 text-xs text-slate-300'>${item.note}</p>` : ''}
          </div>
          <div class='text-sm font-semibold text-white'>${currency(item.qty * item.price)}</div>
        </div>
      </div>
    `).join('')
    : "<div class='rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300'>No items selected yet.</div>";

  document.getElementById('summary-room').textContent = state.customerCart.room || '-';
  document.getElementById('summary-items-count').textContent = String(entries.reduce((sum, item) => sum + item.qty, 0));
  document.getElementById('summary-total').textContent = currency(total);
  document.getElementById('customer-pending-count').textContent = String(
    myOrders.filter((order) => order.status === 'incoming' || order.status === 'processing').length
  );
  document.getElementById('customer-spend').textContent = currency(
    myOrders
      .filter((order) => {
        const date = new Date(order.createdAt);
        return date.getMonth() === currentMonth && date.getFullYear() === currentYear;
      })
      .reduce((sum, order) => sum + order.total, 0)
  );
}

function bindActions(user) {
  document.getElementById('room-select').onchange = (event) => {
    state.customerCart.room = event.target.value;
    persistState();
    renderSummary(user);
  };

  document.getElementById('global-note').oninput = (event) => {
    state.customerCart.note = event.target.value;
    persistState();
  };

  document.getElementById('menu-grid').onclick = (event) => {
    const button = event.target.closest('[data-action]');
    if (!button) return;
    const productId = button.dataset.productId;
    const item = state.customerCart.items[productId] || { qty: 0, note: '' };
    if (button.dataset.action === 'increase') item.qty += 1;
    if (button.dataset.action === 'decrease') item.qty = Math.max(0, item.qty - 1);
    state.customerCart.items[productId] = item;
    persistState();
    renderMenu();
    renderSummary(user);
    bindActions(user);
  };

  document.getElementById('menu-grid').oninput = (event) => {
    const input = event.target.closest('[data-note-input]');
    if (!input) return;
    const item = state.customerCart.items[input.dataset.productId] || { qty: 0, note: '' };
    item.note = input.value;
    state.customerCart.items[input.dataset.productId] = item;
    persistState();
  };

  document.getElementById('place-order-btn').onclick = () => {
    if (!getCartEntries(state.customerCart).length) {
      showToast('Add at least one product before confirming the order.', 'warning');
      return;
    }

    createCustomerOrder(user);
    showToast('Your order has been added to the incoming queue.', 'success');
    renderMenu();
    renderSummary(user);
    bindActions(user);
  };
}
