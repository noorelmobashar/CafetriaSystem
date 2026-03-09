import { appUrl } from './context.js';
import { getCurrentUser, getRooms, persistState, state } from './store.js';
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
  const normalizedEmail = email.trim().toLowerCase();
  const normalizedPassword = password.trim();

  let user = state.data.users.find(
    (entry) =>
      entry.email.toLowerCase() === normalizedEmail &&
      entry.password === normalizedPassword &&
      entry.role === role
  );

  if (!user) {
    user = state.data.users.find(
      (entry) => entry.email.toLowerCase() === normalizedEmail && entry.password === normalizedPassword
    );
  }

  if (!user) return null;

  state.session = { userId: user.id, role: user.role };
  state.loginRole = user.role;
  if (user.role === 'customer') {
    state.customerCart.room = state.customerCart.room || getRooms()[0] || '';
  }
  persistState();
  return user;
}

export function logout() {
  state.session = null;
  persistState();
  redirect(routes.login);
}
