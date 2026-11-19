@extends('layouts.app')
@section('title', 'Návod: '.$guide->title)
@section('content')
@php $current = $progress?->step ?? 1; @endphp
<div class="container py-4" style="max-width:900px">
  <nav class="small mb-3"><a href="{{ route('guides') }}" class="link-secondary"><i class="bi bi-arrow-left"></i> Všechny návody</a></nav>
  <h1>{{ $guide->title }}</h1>
  <p class="text-secondary">{{ $guide->intro }}</p>
  <div class="d-flex flex-wrap gap-3 small mb-4">
    @if ($guide->product)<span><i class="bi bi-box"></i> <a href="{{ route('product.show', $guide->product->slug) }}">{{ $guide->product->name }}</a></span>
      <span><i class="bi bi-clock"></i> {{ $guide->product->install_time }}</span><span>@include('partials.diff', ['d' => $guide->product->difficulty])</span>@endif
    @if ($guide->video_url)<a href="{{ $guide->video_url }}" target="_blank" rel="noopener"><i class="bi bi-play-circle"></i> Video {{ $guide->video_minutes }} min</a>
    @elseif ($guide->video_minutes)<span><i class="bi bi-camera-video"></i> Video {{ $guide->video_minutes }} min (připravujeme)</span>@endif
  </div>
  @if ($guide->product && $guide->product->tools->isNotEmpty())
    <div class="pp-box p-3 mb-4"><b><i class="bi bi-tools"></i> Nářadí:</b> {{ $guide->product->tools->pluck('name')->implode(', ') }}</div>
  @endif
  @if ($progress)
    <div class="alert {{ $progress->done ? 'alert-success' : 'alert-primary' }} border-2">{{ $progress->done ? 'Tuhle montáž máš hotovou.' : 'Skončil jsi u kroku '.$progress->step.' z '.$guide->step_count.'.' }}</div>
  @endif
  <ol class="list-unstyled vstack gap-3">
    @foreach ($guide->steps as $i => $s)
      <li class="pp-card p-3 {{ $progress && ! $progress->done && $i + 1 === $current ? 'border-primary' : '' }}" id="krok-{{ $i + 1 }}">
        <div class="d-flex gap-3">
          <div class="pp-img sm flex-shrink-0" style="width:120px;height:80px;min-height:0"><span class="fs-2 fw-bold text-primary">{{ $i + 1 }}</span></div>
          <div class="flex-fill"><b>{{ $i + 1 }}. {{ $s['title'] }}</b><p class="small mb-2 mt-1">{{ $s['body'] }}</p>
            @auth
              <form method="post" action="{{ route('guides.progress', $guide) }}">@csrf
                @if ($loop->last)
                  <input type="hidden" name="step" value="{{ $i + 1 }}"><button name="done" value="1" class="btn btn-sm btn-dark"><i class="bi bi-check2-all"></i> Montáž hotová</button>
                @else
                  <input type="hidden" name="step" value="{{ $i + 2 }}"><button class="btn btn-sm btn-outline-dark">Hotovo, další krok</button>
                @endif
              </form>
            @endauth
          </div>
        </div>
      </li>
    @endforeach
  </ol>
  @guest<p class="small text-secondary"><a href="{{ route('login') }}">Přihlas se</a> a návod si zapamatuje, kde jsi skončil.</p>@endguest
</div>
@endsection
