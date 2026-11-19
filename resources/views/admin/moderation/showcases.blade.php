@extends('admin.layout')
@section('title', 'Před a po')
@section('content')
<h1 class="h3 mb-3">Před a po montáži</h1>
<div class="row g-3">
  @forelse ($showcases as $s)
    <div class="col-md-6 col-xl-4"><div class="pp-card p-2 h-100">
      <div class="d-flex gap-1 mb-2"><img src="{{ $s->url($s->before_image) }}" class="w-50 rounded" style="aspect-ratio:1;object-fit:cover" alt="Před"><img src="{{ $s->url($s->after_image) }}" class="w-50 rounded" style="aspect-ratio:1;object-fit:cover" alt="Po"></div>
      <b>{{ $s->title }}</b> @unless ($s->is_approved)<span class="badge text-bg-warning">čeká</span>@endunless<div class="small text-secondary">{{ $s->author_name }} · {{ $s->product?->name }}</div>
      <div class="d-flex gap-2 mt-2">
        <form method="post" action="{{ route('admin.showcases.toggle', $s) }}">@csrf<button class="btn btn-sm {{ $s->is_approved ? 'btn-outline-dark' : 'btn-dark' }}">{{ $s->is_approved ? 'Skrýt' : 'Zveřejnit' }}</button></form>
        <form method="post" action="{{ route('admin.showcases.destroy', $s) }}" data-confirm="Smazat?">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger" aria-label="Smazat"><i class="bi bi-trash"></i></button></form>
      </div>
    </div></div>
  @empty <p class="text-secondary">Zákazníci zatím nic nenahráli. Formulář je na stránce <a href="{{ route('showcases') }}" target="_blank">Před a po montáži</a>.</p> @endforelse
</div>
<div class="mt-3">{{ $showcases->links() }}</div>
@endsection
