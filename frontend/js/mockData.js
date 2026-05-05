// Dummy Data representing database responses

const __MOCK_DATA__ = {
  inventory: [
    { id: 'ITM001', name: 'Silk Evening Gown', category: 'Dresses', price: 299.99, stock: 15, branch: 'Downtown', status: 'In Stock' },
    { id: 'ITM002', name: 'Leather Crossbody Bag', category: 'Accessories', price: 129.50, stock: 8, branch: 'Uptown', status: 'Low Stock' },
    { id: 'ITM003', name: 'Classic Trench Coat', category: 'Outerwear', price: 189.00, stock: 24, branch: 'Downtown', status: 'In Stock' },
    { id: 'ITM004', name: 'Gold Plated Necklace', category: 'Jewelry', price: 75.00, stock: 2, branch: 'Uptown', status: 'Low Stock' },
    { id: 'ITM005', name: 'Velvet Blazer', category: 'Jackets', price: 145.00, stock: 0, branch: 'Downtown', status: 'Out of Stock' },
  ],
  branches: [
    { id: 'BR001', name: 'Downtown Boutique', manager: 'Alice Smith', totalItems: 1450, totalSales: '$24,500' },
    { id: 'BR002', name: 'Uptown Premium', manager: 'Bob Jones', totalItems: 890, totalSales: '$18,200' },
  ],
  recentSales: [
    { id: 'TRX-1029', date: '2026-04-10', items: '2', total: 374.99, seller: 'Sarah Connor', branch: 'Downtown' },
    { id: 'TRX-1028', date: '2026-04-09', items: '1', total: 129.50, seller: 'John Doe', branch: 'Uptown' },
    { id: 'TRX-1027', date: '2026-04-09', items: '3', total: 563.00, seller: 'Sarah Connor', branch: 'Downtown' },
  ],
  users: [
    { id: 'USR-1', name: 'Admin Master', role: 'manager', email: 'admin@boutique.com', password: 'password123', status: 'Active' },
    { id: 'USR-2', name: 'Alice Smith', role: 'store_keeper', email: 'store@boutique.com', password: 'password123', status: 'Active' },
    { id: 'USR-3', name: 'Sarah Connor', role: 'seller', email: 'seller@boutique.com', password: 'password123', status: 'Active' },
  ]
};

// Expose globally for app.js
window.MOCK_DATA = __MOCK_DATA__;
