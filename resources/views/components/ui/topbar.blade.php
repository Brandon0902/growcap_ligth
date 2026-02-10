<header class="sticky top-0 z-40 backdrop-blur bg-white/70 border-b border-black/5">
  <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
      <div class="h-10 w-10 overflow-hidden rounded-2xl bg-gray-100 ring-1 ring-black/5 flex items-center justify-center">
        <img src="{{ asset('images/growcap-logo.svg') }}" alt="Logo Growcap" class="h-full w-full object-cover">
      </div>
      <div class="leading-tight">
        <div class="text-sm font-extrabold tracking-wide">GROWCAP</div>
        <div class="text-[10px] text-gray-400 -mt-0.5">BY MONEYGO</div>
      </div>
    </a>

    <div class="flex items-center gap-3">
      <button class="h-11 w-11 rounded-2xl bg-white shadow-sm ring-1 ring-black/5 flex items-center justify-center hover:bg-purple-50 transition"
              aria-label="Apps">
        <span class="text-purple-700">▦</span>
      </button>
      <a href="{{ route('perfil.show') }}"
         class="h-11 w-11 rounded-2xl bg-white shadow-sm ring-1 ring-black/5 flex items-center justify-center hover:bg-purple-50 transition"
         aria-label="Perfil">
        <span class="text-purple-700">👤</span>
      </a>
    </div>

  </div>
</header>
