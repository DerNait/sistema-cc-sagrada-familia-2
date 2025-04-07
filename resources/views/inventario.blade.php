@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <inventario></inventario>
    </div>
</body>
</html>
@endsection