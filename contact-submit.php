<?php
/**
 * Contact form handler. Now wired to includes/mailer.php.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/mailer.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$subject_field = trim($_POST['subject'] ?? '');
$enquiry_type = trim($_POST['enquiry_type'] ?? '');
$message = trim($_POST['message'] ?? '');
$submitted = $name !== '' && $email !== '' && $message !== '' && filter_var($email, FILTER_VALIDATE_EMAIL);
$mail_sent = false;

if ($submitted) {
    $body = email_template('New Contact Form Submission', '
        <p><strong>Name:</strong> ' . htmlspecialchars($name) . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
        <p><strong>Phone:</strong> ' . htmlspecialchars($phone ?: '-') . '</p>
        <p><strong>Enquiry Type:</strong> ' . htmlspecialchars($enquiry_type ?: '-') . '</p>
        <p><strong>Subject:</strong> ' . htmlspecialchars($subject_field ?: '-') . '</p>
        <p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($message)) . '</p>
    ');
    $mail_sent = send_email(SMTP_USER, 'Verve Creative Studio', 'New Contact Form Submission — ' . ($subject_field ?: $name), $body, '', $email, $name);
}

$page_title = 'Message Received | Verve Creative Studio';
$noindex = true;
require __DIR__ . '/includes/header.php';
?>
<section style="padding:100px 0;text-align:center;">
  <div class="wrap" style="max-width:560px;">
    <?php if ($submitted): ?>
      <p class="eyebrow" style="justify-content:center;">Message Received</p>
      <h1 style="font-size:32px;color:var(--cream);margin-bottom:16px;">Thank You, <?php echo htmlspecialchars($name); ?>.</h1>
      <p style="color:var(--muted);font-size:15px;margin-bottom:12px;">We've received your message and will get back to you soon.</p>
      <?php if (!$mail_sent): ?>
      <p style="color:var(--muted-dim);font-size:12.5px;">Note: the notification email couldn't be sent just now — if this persists, check the SMTP configuration.</p>
      <?php endif; ?>
    <?php else: ?>
      <h1 style="font-size:28px;color:var(--cream);margin-bottom:16px;">Something's Missing</h1>
      <p style="color:var(--muted);font-size:15px;">Please go back and fill in all required fields with a valid email address.</p>
    <?php endif; ?>
    <a href="<?php echo SITE_URL; ?>/" class="btn btn-solid" style="margin-top:24px;display:inline-flex;">Return Home</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
