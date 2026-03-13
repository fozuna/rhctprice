<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Pipeline de Seleção</h1>
    
    <form method="GET" action="<?= $base ?>/admin/pipeline" class="flex items-center space-x-2">
        <label for="vaga_id" class="text-sm font-medium text-gray-700">Filtrar por Vaga:</label>
        <select name="vaga_id" id="vaga_id" data-autosubmit="1" class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todas as Vagas</option>
            <?php foreach ($vagas as $v): ?>
                <option value="<?= $v['id'] ?>" <?= ($selectedVaga == $v['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($v['titulo']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="flex overflow-x-auto pb-4 space-x-4 h-[calc(100vh-12rem)]">
    <?php foreach ($kanban as $stageId => $col): ?>
        <div class="flex-shrink-0 w-80 bg-gray-100 rounded-lg shadow-sm flex flex-col h-full border-t-4" data-kanban-board-column="1" style="border-color: <?= $col['stage']['cor'] ?>">
            <div class="p-3 bg-white rounded-t border-b flex justify-between items-center sticky top-0 z-10">
                <h3 class="font-semibold text-gray-700"><?= htmlspecialchars($col['stage']['nome']) ?></h3>
                <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full" data-kanban-count="1"><?= count($col['items']) ?></span>
            </div>
            
            <div class="p-2 flex-1 overflow-y-auto space-y-3 kanban-column" data-kanban-column="1" data-stage-id="<?= $stageId ?>">
                 
                <?php foreach ($col['items'] as $c): ?>
                    <div class="bg-white p-3 rounded shadow-sm border border-gray-200 cursor-move hover:shadow-md transition-shadow group relative" data-kanban-card="1" draggable="true" id="cand-<?= $c['id'] ?>" data-cand-id="<?= $c['id'] ?>">
                        
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-medium text-gray-900 text-sm truncate w-full" title="<?= htmlspecialchars($c['nome']) ?>">
                                <?= htmlspecialchars($c['nome']) ?>
                            </h4>
                        </div>
                        
                        <p class="text-xs text-gray-500 mb-1 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <?= htmlspecialchars($c['vaga_titulo'] ?? 'Vaga não encontrada') ?>
                        </p>
                        
                        <div class="flex justify-between items-center mt-3">
                            <a href="<?= $base ?>/admin/candidaturas/<?= $c['id'] ?>" class="text-xs text-blue-600 hover:text-blue-800 hover:underline">Ver detalhes</a>
                            <span class="text-xs text-gray-400"><?= date('d/m', strtotime($c['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                
            </div>
        </div>
    <?php endforeach; ?>
</div>
