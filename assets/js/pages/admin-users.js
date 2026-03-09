import { ensureRole } from '../core/auth.js';
import { initShell } from '../components/layout.js';
import { initAdminUsersPage } from '../components/admin-users.js';

const user = ensureRole('admin');
if (user) {
  initShell('admin');
  initAdminUsersPage();
}
