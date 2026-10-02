<?php

namespace Tests\Feature;

use App\Enums\InstitutionType;
use App\Enums\ProposalSource;
use App\Enums\ProposalStatus;
use App\Mail\NewProposalReceived;
use App\Models\Proposal;
use App\Services\ProposalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

/** SPEC-005 — formulário público de proposta, agora persistido no banco. */
class ProposalSubmissionTest extends TestCase
{
    use BuildsFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    public function test_rf01_form_lists_interest_areas_without_institution_type(): void
    {
        $this->get('/solicitar-proposta')
            ->assertOk()
            ->assertSee('Solicitar proposta')
            ->assertDontSee('Tipo de instituição')
            ->assertDontSee('Prefeitura')
            ->assertSee('Backup &amp; Continuidade', false)
            ->assertSee('Governança de TI');
    }

    public function test_root_shows_coming_soon_with_chat_and_form_alternative(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Disponível em breve')
            ->assertSee('data-chat-open="proposal"', false)
            ->assertSee('Prefiro preencher o formulário')
            ->assertSee('window.NORDIT_CHAT', false)
            ->assertSee(route('proposals.store'), false);
    }

    public function test_rf12_chat_submission_returns_protocol_as_json(): void
    {
        $this->postJson('/solicitar-proposta', $this->validProposalPayload(['source' => 'chat']))
            ->assertCreated()
            ->assertJson(['protocol' => 'NRD-'.now()->format('Y').'-0001', 'duplicate' => false])
            ->assertJsonPath('tracking_url', route('proposals.tracking', ['protocolo' => 'NRD-'.now()->format('Y').'-0001']));

        $this->assertSame(ProposalSource::Chat, Proposal::sole()->source);
    }

    public function test_rf12_chat_duplicate_returns_existing_protocol(): void
    {
        $this->postJson('/solicitar-proposta', $this->validProposalPayload(['source' => 'chat']));

        $this->postJson('/solicitar-proposta', $this->validProposalPayload(['source' => 'chat']))
            ->assertOk()
            ->assertJson(['protocol' => Proposal::sole()->protocol, 'duplicate' => true]);
    }

    public function test_rf12_chat_validation_errors_come_back_in_portuguese(): void
    {
        $this->postJson('/solicitar-proposta', $this->validProposalPayload(['email' => 'invalido', 'source' => 'chat']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email' => 'Informe um e-mail válido.']);

        $this->assertDatabaseCount('proposals', 0);
    }

    public function test_form_submission_is_recorded_as_form_source(): void
    {
        $this->post('/solicitar-proposta', $this->validProposalPayload());

        $this->assertSame(ProposalSource::Form, Proposal::sole()->source);
    }

    public function test_rf02_submission_persists_with_protocol_status_and_areas(): void
    {
        $this->post('/solicitar-proposta', $this->validProposalPayload())
            ->assertRedirect('/solicitar-proposta/recebida');

        $proposal = Proposal::with('interestAreas', 'statusChanges')->sole();
        $year = now()->format('Y');

        $this->assertSame("NRD-{$year}-0001", $proposal->protocol);
        $this->assertSame(ProposalStatus::Received, $proposal->status);
        $this->assertSame('maria@exemplo.com.br', $proposal->email);
        $this->assertSame('81999990000', $proposal->phone);
        $this->assertSame(InstitutionType::Cartorio, $proposal->institution_type);
        $this->assertEqualsCanonicalizing(['backup-continuidade', 'lgpd-conformidade'], $proposal->interestAreas->pluck('slug')->all());
        $this->assertCount(1, $proposal->statusChanges);
        $this->assertNull($proposal->statusChanges->first()->user_id);

        $this->followingRedirects()
            ->post('/solicitar-proposta', $this->validProposalPayload(['email' => 'outra@exemplo.com.br']))
            ->assertSee("NRD-{$year}-0002")
            ->assertSee('Solicitação recebida!');
    }

    public function test_rf01_required_fields_are_validated_in_portuguese(): void
    {
        $this->from('/solicitar-proposta')
            ->post('/solicitar-proposta', ['email' => 'invalido', 'phone' => '123'])
            ->assertRedirect('/solicitar-proposta')
            ->assertSessionHasErrors([
                'contact_name' => 'Informe seu nome completo.',
                'organization' => 'Informe a serventia.',
                'email' => 'Informe um e-mail válido.',
                'phone' => 'Informe um telefone com DDD, ex.: (81) 99999-0000.',
            ]);

        $this->assertDatabaseCount('proposals', 0);
    }

    public function test_optional_fields_can_be_left_blank(): void
    {
        $this->post('/solicitar-proposta', $this->validProposalPayload(['phone' => '', 'message' => '', 'interest_areas' => []]))
            ->assertSessionHasNoErrors();

        $proposal = Proposal::sole();
        $this->assertNull($proposal->phone);
        $this->assertNull($proposal->message);
    }

    public function test_rf08_open_duplicate_returns_existing_protocol(): void
    {
        $this->post('/solicitar-proposta', $this->validProposalPayload());
        $first = Proposal::sole();

        $this->followingRedirects()
            ->post('/solicitar-proposta', $this->validProposalPayload(['email' => 'maria@exemplo.com.br']))
            ->assertSee($first->protocol)
            ->assertSee('Já existe uma solicitação em aberto');

        $this->assertDatabaseCount('proposals', 1);
    }

    public function test_rf08_new_request_allowed_after_proposal_was_sent(): void
    {
        $this->post('/solicitar-proposta', $this->validProposalPayload());
        Proposal::sole()->forceFill(['status' => ProposalStatus::ProposalSent])->save();

        $this->post('/solicitar-proposta', $this->validProposalPayload());

        $this->assertDatabaseCount('proposals', 2);
    }

    public function test_rf07_team_is_notified_by_email(): void
    {
        Mail::fake();
        config(['nordit.leads_mail_to' => 'comercial@nordit.test']);

        $this->post('/solicitar-proposta', $this->validProposalPayload());

        Mail::assertQueued(NewProposalReceived::class, fn (NewProposalReceived $mail) => $mail->hasTo('comercial@nordit.test')
            && $mail->proposal->is(Proposal::sole()));
    }

    public function test_rf07_no_email_when_recipient_is_not_configured(): void
    {
        Mail::fake();
        config(['nordit.leads_mail_to' => null]);

        $this->post('/solicitar-proposta', $this->validProposalPayload());

        Mail::assertNothingOutgoing();
        $this->assertDatabaseCount('proposals', 1);
    }

    public function test_honeypot_submission_is_silently_discarded(): void
    {
        $this->followingRedirects()
            ->post('/solicitar-proposta', $this->validProposalPayload(['website' => 'http://spam.test']))
            ->assertSee('Solicitação recebida!');

        $this->assertDatabaseCount('proposals', 0);
    }

    public function test_received_page_without_submission_goes_back_to_form(): void
    {
        $this->get('/solicitar-proposta/recebida')->assertRedirect('/solicitar-proposta');
    }

    public function test_rf03_tracking_shows_current_status(): void
    {
        $this->post('/solicitar-proposta', $this->validProposalPayload());
        $protocol = Proposal::sole()->protocol;

        $this->get('/consultar-solicitacao?protocolo='.strtolower($protocol))
            ->assertOk()
            ->assertSee($protocol)
            ->assertSee('Maria Souza — 1º Ofício de Notas de Recife')
            ->assertSee('timeline__step--current', false);

        $this->get('/solicitacao/'.$protocol)->assertRedirect('/consultar-solicitacao?protocolo='.$protocol);
    }

    public function test_rf03_closed_statuses_show_closing_message_and_reached_steps(): void
    {
        $this->post('/solicitar-proposta', $this->validProposalPayload());
        $proposal = Proposal::sole();
        $sysadmin = $this->makeSysadmin();
        $service = app(ProposalService::class);

        // Arquivada logo após "Recebida": a linha do tempo não avança além da primeira etapa.
        $service->changeStatus($proposal, ProposalStatus::Archived, null, $sysadmin);
        $response = $this->get('/consultar-solicitacao?protocolo='.$proposal->protocol)
            ->assertSee('Esta solicitação foi encerrada');
        $this->assertSame(1, substr_count($response->getContent(), 'timeline__step--done'));

        $service->changeStatus($proposal, ProposalStatus::ProposalSent, null, $sysadmin);
        $service->changeStatus($proposal, ProposalStatus::Won, null, $sysadmin);
        $response = $this->get('/consultar-solicitacao?protocolo='.$proposal->protocol)
            ->assertSee('Proposta aceita!');
        $this->assertSame(3, substr_count($response->getContent(), 'timeline__step--done'));
    }

    public function test_rf03_unknown_protocol_shows_friendly_message(): void
    {
        $this->get('/consultar-solicitacao?protocolo=NRD-1999-9999')
            ->assertOk()
            ->assertSee('Não encontramos nenhuma solicitação com esse protocolo');
    }
}
