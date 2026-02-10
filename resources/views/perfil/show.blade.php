@extends('layouts.app')

@section('title', 'Growcap | Mi perfil')

@php
  $labelMap = [
    'nombre' => 'Nombre',
    'apellido' => 'Apellido',
    'email' => 'Correo',
    'telefono' => 'Teléfono',
    'estado_nombre' => 'Estado',
    'municipio_nombre' => 'Municipio',
    'rfc' => 'RFC',
    'direccion' => 'Dirección',
    'colonia' => 'Colonia',
    'cp' => 'Código postal',
    'beneficiario' => 'Beneficiario 1',
    'beneficiario_telefono' => 'Teléfono beneficiario 1',
    'beneficiario_02' => 'Beneficiario 2',
    'beneficiario_telefono_02' => 'Teléfono beneficiario 2',
    'banco' => 'Banco',
    'cuenta' => 'Cuenta',
    'fecha_ingreso' => 'Fecha de ingreso',
  ];

  $userDataOrder = [
    'estado_nombre',
    'municipio_nombre',
    'rfc',
    'direccion',
    'colonia',
    'cp',
    'beneficiario',
    'beneficiario_telefono',
    'beneficiario_02',
    'beneficiario_telefono_02',
    'banco',
    'cuenta',
    'fecha_ingreso',
  ];
@endphp

@section('content')
  <section class="py-10 lg:py-14">
    <div class="max-w-4xl mx-auto space-y-6">
      <header>
        <h1 class="text-3xl font-black text-gray-900">Mi perfil</h1>
        <p class="mt-1 text-sm text-gray-500">Aquí puedes revisar tus datos de cliente y tus datos registrados.</p>
      </header>

      @if($errorMessage)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
          {{ $errorMessage }}
        </div>
      @endif

      <article class="rounded-3xl bg-white ring-1 ring-black/5 shadow-sm p-5 sm:p-7">
        <h2 class="text-lg font-bold text-gray-900">Datos del cliente</h2>
        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
          @foreach (['nombre', 'apellido', 'email', 'telefono'] as $key)
            <div class="rounded-2xl bg-gray-50 p-3">
              <dt class="text-xs uppercase tracking-wide text-gray-400">{{ $labelMap[$key] }}</dt>
              <dd class="mt-1 text-sm font-semibold text-gray-800">{{ $cliente[$key] ?? '—' }}</dd>
            </div>
          @endforeach
        </dl>
      </article>

      <article class="rounded-3xl bg-white ring-1 ring-black/5 shadow-sm p-5 sm:p-7">
        <h2 class="text-lg font-bold text-gray-900">User data</h2>

        @if(empty($userData))
          <p class="mt-4 text-sm text-gray-500">Aún no hay datos registrados.</p>
        @else
          <dl class="mt-4 grid gap-3 sm:grid-cols-2">
            @foreach ($userDataOrder as $key)
              <div class="rounded-2xl bg-gray-50 p-3">
                <dt class="text-xs uppercase tracking-wide text-gray-400">{{ $labelMap[$key] ?? str_replace('_', ' ', $key) }}</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-800">{{ $userData[$key] ?? '—' }}</dd>
              </div>
            @endforeach
          </dl>
        @endif
      </article>
    </div>
  </section>
@endsection
