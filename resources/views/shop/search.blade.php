@extends('layouts.app')
@section('title', 'Hledání: '.$term)
@section('content')
<div class="container py-4">
  <h1>Hledání</h1>
  <form class="d-flex gap-2 mb-4" style="max-width:520px"><input class="form-control" name="q" value="{{ $term }}" placeholder="Např. USB-C, Bluetooth, E46…" autofocus><button class="btn btn-dark">Hledat</button></form>
  @if (mb_strlen($term) < 2)
    <p class="text-secondary">Zadej aspoň dva znaky.</p>
  @else
    <h2 class="h5">Produkty <span class="text-secondary">({{ $products->count() }})</span></h2>
    <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
      @forelse ($products as $p)<div class="col">@include('partials.product-card', ['p' => $p])</div>
      @empty<div class="col-12 text-secondary">Žádný produkt jsme nenašli.</div>@endforelse
    </div>
    <h2 class="h5">Návody <span class="text-secondary">({{ $guides->count() }})</span></h2>
    <div class="vstack gap-2">
      @forelse ($guides as $g)<a href="{{ route('guides.show', $g->slug) }}" class="pp-box p-3 text-reset text-decoration-none"><i class="bi bi-book"></i> <b>{{ $g->title }}</b> <span class="small text-secondary">· {{ $g->step_count }} kroků</span></a>
      @empty<div class="text-secondary">Žádný návod jsme nenašli.</div>@endforelse
    </div>
  @endif
</div>
@endsection
