<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class HomepageUploadService
{
    private const BASE_DIR = '/public/uploads/homepage';
    private const IMAGE_MAX_BYTES = 3145728;
    private const PDF_MAX_BYTES = 5242880;
    private const RULES = [
        'headshot' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'mimes' => ['image/jpeg', 'image/png', 'image/webp'],
            'max_bytes' => self::IMAGE_MAX_BYTES,
        ],
        'cv_pdf' => [
            'extensions' => ['pdf'],
            'mimes' => ['application/pdf'],
            'max_bytes' => self::PDF_MAX_BYTES,
        ],
    ];

    public function __construct(private string $projectRoot)
    {
    }

    public function store(array $file, string $documentType): array
    {
        if (!isset(self::RULES[$documentType])) {
            throw new RuntimeException('This document type does not accept uploads.');
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed. Please choose a valid file and try again.');
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            throw new RuntimeException('Upload could not be verified.');
        }

        $originalName = (string) ($file['name'] ?? 'upload');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $rule = self::RULES[$documentType];

        if (!in_array($extension, $rule['extensions'], true)) {
            throw new RuntimeException('Invalid file type for this document.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $rule['max_bytes']) {
            throw new RuntimeException('File size is not allowed for this document.');
        }

        $mimeType = $this->detectMimeType($tmpPath);
        if (!in_array($mimeType, $rule['mimes'], true)) {
            throw new RuntimeException('Uploaded file contents do not match the required document type.');
        }

        $targetDir = $this->projectRoot . self::BASE_DIR;
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Upload directory could not be created.');
        }

        $filename = $documentType . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetPath = $targetDir . '/' . $filename;

        if (!move_uploaded_file($tmpPath, $targetPath)) {
            throw new RuntimeException('Uploaded file could not be saved.');
        }

        return [
            'file_path' => '/uploads/homepage/' . $filename,
            'mime_type' => $mimeType,
            'file_size_bytes' => $size,
        ];
    }

    private function detectMimeType(string $path): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mimeType = finfo_file($finfo, $path) ?: '';
                finfo_close($finfo);
                if ($mimeType !== '') {
                    return $mimeType;
                }
            }
        }

        if (function_exists('mime_content_type')) {
            return (string) mime_content_type($path);
        }

        return '';
    }
}
