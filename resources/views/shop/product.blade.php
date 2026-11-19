@extends('layouts.app')
@section('step', $tour ? '3' : '0')
@section('title', $product->name)
@section('description', $product->short_description)
@push('head')
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'Product', 'name' => $product->name, 'sku' => $product->sku, 'description' => $product->short_description,
  'brand' => ['@type' => 'Brand', 'name' => $product->manufacturer], 'offers' => ['@type' => 'Offer', 'priceCurrency' => 'CZK', 'price' => $product->price, 'availability' => $product->in_stock ? 'https://schema.org/InStock' : 'https://schema.org/BackOrder']]
  + ($product->rating ? ['aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => $product->rating, 'reviewCount' => $reviews->count()]] : []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush
@section('content')
<div class="container py-4">
  @if ($tour)
    <nav class="small mb-3"><a href="{{ route('car.show', [$car->slug, 'zona' => $product->zone?->slug]) }}" class="link-secondary"><i class="bi bi-arrow-left"></i> Zpět na {{ $car->short_name }}{{ $product->zone ? ' · '.$product->zone->name : '' }}</a></nav>
  @else
    <nav class="small mb-3" aria-label="Drobečková navigace"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="{{ route('home') }}" class="link-secondary">Úvod</a></li>
      @if ($product->category?->parent)<li class="breadcrumb-item"><a href="{{ route('catalog', $product->category->parent->slug) }}" class="link-secondary">{{ $product->category->parent->name }}</a></li>@endif
      @if ($product->category)<li class="breadcrumb-item"><a href="{{ route('catalog', $product->category->slug) }}" class="link-secondary">{{ $product->category->name }}</a></li>@endif
      <li class="breadcrumb-item active">{{ $product->name }}</li>
    </ol></nav>
  @endif

  <div class="row g-4">
    <div class="col-lg-7">
      @include('partials.img', ['p' => $product, 'class' => 'mb-2 border border-2 border-dark', 'style' => 'aspect-ratio:4/3'])
      <div class="row g-2">
        <div class="col-3"><div class="pp-img sm" style="aspect-ratio:1"><i class="bi bi-zoom-in"></i></div></div>
        <div class="col-3"><div class="pp-img sm" style="aspect-ratio:1"><i class="bi bi-box-seam"></i></div></div>
        <div class="col-3"><div class="pp-img sm" style="aspect-ratio:1"><i class="bi bi-plug"></i></div></div>
        <div class="col-3">@if ($product->video_url)<a href="{{ $product->video_url }}" target="_blank" rel="noopener" class="pp-img sm text-decoration-none" style="aspect-ratio:1"><i class="bi bi-play-circle"></i></a>@else<div class="pp-img sm" style="aspect-ratio:1"><i class="bi bi-camera-video-off"></i></div>@endif</div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="position-sticky" style="top:1rem">
        <h1 class="h2">{{ $product->name }}</h1>
        <div class="small text-secondary mb-3">
          {!! collect([
            $product->own_production ? '<i class="bi bi-cpu"></i> Vlastní výroba PlugPlay' : e($product->manufacturer),
            $product->rating ? '<i class="bi bi-star-fill text-warning"></i> '.str_replace('.', ',', $product->rating).' ('.$reviews->count().' hodnocení)' : null,
            'Kód: '.e($product->sku),
          ])->filter()->implode(' · ') !!}
        </div>

        @if ($tour)
          <div class="alert alert-primary border-2 d-flex gap-3 align-items-start mb-3">
            <i class="bi bi-patch-check-fill fs-3"></i>
            <div><b>Pasuje do {{ $car->full_name }}{{ $car->years ? ', '.$car->years : '' }}</b><br><span class="small">{{ $product->is_universal ? 'Univerzální produkt – funguje v jakémkoli autě s palubní sítí 12 V.' : 'Vyzkoušeli jsme ho v tomto modelu. Kabeláž s konektorem je součástí balení.' }}</span></div>
          </div>
        @else
          <form class="pp-box p-3 mb-3" method="get">
            <b class="small"><i class="bi bi-car-front"></i> Pasuje do tvého auta?</b>
            <div class="d-flex gap-2 mt-2">
              <select name="over" class="form-select form-select-sm" aria-label="Model auta"><option value="">Vyber model…</option>
                @foreach ($carModels as $m)<option value="{{ $m->id }}" @selected($checked?->id === $m->id)>{{ $m->full_name }}</option>@endforeach</select>
              <button class="btn btn-sm btn-outline-dark text-nowrap">Ověřit</button>
            </div>
            @if ($checked)
              @if ($product->fits($checked))
                <div class="small text-success fw-bold mt-2"><i class="bi bi-check-circle-fill"></i> Ano, do {{ $checked->full_name }} pasuje.</div>
              @else
                <div class="small text-danger fw-bold mt-2"><i class="bi bi-x-circle-fill"></i> Do {{ $checked->full_name }} jsme ho neověřili.</div>
              @endif
            @elseif ($car && ! $fits)
              <div class="small text-danger mt-2"><i class="bi bi-exclamation-triangle"></i> Do tvého {{ $car->short_name }} jsme ho zatím neověřili.</div>
            @endif
            <div class="small text-secondary mt-2">
              @if ($product->is_universal) Univerzální produkt – pasuje do všech aut.
              @else Ověřeno v {{ $product->carModels->count() }} {{ plural($product->carModels->count(), 'modelu', 'modelech', 'modelech') }}:
                {{ $product->carModels->pluck('short_name')->implode(', ') }} @endif
            </div>
          </form>
        @endif

        <div class="row g-2 text-center mb-3">
          <div class="col-4"><div class="pp-box p-2 h-100"><div class="small text-secondary">Náročnost</div><span class="fs-5">@include('partials.diff', ['d' => $product->difficulty])</span><div class="small">{{ \App\Models\Product::DIFFICULTY[$product->difficulty] }}</div></div></div>
          <div class="col-4"><div class="pp-box p-2 h-100"><div class="small text-secondary">Montáž</div><div class="fs-5 fw-bold">{{ $product->install_minutes ? $product->install_time : '–' }}</div></div></div>
          <div class="col-4"><div class="pp-box p-2 h-100"><div class="small text-secondary">Servis</div><div class="fs-6 fw-bold">{{ $product->needs_service ? 'doporučený' : 'není potřeba' }}</div></div></div>
        </div>

        <form method="post" action="{{ route('cart.add') }}">@csrf
          <input type="hidden" name="product_id" value="{{ $product->id }}">
          @if ($product->tools->isNotEmpty())
            <div class="mb-3">
              <b class="small">Co budeš potřebovat</b>
              <div class="d-flex flex-wrap gap-2 mt-1 small">
                @foreach ($product->tools as $t)
                  <span class="pp-chip"><i class="bi bi-check-lg"></i> {{ $t->name }}</span>
                @endforeach
                @foreach ($product->tools->whereNotNull('product_id') as $t)
                  <label class="pp-chip"><input type="checkbox" class="form-check-input m-0" name="with_tools[]" value="{{ $t->product_id }}"> Nemáš {{ mb_strtolower($t->name) }}? Přidej (+{{ czk($t->product->price) }})</label>
                @endforeach
              </div>
            </div>
          @endif

          @if ($product->variantList())
            <div class="mb-3">
              <span class="form-label small fw-bold mb-1 d-block">{{ $product->variant_label ?: 'Varianta' }}</span>
              <div class="btn-group w-100" role="group">
                @foreach ($product->variantList() as $i => $v)
                  <input type="radio" class="btn-check" name="variant" id="v{{ $i }}" value="{{ $v }}" @checked($i === 0)><label class="btn btn-outline-dark" for="v{{ $i }}">{{ $v }}</label>
                @endforeach
              </div>
            </div>
          @endif

          <div class="d-flex align-items-end justify-content-between mb-2">
            <div>
              @if ($product->old_price)<s class="text-secondary">{{ czk($product->old_price) }}</s>@endif
              <div class="fs-2 fw-bold lh-1">{{ czk($product->price) }}</div>
              <div class="small {{ $product->in_stock ? 'text-success' : 'text-warning-emphasis' }}"><i class="bi bi-circle-fill"></i> {{ $product->availability }}</div>
            </div>
            <div class="small text-secondary text-end">včetně DPH<br>doprava od 69 Kč</div>
          </div>
          @if ($tour)
            <input type="hidden" name="qty" value="1">
            <button class="btn btn-dark btn-lg w-100"><i class="bi bi-plus-lg"></i> Přidat do plánu</button>
          @else
            <div class="d-flex gap-2">
              <input type="number" name="qty" min="1" max="99" class="form-control form-control-lg" style="width:90px" value="1" aria-label="Počet kusů">
              <button class="btn btn-dark btn-lg flex-fill"><i class="bi bi-cart-plus"></i> Do košíku</button>
            </div>
          @endif
        </form>
        <div class="small text-secondary text-center mt-2"><i class="bi bi-arrow-repeat"></i> Nepasuje? Do 30 dnů ji vrátíš zdarma.</div>
      </div>
    </div>
  </div>

  <ul class="nav nav-tabs mt-5" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#t-test">Náš test</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-guide">Návod{{ $guide?->carModel ? ' pro '.$guide->carModel->short_name : '' }}</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-spec">Parametry a popis</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-rev" id="tab-rev">Recenze ({{ $reviews->count() }})</button></li>
  </ul>
  <div class="tab-content bg-white border border-top-0 p-4 rounded-bottom">
    <div class="tab-pane fade show active" id="t-test">
      @if ($product->test_score || $product->pros || $product->cons)
        <div class="row g-4">
          <div class="col-md-4 text-center"><div class="pp-box p-4">
            @if ($product->test_score)<div class="display-5">{{ $product->test_score }}/10</div>@endif
            @if ($product->test_quote)<div class="pp-hand fs-5">„{{ $product->test_quote }}“</div>@endif
          </div></div>
          <div class="col-md-4"><h2 class="h5"><i class="bi bi-hand-thumbs-up"></i> Co se nám líbí</h2><ul class="small">@foreach ($product->lines('pros') as $l)<li>{{ $l }}</li>@endforeach</ul></div>
          <div class="col-md-4"><h2 class="h5"><i class="bi bi-hand-thumbs-down"></i> Na co si dát pozor</h2><ul class="small">@foreach ($product->lines('cons') as $l)<li>{{ $l }}</li>@endforeach</ul></div>
        </div>
      @else
        <p class="text-secondary mb-0">Test tohoto produktu připravujeme.</p>
      @endif
    </div>
    <div class="tab-pane fade" id="t-guide">
      @if ($guide)
        <div class="row g-4">
          @foreach (array_slice($guide->steps, 0, 3) as $i => $s)
            <div class="col-md-4"><div class="pp-img mb-2 sm" style="aspect-ratio:16/10"><span class="fs-2 fw-bold text-primary">{{ $i + 1 }}</span></div><b>{{ $i + 1 }}. {{ $s['title'] }}</b><p class="small text-secondary mt-1 mb-0">{{ $s['body'] }}</p></div>
          @endforeach
        </div>
        <a href="{{ route('guides.show', $guide->slug) }}" class="btn btn-outline-dark mt-3"><i class="bi bi-book"></i> Celý návod ({{ $guide->step_count }} kroků{{ $guide->video_minutes ? ' a video' : '' }})</a>
      @else
        <p class="text-secondary mb-0">Návod k tomuto produktu je v balení. Online verzi připravujeme.</p>
      @endif
    </div>
    <div class="tab-pane fade" id="t-spec">
      @if ($product->description)<p>{{ $product->description }}</p>@endif
      <dl class="row small mb-0" style="max-width:560px">
        @foreach ($product->specList() as $k => $v)<dt class="col-5">{{ $k }}</dt><dd class="col-7">{{ $v }}</dd>@endforeach
        @if ($product->location_in_car)<dt class="col-5">Umístění v autě</dt><dd class="col-7">{{ $product->location_in_car }}</dd>@endif
        <dt class="col-5">Výrobce</dt><dd class="col-7">{{ $product->manufacturer ?: '–' }}</dd>
      </dl>
    </div>
    <div class="tab-pane fade" id="t-rev">
      @if ($car && $myReviews->isNotEmpty())
        <div class="d-flex gap-2 mb-3 small">
          <a href="?recenze=moje" class="pp-chip {{ $onlyMine ? 'on' : '' }}">Jen {{ $car->short_name }} ({{ $myReviews->count() }})</a>
          <a href="?recenze=vse" class="pp-chip {{ ! $onlyMine ? 'on' : '' }}">Všechna auta ({{ $reviews->count() }})</a>
        </div>
      @endif
      <div class="vstack gap-2 mb-4">
        @forelse ($shownReviews as $r)
          <div class="pp-box p-3"><b>{{ $r->author_name }}{{ $r->car_label ? ', '.$r->car_label : '' }}</b>
            <span class="text-warning">@for ($i = 1; $i <= 5; $i++)<i class="bi {{ $i <= $r->rating ? 'bi-star-fill' : 'bi-star' }}"></i>@endfor</span>
            @if ($r->install_minutes)<span class="text-secondary small">· montáž za {{ $r->install_minutes }} minut</span>@endif
            <p class="small mb-0 mt-1">„{{ $r->body }}“</p></div>
        @empty
          <p class="text-secondary">Zatím tu není žádná recenze. Buď první!</p>
        @endforelse
      </div>
      <form method="post" action="{{ route('product.review', $product) }}" class="pp-card p-3" style="max-width:640px">@csrf
        <h3 class="h5">Napiš recenzi</h3>
        <div class="row g-2">
          <div class="col-sm-5"><label class="form-label small" for="rn">Jméno</label><input class="form-control form-control-sm" id="rn" name="author_name" value="{{ old('author_name', auth()->user()?->first_name) }}" required></div>
          <div class="col-sm-4"><label class="form-label small" for="rr">Hodnocení</label><select id="rr" name="rating" class="form-select form-select-sm">@for ($i = 5; $i >= 1; $i--)<option value="{{ $i }}">{{ str_repeat('★', $i) }}</option>@endfor</select></div>
          <div class="col-sm-3"><label class="form-label small" for="rm">Montáž (min)</label><input type="number" min="0" class="form-control form-control-sm" id="rm" name="install_minutes"></div>
          <div class="col-12"><label class="form-label small" for="rb">Jak se ti montovalo a jak to funguje?</label><textarea class="form-control form-control-sm" id="rb" name="body" rows="3" required minlength="10">{{ old('body') }}</textarea></div>
        </div>
        <div class="small text-secondary mt-2">{{ $car ? 'Recenzi přiřadíme k autu '.$car->full_name.'.' : '' }} Recenze se zobrazí po schválení.</div>
        <button class="btn btn-dark btn-sm mt-2">Odeslat recenzi</button>
      </form>
    </div>
  </div>

  @if ($crossSell->isNotEmpty())
    <h2 class="h4 mt-5 mb-3">{{ $car ? 'Majitelé '.$car->short_name.' si k tomu často přidávají' : 'Zákazníci si k tomu často přidávají' }}</h2>
    <div class="row row-cols-1 row-cols-md-3 g-3">
      @foreach ($crossSell as $cs)
        <div class="col"><div class="pp-box p-3 d-flex gap-3 align-items-center h-100">
          <a href="{{ route('product.show', $cs->slug) }}">@include('partials.img', ['p' => $cs, 'class' => 'sm flex-shrink-0', 'style' => 'width:72px;height:72px;min-height:0'])</a>
          <div class="flex-fill"><a href="{{ route('product.show', $cs->slug) }}" class="small fw-bold text-reset">{{ $cs->name }}</a><div class="small text-secondary">{{ czk($cs->price) }}{{ $cs->install_minutes ? ' · '.$cs->install_time : '' }}</div></div>
          <form method="post" action="{{ route('cart.add') }}">@csrf <input type="hidden" name="product_id" value="{{ $cs->id }}"><input type="hidden" name="stay" value="1"><button class="btn btn-sm btn-outline-dark" aria-label="Přidat {{ $cs->name }}"><i class="bi bi-plus-lg"></i></button></form>
        </div></div>
      @endforeach
    </div>
  @endif
</div>
@endsection
@if (request()->has('recenze'))
@push('scripts')<script>bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-rev')).show();</script>@endpush
@endif
