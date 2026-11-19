<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Administrace') | PlugPlay admin</title>
  <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
@php
  $pendingReviews = \App\Models\Review::where('is_approved', false)->count();
  $pendingShow = \App\Models\Showcase::where('is_approved', false)->count();
  $unread = \App\Models\Message::where('is_read', false)->count();
  $newOrders = \App\Models\Order::where('status', 'new')->count();
  $nav = [
    'Přehled' => [['admin.dashboard', 'bi-speedometer2', 'Přehled', null]],
    'Prodej' => [['admin.orders.index', 'bi-receipt', 'Objednávky', $newOrders], ['admin.customers.index', 'bi-people', 'Zákazníci', null], ['admin.coupons.index', 'bi-ticket-perforated', 'Slevové kódy', null]],
    'Katalog' => [['admin.products.index', 'bi-box-seam', 'Produkty', null], ['admin.categories.index', 'bi-diagram-3', 'Kategorie', null], ['admin.bundles.index', 'bi-box2-heart', 'Sady', null], ['admin.cars.index', 'bi-car-front', 'Modely aut', null], ['admin.zones.index', 'bi-bullseye', 'Zóny auta', null], ['admin.tools.index', 'bi-tools', 'Nářadí', null]],
    'Obsah' => [['admin.guides.index', 'bi-book', 'Návody', null], ['admin.pages.index', 'bi-file-text', 'Stránky', null], ['admin.reviews.index', 'bi-star', 'Recenze', $pendingReviews], ['admin.showcases.index', 'bi-images', 'Před a po', $pendingShow], ['admin.messages.index', 'bi-envelope', 'Zprávy', $unread]],
    'Nastavení' => [['admin.shipping.index', 'bi-truck', 'Doprava', null], ['admin.payments.index', 'bi-credit-card', 'Platby', null], ['admin.settings', 'bi-gear', 'Obchod', null]],
  ];
@endphp
<div class="d-flex min-vh-100">
  <nav class="pp-admin-nav text-white p-3 flex-shrink-0 d-none d-md-block" style="width:230px;background:#14171c">
    <a href="{{ route('admin.dashboard') }}" class="pp-logo text-white d-block mb-3">Plug<b>Play</b> <span class="fs-6 opacity-75">admin</span></a>
    @foreach ($nav as $group => $links)
      <div class="text-uppercase small opacity-50 mt-3 mb-1">{{ $group }}</div>
      @foreach ($links as [$r, $ic, $l, $badge])
        @php $base = \Illuminate\Support\Str::beforeLast($r, '.'); @endphp
        <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs($r) || ($base !== 'admin' && request()->routeIs($base.'.*')) ? 'active' : '' }}" href="{{ route($r) }}"><i class="bi {{ $ic }}"></i> {{ $l }}@if ($badge)<span class="badge text-bg-warning ms-auto">{{ $badge }}</span>@endif</a>
      @endforeach
    @endforeach
    <hr class="opacity-25">
    <a class="nav-link" href="{{ route('home') }}" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Zobrazit e-shop</a>
    <form method="post" action="{{ route('logout') }}">@csrf<button class="nav-link btn btn-link text-start w-100"><i class="bi bi-box-arrow-right"></i> Odhlásit</button></form>
  </nav>
  <div class="flex-fill bg-light" style="min-width:0">
    <div class="bg-white border-bottom px-4 py-2 d-flex align-items-center gap-3">
      <div class="dropdown d-md-none"><button class="btn btn-sm btn-outline-dark" data-bs-toggle="dropdown" aria-label="Menu"><i class="bi bi-list"></i></button>
        <ul class="dropdown-menu">@foreach ($nav as $links)@foreach ($links as [$r, $ic, $l])<li><a class="dropdown-item" href="{{ route($r) }}"><i class="bi {{ $ic }}"></i> {{ $l }}</a></li>@endforeach @endforeach</ul></div>
      <span class="small text-secondary"><i class="bi bi-mortarboard"></i> Školní projekt</span>
      <span class="ms-auto small">{{ auth()->user()->name }}</span>
    </div>
    <div class="p-4">
      @include('partials.flash')
      @yield('content')
    </div>
  </div>
</div>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script>document.querySelectorAll('[data-confirm]').forEach(f => f.addEventListener('submit', e => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));</script>
@stack('scripts')
</body>
</html>
