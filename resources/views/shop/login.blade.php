@extends('layouts.app')
@section('title', 'Přihlášení a registrace')
@section('content')
<div class="container py-5" style="max-width:1000px">
  <div class="row g-4 align-items-stretch">
    <div class="col-md-6">
      <div class="pp-card p-4 h-100">
        <ul class="nav nav-pills nav-fill mb-4" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#login" id="tab-login">Přihlásit se</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#register" id="tab-register">Založit účet</button></li>
        </ul>
        <div class="tab-content">
          <form class="tab-pane fade show active" id="login" method="post" action="{{ route('login.post') }}">@csrf
            <div class="mb-3"><label class="form-label small" for="le">E-mail</label><input class="form-control" id="le" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required></div>
            <div class="mb-2"><label class="form-label small" for="lp">Heslo</label><input class="form-control" id="lp" name="password" type="password" autocomplete="current-password" required></div>
            <div class="d-flex justify-content-between small mb-3">
              <div class="form-check"><input class="form-check-input" type="checkbox" id="rem" name="remember" value="1" checked><label class="form-check-label" for="rem">Zůstat přihlášen</label></div>
              <a href="{{ route('password.request') }}" class="link-secondary">Zapomněl jsi heslo?</a>
            </div>
            <button class="btn btn-dark w-100">Přihlásit se</button>
            <div class="small text-secondary mt-3">Demo: <code>petr@example.cz</code> / <code>heslo123</code> · správce <code>admin@plugplay.cz</code> / <code>admin123</code></div>
          </form>
          <form class="tab-pane fade" id="register" method="post" action="{{ route('register') }}">@csrf
            <div class="mb-3"><label class="form-label small" for="rn">Jméno a příjmení</label><input class="form-control" id="rn" name="name" autocomplete="name" value="{{ old('name') }}" required></div>
            <div class="mb-3"><label class="form-label small" for="re">E-mail</label><input class="form-control" id="re" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required></div>
            <div class="mb-3"><label class="form-label small" for="rp">Heslo</label><input class="form-control" id="rp" name="password" type="password" autocomplete="new-password" required minlength="8"><div class="form-text">Alespoň 8 znaků</div></div>
            <div class="pp-box p-3 mb-3">
              <div class="small fw-bold mb-2"><i class="bi bi-car-front"></i> Rovnou si ulož auto <span class="fw-normal text-secondary">(nepovinné)</span></div>
              <select class="form-select form-select-sm" name="car_model_id" aria-label="Model auta">
                <option value="">Bez auta</option>
                @foreach ($models as $m)<option value="{{ $m->id }}" @selected(old('car_model_id', session('car_model_id')) == $m->id)>{{ $m->full_name }} ({{ $m->years }})</option>@endforeach
              </select>
            </div>
            <div class="form-check small mb-2"><input class="form-check-input" type="checkbox" id="nl" name="newsletter" value="1"><label class="form-check-label" for="nl">Chci dostávat novinky pro svoje auta</label></div>
            <p class="small text-secondary mb-3">Registrací souhlasíš s <a href="{{ route('page', 'obchodni-podminky') }}">obchodními podmínkami</a>. Jak nakládáme s tvými údaji, najdeš v <a href="{{ route('page', 'ochrana-osobnich-udaju') }}">zásadách ochrany osobních údajů</a>.</p>
            <button class="btn btn-dark w-100">Založit účet</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="pp-box p-4 h-100">
        <h1 class="h2">Všechna tvoje auta na jednom místě</h1>
        <p class="text-secondary">Účet není povinný – nakoupit můžeš i bez něj. S účtem ale:</p>
        <ul class="list-unstyled vstack gap-3">
          <li class="d-flex gap-3"><i class="bi bi-car-front fs-4 text-primary"></i><div><b>Uložíš si všechna svoje auta</b><div class="small text-secondary">Přepneš mezi nimi jedním klikem a uvidíš jen produkty, které pasují.</div></div></li>
          <li class="d-flex gap-3"><i class="bi bi-check2-square fs-4 text-primary"></i><div><b>Uvidíš, co už máš namontované</b><div class="small text-secondary">Přehled upgradů pro každé auto zvlášť.</div></div></li>
          <li class="d-flex gap-3"><i class="bi bi-book fs-4 text-primary"></i><div><b>Návody budeš mít vždy po ruce</b><div class="small text-secondary">Ke všemu, co sis koupil – i za rok, až budeš něco předělávat.</div></div></li>
          <li class="d-flex gap-3"><i class="bi bi-bookmark fs-4 text-primary"></i><div><b>Neztratíš rozpracovaný plán</b><div class="small text-secondary">Ulož si ho a vrať se k němu, kdy budeš chtít.</div></div></li>
          <li class="d-flex gap-3"><i class="bi bi-bell fs-4 text-primary"></i><div><b>Dozvíš se o novinkách pro svoje auto</b><div class="small text-secondary">Dáme vědět, jakmile otestujeme nový upgrade pro tvůj model.</div></div></li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
  if (location.hash === '#registrace' || @json(old('name') !== null)) bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-register')).show();
</script>
@endpush
