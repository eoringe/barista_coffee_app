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
