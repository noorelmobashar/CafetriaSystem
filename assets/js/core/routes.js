export const routes = {
  login: 'index.php',
  customerMenu: 'customer/menu.php',
  customerOrders: 'customer/orders.php',
  adminDashboard: 'admin/index.php',
  adminProducts: 'admin/products.php',
  adminUsers: 'admin/users.php',
  adminManualOrder: 'admin/manual-order.php',
  adminChecks: 'admin/checks.php',
};

export function getHomeRoute(role) {
  return role === 'admin' ? routes.adminDashboard : routes.customerMenu;
}
