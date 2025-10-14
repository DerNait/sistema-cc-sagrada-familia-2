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
use App\Http\Controllers\PagosController;
use App\Http\Controllers\CSVController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\PagosEstudianteController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\Catalogs\GradosController;

Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/tipos', [ProductoController::class, 'tipos']);

Auth::routes();


Route::group(['middleware' => ['auth', 'forerunner']], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::prefix('cursos')->name('cursos.')->group(function () {
        Route::get('/',            [CursoController::class, 'index'])->name('index');
        Route::get('/{curso}',     [CursoController::class, 'show'])->name('show');
        Route::get('{curso}/data', [CursoController::class, 'data'])->name('data');
        Route::get('/export-calificaciones', [CursosController::class, 'exportCalificaciones'])->name('export-calificaciones'); #Cambiar Controller, Ruta creada pues falta lógica.

            
        Route::resource('{curso}/notas', NotasController::class)->only(['store', 'update'])
        ->names([
            'store'  => 'notas.store',
            'update' => 'notas.update',
        ]);
    });

    Route::post('/upload', [UploadController::class, 'store'])->name('upload');
    Route::delete('/upload', [UploadController::class, 'destroy'])->name('upload.delete');

    Route::get('/pagos', [PagosController::class, 'index'])->name('pagos.index');
    Route::post('/pagos', [PagosController::class, 'store'])->name('pagos.store');

    Route::post('/admin/roles/{role}/permisos', [RoleModulePermissionController::class, 'update'])->name('roles.permisos.update');

    Route::prefix('admin')->name('admin.')->group(function () {
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

        Route::prefix('pagos')->name('pagos.')->group(function () {
            Route::get('/', [PagosController::class, 'index'])->name('index');
            Route::get('crear', [PagosController::class, 'create'])->name('create');
            Route::post('/', [PagosController::class, 'store'])->name('store');
            Route::get('{id}', [PagosController::class, 'show'])->name('show');
            Route::get('{id}/editar', [PagosController::class, 'edit'])->name('edit');
            Route::put('{id}', [PagosController::class, 'update'])->name('update');
            Route::delete('{id}', [PagosController::class, 'destroy'])->name('destroy');
            Route::get('cargar', [PagosController::class, 'createUpload'])->name('cargar');
            Route::post('cargar', [PagosController::class, 'storeUpload'])->name('cargar.store');
        });

        Route::prefix('cargar_csv')->name('cargar_csv.')->group(function () {
            Route::get('/', [CSVController::class, 'index'])->name('index');
            Route::post('/procesar', [CSVController::class, 'procesar'])->name('procesar');
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
            Route::get('/',                 [SeccionesController::class, 'index'])->name('index');
            Route::get('/export',           [SeccionesController::class, 'export'])->name('export');
            Route::get('{id}',              [SeccionesController::class, 'show'])->name('show');
            Route::get('/crear',            [SeccionesController::class, 'create'])->name('create');
            Route::post('/',                [SeccionesController::class, 'store'])->name('store');
            Route::get('{id}/editar',       [SeccionesController::class, 'edit'])->name('edit');
            Route::put('{id}',              [SeccionesController::class, 'update'])->name('update');
            Route::delete('{id}',           [SeccionesController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('grados')->name('grados.')->group(function () {
            Route::get('/',                 [GradosController::class, 'index'])->name('index');
            Route::get('/export',           [GradosController::class, 'export'])->name('export');
            Route::get('{id}',              [GradosController::class, 'show'])->name('show');
            Route::get('/crear',            [GradosController::class, 'create'])->name('create');
            Route::post('/',                [GradosController::class, 'store'])->name('store');
            Route::get('{id}/editar',       [GradosController::class, 'edit'])->name('edit');
            Route::put('{id}',              [GradosController::class, 'update'])->name('update');
            Route::delete('{id}',           [GradosController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('perfil')->name('perfil.')->group(function () {
            Route::get('/', [PerfilController::class, 'show'])->name('show');
            Route::get('/editar', [PerfilController::class, 'edit'])->name('edit');
            Route::post('/editar', [PerfilController::class, 'update'])->name('update');
            Route::post('/foto/eliminar', [PerfilController::class, 'destroyPhoto'])->name('foto.destroy');
        });
    });
});

