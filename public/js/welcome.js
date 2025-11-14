(function(){
  const $ = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));
  const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  const api = {
    async list(){ const res = await fetch('/menu-items',{ headers:{ 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' } }); return res.json(); },
    async create(fd){ const token=getCsrfToken(); const res=await fetch('/menu-items',{ method:'POST', headers:{ 'X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest','Accept':'application/json' }, body:fd }); return res.json(); },
    async update(id,fd){ const token=getCsrfToken(); const res=await fetch(`/menu-items/${id}`,{ method:'POST', headers:{ 'X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest','Accept':'application/json' }, body:fd }); return res.json(); },
    async remove(id){ const token=getCsrfToken(); const res=await fetch(`/menu-items/${id}`,{ method:'DELETE', headers:{ 'X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest','Accept':'application/json' } }); return res.json(); },
  };

  const catApi = {
    async list(){ const r=await fetch('/categories',{ headers:{ 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' } }); return r.json(); },
    async create(name){ const fd=new FormData(); fd.append('name',name); const token=getCsrfToken(); const r=await fetch('/categories',{ method:'POST', headers:{ 'X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest','Accept':'application/json' }, body:fd }); return r.json(); },
    async update(id,name){ const fd=new FormData(); fd.append('name',name); const token=getCsrfToken(); const r=await fetch(`/categories/${id}`,{ method:'POST', headers:{ 'X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest','Accept':'application/json' }, body:fd }); return r.json(); },
    async remove(id){ const token=getCsrfToken(); const r=await fetch(`/categories/${id}`,{ method:'DELETE', headers:{ 'X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest','Accept':'application/json' } }); return r.json(); },
  };

  function formatPrice(single,doubleP){ const s=Number(single), d=Number(doubleP); if(Number.isFinite(s)&&Number.isFinite(d)) return `${s} KES / ${d} KES`; return `${single} KES / ${doubleP} KES`; }
  function setStat(id,val){ const el=document.getElementById(id); if(el) el.textContent=val; }
  function showLoading(){ const skelHTML='<span class="skel-bar"></span>'; ['statTotal','statAvailable','statWithImage','statOut'].forEach(id=>{ const el=document.getElementById(id); if(el) el.innerHTML=skelHTML; }); const grid=$('#menuGrid'); const tpl=$('#skeletonCardTemplate'); if(grid&&tpl){ grid.innerHTML=''; for(let i=0;i<6;i++){ grid.appendChild(tpl.content.firstElementChild.cloneNode(true)); } } }
  function showLoadingIndicator(){ const indicator=document.getElementById('loadingIndicator'); if(indicator) indicator.style.display='flex'; }
  function hideLoadingIndicator(){ const indicator=document.getElementById('loadingIndicator'); if(indicator) indicator.style.display='none'; }

  async function loadMenu(){ const grid=$('#menuGrid'); const tpl=$('#menuCardTemplate'); if(!grid||!tpl) return; showLoading(); try{ const data=await api.list(); const items=Array.isArray(data?.data)?data.data:[]; const total=items.length; const available=items.filter(i=>i.available).length; const withImage=items.filter(i=>!!i.image_url).length; const out=items.filter(i=>!i.available||Number(i.portion_available)===0).length; setStat('statTotal',total); setStat('statAvailable',available); setStat('statWithImage',withImage); setStat('statOut',out); grid.innerHTML=''; items.forEach(item=>{ const node=tpl.content.firstElementChild.cloneNode(true); const img=node.querySelector('[data-img]'); const fallbackPath=item.image_path?`/storage/${String(item.image_path).replace(/^\\+|^\/+/, '')}`:null; const imgUrlRaw=item.image_url||fallbackPath; let imgUrl=imgUrlRaw; try{ if(imgUrlRaw){ const u=new URL(imgUrlRaw, window.location.origin); if(u.hostname==='localhost'||u.hostname==='127.0.0.1'){ u.protocol='http:'; u.host='127.0.0.1:8000'; imgUrl=u.toString(); } } }catch(e){} if(imgUrl){ img.addEventListener('load',()=>{}); img.addEventListener('error',()=>{ img.style.display='none'; }); img.src=imgUrl; img.style.display='block'; } else { img.style.display='none'; }
      node.querySelector('[data-title]').textContent=item.coffee_title; node.querySelector('[data-category]').textContent=item.category?.name||'Uncategorized'; node.querySelector('[data-price]').textContent=formatPrice(item.single_price,item.double_price); const avail=node.querySelector('[data-availability]'); const isAvail=item.available&&Number(item.portion_available)>0; avail.textContent=isAvail?'Available':'Out of stock'; avail.setAttribute('data-availability', isAvail?'Available':'Out of stock'); node.querySelector('[data-edit]').addEventListener('click',()=>openModal('edit',item)); node.querySelector('[data-delete]').addEventListener('click', async()=>{ if(!confirm('Delete this item?')) return; await api.remove(item.id); loadMenu(); }); grid.appendChild(node); }); } catch(e){ grid.innerHTML='<p style="color:#e73f3f">Failed to load menu.</p>'; } }

  function openModal(mode,item){ const wrap=$('#menuModal'); const title=$('#modalTitle'); const form=$('#menuForm'); const err=$('#formError'); if(!wrap||!form) return; err.style.display='none'; form.reset(); $('#menuId').value=''; if(mode==='edit'&&item){ title.textContent='Edit Item'; $('#menuId').value=item.id; $('#coffee_title').value=item.coffee_title||''; $('#single_price').value=item.single_price??''; $('#double_price').value=item.double_price??''; $('#portion_available').value=item.portion_available??''; $('#available').checked=!!item.available; $('#special').checked=!!item.special; } else { title.textContent='Add Item'; }
    wrap.classList.add('show'); loadCategoryDropdown().then(()=>{ if(mode==='edit'&&item){ $('#category').value=item.category?.name||''; } }); }
  function closeModal(){ const wrap=$('#menuModal'); if(wrap) wrap.classList.remove('show'); }

  async function loadCategoryDropdown(){ const select=$('#category'); if(!select) return; try{ const res=await catApi.list(); const cats=Array.isArray(res?.data)?res.data:[]; select.innerHTML='<option value="">Select a category...</option>'; cats.forEach(cat=>{ const option=document.createElement('option'); option.value=cat.name; option.textContent=cat.name; select.appendChild(option); }); } catch(e){ console.error('Failed to load categories:', e); } }

  function toFormData(payload){ const fd=new FormData(); Object.entries(payload).forEach(([k,v])=>{ if(v!==undefined&&v!==null) fd.append(k,v); }); return fd; }

  async function submitForm(e){ e.preventDefault(); const id=$('#menuId').value; const saveBtn=e.submitter||document.querySelector('#menuForm button[type="submit"]'); const original=saveBtn?saveBtn.textContent:''; if(saveBtn){ saveBtn.disabled=true; saveBtn.textContent='Saving...'; } showLoadingIndicator(); const payload={ coffee_title:$('#coffee_title').value, category:$('#category').value, single_price:$('#single_price').value, double_price:$('#double_price').value, available:$('#available').checked?'1':'0', special:$('#special').checked?'1':'0', portion_available:$('#portion_available').value, }; const file=$('#image').files&&$('#image').files[0]; if(file){ payload.image=file; } const fd=toFormData(payload); const err=$('#formError'); try{ const result=id?await api.update(id,fd):await api.create(fd); if(!result?.success) throw new Error(result?.message||'Failed'); closeModal(); loadMenu(); } catch(ex){ err.textContent=ex?.message||'Failed to save'; err.style.display='block'; } finally { hideLoadingIndicator(); if(saveBtn){ saveBtn.disabled=false; saveBtn.textContent=original||'Save'; } } }

  async function loadCategories(){ const list=document.getElementById('categoriesList'); const tpl=document.getElementById('categoryItemTemplate'); if(!list||!tpl) return; list.innerHTML=''; for(let i=0;i<4;i++) list.insertAdjacentHTML('beforeend','<div class="card p-4 skel" style="height:56px"></div>'); try{ const res=await catApi.list(); const cats=Array.isArray(res?.data)?res.data:[]; list.innerHTML=''; cats.forEach(cat=>{ const node=tpl.content.firstElementChild.cloneNode(true); const nameInput=node.querySelector('[data-edit-name]'); const saveBtn=node.querySelector('[data-save]'); const deleteBtn=node.querySelector('[data-delete]'); nameInput.value=cat.name; saveBtn.addEventListener('click', async()=>{ saveBtn.disabled=true; const text=saveBtn.textContent; saveBtn.textContent='Saving...'; const r=await catApi.update(cat.id, nameInput.value); saveBtn.disabled=false; saveBtn.textContent=text; if(!r?.success) alert(r?.message||'Failed to update'); }); deleteBtn.addEventListener('click', async()=>{ if(!confirm('Delete this category?')) return; const r=await catApi.remove(cat.id); if(!r?.success){ alert(r?.message||'Failed to delete'); return;} loadCategories(); }); list.appendChild(node); }); } catch(e){ list.innerHTML='<p style="color:#e73f3f">Failed to load categories.</p>'; } }

  async function loadSpecials(){ const grid=$('#specialsGrid'); const tpl=$('#menuCardTemplate'); if(!grid||!tpl) return; showLoading(); showLoadingIndicator(); try{ const response=await fetch('/menu-items?special=1',{ headers:{ 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' } }); const data=await response.json(); if(!data.success) throw new Error('Failed to load specials'); grid.innerHTML=''; const items=data.data; if(items.length===0){ grid.innerHTML='<p class="text-sm text-gray-500">No specials available.</p>'; return; } items.forEach(item=>{ const node=tpl.content.firstElementChild.cloneNode(true); const img=node.querySelector('[data-img]'); const fallbackPath=item.image_path?`/storage/${String(item.image_path).replace(/^\\+|^\/+/, '')}`:null; const imgUrlRaw=item.image_url||fallbackPath; let imgUrl=imgUrlRaw; try{ if(imgUrlRaw){ const u=new URL(imgUrlRaw, window.location.origin); if(u.hostname==='localhost'||u.hostname==='127.0.0.1'){ u.protocol='http:'; u.host='127.0.0.1:8000'; imgUrl=u.toString(); } } }catch(e){} if(imgUrl){ img.src=imgUrl; img.classList.remove('hidden'); img.onerror=()=>{ img.classList.add('hidden'); }; } node.querySelector('[data-title]').textContent=item.coffee_title; node.querySelector('[data-category]').textContent=item.category?.name||'Uncategorized'; node.querySelector('[data-price]').textContent=formatPrice(item.single_price,item.double_price); node.querySelector('[data-availability]').textContent=`${item.available ? 'Available' : 'Unavailable'} • ${item.portion_available} portions`; node.querySelector('[data-edit]').addEventListener('click',()=>openModal('edit',item)); node.querySelector('[data-delete]').addEventListener('click', async()=>{ if(!confirm('Delete this item?')) return; await fetch(`/menu-items/${item.id}`,{ method:'DELETE', headers:{ 'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json' } }); loadMenu(); loadSpecials(); }); grid.appendChild(node); }); } catch(e){ console.error('Error loading specials:', e); grid.innerHTML='<p class="text-sm text-red-500">Failed to load specials.</p>'; } finally { hideLoadingIndicator(); } }

  // Static Orders demo
  const demoOrders = [
    { id: 1, item: 'Caramel Latte', size: 'Double', qty: 2, price: '600 KES', receipt: 'RCPT-10293' },
    { id: 2, item: 'Espresso', size: 'Single', qty: 1, price: '200 KES', receipt: 'RCPT-10294' },
    { id: 3, item: 'Iced Mocha', size: 'Double', qty: 1, price: '450 KES', receipt: 'RCPT-10295' },
  ];

  function renderOrders(){
    const list = document.getElementById('ordersList');
    const tpl = document.getElementById('orderItemTemplate');
    if(!list || !tpl) return;
    list.innerHTML = '';
    demoOrders.forEach(o => {
      const node = tpl.content.firstElementChild.cloneNode(true);
      node.querySelector('[data-item]').textContent = o.item;
      node.querySelector('[data-price]').textContent = o.price;
      node.querySelector('[data-size]').textContent = o.size;
      node.querySelector('[data-qty]').textContent = `Qty: ${o.qty}`;
      node.querySelector('[data-receipt]').textContent = `Receipt: ${o.receipt}`;
      node.querySelector('[data-ready]').addEventListener('click', ()=>{
        alert(`Order ${o.receipt} marked as ready!`);
      });
      list.appendChild(node);
    });
  }

  document.addEventListener('DOMContentLoaded',()=>{
    loadMenu();
    const openBtn=document.getElementById('openCreateModalBtn');
    const closeBtn=document.getElementById('closeModalBtn');
    const cancelBtn=document.getElementById('cancelModalBtn');
    const form=document.getElementById('menuForm');
    if(openBtn) openBtn.addEventListener('click',()=>openModal('create'));
    if(closeBtn) closeBtn.addEventListener('click', closeModal);
    if(cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if(form) form.addEventListener('submit', submitForm);

    const tabMenu=document.getElementById('tabMenu');
    const tab=document.getElementById('tabCategories');
    const specialsTab=document.getElementById('tabSpecials');
    const ordersTab=document.getElementById('tabOrders');
    const menuHeader=document.getElementById('menuHeader');
    const statsSection=document.getElementById('statsSection');
    const menuSection=document.getElementById('menuSection');
    const categoriesSection=document.getElementById('categoriesSection');
    const specialsSection=document.getElementById('specialsSection');
    const ordersSection=document.getElementById('ordersSection');
    const addCatBtn=document.getElementById('addCategoryBtn');
    const newCatInput=document.getElementById('newCategoryName');
    const sidebarHandle=document.getElementById('sidebarHandle');
    const viewGridBtn=document.getElementById('viewGridBtn');
    const viewListBtn=document.getElementById('viewListBtn');
    const menuGrid=document.getElementById('menuGrid');

    function setSidebar(collapsed){
      document.body.classList.toggle('sidebar-collapsed', !!collapsed);
      if(sidebarHandle){
        sidebarHandle.setAttribute('aria-expanded', (!collapsed).toString());
        sidebarHandle.textContent = collapsed ? '▶' : '◀';
        sidebarHandle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
      }
      try{ localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0'); }catch(e){}
    }
    // init from storage
    try{ setSidebar(localStorage.getItem('sidebarCollapsed') === '1'); }catch(e){ setSidebar(false); }
    if(sidebarHandle){
      sidebarHandle.addEventListener('click', ()=>{
        const collapsed = !document.body.classList.contains('sidebar-collapsed');
        setSidebar(collapsed);
      });
      sidebarHandle.addEventListener('keydown', (e)=>{
        if(e.key === 'Enter' || e.key === ' '){
          e.preventDefault();
          const collapsed = !document.body.classList.contains('sidebar-collapsed');
          setSidebar(collapsed);
        }
      });
    }

    function setView(view){
      if(!menuGrid) return;
      // add transient animating class for subtle transition
      menuGrid.classList.add('animating');
      if(view==='list'){
        menuGrid.classList.add('list');
        viewListBtn?.classList.add('active');
        viewGridBtn?.classList.remove('active');
      } else {
        menuGrid.classList.remove('list');
        viewGridBtn?.classList.add('active');
        viewListBtn?.classList.remove('active');
      }
      try{ localStorage.setItem('menuView', view); }catch(e){}
      setTimeout(()=> menuGrid.classList.remove('animating'), 220);
    }
    // Initialize view from storage
    try{ setView(localStorage.getItem('menuView') || 'grid'); }catch(e){ setView('grid'); }
    // Toggle handlers
    viewGridBtn?.addEventListener('click', ()=> setView('grid'));
    viewListBtn?.addEventListener('click', ()=> setView('list'));

    function setActive(tabEl){ [tabMenu,tab,specialsTab].forEach(el=>{ if(!el) return; el.classList.remove('active'); }); if(tabEl) tabEl.classList.add('active'); }

    if(tabMenu) tabMenu.addEventListener('click',()=>{ setActive(tabMenu); menuHeader.style.display=''; statsSection.style.display=''; menuSection.style.display=''; categoriesSection.style.display='none'; specialsSection.style.display='none'; if(ordersSection) ordersSection.style.display='none'; loadMenu(); });
    if(specialsTab) specialsTab.addEventListener('click',()=>{ setActive(specialsTab); menuHeader.style.display='none'; statsSection.style.display='none'; menuSection.style.display='none'; categoriesSection.style.display='none'; if(ordersSection) ordersSection.style.display='none'; specialsSection.style.display=''; showLoadingIndicator(); loadSpecials(); });
    if(ordersTab) ordersTab.addEventListener('click',()=>{ setActive(ordersTab); menuHeader.style.display='none'; statsSection.style.display='none'; menuSection.style.display='none'; categoriesSection.style.display='none'; specialsSection.style.display='none'; if(ordersSection) { ordersSection.style.display=''; showLoadingIndicator(); setTimeout(()=>{ renderOrders(); hideLoadingIndicator(); }, 300); } });
    if(tab) tab.addEventListener('click',()=>{ setActive(tab); menuHeader.style.display='none'; statsSection.style.display='none'; menuSection.style.display='none'; categoriesSection.style.display=''; specialsSection.style.display='none'; loadCategories(); });
    if(addCatBtn) addCatBtn.addEventListener('click', async()=>{ const name=(newCatInput?.value||'').trim(); if(!name) return; addCatBtn.disabled=true; const text=addCatBtn.textContent; addCatBtn.textContent='Adding...'; const r=await catApi.create(name); addCatBtn.disabled=false; addCatBtn.textContent=text; if(!r?.success){ alert(r?.message||'Failed to add'); return; } newCatInput.value=''; loadCategories(); });
  });
})();
