<?php
namespace App\Navigation;

use App\Support\Forerunner\Forerunner;
use App\Models\Menu;
use Illuminate\Support\HtmlString;

class Sidebar
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

    private static function traverse($nodes, $depth = 0): string
    {
        $html = '';

        foreach ($nodes as $node) {
            // ¿Puede verlo el usuario?
            if ($node->permission && !Forerunner::allows(
                $node->permission->module->modulo . '.' . $node->permission->permiso
            )) {
                continue;
            }

            $hasChildren = $node->children->isNotEmpty();
            $margin      = "style=\"margin-left:" . ($depth * 15) . "px\"";
            $icon        = $node->icon ? "<i class=\"nav-icon {$node->icon}\"></i>" : '';

            if ($hasChildren) {
                $html .= "<li class=\"nav-item has-treeview\" {$margin}>
                            <a href=\"#\" class=\"nav-link\">{$icon}<p class=\"ml-2\">{$node->name}
                            <i class=\"nav-arrow fas fa-angle-right right\"></i></p></a>
                            <ul class=\"nav nav-treeview\">";
                $html .= self::traverse($node->children, $depth + 1);
                $html .= "</ul></li>";
            } else {
                $active = request()->routeIs($node->route) ? 'active' : '';
                $href   = route($node->route);
                $html  .= "<li class=\"nav-item\" {$margin}>
                             <a href=\"{$href}\" class=\"nav-link {$active}\">{$icon}<p>{$node->name}</p></a>
                           </li>";
            }
        }

        return $html;
    }
}