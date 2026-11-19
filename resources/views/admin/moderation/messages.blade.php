@extends('admin.layout')
@section('title', 'Zprávy')
@section('content')
<h1 class="h3 mb-3">Zprávy od zákazníků</h1>
<div class="pp-card table-responsive"><table class="table align-middle mb-0">
  <thead><tr><th>Od</th><th>Typ</th><th>Předmět</th><th>Přijato</th></tr></thead>
  <tbody>
    @forelse ($messages as $m)
      <tr class="{{ $m->is_read ? '' : 'fw-bold' }}" onclick="location='{{ route('admin.messages.show', $m) }}'" style="cursor:pointer">
        <td>{{ $m->name }}<div class="small text-secondary fw-normal">{{ $m->email }}</div></td><td><span class="badge text-bg-light border">{{ \App\Models\Message::TYPES[$m->type] ?? $m->type }}</span></td>
        <td><a href="{{ route('admin.messages.show', $m) }}" class="text-reset">{{ $m->subject ?: \Illuminate\Support\Str::limit($m->body, 50) }}</a></td><td class="small">{{ $m->created_at->format('j. n. H:i') }}</td></tr>
    @empty <tr><td colspan="4" class="text-secondary text-center py-4">Žádné zprávy.</td></tr> @endforelse
  </tbody></table></div>
<div class="mt-3">{{ $messages->links() }}</div>
@endsection
