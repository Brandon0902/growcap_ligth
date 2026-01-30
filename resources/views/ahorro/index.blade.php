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

      @php
        $stripeStatus = request()->query('status');
        $stripeAction = request()->query('action');
      @endphp

      @if (session('status_message'))
        <div class="mt-6 rounded-2xl border px-4 py-3 text-sm {{ session('status_type') === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-700' }}">
          {{ session('status_message') }}
        </div>
      @elseif ($stripeStatus)
        @php
          $stripeIsSuccess = $stripeStatus === 'success';
          $stripeLabel = $stripeIsSuccess ? 'Pago confirmado' : 'Pago cancelado';
          $stripeSuffix = $stripeAction ? " (".$stripeAction.")" : '';
        @endphp
        <div class="mt-6 rounded-2xl border px-4 py-3 text-sm {{ $stripeIsSuccess ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-700' }}">
          {{ $stripeLabel }}{{ $stripeSuffix }}.
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
          <form
            class="mt-4 grid gap-3"
            method="POST"
            action="{{ route('ahorro.solicitud') }}"
            data-savings-form
            data-api-base-url="{{ config('app.backend_api_url') }}"
            data-savings-plans-endpoint="/ahorros/planes"
            data-savings-frequency-endpoint="/ahorros/frecuencia"
            data-savings-request-endpoint="/api/ahorros"
            data-savings-stripe-endpoint-template="/api/ahorros/{id}/stripe/checkout"
            data-savings-stripe-return-url="{{ route('ahorro.index') }}"
          >
            @csrf
            <input type="hidden" name="auth_token" value="">
            <input type="hidden" name="auth_token_type" value="">

            <div class="grid gap-3 sm:grid-cols-2">
              <select
                class="h-11 rounded-xl border border-gray-200 px-4"
                name="ahorro_id"
                required
                data-savings-plan-select
              >
                <option value="">Selecciona un plan</option>
              </select>

              <input
                class="h-11 rounded-xl border border-gray-200 px-4"
                name="monto_ahorro"
                type="number"
                min="0"
                step="0.01"
                placeholder="Monto inicial"
                value="{{ old('monto_ahorro') }}"
                required
              >
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
              <input
                class="h-11 rounded-xl border border-gray-200 px-4"
                name="cuota"
                type="number"
                min="0"
                step="0.01"
                placeholder="Cuota por frecuencia"
                value="{{ old('cuota') }}"
                required
                data-savings-cuota
              >

              <select
                class="h-11 rounded-xl border border-gray-200 px-4"
                name="frecuencia_pago"
                required
                data-savings-frequency
              >
                <option value="" disabled {{ old('frecuencia_pago') ? '' : 'selected' }}>Frecuencia de depósito</option>
                <option value="Mensual" {{ old('frecuencia_pago') === 'Mensual' ? 'selected' : '' }}>Mensual</option>
                <option value="Quincenal" {{ old('frecuencia_pago') === 'Quincenal' ? 'selected' : '' }}>Quincenal</option>
                <option value="Semanal" {{ old('frecuencia_pago') === 'Semanal' ? 'selected' : '' }}>Semanal</option>
              </select>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
              <input
                class="h-11 rounded-xl border border-gray-200 bg-gray-50 px-4 text-gray-600"
                type="text"
                placeholder="Rendimiento"
                readonly
                data-savings-plan-yield
              >

              <input
                class="h-11 rounded-xl border border-gray-200 bg-gray-50 px-4 text-gray-600"
                type="text"
                placeholder="Meses mínimos"
                readonly
                data-savings-plan-min-months
              >
            </div>

            <div class="text-xs text-gray-500" data-savings-minimum>
              Selecciona un plan para conocer la cuota mínima.
            </div>

            <div class="grid gap-3 sm:grid-cols-2" data-savings-fecha-fin-wrapper hidden>
              <input
                class="h-11 rounded-xl border border-gray-200 px-4"
                name="fecha_fin"
                type="date"
                value="{{ old('fecha_fin') }}"
                data-savings-fecha-fin
              >
            </div>

            <div class="grid gap-3">
              <div class="text-sm font-semibold text-gray-700">Forma de pago</div>
              <label class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3">
                <input
                  type="radio"
                  name="payment_method"
                  value="normal"
                  class="text-purple-700"
                  checked
                >
                <span>Registrar ahorro (pago manual)</span>
              </label>
              <label class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3">
                <input
                  type="radio"
                  name="payment_method"
                  value="stripe"
                  class="text-purple-700"
                >
                <span>Pagar ahora con Stripe</span>
              </label>
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
        <span class="font-semibold">Nota:</span> Esta pantalla consume <code>/api/ahorros/planes</code> y envía solicitudes a <code>/api/ahorros</code> usando <code>GROWCAP_API_BASE_URL</code> y el token configurado.
      </div>
    </div>
  </div>
@endsection
