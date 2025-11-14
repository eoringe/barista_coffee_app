<section class="card p-4" id="ordersSection" style="display:none">
  <div class="flex items-center justify-between" style="margin-bottom:16px">
    <h2 style="margin:0;font-weight:600">Orders</h2>
    <div class="text-muted">Live queue</div>
  </div>
  <div id="ordersList" class="orders-list"></div>

  <template id="orderItemTemplate">
    <div class="order-card">
      <div class="order-left">
        <div class="order-title">
          <span class="title" data-item></span>
          <span class="price" data-price></span>
        </div>
        <div class="order-meta">
          <span class="badge" data-size></span>
          <span class="badge" data-qty></span>
          <span class="receipt" data-receipt></span>
        </div>
      </div>
      <div class="order-actions">
        <button class="btn btn-ready" data-ready>Mark as Ready</button>
      </div>
    </div>
  </template>
</section>
