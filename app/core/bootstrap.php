<?php
use App\Core\Autoload;
use App\Core\Security;
use App\Core\Config;
use App\Core\Logger;

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
Logger::init($app);

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
    http_response_code(500);
    Logger::exception($e, 'CRITICAL', Logger::captureContext(500));
    error_log('[UNCAUGHT] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=UTF-8');
    }
    if (($app['env'] ?? 'prod') !== 'dev') {
        $ref = substr(hash('sha256', $e->getMessage() . '|' . $e->getFile() . '|' . $e->getLine()), 0, 12);
        echo 'Erro interno do servidor. Referência: ' . $ref;
        return;
    }
    echo 'Erro interno do servidor.';
    echo "\n\n";
    echo (string)$e;
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    $level = 'WARNING';
    if (in_array($severity, [E_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
        $level = 'ERROR';
    } elseif (in_array($severity, [E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED], true)) {
        $level = 'INFO';
    }
    Logger::log($level, $message, Logger::captureContext(http_response_code(), [
        'php_error' => ['severity' => $severity, 'file' => $file, 'line' => $line],
    ]));
    return false;
});

register_shutdown_function(function (): void {
    $last = error_get_last();
    if (!is_array($last)) {
        return;
    }
    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (!in_array((int)$last['type'], $fatalTypes, true)) {
        return;
    }
    http_response_code(500);
    Logger::critical('Fatal shutdown error', Logger::captureContext(500, ['fatal' => $last]));
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
