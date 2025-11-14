<section class="card p-4" id="menuSection">
  <div class="flex items-center justify-between" style="margin-bottom:12px">
    <h2 style="margin:0;font-weight:600">Menu Items</h2>
    <div></div>
  </div>
  <div id="menuGrid" class="grid"></div>

  <template id="menuCardTemplate">
    <div class="card">
      <div class="thumb">
        <img data-img alt="Coffee image" />
      </div>
      <div class="p-4" style="font-size:14px">
        <div class="flex items-center justify-between" style="margin-bottom:8px">
          <div>
            <div style="font-weight:600" data-title></div>
            <div class="text-muted" data-category></div>
          </div>
          <div style="font-weight:600" data-price></div>
        </div>
        <div class="flex items-center justify-between">
          <span data-availability></span>
          <div class="flex gap-2">
            <button class="input" style="cursor:pointer" data-edit>Edit</button>
            <button style="background:#e73f3f;color:#fff;border:none;padding:8px 12px;border-radius:var(--radius);cursor:pointer" data-delete>Delete</button>
          </div>
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
