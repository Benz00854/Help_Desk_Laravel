<?php

namespace App\Repositories;

use App\Core\Database;

class TicketRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ปรับให้ JOIN เพื่อดึงชื่อผู้แจ้ง, ชื่อช่าง และหมวดหมู่ มาแสดงที่หน้า show.php ได้ครบ
    public function find(int $id): ?array
    {
        $sql = "SELECT t.*, 
                       u.name as creator_name, 
                       u.email as creator_email,
                       tech.name as assigned_tech, 
                       c.name as category
                FROM tickets t 
                JOIN users u ON t.user_id = u.id 
                LEFT JOIN users tech ON t.technician_id = tech.id 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO tickets (user_id, category_id, title, description, priority, status) 
                VALUES (?, ?, ?, ?, ?, 'Open')";
        $this->db->query($sql, [
            $data['user_id'],
            $data['category_id'],
            $data['title'],
            $data['description'],
            $data['priority'] ?? 'Medium'
        ]);
        return (int) $this->db->lastInsertId();
    }

    // --- สิทธิ์ USER ---
    public function getTicketsByUser(int $userId): array
    {
        $sql = "SELECT t.*, c.name as category_name 
                FROM tickets t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = ? 
                ORDER BY t.created_at DESC";
        return $this->db->query($sql, [$userId])->fetchAll();
    }

    public function addRating(int $ticketId, int $score, ?string $feedback): bool
    {
        $sql = "INSERT INTO ratings (ticket_id, score, feedback) VALUES (?, ?, ?)";
        return $this->db->query($sql, [$ticketId, $score, $feedback])->rowCount() > 0;
    }

    // --- สิทธิ์ TECHNICIAN ---
    public function getTicketsByTechnician(int $techId): array
    {
        $sql = "SELECT t.*, u.name as requester_name, c.name as category_name 
                FROM tickets t 
                JOIN users u ON t.user_id = u.id 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.technician_id = ? 
                ORDER BY t.updated_at DESC";
        return $this->db->query($sql, [$techId])->fetchAll();
    }

    // --- สิทธิ์ ADMIN ---
    public function getAllTickets(): array
    {
        $sql = "SELECT t.*, u.name as requester_name, tech.name as tech_name, c.name as category_name 
                FROM tickets t 
                JOIN users u ON t.user_id = u.id 
                LEFT JOIN users tech ON t.technician_id = tech.id 
                JOIN categories c ON t.category_id = c.id 
                ORDER BY t.created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function assignTechnician(int $ticketId, int $techId, int $adminId): bool
    {
        try {
            $this->db->beginTransaction();
            $this->db->query("UPDATE tickets SET technician_id = ?, status = 'Assigned', updated_at = NOW() WHERE id = ?", [$techId, $ticketId]);
            $this->db->query("INSERT INTO status_logs (ticket_id, changed_by, from_status, to_status, note) VALUES (?, ?, 'Open', 'Assigned', ?)", [$ticketId, $adminId, "Assigned to Tech ID: {$techId}"]);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // --- Transaction เปลี่ยนสถานะ + Comment + Log (ตาม Sequence Diagram 6.2) ---
    public function updateStatusTransaction(int $ticketId, string $fromStatus, string $toStatus, int $userId, ?string $comment = null, ?string $imagePath = null): bool
    {
        try {
            $this->db->beginTransaction();
            $resolvedClause = ($toStatus === 'Resolved') ? ", resolved_at = NOW()" : "";
            $closedClause = ($toStatus === 'Closed') ? ", closed_at = NOW()" : "";

            $this->db->query("UPDATE tickets SET status = ?, updated_at = NOW() {$resolvedClause} {$closedClause} WHERE id = ?", [$toStatus, $ticketId]);

            if (!empty($comment) || !empty($imagePath)) {
                $this->db->query("INSERT INTO comments (ticket_id, user_id, body, image_path) VALUES (?, ?, ?, ?)", [$ticketId, $userId, $comment ?? '', $imagePath]);
            }

            $this->db->query("INSERT INTO status_logs (ticket_id, changed_by, from_status, to_status, note) VALUES (?, ?, ?, ?, ?)", [$ticketId, $userId, $fromStatus, $toStatus, $comment]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // =========================================================================
    // ฟังก์ชันเพิ่มเติมสำหรับ Admin Dashboard (ตาม Sequence Diagram 6.3)
    // =========================================================================

    // 1. นับจำนวนตั๋วแยกตามสถานะ (SELECT status COUNT GROUP BY)
    public function countByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM tickets GROUP BY status";
        $rows = $this->db->query($sql)->fetchAll();
        $result = ['Open' => 0, 'Assigned' => 0, 'InProgress' => 0, 'Resolved' => 0, 'Closed' => 0];
        foreach ($rows as $row) {
            $result[$row['status']] = (int) $row['count'];
        }
        return $result;
    }

    // 2. คำนวณเวลาเฉลี่ยในการปิดงาน (SELECT AVG TIMESTAMPDIFF)
    public function avgResolutionTime(): string
    {
        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as avg_minutes 
                FROM tickets 
                WHERE status IN ('Resolved', 'Closed') AND resolved_at IS NOT NULL";
        $stmt = $this->db->query($sql);
        $minutes = (float) ($stmt->fetch()['avg_minutes'] ?? 0);
        if ($minutes <= 0)
            return "N/A";
        $hours = round($minutes / 60, 1);
        return "{$hours} ชม.";
    }

    // 3. จัดอันดับช่างที่ปิดงานได้มากที่สุด (SELECT technician COUNT)
    public function topTechnicians(int $limit = 5): array
    {
        $sql = "SELECT u.name, COUNT(t.id) as completed, COALESCE(AVG(r.score), 5.0) as rating
                FROM users u
                JOIN tickets t ON u.id = t.technician_id
                LEFT JOIN ratings r ON t.id = r.ticket_id
                WHERE t.status IN ('Resolved', 'Closed')
                GROUP BY u.id, u.name
                ORDER BY completed DESC
                LIMIT ?";
        return $this->db->query($sql, [$limit])->fetchAll();
    }

    // 4. คำนวณคะแนนเฉลี่ยความพึงพอใจ (SELECT MONTH AVG score)
    public function monthlyRatings(): float
    {
        $sql = "SELECT AVG(score) as avg_score FROM ratings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        $stmt = $this->db->query($sql);
        return round((float) ($stmt->fetch()['avg_score'] ?? 5.0), 1);
    }
}