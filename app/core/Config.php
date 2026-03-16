<?php
namespace App\Core;

class Config
{
    private static ?string $cachedBaseUrl = null;
    private static ?string $cachedVersion = null;

    public static function app(): array
    {
        $baseUrl = self::baseUrl();
        $config = [
            'name' => 'CT Price - Gestão de Currículos',
            'product_name' => 'TRAXTER RH',
            'version' => self::version(),
            'base_url' => $baseUrl,
            'env' => 'dev',
            'security' => [
                'csrf_key' => 'ctprice_csrf_token',
                'session_name' => 'CTPRICESESSID',
                'supervisor_email' => 'admin@traxter.com.br',
                'supervisor_password' => '',
                'allowed_upload_mime' => ['application/pdf'],
                'max_upload_bytes' => 5 * 1024 * 1024,
                'allowed_image_mime' => ['image/png','image/jpeg','image/webp'],
                'max_image_bytes' => 2 * 1024 * 1024,
            ],
            'mail' => [
                'enabled' => true,
                'from' => 'no-reply@ctprice.local',
                'to_hr' => 'rh@ctprice.local',
                'subject_new_application' => 'Nova candidatura recebida',
                'subject_password_recovery' => 'Recuperação de senha',
                'subject_password_changed' => 'Senha redefinida',
                'subject_supervisor_created' => 'Usuário Supervisor criado',
            ],
            'logging' => [
                'level' => 'INFO',
                'alert_email' => '',
                'viewer_key' => '',
            ],
            'database' => [
                'dsn' => 'mysql:host=127.0.0.1;dbname=ctprice;charset=utf8mb4',
                'user' => 'root',
                'pass' => '',
                'options' => [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::ATTR_TIMEOUT => 2,
                ],
            ],
        ];

        $localPath = dirname(__DIR__) . '/config/local.php';
        if (is_file($localPath)) {
            $local = require $localPath;
            if (is_array($local)) {
                $config = self::mergeRecursive($config, $local);
            }
        }

        $config = self::applyEnvOverrides($config);
        return $config;
    }

    public static function baseUrl(): string
    {
        if (self::$cachedBaseUrl !== null) {
            return self::$cachedBaseUrl;
        }

        $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath((string)$_SERVER['DOCUMENT_ROOT']) : false;
        $publicDir = realpath(__DIR__ . '/../../public');

        if ($documentRoot !== false && $publicDir !== false) {
            $doc = rtrim(str_replace('\\', '/', $documentRoot), '/');
            $pub = rtrim(str_replace('\\', '/', $publicDir), '/');

            if ($doc !== '' && strncmp($pub, $doc, strlen($doc)) === 0) {
                $rel = substr($pub, strlen($doc));
                $rel = $rel === false ? '' : $rel;
                $rel = '/' . ltrim($rel, '/');
                $rel = rtrim($rel, '/');
                self::$cachedBaseUrl = $rel === '/' ? '' : $rel;
                return self::$cachedBaseUrl;
            }
        }

        self::$cachedBaseUrl = '/public';
        return self::$cachedBaseUrl;
    }

    public static function version(): string
    {
        if (self::$cachedVersion !== null) {
            return self::$cachedVersion;
        }
        $envVersion = getenv('APP_VERSION');
        if (is_string($envVersion) && trim($envVersion) !== '') {
            self::$cachedVersion = trim($envVersion);
            return self::$cachedVersion;
        }
        if (function_exists('shell_exec')) {
            $nullDevice = strtoupper((string)PHP_OS_FAMILY) === 'WINDOWS' ? 'NUL' : '/dev/null';
            $raw = @shell_exec('git rev-parse --short HEAD 2>' . $nullDevice);
            $hash = trim((string)$raw);
            if ($hash !== '') {
                self::$cachedVersion = $hash;
                return self::$cachedVersion;
            }
        }
        self::$cachedVersion = 'dev';
        return self::$cachedVersion;
    }

    private static function applyEnvOverrides(array $config): array
    {
        $env = getenv('APP_ENV');
        if (is_string($env) && trim($env) !== '') {
            $config['env'] = trim($env);
        }

        $dbDsn = getenv('DB_DSN');
        if (is_string($dbDsn) && trim($dbDsn) !== '') {
            $config['database']['dsn'] = trim($dbDsn);
        }
        $dbUser = getenv('DB_USER');
        if (is_string($dbUser) && trim($dbUser) !== '') {
            $config['database']['user'] = trim($dbUser);
        }
        $dbPass = getenv('DB_PASS');
        if (is_string($dbPass) && $dbPass !== '') {
            $config['database']['pass'] = $dbPass;
        }

        $mailFrom = getenv('MAIL_FROM');
        if (is_string($mailFrom) && trim($mailFrom) !== '') {
            $config['mail']['from'] = trim($mailFrom);
        }
        $mailToHr = getenv('MAIL_TO_HR');
        if (is_string($mailToHr) && trim($mailToHr) !== '') {
            $config['mail']['to_hr'] = trim($mailToHr);
        }

        $logLevel = getenv('LOG_LEVEL');
        if (is_string($logLevel) && trim($logLevel) !== '') {
            $config['logging']['level'] = strtoupper(trim($logLevel));
        }
        $logAlert = getenv('LOG_ALERT_EMAIL');
        if (is_string($logAlert) && trim($logAlert) !== '') {
            $config['logging']['alert_email'] = trim($logAlert);
        }
        $viewerKey = getenv('LOG_VIEWER_KEY');
        if (is_string($viewerKey) && trim($viewerKey) !== '') {
            $config['logging']['viewer_key'] = trim($viewerKey);
        }

        $supEmail = getenv('SUPERVISOR_EMAIL');
        if (is_string($supEmail) && trim($supEmail) !== '') {
            $config['security']['supervisor_email'] = trim($supEmail);
        }
        $supPass = getenv('SUPERVISOR_PASSWORD');
        if (is_string($supPass) && $supPass !== '') {
            $config['security']['supervisor_password'] = $supPass;
        }

        return $config;
    }

    private static function mergeRecursive(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (array_key_exists($key, $base) && is_array($base[$key]) && is_array($value)) {
                $base[$key] = self::mergeRecursive($base[$key], $value);
                continue;
            }
            $base[$key] = $value;
        }
        return $base;
    }
}
