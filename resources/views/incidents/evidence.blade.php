<div class="incident-gallery">
    @forelse($incident->attachments as $attachment)
        @php($previewUrl = route('incidents.attachments.preview', [$incident, $attachment]))
        @php($downloadUrl = route('incidents.attachments.download', [$incident, $attachment]))
        <figure class="incident-attachment">
            @if($attachment->is_image)
                <a class="incident-photo-link" href="{{ $previewUrl }}" data-no-spinner data-incident-photo data-download="{{ $downloadUrl }}" data-name="{{ $attachment->original_name }}" aria-label="Abrir {{ $attachment->original_name }}">
                    <img src="{{ $previewUrl }}" alt="{{ $attachment->original_name }}" loading="lazy">
                </a>
            @else
                <a class="incident-document-link" href="{{ $previewUrl }}" target="_blank" rel="noopener" title="Abrir documento"><i data-lucide="file-text" style="width:36px;height:36px"></i></a>
            @endif
            <figcaption>
                <a class="incident-filename" href="{{ $previewUrl }}" @if($attachment->is_image) data-no-spinner data-photo-caption @else target="_blank" rel="noopener" @endif>{{ $attachment->original_name }}</a>
                <div class="incident-attachment-tools">
                    <span class="incident-muted">{{ number_format($attachment->size / 1024, 0) }} KB</span>
                    <a class="incident-icon" download href="{{ $downloadUrl }}" title="Descargar evidencia" aria-label="Descargar {{ $attachment->original_name }}"><i data-lucide="download"></i></a>
                </div>
                @if($editable ?? false)
                    <label class="incident-remove"><input type="checkbox" name="remove_attachments[]" value="{{ $attachment->id }}" @checked(in_array($attachment->id, old('remove_attachments', [])))> Retirar</label>
                @endif
            </figcaption>
        </figure>
    @empty
        <span class="incident-muted">Sin evidencias adjuntas.</span>
    @endforelse
</div>
@once
    <dialog class="incident-viewer" id="incidentPhotoViewer" aria-labelledby="incidentPhotoTitle">
        <div class="incident-viewer-toolbar">
            <span id="incidentPhotoTitle" class="incident-viewer-title"></span>
            <div class="incident-actions">
                <a id="incidentPhotoDownload" class="incident-icon" download title="Descargar foto" aria-label="Descargar foto"><i data-lucide="download"></i></a>
                <button id="incidentPhotoClose" class="incident-icon" type="button" title="Cerrar" aria-label="Cerrar visor"><i data-lucide="x"></i></button>
            </div>
        </div>
        <img id="incidentPhotoImage" class="incident-viewer-image" alt="">
        <p id="incidentPhotoError" class="incident-muted" role="alert" hidden>No se pudo cargar la foto.</p>
        <div class="incident-viewer-toolbar" style="margin-top:12px;margin-bottom:0">
            <button id="incidentPhotoPrevious" class="incident-icon" type="button" title="Foto anterior" aria-label="Foto anterior"><i data-lucide="chevron-left"></i></button>
            <span id="incidentPhotoCounter" class="incident-muted" aria-live="polite"></span>
            <button id="incidentPhotoNext" class="incident-icon" type="button" title="Foto siguiente" aria-label="Foto siguiente"><i data-lucide="chevron-right"></i></button>
        </div>
    </dialog>
    <script>
        (() => {
            const dialog = document.getElementById('incidentPhotoViewer');
            const photos = Array.from(document.querySelectorAll('[data-incident-photo]'));
            const image = document.getElementById('incidentPhotoImage');
            const error = document.getElementById('incidentPhotoError');
            const previous = document.getElementById('incidentPhotoPrevious');
            const next = document.getElementById('incidentPhotoNext');
            let index = 0;
            let priorOverflow;

            function showPhoto(position) {
                index = (position + photos.length) % photos.length;
                const photo = photos[index];
                error.hidden = true;
                image.alt = photo.dataset.name;
                image.src = photo.href;
                document.getElementById('incidentPhotoTitle').textContent = photo.dataset.name;
                document.getElementById('incidentPhotoDownload').href = photo.dataset.download;
                document.getElementById('incidentPhotoCounter').textContent = `${index + 1} / ${photos.length}`;
                previous.disabled = next.disabled = photos.length < 2;
            }

            photos.forEach((photo, position) => {
                const open = event => {
                    event.preventDefault();
                    showPhoto(position);
                    priorOverflow = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                    dialog.showModal();
                };
                photo.addEventListener('click', open);
                photo.closest('figure').querySelector('[data-photo-caption]').addEventListener('click', open);
            });
            image.addEventListener('error', () => { error.hidden = false; });
            previous.addEventListener('click', () => showPhoto(index - 1));
            next.addEventListener('click', () => showPhoto(index + 1));
            document.getElementById('incidentPhotoClose').addEventListener('click', () => dialog.close());
            dialog.addEventListener('close', () => { document.body.style.overflow = priorOverflow; });
            dialog.addEventListener('click', event => { if (event.target === dialog && !event.target.closest('.incident-viewer-toolbar')) {
                const bounds = dialog.getBoundingClientRect();
                if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
            } });
            dialog.addEventListener('keydown', event => {
                if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                    event.preventDefault();
                    showPhoto(index + (event.key === 'ArrowLeft' ? -1 : 1));
                }
            });
        })();
    </script>
@endonce
