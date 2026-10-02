<?php

namespace App\View\Composers;

use App\Models\InterestArea;
use Illuminate\View\View;

/**
 * Monta a configuração do chat (config/chat.php) para o JS: resolve rotas em URLs e injeta as
 * opções das perguntas (áreas de interesse do banco).
 */
class ChatWidgetComposer
{
    public function compose(View $view): void
    {
        $config = config('chat');

        $choices = [
            'interest_areas' => InterestArea::query()->active()->get()
                ->map(fn (InterestArea $area) => ['value' => $area->id, 'label' => $area->name])
                ->all(),
        ];

        $config['proposal']['questions'] = array_map(
            fn (array $question) => $question + ['choices' => $choices[$question['field']] ?? []],
            $config['proposal']['questions']
        );

        $view->with('chatConfig', [
            'typingDelay' => $config['typing_delay_ms'],
            'header' => $config['header'],
            'steps' => $config['steps'],
            'proposal' => $config['proposal'],
            'urls' => [
                'submit' => route('proposals.store'),
                'form' => route('proposals.create'),
                'tracking' => route('proposals.tracking'),
                'whatsapp' => 'https://wa.me/'.config('nordit.whatsapp_number').'?text='.rawurlencode($config['whatsapp_message']),
            ],
        ]);
    }
}
