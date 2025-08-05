<?php

return [
    
    // Acciones CRUD estándar
    'crud' => [
        'index'   => 'read',
        'show'    => 'read',
        'export'  => 'export',
        'create'  => 'create',
        'store'   => 'create',
        'edit'    => 'update',
        'update'  => 'update',
        'destroy' => 'delete',
    ],

    // Alias por módulo (cuando el nombre NO coincide con CRUD)
    'aliases' => [
        'empleados' => [
            'planilla' => 'export',     // catalogos.empleados.planilla
        ],
        'roles' => [
            'permisos' => 'update',     // catalogos.roles.permisos
        ],
        // agrega más si sale algo especial
    ],

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