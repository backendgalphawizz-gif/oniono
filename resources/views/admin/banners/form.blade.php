<div class="card card-soft"><div class="card-body"><form method="POST" action="{{ $action }}" enctype="multipart/form-data">@csrf @if(!empty($method))@method($method)@endif
<div class="mb-3"><label class="form-label">Title</label><input name="title" class="form-control" value="{{ old('title',$banner->title ?? '') }}" required></div>
<div class="mb-3"><label class="form-label">Image</label><input type="file" name="image" class="form-control" @empty($banner) required @endempty></div>
<div class="mb-3"><label class="form-label">Link</label><input name="link" class="form-control" value="{{ old('link',$banner->link ?? '') }}"></div>
<button class="btn btn-success">Save</button></form></div></div>
