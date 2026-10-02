<?php

namespace App\DTOs;

use App\Enums\InstitutionType;
use App\Enums\ProposalSource;

/** Dados já validados da solicitação (formulário ou chat), normalizados antes do Model. */
final readonly class ProposalDTO
{
    /**
     * @param  list<int>  $interestAreaIds
     */
    public function __construct(
        public string $contactName,
        public string $organization,
        public string $email,
        public ?string $phone,
        public ?string $message,
        public array $interestAreaIds,
        public ProposalSource $source = ProposalSource::Form,
        public InstitutionType $institutionType = InstitutionType::Cartorio,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}

    /** @param  array<string, mixed>  $data  saída de StoreProposalRequest::validated() */
    public static function fromValidated(array $data, ?string $ipAddress, ?string $userAgent): self
    {
        $phone = preg_replace('/\D/', '', (string) ($data['phone'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));

        return new self(
            contactName: trim($data['contact_name']),
            organization: trim($data['organization']),
            email: mb_strtolower(trim($data['email'])),
            phone: $phone !== '' ? $phone : null,
            message: $message !== '' ? $message : null,
            interestAreaIds: array_map('intval', $data['interest_areas'] ?? []),
            source: ProposalSource::tryFrom($data['source'] ?? '') ?? ProposalSource::Form,
            ipAddress: $ipAddress,
            userAgent: $userAgent !== null ? mb_substr($userAgent, 0, 255) : null,
        );
    }

    /** @return array<string, mixed> */
    public function attributes(): array
    {
        return [
            'contact_name' => $this->contactName,
            'organization' => $this->organization,
            'institution_type' => $this->institutionType,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'source' => $this->source,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ];
    }
}
