<?php
// Services/MailService.php
// SwiftMailer wrapper for sending notification emails via Gmail

// Prefer SwiftMailer in vendor; fallback to user-provided folder.
$swiftPath = __DIR__ . '/../vendor/swiftmailer/swiftmailer/lib/swift_required.php';
if (!file_exists($swiftPath)) {
    $swiftPath = 'C:/Users/ASUS/Desktop/swiftmailer-master/lib/swift_required.php';
}
if (!file_exists($swiftPath)) {
    throw new RuntimeException('SwiftMailer not found. Place it under vendor/swiftmailer or at C:/Users/ASUS/Desktop/swiftmailer-master');
}
require_once $swiftPath;

class MailService {
    private $mailer;
    private $notifyEmail;
    private $config;
    private $lastError = null;
    private $lastDebug = [];

    public function __construct() {
        $this->config = $this->getConfig();
        $this->notifyEmail = $this->config['notify_email'];
        $this->configure($this->config);
    }

    /**
     * Configure SwiftMailer for Gmail SMTP.
     */
    private function configure(array $config): void {
        $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
            ->setUsername($config['username'])
            ->setPassword($config['app_password']);

        // Relaxed SSL options for local dev; tighten in production.
        $transport->setStreamOptions([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);

        $this->mailer = new Swift_Mailer($transport);
    }

    public function sendNewReclamation(string $titre, string $userId): bool {
        $subject = 'Nouvelle réclamation créée';
        $body = "<p>Une nouvelle réclamation a été créée.</p>"
              . "<p><strong>Titre :</strong> " . htmlspecialchars($titre) . "</p>"
              . "<p><strong>Utilisateur :</strong> " . htmlspecialchars($userId) . "</p>";
        return $this->dispatch($subject, $body);
    }

    public function sendAdminResponse(int $reclamationId, string $message): bool {
        $subject = 'Réponse administrateur à une réclamation';
        $body = "<p>L'administrateur a répondu à une réclamation.</p>"
              . "<p><strong>ID Réclamation :</strong> " . htmlspecialchars((string)$reclamationId) . "</p>"
              . "<p><strong>Réponse :</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
        return $this->dispatch($subject, $body);
    }

    /**
     * Low-level send with error trapping.
     */
    private function dispatch(string $subject, string $body): bool {
        try {
            $message = (new Swift_Message($subject))
                ->setFrom([$this->config['from_email'] => $this->config['from_name']])
                ->setTo([$this->notifyEmail])
                ->setBody($body, 'text/html', 'UTF-8')
                ->addPart(strip_tags(str_replace('<br>', "\n", $body)), 'text/plain', 'UTF-8');

            $this->mailer->send($message);
            return true;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log('Mail error: ' . $this->lastError);
            return false;
        }
    }

    public function getLastError(): ?string {
        return $this->lastError;
    }

    public function getLastDebug(): array {
        return $this->lastDebug;
    }

    /**
     * Inline config: read Gmail creds from environment, fallback defaults.
     */
    private function getConfig(): array {
        $username = $this->readEnvValue(['GMAIL_USERNAME']) ?: 'lamouchi2002@gmail.com';
        $password = $this->readEnvValue(['GMAIL_APP_PASSWORD']);
        if (empty($password)) {
            throw new RuntimeException('GMAIL_APP_PASSWORD is missing. Set it in the environment for email delivery.');
        }

        return [
            'username' => $username,
            'app_password' => $password,
            'from_email' => $username,
            'from_name' => 'Jurispaix',
            'notify_email' => $this->readEnvValue(['GMAIL_NOTIFY_EMAIL']) ?: 'lamouchi2002@gmail.com'
        ];
    }

    private function readEnvValue(array $keys): ?string {
        foreach ($keys as $key) {
            $val = getenv($key);
            if (!empty($val)) return $val;
            if (!empty($_ENV[$key])) return $_ENV[$key];
            if (!empty($_SERVER[$key])) return $_SERVER[$key];
        }
        return null;
    }
}
?>
