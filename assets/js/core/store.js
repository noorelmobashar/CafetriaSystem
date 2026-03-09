import { seedData } from '../data/seed.js';
import { STORAGE_KEY, createEmptyCart, statusMeta, uid } from './utils.js';

function buildInitialState() {
  const seeded = seedData();
  return {
    data: {
      users: seeded.users,
      products: seeded.products,
      orders: seeded.orders,
    },
    session: null,
    loginRole: 'customer',
    customerCart: seeded.customerCart,
    manualCart: seeded.manualCart,
    filters: {
      customerFrom: '',
      customerTo: '',
      checksFrom: '',
      checksTo: '',
      checksUser: 'all',
    },
  };
}

function loadState() {
  const raw = localStorage.getItem(STORAGE_KEY);
  if (!raw) return buildInitialState();

  const fallback = buildInitialState();
  const parsed = JSON.parse(raw);

  return {
    data: parsed.data || fallback.data,
    session: parsed.session || null,
    loginRole: parsed.loginRole || 'customer',
    customerCart: parsed.customerCart || fallback.customerCart,
    manualCart: parsed.manualCart || fallback.manualCart,
    filters: {
      customerFrom: parsed.filters?.customerFrom || '',
      customerTo: parsed.filters?.customerTo || '',
      checksFrom: parsed.filters?.checksFrom || '',
      checksTo: parsed.filters?.checksTo || '',
      checksUser: parsed.filters?.checksUser || 'all',
    },
  };
}

export const state = loadState();

export function persistState() {
  localStorage.setItem(
    STORAGE_KEY,
    JSON.stringify({
      data: state.data,
      session: state.session,
      loginRole: state.loginRole,
      customerCart: state.customerCart,
      manualCart: state.manualCart,
      filters: state.filters,
    })
  );
}

export function getCurrentUser() {
  return state.data.users.find((user) => user.id === state.session?.userId) || null;
}

export function getCustomerUsers() {
  return state.data.users.filter((user) => user.role === 'customer');
}

export function getRooms() {
  return [...new Set(getCustomerUsers().map((user) => user.roomNo))].sort();
}

export function getProductById(productId) {
  return state.data.products.find((product) => product.id === productId);
}

export function getCartEntries(cart) {
  return Object.entries(cart.items || {})
    .map(([productId, item]) => {
      const product = getProductById(productId);
      if (!product || item.qty <= 0) return null;
      return {
        productId,
        name: product.name,
        price: product.price,
        qty: item.qty,
        note: item.note || '',
        image: product.image,
      };
    })
    .filter(Boolean);
}

export function getCartTotal(cart) {
  return getCartEntries(cart).reduce((sum, item) => sum + item.price * item.qty, 0);
}

export function resetCustomerCart() {
  const user = getCurrentUser();
  state.customerCart = createEmptyCart(user?.roomNo || getRooms()[0] || '');
}

export function customerStatus(order) {
  if (order.status === 'incoming') return { label: 'Processing', className: 'status-processing' };
  if (order.status === 'canceled') return statusMeta.canceled;
  return statusMeta[order.status];
}

export function createCustomerOrder(user) {
  const items = getCartEntries(state.customerCart);
  const order = {
    id: uid('order'),
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
  };

  state.data.orders.unshift(order);
  resetCustomerCart();
  persistState();
  return order;
}

export function cancelOrder(orderId) {
  const order = state.data.orders.find((entry) => entry.id === orderId);
  if (!order) return null;
  order.status = 'canceled';
  persistState();
  return order;
}

export function updateOrderStatus(orderId, nextStatus) {
  const order = state.data.orders.find((entry) => entry.id === orderId);
  if (!order) return null;
  order.status = nextStatus;
  persistState();
  return order;
}

export function saveProduct(payload, productId = null) {
  const existing = state.data.products.find((product) => product.id === productId);
  const nextProduct = {
    id: existing?.id || uid('product'),
    ...payload,
  };

  if (existing) {
    state.data.products = state.data.products.map((product) => (product.id === productId ? nextProduct : product));
    state.data.orders = state.data.orders.map((order) => ({
      ...order,
      items: order.items.map((item) =>
        item.productId === productId ? { ...item, name: nextProduct.name, price: nextProduct.price } : item
      ),
      total: order.items.reduce(
        (sum, item) => sum + (item.productId === productId ? nextProduct.price : item.price) * item.qty,
        0
      ),
    }));
  } else {
    state.data.products.push(nextProduct);
  }

  persistState();
  return nextProduct;
}

export function deleteProduct(productId) {
  state.data.products = state.data.products.filter((product) => product.id !== productId);
  delete state.customerCart.items[productId];
  delete state.manualCart.items[productId];
  state.data.orders = state.data.orders
    .map((order) => {
      const items = order.items.filter((item) => item.productId !== productId);
      return {
        ...order,
        items,
        total: items.reduce((sum, item) => sum + item.qty * item.price, 0),
      };
    })
    .filter((order) => order.items.length > 0 || order.status === 'canceled');
  persistState();
}

export function saveUser(payload, userId = null) {
  const existing = state.data.users.find((user) => user.id === userId);
  const nextUser = {
    id: existing?.id || uid('user'),
    role: 'customer',
    ...payload,
  };

  if (existing) {
    state.data.users = state.data.users.map((user) => (user.id === userId ? { ...user, ...nextUser } : user));
    state.data.orders = state.data.orders.map((order) =>
      order.userId === userId ? { ...order, userName: nextUser.name, room: nextUser.roomNo } : order
    );
  } else {
    state.data.users.push(nextUser);
  }

  persistState();
  return nextUser;
}

export function createManualOrder(admin, user) {
  const items = getCartEntries(state.manualCart);
  const order = {
    id: uid('order'),
    userId: user.id,
    userName: user.name,
    room: state.manualCart.room || user.roomNo,
    note: state.manualCart.note || '',
    createdAt: new Date().toISOString(),
    status: 'incoming',
    source: 'manual',
    createdBy: admin.name,
    items,
    total: getCartTotal(state.manualCart),
  };

  state.data.orders.unshift(order);
  state.manualCart = {
    room: user.roomNo,
    note: '',
    items: {},
    userId: user.id,
  };
  persistState();
  return order;
}
