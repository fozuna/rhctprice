<?php
?>
<div class="bg-white shadow rounded p-6">
  <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-semibold text-ctpblue"><?= Security::e($c['nome']) ?></h2>
      <span class="px-3 py-1 rounded text-white text-sm font-semibold" style="background-color: <?= $c['stage_cor'] ?? '#ccc' ?>">
          <?= Security::e($c['stage_nome'] ?? 'Novo') ?>
      </span>
  </div>
  
  <div class="grid md:grid-cols-2 gap-6">
      <div>
          <?php if (!empty($c['cpf'])): ?>
            <p class="text-gray-600"><strong>CPF:</strong> <?= substr($c['cpf'], 0, 3) . '.' . substr($c['cpf'], 3, 3) . '.' . substr($c['cpf'], 6, 3) . '-' . substr($c['cpf'], 9, 2) ?></p>
          <?php endif; ?>
          <p class="mt-2"><strong>Cargo pretendido:</strong> <?= Security::e($c['cargo_pretendido'] ?? $c['vaga_titulo'] ?? '') ?></p>
          <p class="mt-2"><strong>E-mail:</strong> <?= Security::e($c['email']) ?></p>
          <p class="mt-2"><strong>Telefone:</strong> <?= Security::e($c['telefone']) ?></p>
          <p class="mt-2"><strong>Vaga:</strong> <?= Security::e($c['vaga_titulo'] ?? '-') ?></p>
      </div>
      <div>
          <p class="font-semibold text-gray-700">Experiência/Resumo:</p>
          <div class="mt-1 p-3 bg-gray-50 border rounded text-sm text-gray-800 h-32 overflow-y-auto">
              <?= nl2br(Security::e($c['experiencia'])) ?>
          </div>
          <div class="mt-4">
               <a href="<?= $base ?>/admin/candidaturas/<?= (int)$c['id'] ?>/download" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                   <svg class="mr-2 -ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                   </svg>
                   Baixar Currículo PDF
               </a>
               
               <!-- Placeholder AI Analysis -->
               <button type="button" data-ai-analyze="1" class="ml-2 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                   <svg class="mr-2 -ml-1 h-5 w-5 text-purple-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                   </svg>
                   Analisar com IA
               </button>
          </div>
      </div>
  </div>

  <div class="mt-8 border-t pt-6">
      <h3 class="text-lg font-medium text-gray-900">Atualizar Status / Pipeline</h3>
      <form class="mt-4 space-y-4" action="<?= $base ?>/admin/candidaturas/<?= (int)$c['id'] ?>/atualizar" method="post">
        <input type="hidden" name="csrf" value="<?= Security::e($csrf ?? '') ?>">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Etapa do Processo</label>
              <select name="stage_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md border">
                <?php foreach ($stages as $st): ?>
                  <option value="<?= $st['id'] ?>" <?= ($c['stage_id'] ?? 1) == $st['id'] ? 'selected' : '' ?>>
                    <?= Security::e($st['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
                <!-- Manter status legado oculto ou sincronizado se necessário, mas aqui focamos no stage -->
            </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Observações / Nota do Recrutador</label>
          <textarea name="observacoes" rows="3" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md border" placeholder="Adicione uma nota sobre esta etapa..."></textarea>
        </div>
        <div class="flex items-center justify-end">
          <a href="<?= $base ?>/admin/candidaturas" class="text-gray-600 hover:text-gray-900 mr-4">Voltar</a>
          <button type="submit" class="bg-ctgreen text-white px-4 py-2 rounded hover:bg-ctdark shadow-sm">Salvar Alterações</button>
        </div>
      </form>
  </div>

  <!-- Histórico -->
  <?php if (!empty($historico)): ?>
  <div class="mt-8 bg-gray-50 rounded p-6">
    <h3 class="text-lg font-semibold text-ctpblue mb-4">Histórico de Movimentações</h3>
    <div class="flow-root">
      <ul role="list" class="-mb-8">
        <?php foreach ($historico as $idx => $h): ?>
        <li>
          <div class="relative pb-8">
            <?php if ($idx !== count($historico) - 1): ?>
              <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
            <?php endif; ?>
            <div class="relative flex space-x-3">
              <div>
                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                  <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                  </svg>
                </span>
              </div>
              <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                <div>
                  <p class="text-sm text-gray-500">
                      Alteração de status <span class="font-medium text-gray-900"><?= Security::e($h['status_anterior'] ?? '-') ?></span> para <span class="font-medium text-gray-900"><?= Security::e($h['status_novo']) ?></span>
                  </p>
                  <?php if (!empty($h['observacoes'])): ?>
                      <p class="mt-1 text-sm text-gray-700 bg-white p-2 rounded border border-gray-200"><?= nl2br(Security::e($h['observacoes'])) ?></p>
                  <?php endif; ?>
                </div>
                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                  <time datetime="<?= $h['created_at'] ?>"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></time>
                  <div class="text-xs">por <?= Security::e($h['usuario_nome'] ?? 'Sistema') ?></div>
                </div>
              </div>
            </div>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endif; ?>
</div>
