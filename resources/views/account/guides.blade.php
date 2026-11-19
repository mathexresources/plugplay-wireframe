@extends('account.layout')
@section('title', 'Moje návody')
@section('account')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h2 class="mb-0">Moje návody</h2>
  <form><select name="auto" class="form-select form-select-sm w-auto" aria-label="Auto" onchange="this.form.submit()"><option value="">Všechna auta</option>
    @foreach ($cars as $c)<option value="{{ $c->car_model_id }}" @selected(request('auto') == $c->car_model_id)>{{ $c->label }}</option>@endforeach</select></form>
</div>
<div class="row row-cols-1 row-cols-md-2 g-3">
  @forelse ($guides as $g)
    @php $pr = $progress->get($g->id); @endphp
    <div class="col"><a href="{{ route('guides.show', $g->slug) }}" class="pp-box p-3 d-flex gap-3 text-reset text-decoration-none h-100">
      <div class="pp-img sm flex-shrink-0" style="width:96px;height:72px;min-height:0"><i class="bi {{ $g->video_minutes ? 'bi-play' : 'bi-book' }}"></i></div>
      <div><b>{{ $g->title }}</b><div class="small text-secondary">{{ $g->step_count }} kroků{{ $g->video_minutes ? ' · video '.$g->video_minutes.' min' : '' }}</div>
        @if ($pr?->done)<span class="badge text-bg-success">Hotovo</span>@elseif ($pr)<span class="badge text-bg-light border">Krok {{ $pr->step }} z {{ $g->step_count }}</span>@else<span class="badge text-bg-light border">Nezačato</span>@endif</div>
    </a></div>
  @empty
    <div class="col-12"><div class="pp-box p-4 text-secondary">Návody se tu objeví ke všemu, co si koupíš. Zatím je najdeš v sekci <a href="{{ route('guides') }}">Návody</a>.</div></div>
  @endforelse
</div>
@endsection
