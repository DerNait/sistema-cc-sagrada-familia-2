@extends('layouts.app')

@section('content')
<div class="container py-4">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 m-0">Mi perfil</h1>
    <a href="{{ route('perfil.edit') }}" class="btn btn-primary">
      <i class="fa-solid fa-user-pen me-1"></i> Editar perfil
    </a>
  </div>

  @php
    $u = $user ?? auth()->user();
    $avatarUrl = null;

    if (isset($u->foto_perfil_url) && $u->foto_perfil_url) {
        $avatarUrl = $u->foto_perfil_url;
    } elseif (!empty($u->foto_perfil ?? null)) {
        try {
            $avatarUrl = \Illuminate\Support\Facades\Storage::url($u->foto_perfil);
        } catch (\Throwable $e) {
            $avatarUrl = null;
        }
    }

    $nombreMostrar = $u->name ?? $u->nombre ?? '';
    $initial = strtoupper(mb_substr($nombreMostrar !== '' ? $nombreMostrar : 'U', 0, 1, 'UTF-8'));
  @endphp

  <div class="row g-4">
    {{-- Columna izquierda: Avatar y acciones --}}
    <div class="col-12 col-md-4">
      <div class="card p-3">
        <div class="d-flex flex-column align-items-center">
          <div class="rounded-circle bg-secondary d-flex justify-content-center align-items-center text-white fw-semibold mb-3"
               style="width:128px;height:128px;overflow:hidden;">
            @if($avatarUrl)
              <img src="{{ $avatarUrl }}" alt="Foto de perfil" style="width:100%;height:100%;object-fit:cover;">
            @else
              <span style="font-size:48px;line-height:128px;">{{ $initial }}</span>
            @endif
          </div>

          <div class="d-flex flex-column w-100 gap-2">
            <a href="{{ route('perfil.edit') }}" class="btn btn-outline-primary w-100">
              <i class="fa-solid fa-user-pen me-1"></i> Editar perfil
            </a>

            @if($avatarUrl)
              <form id="form-eliminar-foto" action="{{ route('perfil.foto.destroy') }}" method="POST" class="d-none">
                @csrf
              </form>
              <button id="btn-eliminar-foto" class="btn btn-outline-danger w-100">
                <i class="fa-solid fa-trash me-1"></i> Eliminar foto
              </button>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Columna derecha: Datos del usuario --}}
    <div class="col-12 col-md-8">
      <div class="card p-3">
        <h2 class="h5 mb-3">Información</h2>
        <div class="row g-3">
          <div class="col-12 col-lg-6">
            <div class="form-floating">
              <input type="text" class="form-control" id="fldNombre"
                     value="{{ $nombreMostrar }}" readonly>
              <label for="fldNombre">Nombre</label>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-floating">
              <input type="email" class="form-control" id="fldEmail" value="{{ $u->email }}" readonly>
              <label for="fldEmail">Email</label>
            </div>
          </div>

          @if(!empty($u->apellido))
          <div class="col-12 col-lg-6">
            <div class="form-floating">
              <input type="text" class="form-control" id="fldApellido" value="{{ $u->apellido }}" readonly>
              <label for="fldApellido">Apellido</label>
            </div>
          </div>
          @endif

          <div class="col-12 col-lg-6">
            <div class="form-floating">
              <input type="text" class="form-control" id="fldCreado"
                     value="{{ optional($u->created_at)->format('d/m/Y H:i') }}" readonly>
              <label for="fldCreado">Creado</label>
            </div>
          </div>

          {{-- Agrega más campos si los tienes --}}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Confirmación SweetAlert para eliminar foto
  (function() {
    const btn = document.getElementById('btn-eliminar-foto');
    const form = document.getElementById('form-eliminar-foto');
    if (!btn || !form) return;

    btn.addEventListener('click', function () {
      Swal.fire({
        icon: 'warning',
        title: '¿Eliminar foto de perfil?',
        text: 'Podrás subir una nueva desde "Editar perfil".',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  })();
</script>
@endpush
