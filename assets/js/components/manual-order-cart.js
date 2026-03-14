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
