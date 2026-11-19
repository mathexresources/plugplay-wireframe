@extends('layouts.app')
@section('title', 'Kontakt a poradna')
@section('content')
<div class="container py-4" style="max-width:820px">
  <h1>Napiš nám</h1>
  <p class="text-secondary">Poradíme s výběrem, montáží i s tím, jaký model auta máš. Odpovídáme do 24 hodin v pracovní dny.</p>
  <form class="pp-card p-4" method="post" action="{{ route('contact.send') }}" enctype="multipart/form-data">@csrf
    <div class="row g-3">
      <div class="col-md-4"><label class="form-label small" for="ty">Typ</label><select id="ty" name="type" class="form-select">@foreach (\App\Models\Message::TYPES as $k => $l)<option value="{{ $k }}" @selected(old('type', $type) === $k)>{{ $l }}</option>@endforeach</select></div>
      <div class="col-md-4"><label class="form-label small" for="nm">Jméno</label><input class="form-control" id="nm" name="name" required value="{{ old('name', auth()->user()?->name) }}"></div>
      <div class="col-md-4"><label class="form-label small" for="em">E-mail</label><input class="form-control" id="em" name="email" type="email" required value="{{ old('email', auth()->user()?->email) }}"></div>
      <div class="col-12"><label class="form-label small" for="su">Předmět</label><input class="form-control" id="su" name="subject" value="{{ old('subject', $subject) }}"></div>
      <div class="col-12"><label class="form-label small" for="bo">Zpráva</label><textarea class="form-control" id="bo" name="body" rows="5" required>{{ old('body') }}</textarea></div>
      <div class="col-12"><label class="form-label small" for="at">Fotka <span class="text-secondary">(nepovinné – třeba konektoru nebo rádia)</span></label><input class="form-control" id="at" name="attachment" type="file" accept="image/*"></div>
      <input type="text" name="website" class="d-none" tabindex="-1" autocomplete="off" aria-hidden="true">
    </div>
    <button class="btn btn-dark mt-3">Odeslat</button>
  </form>
</div>
@endsection
