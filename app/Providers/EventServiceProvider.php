<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Los listeners de eventos para la aplicación.
     *
     * @var array<array-key, array<int, string>>
     */
    protected $listen = [
        // Example:
        // Registered::class => [
        //     SendEmailVerificationNotification::class,
        // ],
    ];

    /**
     * Registra cualquier evento.
     */
    public function boot(): void
    {
        //
    }
}