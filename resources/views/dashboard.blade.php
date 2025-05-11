{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Panel de Control</h1>
    <p>Bienvenido, {{ Auth::user()->name }}.</p>
    {{-- Aquí puedes añadir estadísticas, gráficas, lo que necesites… --}}
@endsection