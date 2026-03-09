import { seedData } from './data/seed.js';
import { STORAGE_KEY, createEmptyCart, statusMeta } from './utils.js';

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
    adminSection: 'home',
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
    adminSection: parsed.adminSection || 'home',
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
      adminSection: state.adminSection,
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
      if (!product || item.qty <= 0) {
        return null;
      }

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
  if (order.status === 'incoming') {
    return { label: 'Processing', className: 'status-processing' };
  }

  if (order.status === 'canceled') {
    return statusMeta.canceled;
  }

  return statusMeta[order.status];
}
