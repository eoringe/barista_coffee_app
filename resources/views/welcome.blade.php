<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Barista Admin</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <style>
        :root {
          --bg: #F6F5F3;
          --fg: #1f1e1b;
          --card: #ffffff;
          --primary: #c47a33;
          --primary-dark: #a36127;
          --border: #e2e0dc;
          --radius: 10px;
        }
        @media (prefers-color-scheme: dark) {
          :root {
            --bg: #0c0c0c;
            --fg: #F2F1EF;
            --card: #161513;
            --border: #34332f;
          }
        }
        html, body {
          margin:0;
          padding:0;
          font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
        }
        body {
          background: var(--bg);
          color: var(--fg);
          min-height: 100vh;
          padding: 32px;
        }

        /* Layout */
        .container {
          max-width: 1280px;
          margin: 0 0 0 250px; /* Add left margin to prevent sidebar overlap */
          display: grid;
          gap: 32px;
          padding: 20px; /* Add some padding for better spacing */
        }
        @media (min-width: 1060px) {
          .container { grid-template-columns: 240px 1fr; }
        }

        /* Sidebar */
        .sidebar {
          background: var(--card);
          border-radius: var(--radius);
          padding: 20px;
          box-shadow: 0 2px 6px rgba(0,0,0,.07);
        }
        .sidebar a {
          display:flex;
          align-items:center;
          gap:10px;
          padding:12px;
          text-decoration:none;
          color:inherit;
          border-radius: var(--radius);
          font-size:14px;
          cursor:pointer;
        }
        .sidebar a.active {
          background: var(--primary);
          color:#fff;
        }

        /* Cards */
        .card {
          background: var(--card);
          border-radius: var(--radius);
          overflow:hidden;
          box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .p-4 { padding:20px; }

        /* Inputs, Buttons */
        .input, .btn {
          padding:10px 14px;
          border-radius:var(--radius);
          font-size:14px;
          border:1px solid var(--border);
        }
        .btn {
          background:var(--primary);
          color:#fff;
          border:none;
          cursor:pointer;
        }
        .btn:hover {
          background:var(--primary-dark);
        }

        /* Grid */
        .grid {
          display:grid;
          gap:24px;
        }
        @media (min-width: 768px) {
          .grid { grid-template-columns: repeat(3, 1fr); }
        }

        /* Stats */
        .stats {
          display:grid;
          gap:20px;
        }
        @media (min-width: 640px) {
          .stats { grid-template-columns: repeat(4, 1fr); }
        }
        .stats .card p:first-child {
          font-size:13px;
          opacity:.6;
        }

        /* Images */
        .thumb {
          height:200px;
          background:#e8e7e4;
          overflow:hidden;
        }
        .thumb img {
          width:100%;
          height:100%;
          object-fit:cover;
        }

        /* Flex helpers */
        .flex { display:flex; }
        .items-center { align-items:center; }
        .justify-between { justify-content:space-between; }
        .gap-2 { gap:8px; }
        .gap-3 { gap:12px; }
        .gap-4 { gap:16px; }
        .text-muted { font-size:14px; opacity:.6; }

        /* Availability pill */
        [data-availability] {
          font-size:12px;
          padding:4px 8px;
          border-radius:6px;
          font-weight:600;
        }
        [data-availability="Available"] {
          background:rgba(34,197,94,0.15);
          color:#15803d;
        }
        [data-availability="Out of stock"] {
          background:rgba(239,68,68,0.15);
          color:#b91c1c;
        }

        /* Modal */
        .modal-backdrop {
          position:fixed;
          inset:0;
          background:rgba(0,0,0,.5);
          display:none;
          align-items:center;
          justify-content:center;
          padding:16px;
          z-index:50;
        }
        .modal-backdrop.show {
          display:flex;
        }
        .modal {
          width:100%;
          max-width:640px;
        }

        /* Skeleton Loading */
        @keyframes skel {
          0% { background-position: 100% 0; }
          100% { background-position: 0 0; }
        }
        .skel {
          background: linear-gradient(90deg, #e8e7e4 25%, #f2f1ef 37%, #e8e7e4 63%);
          background-size: 400% 100%;
          animation: skel 1.2s ease-in-out infinite;
          border-radius: var(--radius);
        }
        .skel-line { border-radius: 8px; }
        .skel-bar {
          display:inline-block;
          width:64px; height:22px;
          border-radius: 8px;
          background: linear-gradient(90deg, #e8e7e4 25%, #f2f1ef 37%, #e8e7e4 63%);
          background-size: 400% 100%;
          animation: skel 1.2s ease-in-out infinite;
        }

        /* Loading Spinner */
        @keyframes spin {
          to { transform: rotate(360deg); }
        }
        </style>
    </head>

<body>
<!-- Loading Indicator -->
<div id="loadingIndicator" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);display:none;align-items:center;justify-content:center;z-index:9999">
  <div style="background:var(--card);padding:24px 32px;border-radius:var(--radius);box-shadow:0 4px 12px rgba(0,0,0,0.2);text-align:center">
    <div style="width:40px;height:40px;border:4px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 12px"></div>
    <p style="margin:0;font-weight:600;font-size:14px">Loading...</p>
  </div>
</div>

<div class="container">
    <aside class="sidebar">
        <h2 style="font-weight:700;margin:0 0 16px;font-size:18px">Barista Admin</h2>
        <a id="tabMenu" class="active">Menu</a>
        <a id="tabSpecials">Today's Specials</a>
        <a id="tabCategories">Categories</a>
        <a style="opacity:.5;cursor:not-allowed;">Orders</a>
        <a style="opacity:.5;cursor:not-allowed;">Settings</a>
    </aside>

    <main class="flex" style="flex-direction:column; gap:16px; height: 100%;">
        <section class="card p-4" id="menuSection" style="display:flex; flex-direction: column; height: 100%;">
            <div class="flex items-center justify-between" style="margin-bottom: 16px;">
                <h2 style="margin:0;font-weight:600">Menu Items</h2>
                <div class="flex items-center gap-2">
                    <select id="categoryFilter" class="input" style="min-width: 150px;">
                        <option value="">All Categories</option>
                        <!-- Categories will be populated by JavaScript -->
                    </select>
                    <input id="menuSearch" class="input" placeholder="Search menu items..." style="min-width: 200px;" />
                    <button id="openCreateModalBtn" class="btn">Add Item</button>
                </div>
            </div>
            
            <div id="menuList" class="menu-items-list" style="flex: 1; min-height: 0; overflow-y: auto;">
                <!-- Menu items will be inserted here by JavaScript -->
            </div>
            
            <template id="menuCardTemplate">
                <div class="menu-list-item">
                    <img class="menu-item-image" data-img alt="Menu item" onerror="this.style.display='none'" />
                    <div class="menu-item-details">
                        <div class="menu-item-name" data-title></div>
                        <div class="menu-item-category" data-category></div>
                    </div>
                    <div class="menu-item-price" data-price></div>
                    <div class="menu-item-actions">
                        <div class="menu-item-availability" data-availability></div>
                        <div class="action-buttons">
                            <button class="btn-edit" data-edit>Edit</button>
                            <button class="btn-delete" data-delete>Delete</button>
                        </div>
                    </div>
                </div>
            </template>

            <template id="skeletonCardTemplate">
                <div class="card">
                    <div class="thumb skel"></div>
                    <div class="p-4" style="font-size:14px">
                        <div class="flex items-center justify-between" style="margin-bottom:8px">
                            <div>
                                <div class="skel skel-line" style="width:140px;height:14px"></div>
                                <div class="skel skel-line" style="width:100px;height:12px;margin-top:6px"></div>
                            </div>
                            <div class="skel skel-line" style="width:110px;height:14px"></div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="skel skel-line" style="width:120px;height:12px"></div>
                            <div class="flex gap-2">
                                <div class="skel" style="width:56px;height:30px;border-radius:10px"></div>
                                <div class="skel" style="width:64px;height:30px;border-radius:10px"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </section>

        <!-- Specials Section -->
        <section class="card p-4" id="specialsSection" style="display:none">
            <div class="flex items-center justify-between" style="margin-bottom:16px">
                <h2 style="margin:0;font-weight:600">Today's Specials</h2>
            </div>
            <div id="specialsGrid" class="grid"></div>
        </section>
        
        <!-- Categories Section -->
        <section class="card p-4" id="categoriesSection" style="display:none">
            <div class="flex items-center justify-between" style="margin-bottom:16px">
                <h2 style="margin:0;font-weight:600">Categories</h2>
                <div class="flex gap-2">
                    <input id="newCategoryName" class="input" placeholder="New category name" style="max-width:200px" />
                    <button id="addCategoryBtn" class="btn">Add</button>
                </div>
            </div>
            <div id="categoriesList" style="display:flex;flex-direction:column;gap:8px"></div>
            <template id="categoryItemTemplate">
                <div class="flex items-center justify-between card" style="padding:12px 16px">
                    <div class="flex items-center gap-2" style="flex:1">
                        <input data-edit-name class="input" style="flex:1;max-width:300px" />
                    </div>
                    <div class="flex items-center gap-2">
                        <button data-save class="btn" style="padding:8px 16px;font-size:13px">Save</button>
                        <button data-delete style="background:#e73f3f;color:#fff;border:none;padding:8px 16px;border-radius:var(--radius);cursor:pointer;font-size:13px">Delete</button>
                    </div>
                </div>
            </template>
        </section>
    </main>
</div>

<!-- Modal -->
<div id="menuModal" class="modal-backdrop">
    <div class="modal card">
        <div class="flex items-center justify-between p-4" style="border-bottom:1px solid var(--border)">
            <h2 id="modalTitle" style="font-size:18px;font-weight:600;margin:0">Add Item</h2>
            <button id="closeModalBtn" class="input" style="cursor:pointer">Close</button>
        </div>
        <form id="menuForm" class="p-4" enctype="multipart/form-data" style="display:grid;gap:12px">
            <input type="hidden" id="menuId" name="menuId" />
            <div>
                <label style="font-size:14px;margin-bottom:4px;display:block">Coffee Title</label>
                <input id="coffee_title" name="coffee_title" required class="input" placeholder="Espresso" />
            </div>
            <div>
                <label style="font-size:14px;margin-bottom:4px;display:block">Category</label>
                <select id="category" name="category" required class="input" style="width:100%">
                    <option value="">Select a category...</option>
                </select>
            </div>
            <div class="flex gap-3" style="flex-wrap:wrap">
                <div style="flex:1 1 160px">
                    <label style="font-size:14px;margin-bottom:4px;display:block">Single Price (cents)</label>
                    <input id="single_price" name="single_price" type="number" min="0" required class="input">
                </div>
                <div style="flex:1 1 160px">
                    <label style="font-size:14px;margin-bottom:4px;display:block">Double Price (cents)</label>
                    <input id="double_price" name="double_price" type="number" min="0" required class="input">
                </div>
            </div>
            <div class="flex gap-3" style="flex-wrap:wrap">
                <div style="flex:1 1 160px">
                    <label style="font-size:14px;margin-bottom:4px;display:block">Portions Available</label>
                    <input id="portion_available" name="portion_available" type="number" min="0" required class="input">
                </div>
                <label class="flex items-center gap-2" style="margin-top:28px;font-size:14px">
                    <input type="checkbox" id="available" name="available" class="form-checkbox h-4 w-4 text-primary-600">
                    <label for="available" class="text-sm font-medium">Available</label>
                </label>
            </div>
            <div class="flex flex-col gap-4">
              <label class="flex items-center gap-2" style="font-size:14px">
                <input type="checkbox" id="special" name="special" class="form-checkbox h-4 w-4 text-yellow-500">
                <label for="special" class="text-sm font-medium">Today's Special</label>
              </label>
            </div>
            <div>
                <label style="font-size:14px;margin-bottom:4px;display:block">Image</label>
                <input id="image" name="image" type="file" accept="image/*" />
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="button" id="cancelModalBtn" class="input" style="cursor:pointer">Cancel</button>
                <button type="submit" class="btn">Save</button>
            </div>
            <p id="formError" style="color:#e73f3f;font-size:14px;display:none"></p>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script>
(function() {
  const $ = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));

  // Get CSRF token
  const getCsrfToken = () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    console.log('[CSRF] Token:', token ? 'Found' : 'NOT FOUND');
    return token;
  };

  const api = {
    async list() {
      console.log('[API] Fetching menu items from /menu-items');
      const res = await fetch('/menu-items', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      return res.json();
    },
    async create(fd) {
      console.log('[API] Creating menu item via POST /menu-items');
      const token = getCsrfToken();
      const res = await fetch('/menu-items', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: fd
      });
      return res.json();
    },
    async update(id, fd) {
      console.log(`[API] Updating menu item ${id} via POST /menu-items/${id}`);
      const token = getCsrfToken();
      const res = await fetch(`/menu-items/${id}`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: fd
      });
      return res.json();
    },
    async remove(id) {
      console.log(`[API] Deleting menu item ${id} via DELETE /menu-items/${id}`);
      const token = getCsrfToken();
      const res = await fetch(`/menu-items/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      return res.json();
    }
  };

  const catApi = {
    async list() {
      const r = await fetch('/categories', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      });
      return r.json();
    },
    async create(name) {
      const fd = new FormData();
      fd.append('name', name);
      const token = getCsrfToken();
      const r = await fetch('/categories', {
        method:'POST',
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: fd
      });
      return r.json();
    },
    async update(id, name) {
      const fd = new FormData();
      fd.append('name', name);
      const token = getCsrfToken();
      const r = await fetch(`/categories/${id}`, {
        method:'POST',
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: fd
      });
      return r.json();
    },
    async remove(id) {
      const token = getCsrfToken();
      const r = await fetch(`/categories/${id}`, {
        method:'DELETE',
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      return r.json();
    },
  };

  function formatPrice(single, doubleP) {
    const s = Number(single);
    const d = Number(doubleP);
    if (Number.isFinite(s) && Number.isFinite(d)) {
      return `${s} KES / ${d} KES`;
    }
    return `${single} KES / ${doubleP} KES`;
  }

  function setStat(id, val) { 
    const el = document.getElementById(id); 
    if (el) el.textContent = val; 
  }

  function showLoading() {
    const skelHTML = '<span class="skel-bar"></span>';
    const s1 = document.getElementById('statTotal'); if (s1) s1.innerHTML = skelHTML;
    const s2 = document.getElementById('statAvailable'); if (s2) s2.innerHTML = skelHTML;
    const s3 = document.getElementById('statWithImage'); if (s3) s3.innerHTML = skelHTML;
    const s4 = document.getElementById('statOut'); if (s4) s4.innerHTML = skelHTML;

    const grid = $('#menuGrid');
    const tpl = $('#skeletonCardTemplate');
    if (grid && tpl) {
      grid.innerHTML = '';
      for (let i = 0; i < 6; i++) {
        grid.appendChild(tpl.content.firstElementChild.cloneNode(true));
      }
    }
  }

  function showLoadingIndicator() {
    const indicator = document.getElementById('loadingIndicator');
    if (indicator) indicator.style.display = 'flex';
  }

  function hideLoadingIndicator() {
    const indicator = document.getElementById('loadingIndicator');
    if (indicator) indicator.style.display = 'none';
  }

  async function loadMenu() {
    const list = document.getElementById('menuList');
    const tpl = document.getElementById('menuCardTemplate');
    if (!list || !tpl) return;
    showLoading();
    try {
      const data = await api.list();
      const items = Array.isArray(data?.data) ? data.data : [];
      
      // Clear the list and show loading skeleton
      list.innerHTML = '';
      
      // Add skeleton loading
      const skeletonCount = 5;
      for (let i = 0; i < skeletonCount; i++) {
        const skeleton = document.createElement('div');
        skeleton.className = 'menu-list-item';
        skeleton.innerHTML = `
          <div class="menu-item-content">
            <div class="menu-item-details">
              <div class="skel-line" style="width: 150px; height: 18px; margin-bottom: 4px;"></div>
              <div class="skel-line" style="width: 100px; height: 14px;"></div>
            </div>
            <div class="skel-line" style="width: 80px; height: 18px;"></div>
          </div>
          <div class="menu-item-actions">
            <div class="skel-line" style="width: 80px; height: 24px; border-radius: 4px;"></div>
            <div class="action-buttons">
              <div class="skel-line" style="width: 50px; height: 28px; border-radius: 6px;"></div>
              <div class="skel-line" style="width: 60px; height: 28px; border-radius: 6px;"></div>
            </div>
          </div>
        `;
        list.appendChild(skeleton);
      }
      
      // Load categories if not already loaded
      if (categories.length === 0) {
        await loadCategories();
      }
      
      // Clear the list again before adding real items
      list.innerHTML = '';
      
      if (items.length === 0) {
        const emptyMessage = document.createElement('div');
        emptyMessage.className = 'text-center py-8 text-gray-500';
        emptyMessage.textContent = 'No menu items found. Click "Add Item" to get started.';
        list.appendChild(emptyMessage);
        return;
      }
      
      // Render menu items
      items.forEach(item => {
        const node = tpl.content.firstElementChild.cloneNode(true);
        
        // Set item data
        node.querySelector('[data-title]').textContent = item.coffee_title || 'Unnamed Item';
        node.querySelector('[data-category]').textContent = item.category?.name || 'Uncategorized';
        
        // Format price
        const price = formatPrice(item.single_price, item.double_price);
        node.querySelector('[data-price]').textContent = price;
        
        // Set availability
        const isAvailable = item.available && Number(item.portion_available) > 0;
        const availabilityEl = node.querySelector('[data-availability]');
        availabilityEl.textContent = isAvailable ? 'Available' : 'Out of stock';
        availabilityEl.setAttribute('data-availability', isAvailable ? 'Available' : 'Out of stock');
        
        // Set up edit and delete buttons
        const editBtn = node.querySelector('[data-edit]');
        const deleteBtn = node.querySelector('[data-delete]');
        
        if (editBtn) {
          editBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            openModal('edit', item);
          });
        }
        
        if (deleteBtn) {
          deleteBtn.addEventListener('click', async (e) => {
            e.stopPropagation();
            if (confirm('Are you sure you want to delete this item?')) {
              try {
                await api.remove(item.id);
                loadMenu(); // Refresh the list
              } catch (error) {
                console.error('Failed to delete item:', error);
                alert('Failed to delete item. Please try again.');
              }
            }
          });
        }
        
        // Make the whole item clickable to edit
        node.style.cursor = 'pointer';
        node.addEventListener('click', () => {
          openModal('edit', item);
        });
        
        list.appendChild(node);
      });
      
    } catch (e) {
      console.error('Failed to load menu:', e);
      const errorMsg = document.createElement('div');
      errorMsg.className = 'text-center py-8 text-red-500';
      errorMsg.textContent = 'Failed to load menu items. Please try again.';
      list.appendChild(errorMsg);
    }
  }

  function openModal(mode, item) {
    const wrap = $('#menuModal');
    const title = $('#modalTitle');
    const form = $('#menuForm');
    const err = $('#formError');
    if (!wrap || !form) return;
    err.style.display = 'none';
    form.reset();
    $('#menuId').value = '';
    
    if (mode === 'edit' && item) {
      title.textContent = 'Edit Item';
      $('#menuId').value = item.id;
      $('#coffee_title').value = item.coffee_title || '';
      $('#single_price').value = item.single_price ?? '';
      $('#double_price').value = item.double_price ?? '';
      $('#portion_available').value = item.portion_available ?? '';
      $('#available').checked = !!item.available;
      $('#special').checked = !!item.special;
    } else {
      title.textContent = 'Add Item';
    }
    
    // Show modal immediately
    wrap.classList.add('show');
    
    // Load categories in background and set selected value after
    loadCategoryDropdown().then(() => {
      if (mode === 'edit' && item) {
        $('#category').value = item.category?.name || '';
      }
    });
  }

  function closeModal() {
    const wrap = $('#menuModal'); 
    if (wrap) wrap.classList.remove('show');
  }

  async function loadCategoryDropdown() {
    const select = $('#category');
    if (!select) return;
    try {
      const res = await catApi.list();
      const cats = Array.isArray(res?.data) ? res.data : [];
      select.innerHTML = '<option value="">Select a category...</option>';
      cats.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.name;
        option.textContent = cat.name;
        select.appendChild(option);
      });
    } catch (e) {
      console.error('Failed to load categories:', e);
    }
  }

  function toFormData(payload) {
    const fd = new FormData();
    Object.entries(payload).forEach(([k, v]) => { 
      if (v !== undefined && v !== null) fd.append(k, v); 
    });
    return fd;
  }

  async function submitForm(e) {
    e.preventDefault();
    console.log('========== FORM SUBMISSION STARTED ==========');
    const id = $('#menuId').value;
    console.log('Menu Item ID:', id);
    console.log('Is Update:', !!id);
    const saveBtn = e.submitter || document.querySelector('#menuForm button[type="submit"]');
    const original = saveBtn ? saveBtn.textContent : '';
    if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Saving...'; }
    showLoadingIndicator();
    const payload = {
      coffee_title: $('#coffee_title').value,
      category: $('#category').value,
      single_price: $('#single_price').value,
      double_price: $('#double_price').value,
      available: $('#available').checked ? '1' : '0',
      special: $('#special').checked ? '1' : '0',
      portion_available: $('#portion_available').value,
    };
    console.log('Form payload:', payload);
    const file = $('#image').files && $('#image').files[0];
    if (file) {
      payload.image = file;
      console.log('File attached:', file.name, file.size, 'bytes');
    }
    const fd = toFormData(payload);
    console.log('FormData entries:');
    for (let [key, value] of fd.entries()) {
      console.log(`  - ${key}:`, value);
    }
    const err = $('#formError');
    try {
      const result = id ? await api.update(id, fd) : await api.create(fd);
      console.log('API Response:', result);
      if (!result?.success) throw new Error(result?.message || 'Failed');
      console.log('✓ Menu item saved successfully!');
      console.log('========== FORM SUBMISSION COMPLETED ==========');
      closeModal();
      loadMenu();
    } catch (ex) {
      console.error('========== FORM SUBMISSION ERROR ==========');
      console.error('Error:', ex);
      console.error('==========================================');
      err.textContent = ex?.message || 'Failed to save';
      err.style.display = 'block';
    } finally {
      hideLoadingIndicator();
      if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = original || 'Save'; }
    }
  }

  async function loadCategories() {
    const list = document.getElementById('categoriesList');
    const tpl = document.getElementById('categoryItemTemplate');
    if (!list || !tpl) return;
    list.innerHTML = '';
    // show skeletons
    for (let i=0;i<4;i++) list.insertAdjacentHTML('beforeend', '<div class="card p-4 skel" style="height:56px"></div>');
    try {
      const res = await catApi.list();
      const cats = Array.isArray(res?.data) ? res.data : [];
      list.innerHTML = '';
      cats.forEach(cat => {
        const node = tpl.content.firstElementChild.cloneNode(true);
        const nameInput = node.querySelector('[data-edit-name]');
        const saveBtn = node.querySelector('[data-save]');
        const deleteBtn = node.querySelector('[data-delete]');
        nameInput.value = cat.name;
        saveBtn.addEventListener('click', async () => {
          saveBtn.disabled = true; const text = saveBtn.textContent; saveBtn.textContent = 'Saving...';
          const r = await catApi.update(cat.id, nameInput.value);
          saveBtn.disabled = false; saveBtn.textContent = text;
          if (!r?.success) alert(r?.message || 'Failed to update');
        });
        deleteBtn.addEventListener('click', async () => {
          if (!confirm('Delete this category?')) return;
          const r = await catApi.remove(cat.id);
          if (!r?.success) { alert(r?.message || 'Failed to delete'); return; }
          loadCategories();
        });
        list.appendChild(node);
      });
    } catch (e) {
      list.innerHTML = '<p style="color:#e73f3f">Failed to load categories.</p>';
    }
  }

  async function loadSpecials() {
    const grid = $('#specialsGrid');
    const tpl = $('#menuCardTemplate');
    if (!grid || !tpl) return;
    showLoading();
    try {
      const response = await fetch('/menu-items?special=1', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      const data = await response.json();
      if (!data.success) throw new Error('Failed to load specials');
      
      grid.innerHTML = '';
      const items = data.data;
      
      if (items.length === 0) {
        grid.innerHTML = '<p class="text-sm text-gray-500">No specials available.</p>';
        return;
      }
      
      items.forEach(item => {
        const node = tpl.content.firstElementChild.cloneNode(true);
        const img = node.querySelector('[data-img]');
        
        // Prefer backend-provided image_url; fallback to /storage/{image_path}
        const fallbackPath = item.image_path ? `/storage/${String(item.image_path).replace(/^\+|^\/\/+/, '')}` : null;
        const imgUrlRaw = item.image_url || fallbackPath;
        
        // Normalize host to http://127.0.0.1:8000 when a localhost/127 URL is provided
        let imgUrl = imgUrlRaw;
        try {
          if (imgUrlRaw) {
            const u = new URL(imgUrlRaw, window.location.origin);
            if (u.hostname === 'localhost' || u.hostname === '127.0.0.1') {
              u.protocol = 'http:';
              u.host = '127.0.0.1:8000';
              imgUrl = u.toString();
            }
          }
        } catch (e) {
          // keep original relative path
        }
        
        console.log('[Specials Image Debug] Item:', {
          id: item.id,
          coffee_title: item.coffee_title,
          image_url_field: item.image_url,
          image_path_field: item.image_path,
          computed_fallback: fallbackPath,
          final_url_used: imgUrl,
        });
        
        if (imgUrl) {
          img.src = imgUrl;
          img.classList.remove('hidden');
          img.onerror = () => {
            console.error(`Failed to load image: ${imgUrl}`);
            img.classList.add('hidden');
          };
        }
        
        node.querySelector('[data-title]').textContent = item.coffee_title;
        node.querySelector('[data-category]').textContent = item.category?.name || 'Uncategorized';
        node.querySelector('[data-price]').textContent = formatPrice(item.single_price, item.double_price);
        node.querySelector('[data-availability]').textContent = `${item.available ? 'Available' : 'Unavailable'} • ${item.portion_available} portions`;
        
        node.querySelector('[data-edit]').addEventListener('click', () => openModal('edit', item));
        node.querySelector('[data-delete]').addEventListener('click', async () => {
          if (!confirm('Delete this item?')) return;
          await fetch(`/menu-items/${item.id}`, {
            method: 'DELETE',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json'
            }
          });
          loadMenu();
          loadSpecials();
        });
        
        grid.appendChild(node);
      });
    } catch (e) {
      console.error('Error loading specials:', e);
      grid.innerHTML = '<p class="text-sm text-red-500">Failed to load specials.</p>';
    }
  }

  // Limit a CSS grid to display at most N items at once by calculating rows based on current columns
  function sizeMenuGridMaxItems(selector, maxItems) {
    const grid = document.querySelector(selector);
    if (!grid) return;
    
    const firstCard = grid.querySelector('.card');
    if (!firstCard) { 
      grid.style.maxHeight = ''; 
      return; 
    }
    
    // Get computed styles
    const cs = window.getComputedStyle(grid);
    const cols = Math.max(1, (cs.gridTemplateColumns || '').split(' ').filter(Boolean).length);
    const rowGap = parseFloat(cs.rowGap || cs.gridRowGap || '24') || 24;
    
    // Get actual card height including margins
    const cardRect = firstCard.getBoundingClientRect();
    const cardHeight = cardRect.height;
    
    // Calculate how many rows we need for maxItems
    const rows = Math.ceil(maxItems / cols);
    
    // Calculate total height: (rows * cardHeight) + ((rows - 1) * gap)
    const totalHeight = (rows * cardHeight) + ((rows - 1) * rowGap);
    
    grid.style.maxHeight = Math.ceil(totalHeight) + 'px';
    grid.style.overflowY = 'auto';
    grid.style.webkitOverflowScrolling = 'touch';
  }

  document.addEventListener('DOMContentLoaded', () => {
    loadMenu();
    
    const openBtn = document.getElementById('openCreateModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    const cancelBtn = document.getElementById('cancelModalBtn');
    const form = document.getElementById('menuForm');
    
    if (openBtn) openBtn.addEventListener('click', () => openModal('create'));
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (form) form.addEventListener('submit', submitForm);

    // Initial sizing: cap Menu Items to 4 visible cards
    sizeMenuGridMaxItems('#menuGrid', 4);

    // Recalculate on window resize
    window.addEventListener('resize', () => {
      sizeMenuGridMaxItems('#menuGrid', 4);
    });

    // Tab navigation
    const tabMenu = document.getElementById('tabMenu');
    const tab = document.getElementById('tabCategories');
    const specialsTab = document.getElementById('tabSpecials');
    const menuHeader = document.getElementById('menuHeader');
    const statsSection = document.getElementById('statsSection');
    const menuSection = document.getElementById('menuSection');
    const categoriesSection = document.getElementById('categoriesSection');
    const specialsSection = document.getElementById('specialsSection');
    const addCatBtn = document.getElementById('addCategoryBtn');
    const newCatInput = document.getElementById('newCategoryName');

    function setActive(tabEl) {
      [tabMenu, tab, specialsTab].forEach(el => { 
        if (!el) return; 
        el.classList.remove('active'); 
      });
      if (tabEl) tabEl.classList.add('active');
    }

    if (tabMenu) tabMenu.addEventListener('click', () => {
      // show menu
      setActive(tabMenu);
      menuHeader.style.display = '';
      statsSection.style.display = '';
      menuSection.style.display = '';
      categoriesSection.style.display = 'none';
      specialsSection.style.display = 'none';
      loadMenu();
      // Give the DOM a tick to layout, then cap to 4 visible items
      setTimeout(() => sizeMenuGridMaxItems('#menuGrid', 4), 0);
    });

    if (specialsTab) specialsTab.addEventListener('click', () => {
      // show specials
      setActive(specialsTab);
      menuHeader.style.display = 'none';
      statsSection.style.display = 'none';
      menuSection.style.display = 'none';
      categoriesSection.style.display = 'none';
      specialsSection.style.display = '';
      loadSpecials();
    });

    if (tab) tab.addEventListener('click', () => {
      // toggle to categories
      setActive(tab);
      menuHeader.style.display = 'none';
      statsSection.style.display = 'none';
      menuSection.style.display = 'none';
      categoriesSection.style.display = '';
      specialsSection.style.display = 'none';
      loadCategories();
    });
    
    if (addCatBtn) addCatBtn.addEventListener('click', async () => {
      const name = (newCatInput?.value || '').trim();
      if (!name) return;
      addCatBtn.disabled = true; 
      const text = addCatBtn.textContent; 
      addCatBtn.textContent = 'Adding...';
      const r = await catApi.create(name);
      addCatBtn.disabled = false; 
      addCatBtn.textContent = text;
      if (!r?.success) { 
        alert(r?.message || 'Failed to add'); 
        return; 
      }
      newCatInput.value = '';
      loadCategories();
    });
  });
})();
</script>

</body>
</html>