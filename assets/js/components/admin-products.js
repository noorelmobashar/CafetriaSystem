import { deleteProduct, saveProduct, state } from '../core/store.js';
import { PRODUCT_CATEGORIES, createIllustration, currency } from '../core/utils.js';
import { closeModal, getModalFileData, modalInput, openModal, showToast, tableShell } from './shared.js';

export function initAdminProductsPage() {
  renderProducts();
  bindProductActions();
}

function renderProducts() {
  const head = `<tr><th>Product</th><th>Category</th><th>Price</th><th>Actions</th></tr>`;
  const body = state.data.products
    .map((product) => `
      <tr>
        <td><div class='flex items-center gap-4'><img src='${product.image}' alt='${product.name}' class='h-14 w-14 rounded-2xl object-cover' /><div><p class='font-semibold text-slate-900'>${product.name}</p><p class='text-sm text-slate-500'>Editable menu item</p></div></div></td>
        <td>${product.category}</td>
        <td>${currency(product.price)}</td>
        <td><div class='flex flex-wrap gap-2'><button data-edit-product='${product.id}' class='rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800'>Edit</button><button data-delete-product='${product.id}' class='rounded-2xl bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100'>Delete</button></div></td>
      </tr>
    `)
    .join('');

  document.getElementById('products-table').innerHTML = tableShell(head, body);
}

function bindProductActions() {
  document.getElementById('add-product-btn').onclick = () => openProductModal();
  document.getElementById('products-table').onclick = (event) => {
    const editButton = event.target.closest('[data-edit-product]');
    const deleteButton = event.target.closest('[data-delete-product]');

    if (editButton) {
      openProductModal(editButton.dataset.editProduct);
      return;
    }

    if (deleteButton) {
      deleteProduct(deleteButton.dataset.deleteProduct);
      renderProducts();
      bindProductActions();
      showToast('Product deleted.', 'success');
    }
  };
}

function openProductModal(productId = null) {
  const product = state.data.products.find((entry) => entry.id === productId);

  openModal({
    kicker: product ? 'Update product' : 'Create product',
    title: product ? product.name : 'Add new product',
    render: () => `
      <div class='modal-grid cols-2'>
        ${modalInput('Product name', 'name', product?.name || '', 'text', 'Tea')}
        ${modalInput('Price (LE)', 'price', product?.price || '', 'number', '5')}
        <div>
          <label class='mb-2 block text-sm font-semibold text-slate-700'>Category</label>
          <select name='category' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100'>
            ${PRODUCT_CATEGORIES.map((category) => `<option value='${category}' ${category === (product?.category || PRODUCT_CATEGORIES[0]) ? 'selected' : ''}>${category}</option>`).join('')}
          </select>
        </div>
        <div>
          <label class='mb-2 block text-sm font-semibold text-slate-700'>Picture upload</label>
          <input name='image' type='file' accept='image/*' class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100' />
        </div>
      </div>
      <button type='submit' class='w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800'>${product ? 'Save changes' : 'Create product'}</button>
    `,
    onSubmit: async (formData) => {
      saveProduct(
        {
          name: formData.get('name').trim(),
          price: Number(formData.get('price')),
          category: formData.get('category').trim(),
          image: getModalFileData() || product?.image || createIllustration(formData.get('name').trim(), '#2563eb', '#60a5fa', '☕'),
        },
        productId
      );
      closeModal();
      renderProducts();
      bindProductActions();
      showToast(product ? 'Product updated.' : 'Product created.', 'success');
    },
  });
}
