@extends('layouts.app')

@section('title', 'Growcap | Inicio')

@section('content')
  <section class="py-10 lg:py-14 text-center">
    <div class="text-[11px] tracking-[0.35em] text-gray-400">PATRIMONIO TOTAL</div>

    <div class="mt-3 text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900">
      $12,450.00
    </div>

    <h2 class="mt-6 text-2xl sm:text-3xl lg:text-4xl font-extrabold text-purple-700">
      ¿Cómo quieres crecer hoy?
    </h2>
  </section>

  <section class="pb-10 lg:pb-14">
    <div class="grid gap-6 sm:gap-7 lg:gap-8 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">

      <x-ui.feature-card
        title="Ahorro Flexible"
        subtitle="Tu red de seguridad financiera"
        icon="🐷"
        icon-bg="bg-emerald-50"
        href="{{ route('ahorro.index') }}"
      />

      <x-ui.feature-card
        title="Inversión Plazo"
        subtitle="Multiplica tu patrimonio"
        icon="📈"
        icon-bg="bg-purple-50"
        href="{{ route('inversion.index') }}"
      />

      <x-ui.feature-card
        title="Préstamos"
        subtitle="Liquidez inmediata"
        icon="💳"
        icon-bg="bg-orange-50"
        href="{{ route('prestamos.index') }}"
      />

    </div>

    {{-- Botón flotante estilo WhatsApp (opcional) --}}
    <a href="#"
       class="fixed bottom-24 md:bottom-10 right-6 md:right-10 h-14 w-14 rounded-full
              bg-emerald-500 shadow-lg flex items-center justify-center text-white text-2xl">
      💬
    </a>
  </section>
@endsection
