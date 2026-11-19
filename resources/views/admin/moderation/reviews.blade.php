@extends('admin.layout')
@section('title', 'Recenze')
@section('content')
<h1 class="h3 mb-3">Recenze</h1>
<div class="vstack gap-2">
  @forelse ($reviews as $r)
    <div class="pp-card p-3 d-flex flex-wrap gap-3 align-items-start">
      <div class="flex-fill"><b>{{ $r->author_name }}</b>{{ $r->car_label ? ', '.$r->car_label : '' }} · <span class="text-warning">{{ str_repeat('★', $r->rating) }}</span>
        @unless ($r->is_approved)<span class="badge text-bg-warning">čeká na schválení</span>@endunless
        <div class="small text-secondary">{{ $r->product?->name }} · {{ $r->created_at->format('j. n. Y') }}</div><p class="small mb-0 mt-1">{{ $r->body }}</p></div>
      <form method="post" action="{{ route('admin.reviews.toggle', $r) }}">@csrf<button class="btn btn-sm {{ $r->is_approved ? 'btn-outline-dark' : 'btn-dark' }}">{{ $r->is_approved ? 'Skrýt' : 'Schválit' }}</button></form>
      <form method="post" action="{{ route('admin.reviews.destroy', $r) }}" data-confirm="Smazat recenzi?">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger" aria-label="Smazat"><i class="bi bi-trash"></i></button></form>
    </div>
  @empty <p class="text-secondary">Žádné recenze.</p> @endforelse
</div>
<div class="mt-3">{{ $reviews->links() }}</div>
@endsection
