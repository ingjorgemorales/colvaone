<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentAttachment;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class IncidentPdfService
{
    public function render(Incident $incident): string
    {
        $workDirectory = storage_path('app/private/pdf');
        File::ensureDirectoryExists($workDirectory);
        $options = new Options([
            'defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false,
            'isPhpEnabled' => false, 'isJavascriptEnabled' => false,
            'chroot' => storage_path('app/private'), 'tempDir' => $workDirectory, 'fontCache' => $workDirectory,
        ]);
        $evidence = $incident->attachments->map(fn ($attachment) => [
            'name' => $attachment->original_name, 'size' => $attachment->size,
            'is_image' => $attachment->is_image,
            'source' => $attachment->is_image ? $this->imageData($attachment) : null,
        ]);
        $pdf = new Dompdf($options);
        $logo = $this->logoData();
        $pdf->setPaper('A4');
        $pdf->loadHtml(view('incidents.pdf', compact('incident', 'evidence', 'logo'))->render());
        $pdf->render();
        $pdf->getCanvas()->page_text(40, 810, $incident->ticket.'  |  ColvaOne  |  {PAGE_NUM} / {PAGE_COUNT}', null, 8, [0.39, 0.45, 0.55]);

        return $pdf->output();
    }

    private function logoData(): string
    {
        // Flatten transparency onto white so the logo renders reliably on every PDF page.
        $source = imagecreatefrompng(public_path('images/logo-login.png'));
        $logo = imagecreatetruecolor(imagesx($source), imagesy($source));
        imagefill($logo, 0, 0, imagecolorallocate($logo, 255, 255, 255));
        imagecopy($logo, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));
        ob_start();
        imagepng($logo);
        $bytes = ob_get_clean();
        unset($source, $logo);

        return 'data:image/png;base64,'.base64_encode($bytes);
    }

    private function imageData(IncidentAttachment $attachment): ?string
    {
        $disk = Storage::disk('local');
        if (!$disk->exists($attachment->path)) { return null; }
        $path = $disk->path($attachment->path);
        $dimensions = @getimagesize($path);
        if (!$dimensions || $dimensions[0] * $dimensions[1] > 25000000) { return null; }
        $source = @imagecreatefromstring($disk->get($attachment->path));
        if (!$source) { return null; }

        // Reduce only the report copy; uploaded originals stay untouched.
        try {
            if ($attachment->mime_type === 'image/jpeg' && function_exists('exif_read_data')) {
                $orientation = (@exif_read_data($path)['Orientation']) ?? 1;
                if (in_array($orientation, [2, 4, 5, 7], true)) { imageflip($source, IMG_FLIP_HORIZONTAL); }
                $angle = match ($orientation) { 3, 4 => 180, 5, 6 => -90, 7, 8 => 90, default => 0 };
                if ($angle) { $source = imagerotate($source, $angle, 0); }
            }
            $scale = min(1, 1600 / imagesx($source), 1200 / imagesy($source));
            $width = max(1, (int) round(imagesx($source) * $scale));
            $height = max(1, (int) round(imagesy($source) * $scale));
            $copy = imagecreatetruecolor($width, $height);
            imagefill($copy, 0, 0, imagecolorallocate($copy, 255, 255, 255));
            imagecopyresampled($copy, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
            ob_start();
            imagejpeg($copy, null, 85);
            $bytes = ob_get_clean();

            return 'data:image/jpeg;base64,'.base64_encode($bytes);
        } finally {
            unset($source, $copy);
        }
    }
}
