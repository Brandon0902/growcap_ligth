<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Growcap')</title>

  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="h-full bg-gradient-to-br from-white via-white to-purple-50 text-gray-900">
  <div class="min-h-screen">
    @yield('content')
  </div>
</body>
</html>
