<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Catalogs\EmpleadosController;
use App\Http\Controllers\Catalogs\UsersController;
use App\Http\Controllers\Catalogs\EstudiantesController;
use App\Http\Controllers\Catalogs\ProductosController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Catalogs\CursosController;
use App\Http\Controllers\Catalogs\ActividadesController;
use App\Http\Controllers\Catalogs\SeccionesController;
use App\Http\Controllers\CursosEstudianteController;
use App\Http\Controllers\CursoController;



Route::get('/homepadres', function () {
    return view('Homepadres'); 
    // cambiar esto al home.blade.php sin el blade
});

Route::get('/estudiantes', function(){
    return view ('estudiantes');
});

Route::get('/homeprofesores', function () {
    return view('Homeprofesores'); 
    // cambiar esto al home.blade.php sin el blade
});

Route::get('/students', function () {
    return view('Este es el student');
});

Route::get('/calificaciones', function () {
    return view('calificaciones');
});

Route::get('/profesores', function () {
    return view('profesores');
});

Route::get('/planilla', function () {
    return view('planilla');
});

Route::get('/courses', function () {
    return view('cursos');
});

//Pendiente
Route::get('/cursos', function () {
    return view('cursos');
});

Route::get('/payment', function () {
    return view('pagos');
});

Route::get('/agregacioninventario', function () {
    return view('AgregacionInventario');
});

Route::get('/inventario', function () {
    return view('Inventario');
});

Route::get('/empleados', function () {
    return view('empleados.empleados');
});

Route::get('/edicion', function () {
    return view('edicionusuarios');
});

Route::get('/curso-edicion', function () {
    return view('cursoedicion');
});

Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/tipos', [ProductoController::class, 'tipos']);

Route::get('/MisCursos', [App\Http\Controllers\CursosEstudianteController::class, 'index'])
    ->middleware('auth')
    ->name('estudiante.cursos');


Auth::routes();

Route::get('/empleados/planilla', [EmpleadosController::class, 'planilla']);

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/exportar-empleados', [App\Http\Controllers\ExportController::class, 'export'])->name('export.empleados');

Route::middleware('auth')->group(function () {
    Route::get('/cursos', [CursoController::class, 'index'])->name('cursos.index');
    Route::get('/cursos/{curso}', [CursoController::class, 'show'])->name('cursos.show');
});


Route::prefix('catalogos')->name('catalogos.')->group(function () {
    Route::prefix('empleados')->name('empleados.')->group(function () {
        Route::get('/',           [EmpleadosController::class, 'index'])->name('index');
        Route::get('{id}',        [EmpleadosController::class, 'show'])->name('show');
        Route::get('crear',       [EmpleadosController::class, 'create'])->name('create');
        Route::post('crear',      [EmpleadosController::class, 'store'])->name('store');
        Route::get('{id}/editar', [EmpleadosController::class, 'edit'])->name('edit');
        Route::put('{id}',        [EmpleadosController::class, 'update'])->name('update');
        Route::delete('{id}',     [EmpleadosController::class, 'destroy'])->name('destroy');

        Route::get('planilla', [EmpleadosController::class, 'planilla'])->name('planilla');
    });   

    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/',           [UsersController::class, 'index'])->name('index');
        Route::get('{id}',        [UsersController::class, 'show'])->name('show');
        Route::get('crear',       [UsersController::class, 'create'])->name('create');
        Route::post('crear',      [UsersController::class, 'store'])->name('store');
        Route::get('{id}/editar', [UsersController::class, 'edit'])->name('edit');
        Route::put('{id}',        [UsersController::class, 'update'])->name('update');
        Route::delete('{id}',     [UsersController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('estudiantes')->name('estudiantes.')->group(function () {
        Route::get('/',           [EstudiantesController::class, 'index'])->name('index');
        Route::get('{id}',        [EstudiantesController::class, 'show'])->name('show');
        Route::get('crear',       [EstudiantesController::class, 'create'])->name('create');
        Route::post('crear',      [EstudiantesController::class, 'store'])->name('store');
        Route::get('{id}/editar', [EstudiantesController::class, 'edit'])->name('edit');
        Route::put('{id}',        [EstudiantesController::class, 'update'])->name('update');
        Route::delete('{id}',     [EstudiantesController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('productos')->name('productos.')->group(function () {
        Route::get('/',           [ProductosController::class, 'index'])->name('index');
        Route::get('{id}',        [EstudiantesController::class, 'show'])->name('show');
        Route::get('crear',       [ProductosController::class, 'create'])->name('create');
        Route::post('crear',      [ProductosController::class, 'store'])->name('store');
        Route::get('{id}/editar', [ProductosController::class, 'edit'])->name('edit');
        Route::put('{id}',        [ProductosController::class, 'update'])->name('update');
        Route::delete('{id}',     [ProductosController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('cursos')->name('cursos.')->group(function () {
        Route::get('/',           [CursosController::class, 'index'])->name('index');
        Route::get('{id}',        [CursosController::class, 'show'])->name('show');
        Route::get('crear',       [CursosController::class, 'create'])->name('create');
        Route::post('crear',      [CursosController::class, 'store'])->name('store');
        Route::get('{id}/editar', [CursosController::class, 'edit'])->name('edit');
        Route::put('{id}',        [CursosController::class, 'update'])->name('update');
        Route::delete('{id}',     [CursosController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('actividades')->name('actividades.')->group(function () {
        Route::get('/',           [ActividadesController::class, 'index'])->name('index');
        Route::get('{id}',        [ActividadesController::class, 'show'])->name('show');
        Route::get('crear',       [ActividadesController::class, 'create'])->name('create');
        Route::get('crear',       [ActividadesController::class, 'create'])->name('create');
        Route::post('crear',      [ActividadesController::class, 'store'])->name('store');
        Route::get('{id}/editar', [ActividadesController::class, 'edit'])->name('edit');
        Route::put('{id}',        [ActividadesController::class, 'update'])->name('update');
        Route::delete('{id}',     [ActividadesController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('secciones')->name('secciones.')->group(function () {
        Route::get('/',           [SeccionesController::class, 'index'])->name('index');
        Route::get('{id}',        [SeccionesController::class, 'show'])->name('show');
        Route::get('/crear',      [SeccionesController::class, 'create'])->name('create');
        Route::post('/',          [SeccionesController::class, 'store'])->name('store');
        Route::get('{id}/editar', [SeccionesController::class, 'edit'])->name('edit');
        Route::put('{id}',        [SeccionesController::class, 'update'])->name('update');
        Route::delete('{id}',     [SeccionesController::class, 'destroy'])->name('destroy');
    });
});