@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro Cultural Sagrada Familia II</title>

    {{-- Estilos y scripts --}}
    @vite('resources/js/app.js')
</head>
<body class="bg-light-subtle d-flex flex-column min-vh-100">

    {{-- Contenido principal --}}
    <main class="flex-fill p-3">
        <div id="app">
            <home-padres></home-padres>
        </div>
    </main>
</body>
</html>
@endsection
