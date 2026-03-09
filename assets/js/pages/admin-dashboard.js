import { ensureRole } from '../core/auth.js';
import { initShell } from '../components/layout.js';
import { initAdminPipelinePage } from '../components/admin-pipeline.js';

const user = ensureRole('admin');
if (user) {
  initShell('admin');
  initAdminPipelinePage();
}
