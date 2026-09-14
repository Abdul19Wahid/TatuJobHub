<?php
/**
 * FileUpload.php — Secure file upload handler
 *
 * Usage:
 *   $result = FileUpload::upload($_FILES['resume'], 'resumes');
 *   if ($result['ok']) { $path = $result['path']; }
 *   else               { $error = $result['error']; }
 */
class FileUpload
{
    private static array $allowedResume = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    private static array $allowedImage = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];

    // ── Upload a file ─────────────────────────────────────────────────────────
    /**
     * @param  array  $file      $_FILES['field']
     * @param  string $type      'resumes' | 'avatars' | 'company-logos' | 'cover-letters' | 'documents'
     * @return array  ['ok'=>bool, 'path'=>string, 'filename'=>string, 'error'=>string]
     */
    public static function upload(array $file, string $type): array
    {
        // Basic upload error check
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => self::uploadErrorMessage($file['error'])];
        }

        // Validate upload type
        $validTypes = ['resumes', 'avatars', 'company-logos', 'cover-letters', 'documents'];
        if (!in_array($type, $validTypes)) {
            return ['ok' => false, 'error' => 'Invalid upload type.'];
        }

        // Determine rules by type
        $isImage  = in_array($type, ['avatars', 'company-logos']);
        $allowed  = $isImage ? self::$allowedImage : self::$allowedResume;
        $maxMb    = $isImage ? 2 : 5;

        // Size check
        $maxBytes = $maxMb * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            return ['ok' => false, 'error' => "File is too large. Maximum size is {$maxMb}MB."];
        }

        // MIME type check using fileinfo (more reliable than browser-reported type)
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowed)) {
            $ext = $isImage ? 'JPG, PNG, WEBP' : 'PDF, DOC, DOCX';
            return ['ok' => false, 'error' => "Invalid file type. Allowed: {$ext}."];
        }

        // Build safe filename: random hex + original extension
        $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeExt     = self::safeExtension($mimeType);
        $filename    = bin2hex(random_bytes(16)) . '.' . ($safeExt ?: $originalExt);

        // Destination
        $destDir  = ROOT_PATH . '/storage/' . $type . '/';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $destPath = $destDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['ok' => false, 'error' => 'Failed to save file. Please try again.'];
        }

        return [
            'ok'            => true,
            'path'          => '/storage/' . $type . '/' . $filename,
            'filename'      => $filename,
            'original_name' => $file['name'],
            'error'         => '',
        ];
    }

    // ── Delete an old file ────────────────────────────────────────────────────
    public static function delete(string $relativePath): void
    {
        if (!$relativePath) return;
        $full = ROOT_PATH . $relativePath;
        if (file_exists($full)) unlink($full);
    }

    // ── Get a public URL for serving a stored file ────────────────────────────
    // Storage files live outside /public so they must be served via a PHP proxy.
    // For now, we'll just use a serve.php route.
    public static function url(string $relativePath): string
    {
        if (!$relativePath) return '';
        return BASE_URL . '/file?path=' . urlencode(ltrim($relativePath, '/'));
    }

    // ── Map MIME to safe extension ────────────────────────────────────────────
    private static function safeExtension(string $mime): string
    {
        return match($mime) {
            'image/jpeg'     => 'jpg',
            'image/png'      => 'png',
            'image/webp'     => 'webp',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            default          => '',
        };
    }

    // ── Human-readable upload error ───────────────────────────────────────────
    private static function uploadErrorMessage(int $code): string
    {
        return match($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File exceeds maximum allowed size.',
            UPLOAD_ERR_PARTIAL   => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE   => 'No file was selected.',
            UPLOAD_ERR_NO_TMP_DIR => 'Temporary folder is missing.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            default              => 'An unknown upload error occurred.',
        };
    }
}
