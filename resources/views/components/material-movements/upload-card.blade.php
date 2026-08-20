<div class="card card-primary h-100">

    <div class="card-header">

        <h3 class="card-title">
            <i class="fas fa-file-import mr-2"></i>
            Importación automática
        </h3>

    </div>

    <div class="card-body">

        <p class="text-muted">
            Solo sube el reporte; todos los datos se leen automáticamente
            desde el archivo.
        </p>

        <form
            action="{{ route('material-movements.store') }}"
            method="POST"
            enctype="multipart/form-data"
            data-upload-form
        >
            @csrf

            <div class="form-group">

                <label for="document">
                    Archivo Excel <span class="text-danger">*</span>
                </label>

                <div
                    class="border border-primary rounded p-4 text-center bg-light"
                    style="border-style: dashed !important; cursor: pointer;"
                    data-drop-zone
                >

                    <input
                        type="file"
                        name="document"
                        id="document"
                        class="d-none"
                        accept=".xlsx,.xls,.csv"
                        required
                        data-file-input
                    >

                    <label
                        for="document"
                        class="mb-0"
                        style="cursor: pointer;"
                    >

                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>

                        <h5 data-file-name>
                            Arrastra el archivo aquí o selecciónalo
                        </h5>

                        <p class="text-muted mb-0">
                            Excel, CSV o reporte Excel sin extensión
                        </p>

                        <small class="text-muted">
                            Máximo 20 MB
                        </small>

                    </label>

                </div>

                @error('document')
                    <div class="text-danger mt-2">

                        <i class="fas fa-exclamation-circle mr-1"></i>

                        {{ $message }}

                    </div>
                @enderror

            </div>

            <div class="form-group mb-0">

                <button
                    type="submit"
                    class="btn btn-primary btn-lg btn-block"
                >
                    <i class="fas fa-upload mr-2"></i>

                    Importar y registrar movimiento
                </button>

            </div>

        </form>

    </div>

</div>
