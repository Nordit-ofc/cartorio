<?php

namespace App\View\Composers;

use App\Models\InterestArea;
use Illuminate\View\View;

/** Menu "Serviços" da navbar pública: as mesmas áreas do formulário de proposta. */
class SiteNavbarComposer
{
    public function compose(View $view): void
    {
        $view->with('serviceAreas', InterestArea::query()->active()->get());
    }
}
