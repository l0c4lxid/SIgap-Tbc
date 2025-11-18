@extends('layouts.softui')

@section('title','Login')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <h3>Login</h3>

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div class="mb-3">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" class="form-control" required autofocus>
      </div>

      <div class="mb-3">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" class="form-control" required>
      </div>

      <div class="mb-3 form-check">
        <input type="checkbox" name="remember" id="remember" class="form-check-input">
        <label for="remember" class="form-check-label">Remember me</label>
      </div>

      <button class="btn btn-primary">Login</button>
    </form>
    <hr/>
    <a href="{{ route('register') }}">Register</a>
  </div>
</div>
@endsection
