@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Centro Cultural Sagrada Familia II</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/js/app.js')
</head>
<body class="bg-light-subtle d-flex flex-column min-vh-100">

    {{-- Contenido principal --}}
    <main class="flex-fill p-3">
        <div id="app">
            <home-profesores></home-profesores>
        </div>
    </main>
</body>
</html>
@endsection