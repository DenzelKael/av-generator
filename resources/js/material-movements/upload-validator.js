const MAX_FILE_SIZE = 20 * 1024 * 1024;

const ALLOWED_EXTENSIONS = ["xlsx", "xls", "csv"];

export function validateFile(file) {
    if (!file) {
        return {
            valid: false,
            message: "Debes seleccionar un archivo.",
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Validar tamaño
    |--------------------------------------------------------------------------
    */

    if (file.size > MAX_FILE_SIZE) {
        return {
            valid: false,
            message: "El archivo supera el tamaño máximo permitido de 20 MB.",
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Obtener extensión
    |--------------------------------------------------------------------------
    */

    const extension = getExtension(file.name);

    /*
    |--------------------------------------------------------------------------
    | Archivos con extensión
    |--------------------------------------------------------------------------
    */

    if (extension) {
        if (!ALLOWED_EXTENSIONS.includes(extension)) {
            return {
                valid: false,
                message: "El archivo debe ser XLSX, XLS o CSV.",
            };
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Archivo sin extensión
    |--------------------------------------------------------------------------
    |
    | Los reportes generados por el sistema pueden llegar sin extensión.
    | No los rechazamos aquí. El backend verificará posteriormente
    | que el contenido sea realmente un archivo válido.
    |
    */

    return {
        valid: true,
        message: null,
    };
}

function getExtension(filename) {
    const parts = filename.split(".");

    if (parts.length === 1) {
        return "";
    }

    return parts.pop().toLowerCase();
}

export function formatFileSize(bytes) {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
}
