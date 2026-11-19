@extends('account.layout')
@section('title', 'Objednávka #'.$order->number)
@section('account')
<a href="{{ route('account.orders') }}" class="small link-secondary"><i class="bi bi-arrow-left"></i> Všechny objednávky</a>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 my-2">
  <h2 class="mb-0">Objednávka #{{ $order->number }}</h2>
  <span class="badge fs-6 text-bg-{{ $order->status_color }}">{{ $order->status_label }}</span>
</div>
<p class="small text-secondary">{{ $order->created_at->format('j. n. Y H:i') }} · {{ $order->is_paid ? 'zaplaceno' : 'nezaplaceno' }}{{ $order->tracking_number ? ' · číslo zásilky '.$order->tracking_number : '' }}</p>
<div class="pp-card p-3 mb-3">
  @foreach ($order->items as $i)
    <div class="d-flex justify-content-between small py-1"><span>{{ $i->quantity }}× @if ($i->product)<a href="{{ route('product.show', $i->product->slug) }}">{{ $i->name }}</a>@else{{ $i->name }}@endif{{ $i->variant ? ' ('.$i->variant.')' : '' }}</span><span>{{ czk($i->price * $i->quantity) }}</span></div>
  @endforeach
  @if ($order->discount)<div class="d-flex justify-content-between small text-success"><span>Sleva {{ $order->coupon_code }}</span><span>−{{ czk($order->discount) }}</span></div>@endif
  <div class="d-flex justify-content-between small"><span>{{ $order->shipping_name }}</span><span>{{ czk($order->shipping_price) }}</span></div>
  <div class="d-flex justify-content-between small"><span>{{ $order->payment_name }}</span><span>{{ czk($order->payment_price) }}</span></div>
  <hr class="my-2"><div class="d-flex justify-content-between fw-bold"><span>Celkem</span><span>{{ czk($order->total) }}</span></div>
</div>
<div class="row g-3">
  <div class="col-md-6"><div class="pp-box p-3 h-100 small"><b>Doručení</b><br>{{ $order->name }}<br>{{ $order->pickup_point ?: trim($order->street.', '.$order->zip.' '.$order->city, ', ') ?: 'Osobní odběr' }}<br>{{ $order->email }} · {{ $order->phone }}</div></div>
  <div class="col-md-6"><div class="pp-box p-3 h-100 small"><b>Vrácení a reklamace</b><p class="mb-2">Nepasuje nebo nefunguje? Do 30 dnů od doručení ti zboží vyměníme nebo vrátíme peníze.</p>
    <a href="{{ route('contact', ['typ' => 'podpora', 'predmet' => 'Vrácení / reklamace – objednávka #'.$order->number]) }}" class="btn btn-sm btn-outline-dark">Vrátit nebo reklamovat</a>
    <a href="{{ route('checkout.done', ['order' => $order, 't' => $order->token]) }}" class="btn btn-sm btn-link">Návody k objednávce</a></div></div>
</div>
@endsection
