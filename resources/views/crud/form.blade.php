{{-- resources/views/crud/form.blade.php --}}
@extends('layouts.app')

@section('content')
    <h4>{{ $item ? 'Editar' : 'Nuevo' }} {{ ucfirst(request()->segment(1)) }}</h4>

    <form method="POST" action="{{ $action }}">
        @csrf
        @if($item) @method('PUT') @endif

        @foreach($columns as $c)
            @if(!$c->editable) @continue @endif

            <div class="mb-3">
                <label class="form-label">{{ $c->label }}</label>

                @if($c->type==='relation' && is_array($c->options))
                    {{-- Select genérico --}}
                    <select name="{{ $c->field }}"
                            class="form-select @error($c->field) is-invalid @enderror">
                        <option value="">-- Selecciona --</option>
                        @foreach($c->options as $val => $label)
                            <option value="{{ $val }}"
                                {{ old($c->field, data_get($item, $c->field)) == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error($c->field)
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                @else
                    {{-- Input genérico para string|numeric|date|time|datetime --}}
                    @php
                        // Determinamos el tipo de <input>
                        $htmlType = match($c->type) {
                            'numeric'  => 'number',
                            'date'     => 'date',
                            'time'     => 'time',
                            'datetime' => 'datetime-local',
                            default    => 'text',
                        };

                        // Calculamos el valor inicial bien formateado
                        $raw = old($c->field, data_get($item, $c->field));
                        if ($raw && in_array($c->type, ['date','time','datetime'])) {
                            $dt = \Carbon\Carbon::parse($raw);
                            $format = match($c->type) {
                                'date'     => 'Y-m-d',
                                'time'     => 'H:i',
                                'datetime' => 'Y-m-d\TH:i',
                            };
                            $value = $dt->format($format);
                        } else {
                            $value = $raw;
                        }
                    @endphp

                    <input name="{{ $c->field }}"
                           type="{{ $htmlType }}"
                           {{ $c->type === 'numeric' ? 'step=any' : '' }}
                           class="form-control @error($c->field) is-invalid @enderror"
                           value="{{ $value }}">
                    @error($c->field)
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                @endif

            </div>
        @endforeach

        <button class="btn btn-success">Guardar</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancelar</a>
    </form>
@endsection