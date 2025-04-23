@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empleados</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <Empleados></Empleados> 
    </div>
</body>
</html>
@endsection