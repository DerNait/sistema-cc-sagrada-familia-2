<?php

namespace Tests\Feature\Estudiantes;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\{User,Beca,Estudiante};

class StoreEstudianteTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_store_creates_estudiante()
    {
        $admin = User::factory()->create(['rol_id' => 1]);
        $this->actingAs($admin);

        $user = User::factory()->create();
        $beca = Beca::factory()->create();

        $token = csrf_token();
        $this->withSession(['_token' => $token]);

        $response = $this->post(
            route('admin.estudiantes.store'),
            [
                '_token'     => $token,
                'usuario_id' => $user->id,
                'beca_id'    => $beca->id,
            ]
        );

        $response->assertRedirect(route('admin.estudiantes.index'));
        $this->assertDatabaseHas('estudiantes', [
            'usuario_id' => $user->id,
            'beca_id'    => $beca->id,
        ]);
    }
}

