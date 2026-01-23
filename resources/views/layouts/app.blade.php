<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Growcap')</title>

  {{-- Asume Vite/Tailwind en tu proyecto Laravel 12 --}}
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="h-full bg-gradient-to-br from-white via-white to-purple-50 text-gray-900">

  <div class="min-h-screen">
    <x-ui.topbar />

    <main class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
      @yield('content')
    </main>

    <x-ui.bottom-nav />
  </div>

</body>
</html>
