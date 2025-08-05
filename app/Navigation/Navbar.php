<?php
namespace App\Navigation;

use App\Support\Forerunner\Forerunner;
use App\Models\Menu;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Navbar
{
    public static function render(): HtmlString
    {
        $items = Menu::with('children', 'permission.module')
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();

        $html = self::traverse($items);
        return new HtmlString($html);
    }

    private static function traverse($nodes): string
    {
        $html = '<ul class="nav nav-pills gap-2">';

        foreach ($nodes as $node) {

            // ----- 1. Evalúa permiso del nodo -----------
            $ability = optional($node->permission)->module->modulo
                    . '.'
                    . optional($node->permission)->permiso;
            $canViewNode = !$node->permission || Forerunner::allows($ability);

            // ----- 2. Filtra hijos recursivamente -------
            $children = $node->children
                ->filter(function ($child) {
                    $ability = optional($child->permission)->module->modulo
                            . '.'
                            . optional($child->permission)->permiso;
                    return !$child->permission || Forerunner::allows($ability);
                });

            // Si el padre NO tiene permiso y tampoco quedó ningún hijo => omite todo
            if (!$canViewNode && $children->isEmpty()) {
                continue;
            }

            // Icono + active
            $icon = $node->icon ? "<i class=\"{$node->icon} me-1\"></i>" : '';
            $currentRoute = Route::currentRouteName();
            $routeBase = explode('.', $node->route)[0];
            $isActive = Str::startsWith($currentRoute, $routeBase) ? 'active' : '';

            // ----- 3. Render padre con dropdown si hay hijos -----
            if ($children->isNotEmpty()) {
                $html .= '
                    <li class="nav-item dropdown">
                        <a class="nav-link '.$isActive.' dropdown-toggle" href="#" role="button">
                            '.$icon.e($node->name).'
                        </a>
                        <ul class="dropdown-menu shadow-sm">';

                        foreach ($children as $child) {
                            $href = route($child->route);
                            $html .= '<li><a class="dropdown-item" href="'.$href.'">'.e($child->name).'</a></li>';
                        }

                $html .= '</ul></li>';

            // ----- 4. Render padre simple si no hay hijos -----
            } elseif ($canViewNode) {
                $href = route($node->route);
                $html .= '
                    <li class="nav-item">
                        <a class="nav-link '.$isActive.'" href="'.$href.'">
                            '.$icon.e($node->name).'
                        </a>
                    </li>';
            }
        }

        $html .= '</ul>';
        return $html;
    }
}