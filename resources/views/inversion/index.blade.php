@extends('layouts.app')

@section('title', 'Growcap | Inversión Plazo')

@section('content')
  @php
    $stripeStatus = request()->query('status');
    $stripeIsSuccess = $stripeStatus === 'success';
    $stripeIsCanceled = $stripeStatus === 'cancel';

    $statusType = session('status_type');
    $statusMessage = session('status_message');

    $investmentSuccess = ($statusType === 'success') || $stripeIsSuccess;
    $completionMessage = $statusMessage
      ?? ($stripeIsSuccess
        ? 'Pago confirmado. Tu solicitud quedó en estado pendiente de revisión.'
        : 'Solicitud enviada correctamente. Tu inversión quedó en estado pendiente de revisión.');
  @endphp

  <div class="py-8 lg:py-10">
    <div class="rounded-3xl bg-white/80 backdrop-blur-xl shadow-xl ring-1 ring-purple-100 p-6 sm:p-8">
      <div class="flex items-center gap-3">
        <div class="h-12 w-12 rounded-2xl bg-purple-50 flex items-center justify-center">
          <span class="text-xl">📈</span>
        </div>
        <div>
          <div class="text-2xl font-extrabold">Inversión Plazo</div>
          <div class="text-gray-500">Proceso guiado paso a paso</div>
        </div>
      </div>

      @if (($statusType === 'error') || $stripeIsCanceled)
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {{ $statusMessage ?? 'Pago cancelado. Puedes intentar de nuevo cuando quieras.' }}
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

      <div
        class="mt-8 rounded-3xl border-2 border-purple-200 bg-gradient-to-b from-white to-purple-50/60 p-4 shadow-lg sm:p-6 lg:p-8"
        data-investment-wizard
        data-investment-completed="{{ $investmentSuccess ? '1' : '0' }}"
        data-investment-has-errors="{{ $errors->any() ? '1' : '0' }}"
      >
        <div class="mb-8">
          <div class="mb-5 grid gap-2 sm:grid-cols-4">
            <div class="rounded-2xl border px-3 py-2 text-center text-xs font-semibold" data-investment-step-badge="1">🎯 Plan</div>
            <div class="rounded-2xl border px-3 py-2 text-center text-xs font-semibold" data-investment-step-badge="2">💵 Monto</div>
            <div class="rounded-2xl border px-3 py-2 text-center text-xs font-semibold" data-investment-step-badge="3">💳 Pago</div>
            <div class="rounded-2xl border px-3 py-2 text-center text-xs font-semibold" data-investment-step-badge="4">✅ Confirmación</div>
          </div>
          <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-purple-700">
            <span>Paso <span data-investment-current-step>{{ $investmentSuccess ? 4 : 1 }}</span> de 4</span>
            <span data-investment-current-title>{{ $investmentSuccess ? 'Confirmación' : 'Elige el plan' }}</span>
          </div>
          <div class="mt-3 h-2 overflow-hidden rounded-full bg-purple-100">
            <div
              class="h-full rounded-full bg-purple-700 transition-all duration-300"
              style="width: {{ $investmentSuccess ? '100%' : '25%' }}"
              data-investment-progress
            ></div>
          </div>
        </div>

        <form
          class="grid gap-4"
          method="POST"
          action="{{ route('inversion.solicitud') }}"
          data-investment-form
          data-api-base-url="{{ config('app.backend_api_url') }}"
          data-investment-plans-endpoint="/inversiones/planes"
          data-investment-request-endpoint="/api/inversiones"
          data-investment-stripe-endpoint-template="/api/inversiones/{id}/stripe/checkout"
          data-investment-stripe-return-url="{{ url('/inversion') }}"
        >
          @csrf
          <input type="hidden" name="auth_token" value="">
          <input type="hidden" name="auth_token_type" value="">

          <section class="grid gap-4 text-center" data-step-panel="1">
            <h2 class="text-3xl font-black text-gray-900">Elige el plan</h2>
            <p class="text-sm text-gray-500">Paso 1: selecciona el plan que más te guste.</p>
            <select
              class="mx-auto h-14 w-full max-w-xl rounded-2xl border-2 border-purple-200 bg-white px-4 text-lg shadow-sm"
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
            <button
              type="button"
              class="mx-auto mt-2 h-12 w-full max-w-xs rounded-xl bg-gradient-to-r from-purple-600 to-fuchsia-600 px-5 text-lg font-bold text-white shadow-md transition hover:scale-[1.01]"
              data-step-next
            >
              Continuar
            </button>
          </section>

          <section class="hidden grid gap-4 text-center" data-step-panel="2">
            <h2 class="text-3xl font-black text-gray-900">Escribe la cantidad a invertir</h2>
            <p class="text-sm text-gray-500">Paso 2: coloca el monto y revisa los datos del plan.</p>
            <input
              class="mx-auto h-14 w-full max-w-xl rounded-2xl border-2 border-purple-200 bg-white px-4 text-lg shadow-sm"
              name="cantidad"
              type="number"
              min="1"
              step="0.01"
              placeholder="Cantidad a invertir"
              value="{{ old('cantidad') }}"
              required
            >

            <div class="mx-auto grid w-full max-w-xl gap-3 sm:grid-cols-2">
              <input
                class="h-11 rounded-xl border border-purple-100 bg-white px-4 text-gray-700"
                name="tiempo"
                type="text"
                placeholder="Periodo en meses"
                value="{{ old('tiempo') }}"
                readonly
                data-investment-plan-period
              >

              <input
                class="h-11 rounded-xl border border-purple-100 bg-white px-4 text-gray-700"
                type="text"
                placeholder="Rendimiento"
                readonly
                data-investment-plan-yield
              >
            </div>

            <div class="mx-auto mt-2 flex w-full max-w-xl gap-3">
              <button type="button" class="h-11 flex-1 rounded-xl border border-purple-300 bg-white font-semibold text-purple-700" data-step-prev>Regresar</button>
              <button type="button" class="h-11 flex-1 rounded-xl bg-gradient-to-r from-purple-600 to-fuchsia-600 font-semibold text-white" data-step-next>Continuar</button>
            </div>
          </section>

          <section class="hidden grid gap-4 text-center" data-step-panel="3">
            <h2 class="text-3xl font-black text-gray-900">Selecciona el método de pago</h2>
            <p class="text-sm text-gray-500">Paso 3: elige cómo quieres completar tu solicitud.</p>

            <div class="mx-auto grid w-full max-w-xl gap-3 text-left">
              <label class="flex items-center gap-2 rounded-xl border-2 border-purple-100 bg-white px-4 py-3 shadow-sm">
                <input
                  type="radio"
                  name="payment_method"
                  value="normal"
                  class="text-purple-700"
                  {{ old('payment_method', 'normal') === 'normal' ? 'checked' : '' }}
                >
                <span>Registrar inversión (pago manual)</span>
              </label>
              <label class="flex items-center gap-2 rounded-xl border-2 border-purple-100 bg-white px-4 py-3 shadow-sm">
                <input
                  type="radio"
                  name="payment_method"
                  value="stripe"
                  class="text-purple-700"
                  {{ old('payment_method') === 'stripe' ? 'checked' : '' }}
                >
                <span>Pagar ahora con Stripe</span>
              </label>
            </div>

            <div class="mx-auto mt-2 flex w-full max-w-xl gap-3">
              <button type="button" class="h-11 flex-1 rounded-xl border border-purple-300 bg-white font-semibold text-purple-700" data-step-prev>Regresar</button>
              <button class="h-11 flex-1 rounded-xl bg-gradient-to-r from-purple-600 to-fuchsia-600 font-semibold text-white shadow-md transition hover:scale-[1.01]" type="submit">
                Confirmar solicitud
              </button>
            </div>
          </section>

          <section class="{{ $investmentSuccess ? 'grid' : 'hidden' }} gap-4 text-center" data-step-panel="4">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-3xl">✅</div>
            <h2 class="text-3xl font-black text-emerald-700">¡Solicitud enviada con éxito!</h2>
            <p class="mx-auto max-w-2xl text-base text-gray-600">{{ $completionMessage }}</p>
            <p class="text-sm text-gray-500">Tu solicitud quedó en estado <span class="font-semibold">pendiente de revisión</span>.</p>
          </section>
        </form>
      </div>

      <div
        class="requests-showcase mt-8 rounded-2xl p-5"
        data-requests-feed
        data-api-base-url="{{ config('app.backend_api_url') }}"
        data-requests-endpoint="/api/inversiones"
        data-requests-type="Inversión"
        data-requests-empty="Aún no tienes solicitudes de inversión."
        data-requests-limit="4"
      >
        <div class="flex items-start justify-between gap-3">
          <div>
            <div class="text-sm text-white/80">Tus solicitudes</div>
            <div class="mt-1 text-lg font-bold text-white">Historial reciente</div>
          </div>
          <div class="requests-count text-xs" data-requests-count>0 solicitudes</div>
        </div>
        <div class="mt-4 grid gap-3" data-requests-list>
          <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-center text-sm text-gray-500">
            Cargando solicitudes...
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
