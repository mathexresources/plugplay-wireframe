@extends('account.layout')
@section('title', 'Osobní údaje')
@section('account')
<h2 class="mb-3">Osobní údaje a adresy</h2>
<div class="row g-4">
  <div class="col-md-6">
    <form class="pp-card p-3 mb-4" method="post" action="{{ route('account.profile.update') }}">@csrf @method('put')
      <h3 class="h6 fw-bold">Kontaktní údaje</h3>
      <div class="mb-2"><label class="form-label small" for="un">Jméno a příjmení</label><input class="form-control form-control-sm" id="un" name="name" value="{{ old('name', $user->name) }}" required></div>
      <div class="mb-2"><label class="form-label small" for="ue">E-mail</label><input class="form-control form-control-sm" id="ue" name="email" type="email" value="{{ old('email', $user->email) }}" required></div>
      <div class="mb-3"><label class="form-label small" for="ut">Telefon</label><input class="form-control form-control-sm" id="ut" name="phone" value="{{ old('phone', $user->phone) }}"></div>
      <h3 class="h6 fw-bold">Upozornění e-mailem</h3>
      <div class="form-check form-switch small"><input class="form-check-input" type="checkbox" name="notify_upgrades" value="1" @checked($user->notify_upgrades) id="ntf1"><label class="form-check-label" for="ntf1">Nové upgrady pro moje auta</label></div>
      <div class="form-check form-switch small mb-3"><input class="form-check-input" type="checkbox" name="notify_deals" value="1" @checked($user->notify_deals) id="ntf2"><label class="form-check-label" for="ntf2">Akce a slevy</label></div>
      <button class="btn btn-sm btn-dark">Uložit změny</button>
    </form>
    <form class="pp-card p-3" method="post" action="{{ route('account.password') }}">@csrf @method('put')
      <h3 class="h6 fw-bold">Změnit heslo</h3>
      <input class="form-control form-control-sm mb-2" type="password" name="current_password" placeholder="Současné heslo" required autocomplete="current-password">
      <input class="form-control form-control-sm mb-2" type="password" name="password" placeholder="Nové heslo (min. 8 znaků)" required minlength="8" autocomplete="new-password">
      <input class="form-control form-control-sm mb-3" type="password" name="password_confirmation" placeholder="Nové heslo znovu" required>
      <button class="btn btn-sm btn-dark">Změnit heslo</button>
    </form>
  </div>
  <div class="col-md-6">
    <div class="pp-card p-3">
      <h3 class="h6 fw-bold">Doručovací adresy</h3>
      @foreach ($user->addresses as $a)
        <div class="pp-box p-2 small mb-2 d-flex align-items-center gap-2"><i class="bi bi-house"></i><span class="flex-fill">{{ $a->label ? $a->label.' – ' : '' }}{{ $a->street }}, {{ $a->zip }} {{ $a->city }}</span>
          @if ($a->is_default)<span class="badge text-bg-light border">výchozí</span>@endif
          <form method="post" action="{{ route('account.addresses.delete', $a) }}">@csrf @method('delete')<button class="btn btn-sm btn-link link-secondary p-0" aria-label="Smazat adresu"><i class="bi bi-trash"></i></button></form></div>
      @endforeach
      <details class="mt-2"><summary class="small link-primary" style="cursor:pointer">Přidat adresu</summary>
        <form method="post" action="{{ route('account.addresses.store') }}" class="mt-2">@csrf
          <input class="form-control form-control-sm mb-2" name="label" placeholder="Označení (např. Domů)">
          <input class="form-control form-control-sm mb-2" name="street" placeholder="Ulice a číslo" required>
          <div class="d-flex gap-2 mb-2"><input class="form-control form-control-sm" name="city" placeholder="Město" required><input class="form-control form-control-sm" style="max-width:110px" name="zip" placeholder="PSČ" required></div>
          <div class="form-check small mb-2"><input class="form-check-input" type="checkbox" name="is_default" value="1" id="def"><label class="form-check-label" for="def">Výchozí adresa</label></div>
          <button class="btn btn-sm btn-dark">Uložit adresu</button>
        </form></details>
    </div>
  </div>
</div>
@unless ($user->is_admin)
  <details class="mt-4"><summary class="small text-danger" style="cursor:pointer">Zrušit účet</summary>
    <form method="post" action="{{ route('account.destroy') }}" class="pp-box p-3 mt-2" style="max-width:420px">@csrf @method('delete')
      <p class="small">Smažeme účet, garáž i uložené plány. Objednávky zůstanou kvůli účetnictví.</p>
      <input class="form-control form-control-sm mb-2" type="password" name="password" placeholder="Pro potvrzení zadej heslo" required>
      <button class="btn btn-sm btn-danger">Opravdu zrušit účet</button>
    </form></details>
@endunless
@endsection
