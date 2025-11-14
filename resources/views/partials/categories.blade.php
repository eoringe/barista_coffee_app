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
