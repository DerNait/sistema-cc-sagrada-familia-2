<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Catalogs\EmpleadosController;
use App\Http\Controllers\Catalogs\UsersController;
use App\Http\Controllers\Catalogs\RolesController;
use App\Http\Controllers\Catalogs\EstudiantesController;
use App\Http\Controllers\Catalogs\ProductosController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Catalogs\CursosController;
use App\Http\Controllers\Catalogs\ActividadesController;
use App\Http\Controllers\Catalogs\SeccionesController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotasController;
use App\Http\Controllers\RoleModulePermissionController;

Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/tipos', [ProductoController::class, 'tipos']);

Auth::routes();

Route::group(['middleware' => ['auth', 'forerunner']], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/cursos', [CursoController::class, 'index'])->name('cursos.index');
    Route::get('/cursos/{curso}', [CursoController::class, 'show'])->name('cursos.show');
    Route::resource('/notas', NotasController::class)->only(['store', 'update']);

    Route::post('/catalogos/roles/{role}/permisos', [RoleModulePermissionController::class, 'update'])->name('roles.permisos.update');

    Route::prefix('catalogos')->name('catalogos.')->group(function () {
        Route::prefix('empleados')->name('empleados.')->group(function () {
            Route::get('/',           [EmpleadosController::class, 'index'])->name('index');
            Route::get('/export',     [EmpleadosController::class, 'export'])->name('export');
            Route::get('{id}',        [EmpleadosController::class, 'show'])->name('show');
            Route::get('crear',       [EmpleadosController::class, 'create'])->name('create');
            Route::post('/',          [EmpleadosController::class, 'store'])->name('store');
            Route::get('{id}/editar', [EmpleadosController::class, 'edit'])->name('edit');
            Route::put('{id}',        [EmpleadosController::class, 'update'])->name('update');
            Route::delete('{id}',     [EmpleadosController::class, 'destroy'])->name('destroy');
    
            Route::get('planilla', [EmpleadosController::class, 'planilla'])->name('planilla');
        });   
    
        Route::prefix('usuarios')->name('usuarios.')->group(function () {
            Route::get('/',           [UsersController::class, 'index'])->name('index');
            Route::get('/export',     [UsersController::class, 'export'])->name('export');
            Route::get('{id}',        [UsersController::class, 'show'])->name('show');
            Route::get('crear',       [UsersController::class, 'create'])->name('create');
            Route::post('/',          [UsersController::class, 'store'])->name('store');
            Route::get('{id}/editar', [UsersController::class, 'edit'])->name('edit');
            Route::put('{id}',        [UsersController::class, 'update'])->name('update');
            Route::delete('{id}',     [UsersController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/',             [RolesController::class, 'index'])->name('index');
            Route::get('/export',       [RolesController::class, 'export'])->name('export');
            Route::get('{id}/permisos', [RolesController::class, 'permisos'])->name('permissions');
            Route::get('{id}',          [RolesController::class, 'show'])->name('show');
            Route::get('crear',         [RolesController::class, 'create'])->name('create');
            Route::post('/',            [RolesController::class, 'store'])->name('store');
            Route::get('{id}/editar',   [RolesController::class, 'edit'])->name('edit');
            Route::put('{id}',          [RolesController::class, 'update'])->name('update');
            Route::delete('{id}',       [RolesController::class, 'destroy'])->name('destroy');
        });
    
        Route::prefix('estudiantes')->name('estudiantes.')->group(function () {
            Route::get('/',           [EstudiantesController::class, 'index'])->name('index');
            Route::get('/export',     [EstudiantesController::class, 'export'])->name('export');
            Route::get('{id}',        [EstudiantesController::class, 'show'])->name('show');
            Route::get('crear',       [EstudiantesController::class, 'create'])->name('create');
            Route::post('/',          [EstudiantesController::class, 'store'])->name('store');
            Route::get('{id}/editar', [EstudiantesController::class, 'edit'])->name('edit');
            Route::put('{id}',        [EstudiantesController::class, 'update'])->name('update');
            Route::delete('{id}',     [EstudiantesController::class, 'destroy'])->name('destroy');
        });
    
        Route::prefix('productos')->name('productos.')->group(function () {
            Route::get('/',           [ProductosController::class, 'index'])->name('index');
            Route::get('/export',     [ProductosController::class, 'export'])->name('export');
            Route::get('{id}',        [ProductosController::class, 'show'])->name('show');
            Route::get('crear',       [ProductosController::class, 'create'])->name('create');
            Route::post('/',          [ProductosController::class, 'store'])->name('store');
            Route::get('{id}/editar', [ProductosController::class, 'edit'])->name('edit');
            Route::put('{id}',        [ProductosController::class, 'update'])->name('update');
            Route::delete('{id}',     [ProductosController::class, 'destroy'])->name('destroy');
        });
    
        Route::prefix('cursos')->name('cursos.')->group(function () {
            Route::get('/',           [CursosController::class, 'index'])->name('index');
            Route::get('/export',     [CursosController::class, 'export'])->name('export');
            Route::get('{id}',        [CursosController::class, 'show'])->name('show');
            Route::get('crear',       [CursosController::class, 'create'])->name('create');
            Route::post('/',          [CursosController::class, 'store'])->name('store');
            Route::get('{id}/editar', [CursosController::class, 'edit'])->name('edit');
            Route::put('{id}',        [CursosController::class, 'update'])->name('update');
            Route::delete('{id}',     [CursosController::class, 'destroy'])->name('destroy');
        });
    
        Route::prefix('actividades')->name('actividades.')->group(function () {
            Route::get('/',           [ActividadesController::class, 'index'])->name('index');
            Route::get('/export',     [ActividadesController::class, 'export'])->name('export');
            Route::get('{id}',        [ActividadesController::class, 'show'])->name('show');
            Route::get('crear',       [ActividadesController::class, 'create'])->name('create');
            Route::post('/',          [ActividadesController::class, 'store'])->name('store');
            Route::get('{id}/editar', [ActividadesController::class, 'edit'])->name('edit');
            Route::put('{id}',        [ActividadesController::class, 'update'])->name('update');
            Route::delete('{id}',     [ActividadesController::class, 'destroy'])->name('destroy');
        });
    
        Route::prefix('secciones')->name('secciones.')->group(function () {
            Route::get('/',           [SeccionesController::class, 'index'])->name('index');
            Route::get('/export',     [SeccionesController::class, 'export'])->name('export');
            Route::get('{id}',        [SeccionesController::class, 'show'])->name('show');
            Route::get('/crear',      [SeccionesController::class, 'create'])->name('create');
            Route::post('/',          [SeccionesController::class, 'store'])->name('store');
            Route::get('{id}/editar', [SeccionesController::class, 'edit'])->name('edit');
            Route::put('{id}',        [SeccionesController::class, 'update'])->name('update');
            Route::delete('{id}',     [SeccionesController::class, 'destroy'])->name('destroy');
        });
    });
});
