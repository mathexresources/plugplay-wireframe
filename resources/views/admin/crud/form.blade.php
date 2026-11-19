@extends('admin.layout')
@section('title', ($item ? 'Upravit' : 'Nový').' '.$c->singular)
@section('content')
<a href="{{ route($c->route.'.index') }}" class="small link-secondary"><i class="bi bi-arrow-left"></i> {{ $c->title }}</a>
<h1 class="h3 my-2">{{ $item ? 'Upravit: '.($item->name ?? $item->title ?? $item->code ?? '') : 'Nový '.$c->singular }}</h1>
@if ($item && $item instanceof \App\Models\Product)<a href="{{ route('product.show', $item->slug) }}" target="_blank" class="small"><i class="bi bi-box-arrow-up-right"></i> Zobrazit v e-shopu</a>@endif
<form method="post" enctype="multipart/form-data" action="{{ $item ? route($c->route.'.update', $item->getKey()) : route($c->route.'.store') }}" class="pp-card p-4 mt-3">@csrf @if ($item) @method('put') @endif
  <div class="row g-3">
    @foreach ($fields as $f)
      @php $type = $f['type'] ?? 'text'; $name = $f['name']; $val = old($name, $f['value'] ?? ($item ? $item->$name : ($f['default'] ?? null))); @endphp
      <div class="col-md-{{ $f['col'] ?? 12 }}">
        @if ($type === 'checkbox')
          <div class="form-check form-switch mt-md-4"><input class="form-check-input" type="checkbox" name="{{ $name }}" id="f-{{ $name }}" value="1" @checked($val)><label class="form-check-label" for="f-{{ $name }}">{{ $f['label'] }}</label></div>
        @else
          <label class="form-label small fw-bold" for="f-{{ $name }}">{{ $f['label'] }}</label>
          @switch($type)
            @case('textarea')<textarea class="form-control @error($name) is-invalid @enderror" name="{{ $name }}" id="f-{{ $name }}" rows="4">{{ $val }}</textarea>@break
            @case('html')<textarea class="form-control font-monospace small @error($name) is-invalid @enderror" name="{{ $name }}" id="f-{{ $name }}" rows="12">{{ $val }}</textarea>@break
            @case('number')<input type="number" class="form-control @error($name) is-invalid @enderror" name="{{ $name }}" id="f-{{ $name }}" value="{{ $val }}">@break
            @case('date')<input type="date" class="form-control @error($name) is-invalid @enderror" name="{{ $name }}" id="f-{{ $name }}" value="{{ $val instanceof \Carbon\Carbon ? $val->format('Y-m-d') : $val }}">@break
            @case('select')
              <select class="form-select @error($name) is-invalid @enderror" name="{{ $name }}" id="f-{{ $name }}">@foreach ($f['options'] as $k => $l)<option value="{{ $k }}" @selected((string) $val === (string) $k)>{{ $l }}</option>@endforeach</select>@break
            @case('multiselect')
              <div class="border rounded p-2 bg-white" style="max-height:220px;overflow:auto">
                @foreach ($f['options'] as $k => $l)<div class="form-check small"><input class="form-check-input" type="checkbox" name="{{ $name }}[]" value="{{ $k }}" id="f-{{ $name }}-{{ $k }}" @checked(in_array($k, (array) $val))><label class="form-check-label" for="f-{{ $name }}-{{ $k }}">{{ $l }}</label></div>@endforeach
              </div>@break
            @case('image')
              @if ($item && $item->$name)
                <div class="d-flex align-items-center gap-2 mb-2"><img src="{{ \Illuminate\Support\Facades\Storage::url($item->$name) }}" alt="" style="height:80px" class="rounded border">
                  <div class="form-check small"><input class="form-check-input" type="checkbox" name="{{ $name }}_remove" value="1" id="f-{{ $name }}-rm"><label class="form-check-label" for="f-{{ $name }}-rm">Odstranit</label></div></div>
              @endif
              <input type="file" accept="image/*" class="form-control @error($name) is-invalid @enderror" name="{{ $name }}" id="f-{{ $name }}">@break
            @case('steps')
              @php $steps = old($name, $item?->$name ?? [['title' => '', 'body' => '']]); @endphp
              <div id="steps" class="vstack gap-2">
                @foreach ($steps as $i => $s)
                  <div class="border rounded p-2 bg-white step-row"><div class="d-flex gap-2 mb-1"><span class="badge text-bg-dark align-self-center step-num">{{ $i + 1 }}</span>
                    <input class="form-control form-control-sm" name="steps[{{ $i }}][title]" value="{{ $s['title'] }}" placeholder="Název kroku" required>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.step-row').remove();renum()" aria-label="Odebrat krok"><i class="bi bi-x"></i></button></div>
                    <textarea class="form-control form-control-sm" name="steps[{{ $i }}][body]" rows="2" placeholder="Popis">{{ $s['body'] }}</textarea></div>
                @endforeach
              </div>
              <button type="button" class="btn btn-sm btn-outline-dark mt-2" onclick="addStep()"><i class="bi bi-plus"></i> Přidat krok</button>
              @push('scripts')<script>
                function renum() { document.querySelectorAll('#steps .step-row').forEach((r, i) => { r.querySelector('.step-num').textContent = i + 1; r.querySelectorAll('[name]').forEach(el => el.name = el.name.replace(/steps\[\d+\]/, `steps[${i}]`)); }); }
                function addStep() { const t = document.querySelector('#steps .step-row').cloneNode(true); t.querySelectorAll('input,textarea').forEach(el => el.value = ''); document.getElementById('steps').appendChild(t); renum(); }
              </script>@endpush
              @break
            @default<input type="text" class="form-control @error($name) is-invalid @enderror" name="{{ $name }}" id="f-{{ $name }}" value="{{ $val }}">
          @endswitch
          @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          @if (! empty($f['help']))<div class="form-text">{{ $f['help'] }}</div>@endif
        @endif
      </div>
    @endforeach
  </div>
  <div class="d-flex gap-2 mt-4"><button class="btn btn-dark">Uložit</button><a href="{{ route($c->route.'.index') }}" class="btn btn-outline-dark">Zpět</a></div>
</form>
@endsection
