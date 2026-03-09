window.tailwind = window.tailwind || {};
window.tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
      colors: {
        brand: {
          50: '#eff6ff',
          100: '#dbeafe',
          500: '#2563eb',
          600: '#1d4ed8',
          700: '#1e40af',
        },
        cafe: {
          100: '#fff7ed',
          200: '#ffedd5',
          400: '#fb923c',
          500: '#f97316',
          600: '#ea580c',
        },
        pine: {
          500: '#0f766e',
          600: '#115e59',
        },
      },
      boxShadow: {
        soft: '0 20px 45px rgba(15, 23, 42, 0.10)',
        glow: '0 25px 50px rgba(37, 99, 235, 0.18)',
      },
      backgroundImage: {
        mesh: 'radial-gradient(circle at top left, rgba(37,99,235,0.22), transparent 28%), radial-gradient(circle at top right, rgba(249,115,22,0.16), transparent 22%), linear-gradient(135deg, rgba(255,255,255,0.97), rgba(248,250,252,0.92))',
      },
    },
  },
};
