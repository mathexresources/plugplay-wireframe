@extends('admin.layout')
@section('title', 'Přehled')
@section('content')
<h1 class="h3 mb-4">Přehled</h1>
<div class="row g-3 mb-4">
  @foreach ([['Tržby za 30 dní', czk($stats['revenue30']), $stats['orders30'].' objednávek'], ['Průměrná objednávka', czk($stats['avg']), 'za 30 dní'], ['Nové objednávky', $stats['newOrders'], $stats['unpaid'].' nezaplacených'], ['Zákazníci', $stats['customers'], $stats['products'].' aktivních produktů']] as [$l, $v, $sub])
    <div class="col-6 col-xl-3"><div class="pp-stat p-3 h-100"><div class="small text-secondary">{{ $l }}</div><div class="fs-3 fw-bold">{{ $v }}</div><div class="small text-secondary">{{ $sub }}</div></div></div>
  @endforeach
</div>

@if (array_sum($pending))
  <div class="alert alert-warning border-2 d-flex flex-wrap gap-3">
    <b><i class="bi bi-bell"></i> Čeká na tebe:</b>
    @if ($pending['reviews'])<a href="{{ route('admin.reviews.index') }}">{{ $pending['reviews'] }} recenzí ke schválení</a>@endif
    @if ($pending['showcases'])<a href="{{ route('admin.showcases.index') }}">{{ $pending['showcases'] }} proměn ke schválení</a>@endif
    @if ($pending['messages'])<a href="{{ route('admin.messages.index') }}">{{ $pending['messages'] }} nepřečtených zpráv</a>@endif
  </div>
@endif

<div class="row g-4 mb-4">
  <div class="col-xl-8">
    <div class="pp-card p-3 h-100">
      <h2 class="h6 fw-bold">Tržby po dnech – posledních 30 dní</h2>
      @php $max = max(1, $days->max('revenue')); @endphp
      <div class="d-flex align-items-end gap-1 mt-3" style="height:180px;border-bottom:1px solid #d0d5dd" role="img" aria-label="Sloupcový graf denních tržeb">
        @foreach ($days as $d)
          <div class="flex-fill h-100 d-flex align-items-end" data-bs-toggle="tooltip" data-bs-title="{{ $d['date']->format('j. n.') }}: {{ czk($d['revenue']) }} ({{ $d['count'] }} obj.)">
            <div class="w-100" style="height:{{ $d['revenue'] ? max(3, round($d['revenue'] / $max * 100)) : 0 }}%;background:#1f6feb;border-radius:4px 4px 0 0;min-width:4px"></div>
          </div>
        @endforeach
      </div>
      <div class="d-flex justify-content-between small text-secondary mt-1"><span>{{ $days->first()['date']->format('j. n.') }}</span><span>max {{ czk($max) }}/den</span><span>dnes</span></div>
      <div class="small text-secondary mt-2">Celkové tržby od spuštění: <b>{{ czk($stats['revenueAll']) }}</b> (bez zrušených objednávek).</div>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="pp-card p-3 h-100">
      <h2 class="h6 fw-bold">Objednávky podle stavu</h2>
      @foreach (\App\Models\Order::STATUSES as $k => [$l, $color])
        <a href="{{ route('admin.orders.index', ['stav' => $k]) }}" class="d-flex justify-content-between small py-1 text-reset text-decoration-none"><span><span class="badge text-bg-{{ $color }}">{{ $l }}</span></span><b>{{ $byStatus[$k] ?? 0 }}</b></a>
      @endforeach
      <h2 class="h6 fw-bold mt-3">Nejčastější auta v objednávkách</h2>
      @foreach ($byCar as $car => $cnt)<div class="d-flex justify-content-between small py-1"><span>{{ $car }}</span><b>{{ $cnt }}</b></div>@endforeach
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-6">
    <div class="pp-card p-3 h-100">
      <div class="d-flex justify-content-between"><h2 class="h6 fw-bold">Poslední objednávky</h2><a href="{{ route('admin.orders.index') }}" class="small">Všechny →</a></div>
      <table class="table table-sm small mb-0"><tbody>
        @forelse ($latest as $o)
          <tr><td><a href="{{ route('admin.orders.show', $o) }}">#{{ $o->number }}</a></td><td>{{ $o->name }}</td><td>{{ $o->created_at->format('j. n. H:i') }}</td><td><span class="badge text-bg-{{ $o->status_color }}">{{ $o->status_label }}</span></td><td class="text-end">{{ czk($o->total) }}</td></tr>
        @empty <tr><td class="text-secondary">Zatím žádné objednávky.</td></tr> @endforelse
      </tbody></table>
    </div>
  </div>
  <div class="col-xl-3">
    <div class="pp-card p-3 h-100">
      <h2 class="h6 fw-bold">Nejprodávanější</h2>
      @foreach ($top as $t)<div class="small py-1 d-flex justify-content-between gap-2"><span>{{ $t->name }}</span><b class="text-nowrap">{{ $t->qty }} ks</b></div>@endforeach
    </div>
  </div>
  <div class="col-xl-3">
    <div class="pp-card p-3 h-100">
      <h2 class="h6 fw-bold">Dochází zásoby</h2>
      @forelse ($lowStock as $p)<a href="{{ route('admin.products.edit', $p) }}" class="small py-1 d-flex justify-content-between gap-2 text-reset text-decoration-none"><span>{{ $p->name }}</span><b class="text-nowrap {{ $p->stock ? 'text-warning-emphasis' : 'text-danger' }}">{{ $p->stock }} ks</b></a>
      @empty<div class="small text-secondary">Všeho je dost.</div>@endforelse
    </div>
  </div>
</div>
@endsection
@push('scripts')<script>document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));</script>@endpush
