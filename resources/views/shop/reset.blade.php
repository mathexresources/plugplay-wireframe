@extends('layouts.app')
@section('title', 'Nové heslo')
@section('content')
<div class="container py-5" style="max-width:480px">
  <form class="pp-card p-4" method="post" action="{{ route('password.update') }}">@csrf
    <h1 class="h3">Nové heslo</h1>
    <input type="hidden" name="token" value="{{ $token }}">
    <label class="form-label small" for="em">E-mail</label><input class="form-control mb-2" id="em" name="email" type="email" required value="{{ old('email', $email) }}">
    <label class="form-label small" for="pw">Nové heslo</label><input class="form-control mb-2" id="pw" name="password" type="password" required minlength="8">
    <label class="form-label small" for="pw2">Heslo znovu</label><input class="form-control mb-3" id="pw2" name="password_confirmation" type="password" required>
    <button class="btn btn-dark w-100">Uložit heslo</button>
  </form>
</div>
@endsection
