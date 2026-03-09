import { ensureRole } from '../core/auth.js';
import { initShell } from '../components/layout.js';
import { initAdminManualOrderPage } from '../components/admin-manual-order.js';

const user = ensureRole('admin');
if (user) {
  initShell('admin');
  initAdminManualOrderPage();
}
