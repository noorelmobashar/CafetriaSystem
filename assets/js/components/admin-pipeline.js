import { state, updateOrderStatus } from '../core/store.js';
import { currency, formatDateTime, statusMeta } from '../core/utils.js';
import { showToast } from './shared.js';

export function initAdminPipelinePage() {
  renderPipeline();
  bindPipelineActions();
}

function getBuckets() {
  const activeOrders = state.data.orders.filter((order) => order.status !== 'canceled');
  return {
    incoming: activeOrders.filter((order) => order.status === 'incoming'),
    processing: activeOrders.filter((order) => order.status === 'processing'),
    delivery: activeOrders.filter((order) => order.status === 'out-for-delivery'),
    done: activeOrders.filter((order) => order.status === 'done'),
  };
}

function renderPipeline() {
  const buckets = getBuckets();
  document.getElementById('stat-incoming').textContent = String(buckets.incoming.length);
  document.getElementById('stat-processing').textContent = String(buckets.processing.length);
  document.getElementById('stat-delivery').textContent = String(buckets.delivery.length);
  document.getElementById('stat-done').textContent = String(buckets.done.length);
  document.getElementById('incoming-count').textContent = String(buckets.incoming.length);
  document.getElementById('processing-count').textContent = String(buckets.processing.length);
  document.getElementById('delivery-count').textContent = String(buckets.delivery.length);
  document.getElementById('done-count').textContent = String(buckets.done.length);

  renderColumn('incoming-list', buckets.incoming, 'processing', 'Start processing');
  renderColumn('processing-list', buckets.processing, 'out-for-delivery', 'Send to delivery');
  renderColumn('delivery-list', buckets.delivery, 'done', 'Mark as done');
  renderColumn('done-list', buckets.done);
}

function renderColumn(containerId, orders, nextStatus = null, actionLabel = '') {
  const container = document.getElementById(containerId);
  container.innerHTML = orders.length
    ? orders
        .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
        .map((order) => {
          const status = statusMeta[order.status];
          return `
            <article class='card-hover rounded-[1.5rem] border border-slate-200 bg-white p-4'>
              <div class='flex items-start justify-between gap-3'>
                <div>
                  <h3 class='text-base font-bold text-slate-900'>${order.userName}</h3>
                  <p class='mt-1 text-sm text-slate-500'>${order.room} • ${formatDateTime(order.createdAt)}</p>
                </div>
                <span class='status-pill ${status.className}'>${status.label}</span>
              </div>
              <div class='mt-4 space-y-2 text-sm text-slate-600'>
                ${order.items.map((item) => `<div class='flex items-center justify-between gap-3'><span>${item.qty} × ${item.name}</span><span class='font-semibold text-slate-800'>${currency(item.price * item.qty)}</span></div>`).join('')}
              </div>
              ${order.note ? `<p class='mt-4 rounded-2xl bg-slate-50 px-3 py-2 text-sm text-slate-600'>${order.note}</p>` : ''}
              <div class='mt-4 flex items-center justify-between'>
                <p class='text-lg font-bold text-slate-900'>${currency(order.total)}</p>
                ${nextStatus ? `<button data-order-id='${order.id}' data-next-status='${nextStatus}' class='rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800'>${actionLabel}</button>` : `<span class='rounded-2xl bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700'>Completed</span>`}
              </div>
            </article>
          `;
        })
        .join('')
    : "<div class='rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500'>No orders in this stage.</div>";
}

function bindPipelineActions() {
  ['incoming-list', 'processing-list', 'delivery-list', 'done-list'].forEach((id) => {
    document.getElementById(id).onclick = (event) => {
      const button = event.target.closest('[data-next-status]');
      if (!button) return;
      const order = updateOrderStatus(button.dataset.orderId, button.dataset.nextStatus);
      if (!order) return;
      renderPipeline();
      bindPipelineActions();
      showToast(`Order moved to ${statusMeta[order.status].label}.`, 'success');
    };
  });
}
