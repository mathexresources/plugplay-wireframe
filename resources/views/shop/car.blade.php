@extends('layouts.app')
@section('step', '2')
@section('title', 'Elektronika do '.$model->full_name.' – upgrady s návodem')
@section('description', \Illuminate\Support\Str::limit($model->description, 155))
@php $q = fn (array $over) => route('car.show', $model->slug).'?'.http_build_query(array_filter(array_merge(['zona' => $zone?->slug, 'cil' => $goal, 'narocnost' => $maxDiff < 3 ? $maxDiff : null, 'razeni' => $sort !== 'oblibene' ? $sort : null], $over))); @endphp
@section('content')
<div class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <div>
      <p class="text-uppercase small fw-bold text-secondary mb-1">Upgrady pro tvoje auto</p>
      <h1 class="mb-0">{{ $model->full_name }} <span class="text-secondary fs-4">{{ $model->years }}{{ session('car_body') ? ' · '.session('car_body') : '' }}</span></h1>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-3">
      <div class="small text-secondary"><i class="bi bi-check2-circle"></i> {{ $all->count() }} {{ plural($all->count(), 'ověřený upgrade', 'ověřené upgrady', 'ověřených upgradů') }} pro tento model</div>
      @guest
        <a href="{{ route('login') }}#registrace" class="btn btn-sm btn-outline-dark"><i class="bi bi-bookmark-plus"></i> Uložit auto do garáže</a>
      @else
        @if (! $garageCar)
          <form method="post" action="{{ route('account.cars.store') }}">@csrf <input type="hidden" name="car_model_id" value="{{ $model->id }}"><input type="hidden" name="body_type" value="{{ session('car_body') }}"><input type="hidden" name="make_active" value="1">
            <button class="btn btn-sm btn-outline-dark"><i class="bi bi-bookmark-plus"></i> Uložit auto do garáže</button></form>
        @else
          <a href="{{ route('account.garage') }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-bookmark-check"></i> {{ $garageCar->label }} je v garáži</a>
        @endif
      @endguest
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-lg-8">
      <div class="pp-blueprint">
        @include('partials.car-svg')
        @foreach ($zones as $z)
          <a href="{{ $q(['zona' => $z->slug]) }}#vysledky" class="pp-hot {{ $zone && $zone->id === $z->id ? 'active' : '' }} {{ $z->count ? '' : 'empty' }}" style="left:{{ $z->pos_x }}%;top:{{ $z->pos_y }}%">
            <span class="dot">{{ $z->count }}</span><span class="label">{{ $z->name }}</span></a>
        @endforeach
      </div>
      <div class="small text-secondary mt-2"><i class="bi bi-hand-index"></i> Klikni na část auta – číslo ukazuje počet ověřených upgradů.</div>
      <div class="d-flex d-sm-none flex-wrap gap-2 mt-2">
        @foreach ($zones as $z)<a href="{{ $q(['zona' => $z->slug]) }}#vysledky" class="pp-chip {{ $zone && $zone->id === $z->id ? 'on' : '' }}"><i class="bi {{ $z->icon }}"></i> {{ $z->name }} ({{ $z->count }})</a>@endforeach
      </div>
    </div>
    <div class="col-lg-4">
      <div class="pp-card p-3 h-100">
        <h2 class="h5">Co od auta čekáš?</h2>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <a href="{{ $q(['cil' => null]) }}#vysledky" class="pp-chip {{ ! $goal ? 'on' : '' }}"><i class="bi bi-stars"></i> Všechno</a>
          @foreach (\App\Models\Product::GOALS as $key => [$label, $icon])
            <a href="{{ $q(['cil' => $key]) }}#vysledky" class="pp-chip {{ $goal === $key ? 'on' : '' }}"><i class="bi {{ $icon }}"></i> {{ $label }}</a>
          @endforeach
        </div>
        <h2 class="h5">Do jaké montáže se pustíš?</h2>
        <div class="vstack gap-2">
          @foreach (\App\Models\Product::DIFFICULTY_LONG as $d => $label)
            <a href="{{ $q(['narocnost' => $d === 3 ? null : $d]) }}#vysledky" class="pp-chip {{ $maxDiff === $d ? 'on' : '' }}">@include('partials.diff', ['d' => $d]) {{ $label }}</a>
          @endforeach
        </div>
        <div class="small text-secondary mt-3"><i class="bi bi-info-circle"></i> Náročnější produkty uvidíš taky – šedě a na konci seznamu.</div>
      </div>
    </div>
  </div>

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3" id="vysledky">
    <h2 class="h4 mb-0">{{ $zone?->name }} pro {{ $model->short_name }} <span class="text-secondary fs-6">{{ $products->count() }} {{ plural($products->count(), 'upgrade', 'upgrady', 'upgradů') }}</span></h2>
    <a href="{{ route('catalog', ['auto' => $model->id]) }}" class="small link-secondary ms-auto"><i class="bi bi-list"></i> Zobrazit jako klasický katalog</a>
    <form class="d-flex"><input type="hidden" name="zona" value="{{ $zone?->slug }}"><input type="hidden" name="cil" value="{{ $goal }}"><input type="hidden" name="narocnost" value="{{ $maxDiff < 3 ? $maxDiff : '' }}">
      <select name="razeni" class="form-select form-select-sm w-auto" aria-label="Řazení" onchange="this.form.submit()">
        <option value="oblibene" @selected($sort === 'oblibene')>Nejoblíbenější u {{ $model->code ?: $model->name }}</option>
        <option value="snadne" @selected($sort === 'snadne')>Nejsnazší montáž</option>
        <option value="levne" @selected($sort === 'levne')>Od nejlevnějšího</option>
      </select></form>
  </div>

  <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
    @forelse ($products as $p)
      <div class="col">@include('partials.product-card', ['p' => $p, 'car' => $model, 'harder' => $p->difficulty > $maxDiff])</div>
    @empty
      <div class="col-12"><div class="pp-box p-4 text-center text-secondary">Pro tuhle část auta a zvolený cíl zatím nic nemáme. <a href="{{ $q(['cil' => null]) }}">Zobrazit vše</a></div></div>
    @endforelse
  </div>

  @foreach ($bundles as $b)
    <div class="pp-card p-4 mb-4">
      <div class="row g-4 align-items-center">
        <div class="col-md-3"><div class="pp-img" style="aspect-ratio:1"><i class="bi bi-box2-heart"></i></div></div>
        <div class="col-md-6">
          <span class="badge text-bg-primary mb-2">Sada pro {{ $model->short_name }}</span>
          <h2 class="h3">{{ $b->name }}</h2>
          <p class="small text-secondary mb-2">{{ $b->description }}</p>
          <ul class="small mb-0">@foreach ($b->products as $bp)<li><a class="text-reset" href="{{ route('product.show', $bp->slug) }}">{{ $bp->name }}</a></li>@endforeach</ul>
        </div>
        <div class="col-md-3 text-md-end">
          @if ($b->full_price > $b->price)<div class="text-secondary text-decoration-line-through small">{{ czk($b->full_price) }}</div>@endif
          <div class="fs-3 fw-bold">{{ czk($b->price) }}</div>
          <div class="small text-secondary mb-2"><i class="bi bi-clock"></i> zhruba {{ \App\Models\Product::minutes($b->install_minutes) }} práce</div>
          <form method="post" action="{{ route('cart.bundle', $b) }}">@csrf <button class="btn btn-dark w-100">Přidat sadu do plánu</button></form>
        </div>
      </div>
    </div>
  @endforeach

  <section class="mt-5">
    <h2 class="h5">Elektronika do {{ $model->short_name }}: co se vyplatí</h2>
    <p class="small text-secondary">{{ $model->description }}</p>
  </section>
  <p class="text-center small text-secondary mt-4">Nenašel jsi, co hledáš? <a href="{{ route('contact', ['typ' => 'navrh', 'predmet' => 'Návrh pro '.$model->full_name]) }}">Napiš nám, co bys do auta chtěl</a> – zkusíme to najít a otestovat.</p>
</div>
@endsection
