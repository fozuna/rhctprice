<?php
return [
    'env' => 'prod',
    'security' => [
        'supervisor_email' => 'admin@seu-dominio.com.br',
        'supervisor_password' => 'troque-por-uma-senha-forte',
    ],
    'mail' => [
        'enabled' => true,
        'from' => 'no-reply@seu-dominio.com.br',
        'to_hr' => 'rh@seu-dominio.com.br',
    ],
    'database' => [
        'dsn' => 'mysql:host=localhost;dbname=seu_banco;charset=utf8mb4',
        'user' => 'seu_usuario',
        'pass' => 'sua_senha',
    ],
];

