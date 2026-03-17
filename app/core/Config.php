<?php
class Config
{
    public static function get(): array
    {
        $config = [
            'app' => [
                'name' => 'CT Price',
                'base_url' => 'http://localhost/ctprice',
                'public_jobs_url' => 'http://localhost/ctprice/vagas',
                'env' => 'development'
            ],
            'database' => [
                'dsn' => '',
                'user' => '',
                'pass' => ''
            ],
            'security' => [
                'csrf_key' => 'csrf_token',
                'session_name' => 'CTPRICESESSID',
                'supervisor_email' => 'admin@seu-dominio.com.br',
                'supervisor_password' => 'troque-por-uma-senha-forte',
                'allowed_upload_mime' => ['application/pdf'],
                'max_upload_bytes' => 5 * 1024 * 1024,
                'allowed_image_mime' => ['image/png', 'image/jpeg', 'image/webp'],
                'max_image_bytes' => 2 * 1024 * 1024
            ],
            'mail' => [
                'enabled' => true,
                'from' => 'no-reply@seu-dominio.com.br',
                'to_hr' => 'rh@seu-dominio.com.br',
                'subject_new_application' => 'Nova candidatura recebida',
                'subject_password_recovery' => 'Recuperação de senha',
                'subject_password_changed' => 'Senha redefinida',
                'subject_supervisor_created' => 'Usuário Supervisor criado'
            ],
            'logging' => [
                'level' => 'INFO',
                'alert_email' => '',
                'viewer_key' => ''
            ]
        ];

        $legacyPath = __DIR__ . '/../config/config.php';
        if (file_exists($legacyPath)) {
            $legacy = require $legacyPath;
            if (is_array($legacy)) {
                $config = array_replace_recursive($config, $legacy);
            }
        }

        $localPath = __DIR__ . '/../config/local.php';
        if (file_exists($localPath)) {
            $local = require $localPath;
            if (is_array($local)) {
                $config = array_replace_recursive($config, $local);
            }
        }
        return $config;
    }

    public static function app(): array
    {
        $cfg = self::get();
        return [
            'name' => $cfg['app']['name'],
            'product_name' => $cfg['app']['name'],
            'version' => '1.0.0',
            'base_url' => $cfg['app']['base_url'],
            'public_jobs_url' => $cfg['app']['public_jobs_url'],
            'env' => $cfg['app']['env'] ?? 'development',
            'security' => $cfg['security'],
            'mail' => $cfg['mail'],
            'logging' => $cfg['logging'],
            'database' => [
                'dsn' => $cfg['database']['dsn'],
                'user' => $cfg['database']['user'],
                'pass' => $cfg['database']['pass'],
                'options' => [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::ATTR_TIMEOUT => 5
                ]
            ]
        ];
    }
}
