{{-- Navbar do site público (antes TheNavbar.vue). $serviceAreas vem do SiteNavbarComposer. --}}
@php($navLinks = collect(config('site.nav_links'))->filter(fn ($link) => Route::has($link['route'])))
<header class="site-header">
    <div class="site-nav">
        <div class="site-nav__left">
            <a href="{{ route('home') }}" aria-label="Ir para a página inicial">
                <span class="brand-logo site-nav__logo">nord<span class="brand-logo__accent">it</span></span>
            </a>

            <nav class="site-nav__links" aria-label="Principal">
                {{-- Abre no hover e no foco do teclado (só CSS). --}}
                <div class="site-nav__services">
                    <button class="site-nav__services-toggle" type="button" aria-haspopup="true">
                        Serviços <x-icon name="chevron-down" size="12" />
                    </button>
                    <div class="site-nav__dropdown site-nav__dropdown--services">
                        <div class="services-menu">
                            @foreach($serviceAreas as $area)
                                <div class="services-menu__item">
                                    <span class="services-menu__icon"><x-icon :name="$area->icon" size="21" /></span>
                                    <span>
                                        <span class="services-menu__title">{{ $area->name }}</span>
                                        <span class="services-menu__description">{{ $area->description }}</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @foreach($navLinks as $link)
                    <a href="{{ route($link['route']) }}" class="site-nav__link">{{ $link['label'] }}</a>
                @endforeach
            </nav>
        </div>

        <div class="site-nav__right">
            <div class="dropdown">
                <button class="site-nav__login-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Entrar <x-icon name="chevron-down" size="12" class="site-nav__login-chevron" />
                </button>
                <div class="dropdown-menu dropdown-menu-end login-menu">
                    @foreach(config('site.login_menu') as $item)
                        <a href="{{ route($item['route']) }}" class="login-menu__item">
                            <span class="login-menu__icon @if($item['emphasis']) login-menu__icon--emphasis @endif">
                                <x-icon :name="$item['icon']" size="18" />
                            </span>
                            <span>
                                <span class="login-menu__title">{{ $item['title'] }}</span>
                                <span class="login-menu__description">{{ $item['description'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('proposals.create') }}" class="nd-btn nd-btn--primary nd-btn--md site-nav__cta">Solicitar proposta</a>

            <button class="site-nav__burger" type="button" data-bs-toggle="collapse" data-bs-target="#site-mobile-menu" aria-controls="site-mobile-menu" aria-expanded="false" aria-label="Abrir menu">
                <x-icon name="menu" size="24" />
            </button>
        </div>

        <nav id="site-mobile-menu" class="collapse site-nav__mobile-menu" aria-label="Menu móvel">
            @foreach($navLinks as $link)
                <a href="{{ route($link['route']) }}">{{ $link['label'] }}</a>
            @endforeach
            <a href="{{ route('proposals.create') }}">Solicitar proposta</a>
            <a href="{{ route('proposals.tracking') }}">Consultar solicitação</a>
        </nav>
    </div>
</header>
