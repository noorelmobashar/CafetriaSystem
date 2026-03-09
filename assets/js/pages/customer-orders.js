import { ensureRole } from '../core/auth.js';
import { initShell } from '../components/layout.js';
import { initCustomerOrdersPage } from '../components/customer-orders-panel.js';

const user = ensureRole('customer');
if (user) {
  initShell('customer');
  initCustomerOrdersPage();
}
