import './bootstrap';
import axios from 'axios';

const $ = (sel) => document.querySelector(sel);
const $$ = (sel) => Array.from(document.querySelectorAll(sel));

// Get CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const api = axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken,
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

// Add request interceptor to handle form data
api.interceptors.request.use(config => {
  // Don't set content type for FormData (handled by browser)
  if (config.data instanceof FormData) {
    delete config.headers['Content-Type'];
  }
  return config;
});

function formatPrice(single, doubleP) {
  const s = (Number(single) / 100).toFixed(2);
  const d = (Number(doubleP) / 100).toFixed(2);
  return `$${s} / $${d}`;
}

async function fetchMenu() {
  const grid = $('#menuGrid');
  if (!grid) return;
  grid.innerHTML = '';
  try {
    const response = await fetch('/menu-items', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    const data = await response.json();
    if (!data.success) throw new Error('Failed to load');
    const tpl = $('#menuCardTemplate');
    const items = data.data;
    // Update stats
    const total = items.length;
    const available = items.filter(i => i.available).length;
    const withImage = items.filter(i => !!i.image_url).length;
    const out = items.filter(i => !i.available || i.portion_available === 0).length;
    const setText = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value; };
    setText('statTotal', total);
    setText('statAvailable', available);
    setText('statWithImage', withImage);
    setText('statOut', out);

    items.forEach(item => {
      const node = tpl.content.firstElementChild.cloneNode(true);
      const img = node.querySelector('[data-img]');
      if (item.image_url) {
        img.src = item.image_url;
        img.classList.remove('hidden');
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
        fetchMenu();
      });

      grid.appendChild(node);
    });
  } catch (e) {
    grid.innerHTML = '<p class="text-sm text-[#f53003]">Failed to load menu.</p>';
  }
}

function openModal(mode, item) {
  const modal = $('#menuModal');
  const title = $('#modalTitle');
  const form = $('#menuForm');
  const formError = $('#formError');
  
  // Reset form and clear any previous errors
  form.reset();
  formError.classList.add('hidden');
  formError.textContent = '';
  
  // Clear any previous file input
  const fileInput = $('#image');
  if (fileInput) {
    fileInput.value = '';
  }
  
  // Set modal title and mode
  const isEdit = mode === 'edit';
  title.textContent = isEdit ? 'Edit Item' : 'Add Item';
  
  // Initialize form with item data or empty values
  const formData = {
    menuId: isEdit ? item.id : '',
    coffee_title: isEdit ? item.coffee_title : '',
    category: isEdit ? (item.category?.name || '') : '',
    single_price: isEdit ? item.single_price : '',
    double_price: isEdit ? item.double_price : '',
    available: isEdit ? !!item.available : true
  };
  
  // Set form field values
  Object.entries(formData).forEach(([key, value]) => {
    const element = form.querySelector(`[name="${key}"]`);
    if (element) {
      if (element.type === 'checkbox') {
        element.checked = value;
      } else {
        element.value = value;
      }
    }
  });
  
  // Show the modal
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  
  // Focus the first input field
  const firstInput = form.querySelector('input:not([type="hidden"]):not([type="file"])');
  if (firstInput) {
    setTimeout(() => firstInput.focus(), 100);
  }
}

function closeModal() {
  const modal = $('#menuModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

function toFormData(payload) {
  const fd = new FormData();
  Object.entries(payload).forEach(([k, v]) => {
    if (v !== undefined && v !== null) fd.append(k, v);
  });
  return fd;
}

async function handleSubmit(e) {
  e.preventDefault();
  console.log('========== FORM SUBMISSION STARTED ==========');
  
  const form = document.getElementById('menuForm');
  const id = form.querySelector('[name="menuId"]')?.value;
  
  console.log('Form ID:', form.id);
  console.log('Menu Item ID:', id);
  console.log('Is Update:', !!id);
  
  // Create new FormData from the form
  const formData = new FormData(form);
  
  console.log('Initial FormData entries:');
  for (let [key, value] of formData.entries()) {
    console.log(`  - ${key}:`, value);
  }
  
  // Ensure all fields are included
  const fields = ['coffee_title', 'category', 'single_price', 'double_price', 'portion_available', 'available'];
  console.log('Checking required fields...');
  fields.forEach(field => {
    const element = form.querySelector(`[name="${field}"]`);
    if (element) {
      const value = element.type === 'checkbox' ? element.checked : element.value;
      formData.set(field, value);
      console.log(`  ✓ ${field}:`, value, `(type: ${element.type})`);
    } else {
      console.error(`  ✗ Field not found: ${field}`);
    }
  });
  
  // Add CSRF token
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  formData.set('_token', csrfToken);
  console.log('CSRF Token:', csrfToken);
  
  // Determine URL and log request details
  const url = id ? `/menu-items/${id}` : '/menu-items';
  console.log('Request URL:', url);
  console.log('Request Method: POST');
  
  // Log final form data
  console.log('Final FormData being sent:');
  for (let [key, value] of formData.entries()) {
    if (value instanceof File) {
      console.log(`  - ${key}: [File] ${value.name} (${value.size} bytes)`);
    } else {
      console.log(`  - ${key}:`, value);
    }
  }
  
  try {
    console.log('Sending fetch request...');
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: formData
    });

    console.log('Response status:', response.status);
    console.log('Response headers:', Object.fromEntries(response.headers.entries()));
    
    const data = await response.json();
    console.log('Response data:', data);
    
    if (!response.ok) {
      console.error('Request failed with status:', response.status);
      throw new Error(data.message || 'Failed to save');
    }
    
    console.log('✓ Menu item saved successfully!');
    console.log('========== FORM SUBMISSION COMPLETED ==========');
    closeModal();
    fetchMenu();
  } catch (err) {
    console.error('========== FORM SUBMISSION ERROR ==========');
    console.error('Error type:', err.constructor.name);
    console.error('Error message:', err.message);
    console.error('Error stack:', err.stack);
    console.error('==========================================');
    
    const errorMessage = err.message || 'Failed to save';
    const el = $('#formError');
    el.textContent = errorMessage;
    el.classList.remove('hidden');
  }
}

// Edit button click handler
document.addEventListener('click', (e) => {
  if (e.target.matches('.edit-btn')) {
    const id = e.target.dataset.id;
    const item = menuItems.find(i => i.id == id);
    if (item) {
      openModal('edit', item);
    }
  }
});

document.addEventListener('DOMContentLoaded', () => {
  fetchMenu();
  const openBtn = $('#openCreateModalBtn');
  const closeBtn = $('#closeModalBtn');
  const cancelBtn = $('#cancelModalBtn');
  const form = $('#menuForm');
  
  // Set form method to POST for both create and update
  if (form) {
    form.method = 'POST';
  }

  if (openBtn) openBtn.addEventListener('click', () => openModal('create'));
  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
  if (form) form.addEventListener('submit', handleSubmit);
});
