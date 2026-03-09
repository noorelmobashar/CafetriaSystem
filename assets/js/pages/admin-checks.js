import { ensureRole } from '../core/auth.js';
import { initShell } from '../components/layout.js';
import { initAdminChecksPage } from '../components/admin-checks.js';

const user = ensureRole('admin');
if (user) {
  initShell('admin');
  initAdminChecksPage();
}
