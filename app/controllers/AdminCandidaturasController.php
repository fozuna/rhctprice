<?php
class AdminCandidaturasController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin', 'rh', 'viewer']);
        $filters = [
            'vaga_id' => isset($_GET['vaga_id']) ? (int)$_GET['vaga_id'] : null,
            'stage_id' => isset($_GET['stage_id']) ? (int)$_GET['stage_id'] : null,
            'data_de' => Security::sanitizeString($_GET['data_de'] ?? ''),
            'data_ate' => Security::sanitizeString($_GET['data_ate'] ?? ''),
        ];
        $candidaturas = Candidatura::all(array_filter($filters, fn($v) => $v !== null && $v !== ''));
        $vagas = Vaga::all();
        $stages = PipelineStage::all();
        $this->view->render('admin/candidaturas/index', [
            'candidaturas' => $candidaturas,
            'vagas' => $vagas,
            'stages' => $stages,
            'filters' => $filters,
        ], 'layouts/admin');
    }

    public function show(string $id): void
    {
        Auth::requireRole(['admin', 'rh', 'viewer']);
        $c = Candidatura::find((int)$id);
        if (!$c) { http_response_code(404); echo 'Candidatura não encontrada'; return; }
        $historico = Candidatura::getHistorico((int)$id);
        $stages = PipelineStage::all();
        $csrf = Security::csrfToken();
        $this->view->render('admin/candidaturas/show', [
            'c' => $c, 
            'historico' => $historico, 
            'stages' => $stages,
            'csrf' => $csrf
        ], 'layouts/admin');
    }
    
    public function download(string $id): void
    {
        Auth::requireRole(['admin', 'rh']);
        $c = Candidatura::find((int)$id);
        if (!$c) { http_response_code(404); echo 'Candidatura não encontrada'; return; }
        $name = basename((string)($c['pdf_path'] ?? ''));
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($ext !== 'pdf') { http_response_code(400); echo 'Arquivo inválido.'; return; }
        $dir = STORAGE_PATH . DIRECTORY_SEPARATOR . 'resumes';
        $file = $dir . DIRECTORY_SEPARATOR . $name;
        $real = realpath($file);
        $realDir = realpath($dir);
        if ($real === false || $realDir === false || strpos($real, $realDir) !== 0 || !is_file($real)) {
            http_response_code(404); echo 'Arquivo não encontrado'; return;
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="curriculo-' . (int)$c['id'] . '.pdf"');
        header('Content-Length: ' . filesize($real));
        readfile($real);
        exit;
    }

    public function update(string $id): void
    {
        Auth::requireRole(['admin', 'rh']);
        if (!Security::csrfCheck($_POST['csrf'] ?? '')) {
            http_response_code(400);
            echo 'Falha na verificação de segurança (CSRF).';
            return;
        }
        
        $stageId = (int)($_POST['stage_id'] ?? 0);
        $observacoes = Security::sanitizeString($_POST['observacoes'] ?? '');
        $usuarioId = $_SESSION['user_id'] ?? null;
        
        $pdo = Database::conn();
        try {
            $pdo->beginTransaction();
            
            if ($stageId > 0) {
                Candidatura::updateStage((int)$id, $stageId, $usuarioId);
            }
            
            // Se houver observações, adiciona nota separada ou atualiza campo legado
            if (!empty($observacoes)) {
                // Para manter compatibilidade com historico antigo
                 $c = Candidatura::find((int)$id);
                 Candidatura::updateStatusNotes((int)$id, $c['status'] ?? 'custom', $observacoes, $usuarioId);
            }
            
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo 'Falha ao atualizar candidatura.';
            return;
        }

        // Redireciona usando base_url da aplicação
        redirect('/admin/candidaturas/' . (int)$id);
    }
}
