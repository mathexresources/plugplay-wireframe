@extends('layouts.app')
@section('title', $page->title)
@section('content')
<div class="container py-4" style="max-width:820px">
  <h1>{{ $page->title }}</h1>
  <div class="pp-box p-4">{!! $page->body !!}</div>
</div>
@endsection
