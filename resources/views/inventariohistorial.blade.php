@extends('layouts.app')

@section('content')
    <div id="app">
        {{-- Componente Vue --}}
        <inventario-historial :movimientos='@json($movimientos)'></inventario-historial>
    </div>
@endsection
