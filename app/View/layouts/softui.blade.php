<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title','Dashboard')</title>
    <!--
      Instruksi: Ambil markup dan assets (CSS/JS) dari
      Soft UI Dashboard (Creative Tim) dan paste di sini.
      - Copy head CSS + vendor CSS ke bagian head.
      - Copy footer JS ke bagian sebelum </body>.
      - Ganti tempat konten dengan @yield('content').
    -->
    <!-- Placeholder minimal styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">App</a>
    <div class="d-flex">
      @auth
        <span class="me-3">Hai, {{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="btn btn-outline-danger btn-sm">Logout</button>
        </form>
      @endauth
    </div>
  </div>
</nav>

<div class="container mt-4">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Paste Soft UI scripts here when integrating -->
</body>
</html>
