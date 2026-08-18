const form = document.querySelector('[data-upload-form]');

if (form) {
    const input = form.querySelector('[data-file-input]');
    const zone = form.querySelector('[data-drop-zone]');
    const fileName = form.querySelector('[data-file-name]');

    const displayFile = (file) => {
        if (!file) return;
        fileName.textContent = file.name;
    };
    input.addEventListener('change', () => displayFile(input.files[0]));
    ['dragenter', 'dragover'].forEach((event) => zone.addEventListener(event, (e) => { e.preventDefault(); zone.classList.add('border-blue-500', 'bg-blue-50'); }));
    ['dragleave', 'drop'].forEach((event) => zone.addEventListener(event, (e) => { e.preventDefault(); zone.classList.remove('border-blue-500', 'bg-blue-50'); }));
    zone.addEventListener('drop', (event) => { input.files = event.dataTransfer.files; displayFile(input.files[0]); });
}
