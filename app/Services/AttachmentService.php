<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use Illuminate\Support\Collection;

class AttachmentService
{
    private const DISK = 'private';

    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    private const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function storeMany(
        Model $attachable,
        array $files,
        ?string $description = null
    ): Collection {
        $storedPaths = [];

        try {
            return DB::transaction(function () use (
                $attachable,
                $files,
                $description,
                &$storedPaths
            ) {
                $attachments = collect();

                foreach ($files as $file) {
                    if (!$file instanceof UploadedFile || !$file->isValid()) {
                        throw new RuntimeException(
                            'Um dos arquivos enviados é inválido.'
                        );
                    }

                    if ($file->getSize() > self::MAX_FILE_SIZE) {
                        throw new RuntimeException(
                            'Cada arquivo deve ter no máximo 10 MB.'
                        );
                    }

                    if (!in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
                        throw new RuntimeException(
                            'Formato de arquivo não permitido.'
                        );
                    }

                    $extension = strtolower(
                        $file->guessExtension()
                            ?: $file->getClientOriginalExtension()
                    );

                    $extension = preg_replace('/[^a-z0-9]/', '', $extension);

                    $directory = 'attachments/'
                        . Str::snake(class_basename($attachable))
                        . '/'
                        . $attachable->getKey();

                    $filename = Str::uuid() . '.' . $extension;

                    $path = Storage::disk(self::DISK)->putFileAs(
                        $directory,
                        $file,
                        $filename
                    );

                    if (!$path) {
                        throw new RuntimeException(
                            'Não foi possível armazenar um dos arquivos.'
                        );
                    }

                    $storedPaths[] = $path;

                    $attachment = Attachment::create([
                        'attachable_type' => $attachable->getMorphClass(),
                        'attachable_id' => $attachable->getKey(),
                        'uploaded_by' => Auth::id(),
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'disk' => self::DISK,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'description' => $description,
                    ]);

                    $attachments->push($attachment);
                }

                return $attachments;
            });
        } catch (Throwable $exception) {
            // Remove somente arquivos de uma tentativa que falhou.
            // Anexos já persistidos não são excluídos.
            if (!empty($storedPaths)) {
                Storage::disk(self::DISK)->delete($storedPaths);
            }

            throw $exception;
        }
    }
}