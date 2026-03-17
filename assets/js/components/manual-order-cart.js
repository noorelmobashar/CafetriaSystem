/**
 * manual-order-cart.js
 * Live-updates the cart summary and total whenever a product qty input changes.
 * Reads product metadata from data attributes on each <article> card.
 */

function formatCurrency(amount) {
  return (
    Number(amount).toLocaleString("en-EG", { maximumFractionDigits: 0 }) + " LE"
  );
}

const PRODUCTS_PER_PAGE = 5;

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

// React to every keystroke / spinner change on any qty input
document.addEventListener("input", (event) => {
  if (event.target.matches('input[name^="qty["]')) {
    updateCart();
  }
});

// Run once on load to reflect any server-preserved values
updateCart();

// ── Client-side search + pagination (no page reload) ──────────────────────────

const productGrid = document.getElementById("product-grid");
const paginationContainer = document.getElementById("product-pagination");
const searchInput = document.getElementById("product-search-input");
const emptyState = document.getElementById("product-grid-empty");
const allCards = Array.from(
  document.querySelectorAll("#product-grid article[data-product-id]"),
);

let filteredCards = [...allCards];
let currentPage = 1;

function renderPagination(totalPages) {
  if (!paginationContainer) return;

  paginationContainer.innerHTML = "";

  if (filteredCards.length === 0 || totalPages <= 1) {
    return;
  }

  for (let i = 1; i <= totalPages; i += 1) {
    const button = document.createElement("button");
    button.type = "button";
    button.textContent = String(i);
    button.className =
      "px-3 py-1 rounded border " +
      (i === currentPage ? "bg-slate-900 text-white" : "bg-white");

    button.addEventListener("click", () => {
      currentPage = i;
      applyPagination();
    });

    paginationContainer.appendChild(button);
  }
}

function applyPagination() {
  if (!productGrid) return;

  allCards.forEach((card) => {
    card.style.display = "none";
  });

  const totalPages = Math.max(
    1,
    Math.ceil(filteredCards.length / PRODUCTS_PER_PAGE),
  );

  if (currentPage > totalPages) {
    currentPage = totalPages;
  }

  const start = (currentPage - 1) * PRODUCTS_PER_PAGE;
  const end = start + PRODUCTS_PER_PAGE;
  filteredCards.slice(start, end).forEach((card) => {
    card.style.display = "";
  });

  if (emptyState) {
    emptyState.style.display =
      filteredCards.length === 0 && allCards.length > 0 ? "" : "none";
  }

  renderPagination(totalPages);
}

if (searchInput) {
  searchInput.addEventListener("input", (event) => {
    const query = event.target.value.trim().toLowerCase();
    filteredCards = allCards.filter((article) => {
      const name = (article.dataset.productName ?? "").toLowerCase();
      const category = (article.dataset.productCategory ?? "").toLowerCase();
      return !query || name.includes(query) || category.includes(query);
    });

    currentPage = 1;
    applyPagination();
  });
}

if (allCards.length > 0) {
  currentPage = 1;
  applyPagination();
}
