{{-- resources/views/crud/table.blade.php --}}
@extends('layouts.app')

@section('content')
    <h4>{{ ucfirst(request()->segment(1)) }}</h4>

    <form class="row g-2 mb-3" method="GET">
        @foreach($columns as $c)
            @if($c->filterable)
                <div class="col-auto">
                    <label class="form-label">{{ $c->label }}</label>

                    @php
                        // Construimos el name en bracket-notation:
                        // "user.role.id" → ["user","role","id"]
                        $parts = explode('.', $c->field);
                        $name  = array_shift($parts);
                        foreach($parts as $p) {
                            $name .= "[{$p}]";
                        }
                        // obtenemos el valor actual con data_get()
                        $current = data_get($filters, $c->field);
                    @endphp

                    @if($c->filterType === 'select' && is_array($c->filterOptions))
                        <select name="{{ $name }}" class="form-select">
                            <option value="">— Todos —</option>
                            @foreach($c->filterOptions as $val => $label)
                                <option value="{{ $val }}"
                                    {{ (string)$current === (string)$val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        @php
                            // tipo de input HTML según filterType
                            $htmlType = match($c->filterType) {
                                'numeric' => 'number',
                                'date'    => 'date',
                                default   => 'text',
                            };
                        @endphp
                        <input
                            type="{{ $htmlType }}"
                            name="{{ $name }}"
                            value="{{ $current }}"
                            class="form-control"
                        />
                    @endif
                </div>
            @endif
        @endforeach

        <div class="col-auto align-self-end">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
        <div class="col-auto align-self-end">
            <a href="{{ route( Str::beforeLast(Route::currentRouteName(), '.') . '.index') }}"
               class="btn btn-outline-secondary">
               Limpiar
            </a>
        </div>
    </form>

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
                    @if($c->visible)
                        <th>{{ $c->label }}</th>
                    @endif
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
                        @if($c->type === 'relation' && is_array($c->options))
                            {{-- Relaciones: mostramos la etiqueta que definiste en options --}}
                            {{ $c->options[$row->{$c->field}] ?? '' }}
                        @else
                            {{-- O cualquier otra columna, incluida la dot-notation --}}
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