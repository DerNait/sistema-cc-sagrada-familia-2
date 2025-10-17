@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Inventario</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        {{-- Componente Vue --}}
        <inventario-historial 
            :movimientos='@json($movimientos)'>
        </inventario-historial>
    </div>
</body>
</html>
@endsection
