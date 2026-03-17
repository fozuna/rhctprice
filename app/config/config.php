<?php
return [
    'app' => [
        'name' => 'CT Price',
        'base_url' => 'https://SEU_DOMINIO/rhctprice',
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
        'supervisor_password' => 'troque-por-uma-senha-forte'
    ],
    'mail' => [
        'enabled' => true,
        'from' => 'no-reply@seu-dominio.com.br',
        'to_hr' => 'rh@seu-dominio.com.br'
    ],
    'logging' => [
        'level' => 'INFO',
        'alert_email' => '',
        'viewer_key' => 'ALTERE_ESTA_CHAVE'
    ]
];

