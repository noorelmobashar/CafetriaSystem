import { saveUser, getCustomerUsers, state } from '../core/store.js';
import { createAvatar } from '../core/utils.js';
import { closeModal, getModalFileData, modalInput, openModal, showToast, tableShell } from './shared.js';

export function initAdminUsersPage() {
  renderUsers();
  bindUserActions();
}

function renderUsers() {
  const head = `<tr><th>User</th><th>Email</th><th>Room</th><th>Ext.</th><th>Actions</th></tr>`;
  const body = getCustomerUsers()
    .map((user) => `
      <tr>
        <td><div class='flex items-center gap-4'><img src='${user.avatar}' alt='${user.name}' class='h-14 w-14 rounded-2xl object-cover' /><div><p class='font-semibold text-slate-900'>${user.name}</p><p class='text-sm text-slate-500'>Employee account</p></div></div></td>
        <td>${user.email}</td>
        <td>${user.roomNo}</td>
        <td>${user.ext}</td>
        <td><button data-edit-user='${user.id}' class='rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800'>Edit</button></td>
      </tr>
    `)
    .join('');

  document.getElementById('users-table').innerHTML = tableShell(head, body);
}

function bindUserActions() {
  document.getElementById('add-user-btn').onclick = () => openUserModal();
  document.getElementById('users-table').onclick = (event) => {
    const editButton = event.target.closest('[data-edit-user]');
    if (!editButton) return;
    openUserModal(editButton.dataset.editUser);
  };
}

function openUserModal(userId = null) {
  const user = state.data.users.find((entry) => entry.id === userId);

  openModal({
    kicker: user ? 'Update user' : 'Create user',
    title: user ? user.name : 'Add new employee',
    render: () => `
      <div class='modal-grid cols-2'>
        ${modalInput('Name', 'name', user?.name || '', 'text', 'Employee name')}
        ${modalInput('Email', 'email', user?.email || '', 'email', 'name@company.com')}
        ${modalInput('Password', 'password', user?.password || '', 'text', '••••••')}
        ${modalInput('Room No.', 'roomNo', user?.roomNo || '', 'text', 'Room 201')}
        ${modalInput('Ext.', 'ext', user?.ext || '', 'text', '201')}
        <div>
          <label class='mb-2 block text-sm font-semibold text-slate-700'>Profile picture</label>
          <input name='avatar' type='file' accept='image/*' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100' />
        </div>
      </div>
      <button type='submit' class='w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800'>${user ? 'Save changes' : 'Create user'}</button>
    `,
    onSubmit: async (formData) => {
      saveUser(
        {
          name: formData.get('name').trim(),
          email: formData.get('email').trim().toLowerCase(),
          password: formData.get('password').trim(),
          roomNo: formData.get('roomNo').trim(),
          ext: formData.get('ext').trim(),
          avatar: getModalFileData() || user?.avatar || createAvatar(formData.get('name').trim(), '#0f766e', '#34d399'),
        },
        userId
      );
      closeModal();
      renderUsers();
      bindUserActions();
      showToast(user ? 'User updated.' : 'User created.', 'success');
    },
  });
}
