@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <planilla></planilla>
    </div>
</body>
</html>
@endsection
