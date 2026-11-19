@if (session('ok') || $errors->any())
  <div class="container mt-3">
    @if (session('ok'))
      <div class="alert alert-success border-2 border-success alert-dismissible fade show mb-0" role="status"><i class="bi bi-check-circle"></i> {{ session('ok') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zavřít"></button></div>
    @endif
    @if ($errors->any())
      <div class="alert alert-danger border-2 border-danger mb-0 mt-2" role="alert">
        <b><i class="bi bi-exclamation-triangle"></i> Něco nesedí:</b>
        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif
  </div>
@endif
