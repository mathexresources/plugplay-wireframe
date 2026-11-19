@extends('layouts.app')
@section('title', 'Zapomenuté heslo')
@section('content')
<div class="container py-5" style="max-width:480px">
  <form class="pp-card p-4" method="post" action="{{ route('password.email') }}">@csrf
    <h1 class="h3">Zapomenuté heslo</h1>
    <p class="small text-secondary">Pošleme ti odkaz pro nastavení nového hesla.</p>
    <label class="form-label small" for="em">E-mail</label>
    <input class="form-control mb-3" id="em" name="email" type="email" required value="{{ old('email') }}">
    <button class="btn btn-dark w-100">Poslat odkaz</button>
    <div class="small text-secondary mt-3"><i class="bi bi-info-circle"></i> Školní projekt: e-maily se neodesílají, odkaz najdeš v <code>storage/logs/laravel.log</code>.</div>
  </form>
</div>
@endsection
