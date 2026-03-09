import { appUrl } from './context.js';
import { getCurrentUser, persistState, state } from './store.js';
import { routes, getHomeRoute } from './routes.js';

export function redirect(path) {
  window.location.href = appUrl(path);
}

export function ensureGuestOnly() {
  const user = getCurrentUser();
  if (user) {
    redirect(getHomeRoute(user.role));
    return user;
  }
  return null;
}

export function ensureRole(role) {
  const user = getCurrentUser();
  if (!user || user.role !== role) {
    redirect(routes.login);
    return null;
  }
  return user;
}

export function loginWithCredentials(email, password, role) {
  const user = state.data.users.find(
    (entry) => entry.email.toLowerCase() === email.toLowerCase() && entry.password === password && entry.role === role
  );

  if (!user) return null;

  state.session = { userId: user.id, role: user.role };
  if (user.role === 'customer') {
    state.customerCart.room = state.customerCart.room || user.roomNo;
  }
  persistState();
  return user;
}

export function logout() {
  state.session = null;
  persistState();
  redirect(routes.login);
}
