<?php
namespace App\Navigation;

use App\Support\Forerunner\Forerunner;
use App\Models\Menu;
use Illuminate\Support\HtmlString;

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
        // nav.nav-pills dará la base de Bootstrap
        $html = '<ul class="nav nav-pills gap-2">';

        foreach ($nodes as $node) {

            // … (misma verificación de permisos) …

            $hasChildren = $node->children->isNotEmpty();
            $icon = $node->icon ? "<i class=\"{$node->icon} me-1\"></i>" : '';

            // ¿Ruta actual? => clase active para resaltar
            $isActive = request()->routeIs($node->route . '*') ? 'active' : '';

            if ($hasChildren) {
                $html .= '
                    <li class="nav-item dropdown">
                        <a class="nav-link '.$isActive.' dropdown-toggle" href="#" data-bs-toggle="dropdown" role="button">
                            '.$icon.e($node->name).'
                        </a>
                        <ul class="dropdown-menu shadow-sm">';
                            foreach ($node->children as $child) {
                                // … permisos hijo …
                                $href = route($child->route);
                                $html .= '<li><a class="dropdown-item" href="'.$href.'">'.e($child->name).'</a></li>';
                            }
                $html .= '</ul></li>';
            } else {
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