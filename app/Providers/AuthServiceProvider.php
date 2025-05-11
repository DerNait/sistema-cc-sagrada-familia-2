<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * El policy map de la aplicación.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Ejemplo:
        // Post::class => PostPolicy::class,
    ];

    /**
     * Registra cualquier servicio de autenticación / autorización.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Ejemplo simple de Gate
        Gate::define('is-root', fn (User $user) => $user->rol_id === 1);
    }
}