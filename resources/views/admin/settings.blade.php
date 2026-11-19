@extends('admin.layout')
@section('title', 'Nastavení obchodu')
@section('content')
<h1 class="h3 mb-3">Nastavení obchodu</h1>
<form method="post" action="{{ route('admin.settings.update') }}" class="pp-card p-4" style="max-width:820px">@csrf @method('put')
  <div class="row g-3">
    @foreach ($fields as $k => [$label])
      <div class="col-md-6"><label class="form-label small fw-bold" for="s-{{ $k }}">{{ $label }}</label><input class="form-control" id="s-{{ $k }}" name="{{ $k }}" value="{{ old($k, $values[$k]) }}"></div>
    @endforeach
  </div>
  <button class="btn btn-dark mt-4">Uložit</button>
</form>
<div class="pp-box p-3 mt-4" style="max-width:820px">
  <b><i class="bi bi-rss"></i> Napojení na srovnávače cen</b>
  <p class="small mb-1">XML feed produktů ve formátu Heureka.cz je na adrese:</p>
  <code>{{ route('feed.heureka') }}</code>
</div>
@endsection
