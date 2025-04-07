@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagos</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <pagos></pagos>
    </div>
</body>
</html>
@endsection