/**
 * customer-menu-cart.js
 * Client-side cart for the customer menu.
 * Products are PHP-rendered with data-* attributes.
 * Cart state lives in JS; the order is submitted via #order-form.
 */

const cart = {}; // { [productId]: { qty, note } }

function formatCurrency(amount) {
  return (
    Number(amount).toLocaleString("en-EG", { maximumFractionDigits: 0 }) + " LE"
  );
}

function getCartEntries() {
  return Object.entries(cart)
    .filter(([, item]) => item.qty > 0)
    .map(([productId, item]) => {
      const article = document.querySelector(
        `article[data-product-id="${productId}"]`,
      );
      return {
        productId,
        name: article?.dataset.productName ?? "",
        price: parseFloat(article?.dataset.productPrice ?? "0"),
        qty: item.qty,
        note: item.note || "",
      };
    });
}

function renderCartSidebar() {
  const entries = getCartEntries();
  const total = entries.reduce((sum, item) => sum + item.price * item.qty, 0);
  const itemCount = entries.reduce((sum, item) => sum + item.qty, 0);
  const room = document.getElementById("room-select")?.value ?? "";

  const cartItemsEl = document.getElementById("cart-items");
  if (cartItemsEl) {
    cartItemsEl.innerHTML = entries.length
      ? entries
          .map(
            (item) => `
          <div class="rounded-2xl bg-white/10 p-4">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="font-semibold text-white">${item.name}</p>
                <p class="mt-1 text-sm text-slate-300">${item.qty} × ${formatCurrency(item.price)}</p>
                ${item.note ? `<p class="mt-2 text-xs text-slate-300">${item.note}</p>` : ""}
              </div>
              <div class="text-sm font-semibold text-white">${formatCurrency(item.qty * item.price)}</div>
            </div>
          </div>
        `,
          )
          .join("")
      : '<div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300">No items selected yet.</div>';
  }

  const summaryRoom = document.getElementById("summary-room");
  const summaryCount = document.getElementById("summary-items-count");
  const summaryTotal = document.getElementById("summary-total");
  if (summaryRoom) summaryRoom.textContent = room ? `Room ${room}` : "-";
  if (summaryCount) summaryCount.textContent = String(itemCount);
  if (summaryTotal) summaryTotal.textContent = formatCurrency(total);
}

function updateQtyDisplay(productId) {
  const qty = cart[productId]?.qty ?? 0;
  const article = document.querySelector(
    `article[data-product-id="${productId}"]`,
  );
  const span = article?.querySelector("[data-qty-display]");
  if (span) span.textContent = String(qty);
  
  // Disable minus button when qty is 0
  const decreaseBtn = article?.querySelector('[data-action="decrease"]');
  if (decreaseBtn) {
    if (qty === 0) {
      decreaseBtn.disabled = true;
      decreaseBtn.style.opacity = '0.5';
      decreaseBtn.style.cursor = 'not-allowed';
    } else {
      decreaseBtn.disabled = false;
      decreaseBtn.style.opacity = '1';
      decreaseBtn.style.cursor = 'pointer';
    }
  }
}

// +/- buttons
document.getElementById("menu-grid")?.addEventListener("click", (event) => {
  const button = event.target.closest("[data-action]");
  if (!button) return;
  const productId = button.dataset.productId;
  if (!cart[productId]) cart[productId] = { qty: 0, note: "" };
  if (button.dataset.action === "increase") cart[productId].qty += 1;
  if (button.dataset.action === "decrease")
    cart[productId].qty = Math.max(0, cart[productId].qty - 1);
  updateQtyDisplay(productId);
  renderCartSidebar();
});

// Item notes
document.getElementById("menu-grid")?.addEventListener("input", (event) => {
  const input = event.target.closest("[data-note-input]");
  if (!input) return;
  const productId = input.dataset.productId;
  if (!cart[productId]) cart[productId] = { qty: 0, note: "" };
  cart[productId].note = input.value;
  renderCartSidebar();
});

// Room change → update sidebar label
document
  .getElementById("room-select")
  ?.addEventListener("change", renderCartSidebar);

// Client-side product search on PHP-rendered articles
document
  .getElementById("product-search")
  ?.addEventListener("input", (event) => {
    const query = event.target.value.trim().toLowerCase();
    const articles = document.querySelectorAll(
      "#menu-grid article[data-product-id]",
    );
    let visible = 0;

    articles.forEach((article) => {
      const name = (article.dataset.productName ?? "").toLowerCase();
      const category = (article.dataset.productCategory ?? "").toLowerCase();
      const matches =
        !query || name.includes(query) || category.includes(query);
      article.style.display = matches ? "" : "none";
      if (matches) visible++;
    });

    const emptyState = document.getElementById("menu-grid-empty");
    if (emptyState)
      emptyState.style.display =
        visible === 0 && articles.length > 0 ? "" : "none";
  });

// Confirm order
document.getElementById("place-order-btn")?.addEventListener("click", () => {
  const entries = getCartEntries();

  if (!entries.length) {
    const btn = document.getElementById("place-order-btn");
    const origText = btn.textContent;
    const origClass = btn.className;
    
    // Show error state with red background
    btn.textContent = "❌ Add at least one product first";
    btn.className = origClass + " bg-red-500 hover:bg-red-600";
    btn.disabled = true;
    
    setTimeout(() => {
      btn.textContent = origText;
      btn.className = origClass;
      btn.disabled = false;
    }, 2500);
    return;
  }

  const form = document.getElementById("order-form");

  // Remove any previously injected cart inputs
  form.querySelectorAll("[data-cart-input]").forEach((el) => el.remove());

  entries.forEach((item) => {
    const qtyInput = document.createElement("input");
    qtyInput.type = "hidden";
    qtyInput.name = `qty[${item.productId}]`;
    qtyInput.value = String(item.qty);
    qtyInput.setAttribute("data-cart-input", "true");
    form.appendChild(qtyInput);

    if (item.note) {
      const noteInput = document.createElement("input");
      noteInput.type = "hidden";
      noteInput.name = `item_note[${item.productId}]`;
      noteInput.value = item.note;
      noteInput.setAttribute("data-cart-input", "true");
      form.appendChild(noteInput);
    }
  });

  form.submit();
});

// Initial render
renderCartSidebar();
