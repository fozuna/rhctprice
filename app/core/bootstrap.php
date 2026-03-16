<?php
use App\Core\Autoload;
use App\Core\Security;
use App\Core\Config;

require __DIR__ . '/Autoload.php';
Autoload::register();

define('BASE_PATH', dirname(__DIR__, 2));
define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'app');
define('PUBLIC_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'public');
define('STORAGE_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'storage');

// Configurações básicas
$app = Config::app();

$logsDir = STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs';
if (!is_dir($logsDir)) {
    @mkdir($logsDir, 0775, true);
}
$errorLogFile = $logsDir . DIRECTORY_SEPARATOR . 'app-error.log';
if (is_file($errorLogFile) || is_dir($logsDir)) {
    @ini_set('log_errors', '1');
    @ini_set('error_log', $errorLogFile);
}

// Segurança de sessão
Security::startSecureSession($app['security']['session_name']);
// Aplicar timeout de inatividade de 20 minutos
Security::enforceInactivityTimeout(1200);

// Erros em dev
if ($app['env'] === 'dev') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

set_exception_handler(function (\Throwable $e) use ($app): void {
    error_log('[UNCAUGHT] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    if (($app['env'] ?? 'prod') === 'dev') {
        echo '<h1>Erro interno</h1><pre>' . htmlspecialchars((string)$e) . '</pre>';
        return;
    }
    echo 'Erro interno do servidor.';
});

// Garantir diretório de currículos
if (!is_dir(STORAGE_PATH . DIRECTORY_SEPARATOR . 'resumes')) {
    @mkdir(STORAGE_PATH . DIRECTORY_SEPARATOR . 'resumes', 0775, true);
}

if (!is_dir(STORAGE_PATH . DIRECTORY_SEPARATOR . 'ratelimit')) {
    @mkdir(STORAGE_PATH . DIRECTORY_SEPARATOR . 'ratelimit', 0775, true);
}

if (!is_dir(STORAGE_PATH . DIRECTORY_SEPARATOR . 'audit')) {
    @mkdir(STORAGE_PATH . DIRECTORY_SEPARATOR . 'audit', 0775, true);
}

// Garantir diretório público para logos
$logosDir = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'logos';
if (!is_dir($logosDir)) {
    @mkdir($logosDir, 0775, true);
}
