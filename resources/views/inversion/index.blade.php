@extends('layouts.app')

@section('title', 'Growcap | Inversión Plazo')

@section('content')
  <div class="py-8 lg:py-10">
    <div class="rounded-3xl bg-white/70 backdrop-blur shadow-sm ring-1 ring-black/5 p-6 sm:p-8">
      <div class="flex items-center gap-3">
        <div class="h-12 w-12 rounded-2xl bg-purple-50 flex items-center justify-center">
          <span class="text-xl">📈</span>
        </div>
        <div>
          <div class="text-2xl font-extrabold">Inversión Plazo</div>
          <div class="text-gray-500">Planes, rendimiento y seguimiento</div>
        </div>
      </div>

      <div class="mt-8 grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-black/5 p-5">
          <div class="text-sm text-gray-500">Acción principal</div>
          <div class="mt-2 text-lg font-bold">Botón principal</div>
          <div class="mt-4">
            <button class="w-full h-11 rounded-xl bg-purple-700 text-white font-semibold hover:bg-purple-800 transition">
              Continuar
            </button>
          </div>
        </div>
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-black/5 p-5">
          <div class="text-sm text-gray-500">Acción secundaria</div>
          <div class="mt-2 text-lg font-bold">Ver movimientos</div>
          <div class="mt-4">
            <button class="w-full h-11 rounded-xl bg-black text-white font-semibold hover:bg-black/90 transition">
              Ver historial
            </button>
          </div>
        </div>
      </div>

      <div class="mt-6 rounded-2xl bg-purple-50/60 p-5 text-sm text-gray-700">
        <span class="font-semibold">Nota:</span> Esta pantalla es “UI only”. Aquí conectas tus datos reales y acciones (crear solicitud, depositar, pagar, etc.).
      </div>
    </div>
  </div>
@endsection
