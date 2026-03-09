export const STORAGE_KEY = 'cafetria-system-v3';

export const statusMeta = {
  incoming: { label: 'Incoming', className: 'status-incoming' },
  processing: { label: 'Processing', className: 'status-processing' },
  'out-for-delivery': { label: 'Out for delivery', className: 'status-out-for-delivery' },
  done: { label: 'Done', className: 'status-done' },
  canceled: { label: 'Canceled', className: 'bg-slate-200 text-slate-700' },
};

export const PRODUCT_CATEGORIES = ['Hot Drinks', 'Cold Drinks', 'Snacks', 'Desserts'];

export const ROOM_OPTIONS = ['Room 101', 'Room 118', 'Room 201', 'Room 305', 'Meeting Room A', 'Office Hub'];

export function uid(prefix) {
  return `${prefix}-${Math.random().toString(36).slice(2, 10)}-${Date.now().toString(36)}`;
}

export function currency(value) {
  return `${Number(value || 0).toFixed(0)} LE`;
}

export function formatDateTime(value) {
  return new Intl.DateTimeFormat('en-GB', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value));
}

export function formatDateInput(value) {
  const date = new Date(value);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

export function getDateDaysAgo(days) {
  const date = new Date();
  date.setDate(date.getDate() - days);
  return date.toISOString();
}

export function createIllustration(label, colorA, colorB, icon) {
  const svg = `
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 240 180'>
      <defs>
        <linearGradient id='g' x1='0%' y1='0%' x2='100%' y2='100%'>
          <stop offset='0%' stop-color='${colorA}' />
          <stop offset='100%' stop-color='${colorB}' />
        </linearGradient>
      </defs>
      <rect width='240' height='180' rx='28' fill='url(#g)' />
      <circle cx='188' cy='42' r='30' fill='rgba(255,255,255,0.18)' />
      <circle cx='48' cy='142' r='36' fill='rgba(255,255,255,0.12)' />
      <text x='30' y='80' font-size='52'>${icon}</text>
      <text x='30' y='122' font-family='Arial, sans-serif' font-size='24' font-weight='700' fill='white'>${label}</text>
    </svg>
  `;
  return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;
}

export function createAvatar(name, toneA, toneB) {
  const initials = name
    .split(' ')
    .map((part) => part[0])
    .slice(0, 2)
    .join('')
    .toUpperCase();

  const svg = `
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 160 160'>
      <defs>
        <linearGradient id='avatar' x1='0%' y1='0%' x2='100%' y2='100%'>
          <stop offset='0%' stop-color='${toneA}' />
          <stop offset='100%' stop-color='${toneB}' />
        </linearGradient>
      </defs>
      <rect width='160' height='160' rx='48' fill='url(#avatar)' />
      <circle cx='80' cy='64' r='28' fill='rgba(255,255,255,0.22)' />
      <text x='50%' y='56%' dominant-baseline='middle' text-anchor='middle' font-family='Arial, sans-serif' font-size='42' font-weight='700' fill='white'>${initials}</text>
    </svg>
  `;

  return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;
}

export function createEmptyCart(defaultRoom = '') {
  return {
    room: defaultRoom,
    note: '',
    items: {},
  };
}
