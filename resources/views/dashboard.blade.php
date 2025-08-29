@extends('layouts.app')

@section('content')
<div id="app">
    <dashboard :params='@json($params)'></dashboard>
</div>
@endsection
