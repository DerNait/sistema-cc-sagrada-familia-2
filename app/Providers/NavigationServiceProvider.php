<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use App\Navigation\Sidebar;

class NavigationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../database/migrations/2025_05_09_040059_create_menus_table.php'
                => database_path('migrations/2025_05_09_040059_create_menus_table.php'),
        ], 'navigation-migrations');

        AliasLoader::getInstance()->alias('Sidebar', Sidebar::class);
    }

    public function register()
    {
        // TODO: registrar comandos artisan tipo `make:navigation`
    }
}