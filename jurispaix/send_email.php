<?php
// Standalone SwiftMailer example using Gmail SMTP with env-based credentials

// Prefer SwiftMailer in vendor; fallback to user-provided folder.
$swiftPath = __DIR__ . '/vendor/swiftmailer/swiftmailer/lib/swift_required.php';
if (!file_exists($swiftPath)) {
    $swiftPath = 'C:/Users/ASUS/Desktop/swiftmailer-master/lib/swift_required.php';
}
if (!file_exists($swiftPath)) {
    throw new RuntimeException('SwiftMailer not found. Place it under vendor/swiftmailer or at C:/Users/ASUS/Desktop/swiftmailer-master');
}
require_once $swiftPath;

$paragraph = $_POST['paragraph'] ?? "Here is a default English paragraph. You can edit it before sending.";
$toEmail   = $_POST['to_email']  ?? 'lamouchi2002@gmail.com';
$toName    = $_POST['to_name']   ?? '';
$subject   = $_POST['subject']   ?? 'Custom Email from Jurispaix';

$username = getenv('GMAIL_USERNAME') ?: 'lamouchi2002@gmail.com';
$password = getenv('GMAIL_APP_PASSWORD') ?: '';
if (empty($password)) {
    throw new RuntimeException('GMAIL_APP_PASSWORD is missing. Set it in the environment for email delivery.');
}

$transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
    ->setUsername($username)
    ->setPassword($password);

$transport->setStreamOptions([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
    ],
]);

$mailer = new Swift_Mailer($transport);

$message = (new Swift_Message($subject))
    ->setFrom([$username => 'Jurispaix'])
    ->setTo([$toEmail => $toName ?: $toEmail])
    ->setBody(nl2br($paragraph), 'text/html', 'UTF-8')
    ->addPart(strip_tags($paragraph), 'text/plain', 'UTF-8');

try {
    $mailer->send($message);
    echo 'Email sent successfully!';
} catch (Exception $e) {
    error_log('Mail send error: ' . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
