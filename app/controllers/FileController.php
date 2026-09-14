<?php
class FileController
{
    public function serve(): void
    {
        $raw      = $_GET['path'] ?? '';
        $relative = ltrim(rawurldecode($raw), '/');
        $relative = str_replace(['../', './', '..\\', '.\\', '..'], '', $relative);

        if (!str_starts_with($relative, 'storage/')) {
            http_response_code(403); echo 'Access denied.'; exit;
        }

        $fullPath = ROOT_PATH . '/' . $relative;
        if (!file_exists($fullPath) || !is_file($fullPath)) {
            http_response_code(404); echo 'File not found.'; exit;
        }

        $realFile    = realpath($fullPath);
        $realStorage = realpath(ROOT_PATH . '/storage');
        if (!$realFile || !$realStorage || !str_starts_with($realFile, $realStorage)) {
            http_response_code(403); echo 'Access denied.'; exit;
        }

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $realFile);
        finfo_close($finfo);

        $allowed = ['image/jpeg','image/jpg','image/png','image/webp','image/gif',
                    'application/pdf','application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        if (!in_array($mimeType, $allowed)) {
            http_response_code(403); echo 'File type not permitted.'; exit;
        }

        $cacheable = str_starts_with($mimeType, 'image/');
        if ($cacheable) {
            header('Cache-Control: public, max-age=604800, immutable');
        }
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($realFile));
        header('X-Content-Type-Options: nosniff');
        $disposition = ($mimeType === 'application/pdf' || $cacheable) ? 'inline' : 'attachment';
        header('Content-Disposition: ' . $disposition . '; filename="' . basename($realFile) . '"');
        readfile($realFile);
        exit;
    }
}
