<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
    <p class="text-gray-600">Visão geral do recrutamento</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Card Vagas Ativas -->
    <div class="bg-white rounded-lg shadow p-6 flex items-center border-l-4 border-blue-500">
        <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Vagas Ativas</p>
            <p class="text-2xl font-bold text-gray-800"><?= $vagasAtivas ?></p>
        </div>
    </div>

    <!-- Card Total Candidaturas -->
    <div class="bg-white rounded-lg shadow p-6 flex items-center border-l-4 border-green-500">
        <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Total de Candidaturas</p>
            <p class="text-2xl font-bold text-gray-800"><?= $totalCandidaturas ?></p>
        </div>
    </div>
</div>

<!-- Funnel / Stages Overview -->
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Pipeline de Seleção</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <?php foreach ($stages as $st): ?>
            <div class="bg-gray-50 rounded p-4 text-center border-t-4" style="border-color: <?= $st['cor'] ?>">
                <p class="text-sm font-medium text-gray-500"><?= htmlspecialchars($st['nome']) ?></p>
                <p class="text-xl font-bold text-gray-800 mt-1"><?= $stats[$st['nome']] ?? 0 ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="mt-6 text-right">
        <a href="<?= $base ?>/admin/pipeline" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Ver Quadro Kanban Completo &rarr;</a>
    </div>
</div>
