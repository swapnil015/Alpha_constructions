<?php
// Authenticated CV download — file lives outside web root.
$id = (int)($_GET['id'] ?? 0);
$r  = db_one("SELECT cv_file_path, applicant_name FROM job_applications WHERE id = ?", [$id]);
if (!$r || empty($r['cv_file_path']) || !is_file($r['cv_file_path'])) {
    http_response_code(404); exit('Not found');
}
$realBase = realpath(CV_STORAGE_PATH);
$realFile = realpath($r['cv_file_path']);
if (!$realFile || strpos($realFile, $realBase) !== 0) { http_response_code(403); exit('Forbidden'); }

$ext = pathinfo($realFile, PATHINFO_EXTENSION);
$dl  = preg_replace('/[^A-Za-z0-9._-]/','_', $r['applicant_name']) . '-cv.' . $ext;
header('Content-Type: application/octet-stream');
header('Content-Length: ' . filesize($realFile));
header('Content-Disposition: attachment; filename="' . $dl . '"');
header('X-Content-Type-Options: nosniff');
readfile($realFile);
exit;
