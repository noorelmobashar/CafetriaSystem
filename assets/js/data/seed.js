import {
  createAvatar,
  createEmptyCart,
  createIllustration,
  getDateDaysAgo,
  uid,
} from '../core/utils.js';

export function seedData() {
  const products = [
    {
      id: uid('product'),
      name: 'Tea',
      price: 5,
      category: 'Hot Drinks',
      image: createIllustration('Tea', '#0f766e', '#34d399', '🍵'),
    },
    {
      id: uid('product'),
      name: 'Coffee',
      price: 6,
      category: 'Hot Drinks',
      image: createIllustration('Coffee', '#7c3aed', '#a78bfa', '☕'),
    },
    {
      id: uid('product'),
      name: 'Nescafe',
      price: 8,
      category: 'Hot Drinks',
      image: createIllustration('Nescafe', '#ea580c', '#fdba74', '🥤'),
    },
    {
      id: uid('product'),
      name: 'Cola',
      price: 10,
      category: 'Cold Drinks',
      image: createIllustration('Cola', '#1d4ed8', '#60a5fa', '🧊'),
    },
  ];

  const users = [
    {
      id: uid('user'),
      role: 'admin',
      name: 'Admin User',
      email: 'admin@company.com',
      password: 'admin123',
      roomNo: 'Office Hub',
      ext: '100',
      avatar: createAvatar('Admin User', '#0f172a', '#334155'),
    },
    {
      id: uid('user'),
      role: 'customer',
      name: 'Alaa Hassan',
      email: 'employee@company.com',
      password: '123456',
      roomNo: 'Room 201',
      ext: '201',
      avatar: createAvatar('Alaa Hassan', '#2563eb', '#60a5fa'),
    },
    {
      id: uid('user'),
      role: 'customer',
      name: 'Mariam Adel',
      email: 'mariam@company.com',
      password: '123456',
      roomNo: 'Room 305',
      ext: '305',
      avatar: createAvatar('Mariam Adel', '#f97316', '#fdba74'),
    },
    {
      id: uid('user'),
      role: 'customer',
      name: 'Omar Samy',
      email: 'omar@company.com',
      password: '123456',
      roomNo: 'Room 118',
      ext: '118',
      avatar: createAvatar('Omar Samy', '#0f766e', '#6ee7b7'),
    },
  ];

  const [tea, coffee, nescafe, cola] = products;
  const [admin, alaa, mariam, omar] = users;

  const orders = [
    {
      id: uid('order'),
      userId: alaa.id,
      userName: alaa.name,
      room: alaa.roomNo,
      note: '1 Tea Extra Sugar',
      createdAt: getDateDaysAgo(0),
      status: 'processing',
      source: 'customer',
      items: [{ productId: tea.id, name: tea.name, price: tea.price, qty: 1, note: 'Extra Sugar' }],
      total: 5,
      createdBy: alaa.name,
    },
    {
      id: uid('order'),
      userId: mariam.id,
      userName: mariam.name,
      room: mariam.roomNo,
      note: 'Deliver quickly please',
      createdAt: getDateDaysAgo(1),
      status: 'out-for-delivery',
      source: 'customer',
      items: [{ productId: coffee.id, name: coffee.name, price: coffee.price, qty: 2, note: '' }],
      total: 12,
      createdBy: mariam.name,
    },
    {
      id: uid('order'),
      userId: omar.id,
      userName: omar.name,
      room: omar.roomNo,
      note: 'Manual bill assigned',
      createdAt: getDateDaysAgo(2),
      status: 'done',
      source: 'manual',
      items: [
        { productId: nescafe.id, name: nescafe.name, price: nescafe.price, qty: 1, note: '' },
        { productId: cola.id, name: cola.name, price: cola.price, qty: 1, note: '' },
      ],
      total: 18,
      createdBy: admin.name,
    },
    {
      id: uid('order'),
      userId: alaa.id,
      userName: alaa.name,
      room: alaa.roomNo,
      note: 'No ice',
      createdAt: getDateDaysAgo(0),
      status: 'incoming',
      source: 'customer',
      items: [{ productId: cola.id, name: cola.name, price: cola.price, qty: 1, note: 'No ice' }],
      total: 10,
      createdBy: alaa.name,
    },
  ];

  return {
    users,
    products,
    orders,
    customerCart: createEmptyCart(users.find((user) => user.role === 'customer')?.roomNo || ''),
    manualCart: { ...createEmptyCart(), userId: users.find((user) => user.role === 'customer')?.id || '' },
  };
}
