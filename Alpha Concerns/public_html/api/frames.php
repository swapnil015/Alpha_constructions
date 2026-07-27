<?php
/**
 * Hero frame-sequence manifest.
 *
 * Lists every image in /assets/frames in natural sort order as JSON, so the
 * hero animation picks up whatever naming convention the sequence was exported
 * with (frame_0001.png, 0001.jpg, ezgif-frame-001.jpg, …). Re-exporting the
 * sequence needs no code change and no frame count to keep in sync.
 *
 * Response: { "count": 240, "frames": ["/assets/frames/…", …] }
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
// The list only changes when frames are redeployed.
header('Cache-Control: public, max-age=600');

$dir = dirname(__DIR__) . '/assets/frames';
$url = '/assets/frames/';

if (!is_dir($dir)) {
    http_response_code(404);
    echo json_encode([
        'count'  => 0,
        'frames' => [],
        'error'  => 'assets/frames does not exist',
    ]);
    exit;
}

$allowed = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
$files   = [];

foreach (scandir($dir) ?: [] as $entry) {
    if ($entry === '.' || $entry === '..') continue;
    if (!in_array(strtolower(pathinfo($entry, PATHINFO_EXTENSION)), $allowed, true)) continue;
    if (!is_file($dir . '/' . $entry)) continue;
    $files[] = $entry;
}

// Natural, case-insensitive sort so frame-2 precedes frame-10 even unpadded.
natcasesort($files);

echo json_encode([
    'count'  => count($files),
    'frames' => array_values(array_map(
        static fn(string $f): string => $url . rawurlencode($f),
        $files
    )),
], JSON_UNESCAPED_SLASHES);
