@extends('layouts.app')
@section('title', 'Objednávka přijata')
@section('content')
@php $statusOrder = ['new', 'packing', 'shipped', 'delivered']; $cur = array_search($order->status, $statusOrder); @endphp
<div class="container py-5" style="max-width:900px">
  <div class="text-center mb-5">
    <i class="bi bi-check-circle display-3 text-success"></i>
    <h1 class="mt-2">Díky! Objednávku jsme přijali.</h1>
    <p class="text-secondary">Číslo objednávky <b>#{{ $order->number }}</b>. Potvrzení jsme poslali na {{ $order->email }}.</p>
  </div>

  <div class="pp-card p-4 mb-4">
    @if ($order->status === 'cancelled')
      <div class="text-center text-danger fw-bold"><i class="bi bi-x-circle"></i> Objednávka byla zrušena.</div>
    @else
      <div class="row text-center small g-0">
        @foreach ([['Přijata', 'bi-check-circle-fill'], ['Balíme', 'bi-box-seam'], ['Na cestě', 'bi-truck'], ['Montáž', 'bi-tools']] as $idx => [$l, $ic])
          <div class="col {{ $idx <= $cur ? '' : 'text-secondary' }}"><i class="bi {{ $ic }} fs-4 {{ $idx <= $cur ? 'text-primary' : '' }}"></i><div class="{{ $idx === $cur ? 'fw-bold' : '' }}">{{ $l }}</div></div>
        @endforeach
      </div>
    @endif
  </div>

  @if ($order->payment_code === 'transfer' && ! $order->is_paid)
    <div class="pp-box p-4 mb-4">
      <div class="row g-4 align-items-center">
        <div class="col-md-8">
          <h2 class="h5"><i class="bi bi-qr-code"></i> Platba převodem</h2>
          <dl class="row small mb-0">
            <dt class="col-5">Číslo účtu</dt><dd class="col-7">{{ $bank }}</dd>
            <dt class="col-5">Variabilní symbol</dt><dd class="col-7">{{ preg_replace('/\D/', '', $order->number) }}</dd>
            <dt class="col-5">Částka</dt><dd class="col-7"><b>{{ czk($order->total) }}</b></dd>
          </dl>
          <div class="small text-secondary mt-2">Školní projekt – nic neplať, účet je smyšlený.</div>
        </div>
        <div class="col-md-4 text-center"><div id="qr" class="d-inline-block p-2 bg-white border border-2 border-dark rounded"></div></div>
      </div>
    </div>
    @push('scripts')
    <script src="{{ asset('vendor/qrcode/qrcode.min.js') }}"></script>
    <script>new QRCode(document.getElementById('qr'), { text: @json('SPD*1.0*ACC:'.$iban.'*AM:'.$order->total.'.00*CC:CZK*X-VS:'.preg_replace('/\D/', '', $order->number).'*MSG:PLUGPLAY '.$order->number), width: 150, height: 150 });</script>
    @endpush
  @elseif ($order->payment_code === 'cod')
    <div class="pp-box p-3 mb-4"><i class="bi bi-cash-coin"></i> Zaplatíš při převzetí: <b>{{ czk($order->total) }}</b>.</div>
  @elseif ($order->payment_code === 'card' && ! $order->is_paid)
    <div class="pp-box p-3 mb-4"><i class="bi bi-credit-card"></i> Platba kartou zatím neproběhla. <a href="{{ route('checkout.gateway', ['order' => $order, 't' => $order->token]) }}">Zaplatit kartou</a></div>
  @endif

  @if (! $order->user_id && ! auth()->check())
    <form method="post" action="{{ route('checkout.account', ['order' => $order, 't' => $order->token]) }}" class="pp-box p-3 mb-4 d-flex flex-wrap align-items-center gap-3">@csrf
      <i class="bi bi-car-front fs-3"></i>
      <div class="flex-fill"><b>Ulož si {{ $order->car_label ?: 'auto' }} do garáže</b><div class="small text-secondary">Stačí zvolit heslo. Objednávka, návody i auto se uloží k tvému účtu.</div></div>
      <input type="password" name="password" class="form-control form-control-sm" style="max-width:200px" placeholder="Heslo (min. 8 znaků)" aria-label="Heslo" required minlength="8">
      <button class="btn btn-sm btn-dark">Založit účet</button>
    </form>
  @endif

  <div class="pp-card p-3 mb-4">
    <h2 class="h6 fw-bold">Shrnutí objednávky</h2>
    @foreach ($order->items as $i)
      <div class="d-flex justify-content-between small"><span>{{ $i->quantity }}× {{ $i->name }}{{ $i->variant ? ' ('.$i->variant.')' : '' }}</span><span>{{ czk($i->price * $i->quantity) }}</span></div>
    @endforeach
    @if ($order->discount)<div class="d-flex justify-content-between small text-success"><span>Sleva {{ $order->coupon_code }}</span><span>−{{ czk($order->discount) }}</span></div>@endif
    <div class="d-flex justify-content-between small"><span>{{ $order->shipping_name }}{{ $order->pickup_point ? ' – '.$order->pickup_point : '' }}</span><span>{{ czk($order->shipping_price) }}</span></div>
    <div class="d-flex justify-content-between small"><span>{{ $order->payment_name }}</span><span>{{ czk($order->payment_price) }}</span></div>
    <hr class="my-2"><div class="d-flex justify-content-between fw-bold"><span>Celkem</span><span>{{ czk($order->total) }}</span></div>
  </div>

  @if ($guides->isNotEmpty())
    <h2 class="h4">Než balík dorazí, připrav se</h2>
    <div class="row g-3 mb-4">
      @foreach ($guides as $g)
        <div class="col-md-6"><div class="pp-box p-3 d-flex gap-3 h-100"><div class="pp-img sm flex-shrink-0" style="width:96px;height:72px;min-height:0"><i class="bi bi-play"></i></div>
          <div><b>Návod: {{ $g->title }}</b><div class="small text-secondary">{{ $g->step_count }} kroků{{ $g->video_minutes ? ' · video '.$g->video_minutes.' min' : '' }}</div><a href="{{ route('guides.show', $g->slug) }}" class="small">Otevřít návod →</a></div></div></div>
      @endforeach
    </div>
  @endif

  @if ($tools->isNotEmpty())
    <div class="pp-box p-3 mb-4"><b><i class="bi bi-tools"></i> Připrav si:</b>
      {{ $tools->filter(fn ($t) => ! in_array($t->product_id, $boughtToolProducts))->pluck('name')->implode(', ') ?: 'nic dalšího – všechno je v balíku' }}.
      @if ($tools->filter(fn ($t) => in_array($t->product_id, $boughtToolProducts))->isNotEmpty())<span class="text-secondary">{{ $tools->filter(fn ($t) => in_array($t->product_id, $boughtToolProducts))->pluck('name')->implode(', ') }} najdeš v balíku.</span>@endif
    </div>
  @endif

  <div class="row g-3">
    <div class="col-md-6"><div class="pp-card p-3 h-100"><b><i class="bi bi-chat-dots"></i> Nevíš si rady s montáží?</b><p class="small text-secondary mb-2">Pošli nám fotku a poradíme. Odpovídáme do 24 hodin.</p><a href="{{ route('contact', ['typ' => 'podpora', 'predmet' => 'Objednávka #'.$order->number]) }}" class="btn btn-sm btn-outline-dark">Napsat podpoře</a></div></div>
    <div class="col-md-6"><div class="pp-card p-3 h-100"><b><i class="bi bi-camera"></i> Pochlub se výsledkem</b><p class="small text-secondary mb-2">Za fotky před a po montáži dostaneš slevu 10 % na další nákup.</p><a href="{{ route('showcases') }}#nahrat" class="btn btn-sm btn-outline-dark">Nahrát fotky</a></div></div>
  </div>

  @if ($order->carModel)<div class="text-center mt-5"><a href="{{ route('car.show', $order->carModel->slug) }}" class="btn btn-dark">Co dalšího vylepšit na {{ $order->carModel->short_name }}</a></div>@endif
</div>
@endsection
