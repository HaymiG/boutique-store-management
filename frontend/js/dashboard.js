window.saveDB = function () {
  localStorage.setItem('BoutiqueDB', JSON.stringify(window.MOCK_DATA));
};

document.addEventListener('DOMContentLoaded', function () {
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

  var saved = localStorage.getItem('BoutiqueDB');
  if (saved) {
    window.MOCK_DATA = JSON.parse(saved);
  } else {
    window.saveDB();
  }

  var userRole = localStorage.getItem('userRole');
  if (!userRole) {
    window.location.href = 'login.html';
    return;
  }

  setupRoleUI(userRole);
  setupMobileMenu();
});

function setupRoleUI(role) {
  var userNameEl = document.getElementById('userNameDisplay');
  var userRoleEl = document.getElementById('userRoleDisplay');
  var userAvatarEl = document.getElementById('userAvatar');
  var sidebarNav = document.getElementById('sidebarNav');

  var localName = localStorage.getItem('userName');
  var navItems = [];
  var defaultView = '';

  if (role === 'manager') {
    userNameEl.textContent = localName || 'Adem Abe';
    userRoleEl.textContent = 'Manager';
    navItems = [
      { id: 'overview', icon: 'layout-dashboard', label: 'Overview' },
      { id: 'branches', icon: 'store', label: 'Branches' },
      { id: 'users', icon: 'users', label: 'Users' },
      { id: 'inventory', icon: 'package-search', label: 'Inventory' },
      { id: 'reports', icon: 'bar-chart-2', label: 'Analytics' }
    ];
    defaultView = 'overview';
    populateFilterSelects();
    renderRecentSales();
    renderBranches();
    renderUsers();
    updateWidgets();

  } else if (role === 'store_keeper') {
    userNameEl.textContent = localName || 'Abigiya Mulugeta';
    userRoleEl.textContent = 'Store Keeper';
    navItems = [
      { id: 'inventory', icon: 'package', label: 'Stock' },
      { id: 'stock-ops', icon: 'bell', label: 'Alerts' },
      { id: 'my-sales', icon: 'activity', label: 'Performance' }
    ];
    defaultView = 'inventory';
    var addBtn = document.getElementById('addItemBtn');
    if (addBtn) {
      addBtn.classList.remove('hidden');
      addBtn.onclick = createItem;
    }
    var recvBtn = document.getElementById('receiveStockBtn');
    if (recvBtn) recvBtn.classList.remove('hidden');
    var skRpt = document.getElementById('skReportBtn');
    if (skRpt) skRpt.classList.remove('hidden');
    renderStockAlerts();
    renderMySales();

  } else if (role === 'seller') {
    userNameEl.textContent = localName || 'Mariamawit Messay';
    userRoleEl.textContent = 'Seller';
    navItems = [
      { id: 'pos', icon: 'credit-card', label: 'POS' },
      { id: 'inventory', icon: 'search', label: 'Items' },
      { id: 'my-sales', icon: 'activity', label: 'Performance' }
    ];
    defaultView = 'pos';
    renderPOSItems();
    renderMySales();
  }

  var finalName = userNameEl.textContent;
  var parts = finalName.split(' ');
  var initials = '';
  if (parts.length > 1) {
    initials = (parts[0][0] + parts[1][0]).toUpperCase();
  } else {
    initials = finalName.substring(0, 2).toUpperCase();
  }
  userAvatarEl.textContent = initials;

  navItems.forEach(function (item) {
    var btn = document.createElement('div');
    btn.className = 'navitem';
    btn.id = 'nav-' + item.id;
    btn.innerHTML = '<i data-lucide="' + item.icon + '" class="navicon"></i> <span>' + item.label + '</span>';
    btn.onclick = function () { switchView(item.id); };
    sidebarNav.appendChild(btn);
  });

  if (typeof lucide !== 'undefined') lucide.createIcons();
  renderInventoryTable();
  switchView(defaultView);
}

function switchView(viewId) {
  document.querySelectorAll('.viewsection').forEach(function (el) { el.classList.remove('active'); });
  var target = document.getElementById('view-' + viewId);
  if (target) target.classList.add('active');

  document.querySelectorAll('.navitem').forEach(function (el) { el.classList.remove('active'); });
  var nav = document.getElementById('nav-' + viewId);
  if (nav) nav.classList.add('active');

  var sidebar = document.getElementById('sidebar');
  if (sidebar) sidebar.classList.remove('open');
}

function logout() {
  localStorage.removeItem('userRole');
  localStorage.removeItem('userName');
  window.location.href = 'login.html';
}

function updateWidgets() {
  var today = new Date().toISOString().split('T')[0];
  var todaySales = (window.MOCK_DATA.recentSales || []).filter(function (s) { return s.date === today; });
  var todayRev = todaySales.reduce(function (a, c) { return a + c.total; }, 0);
  var revEl = document.getElementById('widgetRevenue');
  if (revEl) revEl.textContent = 'Br ' + todayRev.toFixed(2);

  var totalStock = (window.MOCK_DATA.inventory || []).reduce(function (a, c) { return a + c.stock; }, 0);
  var invEl = document.getElementById('widgetInventory');
  if (invEl) invEl.textContent = totalStock.toLocaleString();

  var branchCount = (window.MOCK_DATA.branches || []).length;
  var brEl = document.getElementById('widgetBranches');
  if (brEl) brEl.textContent = branchCount + ' Active';
}

function populateFilterSelects() {
  var branchSelect = document.getElementById('salesBranchFilter');
  var sellerSelect = document.getElementById('salesSellerFilter');
  if (branchSelect && window.MOCK_DATA.branches) {
    window.MOCK_DATA.branches.forEach(function (b) {
      var opt = document.createElement('option');
      opt.value = b.name;
      opt.textContent = b.name;
      branchSelect.appendChild(opt);
    });
  }
  if (sellerSelect && window.MOCK_DATA.users) {
    window.MOCK_DATA.users.filter(function (u) { return u.role === 'seller'; }).forEach(function (u) {
      var opt = document.createElement('option');
      opt.value = u.name;
      opt.textContent = u.name;
      sellerSelect.appendChild(opt);
    });
  }
}

function handleTimeFilterChange() {
  var tf = document.getElementById('salesTimeFilter');
  var dateRange = document.getElementById('customDateRange');
  if (tf && dateRange) {
    if (tf.value === 'custom') {
      dateRange.classList.remove('hidden');
    } else {
      dateRange.classList.add('hidden');
    }
  }
  renderRecentSales();
}

function filterByTime(dateStr, timeFilter) {
  if (timeFilter === 'all') return true;
  var today = new Date();
  var targetDate = new Date(dateStr);
  if (timeFilter === 'daily') {
    return targetDate.toDateString() === today.toDateString();
  } else if (timeFilter === 'weekly') {
    var diffTime = Math.abs(today - targetDate);
    var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays <= 7;
  } else if (timeFilter === 'monthly') {
    return targetDate.getFullYear() === today.getFullYear() && targetDate.getMonth() === today.getMonth();
  } else if (timeFilter === 'custom') {
    var from = document.getElementById('dateFrom');
    var to = document.getElementById('dateTo');
    if (from && from.value && to && to.value) {
      var fromDate = new Date(from.value);
      var toDate = new Date(to.value);
      toDate.setHours(23, 59, 59);
      return targetDate >= fromDate && targetDate <= toDate;
    }
    return true;
  }
  return true;
}

function renderInventoryTable() {
  var tbody = document.querySelector('#inventoryTable tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  var inventory = window.MOCK_DATA.inventory || [];
  var userRole = localStorage.getItem('userRole');

  inventory.forEach(function (item, index) {
    var statusClass = 'statussuccess';
    if (item.stock < 10) statusClass = 'statuswarning';
    if (item.stock === 0) statusClass = 'statusdanger';

    var actionBtns = '';
    if (userRole === 'store_keeper') {
      actionBtns = '<div class="actiongroup">' +
        '<button class="editbtn" onclick="editItem(' + index + ')">Edit</button>' +
        '<button class="actionbtn" onclick="updateItemQty(' + index + ')">Set Qty</button>' +
        '<button class="damagedbtn" onclick="reportDamage(' + index + ')">Damage</button>' +
        '<button class="transferbtn" onclick="transferItem(' + index + ')">Transfer</button>' +
        '</div>';
    } else if (userRole === 'manager') {
      actionBtns = '<div class="actiongroup">' +
        '<button class="editbtn" onclick="updateItemPrice(' + index + ')">Price</button>' +
        '<button class="actionbtn" onclick="transferItem(' + index + ')">Transfer</button>' +
        '</div>';
    }

    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td class="tableid">' + item.id + '</td>' +
      '<td class="tabletitle">' + item.name + '</td>' +
      '<td>' + item.category + '</td>' +
      '<td>Br ' + item.price.toFixed(2) + '</td>' +
      '<td>' + item.stock + '</td>' +
      '<td>' + (item.damaged || 0) + '</td>' +
      '<td><span class="' + statusClass + '">' + (item.stock === 0 ? 'Out' : (item.stock < 10 ? 'Low' : 'In')) + '</span></td>' +
      '<td>' + item.branch + '</td>' +
      (actionBtns ? '<td>' + actionBtns + '</td>' : '');
    tbody.appendChild(tr);
  });
}

function updateItemQty(index) {
  var item = window.MOCK_DATA.inventory[index];
  var newQty = prompt('Update stock for ' + item.name + ' (Current: ' + item.stock + '):', item.stock);
  if (newQty !== null && !isNaN(newQty)) {
    window.MOCK_DATA.inventory[index].stock = parseInt(newQty);
    window.saveDB();
    renderInventoryTable();
    checkAlerts();
  }
}

function editItem(index) {
  var item = window.MOCK_DATA.inventory[index];
  var newName = prompt('Item name:', item.name);
  if (!newName) return;
  var newCat = prompt('Category:', item.category);
  if (!newCat) return;
  var newQty = prompt('Quantity:', item.stock);
  if (newQty === null) return;
  window.MOCK_DATA.inventory[index].name = newName;
  window.MOCK_DATA.inventory[index].category = newCat;
  if (!isNaN(newQty)) window.MOCK_DATA.inventory[index].stock = parseInt(newQty);
  window.saveDB();
  renderInventoryTable();
}

function reportDamage(index) {
  var item = window.MOCK_DATA.inventory[index];
  var damageQty = prompt('Report damaged/expired for ' + item.name + ' (Stock: ' + item.stock + '):', '1');
  if (damageQty !== null && !isNaN(damageQty)) {
    var val = parseInt(damageQty);
    if (item.stock >= val) {
      window.MOCK_DATA.inventory[index].stock -= val;
      window.MOCK_DATA.inventory[index].damaged = (window.MOCK_DATA.inventory[index].damaged || 0) + val;
      window.saveDB();
      renderInventoryTable();
    } else {
      alert('Cannot report damage greater than active stock.');
    }
  }
}

function updateItemPrice(index) {
  var item = window.MOCK_DATA.inventory[index];
  var newPrice = prompt('Update price for ' + item.name + ' (Current: Br ' + item.price + '):', item.price);
  if (newPrice !== null && !isNaN(newPrice)) {
    window.MOCK_DATA.inventory[index].price = parseFloat(newPrice);
    window.saveDB();
    renderInventoryTable();
  }
}

function transferItem(index) {
  var item = window.MOCK_DATA.inventory[index];
  var branches = (window.MOCK_DATA.branches || []).map(function (b) { return b.name; }).join(', ');
  var newBranch = prompt('Transfer ' + item.name + ' to branch (Current: ' + item.branch + ').\nAvailable: ' + branches, '');
  if (newBranch) {
    window.MOCK_DATA.inventory[index].branch = newBranch;
    window.saveDB();
    renderInventoryTable();
  }
}

function receiveStock() {
  var items = window.MOCK_DATA.inventory || [];
  var list = items.map(function (it, i) { return i + ': ' + it.name + ' (Stock: ' + it.stock + ')'; }).join('\n');
  var idx = prompt('Select item index to receive stock:\n' + list);
  if (idx === null || isNaN(idx)) return;
  var index = parseInt(idx);
  if (index < 0 || index >= items.length) { alert('Invalid selection.'); return; }
  var qty = prompt('Quantity received for ' + items[index].name + ':');
  if (qty !== null && !isNaN(qty) && parseInt(qty) > 0) {
    window.MOCK_DATA.inventory[index].stock += parseInt(qty);
    window.saveDB();
    renderInventoryTable();
    checkAlerts();
    alert('Received ' + qty + ' units of ' + items[index].name + '.');
  }
}

function createItem() {
  var name = prompt('Item Name:');
  if (!name) return;
  var category = prompt('Category:');
  if (!category) return;
  var price = prompt('Sale Price (Br):');
  if (!price || isNaN(price)) return;
  var cost = prompt('Cost Price (Br):');
  if (!cost || isNaN(cost)) return;
  var qty = prompt('Initial Quantity:');
  if (!qty || isNaN(qty)) return;
  var branch = prompt('Branch:', 'Piassa Branch');
  if (!branch) return;
  window.MOCK_DATA.inventory.push({
    id: 'ITM' + String(Math.floor(Math.random() * 9000) + 1000),
    name: name,
    category: category,
    price: parseFloat(price),
    costPrice: parseFloat(cost),
    stock: parseInt(qty),
    damaged: 0,
    branch: branch,
    status: parseInt(qty) > 0 ? 'In Stock' : 'Out of Stock'
  });
  window.saveDB();
  renderInventoryTable();
}

function generateSkReport() {
  var inventory = window.MOCK_DATA.inventory || [];
  var totalItems = inventory.length;
  var totalStock = inventory.reduce(function (a, c) { return a + c.stock; }, 0);
  var totalDamaged = inventory.reduce(function (a, c) { return a + (c.damaged || 0); }, 0);
  var lowStock = inventory.filter(function (i) { return i.stock < 5; });
  var outOfStock = inventory.filter(function (i) { return i.stock === 0; });
  var totalValue = inventory.reduce(function (a, c) { return a + (c.price * c.stock); }, 0);

  var report = '=== INVENTORY REPORT ===\n';
  report += 'Generated: ' + new Date().toLocaleString() + '\n';
  report += 'Generated by: ' + (document.getElementById('userNameDisplay').textContent) + '\n';
  report += '========================\n\n';
  report += 'Total Items: ' + totalItems + '\n';
  report += 'Total Stock Units: ' + totalStock + '\n';
  report += 'Total Damaged: ' + totalDamaged + '\n';
  report += 'Out of Stock Items: ' + outOfStock.length + '\n';
  report += 'Low Stock Items: ' + lowStock.length + '\n';
  report += 'Total Inventory Value: Br ' + totalValue.toFixed(2) + '\n\n';

  if (lowStock.length > 0) {
    report += '--- LOW STOCK ITEMS ---\n';
    lowStock.forEach(function (it) {
      report += '  ' + it.name + ' | Stock: ' + it.stock + ' | Branch: ' + it.branch + '\n';
    });
  }

  if (totalDamaged > 0) {
    report += '\n--- DAMAGED ITEMS ---\n';
    inventory.filter(function (i) { return (i.damaged || 0) > 0; }).forEach(function (it) {
      report += '  ' + it.name + ' | Damaged: ' + it.damaged + ' | Branch: ' + it.branch + '\n';
    });
  }

  alert(report);
}

function renderRecentSales() {
  var tbody = document.querySelector('#recentSalesTable tbody');
  if (!tbody) return;
  tbody.innerHTML = '';

  var tf = document.getElementById('salesTimeFilter');
  var bf = document.getElementById('salesBranchFilter');
  var sf = document.getElementById('salesSellerFilter');

  var tVal = tf ? tf.value : 'all';
  var bVal = bf ? bf.value : 'all';
  var sVal = sf ? sf.value : 'all';

  var filtered = (window.MOCK_DATA.recentSales || []).filter(function (s) {
    var tMatch = filterByTime(s.date, tVal);
    var bMatch = bVal === 'all' || s.branch === bVal;
    var sMatch = sVal === 'all' || s.seller === sVal;
    return tMatch && bMatch && sMatch;
  });

  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" class="cartempty">No transactions found.</td></tr>';
    return;
  }

  filtered.forEach(function (sale) {
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td class="tableid">' + sale.date + '</td>' +
      '<td>' + sale.id + '</td>' +
      '<td>' + sale.seller + '</td>' +
      '<td>' + sale.branch + '</td>' +
      '<td class="tableamount">Br ' + sale.total.toFixed(2) + '</td>';
    tbody.appendChild(tr);
  });
}

function renderMySales() {
  var tbody = document.querySelector('#mySalesTable tbody');
  if (!tbody) return;
  tbody.innerHTML = '';

  var myName = document.getElementById('userNameDisplay').textContent;
  var tf = document.getElementById('mySalesTimeFilter');
  var tVal = tf ? tf.value : 'all';

  var filtered = (window.MOCK_DATA.recentSales || []).filter(function (s) {
    return s.seller === myName && filterByTime(s.date, tVal);
  });

  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4" class="cartempty">No sales found.</td></tr>';
    return;
  }

  filtered.forEach(function (s) {
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td class="tableid">' + s.date + '</td>' +
      '<td>' + s.id + '</td>' +
      '<td>' + s.branch + '</td>' +
      '<td class="tableamount">Br ' + s.total.toFixed(2) + '</td>';
    tbody.appendChild(tr);
  });
}

var cart = [];
var appliedDiscount = 0;

function renderPOSItems() {
  var grid = document.getElementById('posProductGrid');
  if (!grid) return;
  grid.innerHTML = '';
  (window.MOCK_DATA.inventory || []).forEach(function (item) {
    if (item.stock === 0) return;
    var div = document.createElement('div');
    div.className = 'product';
    div.onclick = function () { addToCart(item); };
    div.innerHTML =
      '<div class="producticon"><i data-lucide="tag"></i></div>' +
      '<div class="productname">' + item.name + '</div>' +
      '<div class="productprice">Br ' + item.price.toFixed(2) + '</div>' +
      '<div class="productstock">' + item.stock + ' in stock</div>';
    grid.appendChild(div);
  });
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function addToCart(item) {
  var existing = cart.find(function (c) { return c.id === item.id; });
  if (existing) {
    if (existing.quantity < item.stock) existing.quantity += 1;
    else alert('Not enough stock!');
  } else {
    cart.push({
      id: item.id,
      name: item.name,
      price: item.price,
      costPrice: item.costPrice || 0,
      quantity: 1,
      maxStock: item.stock
    });
  }
  updateCartUI();
}

function updateCartUI() {
  var container = document.getElementById('cartItemsContainer');
  var discountRow = document.getElementById('discountRow');

  if (cart.length === 0) {
    container.innerHTML = '<div class="cartempty">Cart is empty</div>';
    document.getElementById('cartSubtotal').textContent = 'Br 0.00';
    document.getElementById('cartTotal').textContent = 'Br 0.00';
    if (discountRow) discountRow.classList.add('hidden');
    appliedDiscount = 0;
    return;
  }

  container.innerHTML = '';
  var subtotal = 0;

  cart.forEach(function (item, index) {
    var total = item.price * item.quantity;
    subtotal += total;
    var div = document.createElement('div');
    div.className = 'cartitem';
    div.innerHTML =
      '<div class="cartinfo">' +
        '<div class="cartname">' + item.name + '</div>' +
        '<div class="cartprice">Br ' + item.price.toFixed(2) + ' x ' + item.quantity + '</div>' +
      '</div>' +
      '<div class="carttotal">Br ' + total.toFixed(2) + '</div>' +
      '<button class="actiondelete" onclick="removeFromCart(' + index + ')">' +
        '<i data-lucide="trash-2" class="iconsm"></i>' +
      '</button>';
    container.appendChild(div);
  });

  if (typeof lucide !== 'undefined') lucide.createIcons();

  var finalTotal = subtotal * (1 - appliedDiscount);

  document.getElementById('cartSubtotal').textContent = 'Br ' + subtotal.toFixed(2);
  document.getElementById('cartTotal').textContent = 'Br ' + finalTotal.toFixed(2);

  if (appliedDiscount > 0 && discountRow) {
    discountRow.classList.remove('hidden');
    document.getElementById('discountDisplay').textContent = (appliedDiscount * 100) + '%';
  }
}

function removeFromCart(index) {
  cart.splice(index, 1);
  updateCartUI();
}

function processCheckout() {
  if (cart.length === 0) return alert('Cart is empty.');

  var content = document.getElementById('receiptContent');
  var subTotalStr = document.getElementById('cartSubtotal').textContent;
  var totalStr = document.getElementById('cartTotal').textContent;
  var sellerName = document.getElementById('userNameDisplay').textContent;
  var branchName = document.getElementById('activeBranchDisplay').textContent || 'Piassa Branch';

  var receiptText = '=============================\n';
  receiptText += '     BOUTIQUE STORE RECEIPT\n';
  receiptText += '=============================\n';
  receiptText += 'Date: ' + new Date().toLocaleString() + '\n';
  receiptText += 'Seller: ' + sellerName + '\n';
  receiptText += 'Branch: ' + branchName + '\n';
  receiptText += '-----------------------------\n';

  cart.forEach(function (c) {
    receiptText += c.name + ' (x' + c.quantity + ')\n';
    receiptText += '        Br ' + (c.price * c.quantity).toFixed(2) + '\n';
  });

  receiptText += '-----------------------------\n';
  receiptText += 'Subtotal: ' + subTotalStr + '\n';
  if (appliedDiscount > 0) {
    receiptText += 'Discount: ' + (appliedDiscount * 100) + '%\n';
  }
  receiptText += 'TOTAL:    ' + totalStr + '\n';
  receiptText += '=============================\n';
  receiptText += '  Thank you for shopping!\n';
  receiptText += '=============================\n';

  content.textContent = receiptText;
  document.getElementById('receiptModal').classList.remove('hidden');
}

function printReceipt() {
  var content = document.getElementById('receiptContent').textContent;
  var printWin = window.open('', '_blank', 'width=400,height=600');
  printWin.document.write('<html><head><title>Receipt</title>');
  printWin.document.write('<style>body{font-family:monospace;font-size:14px;padding:20px;white-space:pre-wrap;}</style>');
  printWin.document.write('</head><body>');
  printWin.document.write(content);
  printWin.document.write('</body></html>');
  printWin.document.close();
  printWin.print();
}

function closeReceipt() {
  document.getElementById('receiptModal').classList.add('hidden');

  var finalTotal = parseFloat(document.getElementById('cartTotal').textContent.replace('Br ', ''));
  var sellerName = document.getElementById('userNameDisplay').textContent;
  var branchName = document.getElementById('activeBranchDisplay').textContent || 'Piassa Branch';

  window.MOCK_DATA.recentSales.push({
    id: 'TRX-' + String(Math.floor(Math.random() * 9000) + 1000),
    date: new Date().toISOString().split('T')[0],
    items: cart.reduce(function (a, c) { return a + c.quantity; }, 0).toString(),
    total: finalTotal,
    seller: sellerName,
    branch: branchName
  });

  cart.forEach(function (c) {
    var invItem = window.MOCK_DATA.inventory.find(function (i) { return i.id === c.id; });
    if (invItem) invItem.stock -= c.quantity;
  });

  window.saveDB();

  cart = [];
  appliedDiscount = 0;
  updateCartUI();
  renderPOSItems();
  renderMySales();
}

function renderBranches() {
  var tbody = document.querySelector('#branchesTable tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  (window.MOCK_DATA.branches || []).forEach(function (b, i) {
    var branchSales = (window.MOCK_DATA.recentSales || [])
      .filter(function (s) { return s.branch === b.name; })
      .reduce(function (a, c) { return a + c.total; }, 0);
    var branchItems = (window.MOCK_DATA.inventory || [])
      .filter(function (it) { return it.branch === b.name; })
      .reduce(function (a, c) { return a + c.stock; }, 0);
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td class="tableid">' + b.id + '</td>' +
      '<td class="tabletitle">' + b.name + '</td>' +
      '<td>' + b.manager + '</td>' +
      '<td>' + branchItems + '</td>' +
      '<td class="tableamount">Br ' + branchSales.toFixed(2) + '</td>' +
      '<td><div class="actiongroup">' +
        '<button class="editbtn" onclick="editBranch(' + i + ')">Edit</button>' +
        '<button class="deletebtn" onclick="deleteBranch(' + i + ')">Delete</button>' +
      '</div></td>';
    tbody.appendChild(tr);
  });
}

function createBranch() {
  var name = prompt('Branch Name:');
  if (!name) return;
  var mgr = prompt('Manager Name:');
  if (!mgr) return;
  window.MOCK_DATA.branches.push({
    id: 'BR' + String(Math.floor(Math.random() * 9000) + 1000),
    name: name,
    manager: mgr,
    totalItems: 0,
    totalSales: 'Br 0'
  });
  window.saveDB();
  renderBranches();
  updateWidgets();
}

function editBranch(index) {
  var b = window.MOCK_DATA.branches[index];
  var newName = prompt('Branch name (Current: ' + b.name + '):', b.name);
  if (!newName) return;
  var newMgr = prompt('Manager for ' + newName + ':', b.manager);
  if (!newMgr) return;
  window.MOCK_DATA.branches[index].name = newName;
  window.MOCK_DATA.branches[index].manager = newMgr;
  window.saveDB();
  renderBranches();
}

function deleteBranch(index) {
  if (confirm('Delete this branch?')) {
    window.MOCK_DATA.branches.splice(index, 1);
    window.saveDB();
    renderBranches();
    updateWidgets();
  }
}

function renderUsers() {
  var tbody = document.querySelector('#usersTable tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  (window.MOCK_DATA.users || []).forEach(function (u, i) {
    var btnText = u.status === 'Active' ? 'Deactivate' : 'Activate';
    var btnClass = u.status === 'Active' ? 'deletebtn' : 'actionbtn';
    var statusClass = u.status === 'Active' ? 'statussuccess' : 'statusdanger';
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td class="tableid">' + u.id + '</td>' +
      '<td class="tabletitle">' + u.name + '</td>' +
      '<td>' + u.email + '</td>' +
      '<td class="tablecapitalize">' + u.role.replace('_', ' ') + '</td>' +
      '<td><span class="' + statusClass + '">' + u.status + '</span></td>' +
      '<td><button class="' + btnClass + '" onclick="toggleUserStatus(' + i + ')">' + btnText + '</button></td>';
    tbody.appendChild(tr);
  });
}

function createUser() {
  var name = prompt('Full Name:');
  if (!name) return;
  var email = prompt('Email:');
  if (!email) return;
  var role = prompt('Role (manager / store_keeper / seller):');
  if (!role) return;
  window.MOCK_DATA.users.push({
    id: 'USR-' + String(Math.floor(Math.random() * 9000) + 1000),
    name: name,
    email: email,
    role: role,
    password: 'password123',
    status: 'Active'
  });
  window.saveDB();
  renderUsers();
}

function toggleUserStatus(index) {
  var u = window.MOCK_DATA.users[index];
  u.status = u.status === 'Active' ? 'Inactive' : 'Active';
  window.saveDB();
  renderUsers();
}

function generateReport(type) {
  var log = document.getElementById('reportLog');
  var title = document.getElementById('reportTitle');
  var sales = window.MOCK_DATA.recentSales || [];
  var inventory = window.MOCK_DATA.inventory || [];

  if (type === 'daily') {
    title.textContent = 'Daily Sales \u2014 ' + new Date().toLocaleDateString();
    var todaySales = sales.filter(function (s) { return filterByTime(s.date, 'daily'); });
    var tVol = todaySales.reduce(function (a, c) { return a + c.total; }, 0);
    var output = 'Total Transactions: ' + todaySales.length + '\n';
    output += 'Total Volume: Br ' + tVol.toFixed(2) + '\n\n';
    output += '--- TRANSACTIONS ---\n';
    todaySales.forEach(function (s) {
      output += s.id + ' | ' + s.seller + ' | ' + s.branch + ' | Br ' + s.total.toFixed(2) + '\n';
    });
    if (todaySales.length === 0) output += 'No sales recorded today.\n';
    log.textContent = output;

  } else if (type === 'weekly') {
    title.textContent = 'Weekly Revenue Analysis';
    var weeklySales = sales.filter(function (s) { return filterByTime(s.date, 'weekly'); });
    var wVol = weeklySales.reduce(function (a, c) { return a + c.total; }, 0);
    var sellerTotals = {};
    weeklySales.forEach(function (s) {
      sellerTotals[s.seller] = (sellerTotals[s.seller] || 0) + s.total;
    });
    var topSeller = '';
    var topAmount = 0;
    for (var seller in sellerTotals) {
      if (sellerTotals[seller] > topAmount) {
        topAmount = sellerTotals[seller];
        topSeller = seller;
      }
    }
    var output = 'Total Transactions: ' + weeklySales.length + '\n';
    output += 'Weekly Volume: Br ' + wVol.toFixed(2) + '\n\n';
    output += '--- SELLER BREAKDOWN ---\n';
    for (var s in sellerTotals) {
      output += s + ': Br ' + sellerTotals[s].toFixed(2) + '\n';
    }
    output += '\nTop Seller: ' + (topSeller || 'N/A') + ' (Br ' + topAmount.toFixed(2) + ')';
    log.textContent = output;

  } else if (type === 'monthly') {
    title.textContent = 'Monthly Sales \u2014 ' + new Date().toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    var monthlySales = sales.filter(function (s) { return filterByTime(s.date, 'monthly'); });
    var mVol = monthlySales.reduce(function (a, c) { return a + c.total; }, 0);
    var branchTotals = {};
    monthlySales.forEach(function (s) {
      branchTotals[s.branch] = (branchTotals[s.branch] || 0) + s.total;
    });
    var output = 'Total Transactions: ' + monthlySales.length + '\n';
    output += 'Monthly Volume: Br ' + mVol.toFixed(2) + '\n\n';
    output += '--- BY BRANCH ---\n';
    for (var br in branchTotals) {
      output += br + ': Br ' + branchTotals[br].toFixed(2) + '\n';
    }
    log.textContent = output;

  } else if (type === 'branch') {
    title.textContent = 'Sales by Branch';
    var branchData = {};
    sales.forEach(function (s) {
      if (!branchData[s.branch]) branchData[s.branch] = { count: 0, total: 0, sellers: {} };
      branchData[s.branch].count++;
      branchData[s.branch].total += s.total;
      branchData[s.branch].sellers[s.seller] = (branchData[s.branch].sellers[s.seller] || 0) + s.total;
    });
    var output = '';
    for (var branch in branchData) {
      var bd = branchData[branch];
      output += '=== ' + branch + ' ===\n';
      output += 'Transactions: ' + bd.count + '\n';
      output += 'Total Sales: Br ' + bd.total.toFixed(2) + '\n';
      output += 'Sellers:\n';
      for (var sel in bd.sellers) {
        output += '  ' + sel + ': Br ' + bd.sellers[sel].toFixed(2) + '\n';
      }
      var branchStock = inventory.filter(function (it) { return it.branch === branch; });
      output += 'Inventory Items: ' + branchStock.length + '\n';
      output += 'Stock Units: ' + branchStock.reduce(function (a, c) { return a + c.stock; }, 0) + '\n\n';
    }
    log.textContent = output || 'No branch sales data available.';

  } else if (type === 'inventory') {
    title.textContent = 'Inventory Snapshot & Stock Health';
    var damaged = inventory.filter(function (i) { return (i.damaged || 0) > 0; });
    var lowStock = inventory.filter(function (i) { return i.stock > 0 && i.stock < 10; });
    var outOfStock = inventory.filter(function (i) { return i.stock === 0; });
    var healthy = inventory.filter(function (i) { return i.stock >= 10; });
    var totalValue = inventory.reduce(function (a, c) { return a + (c.price * c.stock); }, 0);

    var output = 'Total SKUs: ' + inventory.length + '\n';
    output += 'Total Inventory Value: Br ' + totalValue.toFixed(2) + '\n\n';
    output += '--- FAST MOVING (LOW STOCK) ---\n';
    lowStock.forEach(function (it) {
      output += '  ' + it.name + ' | Stock: ' + it.stock + ' | ' + it.branch + '\n';
    });
    if (lowStock.length === 0) output += '  None\n';
    output += '\n--- OUT OF STOCK ---\n';
    outOfStock.forEach(function (it) {
      output += '  ' + it.name + ' | ' + it.branch + '\n';
    });
    if (outOfStock.length === 0) output += '  None\n';
    output += '\n--- SLOW MOVING (HIGH STOCK) ---\n';
    healthy.forEach(function (it) {
      output += '  ' + it.name + ' | Stock: ' + it.stock + ' | ' + it.branch + '\n';
    });
    output += '\n--- DAMAGED/EXPIRED ---\n';
    damaged.forEach(function (it) {
      output += '  ' + it.name + ' | Damaged: ' + it.damaged + ' | ' + it.branch + '\n';
    });
    if (damaged.length === 0) output += '  None\n';
    log.textContent = output;

  } else if (type === 'profit') {
    title.textContent = 'Profit & Revenue Analysis';
    var totalRevenue = sales.reduce(function (a, c) { return a + c.total; }, 0);
    var totalCost = 0;
    sales.forEach(function (sale) {
      var items = inventory;
      var avgCostRatio = 0;
      var count = 0;
      items.forEach(function (it) {
        if (it.costPrice && it.price > 0) {
          avgCostRatio += it.costPrice / it.price;
          count++;
        }
      });
      var ratio = count > 0 ? avgCostRatio / count : 0.5;
      totalCost += sale.total * ratio;
    });
    var grossProfit = totalRevenue - totalCost;
    var margin = totalRevenue > 0 ? ((grossProfit / totalRevenue) * 100) : 0;

    var branchProfit = {};
    sales.forEach(function (s) {
      if (!branchProfit[s.branch]) branchProfit[s.branch] = 0;
      branchProfit[s.branch] += s.total;
    });

    var output = 'Total Revenue: Br ' + totalRevenue.toFixed(2) + '\n';
    output += 'Estimated Cost: Br ' + totalCost.toFixed(2) + '\n';
    output += 'Gross Profit: Br ' + grossProfit.toFixed(2) + '\n';
    output += 'Profit Margin: ' + margin.toFixed(1) + '%\n\n';
    output += '--- REVENUE BY BRANCH ---\n';
    for (var br in branchProfit) {
      output += br + ': Br ' + branchProfit[br].toFixed(2) + '\n';
    }
    output += '\n--- INVENTORY ASSET VALUE ---\n';
    var costValue = inventory.reduce(function (a, c) { return a + ((c.costPrice || 0) * c.stock); }, 0);
    var retailValue = inventory.reduce(function (a, c) { return a + (c.price * c.stock); }, 0);
    output += 'At Cost: Br ' + costValue.toFixed(2) + '\n';
    output += 'At Retail: Br ' + retailValue.toFixed(2) + '\n';
    output += 'Potential Margin: Br ' + (retailValue - costValue).toFixed(2);
    log.textContent = output;
  }
}

function renderStockAlerts() {
  var container = document.getElementById('stockAlertsContainer');
  if (!container) return;
  var low = (window.MOCK_DATA.inventory || []).filter(function (i) { return i.stock < 5; });
  var damaged = (window.MOCK_DATA.inventory || []).filter(function (i) { return (i.damaged || 0) > 0; });

  if (low.length === 0 && damaged.length === 0) {
    container.innerHTML = '<p class="alertgood">All stock levels are optimal. No damaged items reported.</p>';
    return;
  }

  var html = '';
  low.forEach(function (item) {
    html += '<div class="alertbox">' +
      '<h4 class="alerttitle">Low Stock: ' + item.name + '</h4>' +
      '<p class="alertdesc">Only ' + item.stock + ' left in ' + item.branch + '. SKU: ' + item.id + '</p>' +
      '</div>';
  });
  damaged.forEach(function (item) {
    html += '<div class="alertboxdamage">' +
      '<h4 class="alerttitle">Damaged: ' + item.name + '</h4>' +
      '<p class="alertdesc">' + item.damaged + ' unit(s) damaged in ' + item.branch + '. SKU: ' + item.id + '</p>' +
      '</div>';
  });
  container.innerHTML = html;
}

function checkAlerts() {
  if (document.getElementById('view-stock-ops') && document.getElementById('view-stock-ops').classList.contains('active')) {
    renderStockAlerts();
  }
}

function applyDiscount() {
  if (cart.length === 0) return alert('Add items first.');
  var pct = prompt('Enter discount percentage (e.g. 10 for 10%):');
  if (pct !== null && !isNaN(pct)) {
    var val = parseFloat(pct);
    if (val > 0 && val <= 50) {
      appliedDiscount = val / 100;
      updateCartUI();
      alert('Discount of ' + val + '% applied.');
    } else {
      alert('Discount must be between 1% and 50%.');
    }
  }
}

function changePassword() {
  document.getElementById('passwordModal').classList.remove('hidden');
}

function closePasswordModal() {
  document.getElementById('passwordModal').classList.add('hidden');
  document.getElementById('currentPass').value = '';
  document.getElementById('newPass').value = '';
  document.getElementById('confirmPass').value = '';
}

function submitPasswordChange() {
  var currentPass = document.getElementById('currentPass').value;
  var newPass = document.getElementById('newPass').value;
  var confirmPass = document.getElementById('confirmPass').value;
  var userName = document.getElementById('userNameDisplay').textContent;

  if (!currentPass || !newPass || !confirmPass) {
    alert('Please fill all fields.');
    return;
  }

  if (newPass !== confirmPass) {
    alert('New passwords do not match.');
    return;
  }

  if (newPass.length < 6) {
    alert('Password must be at least 6 characters.');
    return;
  }

  var user = window.MOCK_DATA.users.find(function (u) { return u.name === userName; });
  if (!user) {
    alert('User not found.');
    return;
  }

  if (user.password !== currentPass) {
    alert('Current password is incorrect.');
    return;
  }

  user.password = newPass;
  window.saveDB();
  closePasswordModal();
  alert('Password updated successfully.');
}

function setupMobileMenu() {
  var sidebar = document.getElementById('sidebar');
  var openBtn = document.getElementById('mobileMenuBtn');
  var closeBtn = document.getElementById('mobileMenuClose');

  var toggle = function () {
    var mobile = window.innerWidth <= 768;
    if (openBtn) openBtn.classList.toggle('hidden', !mobile);
    if (closeBtn) closeBtn.classList.toggle('hidden', !mobile);
    if (!mobile && sidebar) sidebar.classList.remove('open');
  };

  toggle();
  window.addEventListener('resize', toggle);
  if (openBtn) openBtn.onclick = function () { sidebar.classList.add('open'); };
  if (closeBtn) closeBtn.onclick = function () { sidebar.classList.remove('open'); };
}
