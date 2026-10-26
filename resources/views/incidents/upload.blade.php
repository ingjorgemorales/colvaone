<label id="incidentUploadLabel" for="attachments">Imágenes de evidencia</label>
<input id="attachments" name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.png" aria-describedby="incidentUploadHelp incidentUploadError" hidden>
<div class="incident-upload-toolbar">
    <button type="button" class="btn-secondary" id="incidentUploadButton" aria-describedby="incidentUploadHelp incidentUploadError"><i data-lucide="image-plus" style="width:16px;height:16px"></i> Adjuntar imágenes</button>
    <span id="incidentUploadCount" class="incident-muted" aria-live="polite">Ninguna imagen seleccionada</span>
    <button type="button" class="incident-icon" id="incidentUploadClear" title="Quitar imágenes seleccionadas" aria-label="Quitar imágenes seleccionadas" hidden><i data-lucide="x"></i></button>
</div>
<ul id="incidentUploadFiles" class="incident-upload-files"></ul>
<div id="incidentUploadError" class="incident-upload-error" role="alert" hidden></div>
<div id="incidentUploadHelp" class="incident-muted">Solo imágenes JPG o PNG. Hasta 10 imágenes, máximo 10 MB por imagen.</div>
<script>
    (() => {
        const input = document.getElementById('attachments');
        const count = document.getElementById('incidentUploadCount');
        const files = document.getElementById('incidentUploadFiles');
        const clear = document.getElementById('incidentUploadClear');
        const error = document.getElementById('incidentUploadError');
        let selected = [];
        document.getElementById('incidentUploadButton').addEventListener('click', () => input.click());
        function updateFiles() {
            // Rebuild the submitted FileList after adding or removing a selection.
            const transfer = new DataTransfer();
            selected.forEach(file => transfer.items.add(file));
            input.files = transfer.files;
            count.textContent = selected.length ? `${selected.length} ${selected.length === 1 ? 'imagen seleccionada' : 'imágenes seleccionadas'}` : 'Ninguna imagen seleccionada';
            clear.hidden = selected.length === 0;
            files.replaceChildren(...selected.map((file, index) => {
                const item = document.createElement('li');
                const name = document.createElement('span');
                name.textContent = `${file.name} (${new Intl.NumberFormat('es-CO', { maximumFractionDigits: 1 }).format(file.size / 1024)} KB)`;
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'incident-icon disable';
                remove.title = `Quitar ${file.name}`;
                remove.setAttribute('aria-label', remove.title);
                const icon = document.createElement('i');
                icon.setAttribute('data-lucide', 'x');
                remove.append(icon);
                remove.addEventListener('click', () => {
                    selected.splice(index, 1);
                    updateFiles();
                    const next = files.querySelectorAll('button');
                    (next[Math.min(index, next.length - 1)] ?? document.getElementById('incidentUploadButton')).focus();
                });
                item.append(name, remove);
                return item;
            }));
            window.lucide?.createIcons();
        }
        function clearError() {
            error.textContent = '';
            error.hidden = true;
        }
        input.addEventListener('change', () => {
            const rejected = [];
            for (const file of input.files) {
                if (!/\.(jpe?g|png)$/i.test(file.name) || (file.type && !['image/jpeg', 'image/png'].includes(file.type))) {
                    rejected.push(file.name);
                    continue;
                }
                const duplicate = selected.some(existing => existing.name === file.name && existing.size === file.size && existing.type === file.type && existing.lastModified === file.lastModified);
                if (!duplicate) selected.push(file);
            }
            clearError();
            if (rejected.length) {
                error.textContent = `Solo puedes subir imágenes JPG o PNG. No se agregaron: ${rejected.join(', ')}.`;
                error.hidden = false;
            }
            updateFiles();
        });
        clear.addEventListener('click', () => { selected = []; clearError(); updateFiles(); });
        input.form?.addEventListener('reset', () => { selected = []; clearError(); updateFiles(); });
    })();
</script>
