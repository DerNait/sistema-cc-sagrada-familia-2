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
        $html = '<ul class="navbar-nav d-flex flex-row">';

        foreach ($nodes as $node) {
            if ($node->permission && !Forerunner::allows(
                $node->permission->module->modulo . '.' . $node->permission->permiso
            )) {
                continue;
            }

            $hasChildren = $node->children->isNotEmpty();
            $icon = $node->icon ? "<i class=\"{$node->icon}\"></i>" : '';

            if ($hasChildren) {
                $html .= '<li class="nav-item dropdown mx-2">';
                $html .= '<a class="nav-link dropdown-toggle" href="#" id="' . $node->name . 'Dropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
                $html .= $icon . ' ' . e($node->name) . '</a>';
                $html .= '<ul class="dropdown-menu" aria-labelledby="' . $node->name . 'Dropdown">';
                foreach ($node->children as $child) {
                    if ($child->permission && !Forerunner::allows(
                        $child->permission->module->modulo . '.' . $child->permission->permiso
                    )) {
                        continue;
                    }
                    $href = route($child->route);
                    $html .= '<li><a class="dropdown-item" href="' . $href . '">' . e($child->name) . '</a></li>';
                }
                $html .= '</ul></li>';
            } else {
                $href = route($node->route);
                $html .= '<li class="nav-item mx-2"><a class="nav-link" href="' . $href . '">' . $icon . ' ' . e($node->name) . '</a></li>';
            }
        }

        $html .= '</ul>';
        return $html;
    }
}