const __MOCK_DATA__ = {
  inventory: [
    { id: 'ITM001', name: 'Silk Evening Gown', category: 'Dresses', price: 299.99, costPrice: 150.00, stock: 15, damaged: 0, branch: 'Piassa Branch', status: 'In Stock' },
    { id: 'ITM002', name: 'Leather Crossbody Bag', category: 'Accessories', price: 129.50, costPrice: 60.00, stock: 8, damaged: 1, branch: 'Bole Premium', status: 'Low Stock' },
    { id: 'ITM003', name: 'Classic Trench Coat', category: 'Outerwear', price: 189.00, costPrice: 100.00, stock: 24, damaged: 0, branch: 'Piassa Branch', status: 'In Stock' },
    { id: 'ITM004', name: 'Gold Plated Necklace', category: 'Jewelry', price: 75.00, costPrice: 30.00, stock: 2, damaged: 0, branch: 'Bole Premium', status: 'Low Stock' },
    { id: 'ITM005', name: 'Velvet Blazer', category: 'Jackets', price: 145.00, costPrice: 85.00, stock: 0, damaged: 2, branch: 'Piassa Branch', status: 'Out of Stock' },
  ],
  branches: [
    { id: 'BR001', name: 'Piassa Branch', manager: 'Frewu Zerihun', totalItems: 1450, totalSales: 'Br 24500' },
    { id: 'BR002', name: 'Bole Premium', manager: 'Haymanot Getachew', totalItems: 890, totalSales: 'Br 18200' },
  ],
  recentSales: [
    { id: 'TRX-1029', date: '2026-04-10', items: '2', total: 374.99, seller: 'Mariamawit Messay', branch: 'Piassa Branch' },
    { id: 'TRX-1028', date: '2026-04-09', items: '1', total: 129.50, seller: 'Kidus Yohannes', branch: 'Bole Premium' },
    { id: 'TRX-1027', date: '2026-04-09', items: '3', total: 563.00, seller: 'Radyat Daniel', branch: 'Piassa Branch' },
  ],
  users: [
    { id: 'USR-1', name: 'Adem Abe', role: 'manager', email: 'admin@boutique.com', password: 'password123', status: 'Active' },
    { id: 'USR-2', name: 'Abigiya Mulugeta', role: 'store_keeper', email: 'store@boutique.com', password: 'password123', status: 'Active' },
    { id: 'USR-3', name: 'Mariamawit Messay', role: 'seller', email: 'seller@boutique.com', password: 'password123', status: 'Active' },
    { id: 'USR-4', name: 'Frewu Zerihun', role: 'manager', email: 'frewu@boutique.com', password: 'password123', status: 'Active' },
    { id: 'USR-5', name: 'Haymanot Getachew', role: 'manager', email: 'haymanot@boutique.com', password: 'password123', status: 'Active' },
    { id: 'USR-6', name: 'Kidus Yohannes', role: 'seller', email: 'kidus@boutique.com', password: 'password123', status: 'Active' },
    { id: 'USR-7', name: 'Radyat Daniel', role: 'seller', email: 'radyat@boutique.com', password: 'password123', status: 'Active' },
  ]
};

window.MOCK_DATA = __MOCK_DATA__;
