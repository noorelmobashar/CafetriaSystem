import {
  customerStatus,
  getCartEntries,
  getCartTotal,
  getCurrentUser,
  getRooms,
  persistState,
  resetCustomerCart,
  state,
} from '../store.js';
import { currency, formatDateInput, formatDateTime } from '../utils.js';
import { showToast } from './shared.js';

export function renderCustomerView(root, rerender) {
  const user = getCurrentUser();
  root.innerHTML = `
    <section class='grid gap-6 xl:grid-cols-[1.3fr,0.7fr]'>
      <div class='space-y-6'>
        <div class='rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6'>
          <div class='flex flex-col gap-3 md:flex-row md:items-end md:justify-between'>
            <div>
              <p class='text-sm font-semibold uppercase tracking-[0.25em] text-cafe-500'>Order your favorites</p>
              <h2 class='mt-2 text-2xl font-bold text-slate-900'>Menu & customization</h2>
            </div>
            <div class='grid gap-3 sm:grid-cols-2'>
              <div>
                <label for='room-select' class='mb-2 block text-sm font-semibold text-slate-700'>Current room</label>
                <select id='room-select' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100'></select>
              </div>
              <div>
                <label for='global-note' class='mb-2 block text-sm font-semibold text-slate-700'>Order note</label>
                <input id='global-note' type='text' placeholder='Example: 1 Tea Extra Sugar' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100' />
              </div>
            </div>
          </div>

          <div id='menu-grid' class='mt-6 grid gap-4 md:grid-cols-2'></div>
        </div>

        <div class='rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6'>
          <div class='flex items-center justify-between'>
            <div>
              <p class='text-sm font-semibold uppercase tracking-[0.25em] text-brand-600'>History</p>
              <h2 class='mt-2 text-2xl font-bold text-slate-900'>My Orders</h2>
            </div>
            <div class='rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-600'>Real-time order states</div>
          </div>
          <div class='mt-5 grid gap-3 md:grid-cols-[1fr,1fr,auto]'>
            <div>
              <label for='customer-date-from' class='mb-2 block text-sm font-semibold text-slate-700'>From</label>
              <input id='customer-date-from' type='date' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100' />
            </div>
            <div>
              <label for='customer-date-to' class='mb-2 block text-sm font-semibold text-slate-700'>To</label>
              <input id='customer-date-to' type='date' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100' />
            </div>
            <div class='flex items-end'>
              <button id='clear-order-filters' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50'>Clear filters</button>
            </div>
          </div>
          <div id='customer-orders' class='mt-6 space-y-4'></div>
        </div>
      </div>

      <aside class='space-y-6'>
        <div class='rounded-[2rem] border border-white/70 bg-slate-900 p-5 text-white shadow-soft md:p-6'>
          <p class='text-sm font-semibold uppercase tracking-[0.28em] text-slate-300'>Current basket</p>
          <h2 class='mt-2 text-2xl font-bold'>Order summary</h2>
          <div id='cart-items' class='mt-6 space-y-3'></div>
          <div class='mt-6 rounded-2xl bg-white/10 p-4'>
            <div class='flex items-center justify-between text-sm text-slate-300'>
              <span>Room</span>
              <span id='summary-room'>-</span>
            </div>
            <div class='mt-3 flex items-center justify-between text-sm text-slate-300'>
              <span>Items</span>
              <span id='summary-items-count'>0</span>
            </div>
            <div class='mt-4 flex items-center justify-between border-t border-white/10 pt-4 text-lg font-semibold text-white'>
              <span>Total</span>
              <span id='summary-total'>0 LE</span>
            </div>
          </div>
          <button id='place-order-btn' class='mt-6 w-full rounded-2xl bg-white px-4 py-3.5 text-sm font-semibold text-slate-900 transition hover:-translate-y-0.5 hover:bg-slate-100'>
            Confirm order
          </button>
        </div>

        <div class='rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6'>
          <p class='text-sm font-semibold uppercase tracking-[0.25em] text-pine-500'>Quick insights</p>
          <div class='mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-1'>
            <div class='rounded-3xl bg-emerald-50 p-4'>
              <p class='text-sm text-emerald-700'>Pending orders</p>
              <p id='customer-pending-count' class='mt-2 text-3xl font-bold text-emerald-900'>0</p>
            </div>
            <div class='rounded-3xl bg-amber-50 p-4'>
              <p class='text-sm text-amber-700'>This month spend</p>
              <p id='customer-spend' class='mt-2 text-3xl font-bold text-amber-900'>0 LE</p>
            </div>
          </div>
        </div>
      </aside>
    </section>
  `;

  renderMenu(root);
  renderSummary(root, user);
  renderOrders(root, user);
  bindActions(root, user, rerender);
}

function renderMenu(root) {
  const roomSelect = root.querySelector('#room-select');
  const globalNote = root.querySelector('#global-note');
  const menuGrid = root.querySelector('#menu-grid');

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

function renderSummary(root, user) {
  const cartItems = root.querySelector('#cart-items');
  const entries = getCartEntries(state.customerCart);
  const total = getCartTotal(state.customerCart);

  cartItems.innerHTML = entries.length
    ? entries
        .map(
          (item) => `
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
          `
        )
        .join('')
    : "<div class='rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300'>No items selected yet.</div>";

  root.querySelector('#summary-room').textContent = state.customerCart.room || '-';
  root.querySelector('#summary-items-count').textContent = String(entries.reduce((sum, item) => sum + item.qty, 0));
  root.querySelector('#summary-total').textContent = currency(total);

  const currentMonth = new Date().getMonth();
  const currentYear = new Date().getFullYear();
  const myOrders = state.data.orders.filter((order) => order.userId === user.id && order.status !== 'canceled');
  const pending = myOrders.filter((order) => order.status === 'incoming' || order.status === 'processing').length;
  const monthSpend = myOrders
    .filter((order) => {
      const date = new Date(order.createdAt);
      return date.getMonth() === currentMonth && date.getFullYear() === currentYear;
    })
    .reduce((sum, order) => sum + order.total, 0);

  root.querySelector('#customer-pending-count').textContent = String(pending);
  root.querySelector('#customer-spend').textContent = currency(monthSpend);
}

function renderOrders(root, user) {
  const from = state.filters.customerFrom;
  const to = state.filters.customerTo;
  const ordersContainer = root.querySelector('#customer-orders');
  const orders = state.data.orders
    .filter((order) => order.userId === user.id)
    .filter((order) => {
      const day = formatDateInput(order.createdAt);
      if (from && day < from) return false;
      if (to && day > to) return false;
      return true;
    })
    .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));

  root.querySelector('#customer-date-from').value = from;
  root.querySelector('#customer-date-to').value = to;

  ordersContainer.innerHTML = orders.length
    ? orders
        .map((order) => {
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
                    ${order.items
                      .map((item) => `<span class='rounded-full bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm'>${item.qty} × ${item.name}</span>`)
                      .join('')}
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
        })
        .join('')
    : "<div class='rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500'>No orders found for the selected date range.</div>";
}

function bindActions(root, user, rerender) {
  root.querySelector('#room-select').onchange = (event) => {
    state.customerCart.room = event.target.value;
    persistState();
    renderSummary(root, user);
  };

  root.querySelector('#global-note').oninput = (event) => {
    state.customerCart.note = event.target.value;
    persistState();
  };

  root.querySelector('#menu-grid').onclick = (event) => {
    const button = event.target.closest('button[data-action]');
    if (!button) return;

    const productId = button.dataset.productId;
    const current = state.customerCart.items[productId] || { qty: 0, note: '' };

    if (button.dataset.action === 'increase') current.qty += 1;
    if (button.dataset.action === 'decrease') current.qty = Math.max(0, current.qty - 1);

    state.customerCart.items[productId] = current;
    persistState();
    renderMenu(root);
    renderSummary(root, user);
    bindActions(root, user, rerender);
  };

  root.querySelector('#menu-grid').oninput = (event) => {
    const input = event.target.closest('[data-note-input]');
    if (!input) return;

    const productId = input.dataset.productId;
    const current = state.customerCart.items[productId] || { qty: 0, note: '' };
    current.note = input.value;
    state.customerCart.items[productId] = current;
    persistState();
  };

  root.querySelector('#place-order-btn').onclick = () => {
    const items = getCartEntries(state.customerCart);
    if (!items.length) {
      showToast('Add at least one product before confirming the order.', 'warning');
      return;
    }

    state.data.orders.unshift({
      id: `order-${Math.random().toString(36).slice(2, 10)}-${Date.now().toString(36)}`,
      userId: user.id,
      userName: user.name,
      room: state.customerCart.room || user.roomNo,
      note: state.customerCart.note || '',
      createdAt: new Date().toISOString(),
      status: 'incoming',
      source: 'customer',
      createdBy: user.name,
      items,
      total: getCartTotal(state.customerCart),
    });

    resetCustomerCart();
    persistState();
    rerender();
    showToast('Your order has been added to the incoming queue.', 'success');
  };

  root.querySelector('#customer-date-from').onchange = (event) => {
    state.filters.customerFrom = event.target.value;
    persistState();
    renderOrders(root, user);
    bindActions(root, user, rerender);
  };

  root.querySelector('#customer-date-to').onchange = (event) => {
    state.filters.customerTo = event.target.value;
    persistState();
    renderOrders(root, user);
    bindActions(root, user, rerender);
  };

  root.querySelector('#clear-order-filters').onclick = () => {
    state.filters.customerFrom = '';
    state.filters.customerTo = '';
    persistState();
    rerender();
  };

  root.querySelector('#customer-orders').onclick = (event) => {
    const button = event.target.closest('[data-cancel-order-id]');
    if (!button) return;

    const order = state.data.orders.find((entry) => entry.id === button.dataset.cancelOrderId);
    if (!order) return;

    order.status = 'canceled';
    persistState();
    rerender();
    showToast('Order canceled successfully.', 'success');
  };
}
