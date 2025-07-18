<?php

namespace Tests\Feature\Estudiantes;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\{Estudiante, User};

class UpdateEstudianteTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_update_modifies_estudiante()
    {
        $estudiante   = Estudiante::factory()->create();
        $nuevoUsuario = User::factory()->create();

        $admin = User::factory()->create(['rol_id' => 1]);
        $this->actingAs($admin);

        $token = csrf_token();
        $this->withSession(['_token' => $token]);

        $response = $this->put(
            route('catalogos.estudiantes.update', $estudiante),
            [
                '_token'     => $token,
                'usuario_id' => $nuevoUsuario->id,
                'beca_id'    => $estudiante->beca_id,
            ]
        );

        $response->assertRedirect(route('catalogos.estudiantes.index'));

        $this->assertDatabaseHas('estudiantes', [
            'id'         => $estudiante->id,
            'usuario_id' => $nuevoUsuario->id,
        ]);
    }
}
