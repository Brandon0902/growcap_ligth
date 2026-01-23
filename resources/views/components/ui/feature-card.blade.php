@props([
  'title' => 'Título',
  'subtitle' => '',
  'icon' => '✨',
  'href' => '#',
  'iconBg' => 'bg-purple-50',
  'arrowColor' => 'text-purple-700',
])

<a href="{{ $href }}"
   class="group rounded-3xl bg-white/70 backdrop-blur shadow-sm ring-1 ring-black/5
          p-7 sm:p-8 transition hover:shadow-md hover:bg-white">

  <div class="h-12 w-12 rounded-2xl {{ $iconBg }} flex items-center justify-center">
    <span class="text-lg">{{ $icon }}</span>
  </div>

  <div class="mt-6 text-2xl font-extrabold text-gray-900">
    {{ $title }}
  </div>

  @if($subtitle)
    <div class="mt-2 text-gray-500">
      {{ $subtitle }}
    </div>
  @endif

  <div class="mt-6 flex justify-end">
    <div class="h-11 w-11 rounded-full bg-gray-100 flex items-center justify-center
                transition group-hover:bg-purple-100">
      <span class="{{ $arrowColor }} text-xl">→</span>
    </div>
  </div>
</a>
