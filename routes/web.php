<?php

use Illuminate\Support\Facades\Route;

Route::get('/iniciosesion', function(){
    return view('iniciosesion');
});

Route::get('/registro', function () {
    return view('registro');
});

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

Route::get('/profesores', function () {
    return view('profesores');
});

Route::get('/courses', function () {
    return view('cursos');
});

Route::get('/payment', function () {
    return view('pagos');
});

Route::get('/inventory', function () {
    return view('inventario');
});
Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
