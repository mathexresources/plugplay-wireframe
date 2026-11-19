@extends('account.layout')
@section('title', 'Moje garáž')
@section('account')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Moje garáž</h2>
  <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addCar"><i class="bi bi-plus-lg"></i> Přidat auto</button>
</div>
<div class="vstack gap-4">
  @foreach ($cars as $car)
    @php $installed = $car->upgrades->where('installed', true)->count(); $pct = $car->total_upgrades ? round($installed / $car->total_upgrades * 100) : 0; @endphp
    <div class="pp-card p-0 overflow-hidden">
      <div class="row g-0">
        <div class="col-md-4"><div class="pp-img h-100 rounded-0 border-end border-2" style="min-height:180px">@if ($car->photo_url)<img src="{{ $car->photo_url }}" alt="{{ $car->label }}">@else<i class="bi bi-car-front"></i>@endif</div></div>
        <div class="col-md-8 p-4">
          <div class="d-flex flex-wrap justify-content-between gap-2">
            <div>
              @if ($car->is_active)<span class="badge text-bg-primary mb-1"><i class="bi bi-star-fill"></i> Aktivní auto</span>@endif
              <h3 class="h4 mb-0">{{ $car->label }} <span class="text-secondary fs-6">{{ $car->carModel->full_name }}{{ $car->year ? ' · '.$car->year : '' }}</span></h3>
              <div class="small text-secondary">
                @if ($car->engine || $car->radio || $car->body_type){{ collect([$car->body_type, $car->engine ? 'motor '.$car->engine : null, $car->radio ? 'rádio '.$car->radio : null])->filter()->implode(' · ') }}
                @else Výbava zatím není vyplněná. <a href="#" class="link-secondary" data-bs-toggle="modal" data-bs-target="#edit{{ $car->id }}">Doplň ji</a> a doporučíme ti přesněji.@endif
              </div>
            </div>
            <div class="dropdown align-self-start">
              <button class="btn btn-sm btn-outline-dark" data-bs-toggle="dropdown" aria-label="Možnosti"><i class="bi bi-three-dots"></i></button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit{{ $car->id }}">Upravit výbavu a fotku</button></li>
                <li><form method="post" action="{{ route('account.cars.delete', $car) }}">@csrf @method('delete')<button class="dropdown-item text-danger">Odebrat z garáže</button></form></li>
              </ul>
            </div>
          </div>
          <div class="my-3">
            <div class="d-flex justify-content-between small"><b>Vylepšeno</b><span>{{ $installed }} z {{ $car->total_upgrades }} upgradů</span></div>
            <div class="progress" style="height:8px" role="progressbar" aria-label="Vylepšeno"><div class="progress-bar bg-dark" style="width:{{ $pct }}%"></div></div>
          </div>
          @if ($car->upgrades->isNotEmpty())
            <div class="small fw-bold mb-1">Namontováno <span class="fw-normal text-secondary">(kliknutím přepneš stav)</span></div>
            <div class="d-flex flex-wrap gap-2 mb-3 small">
              @foreach ($car->upgrades as $up)
                <form method="post" action="{{ route('account.cars.upgrade', [$car, $up]) }}">@csrf
                  <button class="pp-chip {{ $up->installed ? 'on' : '' }}"><i class="bi {{ $up->installed ? 'bi-check-lg' : 'bi-hourglass-split' }}"></i> {{ $up->product->name }}{{ $up->installed ? '' : ' – čeká na montáž' }}</button></form>
              @endforeach
            </div>
          @endif
          <div class="d-flex flex-wrap gap-2">
            @if ($car->is_active)
              <a href="{{ route('car.show', $car->carModel->slug) }}" class="btn btn-dark btn-sm">Co dalšího vylepšit <i class="bi bi-arrow-right"></i></a>
            @else
              <form method="post" action="{{ route('account.cars.activate', $car) }}">@csrf<button class="btn btn-outline-dark btn-sm"><i class="bi bi-star"></i> Nastavit jako aktivní</button></form>
              <a href="{{ route('car.show', $car->carModel->slug) }}" class="btn btn-outline-dark btn-sm">Zobrazit upgrady</a>
            @endif
            <a href="{{ route('account.guides', ['auto' => $car->car_model_id]) }}" class="btn btn-outline-dark btn-sm">Návody k tomuto autu ({{ $guideCounts[$car->car_model_id] ?? 0 }})</a>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="edit{{ $car->id }}" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content pp-card" method="post" action="{{ route('account.cars.update', $car) }}" enctype="multipart/form-data">@csrf @method('put')
      <div class="modal-header"><h2 class="modal-title h5">Upravit {{ $car->label }}</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zavřít"></button></div>
      <div class="modal-body">@include('account.car-form', ['car' => $car])</div>
      <div class="modal-footer"><button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Zrušit</button><button class="btn btn-dark">Uložit</button></div>
    </form></div></div>
  @endforeach
  <button class="pp-box p-4 text-center w-100" data-bs-toggle="modal" data-bs-target="#addCar">
    <i class="bi bi-plus-circle fs-2"></i><div class="fw-bold">Přidat {{ $cars->isEmpty() ? 'první' : 'další' }} auto</div><div class="small text-secondary">služební, víkendové nebo rozpracovaný projekt</div>
  </button>
</div>

<div class="modal fade" id="addCar" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content pp-card" method="post" action="{{ route('account.cars.store') }}" enctype="multipart/form-data">@csrf
  <div class="modal-header"><h2 class="modal-title h5">Přidat auto do garáže</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zavřít"></button></div>
  <div class="modal-body">@include('account.car-form')
    <div class="form-check small mt-3"><input class="form-check-input" type="checkbox" id="mkact" name="make_active" value="1" checked><label class="form-check-label" for="mkact">Nastavit jako aktivní auto</label></div></div>
  <div class="modal-footer"><button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Zrušit</button><button class="btn btn-dark">Uložit do garáže</button></div>
</form></div></div>
@endsection
