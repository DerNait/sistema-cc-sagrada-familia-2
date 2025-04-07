@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Teachers</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <profesores></profesores>
    </div>
</body>
</html>
@endsection