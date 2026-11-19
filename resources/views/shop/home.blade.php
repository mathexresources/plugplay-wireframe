@extends('layouts.app')
@section('step', '1')
@section('content')
<section class="container pt-4">
  <div class="pp-school-hero p-3 d-flex flex-wrap align-items-center gap-3">
    <i class="bi bi-mortarboard fs-2"></i>
    <div class="flex-fill">
      <b>Tohle je školní projekt</b> do předmětu Malé a střední podnikání. E-shop je plně funkční včetně administrace,
      ale <b>nic se neprodává ani neplatí</b> – platba kartou končí ukázkovou stránkou místo platební brány.
    </div>
    <span class="small text-secondary">Demo účty: <code>petr@example.cz / heslo123</code> · admin <code>admin@plugplay.cz / admin123</code></span>
  </div>
</section>

<section class="container py-5" id="vyber-auta">
  <div class="row g-5 align-items-center">
    <div class="col-lg-6">
      <p class="text-uppercase small fw-bold text-secondary mb-2">Plug &amp; play autoelektronika</p>
      <h1 class="display-5 mb-3">Moderní výbava do staršího auta. Zapojíš ji sám.</h1>
      <p class="lead">USB-C nabíjení, Bluetooth, parkovací senzory nebo automatická světla. Každý produkt jsme vyzkoušeli v konkrétním modelu a přidali k němu návod krok za krokem.</p>

      <div class="pp-card p-4 mt-4">
        <h2 class="h4 mb-3"><span class="badge text-bg-dark rounded-circle me-2">1</span>Jaké máš auto?</h2>
        @auth
          @if (auth()->user()->cars->isNotEmpty())
            <div class="d-flex flex-wrap align-items-center gap-2 small mb-3">
              <span class="text-secondary"><i class="bi bi-person-check"></i> Z tvé garáže:</span>
              @foreach (auth()->user()->cars as $c)
                <form method="post" action="{{ route('car.select') }}">@csrf <input type="hidden" name="car_id" value="{{ $c->id }}">
                  <button class="pp-chip {{ $c->is_active ? 'on' : '' }}">{{ $c->label }}</button></form>
              @endforeach
              <span class="text-secondary">nebo vyber jiné:</span>
            </div>
          @endif
        @endauth
        <form method="post" action="{{ route('car.select') }}" id="car-picker">@csrf
          <div class="row g-2">
            <div class="col-sm-4"><label class="form-label small mb-1" for="brand">Značka</label>
              <select class="form-select" id="brand" required><option value="">Vyber…</option></select></div>
            <div class="col-sm-4"><label class="form-label small mb-1" for="model">Model</label>
              <select class="form-select" id="model" name="car_model_id" required disabled><option value="">–</option></select></div>
            <div class="col-sm-4"><label class="form-label small mb-1" for="body">Karoserie</label>
              <select class="form-select" id="body" name="body_type" disabled><option value="">–</option></select></div>
          </div>
          <button class="btn btn-dark btn-lg w-100 mt-3">Ukázat, co jde vylepšit <i class="bi bi-arrow-right"></i></button>
        </form>
        <div class="d-flex flex-wrap justify-content-between gap-2 small mt-2">
          <a href="{{ route('contact', ['typ' => 'dotaz', 'predmet' => 'Nevím, jaký mám model']) }}" class="link-secondary">Nevíš si rady s modelem? Poradíme</a>
          <a href="{{ route('catalog') }}" class="link-secondary">Procházet celý katalog →</a>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="pp-blueprint">@include('partials.car-svg')
        <span class="pp-hot" style="left:42%;top:32%"><span class="dot"><i class="bi bi-usb-c"></i></span><span class="label">USB-C místo zapalovače</span></span>
        <span class="pp-hot" style="left:57%;top:58%"><span class="dot"><i class="bi bi-bluetooth"></i></span><span class="label">Bluetooth v originálním rádiu</span></span>
        <span class="pp-hot" style="left:88%;top:70%"><span class="dot"><i class="bi bi-lightbulb"></i></span><span class="label">LED bez chybových hlášek</span></span>
        <span class="pp-hot" style="left:14%;top:66%"><span class="dot"><i class="bi bi-p-square"></i></span><span class="label">Parkovací senzory</span></span>
      </div>
    </div>
  </div>
</section>

<section class="container pb-5">
  <h2 class="h4 mb-3">Nejčastější modely našich zákazníků</h2>
  <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
    @foreach ($popular as $m)
      <div class="col"><a href="{{ route('car.show', $m->slug) }}" class="pp-box d-block p-2 text-center text-reset text-decoration-none h-100">
        <div class="pp-img mb-2 sm" style="aspect-ratio:16/9"><i class="bi bi-car-front"></i></div>
        <b>{{ $m->full_name }}</b><div class="small text-secondary">{{ $m->upgrade_count }} {{ plural($m->upgrade_count, 'upgrade', 'upgrady', 'upgradů') }}</div></a></div>
    @endforeach
    <div class="col"><a href="{{ route('catalog') }}" class="pp-box d-flex flex-column justify-content-center h-100 p-2 text-center text-reset text-decoration-none"><i class="bi bi-plus-circle fs-3"></i><b>Jiné auto</b><div class="small text-secondary">univerzální produkty</div></a></div>
  </div>
</section>

<section class="bg-white border-top border-bottom py-5">
  <div class="container">
    <h2 class="text-center mb-4">Jak to funguje</h2>
    <div class="row g-4 text-center">
      <div class="col-md-3"><i class="bi bi-car-front fs-1 text-primary"></i><h3 class="h5 mt-2">1. Zadáš svoje auto</h3><p class="small text-secondary">Ukážeme jen produkty, které do něj opravdu pasují.</p></div>
      <div class="col-md-3"><i class="bi bi-grid-3x3-gap fs-1 text-primary"></i><h3 class="h5 mt-2">2. Vybereš, co vylepšit</h3><p class="small text-secondary">Klikneš na část auta – interiér, světla, zamykání nebo kufr.</p></div>
      <div class="col-md-3"><i class="bi bi-patch-check fs-1 text-primary"></i><h3 class="h5 mt-2">3. Víš, do čeho jdeš</h3><p class="small text-secondary">Ještě před nákupem vidíš náročnost, čas montáže, potřebné nářadí a výsledek našeho testu.</p></div>
      <div class="col-md-3"><i class="bi bi-tools fs-1 text-primary"></i><h3 class="h5 mt-2">4. Zapojíš to sám</h3><p class="small text-secondary">Podle návodu s fotkami a videem pro tvůj model. Kabeláž s konektorem je v balení.</p></div>
    </div>
  </div>
</section>

<section class="container py-5">
  <div class="d-flex justify-content-between align-items-end mb-3">
    <h2 class="mb-0">Před a po montáži</h2>
    <a href="{{ route('showcases') }}" class="small">Další proměny →</a>
  </div>
  <div class="row g-4">
    @forelse ($showcases as $s)
      <div class="col-md-4"><div class="pp-product p-0">
        <div class="d-flex"><div class="pp-img flex-fill rounded-0" style="aspect-ratio:1"><img src="{{ $s->url($s->before_image) }}" alt="Před"><span class="badge text-bg-dark position-absolute top-0 start-0 m-2">PŘED</span></div>
        <div class="pp-img flex-fill rounded-0" style="aspect-ratio:1"><img src="{{ $s->url($s->after_image) }}" alt="Po"><span class="badge text-bg-primary position-absolute top-0 start-0 m-2">PO</span></div></div>
        <div class="p-3"><b>{{ $s->title }}</b><div class="small text-secondary">{{ $s->author_name }}@if ($s->product) · <a href="{{ route('product.show', $s->product->slug) }}">{{ $s->product->name }}</a>@endif</div></div>
      </div></div>
    @empty
      @foreach ($featured as $p)
        <div class="col-md-4"><a href="{{ route('product.show', $p->slug) }}" class="pp-product p-0 text-reset text-decoration-none">
          <div class="d-flex"><div class="pp-img flex-fill rounded-0" style="aspect-ratio:1"><i class="bi bi-car-front"></i><span class="badge text-bg-dark position-absolute top-0 start-0 m-2">PŘED</span></div>
          <div class="pp-img flex-fill rounded-0" style="aspect-ratio:1"><i class="bi {{ $p->category->icon ?? 'bi-stars' }}"></i><span class="badge text-bg-primary position-absolute top-0 start-0 m-2">PO</span></div></div>
          <div class="p-3"><b>{{ $p->name }}</b><div class="small text-secondary">{{ $p->install_time }} · @include('partials.diff', ['d' => $p->difficulty]) {{ \App\Models\Product::DIFFICULTY[$p->difficulty] }}</div></div>
        </a></div>
      @endforeach
    @endforelse
  </div>
</section>

<section class="container pb-5">
  <div class="pp-box p-4">
    <div class="row g-4 align-items-center">
      <div class="col-md-4"><div class="pp-img" style="aspect-ratio:16/10"><i class="bi bi-tools"></i></div></div>
      <div class="col-md-8">
        <h2 class="h3">Prodáváme jen to, co jsme sami zapojili</h2>
        <p class="text-secondary mb-3">Každý produkt nejdřív vyzkoušíme v reálném autě. V recenzi najdeš výhody i slabiny – nic nepřikrášlujeme.</p>
        <div class="d-flex flex-wrap gap-3 small">
          <span><i class="bi bi-check2-circle"></i> Ověřená kompatibilita</span>
          <span><i class="bi bi-camera-video"></i> Návody s fotkami a videem</span>
          <span><i class="bi bi-cpu"></i> Vlastní vývoj elektroniky</span>
          <span><i class="bi bi-box-seam"></i> Zásilkovna od 69 Kč</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="container pb-4">
  <h2 class="h5">Autoelektronika do starších aut bez návštěvy servisu</h2>
  <p class="small text-secondary mb-0">Starší auto nemusí znamenat zastaralou výbavu. Nabízíme USB-C nabíječky místo zapalovače, Bluetooth adaptéry do originálních rádií, LED osvětlení, parkovací senzory i moduly pro komfortní zamykání. U každého produktu uvádíme, se kterými modely jsme ho ověřili, jak náročná je montáž a jaké nářadí budeš potřebovat.</p>
</section>
@endsection

@push('scripts')
<script>
  const CARS = @json($carsJson);
  const brand = document.getElementById('brand'), model = document.getElementById('model'), body = document.getElementById('body');
  Object.keys(CARS).forEach(b => brand.add(new Option(b, b)));
  const fill = (sel, items, empty) => { sel.innerHTML = ''; sel.add(new Option(empty, '')); items.forEach(([v, l]) => sel.add(new Option(l, v))); sel.disabled = !items.length; };
  brand.addEventListener('change', () => {
    fill(model, (CARS[brand.value] || []).map(m => [m.id, `${m.label} ${m.years}`]), 'Vyber model…');
    fill(body, [], '–');
    if (model.options.length === 2) { model.selectedIndex = 1; model.dispatchEvent(new Event('change')); }
  });
  model.addEventListener('change', () => {
    const m = (CARS[brand.value] || []).find(x => String(x.id) === model.value);
    fill(body, (m ? m.bodies : []).map(b => [b, b]), 'Nevím / nezáleží');
  });
</script>
@endpush
