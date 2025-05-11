<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ID del rol con acceso total
    |--------------------------------------------------------------------------
    |
    | Este rol podrá acceder a todos los módulos y acciones sin importar si
    | tiene los permisos asignados explícitamente. Usado por Forerunner::isRoot().
    |
    */

    'root_role_id' => 1,

    /*
    |--------------------------------------------------------------------------
    | Nombre del middleware
    |--------------------------------------------------------------------------
    |
    | Nombre que usarás en las rutas para aplicar permisos específicos. Por
    | ejemplo: Route::get(...)->middleware('ability:empleados.update')
    |
    */

    'middleware_name' => 'ability',

];