<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('home'); 
    // cambiar esto al home.blade.php sin el blade
});

Route::get('/students', function () {
    return view('Este es el student');
});

Route::get('/profesores', function () {
    return view('Este es el profesores');
});

Route::get('/courses', function () {
    return view('Este es el cursos');
});

Route::get('/payment', function () {
    return view('Este es el pagos');
});

Route::get('/inventory', function () {
    return view('Este es el inventario');
});