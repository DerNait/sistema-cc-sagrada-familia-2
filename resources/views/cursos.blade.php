@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cursos</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <cursos></cursos>
    </div>
</body>
</html>
@endsection
