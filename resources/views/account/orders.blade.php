@extends('account.layout')
@section('title', 'Objednávky')
@section('account')
<h2 class="mb-3">Objednávky</h2>
@if ($orders->isEmpty())
  <div class="pp-box p-4 text-center text-secondary">Zatím nemáš žádnou objednávku. <a href="{{ route('catalog') }}">Prohlédni si katalog</a>.</div>
@else
  <div class="table-responsive pp-card">
    <table class="table align-middle mb-0">
      <thead><tr><th>Číslo</th><th>Datum</th><th>Auto</th><th>Stav</th><th class="text-end">Cena</th><th></th></tr></thead>
      <tbody>
        @foreach ($orders as $o)
          <tr><td><b>#{{ $o->number }}</b></td><td>{{ $o->created_at->format('j. n. Y') }}</td><td>{{ $o->car_label ?: '–' }}</td>
            <td><span class="badge text-bg-{{ $o->status_color }}">{{ $o->status_label }}</span>@if (! $o->is_paid && $o->status !== 'cancelled') <span class="badge text-bg-light border">nezaplaceno</span>@endif</td>
            <td class="text-end">{{ czk($o->total) }}</td><td class="text-end"><a href="{{ route('account.order', $o) }}" class="btn btn-sm btn-outline-dark">Detail</a></td></tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="small text-secondary mt-2"><i class="bi bi-arrow-repeat"></i> Vrácení zboží a reklamaci vyřídíš přímo v detailu objednávky.</div>
@endif
@endsection
