(function () {
    const modeInputs = document.querySelectorAll('input[name="background_mode"]');
    const colorRow = document.querySelector('.flvccustom-color-row');
    const uploadRow = document.querySelector('.flvccustom-upload-row');
    const urlRow = document.querySelector('.flvccustom-url-row');
    const uploadInput = document.getElementById('flvccustom-upload');
    const uploadPreview = document.getElementById('flvccustom-upload-preview');
    const urlInput = document.getElementById('flvccustom-url');
    const urlPreview = document.getElementById('flvccustom-url-preview');

    function toggleRows(value) {
        colorRow.style.display = value === 'color' ? '' : 'none';
        uploadRow.style.display = value === 'upload' ? '' : 'none';
        urlRow.style.display = value === 'url' ? '' : 'none';
    }

    modeInputs.forEach((input) => {
        input.addEventListener('change', (event) => {
            toggleRows(event.target.value);
        });
    });

    if (uploadInput) {
        uploadInput.addEventListener('change', (event) => {
            const [file] = event.target.files;
            if (!file) {
                uploadPreview.style.display = 'none';
                uploadPreview.src = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                uploadPreview.src = loadEvent.target.result;
                uploadPreview.style.display = '';
            };
            reader.readAsDataURL(file);
        });
    }

    if (urlInput) {
        const updateUrlPreview = () => {
            if (!urlInput.value) {
                urlPreview.style.display = 'none';
                urlPreview.src = '';
                return;
            }
            urlPreview.src = urlInput.value;
            urlPreview.style.display = '';
        };

        urlInput.addEventListener('input', updateUrlPreview);
        updateUrlPreview();
    }
})();
