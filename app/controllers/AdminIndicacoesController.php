<?php
class AdminIndicacoesController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin', 'rh']);
        $filters = [
            'q' => Security::sanitizeString($_GET['q'] ?? ''),
            'pagamento' => Security::sanitizeString($_GET['pagamento'] ?? ''),
            'experiencia' => Security::sanitizeString($_GET['experiencia'] ?? '')
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Candidatura::paginateIndicacoes($filters, $page, 15);
        $items = [];
        foreach ($result['items'] as $row) {
            $row['payment_signal'] = Candidatura::paymentSignal($row);
            $items[] = $row;
        }
        $this->view->render('admin/indicacoes/index', [
            'items' => $items,
            'total' => $result['total'],
            'page' => $result['page'],
            'pages' => $result['pages'],
            'filters' => $filters,
            'csrf' => Security::csrfToken(),
            'flashError' => Security::sanitizeString($_GET['erro'] ?? ''),
            'flashSuccess' => Security::sanitizeString($_GET['ok'] ?? '')
        ], 'layouts/admin');
    }

    public function markPago(string $id): void
    {
        Auth::requireRole(['admin', 'rh']);
        if (!Security::csrfCheck($_POST['csrf'] ?? '')) {
            http_response_code(400);
            echo 'Falha na verificação de segurança (CSRF).';
            return;
        }
        $cand = Candidatura::find((int)$id);
        if (!$cand || (int)($cand['indicacao_colaborador'] ?? 0) !== 1) {
            http_response_code(404);
            echo 'Candidatura indicada não encontrada.';
            return;
        }
        $paymentDate = Security::sanitizeString($_POST['payment_date'] ?? '');
        $actorId = (int)($_SESSION['user_id'] ?? 0);
        $result = Candidatura::markIndicacaoPagamento((int)$id, $paymentDate, $actorId);
        if (!($result['ok'] ?? false)) {
            redirect('/admin/indicacoes?erro=' . urlencode((string)($result['error'] ?? 'Falha ao registrar pagamento.')));
        }
        $nome = (string)($cand['nome'] ?? 'Candidato');
        $vaga = (string)($cand['vaga_titulo'] ?? '-');
        $ator = (string)($_SESSION['user_name'] ?? 'Usuário');
        Mailer::notifyHR('Pagamento de indicação registrado', "Candidato: {$nome}\nVaga: {$vaga}\nData pagamento: {$paymentDate}\nRegistrado por: {$ator}");
        redirect('/admin/indicacoes?ok=' . urlencode('Pagamento registrado com sucesso.'));
    }

    public function updatePaymentDate(string $id): void
    {
        Auth::requireRole(['admin', 'rh']);
        if (!Security::csrfCheck($_POST['csrf'] ?? '')) {
            http_response_code(400);
            echo 'Falha na verificação de segurança (CSRF).';
            return;
        }
        $newDate = Security::sanitizeString($_POST['payment_date_edit'] ?? '');
        $reason = Security::sanitizeString($_POST['payment_edit_reason'] ?? '');
        $actorId = (int)($_SESSION['user_id'] ?? 0);
        $cand = Candidatura::find((int)$id);
        if (!$cand || (int)($cand['indicacao_colaborador'] ?? 0) !== 1) {
            http_response_code(404);
            echo 'Candidatura indicada não encontrada.';
            return;
        }
        $result = Candidatura::updateIndicacaoPaymentDate((int)$id, $newDate, $reason, $actorId);
        if (!($result['ok'] ?? false)) {
            redirect('/admin/indicacoes?erro=' . urlencode((string)($result['error'] ?? 'Falha ao editar data de pagamento.')));
        }
        $nome = (string)($cand['nome'] ?? 'Candidato');
        $vaga = (string)($cand['vaga_titulo'] ?? '-');
        $ator = (string)($_SESSION['user_name'] ?? 'Usuário');
        $old = (string)($result['old_date'] ?? '');
        $new = (string)($result['new_date'] ?? '');
        Mailer::notifyHR('Data de pagamento alterada', "Candidato: {$nome}\nVaga: {$vaga}\nData anterior: {$old}\nData nova: {$new}\nMotivo: {$reason}\nAlterado por: {$ator}");
        redirect('/admin/indicacoes?ok=' . urlencode('Data de pagamento alterada com sucesso.'));
    }

    public function statusApi(string $id): void
    {
        Auth::requireRole(['admin', 'rh', 'viewer']);
        $cand = Candidatura::find((int)$id);
        if (!$cand || (int)($cand['indicacao_colaborador'] ?? 0) !== 1) {
            http_response_code(404);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['ok' => false, 'error' => 'Indicação não encontrada'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $signal = Candidatura::paymentSignal($cand);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'ok' => true,
            'id' => (int)$cand['id'],
            'pagamento_realizado' => (int)($cand['indicacao_pagamento_realizado'] ?? 0),
            'data_pagamento' => $cand['indicacao_data_pagamento'] ?? null,
            'data_contratacao' => $cand['indicacao_data_contratacao'] ?? null,
            'data_fim_experiencia' => $cand['indicacao_data_fim_experiencia'] ?? null,
            'signal' => $signal
        ], JSON_UNESCAPED_UNICODE);
    }

    public function contasReceberApi(): void
    {
        Auth::requireRole(['admin', 'rh', 'viewer']);
        header('Content-Type: application/json; charset=UTF-8');
        $rows = Candidatura::financialIndicacoesDataset(['pagamento' => Security::sanitizeString($_GET['pagamento'] ?? '')]);
        echo json_encode(['ok' => true, 'module' => 'contas_receber', 'items' => $rows], JSON_UNESCAPED_UNICODE);
    }

    public function conciliacaoApi(): void
    {
        Auth::requireRole(['admin', 'rh', 'viewer']);
        header('Content-Type: application/json; charset=UTF-8');
        $rows = Candidatura::financialIndicacoesDataset(['pagamento' => Security::sanitizeString($_GET['pagamento'] ?? '')]);
        echo json_encode(['ok' => true, 'module' => 'conciliacao_bancaria', 'items' => $rows], JSON_UNESCAPED_UNICODE);
    }

    public function relatoriosFinanceirosApi(): void
    {
        Auth::requireRole(['admin', 'rh', 'viewer']);
        header('Content-Type: application/json; charset=UTF-8');
        $rows = Candidatura::financialIndicacoesDataset(['pagamento' => Security::sanitizeString($_GET['pagamento'] ?? '')]);
        $totais = [
            'qtd_total' => count($rows),
            'qtd_pagos' => count(array_filter($rows, static fn($r) => (int)($r['indicacao_pagamento_realizado'] ?? 0) === 1)),
            'qtd_pendentes' => count(array_filter($rows, static fn($r) => (int)($r['indicacao_pagamento_realizado'] ?? 0) === 0))
        ];
        echo json_encode(['ok' => true, 'module' => 'relatorios_financeiros', 'totais' => $totais, 'items' => $rows], JSON_UNESCAPED_UNICODE);
    }
}
