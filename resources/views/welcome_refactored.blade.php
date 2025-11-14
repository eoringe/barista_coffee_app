@extends('layouts.base')

@section('content')
  @include('partials.loading_indicator')
  <div class="container">
    @include('partials.sidebar')
    <main class="flex" style="flex-direction:column; gap:24px">
      @include('partials.menu_header')
      @include('partials.stats')
      @include('partials.menu_grid')
      @include('partials.specials')
      @include('partials.categories')
    </main>
  </div>
  @include('partials.modal')
@endsection
