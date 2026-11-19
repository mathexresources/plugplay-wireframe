@extends('layouts.app')
@section('step', '5')
@section('title', 'Doprava a platba')
@section('content')
<div class="container py-4">
  <h1 class="mb-4">Doprava a platba</h1>
  <form method="post" action="{{ route('checkout.store') }}" class="row g-4" novalidate>@csrf
    <div class="col-lg-8 vstack gap-4">
      <section class="pp-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
          <h2 class="h5 mb-0"><span class="badge text-bg-dark rounded-circle me-2">A</span>Kontaktní údaje</h2>
          @guest<span class="small">Máš účet? <a href="{{ route('login') }}">Přihlas se</a> a údaje doplníme za tebe.</span>@endguest
        </div>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label small" for="em">E-mail</label><input class="form-control @error('email') is-invalid @enderror" id="em" name="email" type="email" required value="{{ old('email', $user?->email) }}" placeholder="Pošleme ti sem i návody"></div>
          <div class="col-md-6"><label class="form-label small" for="tel">Telefon</label><input class="form-control @error('phone') is-invalid @enderror" id="tel" name="phone" required value="{{ old('phone', $user?->phone) }}" placeholder="+420"></div>
          <div class="col-md-6"><label class="form-label small" for="nm">Jméno a příjmení</label><input class="form-control @error('name') is-invalid @enderror" id="nm" name="name" required value="{{ old('name', $user?->name) }}"></div>
          @guest
            <div class="col-md-6 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" id="acc" name="create_account" value="1" @checked(old('create_account', true))><label class="form-check-label small" for="acc">{{ $car ? 'Uložit '.$car->short_name.' do garáže' : 'Založit účet' }} (založit účet)</label></div></div>
            <div class="col-md-6 offset-md-6" id="pw-wrap"><label class="form-label small" for="pw">Heslo k účtu</label><input class="form-control @error('password') is-invalid @enderror" id="pw" name="password" type="password" placeholder="Alespoň 8 znaků" autocomplete="new-password"></div>
          @endguest
        </div>
      </section>

      <section class="pp-card p-4">
        <h2 class="h5 mb-3"><span class="badge text-bg-dark rounded-circle me-2">B</span>Doprava</h2>
        <div class="vstack gap-2">
          @foreach ($shipping as $s)
            @php $price = ($s->free_over_threshold && $summary->freeShipping) ? 0 : $s->price; @endphp
            <label class="pp-box pp-radio p-3 d-flex align-items-center gap-3">
              <input class="form-check-input mt-0 ship" type="radio" name="shipping_method_id" value="{{ $s->id }}" data-price="{{ $price }}" data-address="{{ $s->needs_address ? 1 : 0 }}" data-pickup="{{ $s->needs_pickup_point ? 1 : 0 }}" data-name="{{ $s->name }}" @checked(old('shipping_method_id', $shipping->first()->id) == $s->id)>
              <i class="bi {{ $s->icon }} fs-4"></i>
              <span class="flex-fill"><b>{{ $s->name }}</b><br><span class="small text-secondary">{{ $s->description }}</span></span>
              <b>{{ $price ? czk($price) : 'zdarma' }}</b>
            </label>
          @endforeach
        </div>
        <div id="pickup-wrap" class="mt-3">
          <label class="form-label small" for="pp">Výdejní místo Zásilkovny</label>
          <input class="form-control @error('pickup_point') is-invalid @enderror" id="pp" name="pickup_point" list="pickup-list" value="{{ old('pickup_point') }}" placeholder="Začni psát město nebo ulici…">
          <datalist id="pickup-list">
            @foreach (['Praha 4, Budějovická 1', 'Praha 1, Národní 25', 'Brno, Masarykova 12', 'Ostrava, Nádražní 30', 'Pardubice, Třída Míru 60', 'Hradec Králové, Gočárova 5', 'Plzeň, Americká 40', 'Olomouc, Horní náměstí 3'] as $pt)<option value="{{ $pt }}">@endforeach
          </datalist>
          <div class="form-text">Ve skutečném e-shopu by tu byla mapa (widget Zásilkovny). Ve školním projektu vybíráš ze seznamu.</div>
        </div>
        <div id="address-wrap" class="row g-3 mt-1">
          <div class="col-md-6"><label class="form-label small" for="st">Ulice a číslo</label><input class="form-control @error('street') is-invalid @enderror" id="st" name="street" value="{{ old('street', $address?->street) }}"></div>
          <div class="col-md-4"><label class="form-label small" for="ct">Město</label><input class="form-control @error('city') is-invalid @enderror" id="ct" name="city" value="{{ old('city', $address?->city) }}"></div>
          <div class="col-md-2"><label class="form-label small" for="zp">PSČ</label><input class="form-control @error('zip') is-invalid @enderror" id="zp" name="zip" value="{{ old('zip', $address?->zip) }}"></div>
        </div>
      </section>

      <section class="pp-card p-4">
        <h2 class="h5 mb-3"><span class="badge text-bg-dark rounded-circle me-2">C</span>Platba</h2>
        <div class="row g-2">
          @foreach ($payments as $p)
            <div class="col-sm-4"><label class="pp-box pp-radio p-3 d-block text-center h-100">
              <input class="form-check-input pay" type="radio" name="payment_method_id" value="{{ $p->id }}" data-price="{{ $p->price }}" data-name="{{ $p->name }}" @checked(old('payment_method_id', $payments->first()->id) == $p->id)><br>
              <i class="bi {{ $p->icon }} fs-4"></i><br><b class="small">{{ $p->name }}{{ $p->price ? ' (+'.czk($p->price).')' : '' }}</b>
            </label></div>
          @endforeach
        </div>
        <label class="form-label small mt-3" for="note">Poznámka k objednávce <span class="text-secondary">(nepovinné)</span></label>
        <textarea class="form-control" id="note" name="note" rows="2">{{ old('note') }}</textarea>
      </section>
    </div>

    <div class="col-lg-4">
      <div class="pp-card p-4 position-sticky" style="top:1rem">
        <div class="d-flex align-items-center gap-2 mb-3"><i class="bi {{ $car ? 'bi-car-front-fill' : 'bi-cart3' }} fs-4"></i><div><b>{{ $car ? 'Plán pro '.$car->short_name : 'Tvůj košík' }}</b><div class="small text-secondary">{{ $summary->count }} {{ plural($summary->count, 'položka', 'položky', 'položek') }}@if ($summary->minutes) · zhruba {{ \App\Models\Product::minutes($summary->minutes) }}@endif</div></div></div>
        @foreach ($items as $i)
          <div class="d-flex justify-content-between small gap-2"><span>{{ $i->qty > 1 ? $i->qty.'× ' : '' }}{{ $i->type === 'bundle' ? 'Sada: ' : '' }}{{ $i->model->name }}</span><span class="text-nowrap">{{ czk($i->total) }}</span></div>
        @endforeach
        @if ($summary->discount)<div class="d-flex justify-content-between small text-success"><span>Sleva {{ $summary->coupon->code }}</span><span>−{{ czk($summary->discount) }}</span></div>@endif
        <div class="d-flex justify-content-between small"><span id="sum-ship-name">Doprava</span><span id="sum-ship" class="text-nowrap"></span></div>
        <div class="d-flex justify-content-between small"><span id="sum-pay-name">Platba</span><span id="sum-pay" class="text-nowrap"></span></div>
        <hr>
        <div class="d-flex justify-content-between fs-4 fw-bold"><span>Celkem</span><span id="sum-total" data-base="{{ $summary->total }}">{{ czk($summary->total) }}</span></div>
        <div class="small text-secondary">včetně DPH</div>
        <div class="form-check small my-3"><input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="vop" name="terms" value="1" required><label class="form-check-label" for="vop">Souhlasím s <a href="{{ route('page', 'obchodni-podminky') }}" target="_blank">obchodními podmínkami</a></label></div>
        <button class="btn btn-dark btn-lg w-100">Objednat s povinností platby</button>
        <div class="small text-secondary mt-2"><i class="bi bi-mortarboard"></i> Školní projekt – objednávka se nevyřizuje a nic nezaplatíš.</div>
        <div class="small text-secondary mt-2"><i class="bi bi-book"></i> Návody pro tvoje auto ti pošleme e-mailem hned po objednání.</div>
      </div>
    </div>
  </form>
</div>
@endsection
@push('scripts')
<script>
  const fmt = n => n ? n.toLocaleString('cs-CZ') + ' Kč' : 'zdarma';
  const totalEl = document.getElementById('sum-total');
  function refresh() {
    const s = document.querySelector('.ship:checked'), p = document.querySelector('.pay:checked');
    document.getElementById('pickup-wrap').classList.toggle('d-none', !(s && s.dataset.pickup === '1'));
    document.getElementById('address-wrap').classList.toggle('d-none', !(s && s.dataset.address === '1'));
    document.getElementById('sum-ship-name').textContent = s ? s.dataset.name : 'Doprava';
    document.getElementById('sum-ship').textContent = s ? fmt(+s.dataset.price) : '';
    document.getElementById('sum-pay-name').textContent = p ? p.dataset.name : 'Platba';
    document.getElementById('sum-pay').textContent = p ? fmt(+p.dataset.price) : '';
    totalEl.textContent = (+totalEl.dataset.base + (s ? +s.dataset.price : 0) + (p ? +p.dataset.price : 0)).toLocaleString('cs-CZ') + ' Kč';
  }
  document.querySelectorAll('.ship, .pay').forEach(el => el.addEventListener('change', refresh));
  const acc = document.getElementById('acc');
  if (acc) { const t = () => document.getElementById('pw-wrap').classList.toggle('d-none', !acc.checked); acc.addEventListener('change', t); t(); }
  refresh();
</script>
@endpush
