/**
 * manual-order-cart.js
 * Live-updates the cart summary and total whenever a product qty input changes.
 * Reads product metadata from data attributes on each <article> card.
 * Also handles real-time product search functionality.
 */

function formatCurrency(amount) {
  return (
    Number(amount).toLocaleString("en-EG", { maximumFractionDigits: 0 }) + " LE"
  );
}

function updateCart() {
  const cartLines = document.getElementById("js-cart-lines");
  const cartTotal = document.getElementById("js-cart-total");

  if (!cartLines || !cartTotal) return;

  const inputs = document.querySelectorAll('input[name^="qty["]');
  const lines = [];
  let total = 0;

  inputs.forEach((input) => {
    const qty = parseInt(input.value, 10);
    if (!qty || qty <= 0) return;

    const article = input.closest("article[data-product-id]");
    if (!article) return;

    const name = article.dataset.productName;
    const price = parseFloat(article.dataset.productPrice);
    const lineTotal = price * qty;
    total += lineTotal;
    lines.push({ name, qty, price, lineTotal });
  });

  if (lines.length === 0) {
    cartLines.innerHTML =
      "<div class='rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300'>No products selected.</div>";
  } else {
    cartLines.innerHTML = lines
      .map(
        (line) => `
      <div class="flex items-center justify-between gap-3 rounded-2xl bg-white/10 p-4">
        <div>
          <p class="font-semibold">${line.name}</p>
          <p class="text-sm text-slate-300">${line.qty} × ${formatCurrency(line.price)}</p>
        </div>
        <div class="font-semibold">${formatCurrency(line.lineTotal)}</div>
      </div>
    `,
      )
      .join("");
  }

  cartTotal.textContent = formatCurrency(total);
}

// Real-time search functionality
let searchTimeout;
function performRealTimeSearch(query, page = 1) {
  const searchInput = document.querySelector('input[name="product_search"]');
  const productsGrid = document.getElementById('products-grid');
  
  if (!searchInput || !productsGrid) return;

  // Show loading state
  productsGrid.innerHTML = '<div class="md:col-span-2 rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">Searching...</div>';

  // Fetch search results via AJAX
  fetch(`../admin/ajax-search-products.php?q=${encodeURIComponent(query)}&page=${page}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        renderProducts(data.products, query, data.totalPages, data.currentPage);
      } else {
        productsGrid.innerHTML = '<div class="md:col-span-2 rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-red-500">Error loading products.</div>';
      }
    })
    .catch(error => {
      console.error('Search error:', error);
      productsGrid.innerHTML = '<div class="md:col-span-2 rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-red-500">Error loading products.</div>';
    });
}

function renderProducts(products, searchQuery, totalPages, currentPage) {
  const productsGrid = document.getElementById('products-grid');
  if (!productsGrid) return;

  if (products.length === 0) {
    productsGrid.innerHTML = '<div class="md:col-span-2 rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">No products match your search.</div>';
    return;
  }

  const productsHTML = products.map(product => `
    <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4"
      data-product-id="${product.id}"
      data-product-name="${htmlspecialchars(product.name)}"
      data-product-price="${product.price}">
      <div class="flex gap-4">
        ${product.image_path ? 
          `<img src="../${htmlspecialchars(product.image_path)}" alt="${htmlspecialchars(product.name)}" class="h-20 w-20 rounded object-cover" />` : 
          '<div class="flex h-20 w-20 items-center justify-center rounded bg-slate-200 text-sm text-slate-500">No image</div>'
        }
        <div class="flex-1">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">${htmlspecialchars(product.category || '')}</p>
              <h3 class="mt-1 text-lg font-bold text-slate-900">${htmlspecialchars(product.name)}</h3>
            </div>
            <div class="rounded-2xl bg-white px-3 py-2 text-sm font-semibold text-slate-700">${formatCurrency(product.price)}</div>
          </div>
          <div class="mt-4">
            <label class="text-xs text-slate-500 mb-1 block">Quantity</label>
            <input type="number" name="qty[${product.id}]"
              value="0" min="0"
              class="w-24 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100" />
          </div>
        </div>
      </div>
    </article>
  `).join('');

  // Generate pagination HTML
  let paginationHTML = '';
  if (totalPages > 1) {
    paginationHTML = `
      <div class="mt-4 flex gap-2">
        ${Array.from({length: totalPages}, (_, i) => i + 1).map(pageNum => `
          <button
            type="button"
            onclick="performRealTimeSearch('${htmlspecialchars(searchQuery)}', ${pageNum})"
            class="px-3 py-1 rounded border ${pageNum == currentPage ? 'bg-slate-900 text-white' : 'bg-white'}">
            ${pageNum}
          </button>
        `).join('')}
      </div>
    `;
  }

  productsGrid.innerHTML = `
    <div class="grid gap-4 md:grid-cols-2">
      ${productsHTML}
    </div>
    ${paginationHTML}
  `;
  
  // Re-initialize cart update for new inputs
  updateCart();
}

// Helper function to escape HTML
function htmlspecialchars(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

// React to every keystroke / spinner change on any qty input
document.addEventListener("input", (event) => {
  if (event.target.matches('input[name^="qty["]')) {
    updateCart();
  }
  
  // Real-time search on product search input
  if (event.target.matches('input[name="product_search"]')) {
    clearTimeout(searchTimeout);
    const query = event.target.value.trim();
    
    // Debounce search to avoid too many requests
    searchTimeout = setTimeout(() => {
      performRealTimeSearch(query);
    }, 300); // Wait 300ms after user stops typing
  }
});

// Prevent form submission when pressing Enter in search field
document.addEventListener("keydown", (event) => {
  if (event.target.matches('input[name="product_search"]') && event.key === 'Enter') {
    event.preventDefault();
    const query = event.target.value.trim();
    performRealTimeSearch(query);
  }
});

// Run once on load to reflect any server-preserved values
updateCart();
