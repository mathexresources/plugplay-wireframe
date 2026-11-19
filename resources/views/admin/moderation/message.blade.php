@extends('admin.layout')
@section('title', 'Zpráva')
@section('content')
<a href="{{ route('admin.messages.index') }}" class="small link-secondary"><i class="bi bi-arrow-left"></i> Zprávy</a>
<div class="pp-card p-4 mt-2" style="max-width:820px">
  <span class="badge text-bg-light border">{{ \App\Models\Message::TYPES[$message->type] ?? $message->type }}</span>
  <h1 class="h4 mt-2">{{ $message->subject ?: 'Bez předmětu' }}</h1>
  <div class="small text-secondary mb-3">{{ $message->name }} &lt;{{ $message->email }}&gt; · {{ $message->created_at->format('j. n. Y H:i') }}</div>
  <p style="white-space:pre-line">{{ $message->body }}</p>
  @if ($message->attachment)<a href="{{ \Illuminate\Support\Facades\Storage::url($message->attachment) }}" target="_blank"><img src="{{ \Illuminate\Support\Facades\Storage::url($message->attachment) }}" alt="Příloha" style="max-height:240px" class="rounded border"></a>@endif
  <div class="d-flex gap-2 mt-3">
    <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: 'Váš dotaz')) }}" class="btn btn-dark"><i class="bi bi-reply"></i> Odpovědět e-mailem</a>
    <form method="post" action="{{ route('admin.messages.destroy', $message) }}" data-confirm="Smazat zprávu?">@csrf @method('delete')<button class="btn btn-outline-danger">Smazat</button></form>
  </div>
</div>
@endsection
