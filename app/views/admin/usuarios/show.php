<?php
$isActive = !empty($user->email_verified_at);
?>
<div class="max-w-2xl bg-white shadow rounded p-6">
  <div class="flex items-center justify-between">
    <h2 class="text-xl font-semibold text-ctpblue">Detalhes do usuário</h2>
    <a href="<?= $base ?>/admin/usuarios" class="text-ctpblue hover:text-ctgreen">Voltar</a>
  </div>

  <div class="mt-4 grid md:grid-cols-2 gap-4 text-sm">
    <div>
      <div class="text-gray-500">Nome completo</div>
      <div class="font-medium text-gray-900"><?= Security::e($user->nome) ?></div>
    </div>
    <div>
      <div class="text-gray-500">E-mail</div>
      <div class="font-medium text-gray-900"><?= Security::e($user->email) ?></div>
    </div>
    <div>
      <div class="text-gray-500">Permissão</div>
      <div class="font-medium text-gray-900"><?= Security::e(strtoupper($user->role)) ?></div>
    </div>
    <div>
      <div class="text-gray-500">Status</div>
      <span class="inline-flex mt-1 px-2 py-1 rounded text-xs font-semibold text-white <?= $isActive ? 'bg-ctgreen' : 'bg-red-500' ?>">
        <?= $isActive ? 'Ativo' : 'Inativo' ?>
      </span>
    </div>
    <div>
      <div class="text-gray-500">Data de cadastro</div>
      <div class="font-medium text-gray-900"><?= !empty($user->created_at) ? date('d/m/Y H:i', strtotime((string)$user->created_at)) : '-' ?></div>
    </div>
    <div>
      <div class="text-gray-500">Último reset de senha</div>
      <div class="font-medium text-gray-900"><?= !empty($user->last_password_reset_at) ? date('d/m/Y H:i', strtotime((string)$user->last_password_reset_at)) : '-' ?></div>
    </div>
  </div>

  <div class="mt-6 flex gap-2">
    <form action="<?= $base ?>/admin/usuarios/<?= (int)$user->id ?>/status" method="post">
      <input type="hidden" name="csrf" value="<?= Security::e($csrf) ?>">
      <input type="hidden" name="active" value="<?= $isActive ? '0' : '1' ?>">
      <button class="px-4 py-2 rounded text-white <?= $isActive ? 'bg-orange-600 hover:bg-orange-700' : 'bg-ctgreen hover:bg-ctdark' ?>">
        <?= $isActive ? 'Desativar usuário' : 'Ativar usuário' ?>
      </button>
    </form>
  </div>
</div>
