<?php
class AdminUsuariosController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'q' => Security::sanitizeString($_GET['q'] ?? ''),
            'role' => Security::sanitizeString($_GET['role'] ?? ''),
            'status' => Security::sanitizeString($_GET['status'] ?? '')
        ];
        $result = User::paginateForAdmin($filters, $page, 10);
        $this->view->render('admin/usuarios/index', [
            'users' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'pages' => $result['pages'],
            'perPage' => $result['per_page'],
            'filters' => $filters,
            'csrf' => Security::csrfToken()
        ], 'layouts/admin');
    }

    public function create(): void
    {
        Auth::requireRole(['admin']);
        $csrf = Security::csrfToken();
        $success = isset($_GET['supervisor']) && $_GET['supervisor'] === 'ok'
            ? 'Usuário Supervisor criado/atualizado e protegido com sucesso.'
            : null;
        $this->view->render('admin/usuarios/create', ['csrf' => $csrf, 'success' => $success], 'layouts/admin');
    }

    public function store(): void
    {
        Auth::requireRole(['admin']);
        if (!Security::csrfCheck($_POST['csrf'] ?? '')) {
            http_response_code(400);
            echo 'Falha na verificação de segurança (CSRF).';
            return;
        }
        $nome = Security::sanitizeString($_POST['nome'] ?? '');
        $email = Security::sanitizeString($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $role = Security::sanitizeString($_POST['role'] ?? 'viewer');
        $supervisorEmail = Config::app()['security']['supervisor_email'] ?? '';
        if (!in_array($role, ['admin','rh','viewer'], true)) { $role = 'viewer'; }
        if (!$nome || !$email || !$senha) {
            $this->view->render('admin/usuarios/create', [
                'error' => 'Preencha nome, e-mail e senha.',
                'csrf' => Security::csrfToken()
            ], 'layouts/admin');
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view->render('admin/usuarios/create', [
                'error' => 'E-mail inválido.',
                'csrf' => Security::csrfToken()
            ], 'layouts/admin');
            return;
        }
        if ($supervisorEmail !== '' && strcasecmp($email, $supervisorEmail) === 0) {
            $this->view->render('admin/usuarios/create', [
                'error' => 'Este e-mail é reservado ao usuário Supervisor protegido.',
                'csrf' => Security::csrfToken()
            ], 'layouts/admin');
            return;
        }
        $policy = PasswordPolicy::validate($senha);
        if (!$policy['valid']) {
            $this->view->render('admin/usuarios/create', [
                'error' => implode(' ', $policy['errors']),
                'csrf' => Security::csrfToken()
            ], 'layouts/admin');
            return;
        }
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
        try {
            User::create($nome, $email, $senhaHash, $role);
        } catch (\Throwable $e) {
            $this->view->render('admin/usuarios/create', [
                'error' => 'Falha ao criar usuário: ' . Security::e($e->getMessage()),
                'csrf' => Security::csrfToken()
            ], 'layouts/admin');
            return;
        }
        redirect('/admin/usuarios');
    }

    public function updateRole(string $id): void
    {
        Auth::requireRole(['admin']);
        SchemaManager::ensure();
        if (!Security::csrfCheck($_POST['csrf'] ?? '')) {
            http_response_code(400);
            echo 'Falha na verificação de segurança (CSRF).';
            return;
        }
        $role = Security::sanitizeString($_POST['role'] ?? 'viewer');
        if (!in_array($role, ['admin', 'rh', 'viewer'], true)) {
            $role = 'viewer';
        }
        $actor = User::findById((int)($_SESSION['user_id'] ?? 0));
        $ok = User::attemptRoleUpdate((int)$id, $role, $actor, Security::clientIp());
        if (!$ok) {
            http_response_code(403);
            echo 'Operação não permitida.';
            return;
        }
        redirect('/admin/usuarios');
    }

    public function delete(string $id): void
    {
        Auth::requireRole(['admin']);
        SchemaManager::ensure();
        if (!Security::csrfCheck($_POST['csrf'] ?? '')) {
            http_response_code(400);
            echo 'Falha na verificação de segurança (CSRF).';
            return;
        }
        $actor = User::findById((int)($_SESSION['user_id'] ?? 0));
        $ok = User::attemptDelete((int)$id, $actor, Security::clientIp());
        if (!$ok) {
            http_response_code(403);
            echo 'Operação não permitida.';
            return;
        }
        redirect('/admin/usuarios');
    }

    public function show(string $id): void
    {
        Auth::requireRole(['admin']);
        $user = User::findById((int)$id);
        if (!$user) {
            http_response_code(404);
            echo 'Usuário não encontrado';
            return;
        }
        $this->view->render('admin/usuarios/show', [
            'user' => $user,
            'csrf' => Security::csrfToken()
        ], 'layouts/admin');
    }

    public function updateStatus(string $id): void
    {
        Auth::requireRole(['admin']);
        if (!Security::csrfCheck($_POST['csrf'] ?? '')) {
            http_response_code(400);
            echo 'Falha na verificação de segurança (CSRF).';
            return;
        }
        $active = (string)($_POST['active'] ?? '0') === '1';
        $target = User::findById((int)$id);
        if (!$target) {
            http_response_code(404);
            echo 'Usuário não encontrado';
            return;
        }
        $actor = User::findById((int)($_SESSION['user_id'] ?? 0));
        if (!User::canManageUser($actor, $target)) {
            http_response_code(403);
            echo 'Operação não permitida.';
            return;
        }
        User::setActiveStatus((int)$id, $active);
        redirect('/admin/usuarios');
    }
}
