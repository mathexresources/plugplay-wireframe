@extends('layouts.app')
@section('title', 'Sdílený plán: '.$plan->name)
@section('content')
<div class="container py-4" style="max-width:800px">
  <p class="text-uppercase small fw-bold text-secondary mb-1">Sdílený plán úprav</p>
  <h1>{{ $plan->name }}</h1>
  @if ($plan->carModel)<p class="text-secondary"><i class="bi bi-car-front"></i> {{ $plan->carModel->full_name }}</p>@endif
  <div class="vstack gap-2 my-3">
    @forelse ($lines as $l)
      <div class="pp-box p-3 d-flex justify-content-between"><span>{{ $l->line['qty'] }}× {{ $l->line['type'] === 'bundle' ? 'Sada: ' : '' }}{{ $l->model->name }}{{ $l->line['variant'] ? ' ('.$l->line['variant'].')' : '' }}</span><b>{{ czk($l->model->price * $l->line['qty']) }}</b></div>
    @empty
      <p class="text-secondary">Produkty z plánu už nejsou v nabídce.</p>
    @endforelse
  </div>
  @if ($lines->isNotEmpty())
    <form method="post" action="{{ route('cart.loadShared', $plan->token) }}">@csrf <button class="btn btn-dark btn-lg">Nahrát do mého plánu</button></form>
  @endif
</div>
@endsection
