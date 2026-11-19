@extends('account.layout')
@section('title', 'Uložené plány')
@section('account')
<h2 class="mb-3">Uložené plány</h2>
<div class="vstack gap-3">
  @forelse ($plans as $plan)
    @php $count = collect($plan->items)->sum('qty'); @endphp
    <div class="pp-card p-3 d-flex flex-wrap align-items-center gap-3">
      <i class="bi bi-car-front fs-3"></i>
      <div class="flex-fill"><b>{{ $plan->name }}</b><div class="small text-secondary">{{ $plan->carModel?->full_name ?? 'Bez auta' }} · {{ $count }} {{ plural($count, 'položka', 'položky', 'položek') }} · uloženo {{ $plan->created_at->format('j. n. Y') }}</div></div>
      <form method="post" action="{{ route('account.plans.open', $plan) }}">@csrf<button class="btn btn-sm btn-dark">Otevřít plán</button></form>
      <button class="btn btn-sm btn-outline-dark" aria-label="Sdílet" onclick="navigator.clipboard.writeText('{{ route('cart.shared', $plan->token) }}');this.innerHTML='<i class=\'bi bi-check\'></i> Odkaz zkopírován'"><i class="bi bi-share"></i></button>
      <form method="post" action="{{ route('account.plans.delete', $plan) }}">@csrf @method('delete')<button class="btn btn-sm btn-outline-dark" aria-label="Smazat"><i class="bi bi-trash"></i></button></form>
    </div>
  @empty
    <div class="pp-box p-4 text-secondary">Plán si uložíš tlačítkem „Uložit plán na později“ v košíku.</div>
  @endforelse
</div>
@endsection
