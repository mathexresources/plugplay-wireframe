@extends('admin.layout')
@section('title', $user->name)
@section('content')
<a href="{{ route('admin.customers.index') }}" class="small link-secondary"><i class="bi bi-arrow-left"></i> Zákazníci</a>
<div class="d-flex flex-wrap align-items-center gap-2 my-2">
  <h1 class="h3 mb-0">{{ $user->name }}</h1>@if ($user->is_admin)<span class="badge text-bg-dark">správce</span>@endif
  @if ($user->id !== auth()->id())
    <form method="post" action="{{ route('admin.customers.admin', $user) }}" class="ms-auto">@csrf<button class="btn btn-sm btn-outline-dark">{{ $user->is_admin ? 'Odebrat práva správce' : 'Udělat správcem' }}</button></form>
    <form method="post" action="{{ route('admin.customers.destroy', $user) }}" data-confirm="Opravdu smazat zákazníka?">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger">Smazat</button></form>
  @endif
</div>
<div class="small text-secondary mb-3">{{ $user->email }} · {{ $user->phone ?: 'bez telefonu' }} · registrace {{ $user->created_at->format('j. n. Y') }} · novinky {{ $user->notify_upgrades ? 'ano' : 'ne' }}</div>
<div class="row g-4">
  <div class="col-lg-7"><div class="pp-card p-3"><h2 class="h6 fw-bold">Objednávky</h2>
    <table class="table table-sm small mb-0">@forelse ($user->orders as $o)<tr><td><a href="{{ route('admin.orders.show', $o) }}">#{{ $o->number }}</a></td><td>{{ $o->created_at->format('j. n. Y') }}</td><td><span class="badge text-bg-{{ $o->status_color }}">{{ $o->status_label }}</span></td><td class="text-end">{{ czk($o->total) }}</td></tr>@empty<tr><td class="text-secondary">Žádné.</td></tr>@endforelse</table></div></div>
  <div class="col-lg-5">
    <div class="pp-card p-3 mb-3"><h2 class="h6 fw-bold">Garáž</h2>@forelse ($user->cars as $c)<div class="small">{{ $c->is_active ? '★' : '·' }} {{ $c->label }} – {{ $c->carModel->full_name }}{{ $c->year ? ', '.$c->year : '' }}</div>@empty<div class="small text-secondary">Prázdná.</div>@endforelse</div>
    <div class="pp-card p-3"><h2 class="h6 fw-bold">Adresy</h2>@forelse ($user->addresses as $a)<div class="small">{{ $a->street }}, {{ $a->zip }} {{ $a->city }}</div>@empty<div class="small text-secondary">Žádné.</div>@endforelse</div>
  </div>
</div>
@endsection
