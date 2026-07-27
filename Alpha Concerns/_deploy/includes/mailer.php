<?php
if (!defined('ALPHA_BOOTSTRAP')) { http_response_code(403); exit('Forbidden'); }

/**
 * Send an email. Uses PHPMailer + SMTP if SMTP_ENABLED and PHPMailer is present
 * in /includes/PHPMailer/. Otherwise falls back to PHP mail().
 *
 * Drop these three files into /includes/PHPMailer/ to enable SMTP:
 *   PHPMailer.php  SMTP.php  Exception.php
 *   (download from https://github.com/PHPMailer/PHPMailer/releases)
 */
function send_mail(string $to, string $subject, string $htmlBody, string $textBody = '', string $replyTo = ''): bool {
    $textBody = $textBody ?: trim(strip_tags($htmlBody));

    if (SMTP_ENABLED && is_file(ROOT_PATH . '/includes/PHPMailer/PHPMailer.php')) {
        require_once ROOT_PATH . '/includes/PHPMailer/PHPMailer.php';
        require_once ROOT_PATH . '/includes/PHPMailer/SMTP.php';
        require_once ROOT_PATH . '/includes/PHPMailer/Exception.php';
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to);
            if ($replyTo) $mail->addReplyTo($replyTo);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody;
            return $mail->send();
        } catch (Throwable $e) {
            error_log('PHPMailer error: ' . $e->getMessage());
            return false;
        }
    }

    // Fallback: PHP mail()
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . ">\r\n";
    if ($replyTo) $headers .= 'Reply-To: ' . $replyTo . "\r\n";
    return @mail($to, $subject, $htmlBody, $headers);
}
