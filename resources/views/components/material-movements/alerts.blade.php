{{-- Mensaje de éxito --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">

        <i class="fas fa-check-circle mr-2"></i>

        {{ session('success') }}

        <button
            type="button"
            class="close"
            data-dismiss="alert"
            aria-label="Cerrar"
        >
            <span aria-hidden="true">&times;</span>
        </button>

    </div>
@endif

{{-- Errores generales --}}
@if ($errors->any() && !$errors->has('document'))
    <div class="alert alert-danger">

        <i class="fas fa-exclamation-triangle mr-2"></i>

        El reporte no contiene todos los datos necesarios
        para importarlo automáticamente.

    </div>
@endif
