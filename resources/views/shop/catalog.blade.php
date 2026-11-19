@extends('layouts.app')
@section('title', $cat ? $cat->name.' do auta' : 'Katalog autoelektroniky')
@section('content')
<div class="container py-4">
  <nav class="small mb-2" aria-label="Drobečková navigace">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="{{ route('home') }}" class="link-secondary">Úvod</a></li>
      @if ($cat)
        <li class="breadcrumb-item"><a href="{{ route('catalog') }}" class="link-secondary">Katalog</a></li>
        @if ($cat->parent)<li class="breadcrumb-item"><a href="{{ route('catalog', $cat->parent->slug) }}" class="link-secondary">{{ $cat->parent->name }}</a></li>@endif
        <li class="breadcrumb-item active">{{ $cat->name }}</li>
      @else
        <li class="breadcrumb-item active">Katalog</li>
      @endif
    </ol>
  </nav>

  <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <h1 class="mb-0">{{ $cat ? $cat->name.' do auta' : 'Všechny produkty' }} <span class="text-secondary fs-5">{{ $products->total() }} {{ plural($products->total(), 'produkt', 'produkty', 'produktů') }}</span></h1>
    <a href="{{ route('home') }}#vyber-auta" class="small link-secondary"><i class="bi bi-signpost-split"></i> Nevíš, co vybrat? Zobraz produkty pro svoje auto →</a>
  </div>

  <div class="row g-4">
    <aside class="col-lg-3">
      <button class="btn btn-outline-dark w-100 d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#filters"><i class="bi bi-funnel"></i> Kategorie a filtry</button>
      <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="filters">
        <div class="offcanvas-header"><h2 class="h5 offcanvas-title">Filtry</h2><button class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#filters" aria-label="Zavřít"></button></div>
        <div class="offcanvas-body d-block">
          <nav class="pp-card p-3 mb-3" aria-label="Kategorie">
            <h2 class="h6 fw-bold font-sans">Kategorie</h2>
            <ul class="list-unstyled small mb-0 vstack gap-1">
              @foreach ($topCats as $tc)
                @php $open = $cat && ($cat->id === $tc->id || $cat->parent_id === $tc->id); @endphp
                <li><a href="{{ route('catalog', $tc->slug) }}" class="text-reset text-decoration-none {{ $open ? 'fw-bold' : '' }}"><i class="bi {{ $open ? 'bi-caret-down-fill' : 'bi-caret-right' }}"></i> {{ $tc->name }} <span class="text-secondary">({{ $tc->activeProductCount() }})</span></a>
                  @if ($open && $tc->children->isNotEmpty())
                    <ul class="list-unstyled ps-3 mt-1 vstack gap-1">
                      @foreach ($tc->children as $ch)
                        <li><a href="{{ route('catalog', $ch->slug) }}" class="text-reset {{ $cat->id === $ch->id ? 'fw-bold' : '' }}">{{ $ch->name }} <span class="text-secondary">({{ $ch->activeProductCount() }})</span></a></li>
                      @endforeach
                    </ul>
                  @endif
                </li>
              @endforeach
            </ul>
          </nav>

          <form class="pp-card p-3" method="get">
            <div class="mb-3">
              <h2 class="h6 fw-bold">Pasuje do auta <span class="fw-normal text-secondary small">(nepovinné)</span></h2>
              <select name="auto" class="form-select form-select-sm mb-1" aria-label="Model auta">
                <option value="">Všechna auta</option>
                @foreach ($carModels as $m)<option value="{{ $m->id }}" @selected($model?->id === $m->id)>{{ $m->full_name }}</option>@endforeach
              </select>
              <div class="small text-secondary">Univerzální produkty zobrazujeme vždy.</div>
            </div>
            <div class="mb-3">
              <h2 class="h6 fw-bold">Cena</h2>
              <div class="d-flex gap-2 align-items-center small"><input class="form-control form-control-sm" name="cena_od" type="number" min="0" value="{{ request('cena_od') }}" placeholder="0" aria-label="Cena od"> – <input class="form-control form-control-sm" name="cena_do" type="number" min="0" value="{{ request('cena_do') }}" placeholder="5000" aria-label="Cena do"> Kč</div>
            </div>
            <div class="mb-3">
              <h2 class="h6 fw-bold">Náročnost montáže</h2>
              @foreach (\App\Models\Product::DIFFICULTY as $d => $label)
                <div class="form-check small"><input class="form-check-input" type="checkbox" name="narocnost[]" value="{{ $d }}" id="d{{ $d }}" @checked(in_array($d, (array) request('narocnost', [])))><label class="form-check-label" for="d{{ $d }}">@include('partials.diff', ['d' => $d]) {{ ucfirst($label) }}</label></div>
              @endforeach
            </div>
            @if ($manufacturers->isNotEmpty())
              <div class="mb-3">
                <h2 class="h6 fw-bold">Výrobce</h2>
                @foreach ($manufacturers as $name => $cnt)
                  <div class="form-check small"><input class="form-check-input" type="checkbox" name="vyrobce[]" value="{{ $name }}" id="m{{ $loop->index }}" @checked(in_array($name, (array) request('vyrobce', [])))><label class="form-check-label" for="m{{ $loop->index }}">{{ $name === 'PlugPlay' ? 'PlugPlay – vlastní výroba' : $name }} <span class="text-secondary">({{ $cnt }})</span></label></div>
                @endforeach
              </div>
            @endif
            <div class="mb-3">
              <h2 class="h6 fw-bold">Další možnosti</h2>
              <div class="form-check form-switch small"><input class="form-check-input" type="checkbox" name="skladem" value="1" id="x1" @checked(request('skladem'))><label class="form-check-label" for="x1">Pouze skladem</label></div>
              <div class="form-check form-switch small"><input class="form-check-input" type="checkbox" name="video" value="1" id="x2" @checked(request('video'))><label class="form-check-label" for="x2">S video návodem</label></div>
              <div class="form-check form-switch small"><input class="form-check-input" type="checkbox" name="sleva" value="1" id="x3" @checked(request('sleva'))><label class="form-check-label" for="x3">Ve slevě</label></div>
            </div>
            <input type="hidden" name="razeni" value="{{ request('razeni') }}"><input type="hidden" name="zobrazeni" value="{{ request('zobrazeni') }}">
            <button class="btn btn-dark btn-sm w-100">Filtrovat</button>
            @if (request()->except('page', 'razeni', 'zobrazeni'))<a href="{{ url()->current() }}" class="btn btn-link btn-sm w-100 link-secondary">Zrušit filtry</a>@endif
          </form>
        </div>
      </div>
    </aside>

    <section class="col-lg-9">
      @if ($cat && $cat->children->isNotEmpty())
        <div class="row row-cols-1 row-cols-sm-3 g-2 mb-3">
          @foreach ($cat->children as $ch)
            <div class="col"><a href="{{ route('catalog', $ch->slug) }}" class="pp-box d-flex align-items-center gap-2 p-2 text-reset text-decoration-none small"><div class="pp-img sm flex-shrink-0" style="width:40px;height:40px;min-height:0"><i class="bi {{ $ch->icon }}"></i></div><b>{{ $ch->name }}</b></a></div>
          @endforeach
        </div>
      @endif

      <div class="d-flex flex-wrap align-items-center gap-2 border-bottom pb-2 mb-3">
        <div class="d-flex flex-wrap gap-1 small">
          @if ($model)<span class="badge rounded-pill text-bg-light border">{{ $model->short_name }}</span>@endif
          @if (request('skladem'))<span class="badge rounded-pill text-bg-light border">Pouze skladem</span>@endif
          @if (request('sleva'))<span class="badge rounded-pill text-bg-light border">Ve slevě</span>@endif
        </div>
        <form class="ms-auto d-flex gap-2" method="get">
          @foreach (request()->except('razeni', 'page') as $k => $v)
            @foreach ((array) $v as $vv)<input type="hidden" name="{{ is_array($v) ? $k.'[]' : $k }}" value="{{ $vv }}">@endforeach
          @endforeach
          <select name="razeni" class="form-select form-select-sm w-auto" aria-label="Řazení" onchange="this.form.submit()">
            @foreach (['prodej' => 'Nejprodávanější', 'levne' => 'Od nejlevnějšího', 'drahe' => 'Od nejdražšího', 'hodnoceni' => 'Nejlépe hodnocené', 'nove' => 'Nejnovější'] as $k => $l)
              <option value="{{ $k }}" @selected(request('razeni', 'prodej') === $k)>{{ $l }}</option>
            @endforeach
          </select>
          <div class="btn-group btn-group-sm">
            <a href="{{ request()->fullUrlWithQuery(['zobrazeni' => null]) }}" class="btn {{ $view === 'grid' ? 'btn-dark' : 'btn-outline-dark' }}" aria-label="Mřížka"><i class="bi bi-grid-3x3-gap"></i></a>
            <a href="{{ request()->fullUrlWithQuery(['zobrazeni' => 'seznam']) }}" class="btn {{ $view === 'list' ? 'btn-dark' : 'btn-outline-dark' }}" aria-label="Seznam"><i class="bi bi-list"></i></a>
          </div>
        </form>
      </div>

      @if ($view === 'list')
        <div class="vstack gap-2">
          @forelse ($products as $p)
            <div class="pp-box p-2 d-flex align-items-center gap-3">
              <a href="{{ route('product.show', $p->slug) }}">@include('partials.img', ['p' => $p, 'class' => 'sm flex-shrink-0', 'style' => 'width:72px;height:72px;min-height:0'])</a>
              <div class="flex-fill"><a class="fw-bold text-reset" href="{{ route('product.show', $p->slug) }}">{{ $p->name }}</a>
                <div class="small text-secondary">{{ $p->short_description }} · {{ $p->install_time }} · @include('partials.diff', ['d' => $p->difficulty])</div></div>
              <b class="text-nowrap">{{ czk($p->price) }}</b>
              <form method="post" action="{{ route('cart.add') }}">@csrf <input type="hidden" name="product_id" value="{{ $p->id }}"><input type="hidden" name="stay" value="1"><button class="btn btn-sm btn-dark" aria-label="Do košíku"><i class="bi bi-cart-plus"></i></button></form>
            </div>
          @empty
            <div class="pp-box p-4 text-center text-secondary">Takovým filtrům nic neodpovídá.</div>
          @endforelse
        </div>
      @else
        <div class="row row-cols-2 row-cols-md-3 g-3">
          @forelse ($products as $p)
            <div class="col">@include('partials.product-card', ['p' => $p, 'car' => $model])</div>
          @empty
            <div class="col-12"><div class="pp-box p-4 text-center text-secondary">Takovým filtrům nic neodpovídá.</div></div>
          @endforelse
        </div>
      @endif

      <div class="d-flex justify-content-center mt-4">{{ $products->links() }}</div>

      @if ($cat?->description)
        <div class="pp-box p-3 mt-4">
          <h2 class="h5">Jak vybrat: {{ mb_strtolower($cat->name) }}</h2>
          <p class="small text-secondary mb-0">{{ $cat->description }}</p>
        </div>
      @endif
    </section>
  </div>
</div>
@endsection
