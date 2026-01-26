<?php

namespace App\Services;

class Uploader
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct()
    {
    }

    /**
     * Convert an image file to Base64.
     *
     * @param array $file The file array from $_FILES['key']
     * @return string The base64 encoded string with data URI scheme
     * @throws \Exception If validation fails or read fails
     */
    public function upload(array $file): string
    {
        // Check for upload error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("Upload error code: " . $file['error']);
        }

        // Validate extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new \Exception("Invalid file extension. Allowed: " . implode(', ', self::ALLOWED_EXTENSIONS));
        }

        // Validate MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new \Exception("Invalid file type (MIME). Allowed: " . implode(', ', self::ALLOWED_EXTENSIONS));
        }

        // Read file content
        $content = file_get_contents($file['tmp_name']);
        if ($content === false) {
            throw new \Exception("Failed to read file content.");
        }

        // Convert to Base64
        $base64 = base64_encode($content);
        
        // Return Data URI
        return 'data:' . $mimeType . ';base64,' . $base64;
    }
}

