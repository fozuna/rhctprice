<?php
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="app-base" content="<?= Security::e($base ?? '') ?>">
  <meta name="csrf-token" content="<?= Security::e(Security::csrfToken()) ?>">
  <title>CT Price - Painel</title>
  <link rel="stylesheet" href="<?= $base ?>/assets/tailwind.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style> body { font-family: 'Montserrat', system-ui, -apple-system, sans-serif; } </style>
  <script src="<?= $base ?>/assets/admin.js" defer></script>
</head>
<body class="min-h-screen bg-gray-50">
  <?php include APP_PATH . '/views/layouts/sidebar.php'; ?>

  <main class="ml-64 px-6 py-8 min-h-screen pb-24">
    <?= $content ?>
  </main>

  <footer class="fixed bottom-0 left-64 right-0 border-t bg-white z-20">
    <div class="px-6 py-3 text-gray-500 text-sm text-center">
      © <?= date("Y"); ?> 
      <a href="https://traxter.com.br/" target="_blank">
        <strong>TRAXTER Sistemas e Automações</strong>
      </a> - Todos os direitos reservados.
    </div>
  </footer>
</body>
</html>
