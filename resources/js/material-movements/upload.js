import axios from "axios";
import Dropzone from "dropzone";

import { validateFile } from "./upload-validator";

Dropzone.autoDiscover = false;

document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("#upload-form");

    if (!form) {
        return;
    }

    const submitButton = form.querySelector("[data-submit-button]");

    const submitText = form.querySelector("[data-submit-text]");

    const errorAlert = form.querySelector("[data-upload-error]");

    const errorMessage = form.querySelector("[data-error-message]");

    const successAlert = form.querySelector("[data-upload-success]");

    const successMessage = form.querySelector("[data-success-message]");

    const dropzone = new Dropzone("#material-dropzone", {
        url: "#",

        autoProcessQueue: false,

        uploadMultiple: true,

        parallelUploads: 100,

        addRemoveLinks: true,

        maxFilesize: 20,

        acceptedFiles: null,
    });

    /*
    |--------------------------------------------------------------------------
    | Validación
    |--------------------------------------------------------------------------
    */

    dropzone.on("addedfile", (file) => {
        const validation = validateFile(file);

        if (!validation.valid) {
            dropzone.removeFile(file);

            showError(validation.message);
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    submitButton.addEventListener("click", async () => {
        clearMessages();

        const files = dropzone.files.filter(
            (file) => file.status !== Dropzone.CANCELED,
        );

        if (!files.length) {
            showError("Debes seleccionar al menos un archivo.");

            return;
        }

        setLoading(true);

        const formData = new FormData();

        files.forEach((file) => {
            formData.append("documents[]", file);
        });

        try {
            const response = await axios.post(form.dataset.url, formData, {
                headers: {
                    "X-CSRF-TOKEN": getCsrfToken(),
                    Accept: "application/json",
                },

                onUploadProgress: (progressEvent) => {
                    const percent = Math.round(
                        (progressEvent.loaded / progressEvent.total) * 100,
                    );

                    submitText.textContent = `Subiendo ${percent}%`;
                },
            });

            showSuccess(
                response.data.message ?? "Archivos importados correctamente.",
            );

            dropzone.removeAllFiles();
        } catch (error) {
            handleUploadError(error);
        } finally {
            setLoading(false);
        }
    });

    function setLoading(isLoading) {
        submitButton.disabled = isLoading;

        if (isLoading) {
            submitText.textContent = "Procesando...";
            return;
        }

        submitText.textContent = "Importar movimientos";
    }

    function showError(message) {
        errorMessage.textContent = message;

        errorAlert.classList.remove("d-none");

        successAlert.classList.add("d-none");
    }

    function showSuccess(message) {
        successMessage.textContent = message;

        successAlert.classList.remove("d-none");

        errorAlert.classList.add("d-none");
    }

    function clearMessages() {
        errorAlert.classList.add("d-none");

        successAlert.classList.add("d-none");
    }

    function handleUploadError(error) {
        let message = "Ocurrió un error al procesar los archivos.";

        if (error.response?.data?.message) {
            message = error.response.data.message;
        }

        showError(message);
    }

    function getCsrfToken() {
        return form.querySelector('input[name="_token"]').value;
    }
});
