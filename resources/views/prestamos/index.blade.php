@extends('layouts.app')

@section('title', 'Growcap | Préstamos')

@section('content')
  <div class="py-8 lg:py-10">
    <div class="rounded-3xl bg-white/70 backdrop-blur shadow-sm ring-1 ring-black/5 p-6 sm:p-8">
      <div class="flex items-center gap-3">
        <div class="h-12 w-12 rounded-2xl bg-purple-50 flex items-center justify-center">
          <span class="text-xl">💳</span>
        </div>
        <div>
          <div class="text-2xl font-extrabold">Préstamos</div>
          <div class="text-gray-500">Solicitudes, pagos y estado</div>
        </div>
      </div>

      @if (session('status_message'))
        <div class="mt-6 rounded-2xl border px-4 py-3 text-sm {{ session('status_type') === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-700' }}">
          {{ session('status_message') }}
          @if (session('status_errors'))
            @php
              $statusErrors = (array) session('status_errors');
            @endphp
            @if (count($statusErrors) > 0)
              <ul class="mt-2 list-disc pl-5 text-xs">
                @foreach ($statusErrors as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            @endif
          @endif
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
          <div class="mt-2 text-lg font-bold">Solicitar préstamo</div>
          <form
            class="mt-4 grid gap-3"
            method="POST"
            action="{{ route('prestamos.solicitud') }}"
            enctype="multipart/form-data"
            data-loan-form
            data-api-base-url="{{ config('app.backend_api_url') }}"
            data-loan-plans-endpoint="/prestamos/planes"
          >
            @csrf
            <input type="hidden" name="auth_token" value="">
            <input type="hidden" name="auth_token_type" value="">

            <div class="grid gap-3 sm:grid-cols-2">
              <select
                class="h-11 rounded-xl border border-gray-200 px-4"
                name="id_activo"
                required
                data-loan-plan-select
                data-loan-selected="{{ old('id_activo') }}"
              >
                <option value="">Selecciona un plan</option>
              </select>
              <input
                class="h-11 rounded-xl border border-gray-200 px-4"
                name="cantidad"
                type="number"
                min="1"
                step="0.01"
                placeholder="Monto solicitado"
                value="{{ old('cantidad') }}"
                required
                data-loan-amount
              >
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
              <input
                class="h-11 rounded-xl border border-gray-200 bg-gray-50 px-4 text-gray-600"
                type="text"
                placeholder="Periodo"
                readonly
                data-loan-plan-period
              >
              <input
                class="h-11 rounded-xl border border-gray-200 bg-gray-50 px-4 text-gray-600"
                type="text"
                placeholder="Semanas"
                readonly
                data-loan-plan-weeks
              >
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
              <input
                class="h-11 rounded-xl border border-gray-200 bg-gray-50 px-4 text-gray-600"
                type="text"
                placeholder="Interés"
                readonly
                data-loan-plan-interest
              >
              <input
                class="h-11 rounded-xl border border-gray-200 bg-gray-50 px-4 text-gray-600"
                type="text"
                placeholder="Monto máximo"
                readonly
                data-loan-plan-max
              >
            </div>

            <div class="grid gap-3">
              <div class="text-sm font-semibold text-gray-700">Aval</div>
              <label class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3">
                <input
                  type="radio"
                  name="aval_method"
                  value="codigo"
                  class="text-purple-700"
                  checked
                  data-loan-aval-toggle
                >
                <span>Usar código de aval</span>
              </label>
              <label class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3">
                <input
                  type="radio"
                  name="aval_method"
                  value="documentos"
                  class="text-purple-700"
                  data-loan-aval-toggle
                >
                <span>Subir documentos del aval</span>
              </label>
            </div>

            <div class="grid gap-3" data-loan-aval-code>
              <input
                class="h-11 rounded-xl border border-gray-200 px-4"
                name="codigo_aval"
                placeholder="Código de aval"
                value="{{ old('codigo_aval') }}"
              >
              <p class="text-xs text-gray-500">Ingresa el código del aval activo. Si no lo tienes, sube los documentos.</p>
            </div>

            <div class="grid gap-3" data-loan-aval-docs hidden>
              <div class="text-sm text-gray-500">Documentos requeridos (PDF o imagen, máximo 5MB).</div>
              <input
                class="h-11 rounded-xl border border-gray-200 px-4 py-2"
                name="doc_solicitud_aval"
                type="file"
                accept="application/pdf,image/jpeg,image/png"
              >
              <input
                class="h-11 rounded-xl border border-gray-200 px-4 py-2"
                name="doc_comprobante_domicilio"
                type="file"
                accept="application/pdf,image/jpeg,image/png"
              >
              <input
                class="h-11 rounded-xl border border-gray-200 px-4 py-2"
                name="doc_ine_frente"
                type="file"
                accept="application/pdf,image/jpeg,image/png"
              >
              <input
                class="h-11 rounded-xl border border-gray-200 px-4 py-2"
                name="doc_ine_reverso"
                type="file"
                accept="application/pdf,image/jpeg,image/png"
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
        <span class="font-semibold">Nota:</span> Esta pantalla consume <code>/prestamos/planes</code> para listar planes y envía solicitudes a <code>/prestamos</code> usando <code>GROWCAP_API_BASE_URL</code> y el token configurado.
      </div>
    </div>
  </div>
@endsection
