@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Edición</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <edicion-usuarios></edicion-usuarios>
    </div>
</body>
</html>
@endsection