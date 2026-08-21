<div class="card card-primary h-100">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-import mr-2"></i>
            Importación automática
        </h3>
    </div>

    <div class="card-body">

        <form id="upload-form" data-url="{{ route('material-movements.store') }}">
            @csrf

            <div id="material-dropzone" class="dropzone">
                <div class="dz-message text-center">

                    <i class="fas fa-file-excel fa-3x text-success mb-3"></i>

                    <h5>
                        Arrastra uno o varios archivos aquí
                    </h5>

                    <span class="text-muted d-block">
                        XLSX, XLS, CSV o reportes sin extensión
                    </span>

                </div>
            </div>

            <div class="alert alert-danger d-none mt-3 text-center" data-upload-error>
                <span data-error-message></span>
            </div>

            <div class="alert alert-success d-none mt-3 text-center" data-upload-success>
                <span data-success-message></span>
            </div>

            <button type="button" class="btn btn-primary btn-block mt-3" data-submit-button>
                <i class="fas fa-upload mr-2"></i>

                <span data-submit-text>
                    Importar movimientos
                </span>
            </button>

        </form>

    </div>
</div>
