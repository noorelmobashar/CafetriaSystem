let modalState = {
  config: null,
  fileData: null,
};

export function showToast(message, tone = 'default') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const tones = {
    default: 'bg-slate-900 text-white',
    success: 'bg-emerald-600 text-white',
    warning: 'bg-amber-500 text-slate-950',
    danger: 'bg-rose-600 text-white',
  };

  const toast = document.createElement('div');
  toast.className = `pointer-events-auto min-w-[18rem] rounded-2xl px-4 py-3 text-sm font-semibold shadow-soft ${tones[tone] || tones.default}`;
  toast.textContent = message;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(8px)';
    toast.style.transition = 'all 180ms ease';
  }, 2600);

  setTimeout(() => toast.remove(), 3000);
}

export function tableShell(head, body) {
  return `
    <div class='table-shell custom-scrollbar'>
      <table>
        <thead>${head}</thead>
        <tbody>${body}</tbody>
      </table>
    </div>
  `;
}

export function bindModalShell() {
  const overlay = document.getElementById('modal-overlay');
  const closeButton = document.getElementById('close-modal-btn');

  if (!overlay || !closeButton) return;

  closeButton.onclick = closeModal;
  overlay.onclick = (event) => {
    if (event.target === overlay) {
      closeModal();
    }
  };
}

export function modalInput(label, name, value = '', type = 'text', placeholder = '', required = true) {
  return `
    <div>
      <label class='mb-2 block text-sm font-semibold text-slate-700'>${label}</label>
      <input name='${name}' type='${type}' value='${value}' placeholder='${placeholder}' ${required ? 'required' : ''} class='w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100' />
    </div>
  `;
}

export function getModalFileData() {
  return modalState.fileData;
}

export function openModal(config) {
  modalState = {
    config,
    fileData: null,
  };

  const overlay = document.getElementById('modal-overlay');
  const form = document.getElementById('modal-form');
  const kicker = document.getElementById('modal-kicker');
  const title = document.getElementById('modal-title');

  kicker.textContent = config.kicker;
  title.textContent = config.title;
  form.innerHTML = config.render();
  overlay.classList.remove('hidden');
  overlay.classList.add('flex');

  const fileInput = form.querySelector('input[type="file"]');
  if (fileInput) {
    fileInput.onchange = async (event) => {
      const file = event.target.files?.[0];
      if (!file) return;
      modalState.fileData = await readFileAsDataUrl(file);
      showToast('Image selected successfully.', 'success');
    };
  }

  form.onsubmit = async (event) => {
    event.preventDefault();
    await config.onSubmit(new FormData(form));
  };
}

export function closeModal() {
  modalState = {
    config: null,
    fileData: null,
  };

  const overlay = document.getElementById('modal-overlay');
  if (!overlay) return;
  overlay.classList.add('hidden');
  overlay.classList.remove('flex');
}

function readFileAsDataUrl(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
}
