@extends('admin.layout')
@section('title', 'Objednávky')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h3 mb-0">Objednávky <span class="text-secondary fs-6">{{ $orders->total() }}</span></h1>
  <a href="{{ route('admin.orders.export', request()->query()) }}" class="btn btn-outline-dark"><i class="bi bi-filetype-csv"></i> Export CSV (pro účetnictví)</a>
</div>
<form class="d-flex flex-wrap gap-2 mb-3">
  <input class="form-control form-control-sm" style="max-width:240px" name="q" value="{{ request('q') }}" placeholder="Číslo, jméno, e-mail…">
  <select name="stav" class="form-select form-select-sm w-auto"><option value="">Všechny stavy</option>@foreach (\App\Models\Order::STATUSES as $k => [$l])<option value="{{ $k }}" @selected(request('stav') === $k)>{{ $l }}</option>@endforeach</select>
  <select name="platba" class="form-select form-select-sm w-auto"><option value="">Zaplaceno i ne</option><option value="nezaplaceno" @selected(request('platba') === 'nezaplaceno')>Nezaplacené</option><option value="zaplaceno" @selected(request('platba') === 'zaplaceno')>Zaplacené</option></select>
  <input type="date" name="od" value="{{ request('od') }}" class="form-control form-control-sm w-auto" aria-label="Od"><input type="date" name="do" value="{{ request('do') }}" class="form-control form-control-sm w-auto" aria-label="Do">
  <button class="btn btn-sm btn-outline-dark">Filtrovat</button>
</form>
<div class="pp-card table-responsive">
  <table class="table align-middle mb-0">
    <thead><tr><th>Číslo</th><th>Datum</th><th>Zákazník</th><th>Auto</th><th>Doprava / platba</th><th>Stav</th><th class="text-end">Celkem</th></tr></thead>
    <tbody>
      @forelse ($orders as $o)
        <tr onclick="location='{{ route('admin.orders.show', $o) }}'" style="cursor:pointer">
          <td><a href="{{ route('admin.orders.show', $o) }}"><b>#{{ $o->number }}</b></a></td><td class="small">{{ $o->created_at->format('j. n. Y H:i') }}</td>
          <td>{{ $o->name }}<div class="small text-secondary">{{ $o->email }}</div></td><td class="small">{{ $o->car_label ?: '–' }}</td>
          <td class="small">{{ $o->shipping_name }}<br>{{ $o->payment_name }}</td>
          <td><span class="badge text-bg-{{ $o->status_color }}">{{ $o->status_label }}</span><br><span class="badge {{ $o->is_paid ? 'text-bg-success' : 'text-bg-light border' }}">{{ $o->is_paid ? 'zaplaceno' : 'nezaplaceno' }}</span></td>
          <td class="text-end fw-bold">{{ czk($o->total) }}</td>
        </tr>
      @empty <tr><td colspan="7" class="text-center text-secondary py-4">Žádné objednávky.</td></tr> @endforelse
    </tbody>
  </table>
</div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
