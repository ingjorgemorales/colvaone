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
        document.getElementById('incidentUploadButton').addEventListener('click', () => input.click());
        function updateFiles() {
            const selected = Array.from(input.files);
            count.textContent = selected.length ? `${selected.length} ${selected.length === 1 ? 'archivo seleccionado' : 'archivos seleccionados'}` : 'Ningún archivo seleccionado';
            clear.hidden = selected.length === 0;
            files.replaceChildren(...selected.map(file => {
                const item = document.createElement('li');
                item.textContent = `${file.name} (${new Intl.NumberFormat('es-CO', { maximumFractionDigits: 1 }).format(file.size / 1024)} KB)`;
                return item;
            }));
        }
        input.addEventListener('change', updateFiles);
        clear.addEventListener('click', () => { input.value = ''; updateFiles(); });
    })();
</script>
