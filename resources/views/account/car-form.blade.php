{{-- $car (optional), $models --}}
@php $car = $car ?? null; @endphp
<div class="row g-2 mb-3">
  <div class="col-sm-8"><label class="form-label small">Model</label>
    <select class="form-select" name="car_model_id" required>
      @foreach ($models as $m)<option value="{{ $m->id }}" @selected($car?->car_model_id === $m->id)>{{ $m->full_name }} ({{ $m->years }})</option>@endforeach
    </select></div>
  <div class="col-sm-4"><label class="form-label small">Rok výroby</label><input type="number" class="form-control" name="year" min="1950" max="{{ date('Y') + 1 }}" value="{{ $car?->year }}"></div>
</div>
<div class="row g-2 mb-3">
  <div class="col-sm-6"><label class="form-label small">Přezdívka <span class="text-secondary">(nepovinné)</span></label><input class="form-control" name="nickname" placeholder="např. Golfík" value="{{ $car?->nickname }}"></div>
  <div class="col-sm-6"><label class="form-label small">Karoserie</label><input class="form-control" name="body_type" value="{{ $car?->body_type }}" placeholder="např. Touring"></div>
</div>
<details class="pp-box p-2 small" {{ $car ? 'open' : '' }}>
  <summary class="fw-bold">Výbava – pro přesnější doporučení</summary>
  <div class="row g-2 mt-1">
    <div class="col-6"><label class="form-label small">Rádio</label><input class="form-control form-control-sm" name="radio" value="{{ $car?->radio }}" placeholder="např. Business CD"></div>
    <div class="col-6"><label class="form-label small">Motor</label><input class="form-control form-control-sm" name="engine" value="{{ $car?->engine }}" placeholder="např. 2.0 diesel"></div>
    <div class="col-12"><label class="form-label small">VIN <span class="text-secondary">(17 znaků, nepovinné)</span></label><input class="form-control form-control-sm" name="vin" maxlength="17" value="{{ $car?->vin }}"></div>
    <div class="col-12"><label class="form-label small">Fotka auta</label><input class="form-control form-control-sm" type="file" name="photo" accept="image/*"></div>
  </div>
</details>
