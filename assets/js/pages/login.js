import { ensureGuestOnly, loginWithCredentials, redirect } from '../core/auth.js';
import { getHomeRoute } from '../core/routes.js';
import { persistState, state } from '../core/store.js';
import { showToast } from '../components/shared.js';

ensureGuestOnly();

const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const roleButtons = Array.from(document.querySelectorAll('[data-role]'));

applyRoleState();
applyDemoCredentials();

roleButtons.forEach((button) => {
  button.onclick = () => {
    state.loginRole = button.dataset.role;
    persistState();
    applyRoleState();
    applyDemoCredentials();
  };
});

document.getElementById('forgot-password').onclick = () => {
  showToast('Password recovery flow can be connected to your backend API.', 'warning');
};

document.getElementById('login-form').onsubmit = (event) => {
  event.preventDefault();
  const user = loginWithCredentials(emailInput.value.trim(), passwordInput.value.trim(), state.loginRole);
  if (!user) {
    showToast('Invalid credentials for the selected role.', 'danger');
    return;
  }
  showToast(`Welcome back, ${user.name}.`, 'success');
  redirect(getHomeRoute(user.role));
};

function applyRoleState() {
  roleButtons.forEach((button) => {
    const active = button.dataset.role === state.loginRole;
    button.classList.toggle('role-card-active', active);
    button.classList.toggle('border-slate-200', !active);
    button.classList.toggle('bg-slate-50', !active);
  });
}

function applyDemoCredentials() {
  if (state.loginRole === 'customer') {
    emailInput.value = 'employee@company.com';
    passwordInput.value = '123456';
    return;
  }

  emailInput.value = 'admin@company.com';
  passwordInput.value = 'admin123';
}
