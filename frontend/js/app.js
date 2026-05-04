// Main Application Logic - BoutiqueStore Management System

window.saveDB = function() {
  localStorage.setItem('BoutiqueDB', JSON.stringify(window.MOCK_DATA));
};

document.addEventListener('DOMContentLoaded', () => {
  // Initialize Icons
  lucide.createIcons();

  // Database State Initialization
  const saved = localStorage.getItem('BoutiqueDB');
  if (saved) {
    window.MOCK_DATA = JSON.parse(saved);
  } else {
    window.saveDB();
  }

  // 1. Authentication Check & Initialization
  const userRole = localStorage.getItem('userRole');
  if (!userRole) {
    window.location.href = 'login.html';
    return;
  }

  // 2. Setup UI based on Role
  setupRoleUI(userRole);
  
  // 3. Setup Mobile Responsiveness
  setupMobileMenu();
});

function setupRoleUI(role) {
  // Mock User Identity
  const userNameEl = document.getElementById('userNameDisplay');
  const userRoleEl = document.getElementById('userRoleDisplay');
  const userAvatarEl = document.getElementById('userAvatar');
  const sidebarNav = document.getElementById('sidebarNav');
  
  const localName = localStorage.getItem('userName');
  let navItems = [];
  let defaultView = '';

  let activeUserNameForFilter = localName || '';

  if (role === 'manager') {
    userNameEl.textContent = localName || 'Admin Master';
    userRoleEl.textContent = 'Manager';
    navItems = [
      { id: 'overview', icon: 'layout-dashboard', label: 'Overview' },
      { id: 'branches', icon: 'store', label: 'Branch Directory' },
      { id: 'users', icon: 'users', label: 'User Accounts' },
      { id: 'inventory', icon: 'package-search', label: 'Global Inventory' },
      { id: 'reports', icon: 'bar-chart-2', label: 'Analytics Engine' }
    ];
    defaultView = 'overview';
    renderRecentSales();
    renderBranches();
    renderUsers();

  } else if (role === 'store_keeper') {
    userNameEl.textContent = localName || 'Alice Smith';
    userRoleEl.textContent = 'Store Keeper';
    navItems = [
      { id: 'inventory', icon: 'package', label: 'Stock Manager' },
      { id: 'stock-ops', icon: 'bell', label: 'Active Alerts' },
      { id: 'my-sales', icon: 'activity', label: 'My Performance' }
    ];
    defaultView = 'inventory';
    document.getElementById('addItemBtn').style.display = 'inline-flex';
    document.getElementById('addItemBtn').onclick = createItem;
    renderStockAlerts();
    renderMySales(activeUserNameForFilter);

  } else if (role === 'seller') {
    userNameEl.textContent = localName || 'Sarah Connor';
    userRoleEl.textContent = 'Seller';
    navItems = [
      { id: 'pos', icon: 'credit-card', label: 'Point of Sale' },
      { id: 'inventory', icon: 'search', label: 'Lookup Items' },
      { id: 'my-sales', icon: 'activity', label: 'My Performance' }
    ];
    defaultView = 'pos';
    renderPOSItems();
    renderMySales(activeUserNameForFilter);
    
    // Inject Discount UI
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

  // Dynamic Avatar Generation
  const finalName = userNameEl.textContent;
  const initials = finalName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
  userAvatarEl.textContent = initials;

  // Render Navigation
  navItems.forEach(item => {
    const btn = document.createElement('div');
    btn.className = 'nav-item';
    btn.id = `nav-${item.id}`;
    btn.innerHTML = `<i data-lucide="${item.icon}" class="nav-icon"></i> <span>${item.label}</span>`;
    btn.onclick = () => switchView(item.id);
    sidebarNav.appendChild(btn);
  });
  
  lucide.createIcons();
  
  // Load Global Data
  renderInventoryTable();

  // Switch to default view
  switchView(defaultView);
}

// Global View Switcher
function switchView(viewId) {
  // 1. Hide all views
  document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
  
  // 2. Show target view
  const targetView = document.getElementById(`view-${viewId}`);
  if (targetView) targetView.classList.add('active');

  // 3. Update Nav Active State
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
  const activeNav = document.getElementById(`nav-${viewId}`);
  if (activeNav) activeNav.classList.add('active');

  // 4. Close mobile menu if open
  document.getElementById('sidebar').classList.remove('open');
}

function logout() {
  localStorage.removeItem('userRole');
  window.location.href = 'login.html';
}

// --- Data Render Functions ---

function renderInventoryTable() {
  const tbody = document.querySelector('#inventoryTable tbody');
  if(!tbody) return;
  tbody.innerHTML = '';

  const inventory = window.MOCK_DATA.inventory || [];
  const userRole = localStorage.getItem('userRole');
  
  inventory.forEach((item, index) => {
    let statusClass = 'badge-success';
    if(item.status === 'Low Stock' || item.stock < 10) statusClass = 'badge-warning';
    if(item.status === 'Out of Stock' || item.stock === 0) statusClass = 'badge-danger';

    let actionBtns = '';
    if (userRole === 'store_keeper') {
      actionBtns = `<button class="btn btn-outline" style="padding: 0.2rem 0.5rem; font-size: 0.7rem;" onclick="updateItemQty(${index})">Update Qty</button>`;
    } else if (userRole === 'manager') {
      actionBtns = `<button class="btn btn-outline" style="padding: 0.2rem 0.5rem; font-size: 0.7rem;" onclick="transferItem(${index})">Transfer</button>`;
    }

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="color: var(--text-secondary)">${item.id}</td>
      <td style="font-weight: 500">${item.name}</td>
      <td>${item.category}</td>
      <td>$${item.price.toFixed(2)}</td>
      <td>${item.stock}</td>
      <td><span class="badge ${statusClass}">${item.stock === 0 ? 'Out of Stock' : (item.stock < 10 ? 'Low Stock' : 'In Stock')}</span></td>
      <td>${item.branch}</td>
      ${actionBtns ? `<td>${actionBtns}</td>` : ''}
    `;
    tbody.appendChild(tr);
  });
}

function updateItemQty(index) {
  const item = window.MOCK_DATA.inventory[index];
  const newQty = prompt(`Update stock quantity for ${item.name} (Current: ${item.stock}):`, item.stock);
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
  const name = prompt('Enter Item Name:');
  const price = prompt('Enter Price:');
  const qty = prompt('Enter Initial Quantity:');
  
  if (name && price && qty) {
    window.MOCK_DATA.inventory.push({
      id: 'ITM' + Math.floor(Math.random() * 1000),
      name,
      category: 'General',
      price: parseFloat(price),
      stock: parseInt(qty),
      branch: 'Headquarters',
      status: parseInt(qty) > 0 ? 'In Stock' : 'Out of Stock'
    });
    window.saveDB();
    renderInventoryTable();
  }
}

function renderRecentSales() {
  const tbody = document.querySelector('#recentSalesTable tbody');
  if(!tbody) return;
  tbody.innerHTML = '';
  
  const sales = window.MOCK_DATA.recentSales;
  
  sales.forEach(sale => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="color: var(--text-secondary)">${sale.date}</td>
      <td>${sale.id}</td>
      <td>${sale.seller}</td>
      <td>${sale.branch}</td>
      <td style="font-weight: 600" class="text-gradient">$${sale.total.toFixed(2)}</td>
    `;
    tbody.appendChild(tr);
  });
}

// --- POS Logic For Seller ---

let cart = [];

function renderPOSItems() {
  const grid = document.getElementById('posProductGrid');
  if(!grid) return;
  grid.innerHTML = '';

  const inventory = window.MOCK_DATA.inventory;
  
  inventory.forEach(item => {
    if(item.stock === 0) return; // Hide out of stock in POS

    const div = document.createElement('div');
    div.className = 'glass-panel product-card';
    div.onclick = () => addToCart(item);
    
    // Using a placeholder image generic URL
    div.innerHTML = `
      <div class="product-img d-flex justify-center align-center" style="font-size: 2rem; color: var(--accent-primary)">
        <i data-lucide="tag"></i>
      </div>
      <div style="font-weight: 500; font-size: 0.875rem; margin-bottom: 0.25rem;">${item.name}</div>
      <div style="color: var(--text-secondary); font-size: 0.75rem;">$${item.price.toFixed(2)}</div>
    `;
    grid.appendChild(div);
  });
  
  lucide.createIcons();
}

function addToCart(item) {
  const existing = cart.find(c => c.id === item.id);
  if(existing) {
    if(existing.quantity < item.stock) {
      existing.quantity += 1;
    } else {
      alert('Not enough stock available!');
    }
  } else {
    cart.push({...item, quantity: 1});
  }
  updateCartUI();
}

function updateCartUI() {
  const container = document.getElementById('cartItemsContainer');
  
  if (cart.length === 0) {
    container.innerHTML = '<div style="text-align: center; color: var(--text-muted); margin-top: 2rem;">Cart is empty</div>';
    document.getElementById('cartSubtotal').textContent = '$0.00';
    document.getElementById('cartTotal').textContent = '$0.00';
    return;
  }

  container.innerHTML = '';
  let subtotal = 0;

  cart.forEach((item, index) => {
    const itemTotal = item.price * item.quantity;
    subtotal += itemTotal;

    const div = document.createElement('div');
    div.className = 'cart-item';
    div.innerHTML = `
      <div style="flex: 1;">
        <div style="font-size: 0.875rem; font-weight: 500;">${item.name}</div>
        <div style="font-size: 0.75rem; color: var(--text-secondary);">
          $${item.price.toFixed(2)} x ${item.quantity}
        </div>
      </div>
      <div style="font-weight: 600;">$${itemTotal.toFixed(2)}</div>
      <button class="btn" style="padding: 0.25rem; color: var(--danger);" onclick="removeFromCart(${index})">
        <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
      </button>
    `;
    container.appendChild(div);
  });
  
  lucide.createIcons();

  document.getElementById('cartSubtotal').textContent = `$${subtotal.toFixed(2)}`;
  document.getElementById('cartTotal').textContent = `$${subtotal.toFixed(2)}`;
}

function removeFromCart(index) {
  cart.splice(index, 1);
  updateCartUI();
}

function processCheckout() {
  if(cart.length === 0) {
    alert('Cart is empty.');
    return;
  }

  // Simulate process
  alert(`Payment processed successfully! Total: ${document.getElementById('cartTotal').textContent}\nReceipt generated.`);
  cart = [];
  updateCartUI();
}

// --- Mobile Responsiveness ---
function setupMobileMenu() {
  const sidebar = document.getElementById('sidebar');
  const openBtn = document.getElementById('mobileMenuBtn');
  const closeBtn = document.getElementById('mobileMenuClose');

  if(window.innerWidth <= 768) {
    openBtn.style.display = 'block';
    closeBtn.style.display = 'block';
  }

  window.addEventListener('resize', () => {
    if(window.innerWidth <= 768) {
      openBtn.style.display = 'block';
      closeBtn.style.display = 'block';
    } else {
      openBtn.style.display = 'none';
      closeBtn.style.display = 'none';
      sidebar.classList.remove('open');
    }
  });

  openBtn.onclick = () => sidebar.classList.add('open');
  closeBtn.onclick = () => sidebar.classList.remove('open');
}

// --- NEW CRUD & SRS LOGIC ---

function renderBranches() {
  const tbody = document.querySelector('#branchesTable tbody');
  if(!tbody) return;
  tbody.innerHTML = '';
  const branches = window.MOCK_DATA.branches || [];
  branches.forEach((b, index) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="color: var(--text-secondary)">${b.id}</td>
      <td style="font-weight: 500">${b.name}</td>
      <td>${b.manager}</td>
      <td>${b.totalItems}</td>
      <td><button class="btn btn-danger" style="padding: 0.2rem 0.5rem; font-size: 0.7rem;" onclick="deleteBranch(${index})">Delete</button></td>
    `;
    tbody.appendChild(tr);
  });
}

function createBranch() {
  const name = prompt('Enter New Branch Name:');
  const mgr = prompt('Assign Manager Name:');
  if(name && mgr) {
    window.MOCK_DATA.branches.push({ id: 'BR' + Math.floor(Math.random()*1000), name, manager: mgr, totalItems: 0, totalSales: '$0' });
    window.saveDB();
    renderBranches();
  }
}

function deleteBranch(index) {
  if(confirm('Are you sure you want to delete this branch?')) {
    window.MOCK_DATA.branches.splice(index, 1);
    window.saveDB();
    renderBranches();
  }
}

function renderUsers() {
  const tbody = document.querySelector('#usersTable tbody');
  if(!tbody) return;
  tbody.innerHTML = '';
  const users = window.MOCK_DATA.users || [];
  users.forEach((u, index) => {
    const btnText = u.status === 'Active' ? 'Deactivate' : 'Activate';
    const btnClass = u.status === 'Active' ? 'btn-danger' : 'btn-primary';
    const statusClass = u.status === 'Active' ? 'badge-success' : 'badge-danger';
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="color: var(--text-secondary)">${u.id}</td>
      <td style="font-weight: 500">${u.name}</td>
      <td>${u.email}</td>
      <td style="text-transform: capitalize;">${u.role.replace('_', ' ')}</td>
      <td><span class="badge ${statusClass}">${u.status}</span></td>
      <td><button class="btn ${btnClass}" style="padding: 0.2rem 0.5rem; font-size: 0.7rem;" onclick="toggleUserStatus(${index})">${btnText}</button></td>
    `;
    tbody.appendChild(tr);
  });
}

function createUser() {
  const name = prompt('Enter Full Name:');
  const email = prompt('Enter Email Address:');
  const role = prompt('Enter Role (manager / store_keeper / seller):');
  if(name && email && role) {
    window.MOCK_DATA.users.push({ id: 'USR-' + Math.floor(Math.random()*1000), name, email, role, password: 'password123', status: 'Active' });
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

function generateReport(type) {
  const log = document.getElementById('reportLog');
  const title = document.getElementById('reportTitle');
  if(type === 'daily') {
    title.textContent = "Daily Sales Report - " + new Date().toLocaleDateString();
    log.textContent = JSON.stringify(window.MOCK_DATA.recentSales.slice(0,2), null, 2);
  } else if (type === 'weekly') {
    title.textContent = "Weekly Revenue Analysis";
    log.textContent = "Total Branch Sales: \nDownTown: $24,500\nUptown: $18,200\n\nTop Sales person: Sarah Connor";
  } else if (type === 'inventory') {
    title.textContent = "Fast Moving / Slow Moving Stock";
    const fast = window.MOCK_DATA.inventory.filter(i => i.stock < 10);
    const slow = window.MOCK_DATA.inventory.filter(i => i.stock >= 10);
    log.textContent = "FAST MOVING (LOW STOCK):\n" + JSON.stringify(fast, null, 2) + "\n\nSLOW MOVING:\n" + JSON.stringify(slow, null, 2);
  }
}

function renderStockAlerts() {
  const container = document.getElementById('stockAlertsContainer');
  if(!container) return;
  const low = window.MOCK_DATA.inventory.filter(i => i.stock < 5);
  if(low.length === 0) {
    container.innerHTML = '<p style="color: var(--success);">All stock levels are optimal.</p>';
    return;
  }
  container.innerHTML = low.map(item => `
    <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 1rem; margin-bottom: 1rem;">
      <h4 style="color: var(--warning); font-family: 'Jost'; text-transform: uppercase;">Low Stock Alert: ${item.name}</h4>
      <p style="font-size: 0.8rem; color: var(--text-secondary);">Only ${item.stock} left in ${item.branch}. Please re-order SKU: ${item.id}.</p>
    </div>
  `).join('');
}

function checkAlerts() {
  if (document.getElementById('view-stock-ops').classList.contains('active')) {
    renderStockAlerts();
  }
}

function renderMySales(userName) {
  const tbody = document.querySelector('#mySalesTable tbody');
  if(!tbody) return;
  tbody.innerHTML = '';
  const mySales = (window.MOCK_DATA.recentSales || []).filter(s => s.seller === userName);
  if (mySales.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: var(--text-muted);">No sales recorded yet.</td></tr>';
    return;
  }
  mySales.forEach(s => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="color: var(--text-secondary)">${s.date}</td>
      <td>${s.id}</td>
      <td>${s.branch}</td>
      <td style="font-weight: 600" class="text-gradient">$${s.total.toFixed(2)}</td>
    `;
    tbody.appendChild(tr);
  });
}

function applyDiscount() {
  if (cart.length === 0) return alert('Add items first.');
  const code = prompt('Enter Discount Code (e.g., VIP10):');
  if (code) {
    alert('Discount applied! 10% off total.');
    const totalEl = document.getElementById('cartTotal');
    const currentTotal = parseFloat(totalEl.textContent.replace('$', ''));
    totalEl.textContent = '$' + (currentTotal * 0.9).toFixed(2);
  }
}
