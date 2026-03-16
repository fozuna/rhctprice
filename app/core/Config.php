<?php
namespace App\Core;

class Config
{
    private static ?string $cachedBaseUrl = null;
    private static ?string $cachedVersion = null;

    public static function app(): array
    {
        $baseUrl = self::baseUrl();
        return [
            'name' => 'CT Price - Gestão de Currículos',
            'product_name' => 'TRAXTER RH',
            'version' => self::version(),
            'base_url' => $baseUrl,
            'env' => 'dev',
            'security' => [
                'csrf_key' => 'ctprice_csrf_token',
                'session_name' => 'CTPRICESESSID',
                'supervisor_email' => 'admin@traxter.com.br',
                'supervisor_password' => 'xsW8c#nM?TdvmpxgX&u5',
                'allowed_upload_mime' => ['application/pdf'],
                'max_upload_bytes' => 5 * 1024 * 1024, // 5MB
                'allowed_image_mime' => ['image/png','image/jpeg','image/webp'],
                'max_image_bytes' => 2 * 1024 * 1024, // 2MB
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
            $raw = @shell_exec('git rev-parse --short HEAD 2>/dev/null');
            $hash = trim((string)$raw);
            if ($hash !== '') {
                self::$cachedVersion = $hash;
                return self::$cachedVersion;
            }
        }
        self::$cachedVersion = 'dev';
        return self::$cachedVersion;
    }
}
