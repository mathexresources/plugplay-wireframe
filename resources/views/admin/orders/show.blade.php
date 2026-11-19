@extends('admin.layout')
@section('title', 'Objednávka #'.$order->number)
@section('content')
<a href="{{ route('admin.orders.index') }}" class="small link-secondary"><i class="bi bi-arrow-left"></i> Objednávky</a>
<div class="d-flex flex-wrap align-items-center gap-2 my-2">
  <h1 class="h3 mb-0">Objednávka #{{ $order->number }}</h1>
  <span class="badge fs-6 text-bg-{{ $order->status_color }}">{{ $order->status_label }}</span>
  <span class="badge fs-6 {{ $order->is_paid ? 'text-bg-success' : 'text-bg-light border' }}">{{ $order->is_paid ? 'zaplaceno' : 'nezaplaceno' }}</span>
  <button class="btn btn-sm btn-outline-dark ms-auto" onclick="window.print()"><i class="bi bi-printer"></i> Tisk / dodací list</button>
</div>
<div class="small text-secondary mb-3">Vytvořena {{ $order->created_at->format('j. n. Y H:i') }}</div>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="pp-card p-3 mb-4">
      <table class="table table-sm mb-0">
        <thead><tr><th>Položka</th><th class="text-end">Cena</th><th class="text-end">Ks</th><th class="text-end">Celkem</th></tr></thead>
        <tbody>
          @foreach ($order->items as $i)
            <tr><td>@if ($i->product)<a href="{{ route('admin.products.edit', $i->product) }}">{{ $i->name }}</a> <span class="small text-secondary">{{ $i->product->sku }}</span>@else{{ $i->name }}@endif{{ $i->variant ? ' – '.$i->variant : '' }}</td>
              <td class="text-end">{{ czk($i->price) }}</td><td class="text-end">{{ $i->quantity }}</td><td class="text-end">{{ czk($i->price * $i->quantity) }}</td></tr>
          @endforeach
          <tr><td colspan="3">Mezisoučet</td><td class="text-end">{{ czk($order->subtotal) }}</td></tr>
          @if ($order->discount)<tr class="text-success"><td colspan="3">Sleva {{ $order->coupon_code }}</td><td class="text-end">−{{ czk($order->discount) }}</td></tr>@endif
          <tr><td colspan="3">{{ $order->shipping_name }}</td><td class="text-end">{{ czk($order->shipping_price) }}</td></tr>
          <tr><td colspan="3">{{ $order->payment_name }}</td><td class="text-end">{{ czk($order->payment_price) }}</td></tr>
          <tr class="fw-bold"><td colspan="3">Celkem</td><td class="text-end">{{ czk($order->total) }}</td></tr>
        </tbody>
      </table>
    </div>
    <div class="row g-3">
      <div class="col-md-6"><div class="pp-box p-3 small h-100"><b>Zákazník</b><br>{{ $order->name }}<br>{{ $order->email }}<br>{{ $order->phone }}
        @if ($order->user)<br><a href="{{ route('admin.customers.show', $order->user) }}">Účet zákazníka →</a>@else<br><span class="text-secondary">nákup bez registrace</span>@endif</div></div>
      <div class="col-md-6"><div class="pp-box p-3 small h-100"><b>Doručení</b><br>{{ $order->shipping_name }}<br>
        {{ $order->pickup_point ?: (trim($order->street.', '.$order->zip.' '.$order->city, ', ') ?: '–') }}
        <br><b>Auto:</b> {{ $order->car_label ?: '–' }}</div></div>
      @if ($order->note)<div class="col-12"><div class="pp-box p-3 small"><b>Poznámka zákazníka:</b> {{ $order->note }}</div></div>@endif
    </div>
  </div>
  <div class="col-lg-4">
    <form method="post" action="{{ route('admin.orders.update', $order) }}" class="pp-card p-3">@csrf @method('put')
      <h2 class="h6 fw-bold">Zpracování</h2>
      <label class="form-label small" for="st">Stav</label>
      <select id="st" name="status" class="form-select mb-2">@foreach (\App\Models\Order::STATUSES as $k => [$l])<option value="{{ $k }}" @selected($order->status === $k)>{{ $l }}</option>@endforeach</select>
      <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="is_paid" value="1" id="paid" @checked($order->is_paid)><label class="form-check-label" for="paid">Zaplaceno</label></div>
      <label class="form-label small" for="tr">Číslo zásilky</label><input id="tr" name="tracking_number" class="form-control mb-2" value="{{ $order->tracking_number }}">
      <label class="form-label small" for="an">Interní poznámka</label><textarea id="an" name="admin_note" class="form-control mb-2" rows="3">{{ $order->admin_note }}</textarea>
      <div class="form-check small mb-3"><input class="form-check-input" type="checkbox" name="notify" value="1" id="nt" checked><label class="form-check-label" for="nt">Poslat zákazníkovi e-mail o změně stavu</label></div>
      <button class="btn btn-dark w-100">Uložit</button>
    </form>
    <form method="post" action="{{ route('admin.orders.destroy', $order) }}" class="mt-3 text-end" data-confirm="Opravdu smazat objednávku?">@csrf @method('delete')<button class="btn btn-sm btn-link text-danger">Smazat objednávku</button></form>
  </div>
</div>
@endsection
