@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Students</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <calificaciones :curso-id="{{ $curso->id }}"></calificaciones>
    </div>
</body>
</html>
@endsection