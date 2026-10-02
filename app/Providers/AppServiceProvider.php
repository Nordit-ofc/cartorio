<?php

namespace App\Providers;

use App\View\Composers\ChatWidgetComposer;
use App\View\Composers\SiteNavbarComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('partials.site-navbar', SiteNavbarComposer::class);
        View::composer('partials.chat-widget', ChatWidgetComposer::class);
    }
}
