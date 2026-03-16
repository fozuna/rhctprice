<?php
declare(strict_types=1);

use App\Core\Autoload;
use App\Core\Installer;

require __DIR__ . '/../app/core/Autoload.php';
Autoload::register();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$token = $_SESSION['install_csrf'] ?? '';
if ($token === '') {
    $token = bin2hex(random_bytes(32));
    $_SESSION['install_csrf'] = $token;
}

$requirements = Installer::requirements();
$allOk = true;
foreach ($requirements as $item) {
    if (!$item['ok']) {
        $allOk = false;
    }
}

$messages = [];
$success = false;
$selfDeleted = false;

if (Installer::isInstalled()) {
    http_response_code(403);
    $messages[] = 'Instalador bloqueado: a aplicação já está instalada.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Installer::isInstalled()) {
    $postedToken = (string)($_POST['csrf'] ?? '');
    if (!hash_equals($token, $postedToken)) {
        $messages[] = 'Token de segurança inválido.';
    } else {
        $requiredKey = getenv('INSTALLER_KEY');
        if (is_string($requiredKey) && trim($requiredKey) !== '') {
            $postedKey = (string)($_POST['installer_key'] ?? '');
            if (!hash_equals(trim($requiredKey), trim($postedKey))) {
                $messages[] = 'Chave do instalador inválida.';
            }
        }
    }

    if (count($messages) === 0) {
        try {
            $result = Installer::run([
                'app_env' => (string)($_POST['app_env'] ?? 'prod'),
                'db_dsn' => (string)($_POST['db_dsn'] ?? ''),
                'db_user' => (string)($_POST['db_user'] ?? ''),
                'db_pass' => (string)($_POST['db_pass'] ?? ''),
                'mail_from' => (string)($_POST['mail_from'] ?? ''),
                'mail_to_hr' => (string)($_POST['mail_to_hr'] ?? ''),
                'supervisor_email' => (string)($_POST['supervisor_email'] ?? ''),
                'supervisor_password' => (string)($_POST['supervisor_password'] ?? ''),
                'admin_email' => (string)($_POST['admin_email'] ?? ''),
                'admin_password' => (string)($_POST['admin_password'] ?? ''),
            ], function (string $line) use (&$messages): void {
                $messages[] = $line;
            });
            $success = true;
            $selfDeleted = (bool)($result['self_delete'] ?? false);
        } catch (Throwable $e) {
            $messages[] = 'Erro na instalação: ' . $e->getMessage();
        }
    }
}

$isLocked = Installer::isInstalled();
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Instalador CT Price</title>
  <style>
    body{font-family:Montserrat,system-ui,-apple-system,sans-serif;background:#f7fafc;margin:0}
    .wrap{max-width:900px;margin:24px auto;padding:0 16px}
    .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;box-shadow:0 2px 10px rgba(15,23,42,.06)}
    h1{margin:0 0 12px;color:#0f172a}
    h2{font-size:18px;margin:18px 0 10px;color:#0f172a}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .field{display:flex;flex-direction:column;gap:6px}
    label{font-size:13px;color:#334155}
    input,select{border:1px solid #cbd5e1;border-radius:8px;padding:10px;font-size:14px}
    button{background:#00222C;color:#fff;border:0;border-radius:10px;padding:12px 16px;font-weight:600;cursor:pointer}
    button:disabled{opacity:.5;cursor:not-allowed}
    .ok{color:#166534}
    .bad{color:#991b1b}
    .log{background:#0b1220;color:#dbeafe;padding:12px;border-radius:10px;white-space:pre-wrap;font-size:12px;max-height:260px;overflow:auto}
    .note{font-size:13px;color:#475569}
    @media(max-width:760px){.grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Instalador Web CT Price</h1>
      <p class="note">Preencha os dados e clique em instalar. O processo cria configuração local, importa banco, aplica migrações e prepara diretórios.</p>

      <h2>Requisitos do servidor</h2>
      <ul>
        <?php foreach ($requirements as $req): ?>
          <li class="<?= $req['ok'] ? 'ok' : 'bad' ?>">
            <?= $req['ok'] ? 'OK' : 'FALHA' ?> - <?= htmlspecialchars($req['label']) ?>
          </li>
        <?php endforeach; ?>
      </ul>

      <?php if ($isLocked): ?>
        <p class="bad"><strong>Instalador bloqueado:</strong> instalação já concluída.</p>
      <?php else: ?>
      <form method="post" class="grid">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($token) ?>">
        <div class="field">
          <label>Ambiente</label>
          <select name="app_env">
            <option value="prod">prod</option>
            <option value="dev">dev</option>
          </select>
        </div>
        <?php if (is_string(getenv('INSTALLER_KEY')) && trim((string)getenv('INSTALLER_KEY')) !== ''): ?>
        <div class="field">
          <label>Chave do instalador</label>
          <input name="installer_key" type="password" required>
        </div>
        <?php endif; ?>

        <div class="field"><label>DB DSN</label><input name="db_dsn" required placeholder="mysql:host=localhost;dbname=...;charset=utf8mb4"></div>
        <div class="field"><label>DB Usuário</label><input name="db_user" required></div>
        <div class="field"><label>DB Senha</label><input name="db_pass" type="password"></div>
        <div class="field"><label>E-mail RH</label><input name="mail_to_hr" required placeholder="rh@dominio.com"></div>
        <div class="field"><label>E-mail remetente</label><input name="mail_from" required placeholder="no-reply@dominio.com"></div>
        <div class="field"><label>E-mail supervisor</label><input name="supervisor_email" required></div>
        <div class="field"><label>Senha supervisor</label><input name="supervisor_password" type="password" required></div>
        <div class="field"><label>E-mail admin inicial</label><input name="admin_email" placeholder="admin@dominio.com"></div>
        <div class="field"><label>Senha admin inicial</label><input name="admin_password" type="password"></div>
        <div style="grid-column:1/-1;display:flex;gap:12px;align-items:center">
          <button type="submit" <?= $allOk ? '' : 'disabled' ?>>Instalar agora</button>
          <span class="note">Após sucesso, o instalador é desativado automaticamente.</span>
        </div>
      </form>
      <?php endif; ?>

      <?php if (count($messages) > 0): ?>
        <h2>Log da instalação</h2>
        <div class="log"><?php foreach ($messages as $line) { echo htmlspecialchars($line) . "\n"; } ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <p class="ok"><strong>Instalação concluída.</strong> <?= $selfDeleted ? 'O instalador foi removido automaticamente.' : 'Remova manualmente o arquivo public/install.php.' ?></p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>

