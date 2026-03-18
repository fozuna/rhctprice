<?php
class Auth
{
    public static function login(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        if (!$user) { return false; }
        if (!User::verifyPassword($user, $password)) { return false; }
        $role = strtolower(trim((string)($user->role ?? '')));
        $isSupervisor = (int)($user->is_supervisor ?? 0) === 1;
        if ($role === '' && strtolower($user->email) === 'admin@ctprice.local') {
            $role = 'admin';
        }
        if ($role === 'admin') {
            $isSupervisor = true;
        }
        if ($role === '') {
            $role = 'viewer';
        }
        $_SESSION['user'] = true;
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_name'] = $user->nome;
        $_SESSION['user_is_supervisor'] = $isSupervisor;
        session_regenerate_id(true);
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function canAccessAdmin(): bool
    {
        if (!self::check()) {
            return false;
        }
        if (!empty($_SESSION['user_is_supervisor'])) {
            return true;
        }
        $role = strtolower(trim((string)(self::role() ?? '')));
        return in_array($role, ['admin', 'rh', 'viewer'], true);
    }

    public static function requireRole(array $roles): void
    {
        if (!empty($_SESSION['user_is_supervisor'])) {
            return;
        }
        if (!self::check()) {
            redirect('/login');
        }
        $grantedRoles = array_map(static fn($r) => strtolower(trim((string)$r)), $roles);
        $currentRole = strtolower(trim((string)(self::role() ?? '')));
        if (!in_array($currentRole, $grantedRoles, true)) {
            http_response_code(403);
            echo 'Acesso negado';
            exit;
        }
    }
}
