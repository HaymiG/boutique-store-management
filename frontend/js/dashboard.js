// ===================================
// DASHBOARD.JS — All Dashboard Logic
// Handles auth check, role UI, CRUD, POS
// ===================================

// Database persistence
window.saveDB = function() {
  localStorage.setItem('BoutiqueDB', JSON.stringify(window.MOCK_DATA));
};

document.addEventListener('DOMContentLoaded', () => {
  lucide.createIcons();

  // Database State
  const saved = localStorage.getItem('BoutiqueDB');
  if (saved) {
    window.MOCK_DATA = JSON.parse(saved);
  } else {
    window.saveDB();
  }

  // Auth Check - Verify with backend
  const userId = localStorage.getItem('userId');
  if (!userId) {
    window.location.href = '/login';
    return;
  }

  // Verify auth with backend
  fetch('/api/user', {
    method: 'GET',
    headers: {
      'X-CSRF-Token': localStorage.getItem('csrfToken') || ''
    }
  })
  .then(response => {
    if (!response.ok) throw new Error('Unauthorized');
    return response.json();
  })
  .then(data => {
    if (data.user) {
      const userRole = data.user.role_id;
      setupRoleUI(userRole);
      setupMobileMenu();
    }
  })
  .catch(error => {
    console.error('Auth verification failed:', error);
    localStorage.removeItem('userId');
    localStorage.removeItem('userRole');
    localStorage.removeItem('userName');
    localStorage.removeItem('csrfToken');
    window.location.href = '/login';
  });
});

// ——— Role-Based UI Setup ———
function setupRoleUI(role) {
  const userNameEl = document.getElementById('userNameDisplay');
  const userRoleEl = document.getElementById('userRoleDisplay');
  const userAvatarEl = document.getElementById('userAvatar');
  const sidebarNav = document.getElementById('sidebarNav');

  const localName = localStorage.getItem('userName');
  let navItems = [];
  let defaultView = '';
  let activeUserNameForFilter = localName || '';

  // Convert role_id to role name for comparison
  // 1 = manager, 2 = store_keeper, 3 = seller
  if (role === 1 || role === 'manager') {
    userNameEl.textContent = localName || 'Admin Master';
    userRoleEl.textContent = 'Manager';
    navItems = [
      { id: 'overview', icon: 'layout-dashboard', label: 'Overview' },
      { id: 'branches', icon: 'store', label: 'Branches' },
      { id: 'users', icon: 'users', label: 'Users' },
      { id: 'inventory', icon: 'package-search', label: 'Inventory' },
      { id: 'reports', icon: 'bar-chart-2', label: 'Analytics' }
    ];
    defaultView = 'overview';
    renderRecentSales();
    renderBranches();
    renderUsers();

  } else if (role === 2 || role === 'store_keeper') {
    userNameEl.textContent = localName || 'Alice Smith';
    userRoleEl.textContent = 'Store Keeper';
    navItems = [
      { id: 'inventory', icon: 'package', label: 'Stock' },
      { id: 'stock-ops', icon: 'bell', label: 'Alerts' },
      { id: 'my-sales', icon: 'activity', label: 'Performance' }
    ];
    defaultView = 'inventory';
    document.getElementById('addItemBtn').style.display = 'inline-flex';
    document.getElementById('addItemBtn').onclick = createItem;
    renderStockAlerts();
    renderMySales(activeUserNameForFilter);

  } else if (role === 3 || role === 'seller') {
    userNameEl.textContent = localName || 'Sarah Connor';
    userRoleEl.textContent = 'Seller';
    navItems = [
      { id: 'pos', icon: 'credit-card', label: 'POS' },
      { id: 'inventory', icon: 'search', label: 'Items' },
      { id: 'my-sales', icon: 'activity', label: 'Performance' }
    ];
    defaultView = 'pos';
    renderPOSItems();
    renderMySales(activeUserNameForFilter);

    const cartSummary = document.querySelector('.cart-summary');
    if (!document.getElementById('discountBtn')) {
      const discBtn = document.createElement('button');
      discBtn.id = 'discountBtn';
      discBtn.className = 'btn btn-outline';
      discBtn.style.width = '100%';
      discBtn.style.marginBottom = '1rem';
      discBtn.textContent = 'Apply Discount';
      discBtn.onclick = applyDiscount;
      cartSummary.insertBefore(discBtn, document.getElementById('checkoutBtn'));
    }
  }

  // Avatar
  const finalName = userNameEl.textContent;
  const initials = finalName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
  userAvatarEl.textContent = initials;

  // Render Nav
  navItems.forEach(item => {
    const btn = document.createElement('div');
    btn.className = 'nav-item';
    btn.id = `nav-${item.id}`;
    btn.innerHTML = `<i data-lucide="${item.icon}" class="nav-icon"></i> <span>${item.label}</span>`;
    btn.onclick = () => switchView(item.id);
    sidebarNav.appendChild(btn);
  });

  lucide.createIcons();
  renderInventoryTable();
  switchView(defaultView);
}

// ——— View Switching ———
function switchView(viewId) {
  document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
  const target = document.getElementById(`view-${viewId}`);
  if (target) target.classList.add('active');

  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
  const nav = document.getElementById(`nav-${viewId}`);
  if (nav) nav.classList.add('active');

  document.getElementById('sidebar').classList.remove('open');
}

function logout() {
  // Call API logout endpoint
  fetch('/api/logout', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': localStorage.getItem('csrfToken') || ''
    }
  })
  .then(response => response.json())
  .then(data => {
    // Clear local storage
    localStorage.removeItem('userRole');
    localStorage.removeItem('userName');
    localStorage.removeItem('userId');
    localStorage.removeItem('csrfToken');
    
    // Redirect to login
    window.location.href = '/login';
  })
  .catch(error => {
    console.error('Logout error:', error);
    
    // Force logout anyway
    localStorage.removeItem('userRole');
    localStorage.removeItem('userName');
    localStorage.removeItem('userId');
    localStorage.removeItem('csrfToken');
    
    window.location.href = '/login';
  });
}

// ——— Inventory ———
function renderInventoryTable() {
  const tbody = document.querySelector('#inventoryTable tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  const inventory = window.MOCK_DATA.inventory || [];
  const userRole = parseInt(localStorage.getItem('userRole')) || localStorage.getItem('userRole');

  inventory.forEach((item, index) => {
    let statusClass = 'badge-success';
    if (item.stock < 10) statusClass = 'badge-warning';
    if (item.stock === 0) statusClass = 'badge-danger';

    let actionBtns = '';
    if (userRole === 2 || userRole === 'store_keeper') {
      actionBtns = `<button class="btn btn-outline" style="padding:0.25rem 0.75rem;font-size:0.75rem" onclick="updateItemQty(${index})">Update</button>`;
    } else if (userRole === 1 || userRole === 'manager') {
      actionBtns = `<button class="btn btn-outline" style="padding:0.25rem 0.75rem;font-size:0.75rem" onclick="transferItem(${index})">Transfer</button>`;
    }

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="color:var(--text-secondary)">${item.id}</td>
      <td style="font-weight:600">${item.name}</td>
      <td>${item.category}</td>
      <td>$${item.price.toFixed(2)}</td>
      <td>${item.stock}</td>
      <td><span class="badge ${statusClass}">${item.stock === 0 ? 'Out' : (item.stock < 10 ? 'Low' : 'In Stock')}</span></td>
      <td>${item.branch}</td>
      ${actionBtns ? `<td>${actionBtns}</td>` : ''}
    `;
    tbody.appendChild(tr);
  });
}

function updateItemQty(index) {
  const item = window.MOCK_DATA.inventory[index];
  const newQty = prompt(`Update stock for ${item.name} (Current: ${item.stock}):`, item.stock);
  if (newQty !== null && !isNaN(newQty)) {
    window.MOCK_DATA.inventory[index].stock = parseInt(newQty);
    window.saveDB();
    renderInventoryTable();
    checkAlerts();
  }
}

function transferItem(index) {
  const item = window.MOCK_DATA.inventory[index];
  const newBranch = prompt(`Transfer ${item.name} to branch (Current: ${item.branch}):`, '');
  if (newBranch) {
    window.MOCK_DATA.inventory[index].branch = newBranch;
    window.saveDB();
    renderInventoryTable();
  }
}

function createItem() {
  const name = prompt('Item Name:');
  const price = prompt('Price:');
  const qty = prompt('Initial Quantity:');
  if (name && price && qty) {
    window.MOCK_DATA.inventory.push({
      id: 'ITM' + Math.floor(Math.random() * 1000),
      name, category: 'General', price: parseFloat(price),
      stock: parseInt(qty), branch: 'Headquarters',
      status: parseInt(qty) > 0 ? 'In Stock' : 'Out of Stock'
    });
    window.saveDB();
    renderInventoryTable();
  }
}

// ——— Sales ———
function renderRecentSales() {
  const tbody = document.querySelector('#recentSalesTable tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  window.MOCK_DATA.recentSales.forEach(sale => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="color:var(--text-secondary)">${sale.date}</td>
      <td>${sale.id}</td>
      <td>${sale.seller}</td>
      <td>${sale.branch}</td>
      <td style="font-weight:700">$${sale.total.toFixed(2)}</td>
    `;
    tbody.appendChild(tr);
  });
}

// ——— POS ———
let cart = [];

function renderPOSItems() {
  const grid = document.getElementById('posProductGrid');
  if (!grid) return;
  grid.innerHTML = '';
  window.MOCK_DATA.inventory.forEach(item => {
    if (item.stock === 0) return;
    const div = document.createElement('div');
    div.className = 'product-card';
    div.onclick = () => addToCart(item);
    div.innerHTML = `
      <div class="product-img d-flex justify-center align-center" style="color:var(--text-secondary)">
        <i data-lucide="shopping-bag"></i>
      </div>
      <div style="font-weight:600;font-size:0.875rem;margin-bottom:0.2rem">${item.name}</div>
      <div style="color:var(--text-secondary);font-size:0.8rem">$${item.price.toFixed(2)}</div>
    `;
    grid.appendChild(div);
  });
  lucide.createIcons();
}

function addToCart(item) {
  const existing = cart.find(c => c.id === item.id);
  if (existing) {
    if (existing.quantity < item.stock) existing.quantity += 1;
    else alert('Not enough stock!');
  } else {
    cart.push({ ...item, quantity: 1 });
  }
  updateCartUI();
}

function updateCartUI() {
  const container = document.getElementById('cartItemsContainer');
  if (cart.length === 0) {
    container.innerHTML = '<div style="text-align:center;color:var(--text-muted);margin-top:2rem;font-size:0.875rem">Cart is empty</div>';
    document.getElementById('cartSubtotal').textContent = '$0.00';
    document.getElementById('cartTotal').textContent = '$0.00';
    return;
  }
  container.innerHTML = '';
  let subtotal = 0;
  cart.forEach((item, index) => {
    const total = item.price * item.quantity;
    subtotal += total;
    const div = document.createElement('div');
    div.className = 'cart-item';
    div.innerHTML = `
      <div style="flex:1">
        <div style="font-size:0.875rem;font-weight:600">${item.name}</div>
        <div style="font-size:0.75rem;color:var(--text-secondary)">$${item.price.toFixed(2)} × ${item.quantity}</div>
      </div>
      <div style="font-weight:700">$${total.toFixed(2)}</div>
      <button class="btn" style="padding:0.25rem;color:var(--danger)" onclick="removeFromCart(${index})">
        <i data-lucide="trash-2" style="width:16px;height:16px"></i>
      </button>
    `;
    container.appendChild(div);
  });
  lucide.createIcons();
  document.getElementById('cartSubtotal').textContent = `$${subtotal.toFixed(2)}`;
  document.getElementById('cartTotal').textContent = `$${subtotal.toFixed(2)}`;
}

function removeFromCart(index) { cart.splice(index, 1); updateCartUI(); }

function processCheckout() {
  if (cart.length === 0) return alert('Cart is empty.');
  alert(`Payment processed! Total: ${document.getElementById('cartTotal').textContent}`);
  cart = [];
  updateCartUI();
}

// ——— Branches ———
function renderBranches() {
  const tbody = document.querySelector('#branchesTable tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  (window.MOCK_DATA.branches || []).forEach((b, i) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="color:var(--text-secondary)">${b.id}</td>
      <td style="font-weight:600">${b.name}</td>
      <td>${b.manager}</td>
      <td>${b.totalItems}</td>
      <td><button class="btn btn-danger" style="padding:0.25rem 0.75rem;font-size:0.75rem" onclick="deleteBranch(${i})">Delete</button></td>
    `;
    tbody.appendChild(tr);
  });
}

function createBranch() {
  const name = prompt('Branch Name:');
  const mgr = prompt('Manager Name:');
  if (name && mgr) {
    window.MOCK_DATA.branches.push({ id: 'BR' + Math.floor(Math.random() * 1000), name, manager: mgr, totalItems: 0, totalSales: '$0' });
    window.saveDB();
    renderBranches();
  }
}

function deleteBranch(index) {
  if (confirm('Delete this branch?')) {
    window.MOCK_DATA.branches.splice(index, 1);
    window.saveDB();
    renderBranches();
  }
}

// ——— Users ———
function renderUsers() {
  const tbody = document.querySelector('#usersTable tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  (window.MOCK_DATA.users || []).forEach((u, i) => {
    const btnText = u.status === 'Active' ? 'Deactivate' : 'Activate';
    const btnClass = u.status === 'Active' ? 'btn-danger' : 'btn-primary';
    const statusClass = u.status === 'Active' ? 'badge-success' : 'badge-danger';
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="color:var(--text-secondary)">${u.id}</td>
      <td style="font-weight:600">${u.name}</td>
      <td>${u.email}</td>
      <td style="text-transform:capitalize">${u.role.replace('_', ' ')}</td>
      <td><span class="badge ${statusClass}">${u.status}</span></td>
      <td><button class="btn ${btnClass}" style="padding:0.25rem 0.75rem;font-size:0.75rem" onclick="toggleUserStatus(${i})">${btnText}</button></td>
    `;
    tbody.appendChild(tr);
  });
}

function createUser() {
  const name = prompt('Full Name:');
  const email = prompt('Email:');
  const role = prompt('Role (manager / store_keeper / seller):');
  if (name && email && role) {
    window.MOCK_DATA.users.push({ id: 'USR-' + Math.floor(Math.random() * 1000), name, email, role, password: 'password123', status: 'Active' });
    window.saveDB();
    renderUsers();
  }
}

function toggleUserStatus(index) {
  const u = window.MOCK_DATA.users[index];
  u.status = u.status === 'Active' ? 'Inactive' : 'Active';
  window.saveDB();
  renderUsers();
}

// ——— Reports ———
function generateReport(type) {
  const log = document.getElementById('reportLog');
  const title = document.getElementById('reportTitle');
  if (type === 'daily') {
    title.textContent = 'Daily Sales — ' + new Date().toLocaleDateString();
    log.textContent = JSON.stringify(window.MOCK_DATA.recentSales.slice(0, 2), null, 2);
  } else if (type === 'weekly') {
    title.textContent = 'Weekly Revenue';
    log.textContent = 'Downtown: $24,500\nUptown: $18,200\n\nTop Seller: Sarah Connor';
  } else if (type === 'inventory') {
    title.textContent = 'Inventory Snapshot';
    const fast = window.MOCK_DATA.inventory.filter(i => i.stock < 10);
    const slow = window.MOCK_DATA.inventory.filter(i => i.stock >= 10);
    log.textContent = 'LOW STOCK:\n' + JSON.stringify(fast, null, 2) + '\n\nHEALTHY:\n' + JSON.stringify(slow, null, 2);
  }
}

// ——— Stock Alerts ———
function renderStockAlerts() {
  const container = document.getElementById('stockAlertsContainer');
  if (!container) return;
  const low = window.MOCK_DATA.inventory.filter(i => i.stock < 5);
  if (low.length === 0) {
    container.innerHTML = '<p style="color:var(--success)">All stock levels are optimal.</p>';
    return;
  }
  container.innerHTML = low.map(item => `
    <div class="data-panel" style="margin-bottom:1rem;border-left:3px solid var(--warning)">
      <h4 style="color:var(--warning);font-size:0.875rem;font-weight:600;margin-bottom:0.25rem">Low Stock: ${item.name}</h4>
      <p style="font-size:0.8rem;color:var(--text-secondary)">Only ${item.stock} left in ${item.branch}. SKU: ${item.id}</p>
    </div>
  `).join('');
}

function checkAlerts() {
  if (document.getElementById('view-stock-ops')?.classList.contains('active')) renderStockAlerts();
}

// ——— My Sales ———
function renderMySales(userName) {
  const tbody = document.querySelector('#mySalesTable tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  const mySales = (window.MOCK_DATA.recentSales || []).filter(s => s.seller === userName);
  if (mySales.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--text-muted)">No sales recorded yet.</td></tr>';
    return;
  }
  mySales.forEach(s => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="color:var(--text-secondary)">${s.date}</td>
      <td>${s.id}</td>
      <td>${s.branch}</td>
      <td style="font-weight:700">$${s.total.toFixed(2)}</td>
    `;
    tbody.appendChild(tr);
  });
}

// ——— Discounts ———
function applyDiscount() {
  if (cart.length === 0) return alert('Add items first.');
  const code = prompt('Discount Code (e.g., VIP10):');
  if (code) {
    alert('Discount applied! 10% off total.');
    const el = document.getElementById('cartTotal');
    const cur = parseFloat(el.textContent.replace('$', ''));
    el.textContent = '$' + (cur * 0.9).toFixed(2);
  }
}

// ——— Mobile Menu ———
function setupMobileMenu() {
  const sidebar = document.getElementById('sidebar');
  const openBtn = document.getElementById('mobileMenuBtn');
  const closeBtn = document.getElementById('mobileMenuClose');

  const toggle = () => {
    const mobile = window.innerWidth <= 768;
    openBtn.style.display = mobile ? 'block' : 'none';
    closeBtn.style.display = mobile ? 'block' : 'none';
    if (!mobile) sidebar.classList.remove('open');
  };

  toggle();
  window.addEventListener('resize', toggle);
  openBtn.onclick = () => sidebar.classList.add('open');
  closeBtn.onclick = () => sidebar.classList.remove('open');
}
