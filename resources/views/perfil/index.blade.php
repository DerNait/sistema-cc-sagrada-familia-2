{{-- resources/views/perfil/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h1 class="h4 mb-3">Editar perfil</h1>

  <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data" class="card p-3">
    @csrf
    {{-- Usas POST en web.php, por eso no va @method('PUT') --}}

    <div class="mb-3">
      <label class="form-label">Nombre</label>
      <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name', auth()->user()->name) }}">
      @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3 d-flex align-items-center gap-3">
      <div class="rounded-circle bg-secondary d-flex justify-content-center align-items-center text-white fw-semibold"
           style="width:64px;height:64px;overflow:hidden;">
        @if(auth()->user()->foto_perfil)
          <img src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->foto_perfil) }}"
               alt="Foto de perfil" style="width:100%;height:100%;object-fit:cover;">
        @else
          {{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1, 'UTF-8')) }}
        @endif
      </div>
      <div class="flex-grow-1">
        <label class="form-label mb-1">Cambiar foto</label>
        <input type="file" name="foto_perfil" accept="image/*"
               class="form-control @error('foto_perfil') is-invalid @enderror">
        <div class="form-text">Máx. 2MB. JPG/PNG/WebP.</div>
        @error('foto_perfil') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary" type="submit">Guardar cambios</button>
      <a class="btn btn-outline-secondary" href="{{ url()->previous() }}">Cancelar</a>
    </div>
  </form>
</div>
@endsection
