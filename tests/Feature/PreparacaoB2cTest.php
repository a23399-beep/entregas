<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WooOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PreparacaoB2cTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('O driver pdo_sqlite nao esta instalado neste ambiente.');
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_cliente_b2c_aparece_na_preparacao_sem_mostrar_tudo(): void
    {
        Carbon::setTestNow('2026-09-01 09:00:00');

        $admin = User::factory()->admin()->create();

        // Subscricao a quarta, sem colaborador atribuido nenhum.
        WooOrder::factory()->create([
            'woo_id' => 321,
            'source_type' => 'subscription',
            'status' => 'active',
            'billing_name' => 'Cliente Sem Rota',
            'dia_entrega' => 'quarta',
            'ciclo_entrega' => 'quinzenal',
            'first_delivery_at' => '2026-08-12',
            'delivery_dates' => [],
            'scheduled_delivery_at' => null,
            'postponed_until' => null,
        ]);

        // Sem "Mostrar tudo": o cliente tem de aparecer na mesma.
        $this->actingAs($admin)
            ->get(route('preparacao.index', ['inicio' => '2026-09-08', 'fim' => '2026-09-10']))
            ->assertOk()
            ->assertSee('Cliente Sem Rota');
    }
}
