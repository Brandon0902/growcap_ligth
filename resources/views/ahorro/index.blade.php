@extends('layouts.app')

@section('title', 'Growcap | Ahorro Flexible')

@section('content')
  <div class="py-8 lg:py-10">
    <div class="rounded-3xl bg-white/70 backdrop-blur shadow-sm ring-1 ring-black/5 p-6 sm:p-8">
      <div class="flex items-center gap-3">
        <div class="h-12 w-12 rounded-2xl bg-purple-50 flex items-center justify-center">
          <span class="text-xl">🐷</span>
        </div>
        <div>
          <div class="text-2xl font-extrabold">Ahorro Flexible</div>
          <div class="text-gray-500">Depósitos, retiros y movimientos</div>
        </div>
      </div>

      @if (session('status_message'))
        <div class="mt-6 rounded-2xl border px-4 py-3 text-sm {{ session('status_type') === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-700' }}">
          {{ session('status_message') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          <div class="font-semibold">Revisa los campos del formulario:</div>
          <ul class="mt-2 list-disc pl-5">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="mt-8 grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-black/5 p-5">
          <div class="text-sm text-gray-500">Acción principal</div>
          <div class="mt-2 text-lg font-bold">Solicitar ahorro</div>
          <form class="mt-4 grid gap-3" method="POST" action="{{ route('ahorro.solicitud') }}">
            @csrf
            <input class="h-11 rounded-xl border border-gray-200 px-4" name="full_name" placeholder="Nombre completo" value="{{ old('full_name') }}" required>
            <input class="h-11 rounded-xl border border-gray-200 px-4" name="email" type="email" placeholder="Correo" value="{{ old('email') }}" required>
            <input class="h-11 rounded-xl border border-gray-200 px-4" name="phone" placeholder="Teléfono" value="{{ old('phone') }}" required>
            <input class="h-11 rounded-xl border border-gray-200 px-4" name="amount" type="number" min="1" step="0.01" placeholder="Monto inicial" value="{{ old('amount') }}" required>
            <select class="h-11 rounded-xl border border-gray-200 px-4" name="frequency" required>
              <option value="" disabled {{ old('frequency') ? '' : 'selected' }}>Frecuencia de depósito</option>
              <option value="mensual" {{ old('frequency') === 'mensual' ? 'selected' : '' }}>Mensual</option>
              <option value="quincenal" {{ old('frequency') === 'quincenal' ? 'selected' : '' }}>Quincenal</option>
              <option value="semanal" {{ old('frequency') === 'semanal' ? 'selected' : '' }}>Semanal</option>
            </select>
            <button class="w-full h-11 rounded-xl bg-purple-700 text-white font-semibold hover:bg-purple-800 transition" type="submit">
              Enviar solicitud
            </button>
          </form>
        </div>
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-black/5 p-5">
          <div class="text-sm text-gray-500">Acción secundaria</div>
          <div class="mt-2 text-lg font-bold">Ver movimientos</div>
          <div class="mt-4">
            <button class="w-full h-11 rounded-xl bg-black text-white font-semibold hover:bg-black/90 transition" type="button">
              Ver historial
            </button>
          </div>
        </div>
      </div>

      <div class="mt-6 rounded-2xl bg-purple-50/60 p-5 text-sm text-gray-700">
        <span class="font-semibold">Nota:</span> Esta pantalla ya envía solicitudes a la API de Growcap según la configuración de <code>GROWCAP_API_BASE_URL</code>.
      </div>
    </div>
  </div>
@endsection
