<?php
namespace App\Navigation;

use App\Support\Forerunner\Forerunner;
use App\Models\Menu;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        // contenedor UL
        $html = '<ul class="nav nav-pills gap-2 align-items-center w-100">';

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
                        <a class="nav-link '.$isActive.' dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

        // ======== 5) Bloque de usuario a la derecha ========
        $html .= self::userMenu();

        $html .= '</ul>';
        return $html;
    }

    private static function userMenu(): string
    {
        $u = Auth::user();
        if (!$u) {
            // Si no hay usuario autenticado, no mostramos el menú de usuario
            return '';
        }

        // Intentar resolver URL de avatar:
        // 1) accessor $user->foto_perfil_url
        // 2) columna $user->foto_perfil (ruta en disco 'public')
        // 3) null => usamos inicial
        $avatarUrl = null;

        if (isset($u->foto_perfil_url) && $u->foto_perfil_url) {
            $avatarUrl = $u->foto_perfil_url;
        } elseif (isset($u->foto_perfil) && $u->foto_perfil) {
            // Si guardas solo la clave/ruta en 'public'
            try {
                $avatarUrl = Storage::url($u->foto_perfil);
            } catch (\Throwable $e) {
                $avatarUrl = null;
            }
        }

        // Inicial (nombre|name)
        $nombre = $u->nombre ?? $u->name ?? 'U';
        $inicial = strtoupper(mb_substr($nombre, 0, 1, 'UTF-8'));

        // Ruta para editar perfil (si no existe, usa '#')
        $editHref = Route::has('profile.edit') ? route('profile.edit') : '#';

        // Usamos ms-auto para empujar este li al extremo derecho dentro del UL (con w-100 en UL)
        $avatar = $avatarUrl
            ? '<img src="'.e($avatarUrl).'"
                     alt="Foto de perfil"
                     class="rounded-circle object-fit-cover"
                     style="width:36px;height:36px;"/>'
            : '<div class="rounded-circle bg-secondary d-flex justify-content-center align-items-center text-white fw-semibold"
                     style="width:36px;height:36px;">'.e($inicial).'</div>';

        // Dropdown con Editar perfil + Logout (opcional)
        $logoutForm = '';
        if (Route::has('logout')) {
            $logoutForm = '
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form method="POST" action="'.route('logout').'">
                    '.csrf_field().'
                    <button class="dropdown-item text-danger" type="submit">Cerrar sesión</button>
                  </form>
                </li>';
        }

        return '
            <li class="nav-item dropdown ms-auto">
                <a class="nav-link d-flex align-items-center gap-2 dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    '.$avatar.'
                    <span class="d-none d-sm-inline">'.e($nombre).'</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <a class="dropdown-item" href="'.$editHref.'">
                            Editar perfil
                        </a>
                    </li>
                    '.$logoutForm.'
                </ul>
            </li>';
    }
}
