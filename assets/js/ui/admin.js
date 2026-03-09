import {
  getCartEntries,
  getCartTotal,
  getCurrentUser,
  getCustomerUsers,
  getRooms,
  persistState,
  state,
} from '../store.js';
import {
  adminSections,
  createAvatar,
  createIllustration,
  currency,
  formatDateInput,
  formatDateTime,
  statusMeta,
  uid,
} from '../utils.js';
import { getModalFileData, modalInput, openModal, showToast, tableShell, closeModal } from './shared.js';

export function renderAdminView(root, rerender) {
  root.innerHTML = `
    <div class='space-y-6'>
      <nav class='rounded-[2rem] border border-white/70 bg-white/85 p-3 shadow-soft backdrop-blur'>
        <div id='admin-nav' class='flex flex-wrap gap-2'></div>
      </nav>

      <section id='admin-section-home' class='admin-section space-y-6'>
        <div class='grid gap-4 md:grid-cols-2 xl:grid-cols-4'>
          <div class='rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur'><p class='text-sm text-slate-500'>Incoming</p><p id='stat-incoming' class='mt-2 text-3xl font-bold text-slate-900'>0</p></div>
          <div class='rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur'><p class='text-sm text-slate-500'>Processing</p><p id='stat-processing' class='mt-2 text-3xl font-bold text-slate-900'>0</p></div>
          <div class='rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur'><p class='text-sm text-slate-500'>Out for delivery</p><p id='stat-delivery' class='mt-2 text-3xl font-bold text-slate-900'>0</p></div>
          <div class='rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur'><p class='text-sm text-slate-500'>Done</p><p id='stat-done' class='mt-2 text-3xl font-bold text-slate-900'>0</p></div>
        </div>

        <div class='grid gap-6 xl:grid-cols-4'>
          <div class='kanban-column rounded-[2rem] border border-sky-100 bg-white/85 p-4 shadow-soft backdrop-blur'><div class='mb-4 flex items-center justify-between'><h2 class='text-lg font-bold text-slate-900'>Incoming</h2><span id='incoming-count' class='rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700'>0</span></div><div id='incoming-list' class='space-y-4'></div></div>
          <div class='kanban-column rounded-[2rem] border border-amber-100 bg-white/85 p-4 shadow-soft backdrop-blur'><div class='mb-4 flex items-center justify-between'><h2 class='text-lg font-bold text-slate-900'>Processing</h2><span id='processing-count' class='rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700'>0</span></div><div id='processing-list' class='space-y-4'></div></div>
          <div class='kanban-column rounded-[2rem] border border-violet-100 bg-white/85 p-4 shadow-soft backdrop-blur'><div class='mb-4 flex items-center justify-between'><h2 class='text-lg font-bold text-slate-900'>Out for Delivery</h2><span id='delivery-count' class='rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700'>0</span></div><div id='delivery-list' class='space-y-4'></div></div>
          <div class='kanban-column rounded-[2rem] border border-emerald-100 bg-white/85 p-4 shadow-soft backdrop-blur'><div class='mb-4 flex items-center justify-between'><h2 class='text-lg font-bold text-slate-900'>Done</h2><span id='done-count' class='rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700'>0</span></div><div id='done-list' class='space-y-4'></div></div>
        </div>
      </section>

      <section id='admin-section-products' class='admin-section hidden rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6'>
        <div class='flex flex-col gap-4 md:flex-row md:items-center md:justify-between'>
          <div><p class='text-sm font-semibold uppercase tracking-[0.25em] text-cafe-500'>Inventory</p><h2 class='mt-2 text-2xl font-bold text-slate-900'>Products</h2></div>
          <button id='add-product-btn' class='rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800'>Add product</button>
        </div>
        <div id='products-table' class='mt-6 overflow-hidden rounded-[1.5rem] border border-slate-200'></div>
      </section>

      <section id='admin-section-users' class='admin-section hidden rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6'>
        <div class='flex flex-col gap-4 md:flex-row md:items-center md:justify-between'>
          <div><p class='text-sm font-semibold uppercase tracking-[0.25em] text-brand-600'>Employee base</p><h2 class='mt-2 text-2xl font-bold text-slate-900'>Users</h2></div>
          <button id='add-user-btn' class='rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800'>Add user</button>
        </div>
        <div id='users-table' class='mt-6 overflow-hidden rounded-[1.5rem] border border-slate-200'></div>
      </section>

      <section id='admin-section-manual-order' class='admin-section hidden rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6'>
        <div class='grid gap-6 xl:grid-cols-[0.9fr,1.1fr]'>
          <div>
            <p class='text-sm font-semibold uppercase tracking-[0.25em] text-pine-500'>Assign bills</p>
            <h2 class='mt-2 text-2xl font-bold text-slate-900'>Manual Order</h2>
            <div class='mt-6 space-y-4'>
              <div><label for='manual-user' class='mb-2 block text-sm font-semibold text-slate-700'>Select user</label><select id='manual-user' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100'></select></div>
              <div><label for='manual-room' class='mb-2 block text-sm font-semibold text-slate-700'>Room</label><select id='manual-room' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100'></select></div>
              <div><label for='manual-note' class='mb-2 block text-sm font-semibold text-slate-700'>Note</label><input id='manual-note' type='text' placeholder='Optional order note' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100' /></div>
              <button id='assign-manual-order-btn' class='w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800'>Add to user</button>
            </div>
          </div>
          <div>
            <div class='flex items-center justify-between'>
              <h3 class='text-lg font-bold text-slate-900'>Select products</h3>
              <div class='rounded-2xl bg-slate-100 px-4 py-2 text-sm text-slate-600'>Assigned as Incoming</div>
            </div>
            <div id='manual-products-grid' class='mt-6 grid gap-4 md:grid-cols-2'></div>
            <div class='mt-6 rounded-[1.5rem] bg-slate-900 p-5 text-white'>
              <div id='manual-cart-items' class='space-y-3'></div>
              <div class='mt-4 flex items-center justify-between border-t border-white/10 pt-4 text-lg font-semibold'><span>Total</span><span id='manual-total'>0 LE</span></div>
            </div>
          </div>
        </div>
      </section>

      <section id='admin-section-checks' class='admin-section hidden rounded-[2rem] border border-white/70 bg-white/85 p-5 shadow-soft backdrop-blur md:p-6'>
        <div class='flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between'>
          <div><p class='text-sm font-semibold uppercase tracking-[0.25em] text-cafe-500'>Audit spending</p><h2 class='mt-2 text-2xl font-bold text-slate-900'>Checks</h2></div>
          <div class='grid gap-3 md:grid-cols-4 xl:min-w-[58rem]'>
            <div><label for='checks-user' class='mb-2 block text-sm font-semibold text-slate-700'>User</label><select id='checks-user' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100'></select></div>
            <div><label for='checks-from' class='mb-2 block text-sm font-semibold text-slate-700'>From</label><input id='checks-from' type='date' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100' /></div>
            <div><label for='checks-to' class='mb-2 block text-sm font-semibold text-slate-700'>To</label><input id='checks-to' type='date' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100' /></div>
            <div class='flex items-end'><button id='reset-checks-btn' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50'>Reset</button></div>
          </div>
        </div>
        <div class='mt-6 grid gap-4 md:grid-cols-3'>
          <div class='rounded-[1.5rem] bg-slate-900 p-5 text-white'><p class='text-sm text-slate-300'>Total amount</p><p id='checks-total' class='mt-2 text-3xl font-bold'>0 LE</p></div>
          <div class='rounded-[1.5rem] bg-brand-50 p-5'><p class='text-sm text-brand-700'>Orders counted</p><p id='checks-orders-count' class='mt-2 text-3xl font-bold text-brand-900'>0</p></div>
          <div class='rounded-[1.5rem] bg-cafe-100 p-5'><p class='text-sm text-cafe-600'>Average check</p><p id='checks-average' class='mt-2 text-3xl font-bold text-cafe-600'>0 LE</p></div>
        </div>
        <div id='checks-table' class='mt-6 overflow-hidden rounded-[1.5rem] border border-slate-200'></div>
      </section>
    </div>
  `;

  renderNav(root, rerender);
  renderSections(root, rerender);
}

function renderNav(root, rerender) {
  root.querySelector('#admin-nav').innerHTML = adminSections
    .map(
      (section) => `
        <button data-admin-section='${section.id}' class='rounded-2xl px-4 py-3 text-sm font-semibold transition ${
          state.adminSection === section.id ? 'bg-slate-900 text-white shadow-soft' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
        }'>${section.label}</button>
      `
    )
    .join('');

  root.querySelector('#admin-nav').onclick = (event) => {
    const button = event.target.closest('[data-admin-section]');
    if (!button) return;
    state.adminSection = button.dataset.adminSection;
    persistState();
    rerender();
  };
}

function renderSections(root, rerender) {
  root.querySelectorAll('.admin-section').forEach((section) => section.classList.add('hidden'));
  root.querySelector(`#admin-section-${state.adminSection}`)?.classList.remove('hidden');

  renderHome(root, rerender);
  renderProducts(root, rerender);
  renderUsers(root, rerender);
  renderManualOrders(root, rerender);
  renderChecks(root, rerender);
}

function renderHome(root, rerender) {
  const activeOrders = state.data.orders.filter((order) => order.status !== 'canceled');
  const buckets = {
    incoming: activeOrders.filter((order) => order.status === 'incoming'),
    processing: activeOrders.filter((order) => order.status === 'processing'),
    delivery: activeOrders.filter((order) => order.status === 'out-for-delivery'),
    done: activeOrders.filter((order) => order.status === 'done'),
  };

  root.querySelector('#stat-incoming').textContent = String(buckets.incoming.length);
  root.querySelector('#stat-processing').textContent = String(buckets.processing.length);
  root.querySelector('#stat-delivery').textContent = String(buckets.delivery.length);
  root.querySelector('#stat-done').textContent = String(buckets.done.length);
  root.querySelector('#incoming-count').textContent = String(buckets.incoming.length);
  root.querySelector('#processing-count').textContent = String(buckets.processing.length);
  root.querySelector('#delivery-count').textContent = String(buckets.delivery.length);
  root.querySelector('#done-count').textContent = String(buckets.done.length);

  renderKanbanList(root, 'incoming-list', buckets.incoming, 'processing', 'Start processing');
  renderKanbanList(root, 'processing-list', buckets.processing, 'out-for-delivery', 'Send to delivery');
  renderKanbanList(root, 'delivery-list', buckets.delivery, 'done', 'Mark as done');
  renderKanbanList(root, 'done-list', buckets.done, null, '');

  ['incoming-list', 'processing-list', 'delivery-list', 'done-list'].forEach((id) => {
    root.querySelector(`#${id}`).onclick = (event) => {
      const button = event.target.closest('[data-next-status]');
      if (!button) return;
      const order = state.data.orders.find((entry) => entry.id === button.dataset.orderId);
      if (!order) return;
      order.status = button.dataset.nextStatus;
      persistState();
      rerender();
      showToast(`Order moved to ${statusMeta[order.status].label}.`, 'success');
    };
  });
}

function renderKanbanList(root, containerId, orders, nextStatus, actionLabel) {
  root.querySelector(`#${containerId}`).innerHTML = orders.length
    ? orders
        .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
        .map((order) => {
          const status = statusMeta[order.status];
          return `
            <article class='card-hover rounded-[1.5rem] border border-slate-200 bg-white p-4'>
              <div class='flex items-start justify-between gap-3'>
                <div>
                  <h3 class='text-base font-bold text-slate-900'>${order.userName}</h3>
                  <p class='mt-1 text-sm text-slate-500'>${order.room} • ${formatDateTime(order.createdAt)}</p>
                </div>
                <span class='status-pill ${status.className}'>${status.label}</span>
              </div>
              <div class='mt-4 space-y-2 text-sm text-slate-600'>
                ${order.items.map((item) => `<div class='flex items-center justify-between gap-3'><span>${item.qty} × ${item.name}</span><span class='font-semibold text-slate-800'>${currency(item.price * item.qty)}</span></div>`).join('')}
              </div>
              ${order.note ? `<p class='mt-4 rounded-2xl bg-slate-50 px-3 py-2 text-sm text-slate-600'>${order.note}</p>` : ''}
              <div class='mt-4 flex items-center justify-between'>
                <p class='text-lg font-bold text-slate-900'>${currency(order.total)}</p>
                ${nextStatus ? `<button data-order-id='${order.id}' data-next-status='${nextStatus}' class='rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800'>${actionLabel}</button>` : `<span class='rounded-2xl bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700'>Completed</span>`}
              </div>
            </article>
          `;
        })
        .join('')
    : "<div class='rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500'>No orders in this stage.</div>";
}

function renderProducts(root, rerender) {
  const tableRoot = root.querySelector('#products-table');
  const head = `
    <tr><th>Product</th><th>Category</th><th>Price</th><th>Actions</th></tr>
  `;
  const body = state.data.products
    .map(
      (product) => `
        <tr>
          <td><div class='flex items-center gap-4'><img src='${product.image}' alt='${product.name}' class='h-14 w-14 rounded-2xl object-cover' /><div><p class='font-semibold text-slate-900'>${product.name}</p><p class='text-sm text-slate-500'>Editable menu item</p></div></div></td>
          <td>${product.category}</td>
          <td>${currency(product.price)}</td>
          <td><div class='flex flex-wrap gap-2'><button data-edit-product='${product.id}' class='rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800'>Edit</button><button data-delete-product='${product.id}' class='rounded-2xl bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100'>Delete</button></div></td>
        </tr>
      `
    )
    .join('');

  tableRoot.innerHTML = tableShell(head, body);

  root.querySelector('#add-product-btn').onclick = () => openProductModal(rerender);
  tableRoot.onclick = (event) => {
    const editButton = event.target.closest('[data-edit-product]');
    const deleteButton = event.target.closest('[data-delete-product]');

    if (editButton) {
      openProductModal(rerender, editButton.dataset.editProduct);
      return;
    }

    if (deleteButton) {
      const productId = deleteButton.dataset.deleteProduct;
      state.data.products = state.data.products.filter((product) => product.id !== productId);
      delete state.customerCart.items[productId];
      delete state.manualCart.items[productId];
      state.data.orders = state.data.orders.map((order) => ({
        ...order,
        items: order.items.filter((item) => item.productId !== productId),
        total: order.items
          .filter((item) => item.productId !== productId)
          .reduce((sum, item) => sum + item.qty * item.price, 0),
      }));
      persistState();
      rerender();
      showToast('Product deleted.', 'success');
    }
  };
}

function openProductModal(rerender, productId = null) {
  const product = state.data.products.find((entry) => entry.id === productId);

  openModal({
    kicker: product ? 'Update product' : 'Create product',
    title: product ? product.name : 'Add new product',
    render: () => `
      <div class='modal-grid cols-2'>
        ${modalInput('Product name', 'name', product?.name || '', 'text', 'Tea')}
        ${modalInput('Price (LE)', 'price', product?.price || '', 'number', '5')}
        ${modalInput('Category', 'category', product?.category || '', 'text', 'Hot Drinks')}
        <div>
          <label class='mb-2 block text-sm font-semibold text-slate-700'>Picture upload</label>
          <input name='image' type='file' accept='image/*' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100' />
        </div>
      </div>
      <button type='submit' class='w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800'>${product ? 'Save changes' : 'Create product'}</button>
    `,
    onSubmit: async (formData) => {
      const payload = {
        id: product?.id || uid('product'),
        name: formData.get('name').trim(),
        price: Number(formData.get('price')),
        category: formData.get('category').trim(),
        image: getModalFileData() || product?.image || createIllustration(formData.get('name').trim(), '#2563eb', '#60a5fa', '☕'),
      };

      if (product) {
        state.data.products = state.data.products.map((entry) => (entry.id === product.id ? payload : entry));
        state.data.orders = state.data.orders.map((order) => ({
          ...order,
          items: order.items.map((item) =>
            item.productId === product.id ? { ...item, name: payload.name, price: payload.price } : item
          ),
          total: order.items.reduce(
            (sum, item) =>
              sum + (item.productId === product.id ? payload.price : item.price) * item.qty,
            0
          ),
        }));
      } else {
        state.data.products.push(payload);
      }

      persistState();
      closeModal();
      rerender();
      showToast(product ? 'Product updated.' : 'Product created.', 'success');
    },
  });
}

function renderUsers(root, rerender) {
  const tableRoot = root.querySelector('#users-table');
  const head = `<tr><th>User</th><th>Email</th><th>Room</th><th>Ext.</th><th>Actions</th></tr>`;
  const body = getCustomerUsers()
    .map(
      (user) => `
        <tr>
          <td><div class='flex items-center gap-4'><img src='${user.avatar}' alt='${user.name}' class='h-14 w-14 rounded-2xl object-cover' /><div><p class='font-semibold text-slate-900'>${user.name}</p><p class='text-sm text-slate-500'>Employee account</p></div></div></td>
          <td>${user.email}</td>
          <td>${user.roomNo}</td>
          <td>${user.ext}</td>
          <td><button data-edit-user='${user.id}' class='rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800'>Edit</button></td>
        </tr>
      `
    )
    .join('');

  tableRoot.innerHTML = tableShell(head, body);
  root.querySelector('#add-user-btn').onclick = () => openUserModal(rerender);
  tableRoot.onclick = (event) => {
    const editButton = event.target.closest('[data-edit-user]');
    if (!editButton) return;
    openUserModal(rerender, editButton.dataset.editUser);
  };
}

function openUserModal(rerender, userId = null) {
  const user = state.data.users.find((entry) => entry.id === userId);

  openModal({
    kicker: user ? 'Update user' : 'Create user',
    title: user ? user.name : 'Add new employee',
    render: () => `
      <div class='modal-grid cols-2'>
        ${modalInput('Name', 'name', user?.name || '', 'text', 'Employee name')}
        ${modalInput('Email', 'email', user?.email || '', 'email', 'name@company.com')}
        ${modalInput('Password', 'password', user?.password || '', 'text', '••••••')}
        ${modalInput('Room No.', 'roomNo', user?.roomNo || '', 'text', 'Room 201')}
        ${modalInput('Ext.', 'ext', user?.ext || '', 'text', '201')}
        <div>
          <label class='mb-2 block text-sm font-semibold text-slate-700'>Profile picture</label>
          <input name='avatar' type='file' accept='image/*' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100' />
        </div>
      </div>
      <button type='submit' class='w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800'>${user ? 'Save changes' : 'Create user'}</button>
    `,
    onSubmit: async (formData) => {
      const payload = {
        id: user?.id || uid('user'),
        role: 'customer',
        name: formData.get('name').trim(),
        email: formData.get('email').trim().toLowerCase(),
        password: formData.get('password').trim(),
        roomNo: formData.get('roomNo').trim(),
        ext: formData.get('ext').trim(),
        avatar: getModalFileData() || user?.avatar || createAvatar(formData.get('name').trim(), '#0f766e', '#34d399'),
      };

      if (user) {
        state.data.users = state.data.users.map((entry) => (entry.id === user.id ? { ...entry, ...payload } : entry));
        state.data.orders = state.data.orders.map((order) =>
          order.userId === user.id ? { ...order, userName: payload.name, room: payload.roomNo } : order
        );
      } else {
        state.data.users.push(payload);
      }

      persistState();
      closeModal();
      rerender();
      showToast(user ? 'User updated.' : 'User created.', 'success');
    },
  });
}

function renderManualOrders(root, rerender) {
  const users = getCustomerUsers();
  const userSelect = root.querySelector('#manual-user');
  const roomSelect = root.querySelector('#manual-room');
  const noteInput = root.querySelector('#manual-note');
  const productsGrid = root.querySelector('#manual-products-grid');

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
  root.querySelector('#manual-cart-items').innerHTML = entries.length
    ? entries
        .map(
          (item) => `
            <div class='flex items-center justify-between gap-3 rounded-2xl bg-white/10 p-4'>
              <div><p class='font-semibold'>${item.name}</p><p class='text-sm text-slate-300'>${item.qty} × ${currency(item.price)}</p></div>
              <div class='font-semibold'>${currency(item.qty * item.price)}</div>
            </div>
          `
        )
        .join('')
    : "<div class='rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300'>No products selected.</div>";
  root.querySelector('#manual-total').textContent = currency(getCartTotal(state.manualCart));

  userSelect.onchange = (event) => {
    state.manualCart.userId = event.target.value;
    const nextUser = users.find((user) => user.id === event.target.value);
    state.manualCart.room = nextUser?.roomNo || state.manualCart.room;
    persistState();
    rerender();
  };

  roomSelect.onchange = (event) => {
    state.manualCart.room = event.target.value;
    persistState();
  };

  noteInput.oninput = (event) => {
    state.manualCart.note = event.target.value;
    persistState();
  };

  productsGrid.onclick = (event) => {
    const button = event.target.closest('[data-manual-action]');
    if (!button) return;
    const productId = button.dataset.productId;
    const item = state.manualCart.items[productId] || { qty: 0, note: '' };
    if (button.dataset.manualAction === 'increase') item.qty += 1;
    if (button.dataset.manualAction === 'decrease') item.qty = Math.max(0, item.qty - 1);
    state.manualCart.items[productId] = item;
    persistState();
    rerender();
  };

  root.querySelector('#assign-manual-order-btn').onclick = () => {
    const selected = users.find((user) => user.id === state.manualCart.userId);
    const items = getCartEntries(state.manualCart);
    if (!selected) {
      showToast('Please select a user.', 'warning');
      return;
    }
    if (!items.length) {
      showToast('Select at least one product first.', 'warning');
      return;
    }

    const admin = getCurrentUser();
    state.data.orders.unshift({
      id: uid('order'),
      userId: selected.id,
      userName: selected.name,
      room: state.manualCart.room || selected.roomNo,
      note: state.manualCart.note || '',
      createdAt: new Date().toISOString(),
      status: 'incoming',
      source: 'manual',
      createdBy: admin.name,
      items,
      total: getCartTotal(state.manualCart),
    });

    state.manualCart = {
      room: selected.roomNo,
      note: '',
      items: {},
      userId: selected.id,
    };
    persistState();
    rerender();
    showToast('Manual order assigned to user account.', 'success');
  };
}

function renderChecks(root, rerender) {
  const userSelect = root.querySelector('#checks-user');
  const fromInput = root.querySelector('#checks-from');
  const toInput = root.querySelector('#checks-to');
  const tableRoot = root.querySelector('#checks-table');

  userSelect.innerHTML = [`<option value='all'>All users</option>`]
    .concat(
      getCustomerUsers().map(
        (user) => `<option value='${user.id}' ${state.filters.checksUser === user.id ? 'selected' : ''}>${user.name}</option>`
      )
    )
    .join('');

  fromInput.value = state.filters.checksFrom;
  toInput.value = state.filters.checksTo;

  const filteredOrders = state.data.orders
    .filter((order) => order.status !== 'canceled')
    .filter((order) => (state.filters.checksUser === 'all' ? true : order.userId === state.filters.checksUser))
    .filter((order) => {
      const day = formatDateInput(order.createdAt);
      if (state.filters.checksFrom && day < state.filters.checksFrom) return false;
      if (state.filters.checksTo && day > state.filters.checksTo) return false;
      return true;
    })
    .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));

  const totalAmount = filteredOrders.reduce((sum, order) => sum + order.total, 0);
  root.querySelector('#checks-total').textContent = currency(totalAmount);
  root.querySelector('#checks-orders-count').textContent = String(filteredOrders.length);
  root.querySelector('#checks-average').textContent = currency(filteredOrders.length ? totalAmount / filteredOrders.length : 0);

  const head = `<tr><th>User</th><th>Date</th><th>Status</th><th>Source</th><th>Total</th></tr>`;
  const body = filteredOrders.length
    ? filteredOrders
        .map((order) => {
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
        })
        .join('')
    : "<tr><td colspan='5'><div class='p-8 text-center text-sm text-slate-500'>No matching checks found.</div></td></tr>";

  tableRoot.innerHTML = tableShell(head, body);

  userSelect.onchange = (event) => {
    state.filters.checksUser = event.target.value;
    persistState();
    rerender();
  };

  fromInput.onchange = (event) => {
    state.filters.checksFrom = event.target.value;
    persistState();
    rerender();
  };

  toInput.onchange = (event) => {
    state.filters.checksTo = event.target.value;
    persistState();
    rerender();
  };

  root.querySelector('#reset-checks-btn').onclick = () => {
    state.filters.checksUser = 'all';
    state.filters.checksFrom = '';
    state.filters.checksTo = '';
    persistState();
    rerender();
  };
}
