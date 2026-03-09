import { ensureRole } from '../core/auth.js';
import { initShell } from '../components/layout.js';
import { initCustomerMenuPage } from '../components/customer-menu-panel.js';

const user = ensureRole('customer');
if (user) {
  initShell('customer');
  initCustomerMenuPage();
}
