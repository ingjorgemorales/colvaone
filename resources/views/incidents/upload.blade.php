<label id="incidentUploadLabel" for="attachments">Fotos o documentos de evidencia</label>
<input id="attachments" name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.png,.webp,.pdf" hidden>
<div class="incident-upload-toolbar">
    <button type="button" class="btn-secondary" id="incidentUploadButton"><i data-lucide="paperclip" style="width:16px;height:16px"></i> Adjuntar archivos</button>
    <span id="incidentUploadCount" class="incident-muted" aria-live="polite">Ningún archivo seleccionado</span>
    <button type="button" class="incident-icon" id="incidentUploadClear" title="Quitar archivos seleccionados" aria-label="Quitar archivos seleccionados" hidden><i data-lucide="x"></i></button>
</div>
<ul id="incidentUploadFiles" class="incident-upload-files"></ul>
<div class="incident-muted">JPG, PNG, WEBP o PDF. Hasta 10 archivos, máximo 10 MB por archivo.</div>
<script>
    (() => {
        const input = document.getElementById('attachments');
        const count = document.getElementById('incidentUploadCount');
        const files = document.getElementById('incidentUploadFiles');
        const clear = document.getElementById('incidentUploadClear');
        let selected = [];
        document.getElementById('incidentUploadButton').addEventListener('click', () => input.click());
        function updateFiles() {
            // Rebuild the submitted FileList after adding or removing a selection.
            const transfer = new DataTransfer();
            selected.forEach(file => transfer.items.add(file));
            input.files = transfer.files;
            count.textContent = selected.length ? `${selected.length} ${selected.length === 1 ? 'archivo seleccionado' : 'archivos seleccionados'}` : 'Ningún archivo seleccionado';
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
        input.addEventListener('change', () => {
            for (const file of input.files) {
                const duplicate = selected.some(existing => existing.name === file.name && existing.size === file.size && existing.type === file.type && existing.lastModified === file.lastModified);
                if (!duplicate) selected.push(file);
            }
            updateFiles();
        });
        clear.addEventListener('click', () => { selected = []; updateFiles(); });
        input.form?.addEventListener('reset', () => { selected = []; updateFiles(); });
    })();
</script>
