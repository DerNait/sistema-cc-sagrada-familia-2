<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Empleado;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'apellido'          => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[a-z]/','regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
            'fecha_nacimiento'  => ['required', 'date'],
            'role'              => ['required', 'exists:roles,id'],
            'salario' => ['nullable', 'numeric', 'min:0'],
            'fecha_registro' => ['nullable', 'date'],
        ],
        [
            'name.regex' => 'El campo nombre solo puede contener letras y espacios.',
            'apellido.regex' => 'El campo apellido solo puede contener letras y espacios.',
            'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
        ]);
    }

    public function showRegistrationForm()
    {
        $roles = Role::all();
        return view('auth.register', compact('roles'));
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        \Log::info('Creating new user...');
        
        $rol = Role::findOrFail($data['role']);
        
        return User::create([
            'rol_id'            => $rol->id,
            'name'              => $data['name'],
            'apellido'          => $data['apellido'],
            'email'             => $data['email'],
            'fecha_registro'    => Carbon::now(),
            'fecha_nacimiento'  => $data['fecha_nacimiento'],
            'password'          => Hash::make($data['password']),
        ]);

        if (!empty($data['salario'])) {
            Empleado::create([
                'usuario_id' => $user->id,
                'salario_base' => $data['salario'],
            ]);
        }

        return $user;
    }

    public function runValidator(array $data)
    {
        return $this->validator($data);
    }
}
