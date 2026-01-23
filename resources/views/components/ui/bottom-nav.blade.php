{{-- Bottom nav solo en móvil --}}
<nav class="fixed bottom-0 left-0 right-0 z-40 md:hidden bg-white/80 backdrop-blur border-t border-black/5">
  <div class="mx-auto max-w-6xl px-4">
    <div class="h-16 flex items-center justify-between">

      <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 text-xs {{ request()->routeIs('dashboard') ? 'text-purple-700' : 'text-gray-500' }}">
        <span class="text-lg">⌂</span>
        <span>Inicio</span>
      </a>

      <a href="{{ route('ahorro.index') }}" class="flex flex-col items-center gap-1 text-xs {{ request()->routeIs('ahorro.*') ? 'text-purple-700' : 'text-gray-500' }}">
        <span class="text-lg">🐷</span>
        <span>Ahorro</span>
      </a>

      <a href="{{ route('prestamos.index') }}" class="flex flex-col items-center gap-1 text-xs {{ request()->routeIs('prestamos.*') ? 'text-purple-700' : 'text-gray-500' }}">
        <span class="text-lg">💳</span>
        <span>Préstamos</span>
      </a>

      <a href="#" class="flex flex-col items-center gap-1 text-xs text-gray-500">
        <span class="text-lg">👤</span>
        <span>Perfil</span>
      </a>

    </div>
  </div>
</nav>

{{-- Espaciador para que el contenido no quede debajo del bottom-nav --}}
<div class="h-20 md:hidden"></div>
