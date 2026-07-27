<?php
require __DIR__ . '/../../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
csrf_verify_or_die();

// Honeypot
if (!empty($_POST['website'])) { redirect('/contact'); }

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? 'General Enquiry');
$message = trim($_POST['message'] ?? '');

if ($name === '' || strlen($name) > 150)        { flash_set('error', 'Please provide a valid name.'); redirect('/contact'); }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { flash_set('error', 'Please provide a valid email.'); redirect('/contact'); }
if ($phone === '' || strlen($phone) > 30)       { flash_set('error', 'Please provide a phone number.'); redirect('/contact'); }
if ($message === '' || strlen($message) > 3000) { flash_set('error', 'Please provide a message.'); redirect('/contact'); }

if (!rate_limit_ok('contact')) {
    flash_set('error', 'Too many submissions from your network. Please try again in an hour.');
    redirect('/contact');
}

try {
    db_exec(
        "INSERT INTO inquiries (name, email, phone, subject, message, source_page, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$name, $email, $phone, $subject, $message, $_SERVER['HTTP_REFERER'] ?? '', client_ip()]
    );
} catch (Throwable $e) {
    error_log('Inquiry insert failed: ' . $e->getMessage());
    flash_set('error', 'Sorry — something went wrong. Please try again.');
    redirect('/contact');
}

// Notify admin
$adminBody = '<h2>New enquiry — ' . e(SITE_NAME) . '</h2>
<p><strong>Name:</strong> ' . e($name) . '<br>
<strong>Email:</strong> ' . e($email) . '<br>
<strong>Phone:</strong> ' . e($phone) . '<br>
<strong>Subject:</strong> ' . e($subject) . '</p>
<p><strong>Message:</strong><br>' . nl2br(e($message)) . '</p>';
send_mail(setting('email_primary', ADMIN_EMAIL), 'New enquiry from ' . $name, $adminBody, '', $email);

// Auto-reply
$reply = '<p>Hi ' . e($name) . ',</p>
<p>Thanks for reaching out to ' . e(SITE_NAME) . '. We have received your enquiry and will respond within one working day.</p>
<p>— Team ' . e(SITE_NAME) . '</p>';
send_mail($email, 'We received your message — ' . SITE_NAME, $reply);

flash_set('success', 'Thank you. Your message has been sent — we will be in touch shortly.');
redirect('/contact');
