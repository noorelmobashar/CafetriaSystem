export const appContext = {
  basePath: document.body.dataset.basePath || '.',
  page: document.body.dataset.page || '',
  role: document.body.dataset.role || 'guest',
};

export function appUrl(path = '') {
  const cleanedBase = appContext.basePath.replace(/\/$/, '');
  const cleanedPath = String(path).replace(/^\//, '');
  return cleanedPath ? `${cleanedBase}/${cleanedPath}` : cleanedBase;
}
