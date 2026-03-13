<?php
declare(strict_types=1);

use App\Core\Autoload;
use App\Models\User;

require __DIR__ . '/../app/core/Autoload.php';
Autoload::register();

$email = $argv[1] ?? '';
$password = $argv[2] ?? '';

if ($email === '' || $password === '') {
    fwrite(STDERR, "Uso: php scripts/verify_password_cli.php <email> <senha>\n");
    exit(1);
}

$user = User::findByEmail($email);
if (!$user) {
    fwrite(STDERR, "Usuário não encontrado.\n");
    exit(1);
}

$ok = User::verifyPassword($user, $password);
echo $ok ? "VALID\n" : "INVALID\n";
exit($ok ? 0 : 2);

