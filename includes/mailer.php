<?php
/**
 * SMTP mailer wrapper using PHPMailer, via the IONOS mailbox credentials in config.php.
 *
 * REQUIRES: `composer require phpmailer/phpmailer` run the same way stripe/stripe-php was
 * installed (local Composer + upload vendor/, since SSH shell access is restricted on this
 * hosting account) — added to the SAME vendor/ folder, not a separate one.
 *
 * mailer_is_configured() guards every send — if PHPMailer isn't installed or credentials are
 * still placeholders, send_email() returns false immediately rather than erroring out and
 * breaking whatever flow (booking, contact form) called it.
 */
require_once __DIR__ . '/config.php';

function mailer_is_configured(): bool {
    $vendor_autoload = __DIR__ . '/../vendor/autoload.php';
    return file_exists($vendor_autoload)
        && class_exists('PHPMailer\PHPMailer\PHPMailer')
        && SMTP_USER !== 'TODO' && SMTP_USER !== ''
        && SMTP_PASS !== 'TODO' && SMTP_PASS !== '';
}

/**
 * Sends an email via IONOS SMTP. Returns true on success, false otherwise — deliberately never
 * throws, so a mail failure never breaks the booking/payment/contact flow that triggered it.
 * Failures are logged server-side via error_log() for later debugging.
 */
function send_email(string $to, string $to_name, string $subject, string $html_body, string $text_body = '', string $reply_to = '', string $reply_to_name = ''): bool {
    if (!mailer_is_configured()) {
        error_log('send_email skipped — mailer not configured (missing vendor/ or SMTP credentials).');
        return false;
    }

    require_once __DIR__ . '/../vendor/autoload.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_USER, 'Verve Creative Studio');
        $mail->addAddress($to, $to_name);
        if ($reply_to !== '') {
            $mail->addReplyTo($reply_to, $reply_to_name ?: $reply_to);
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html_body;
        $mail->AltBody = $text_body ?: strip_tags($html_body);

        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log('send_email failed: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Wraps a block of HTML content in the site's basic email template — dark background emails
 * are unreliable across clients, so this deliberately uses a plain light layout instead of
 * trying to replicate the site's dark theme in an email.
 */
function email_template(string $title, string $body_html): string {
    return '
    <div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;padding:32px;color:#222;">
      <h2 style="color:#8a6b23;margin-bottom:20px;">' . htmlspecialchars($title) . '</h2>
      ' . $body_html . '
      <p style="margin-top:32px;font-size:12px;color:#888;">Verve Creative Studio &mdash; ' . htmlspecialchars(SITE_DOMAIN) . '</p>
    </div>';
}
