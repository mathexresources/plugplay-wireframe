@extends('layouts.app')
@section('title', 'Návody na montáž')
@section('content')
<div class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <h1 class="mb-0">Návody{{ $car ? ' pro '.$car->short_name : '' }}</h1>
    <form class="d-flex gap-2">
      <select name="auto" class="form-select form-select-sm w-auto" aria-label="Auto" onchange="this.form.submit()">
        <option value="">Všechna auta</option>
        @foreach ($models as $m)<option value="{{ $m->id }}" @selected($car?->id === $m->id)>{{ $m->full_name }}</option>@endforeach
      </select>
      <div class="form-check form-switch small align-self-center"><input class="form-check-input" type="checkbox" name="video" value="1" id="vid" @checked(request('video')) onchange="this.form.submit()"><label class="form-check-label" for="vid">S videem</label></div>
    </form>
  </div>
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
    @forelse ($guides as $g)
      <div class="col"><a href="{{ route('guides.show', $g->slug) }}" class="pp-box p-3 d-flex gap-3 h-100 text-reset text-decoration-none">
        <div class="pp-img sm flex-shrink-0" style="width:96px;height:72px;min-height:0"><i class="bi {{ $g->video_minutes ? 'bi-play-circle' : 'bi-book' }}"></i></div>
        <div><b>{{ $g->title }}</b><div class="small text-secondary">{{ $g->step_count }} kroků{{ $g->video_minutes ? ' · video '.$g->video_minutes.' min' : '' }}</div>
          <div class="small text-secondary">{{ $g->carModel ? $g->carModel->full_name : 'Obecný návod' }}</div></div>
      </a></div>
    @empty
      <div class="col-12 text-secondary">Pro tohle auto zatím návod nemáme.</div>
    @endforelse
  </div>
</div>
@endsection
