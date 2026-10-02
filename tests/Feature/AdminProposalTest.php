<?php

namespace Tests\Feature;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\User;
use App\Support\Roles;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SysadminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

/** SPEC-005 — login, papéis e painel do sysadmin para analisar as propostas. */
class AdminProposalTest extends TestCase
{
    use BuildsFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBase();
    }

    public function test_rf09_seeder_creates_sysadmin_from_env_without_tenant(): void
    {
        config(['nordit.sysadmin' => ['name' => 'Eduardo', 'email' => 'Admin@Nordit.test', 'password' => 'senha-forte-123']]);

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class); // idempotente

        $user = User::sole();
        $this->assertSame('admin@nordit.test', $user->email);
        $this->assertNull($user->tenant_id);
        $this->assertTrue($user->hasRole(Roles::SUPER_ADMIN));
        $this->assertNotNull($user->public_id);
    }

    public function test_rf09_seeder_uses_default_email_and_generates_password(): void
    {
        config(['nordit.sysadmin' => ['name' => 'Sysadmin nordit', 'email' => 'sysadmin@nordit.com.br', 'password' => null]]);

        $this->artisan('db:seed', ['--class' => SysadminSeeder::class])
            ->expectsOutputToContain('Senha gerada')
            ->assertSuccessful();

        $user = User::sole();
        $this->assertSame('sysadmin@nordit.com.br', $user->email);
        $this->assertTrue($user->isSuperAdmin());
        $originalHash = $user->password;

        // Rodar de novo não troca a senha nem mostra outra.
        $this->artisan('db:seed', ['--class' => SysadminSeeder::class])
            ->doesntExpectOutputToContain('Senha gerada')
            ->assertSuccessful();
        $this->assertSame($originalHash, $user->fresh()->password);
    }

    public function test_rf09_sysadmin_password_can_be_reset_by_command(): void
    {
        $sysadmin = $this->makeSysadmin();
        $originalHash = $sysadmin->password;

        $this->artisan('sysadmin:reset-password', ['email' => 'sysadmin@nordit.test'])
            ->expectsOutputToContain('Nova senha')
            ->assertSuccessful();
        $this->assertNotSame($originalHash, $sysadmin->fresh()->password);

        $this->artisan('sysadmin:reset-password', ['email' => 'ninguem@nordit.test'])->assertFailed();
    }

    public function test_rf09_the_three_roles_exist(): void
    {
        foreach (Roles::all() as $role) {
            $this->assertDatabaseHas('roles', ['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/admin/propostas')->assertRedirect('/entrar');
    }

    public function test_login_with_valid_credentials_lands_on_proposals(): void
    {
        $this->makeSysadmin();

        $this->post('/entrar', ['email' => 'sysadmin@nordit.test', 'password' => 'senha-forte-123'])
            ->assertRedirect('/admin');

        $this->get('/admin')->assertRedirect('/admin/propostas');
        $this->assertNotNull(User::sole()->last_login_at);
    }

    public function test_login_with_wrong_password_shows_generic_message(): void
    {
        $this->makeSysadmin();

        $this->from('/entrar')
            ->post('/entrar', ['email' => 'sysadmin@nordit.test', 'password' => 'errada'])
            ->assertRedirect('/entrar')
            ->assertSessionHasErrors(['email' => 'E-mail ou senha inválidos.']);

        $this->assertGuest();
    }

    public function test_inactive_user_session_is_ended(): void
    {
        $sysadmin = $this->makeSysadmin(['active' => false]);

        $this->actingAs($sysadmin)->get('/admin/propostas')->assertRedirect('/entrar');
        $this->assertGuest();
    }

    public function test_rf10_tenant_users_cannot_see_proposals(): void
    {
        foreach ([Roles::TENANT_ADMIN, Roles::TENANT_USER] as $index => $role) {
            $user = $this->makeTenantUser($role, ['name' => "Instituição {$index}"]);
            $user->forceFill(['email' => "{$role}@exemplo.test"])->save();

            $this->actingAs($user)->get('/admin/propostas')->assertForbidden();
            $this->actingAs($user)->get('/admin')->assertOk()->assertSee('Área do cliente em construção');
        }
    }

    public function test_rf10_sysadmin_lists_and_filters_proposals(): void
    {
        $this->post('/solicitar-proposta', $this->validProposalPayload());
        $this->post('/solicitar-proposta', $this->validProposalPayload([
            'contact_name' => 'João Lima',
            'organization' => 'Registro de Imóveis de Olinda',
            'email' => 'joao@olinda.test',
            'interest_areas' => [],
        ]));

        $this->actingAs($this->makeSysadmin())
            ->get('/admin/propostas')
            ->assertOk()
            ->assertSee('Maria Souza')
            ->assertSee('João Lima')
            ->assertSee('(81) 99999-0000');

        $this->get('/admin/propostas?area=lgpd-conformidade')
            ->assertSee('Maria Souza')
            ->assertDontSee('João Lima');

        $this->get('/admin/propostas?q=olinda')
            ->assertSee('João Lima')
            ->assertDontSee('Maria Souza');
    }

    public function test_rf11_sysadmin_changes_status_and_public_tracking_follows(): void
    {
        $this->post('/solicitar-proposta', $this->validProposalPayload());
        $proposal = Proposal::sole();
        $sysadmin = $this->makeSysadmin();

        $this->actingAs($sysadmin)
            ->patch("/admin/propostas/{$proposal->public_id}/status", ['status' => 'in_review', 'note' => 'Ligar na segunda'])
            ->assertRedirect("/admin/propostas/{$proposal->public_id}")
            ->assertSessionHas('success');

        $proposal->refresh();
        $this->assertSame(ProposalStatus::InReview, $proposal->status);
        $this->assertDatabaseHas('proposal_status_changes', [
            'proposal_id' => $proposal->id,
            'status' => 'in_review',
            'note' => 'Ligar na segunda',
            'user_id' => $sysadmin->id,
        ]);

        $this->get("/admin/propostas/{$proposal->public_id}")
            ->assertOk()
            ->assertSee('Ligar na segunda')
            ->assertSee('Formulário do site');

        auth()->logout();
        $this->get('/consultar-solicitacao?protocolo='.$proposal->protocol)
            ->assertSee('Em análise')
            ->assertDontSee('Ligar na segunda'); // observação é interna
    }

    public function test_rf11_invalid_status_is_rejected(): void
    {
        $this->post('/solicitar-proposta', $this->validProposalPayload());
        $proposal = Proposal::sole();

        $this->actingAs($this->makeSysadmin())
            ->patch("/admin/propostas/{$proposal->public_id}/status", ['status' => 'aprovada'])
            ->assertSessionHasErrors(['status' => 'Status inválido.']);
    }

    public function test_proposal_urls_use_public_id_not_incremental_id(): void
    {
        $this->post('/solicitar-proposta', $this->validProposalPayload());
        $proposal = Proposal::sole();

        $this->actingAs($this->makeSysadmin())
            ->get("/admin/propostas/{$proposal->id}")
            ->assertNotFound();
    }
}
