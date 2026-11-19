@extends('admin.layout')
@section('title', 'Zákazníci')
@section('content')
<h1 class="h3 mb-3">Zákazníci <span class="text-secondary fs-6">{{ $users->total() }}</span></h1>
<form class="d-flex gap-2 mb-3"><input class="form-control form-control-sm" style="max-width:260px" name="q" value="{{ request('q') }}" placeholder="Jméno nebo e-mail…">
  <select name="role" class="form-select form-select-sm w-auto"><option value="">Všichni</option><option value="admin" @selected(request('role') === 'admin')>Jen správci</option></select><button class="btn btn-sm btn-outline-dark">Hledat</button></form>
<div class="pp-card table-responsive">
  <table class="table align-middle mb-0">
    <thead><tr><th>Zákazník</th><th>Registrace</th><th class="text-end">Objednávek</th><th class="text-end">Utraceno</th><th></th></tr></thead>
    <tbody>
      @foreach ($users as $u)
        <tr><td><b>{{ $u->name }}</b> @if ($u->is_admin)<span class="badge text-bg-dark">správce</span>@endif<div class="small text-secondary">{{ $u->email }}</div></td>
          <td class="small">{{ $u->created_at->format('j. n. Y') }}</td><td class="text-end">{{ $u->orders_count }}</td><td class="text-end">{{ czk($u->orders_sum_total) }}</td>
          <td class="text-end"><a href="{{ route('admin.customers.show', $u) }}" class="btn btn-sm btn-outline-dark">Detail</a></td></tr>
      @endforeach
    </tbody>
  </table>
</div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
