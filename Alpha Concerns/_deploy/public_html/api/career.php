<?php
require __DIR__ . '/../../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
csrf_verify_or_die();
if (!empty($_POST['website'])) { redirect('/careers'); }

$jobId       = (int)($_POST['job_id'] ?? 0);
$name        = trim($_POST['applicant_name'] ?? '');
$email       = trim($_POST['email'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$cover       = trim($_POST['cover_note'] ?? '');

$job = db_one('SELECT * FROM job_listings WHERE id = ? AND is_active = 1', [$jobId]);
if (!$job) { flash_set('error','Selected role is no longer available.'); redirect('/careers'); }
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('error','Please provide a valid name and email.'); redirect('/careers');
}
if (!isset($_FILES['cv'])) { flash_set('error','Please attach a CV.'); redirect('/careers'); }

if (!rate_limit_ok('career')) {
    flash_set('error','Too many submissions from your network. Try again later.'); redirect('/careers');
}

$cvPath = upload_cv($_FILES['cv']);
if (!$cvPath) {
    flash_set('error','CV upload failed. Please use PDF/DOC/DOCX, max 5MB.'); redirect('/careers');
}

try {
    db_exec(
        "INSERT INTO job_applications (job_id, applicant_name, email, phone, cover_note, cv_file_path)
         VALUES (?,?,?,?,?,?)",
        [$jobId, $name, $email, $phone, $cover, $cvPath]
    );
} catch (Throwable $e) {
    error_log('Career insert failed: ' . $e->getMessage());
    flash_set('error','Submission failed — please try again.');
    redirect('/careers');
}

$body = '<h2>New job application</h2>
<p><strong>Position:</strong> ' . e($job['title']) . '<br>
<strong>Name:</strong> ' . e($name) . '<br>
<strong>Email:</strong> ' . e($email) . '<br>
<strong>Phone:</strong> ' . e($phone) . '</p>
<p><strong>Cover note:</strong><br>' . nl2br(e($cover)) . '</p>
<p><em>CV stored at: ' . e($cvPath) . '. Download from the admin panel.</em></p>';
send_mail(setting('email_primary', ADMIN_EMAIL), 'New application: ' . $job['title'], $body, '', $email);

flash_set('success','Thank you. Your application has been received.');
redirect('/careers');
