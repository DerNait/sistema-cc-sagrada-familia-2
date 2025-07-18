<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Validator;

class RegisterControllerTest extends TestCase
{
    protected function getBaseData(): array
    {
        return [
            'name' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'password' => 'Test1234@',
            'password_confirmation' => 'Test1234@',
            'fecha_nacimiento' => '1990-01-01',
            'role' => 1,
        ];
    }

    public function test_password_fails_without_uppercase()
    {
        $data = $this->getBaseData();
        $data['password'] = $data['password_confirmation'] = 'test1234@';

        $controller = new RegisterController();
        $validator = $controller->runValidator($data);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_password_fails_without_number()
    {
        $data = $this->getBaseData();
        $data['password'] = $data['password_confirmation'] = 'Testabcd@';

        $controller = new RegisterController();
        $validator = $controller->runValidator($data);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_password_fails_without_special_character()
    {
        $data = $this->getBaseData();
        $data['password'] = $data['password_confirmation'] = 'Test1234';

        $controller = new RegisterController();
        $validator = $controller->runValidator($data);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_password_passes_all_requirements()
    {
        $data = $this->getBaseData();
        $data['password'] = $data['password_confirmation'] = 'ValidPass1@';

        $controller = new RegisterController();
        $validator = $controller->runValidator($data);
        $this->assertFalse($validator->fails());
    }
}

