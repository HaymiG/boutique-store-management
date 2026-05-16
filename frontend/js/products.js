// ===================================
// PRODUCTS.JS — Product Management
// ===================================

let _categories = [];
let _currentPage = 1;
let _searchTimer = null;

// ---- Data loading ----

async function loadProducts(page = 1) {
  _currentPage = page;
  const search     = document.getElementById('productSearch')?.value || '';
  const categoryId = document.getElementById('categoryFilter')?.value || '';

  const params = new URLSearchParams({ page });
  if (search)     params.set('search', search);
  if (categoryId) params.set('category_id', categoryId);

  const tbody = document.getElementById('productsTableBody');
  if (tbody) tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted)">Loading…</td></tr>';

  try {
    const res = await API.get('/api/products?' + params);
    renderProductsTable(res.data.data);
    renderPagination(res.data.total_pages, res.data.page);
  } catch (e) {
    if (tbody) tbody.innerHTML = '<tr><td colspan="8" style="color:var(--danger);text-align:center">Failed to load products</td></tr>';
  }
}

async function loadCategories() {
  try {
    const res = await API.get('/api/products/categories');
    _categories = Array.isArray(res.data) ? res.data : (res.data.data || []);
    populateCategoryDropdowns();
  } catch (e) {
    console.error('Failed to load categories', e);
  }
}

// ---- Rendering ----

function renderProductsTable(items) {
  const tbody = document.getElementById('productsTableBody');
  if (!tbody) return;

  if (!items || !items.length) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted)">No products found</td></tr>';
    return;
  }

  tbody.innerHTML = items.map(item => {
    const stock = item.total_stock ?? 0;
    const statusClass = stock === 0 ? 'badge-danger' : (item.is_low_stock ? 'badge-warning' : 'badge-success');
    const statusText  = stock === 0 ? 'Out' : (item.is_low_stock ? 'Low' : 'In Stock');
    return `<tr>
      <td style="color:var(--text-secondary)">${item.sku}</td>
      <td style="font-weight:600">${item.name}</td>
      <td>${item.category_name || '—'}</td>
      <td>$${parseFloat(item.cost_price).toFixed(2)}</td>
      <td>$${parseFloat(item.selling_price).toFixed(2)}</td>
      <td>${stock}</td>
      <td><span class="badge ${statusClass}">${statusText}</span></td>
      <td>
        <button class="btn btn-outline" style="padding:0.25rem 0.75rem;font-size:0.75rem;margin-right:0.25rem" onclick="openProductForm(${item.id})">Edit</button>
        <button class="btn btn-danger"  style="padding:0.25rem 0.75rem;font-size:0.75rem" onclick="deleteProduct(${item.id},'${item.name.replace(/'/g,"\\'")}')">Del</button>
      </td>
    </tr>`;
  }).join('');
}

function renderPagination(totalPages, current) {
  const el = document.getElementById('productsPagination');
  if (!el) return;
  if (!totalPages || totalPages <= 1) { el.innerHTML = ''; return; }
  let html = '';
  for (let i = 1; i <= totalPages; i++) {
    const cls = i === current ? 'btn-primary' : 'btn-outline';
    html += `<button class="btn ${cls}" style="padding:0.4rem 0.9rem" onclick="loadProducts(${i})">${i}</button>`;
  }
  el.innerHTML = html;
}

function populateCategoryDropdowns() {
  const opts = _categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('');

  const filter = document.getElementById('categoryFilter');
  if (filter) filter.innerHTML = '<option value="">All Categories</option>' + opts;

  const formSel = document.getElementById('pCategory');
  if (formSel) formSel.innerHTML = '<option value="">Select category</option>' + opts;
}

// ---- Search ----

function debounceSearch() {
  clearTimeout(_searchTimer);
  _searchTimer = setTimeout(() => loadProducts(1), 350);
}

// ---- Product form modal ----

async function openProductForm(id = null) {
  await loadCategories();

  document.getElementById('productModalTitle').textContent = id ? 'Edit Product' : 'Add Product';
  document.getElementById('productId').value   = id || '';
  document.getElementById('pName').value       = '';
  document.getElementById('pSku').value        = '';
  document.getElementById('pCost').value       = '';
  document.getElementById('pPrice').value      = '';
  document.getElementById('pReorder').value    = '10';
  document.getElementById('pDesc').value       = '';
  document.getElementById('pCategory').value   = '';

  if (id) {
    try {
      const res = await API.get('/api/products/' + id);
      const p = res.data;
      document.getElementById('pName').value     = p.name;
      document.getElementById('pSku').value      = p.sku;
      document.getElementById('pCost').value     = p.cost_price;
      document.getElementById('pPrice').value    = p.selling_price;
      document.getElementById('pReorder').value  = p.reorder_level;
      document.getElementById('pDesc').value     = p.description || '';
      document.getElementById('pCategory').value = p.category_id;
    } catch (e) { alert('Failed to load product'); return; }
  }

  document.getElementById('productModal').style.display = 'flex';
}

function closeProductModal() {
  document.getElementById('productModal').style.display = 'none';
}

async function submitProductForm(e) {
  e.preventDefault();
  const id = document.getElementById('productId').value;
  const payload = {
    name:          document.getElementById('pName').value,
    sku:           document.getElementById('pSku').value,
    category_id:   document.getElementById('pCategory').value,
    cost_price:    document.getElementById('pCost').value,
    selling_price: document.getElementById('pPrice').value,
    reorder_level: document.getElementById('pReorder').value,
    description:   document.getElementById('pDesc').value,
  };

  try {
    if (id) {
      await API.put('/api/products/' + id, payload);
    } else {
      await API.post('/api/products', payload);
    }
    closeProductModal();
    loadProducts(_currentPage);
  } catch (e) {
    alert(e.message || 'Failed to save product');
  }
}

async function deleteProduct(id, name) {
  if (!confirm(`Delete "${name}"?`)) return;
  try {
    await API.delete('/api/products/' + id);
    loadProducts(_currentPage);
  } catch (e) {
    alert('Failed to delete product');
  }
}

// ---- Category modal ----

async function openCategoryModal() {
  await loadCategories();
  renderCategoryList();
  document.getElementById('newCategoryName').value = '';
  document.getElementById('categoryModal').style.display = 'flex';
}

function closeCategoryModal() {
  document.getElementById('categoryModal').style.display = 'none';
}

function renderCategoryList() {
  const el = document.getElementById('categoryList');
  if (!el) return;
  if (!_categories.length) {
    el.innerHTML = '<p style="color:var(--text-muted);font-size:0.875rem">No categories yet</p>';
    return;
  }
  el.innerHTML = _categories.map(c => `
    <div class="d-flex justify-between align-center" style="padding:0.5rem 0;border-bottom:1px solid var(--border)">
      <span style="font-size:0.875rem;font-weight:500">${c.name} <small style="color:var(--text-muted)">(${c.item_count || 0})</small></span>
      <button class="btn btn-danger" style="padding:0.2rem 0.6rem;font-size:0.75rem" onclick="deleteCategory(${c.id},'${c.name.replace(/'/g,"\\'")}')">Delete</button>
    </div>
  `).join('');
}

async function createCategory() {
  const name = document.getElementById('newCategoryName').value.trim();
  if (!name) return;
  try {
    await API.post('/api/products/categories', { name });
    document.getElementById('newCategoryName').value = '';
    await loadCategories();
    renderCategoryList();
  } catch (e) {
    alert(e.message || 'Failed to create category');
  }
}

async function deleteCategory(id, name) {
  if (!confirm(`Delete category "${name}"?`)) return;
  try {
    await API.delete('/api/products/categories/' + id);
    await loadCategories();
    renderCategoryList();
  } catch (e) {
    alert(e.message || 'Cannot delete — category may have items');
  }
}

// ---- Hook into switchView to auto-load on tab open ----
(function() {
  const _orig = window.switchView;
  window.switchView = function(viewId) {
    if (_orig) _orig(viewId);
    if (viewId === 'products') {
      loadCategories().then(() => loadProducts(1));
    }
  };
})();
