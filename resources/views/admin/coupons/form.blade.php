<div class="card card-soft"><div class="card-body"><form method="POST" action="{{ $action }}">@csrf @if(!empty($method))@method($method)@endif
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code',$coupon->code ?? '') }}" required></div>
<div class="col-md-8"><label class="form-label">Title</label><input name="title" class="form-control" value="{{ old('title',$coupon->title ?? '') }}" required></div>
<div class="col-md-4"><label class="form-label">Type</label><select name="discount_type" class="form-select"><option value="fixed">Fixed</option><option value="percentage">Percentage</option></select></div>
<div class="col-md-4"><label class="form-label">Value</label><input type="number" step="0.01" name="discount_value" class="form-control" value="{{ old('discount_value',$coupon->discount_value ?? '') }}" required></div>
<div class="col-md-4"><label class="form-label">Min Order</label><input type="number" step="0.01" name="min_order_amount" class="form-control" value="{{ old('min_order_amount',$coupon->min_order_amount ?? '') }}"></div>
</div><button class="btn btn-success mt-3">Save</button></form></div></div>
