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

    {{-- Footer fijo con navegación --}}
    <footer class="bg-white border-top shadow-sm py-2 fixed-bottom">
        <div class="container">
            <div class="d-flex justify-content-around text-center small">
                <div><span>🏠</span><br>Home</div>
                <div><span>📋</span><br>Clases</div>
                <div><span>⚠️</span><br>Alertas</div>
            </div>
        </div>
    </footer>

</body>
</html>
@endsection