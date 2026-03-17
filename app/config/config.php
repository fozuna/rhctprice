<?php
return [
    'app' => [
        'name' => 'CT Price',
        'base_url' => 'http://localhost/ctprice',
        'env' => 'production'
    ],
    'security' => [
        'supervisor_email' => 'admin@seu-dominio.com.br',
        'supervisor_password' => 'troque-por-uma-senha-forte',
    ],
    'mail' => [
        'enabled' => true,
        'from' => 'no-reply@seu-dominio.com.br',
        'to_hr' => 'rh@seu-dominio.com.br',
    ],
    'logging' => [
        'level' => 'INFO',
        'alert_email' => 'devops@seu-dominio.com.br',
        'viewer_key' => 'defina-uma-chave-forte-aqui',
    ],
    'database' => [
        'dsn' => 'mysql:host=localhost;dbname=ctprice;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
    ],
];
