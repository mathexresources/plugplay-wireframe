@extends('layouts.app')
@section('title', 'Před a po montáži')
@section('content')
<div class="container py-4">
  <h1>Před a po montáži</h1>
  <div class="row g-4 mb-5">
    @forelse ($showcases as $s)
      <div class="col-md-4"><div class="pp-product p-0">
        <div class="d-flex"><div class="pp-img flex-fill rounded-0" style="aspect-ratio:1"><img src="{{ $s->url($s->before_image) }}" alt="Před"><span class="badge text-bg-dark position-absolute top-0 start-0 m-2">PŘED</span></div>
        <div class="pp-img flex-fill rounded-0" style="aspect-ratio:1"><img src="{{ $s->url($s->after_image) }}" alt="Po"><span class="badge text-bg-primary position-absolute top-0 start-0 m-2">PO</span></div></div>
        <div class="p-3"><b>{{ $s->title }}</b><div class="small text-secondary">{{ $s->author_name }}@if ($s->product) · <a href="{{ route('product.show', $s->product->slug) }}">{{ $s->product->name }}</a>@endif</div></div>
      </div></div>
    @empty
      <div class="col-12 text-secondary">Zatím tu nejsou žádné fotky. Buď první!</div>
    @endforelse
  </div>
  {{ $showcases->links() }}
  <form id="nahrat" class="pp-card p-4" style="max-width:720px" method="post" action="{{ route('showcases.submit') }}" enctype="multipart/form-data">@csrf
    <h2 class="h4">Pochlub se výsledkem</h2>
    <p class="small text-secondary">Za fotky před a po montáži dostaneš slevu 10 % na další nákup.</p>
    <div class="row g-3">
      <div class="col-md-7"><label class="form-label small" for="ti">Co jsi vylepšil</label><input class="form-control" id="ti" name="title" required placeholder="např. BMW E46: USB-C místo zapalovače" value="{{ old('title') }}"></div>
      <div class="col-md-5"><label class="form-label small" for="an">Jméno</label><input class="form-control" id="an" name="author_name" required value="{{ old('author_name', auth()->user()?->first_name) }}"></div>
      <div class="col-12"><label class="form-label small" for="pr">Produkt</label><select class="form-select" id="pr" name="product_id"><option value="">–</option>@foreach ($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
      <div class="col-md-6"><label class="form-label small" for="bi">Fotka PŘED</label><input class="form-control" id="bi" name="before_image" type="file" accept="image/*" required></div>
      <div class="col-md-6"><label class="form-label small" for="ai">Fotka PO</label><input class="form-control" id="ai" name="after_image" type="file" accept="image/*" required></div>
    </div>
    <button class="btn btn-dark mt-3">Odeslat ke schválení</button>
  </form>
</div>
@endsection
