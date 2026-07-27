<?php
if (!defined('ALPHA_BOOTSTRAP')) { http_response_code(403); exit('Forbidden'); }

/**
 * Validate + store an uploaded image. Returns relative URL or null on failure.
 * Auto-converts to WebP when GD is available; otherwise stores original.
 */
function upload_image(array $file, string $subdir = 'misc', string $altSlug = ''): ?string {
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > MAX_IMAGE_BYTES) return null;

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ALLOWED_IMAGE_MIME, true)) return null;

    $extMap = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];
    $ext    = $extMap[$mime] ?? 'jpg';

    $dir = UPLOADS_PATH . '/' . trim($subdir, '/');
    if (!is_dir($dir)) @mkdir($dir, 0755, true);

    $base = $altSlug ? slugify($altSlug) : bin2hex(random_bytes(8));
    $name = $base . '-' . substr(bin2hex(random_bytes(4)), 0, 6);

    // Try WebP conversion via GD
    if (function_exists('imagewebp') && in_array($mime, ['image/jpeg','image/png'], true)) {
        $src = $mime === 'image/jpeg' ? @imagecreatefromjpeg($file['tmp_name']) : @imagecreatefrompng($file['tmp_name']);
        if ($src) {
            if ($mime === 'image/png') {
                imagepalettetotruecolor($src);
                imagealphablending($src, true);
                imagesavealpha($src, true);
            }
            $out = $dir . '/' . $name . '.webp';
            imagewebp($src, $out, 82);
            imagedestroy($src);
            return UPLOADS_URL . '/' . trim($subdir, '/') . '/' . $name . '.webp';
        }
    }

    // Fallback: store original
    $out = $dir . '/' . $name . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $out)) return null;
    return UPLOADS_URL . '/' . trim($subdir, '/') . '/' . $name . '.' . $ext;
}

/**
 * Validate + store a CV outside web root. Returns absolute server path or null.
 */
function upload_cv(array $file): ?string {
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > MAX_DOCUMENT_BYTES) return null;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ALLOWED_DOC_MIME, true)) return null;
    $extMap = ['application/pdf'=>'pdf','application/msword'=>'doc','application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx'];
    $ext    = $extMap[$mime] ?? 'pdf';
    $dir    = CV_STORAGE_PATH . '/' . date('Y/m');
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $name   = date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    $out    = $dir . '/' . $name;
    return move_uploaded_file($file['tmp_name'], $out) ? $out : null;
}
