{{-- Cabeçalho centralizado das páginas internas (antes PageHeader.vue). --}}
<header class="page-header">
    <h1 class="page-header__title">{{ $title }}</h1>
    @if(! empty($text))
        <p class="page-header__text">{{ $text }}</p>
    @endif
</header>
