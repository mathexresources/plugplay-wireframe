@extends('layouts.app')
@section('content')
@php $u = auth()->user(); @endphp
<div class="container py-4">
  <div class="d-flex align-items-center gap-3 mb-4">
    <div class="pp-img sm rounded-circle flex-shrink-0" style="width:56px;height:56px;min-height:0"><i class="bi bi-person"></i></div>
    <div><h1 class="h3 mb-0">Ahoj, {{ $u->first_name }}</h1><div class="small text-secondary">{{ $u->email }} · zákazníkem od roku {{ $u->created_at->year }}</div></div>
  </div>
  <div class="row g-4">
    <aside class="col-lg-3">
      <div class="list-group pp-card overflow-hidden">
        @foreach ([['account.garage', 'bi-car-front', 'Moje garáž', $u->cars()->count()], ['account.orders', 'bi-box-seam', 'Objednávky', null], ['account.guides', 'bi-book', 'Moje návody', null], ['account.plans', 'bi-bookmark', 'Uložené plány', null], ['account.profile', 'bi-person', 'Osobní údaje a adresy', null]] as [$r, $ic, $l, $badge])
          <a class="list-group-item list-group-item-action {{ request()->routeIs($r) || ($r === 'account.orders' && request()->routeIs('account.order')) ? 'active' : '' }}" href="{{ route($r) }}"><i class="bi {{ $ic }} me-2"></i>{{ $l }}@if ($badge)<span class="badge text-bg-dark float-end">{{ $badge }}</span>@endif</a>
        @endforeach
        @if ($u->is_admin)<a class="list-group-item list-group-item-action fw-bold" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Administrace</a>@endif
        <form method="post" action="{{ route('logout') }}">@csrf<button class="list-group-item list-group-item-action text-secondary"><i class="bi bi-box-arrow-right me-2"></i>Odhlásit se</button></form>
      </div>
    </aside>
    <section class="col-lg-9">@yield('account')</section>
  </div>
</div>
@endsection
