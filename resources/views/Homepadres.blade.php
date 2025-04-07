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

    {{-- Encabezado --}}
    <header class="bg-success text-white py-3 shadow-sm text-center">
        <h5 class="mb-0">Centro Cultural Sagrada Familia II</h5>
    </header>

    {{-- Contenido principal --}}
    <main class="flex-fill p-3">
        <div id="app">
            <home-padres></home-padres>
        </div>
    </main>

    {{-- Footer fijo con navegación --}}
    <footer class="bg-white border-top shadow-sm py-2 fixed-bottom">
        <div class="container">
            <div class="d-flex justify-content-around text-center small">
                <div><span>🏠</span><br>Home</div>
                <div><span>💰</span><br>Pagos</div>
                <div><span>⚠️</span><br>Notificaciones</div>
            </div>
        </div>
    </footer>

</body>
</html>
