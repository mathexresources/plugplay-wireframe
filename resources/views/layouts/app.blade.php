<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Elektronika do starších aut, kterou zapojíš sám') | PlugPlay.cz</title>
  <meta name="description" content="@yield('description', 'USB-C nabíjení, Bluetooth, parkovací senzory nebo automatická světla do starších aut. Ověřená kompatibilita s tvým modelem a návod krok za krokem.')">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">
  @stack('head')
</head>
<body>
<div class="pp-school text-center py-1 px-2"><i class="bi bi-mortarboard-fill"></i> <b>Školní projekt</b> – PlugPlay.cz není skutečný obchod, objednávky se nevyřizují a nic se neplatí.</div>

<header class="bg-white border-bottom">
  <div class="container py-2 d-flex flex-wrap align-items-center gap-3">
    <a href="{{ route('home') }}" class="pp-logo" aria-label="PlugPlay.cz – úvodní stránka">Plug<b>Play</b>.cz</a>
    <div class="dropdown">
      <button class="btn btn-sm btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-grid"></i> Katalog</button>
      <ul class="dropdown-menu">
        @foreach ($navCategories as $c)
          <li><a class="dropdown-item" href="{{ route('catalog', $c->slug) }}"><i class="bi {{ $c->icon }} me-1"></i> {{ $c->name }}</a></li>
        @endforeach
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item fw-bold" href="{{ route('catalog') }}">Všechny produkty →</a></li>
      </ul>
    </div>

    @if ($navCar || $navCars->isNotEmpty())
      <div class="dropdown">
        <button class="pp-car-pill dropdown-toggle" data-bs-toggle="dropdown">
          <i class="bi bi-car-front-fill"></i>
          @if ($navCar) {{ $navCar->full_name }} <span class="text-secondary d-none d-md-inline">{{ $navCar->years }}</span> @else Vyber auto @endif
        </button>
        <div class="dropdown-menu p-2" style="min-width:290px">
          @auth
            <div class="small fw-bold text-secondary px-2 mb-1">Moje garáž</div>
            @foreach ($navCars as $c)
              <form method="post" action="{{ route('car.select') }}">@csrf <input type="hidden" name="car_id" value="{{ $c->id }}">
                <button class="dropdown-item rounded d-flex align-items-center gap-2 {{ $navCar && $navCar->id === $c->car_model_id ? 'active' : '' }}">
                  <i class="bi {{ $navCar && $navCar->id === $c->car_model_id ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                  <span class="text-start"><b>{{ $c->label }}</b><br><span class="small">{{ $c->carModel->full_name }}{{ $c->year ? ' · '.$c->year : '' }}</span></span>
                </button>
              </form>
            @endforeach
            <hr class="dropdown-divider">
            <a class="dropdown-item rounded" href="{{ route('account.garage') }}"><i class="bi bi-plus-lg"></i> Přidat nebo upravit auta</a>
          @endauth
          <a class="dropdown-item rounded" href="{{ route('home') }}#vyber-auta"><i class="bi bi-arrow-repeat"></i> Změnit auto</a>
          @if ($navCar)
            <form method="post" action="{{ route('car.clear') }}">@csrf <button class="dropdown-item rounded text-secondary"><i class="bi bi-x-lg"></i> Nakupovat bez auta</button></form>
          @endif
          @guest
            <hr class="dropdown-divider">
            <div class="small px-2 text-secondary">Máš víc aut? <a href="{{ route('login') }}">Přihlas se</a> a ulož si je do garáže.</div>
          @endguest
        </div>
      </div>
    @endif

    <div class="ms-auto d-flex align-items-center gap-2">
      <form action="{{ route('search') }}" class="input-group input-group-sm d-none d-md-flex" style="width:240px" role="search">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Hledat produkt nebo návod…" aria-label="Hledat">
      </form>
      <a href="{{ route('guides') }}" class="btn btn-sm btn-outline-dark d-none d-sm-inline-block"><i class="bi bi-book"></i> Návody</a>
      <div class="dropdown">
        <button class="btn btn-sm btn-outline-dark" data-bs-toggle="dropdown" aria-label="Můj účet">
          <i class="bi {{ auth()->check() ? 'bi-person-check-fill' : 'bi-person' }}"></i><span class="d-none d-lg-inline"> {{ auth()->check() ? auth()->user()->first_name : 'Přihlásit se' }}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          @auth
            @if (auth()->user()->is_admin)
              <li><a class="dropdown-item fw-bold" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Administrace</a></li><li><hr class="dropdown-divider"></li>
            @endif
            <li><a class="dropdown-item" href="{{ route('account.garage') }}"><i class="bi bi-car-front"></i> Moje garáž</a></li>
            <li><a class="dropdown-item" href="{{ route('account.orders') }}"><i class="bi bi-box-seam"></i> Objednávky</a></li>
            <li><a class="dropdown-item" href="{{ route('account.guides') }}"><i class="bi bi-book"></i> Moje návody</a></li>
            <li><a class="dropdown-item" href="{{ route('account.plans') }}"><i class="bi bi-bookmark"></i> Uložené plány</a></li>
            <li><a class="dropdown-item" href="{{ route('account.profile') }}"><i class="bi bi-person"></i> Osobní údaje</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><form method="post" action="{{ route('logout') }}">@csrf<button class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Odhlásit se</button></form></li>
          @else
            <li><a class="dropdown-item" href="{{ route('login') }}">Přihlásit se</a></li>
            <li><a class="dropdown-item" href="{{ route('login') }}#registrace">Založit účet</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><span class="dropdown-item-text small text-secondary">Ulož si auta do garáže<br>a měj návody vždy po ruce.</span></li>
          @endauth
        </ul>
      </div>
      <a href="{{ route('cart') }}" class="btn btn-sm btn-dark position-relative">
        <i class="bi {{ $navCar ? 'bi-list-check' : 'bi-cart3' }}"></i> {{ $navCar ? 'Plán' : 'Košík' }}
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-primary">{{ $cartCount }}</span>
      </a>
    </div>
  </div>
  <form action="{{ route('search') }}" class="container pb-2 d-md-none" role="search">
    <input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Hledat produkt nebo návod…" aria-label="Hledat">
  </form>
</header>

@php $step = (int) View::yieldContent('step'); @endphp
@if ($step >= 1 && $step <= 5 && $navCar)
  @php $steps = [[route('home').'#vyber-auta', 'Tvoje auto'], [route('car.show', $navCar->slug), 'Co vylepšit'], [route('car.show', $navCar->slug), 'Výběr produktu'], [route('cart'), 'Tvůj plán'], [route('checkout'), 'Objednávka']]; @endphp
  <nav class="pp-journey" aria-label="Postup výběru a objednávky">
    <div class="container"><ol>
      @foreach ($steps as $i => [$href, $label])
        @php $n = $i + 1; @endphp
        <li class="{{ $n < $step ? 'done' : ($n === $step ? 'active' : '') }}"><a href="{{ $href }}"><span class="num">@if ($n < $step)<i class="bi bi-check-lg"></i>@else{{ $n }}@endif</span>{{ $label }}</a></li>
      @endforeach
    </ol></div>
  </nav>
@endif

@include('partials.flash')

<main>@yield('content')</main>

<footer class="border-top bg-white mt-5 py-4">
  <div class="container">
    <div class="row g-4 small">
      <div class="col-12 col-md-3">
        <div class="pp-logo fs-5">Plug<b>Play</b>.cz</div>
        <p class="text-secondary mt-2 mb-0">Elektronika do starších aut, kterou zapojíš sám. Testujeme, píšeme návody a radíme s výběrem.</p>
      </div>
      <div class="col-6 col-md-2"><b>Nákup</b>
        @foreach ($footerPages['nakup'] ?? [] as $p)<div><a class="link-secondary" href="{{ route('page', $p->slug) }}">{{ $p->title }}</a></div>@endforeach
      </div>
      <div class="col-6 col-md-3"><b>Poradna</b>
        <div><a class="link-secondary" href="{{ route('guides') }}">Návody podle auta</a></div>
        <div><a class="link-secondary" href="{{ route('guides', ['video' => 1]) }}">Video návody</a></div>
        @foreach ($footerPages['poradna'] ?? [] as $p)<div><a class="link-secondary" href="{{ route('page', $p->slug) }}">{{ $p->title }}</a></div>@endforeach
        <div><a class="link-secondary" href="{{ route('showcases') }}">Před a po montáži</a></div>
      </div>
      <div class="col-6 col-md-2"><b>Oblíbené modely</b>
        @foreach ($footerModels as $m)<div><a class="link-secondary" href="{{ route('car.show', $m->slug) }}">{{ $m->full_name }}</a></div>@endforeach
      </div>
      <div class="col-6 col-md-2"><b>Kontakt</b>
        <div>{{ \App\Models\Setting::get('shop_email') }}</div><div>{{ \App\Models\Setting::get('shop_hours') }}</div>
        <div><a class="link-secondary" href="{{ route('contact') }}">Poradíme s výběrem</a></div>
      </div>
    </div>
    <div class="small text-secondary border-top mt-4 pt-3 d-flex flex-wrap justify-content-between gap-2">
      <span>© {{ date('Y') }} PlugPlay.cz · školní projekt, nejde o skutečný e-shop</span>
      <a class="link-secondary" href="{{ route('feed.heureka') }}">XML feed pro srovnávače</a>
    </div>
  </div>
</footer>

<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
@stack('scripts')
</body>
</html>
