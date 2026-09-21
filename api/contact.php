<?php
/* Contact form handler -- stores the message and sends the email */
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/smtp.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Only POST requests are allowed.', 405);
}

/* Honeypot: bots fill in this hidden field */
if (!empty($_POST['website'])) {
    json_out(array('ok' => true, 'message' => 'Thank you! Your message has been sent.'));
}

/* One message per visitor every 30 seconds */
start_session();
if (!empty($_SESSION['last_contact']) && (time() - $_SESSION['last_contact']) < 30) {
    fail('Please wait a moment before sending another message.', 429);
}

$name    = clean_text(isset($_POST['name']) ? $_POST['name'] : '', 100);
$email   = trim(isset($_POST['email']) ? $_POST['email'] : '');
$subject = clean_text(isset($_POST['subject']) ? $_POST['subject'] : '', 150);
$message = clean_text(isset($_POST['message']) ? $_POST['message'] : '', 5000);

$errors = array();
if ($name === '')                                     $errors['name']    = 'Please enter your name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))       $errors['email']   = 'Please enter a valid email address.';
if ($subject === '')                                  $errors['subject'] = 'Please enter a subject.';
if (strlen($message) < 5)                             $errors['message'] = 'Please write a slightly longer message.';

if ($errors) {
    json_out(array('ok' => false, 'error' => 'Please correct the highlighted fields.', 'fields' => $errors), 422);
}

/* Guard against header injection */
if (preg_match('/[\r\n]/', $email)) {
    fail('That email address is not valid.', 422);
}

/* ---------- 1) always store the message ---------- */
$sentAt = date('Y-m-d H:i:s');

$messageId = db_save('messages', array(
    'name'       => $name,
    'email'      => $email,
    'subject'    => $subject,
    'message'    => $message,
    'ip'         => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
    'created_at' => $sentAt,
));

$_SESSION['last_contact'] = time();

/* ---------- 2) send the email ---------- */
$mailSent  = false;
$mailError = '';

if (SMTP_ENABLED && SMTP_PASS !== '') {
    $text = "New message from your portfolio website\n"
        . str_repeat('-', 40) . "\n"
        . "Name    : $name\n"
        . "Email   : $email\n"
        . "Subject : $subject\n"
        . "Date    : " . $sentAt . "\n"
        . str_repeat('-', 40) . "\n\n"
        . $message . "\n";

    $html = '<div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f8;padding:24px">'
        . '<div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e3e8ee">'
        . '<div style="background:#2C4C6C;color:#fff;padding:18px 24px;font-size:17px;font-weight:bold">New message from your portfolio</div>'
        . '<table style="width:100%;border-collapse:collapse;font-size:14px;color:#243b53">'
        . '<tr><td style="padding:12px 24px;color:#627d98;width:90px">Name</td><td style="padding:12px 24px">' . e($name) . '</td></tr>'
        . '<tr><td style="padding:12px 24px;color:#627d98">Email</td><td style="padding:12px 24px"><a href="mailto:' . e($email) . '">' . e($email) . '</a></td></tr>'
        . '<tr><td style="padding:12px 24px;color:#627d98">Subject</td><td style="padding:12px 24px">' . e($subject) . '</td></tr>'
        . '<tr><td style="padding:12px 24px;color:#627d98">Date</td><td style="padding:12px 24px">' . e($sentAt) . '</td></tr>'
        . '</table>'
        . '<div style="padding:0 24px 24px"><div style="color:#627d98;font-size:13px;margin-bottom:8px">Message</div>'
        . '<div style="background:#f4f6f8;border-radius:8px;padding:16px;font-size:14px;line-height:1.6;color:#243b53;white-space:pre-wrap">'
        . e($message) . '</div></div>'
        . '</div></div>';

    $smtp = new SimpleSMTP(SMTP_HOST, SMTP_PORT, SMTP_SECURE, SMTP_USER, SMTP_PASS, SMTP_VERIFY_CERT);
    $result = $smtp->send(
        MAIL_FROM,
        MAIL_FROM_NAME,
        MAIL_TO,
        'Portfolio: ' . $subject,
        $text,
        $html,
        $email               // Reply-To -> lets you reply straight to the sender
    );

    if ($result['ok']) {
        $mailSent = true;
        db_run('UPDATE messages SET mailed = 1 WHERE id = ?', array($messageId));
    } else {
        $mailError = $result['error'];
        error_log('[portfolio-contact] ' . $mailError);
    }
}

/* Always show the visitor a success message -- it has been stored either way.
   'mailed' is only for the admin/debug view: whether the email actually went out. */
json_out(array(
    'ok'      => true,
    'message' => 'Thank you, ' . $name . '! Your message has been received and I will reply soon.',
    'mailed'  => $mailSent,
));
