<?php
class Config
{
    public static function get(): array
    {
        $default = [
            'app' => [
                'name' => 'CT Price',
                'base_url' => 'http://localhost/ctprice',
                'env' => 'production'
            ],
            'database' => [
                'dsn' => 'mysql:host=localhost;dbname=DB;charset=utf8mb4',
                'user' => 'USER',
                'pass' => 'PASS'
            ],
            'security' => [
                'csrf_key' => 'ctprice_csrf_token',
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

        $configFile = dirname(__DIR__) . '/config/config.php';
        if (is_file($configFile)) {
            $custom = require $configFile;
            if (is_array($custom)) {
                $default = array_replace_recursive($default, $custom);
            }
        }
        return $default;
    }

    public static function app(): array
    {
        $cfg = self::get();
        return [
            'name' => $cfg['app']['name'],
            'product_name' => $cfg['app']['name'],
            'version' => '1.0.0',
            'base_url' => $cfg['app']['base_url'],
            'env' => $cfg['app']['env'],
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
