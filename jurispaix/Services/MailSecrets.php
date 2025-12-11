<?php
// Services/MailSecrets.php
// Handles retrieval and safe storage of the Brevo SMTP credentials

class MailSecrets {
    private const SECRET_FILE = __DIR__ . '/../storage/mail_secret.php';

    public static function getConfig(): array {
        $username = self::readEnvValue(['BREVO_SMTP_USER']) ?: '9dd3ec001@smtp-brevo.com';
        $appPassword = self::resolveAppPassword();

        return [
            'username' => $username,
            'app_password' => $appPassword,
            'from_email' => self::readEnvValue(['BREVO_FROM_EMAIL']) ?: $username,
            'from_name' => self::readEnvValue(['BREVO_FROM_NAME']) ?: 'Jurispaix',
            'notify_email' => self::readEnvValue(['BREVO_NOTIFY_EMAIL']) ?: 'lamouchi2002@gmail.com'
        ];
    }

    private static function resolveAppPassword(): string {
        $env = self::readEnvValue(['BREVO_SMTP_PASS']);
        if (!empty($env)) {
            self::persist($env);
            return $env;
        }

        if (file_exists(self::SECRET_FILE)) {
            $data = include self::SECRET_FILE;
            if (is_array($data) && !empty($data['app_password'])) {
                return $data['app_password'];
            }
        }

        throw new RuntimeException('BREVO_SMTP_PASS is missing. Set it as an environment variable.');
    }

    private static function readEnvValue(array $keys): ?string {
        foreach ($keys as $key) {
            $val = getenv($key);
            if (!empty($val)) return $val;
            if (!empty($_ENV[$key])) return $_ENV[$key];
            if (!empty($_SERVER[$key])) return $_SERVER[$key];
        }
        return null;
    }

    private static function persist(string $token): void {
        $dir = dirname(self::SECRET_FILE);
        if (!is_dir($dir)) mkdir($dir, 0700, true);
        $content = "<?php\nreturn ['app_password' => '" . addslashes($token) . "'];\n";
        file_put_contents(self::SECRET_FILE, $content);
    }
}
?>
