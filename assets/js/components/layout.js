import { logout } from '../core/auth.js';
import { getCurrentUser } from '../core/store.js';
import { bindModalShell } from './shared.js';

// export function initShell(requiredRole) {
//   const user = getCurrentUser();
//   if (!user || user.role !== requiredRole) return null;

//   const nameNode = document.getElementById('shell-user-name');
//   const logoutButton = document.getElementById('logout-btn');

//   if (nameNode) nameNode.textContent = user.name;
//   if (logoutButton) logoutButton.onclick = logout;

//   bindModalShell();
//   return user;
// }
