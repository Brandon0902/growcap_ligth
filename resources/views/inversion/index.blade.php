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

      @if (session('status_message') && session('status_type') === 'success')
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
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

      @if (!empty($plansError))
        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
          {{ $plansError }}
          @if (!empty($plansErrors))
            <ul class="mt-2 list-disc pl-5">
              @foreach ($plansErrors as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          @endif
        </div>
      @endif

      <div class="mt-8 grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-black/5 p-5">
          <div class="text-sm text-gray-500">Acción principal</div>
          <div class="mt-2 text-lg font-bold">Solicitar inversión</div>

          <form
            class="mt-4 grid gap-3"
            method="POST"
            action="{{ route('inversion.solicitud') }}"
            data-investment-form
            data-api-base-url="{{ config('app.backend_api_url') }}"
            data-investment-plans-endpoint="/inversiones/planes"
          >
            @csrf
            <input type="hidden" name="auth_token" value="">
            <input type="hidden" name="auth_token_type" value="">

            <div class="grid gap-3 sm:grid-cols-2">
              <select
                class="h-11 rounded-xl border border-gray-200 px-4"
                name="id_activo"
                required
                data-investment-plan-select
              >
                <option value="">Selecciona un plan</option>
                @forelse ($plans ?? [] as $plan)
                  <option
                    value="{{ $plan['id'] ?? '' }}"
                    data-periodo="{{ $plan['periodo'] ?? $plan['tiempo'] ?? $plan['plazo'] ?? '' }}"
                    data-rendimiento="{{ $plan['rendimiento'] ?? $plan['tasa'] ?? '' }}"
                    @selected(old('id_activo') == ($plan['id'] ?? null))
                  >
                    {{ $plan['label'] ?? 'Plan sin nombre' }}
                  </option>
                @empty
                  <option value="" disabled>No hay planes disponibles</option>
                @endforelse
              </select>

              <input
                class="h-11 rounded-xl border border-gray-200 px-4"
                name="cantidad"
                type="number"
                min="1"
                step="0.01"
                placeholder="Cantidad a invertir"
                value="{{ old('cantidad') }}"
                required
              >
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
              <input
                class="h-11 rounded-xl border border-gray-200 bg-gray-50 px-4 text-gray-600"
                name="tiempo"
                type="text"
                placeholder="Periodo en meses"
                value="{{ old('tiempo') }}"
                readonly
                data-investment-plan-period
              >

              <input
                class="h-11 rounded-xl border border-gray-200 bg-gray-50 px-4 text-gray-600"
                type="text"
                placeholder="Rendimiento"
                readonly
                data-investment-plan-yield
              >
            </div>

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
        <span class="font-semibold">Nota:</span> Esta pantalla consume <code>/api/inversiones/planes</code> para listar planes y envía solicitudes a <code>/api/inversiones</code> usando <code>GROWCAP_API_BASE_URL</code> y el token configurado.
      </div>

      <div class="mt-3 text-xs text-gray-500" data-investment-token-debug>
        Token: verificando...
      </div>
    </div>
  </div>
@endsection
