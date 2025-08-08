@extends('layouts.app')

@section('content')
    <{{ $component }}
        @if(isset($params))
            @foreach($params as $param => $val)
                @if(is_object($val) || is_array($val))
                    :{{ $param }}='@json($val)'
                @else
                    {{ $param }}="{{ $val }}"
                @endif
            @endforeach
        @endif
    />
@stop
