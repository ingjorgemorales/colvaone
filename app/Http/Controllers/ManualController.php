<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManualController extends Controller
{
    private const DIRECTORY = 'app/private/manual';

    private const PREFERRED_FILE = 'manual-usuario.pdf';

    public function show(): BinaryFileResponse|View|Response
    {
        $path = $this->resolveManualPath();

        if ($path === null) {
            return response()->view('manual.unavailable', [], 404);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="manual-usuario.pdf"',
        ]);
    }

    private function resolveManualPath(): ?string
    {
        $directory = storage_path(self::DIRECTORY);

        if (! is_dir($directory)) {
            return null;
        }

        $preferred = $directory . DIRECTORY_SEPARATOR . self::PREFERRED_FILE;

        if (is_file($preferred)) {
            return $preferred;
        }

        $fallback = glob($directory . DIRECTORY_SEPARATOR . '*.pdf') ?: [];

        return $fallback[0] ?? null;
    }
}
