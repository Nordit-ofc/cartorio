<?php

namespace App\Http\Requests;

use App\Enums\ProposalSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Solicitação de proposta (SPEC-002 · RF-01). Qualquer visitante pode enviar, pelo formulário
 * ou pelo chat (SPEC-006, JSON) — as mesmas regras e mensagens valem para os dois.
 */
class StoreProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'contact_name' => ['required', 'string', 'max:120'],
            'organization' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160'],
            // Fixo (10 dígitos) ou celular (11), com ou sem máscara.
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\D*(\d\D*){10,11}$/'],
            'interest_areas' => ['nullable', 'array'],
            'interest_areas.*' => ['integer', Rule::exists('interest_areas', 'id')->where('active', true)],
            'message' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', Rule::enum(ProposalSource::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'contact_name.required' => 'Informe seu nome completo.',
            'organization.required' => 'Informe a serventia.',
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'phone.regex' => 'Informe um telefone com DDD, ex.: (81) 99999-0000.',
            'interest_areas.*.exists' => 'Selecione apenas áreas da lista.',
            '*.max' => 'Texto muito longo (máximo de :max caracteres).',
        ];
    }
}
