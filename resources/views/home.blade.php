@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <home></home>
    </div>
</body>
</html>
@endsection
