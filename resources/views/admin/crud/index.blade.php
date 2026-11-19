@extends('admin.layout')
@section('title', $c->title)
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h3 mb-0">{{ $c->title }} <span class="text-secondary fs-6">{{ $items->total() }}</span></h1>
  @if (Route::has($c->route.'.create'))<a href="{{ route($c->route.'.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Přidat {{ $c->singular }}</a>@endif
</div>
<form class="d-flex flex-wrap gap-2 mb-3">
  <input class="form-control form-control-sm" style="max-width:260px" name="q" value="{{ request('q') }}" placeholder="Hledat…">
  @foreach ($filters as [$name, $label, $opts])
    <select name="{{ $name }}" class="form-select form-select-sm w-auto" aria-label="{{ $label }}">@foreach ($opts as $k => $v)<option value="{{ $k }}" @selected(request($name) == $k)>{{ $v }}</option>@endforeach</select>
  @endforeach
  <button class="btn btn-sm btn-outline-dark">Filtrovat</button>
</form>
<div class="pp-card table-responsive">
  <table class="table align-middle mb-0">
    <thead><tr>@foreach ($columns as $label => $fn)<th>{{ $label }}</th>@endforeach<th></th></tr></thead>
    <tbody>
      @forelse ($items as $item)
        <tr>
          @foreach ($columns as $fn)<td>{{ $fn($item) }}</td>@endforeach
          <td class="text-end text-nowrap">
            <a href="{{ route($c->route.'.edit', $item->getKey()) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-pencil"></i> Upravit</a>
            @if (Route::has($c->route.'.destroy'))
              <form method="post" action="{{ route($c->route.'.destroy', $item->getKey()) }}" class="d-inline" data-confirm="Opravdu smazat?">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger" aria-label="Smazat"><i class="bi bi-trash"></i></button></form>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="{{ count($columns) + 1 }}" class="text-secondary text-center py-4">Nic tu není.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
