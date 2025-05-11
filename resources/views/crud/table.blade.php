{{-- resources/views/crud/table.blade.php --}}
@extends('layouts.app')

@section('content')
    <h4>{{ ucfirst(request()->segment(1)) }}</h4>

    @php
        $current = Route::currentRouteName();
        $base    = Str::beforeLast($current, '.');
    @endphp

    @if($abilities['create'])
        <a href="{{ route($base . '.create') }}"
           class="btn btn-sm btn-primary mb-2">Nuevo</a>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
            @foreach($columns as $c)
                @if($c->visible) <th>{{ $c->label }}</th> @endif
            @endforeach
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data as $row)
            <tr>
            @foreach($columns as $c)
                @if(! $c->visible) @continue @endif
                <td>
                    @if($c->type === 'relation')
                        {{-- Para relaciones: mostramos la etiqueta en options --}}
                        {{ $c->options[$row->{$c->field}] ?? '' }}
                    @else
                        {{-- Para columnas normales (o relacionales via data_get como user.name) --}}
                        {{ data_get($row, $c->field) }}
                    @endif
                </td>
            @endforeach

                <td>
                    @if($abilities['update'])
                        <a href="{{ route($base . '.edit', $row->id) }}"
                           class="btn btn-sm btn-warning">✎</a>
                    @endif

                    @if($abilities['delete'])
                        <form method="POST"
                              action="{{ route($base . '.destroy', $row->id) }}"
                              class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('¿Eliminar?')">🗑</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $data->withQueryString()->links() }}
@endsection