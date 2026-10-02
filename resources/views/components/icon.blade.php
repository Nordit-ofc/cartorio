@props(['name', 'size' => 24])
{{-- Ícone do sistema visual (App\Support\Icons). Substitui o AppIcon.vue. --}}
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" {{ $attributes }}>{!! \App\Support\Icons::PATHS[$name] ?? '' !!}</svg>
