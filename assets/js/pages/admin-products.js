import { ensureRole } from '../core/auth.js';
import { initShell } from '../components/layout.js';
import { initAdminProductsPage } from '../components/admin-products.js';

const user = ensureRole('admin');
if (user) {
  initShell('admin');
  initAdminProductsPage();
}
