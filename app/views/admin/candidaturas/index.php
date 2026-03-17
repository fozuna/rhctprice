<?php
?>
<div class="bg-white shadow rounded p-6">
  <div class="flex justify-between items-center">
      <h2 class="text-xl font-semibold text-ctpblue">Candidaturas</h2>
      <a href="<?= $base ?>/admin/pipeline" class="text-sm bg-blue-100 text-blue-800 px-3 py-1 rounded hover:bg-blue-200">Ver Kanban</a>
  </div>
  
  <form class="mt-4 grid md:grid-cols-4 gap-3" method="get">
    <div>
      <label class="block text-sm font-medium text-gray-700">Vaga</label>
      <select name="vaga_id" class="mt-1 w-full border rounded px-3 py-2 text-sm">
        <option value="">Todas</option>
        <?php foreach ($vagas as $v): ?>
          <option value="<?= (int)$v['id'] ?>" <?= ($filters['vaga_id'] ?? '') == $v['id'] ? 'selected' : '' ?>><?= Security::e($v['titulo']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Etapa (Pipeline)</label>
      <select name="stage_id" class="mt-1 w-full border rounded px-3 py-2 text-sm">
        <option value="">Todas</option>
        <?php foreach ($stages as $st): ?>
          <option value="<?= $st['id'] ?>" <?= ($filters['stage_id'] ?? '') == $st['id'] ? 'selected' : '' ?>>
            <?= Security::e($st['nome']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">De</label>
      <input type="date" name="data_de" value="<?= Security::e($filters['data_de'] ?? '') ?>" class="mt-1 w-full border rounded px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Até</label>
      <div class="flex space-x-2">
          <input type="date" name="data_ate" value="<?= Security::e($filters['data_ate'] ?? '') ?>" class="mt-1 w-full border rounded px-3 py-2 text-sm flex-1" />
          <button class="bg-ctgreen text-white px-4 py-2 rounded hover:bg-ctdark text-sm">Filtrar</button>
      </div>
    </div>
  </form>

  <div class="mt-6 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr class="border-b">
          <th class="text-left p-3 font-medium text-gray-500">#</th>
          <th class="text-left p-3 font-medium text-gray-500">Vaga</th>
          <th class="text-left p-3 font-medium text-gray-500">Nome</th>
          <th class="text-left p-3 font-medium text-gray-500">E-mail</th>
          <th class="text-left p-3 font-medium text-gray-500">Telefone</th>
          <th class="text-left p-3 font-medium text-gray-500">Etapa</th>
          <th class="text-left p-3 font-medium text-gray-500">Data</th>
          <th class="text-left p-3 font-medium text-gray-500">Ações</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php foreach ($candidaturas as $c): ?>
          <tr class="hover:bg-gray-50">
            <td class="p-3 text-gray-500"><?= (int)$c['id'] ?></td>
            <td class="p-3 font-medium text-gray-900"><?= Security::e($c['vaga_titulo'] ?? '-') ?></td>
            <td class="p-3 text-gray-900"><?= Security::e($c['nome']) ?></td>
            <td class="p-3 text-gray-500"><?= Security::e($c['email']) ?></td>
            <td class="p-3 text-gray-500"><?= Security::e($c['telefone']) ?></td>
            <td class="p-3">
                <?php 
                $stageName = $c['stage_nome'] ?? 'Novo';
                $stageColor = $c['stage_cor'] ?? '#cccccc';
                ?>
                <span class="px-2 py-1 rounded text-xs font-semibold text-white" style="background-color: <?= $stageColor ?>;">
                    <?= Security::e($stageName) ?>
                </span>
            </td>
            <td class="p-3 text-gray-500"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
            <td class="p-3 space-x-2">
              <a href="<?= $base ?>/admin/candidaturas/<?= (int)$c['id'] ?>" class="text-blue-600 hover:text-blue-900 font-medium">Detalhes</a>
              <a href="<?= $base ?>/admin/candidaturas/<?= (int)$c['id'] ?>/download" class="text-gray-600 hover:text-gray-900">PDF</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($candidaturas)): ?>
            <tr>
                <td colspan="8" class="p-6 text-center text-gray-500">Nenhuma candidatura encontrada.</td>
            </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
