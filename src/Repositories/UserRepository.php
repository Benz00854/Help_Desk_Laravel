<?php

namespace App\Repositories;

use App\Core\Database;

class UserRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->query("SELECT id, name, email, role, created_at FROM users WHERE id = ?", [$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAllTechnicians(): array
    {
        $stmt = $this->db->query("SELECT id, name, email FROM users WHERE role = 'Technician'");
        return $stmt->fetchAll();
    }

    public function getEmailsByRole(string $role): array
    {
        $stmt = $this->db->query("SELECT email FROM users WHERE role = ?", [$role]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
    // สำหรับระบบ Login ค้นหาด้วย Email (ดึง password hash มาตรวจสอบ)
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->query("SELECT * FROM users WHERE email = ?", [$email]);
        return $stmt->fetch() ?: null;
    }

    // สำหรับหน้า Admin จัดการผู้ใช้งาน (Manage Users)
    public function getAllUsers(): array {
        $stmt = $this->db->query("SELECT id, name, email, role, created_at FROM users ORDER BY id ASC");
        return $stmt->fetchAll();
    }
}