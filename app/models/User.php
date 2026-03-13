<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    public int $id;
    public string $nome;
    public string $email;
    public string $senha_hash;
    public string $role;
    public int $is_supervisor;
    public ?string $email_verified_at;
    public ?string $last_password_reset_at;
    public string $created_at;

    public static function findByEmail(string $email): ?self
    {
        $sql = 'SELECT * FROM usuarios WHERE email = ? LIMIT 1';
        $stmt = Database::conn()->prepare($sql);
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? self::map($data) : null;
    }

    public static function verifyPassword(self $user, string $password): bool
    {
        return password_verify($password, $user->senha_hash);
    }

    public static function create(string $nome, string $email, string $senha_hash, string $role = 'viewer'): int
    {
        $sql = 'INSERT INTO usuarios (nome, email, senha_hash, role) VALUES (?,?,?,?)';
        $stmt = Database::conn()->prepare($sql);
        $stmt->execute([$nome, $email, $senha_hash, $role]);
        return (int)Database::conn()->lastInsertId();
    }

    public static function createSupervisor(string $nome, string $email, string $senhaHash): int
    {
        $sql = 'INSERT INTO usuarios (nome, email, senha_hash, role, is_supervisor, email_verified_at) VALUES (?,?,?,?,?,NOW())';
        $stmt = Database::conn()->prepare($sql);
        $stmt->execute([$nome, $email, $senhaHash, 'admin', 1]);
        return (int)Database::conn()->lastInsertId();
    }

    public static function ensureSupervisor(string $nome, string $email, string $password): int
    {
        $existing = self::findByEmail($email);
        $hash = password_hash($password, PASSWORD_BCRYPT);
        if ($existing) {
            $sql = 'UPDATE usuarios SET role = ?, is_supervisor = 1, senha_hash = ?, email_verified_at = COALESCE(email_verified_at, NOW()) WHERE id = ?';
            $stmt = Database::conn()->prepare($sql);
            $stmt->execute(['admin', $hash, $existing->id]);
            return $existing->id;
        }
        return self::createSupervisor($nome, $email, $hash);
    }

    public static function findById(int $id): ?self
    {
        $sql = 'SELECT * FROM usuarios WHERE id = ? LIMIT 1';
        $stmt = Database::conn()->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? self::map($data) : null;
    }

    public static function updatePassword(int $id, string $passwordHash): bool
    {
        $sql = 'UPDATE usuarios SET senha_hash = ?, last_password_reset_at = NOW() WHERE id = ?';
        $stmt = Database::conn()->prepare($sql);
        return $stmt->execute([$passwordHash, $id]);
    }

    public static function isProtectedSupervisor(self $user): bool
    {
        return (int)($user->is_supervisor ?? 0) === 1;
    }

    public static function canManageUser(?self $actor, self $target): bool
    {
        if (!self::isProtectedSupervisor($target)) {
            return true;
        }
        if (!$actor) {
            return false;
        }
        return (int)($actor->is_supervisor ?? 0) === 1;
    }

    public static function attemptRoleUpdate(int $targetId, string $newRole, ?self $actor, ?string $ip): bool
    {
        $target = self::findById($targetId);
        if (!$target) {
            return false;
        }
        if (!self::canManageUser($actor, $target)) {
            AuditLog::log($actor?->id, $targetId, 'blocked_role_change', 'Tentativa de alterar permissão de supervisor', $ip);
            return false;
        }
        $sql = 'UPDATE usuarios SET role = ? WHERE id = ?';
        $stmt = Database::conn()->prepare($sql);
        return $stmt->execute([$newRole, $targetId]);
    }

    public static function attemptDelete(int $targetId, ?self $actor, ?string $ip): bool
    {
        $target = self::findById($targetId);
        if (!$target) {
            return false;
        }
        if (!self::canManageUser($actor, $target)) {
            AuditLog::log($actor?->id, $targetId, 'blocked_user_delete', 'Tentativa de excluir supervisor', $ip);
            return false;
        }
        $sql = 'DELETE FROM usuarios WHERE id = ?';
        $stmt = Database::conn()->prepare($sql);
        return $stmt->execute([$targetId]);
    }

    private static function map(array $data): self
    {
        $u = new self();
        $u->id = (int)$data['id'];
        $u->nome = $data['nome'];
        $u->email = $data['email'];
        $u->senha_hash = $data['senha_hash'];
        $u->role = $data['role'];
        $u->is_supervisor = (int)($data['is_supervisor'] ?? 0);
        $u->email_verified_at = $data['email_verified_at'] ?? null;
        $u->last_password_reset_at = $data['last_password_reset_at'] ?? null;
        $u->created_at = $data['created_at'];
        return $u;
    }
}
