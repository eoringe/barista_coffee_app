import './bootstrap';
import axios from 'axios';

const $ = (sel) => document.querySelector(sel);
const $$ = (sel) => Array.from(document.querySelectorAll(sel));

const api = axios.create({
  baseURL: '/api',
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
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
    const { data } = await api.get('/menu-items');
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
        await api.delete(`/menu-items/${item.id}`);
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
  $('#formError').classList.add('hidden');
  form.reset();
  $('#menuId').value = '';
  if (mode === 'create') {
    title.textContent = 'Add Item';
  } else {
    title.textContent = 'Edit Item';
    $('#menuId').value = item.id;
    $('#coffee_title').value = item.coffee_title;
    $('#category').value = item.category?.name || '';
    $('#single_price').value = item.single_price;
    $('#double_price').value = item.double_price;
    $('#portion_available').value = item.portion_available;
    $('#available').checked = !!item.available;
  }
  modal.classList.remove('hidden');
  modal.classList.add('flex');
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
  const id = $('#menuId').value;
  const payload = {
    coffee_title: $('#coffee_title').value,
    category: $('#category').value,
    single_price: $('#single_price').value,
    double_price: $('#double_price').value,
    available: $('#available').checked,
    portion_available: $('#portion_available').value,
  };
  const file = $('#image').files[0];
  if (file) payload.image = file;
  try {
    if (id) {
      await api.patch(`/menu-items/${id}`, toFormData(payload));
    } else {
      await api.post('/menu-items', toFormData(payload));
    }
    closeModal();
    fetchMenu();
  } catch (err) {
    const msg = err?.response?.data?.message || 'Failed to save';
    const errors = err?.response?.data?.errors;
    const text = errors ? Object.values(errors).flat().join('\n') : msg;
    const el = $('#formError');
    el.textContent = text;
    el.classList.remove('hidden');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  fetchMenu();
  const openBtn = $('#openCreateModalBtn');
  const closeBtn = $('#closeModalBtn');
  const cancelBtn = $('#cancelModalBtn');
  const form = $('#menuForm');

  if (openBtn) openBtn.addEventListener('click', () => openModal('create'));
  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
  if (form) form.addEventListener('submit', handleSubmit);
});
