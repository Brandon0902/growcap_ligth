@extends('layouts.auth')

@section('title', 'Iniciar sesión | Growcap')

@section('content')
  <div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
      <a href="{{ route('dashboard') }}" class="mb-8 inline-flex items-center gap-3">
        <div class="h-10 w-10 overflow-hidden rounded-2xl bg-gray-100 ring-1 ring-black/5 flex items-center justify-center">
          <img src="{{ asset('images/growcap-logo.svg') }}" alt="Logo Growcap" class="h-full w-full object-cover">
        </div>
        <div class="leading-tight">
          <div class="text-sm font-extrabold tracking-wide">GROWCAP</div>
          <div class="text-[10px] text-gray-400 -mt-0.5">BY MONEYGO</div>
        </div>
      </a>

      <div class="rounded-3xl bg-white shadow-xl shadow-purple-100/60 ring-1 ring-black/5 p-6 sm:p-8">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Bienvenido de nuevo</h1>
          <p class="text-sm text-gray-500 mt-1">Ingresa con tu correo, usuario o código de cliente.</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
          @csrf
          <div>
            <label for="login" class="text-sm font-semibold text-gray-700">Email, usuario o código</label>
            <input id="login" name="login" type="text" autocomplete="username"
                   class="mt-1 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500"
                   placeholder="cliente@correo.com" value="{{ old('login') }}" required>
            @error('login')
              <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
            @error('email')
              <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="password" class="text-sm font-semibold text-gray-700">Contraseña</label>
            <input id="password" name="password" type="password" autocomplete="current-password"
                   class="mt-1 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500"
                   placeholder="••••••••" required>
            @error('password')
              <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="inline-flex items-center gap-2 text-gray-500">
              <input type="checkbox" name="single" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500" @checked(old('single'))>
              Mantener solo esta sesión
            </label>
            <span class="text-gray-400">Dispositivo: cliente-web</span>
          </div>

          @if ($errors->has('login') || $errors->has('password'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
              Verifica los datos ingresados e intenta de nuevo.
            </div>
          @endif

          <button type="submit" data-login-submit
                  class="w-full rounded-2xl bg-purple-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-purple-200/60 transition hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
            Iniciar sesión
          </button>
        </form>
      </div>

      <p class="mt-6 text-center text-xs text-gray-400">¿Necesitas ayuda? Contacta a soporte.</p>
    </div>
  </div>
@endsection
