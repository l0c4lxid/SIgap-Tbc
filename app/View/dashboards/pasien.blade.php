@extends('layouts.softui')

@section('title','Dashboard Pasien')

@section('content')
<h2>Dashboard Pasien</h2>
<p>Hai, {{ $user->name }} (Role: {{ $user->getRoleNames()->first() }})</p>

@include('components.role-menu')
@endsection
