<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function preview(Attachment $attachment): StreamedResponse
    {
        return $this->serve($attachment, false);
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        return $this->serve($attachment, true);
    }

    private function serve(
        Attachment $attachment,
        bool $download
    ): StreamedResponse {
        abort_unless(
            $attachment->attachable()->exists(),
            404,
            'Registro associado ao anexo não encontrado.'
        );

        abort_unless(
            in_array($attachment->mime_type, [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/webp',
            ], true),
            403,
            'Tipo de arquivo não permitido.'
        );

        $disk = Storage::disk($attachment->disk);

        abort_unless(
            $disk->exists($attachment->file_path),
            404,
            'Arquivo não encontrado.'
        );

        $stream = $disk->readStream($attachment->file_path);

        abort_unless(
            $stream !== false,
            404,
            'Não foi possível abrir o arquivo.'
        );

        $filename = Str::of($attachment->original_name)
            ->replace(["\r", "\n", '"'], '')
            ->toString();

        $disposition = $download ? 'attachment' : 'inline';

        return response()->stream(
            function () use ($stream) {
                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => $attachment->mime_type,
                'Content-Length' => $attachment->file_size,
                'Content-Disposition' => $disposition
                    . '; filename="' . $filename . '"',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }
}