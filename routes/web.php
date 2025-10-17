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
use App\Http\Controllers\Catalogs\BolsasController;
use App\Http\Controllers\InventarioController;

// -----------------------------
// RUTAS PÚBLICAS
// -----------------------------
Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/tipos', [ProductoController::class, 'tipos']);

Auth::routes();


Route::group(['middleware' => ['auth']], function () {
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/index', [PerfilController::class, 'index'])->name('index');
        Route::get('/', [PerfilController::class, 'show'])->name('show');
        Route::get('/editar', [PerfilController::class, 'edit'])->name('edit');
        Route::put('/editar', [PerfilController::class, 'update'])->name('update');
        Route::post('/foto/eliminar', [PerfilController::class, 'destroyPhoto'])->name('foto.destroy');
    });

    Route::post('/upload', [UploadController::class, 'store'])->name('upload');
    Route::delete('/upload', [UploadController::class, 'destroy'])->name('upload.delete');
});

// -----------------------------
Route::group(['middleware' => ['auth', 'forerunner']], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // ----- CURSOS -----
    Route::prefix('cursos')->name('cursos.')->group(function () {
        Route::get('/',            [CursoController::class, 'index'])->name('index');
        Route::get('/{curso}',     [CursoController::class, 'show'])->name('show');
        Route::get('{curso}/data', [CursoController::class, 'data'])->name('data');
        Route::get('/export-calificaciones', [CursosController::class, 'exportCalificaciones'])->name('export-calificaciones');

        Route::resource('{curso}/notas', NotasController::class)->only(['store', 'update'])->names([
            'store'  => 'notas.store',
            'update' => 'notas.update',
        ]);
    });

    // ----- UPLOAD -----
    Route::post('/upload', [UploadController::class, 'store'])->name('upload');
    Route::delete('/upload', [UploadController::class, 'destroy'])->name('upload.delete');

    // ----- PAGOS -----
    Route::get('/pagos', [PagosController::class, 'index'])->name('pagos.index');
    Route::post('/pagos', [PagosController::class, 'store'])->name('pagos.store');

    // ----- INVENTARIO -----
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::post('/inventario', [InventarioController::class, 'store'])->name('inventario.store');
    Route::get('/inventario/stock/{id}', [InventarioController::class, 'getProductoStock'])->name('inventario.stock');

    // ----- PERMISOS DE ROLES -----
    Route::post('/admin/roles/{role}/permisos', [RoleModulePermissionController::class, 'update'])->name('roles.permisos.update');

    // -----------------------------
    // MÓDULOS ADMIN
    // -----------------------------
    Route::prefix('admin')->name('admin.')->group(function () {
        // EMPLEADOS
        Route::prefix('empleados')->name('empleados.')->group(function () {
            Route::get('/',           [EmpleadosController::class, 'index'])->name('index');
            Route::get('/export',     [EmpleadosController::class, 'export'])->name('export');
            Route::get('{id}',        [EmpleadosController::class, 'show'])->name('show');
            Route::get('crear',       [EmpleadosController::class, 'create'])->name('create');
            Route::post('/',          [EmpleadosController::class, 'store'])->name('store');
            Route::get('{id}/editar', [EmpleadosController::class, 'edit'])->name('edit');
            Route::put('{id}',        [EmpleadosController::class, 'update'])->name('update');
            Route::delete('{id}',     [EmpleadosController::class, 'destroy'])->name('destroy');
            Route::get('planilla',    [EmpleadosController::class, 'planilla'])->name('planilla');
        });

        // USUARIOS
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

        // ROLES
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

        // ESTUDIANTES
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

        // PRODUCTOS
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

        // CURSOS
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

        // PAGOS
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

        // CSV
        Route::prefix('cargar_csv')->name('cargar_csv.')->group(function () {
            Route::get('/', [CSVController::class, 'index'])->name('index');
            Route::post('/procesar', [CSVController::class, 'procesar'])->name('procesar');
        });

        // ACTIVIDADES
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

        // SECCIONES
        Route::prefix('secciones')->name('secciones.')->group(function () {
            Route::get('/', [SeccionesController::class, 'index'])->name('index');
            Route::get('/export', [SeccionesController::class, 'export'])->name('export');
            Route::get('{id}', [SeccionesController::class, 'show'])->name('show');
            Route::get('/crear', [SeccionesController::class, 'create'])->name('create');
            Route::post('/', [SeccionesController::class, 'store'])->name('store');
            Route::get('{id}/editar', [SeccionesController::class, 'edit'])->name('edit');
            Route::put('{id}', [SeccionesController::class, 'update'])->name('update');
            Route::delete('{id}', [SeccionesController::class, 'destroy'])->name('destroy');
        });

        // GRADOS
        Route::prefix('grados')->name('grados.')->group(function () {
            Route::get('/', [GradosController::class, 'index'])->name('index');
            Route::get('/export', [GradosController::class, 'export'])->name('export');
            Route::get('{id}', [GradosController::class, 'show'])->name('show');
            Route::get('/crear', [GradosController::class, 'create'])->name('create');
            Route::post('/', [GradosController::class, 'store'])->name('store');
            Route::get('{id}/editar', [GradosController::class, 'edit'])->name('edit');
            Route::put('{id}', [GradosController::class, 'update'])->name('update');
            Route::delete('{id}', [GradosController::class, 'destroy'])->name('destroy');
        });

        // BOLSAS
        Route::prefix('bolsas')->name('bolsas.')->group(function () {
            Route::get('/', [BolsasController::class, 'index'])->name('index');
            Route::get('/export', [BolsasController::class, 'export'])->name('export');
            Route::get('{id}', [BolsasController::class, 'show'])->name('show');
            Route::get('/crear', [BolsasController::class, 'create'])->name('create');
            Route::post('/', [BolsasController::class, 'store'])->name('store');
            Route::get('{id}/editar', [BolsasController::class, 'edit'])->name('edit');
            Route::put('{id}', [BolsasController::class, 'update'])->name('update');
            Route::delete('{id}', [BolsasController::class, 'destroy'])->name('destroy');
        });
    });
});
