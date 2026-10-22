<?php
namespace App\Services;

class DashboardService
{
    public function getStatistics(): array
    {
        $tickets = $_SESSION['tickets'] ?? [];
        $total = count($tickets);

        $counts = [
            'OPEN' => 0,
            'ASSIGNED' => 0,
            'IN_PROGRESS' => 0,
            'RESOLVED' => 0,
            'CLOSED' => 0
        ];

        $categories = [
            'Hardware' => 0,
            'Software' => 0,
            'Network' => 0,
            'Printer' => 0,
            'อื่นๆ' => 0
        ];

        $urgentCount = 0;
        $ratingsSum = 0;
        $ratingsCount = 0;
        $techCompleted = [];

        foreach ($tickets as $t) {
            $rawStatus = strtoupper(str_replace(['_', ' '], '', $t['status'] ?? 'OPEN'));

            // นับสถานะ
            if ($rawStatus === 'OPEN')
                $counts['OPEN']++;
            elseif ($rawStatus === 'ASSIGNED')
                $counts['ASSIGNED']++;
            elseif ($rawStatus === 'INPROGRESS')
                $counts['IN_PROGRESS']++;
            elseif ($rawStatus === 'RESOLVED')
                $counts['RESOLVED']++;
            elseif ($rawStatus === 'CLOSED')
                $counts['CLOSED']++;

            // นับตั๋วด่วน (High Priority) ที่ยังไม่ปิดงาน
            if (strtolower($t['priority'] ?? '') === 'high' && $rawStatus !== 'CLOSED') {
                $urgentCount++;
            }

            // นับหมวดหมู่
            $cat = $t['category'] ?? 'อื่นๆ';
            if (isset($categories[$cat])) {
                $categories[$cat]++;
            } else {
                $categories['อื่นๆ']++;
            }

            // คำนวณคะแนนดาว
            if (!empty($t['rating'])) {
                $ratingsSum += (int) $t['rating'];
                $ratingsCount++;
            }

            // นับผลงานช่าง
            if (in_array($rawStatus, ['RESOLVED', 'CLOSED']) && !empty($t['assigned_tech']) && $t['assigned_tech'] !== 'ยังไม่ได้มอบหมาย') {
                $techName = $t['assigned_tech'];
                $techCompleted[$techName] = ($techCompleted[$techName] ?? 0) + 1;
            }
        }

        // รวมงานรอดำเนินการ และงานเสร็จสิ้น
        $pendingTotal = $counts['OPEN'] + $counts['ASSIGNED'];
        $completedTotal = $counts['RESOLVED'] + $counts['CLOSED'];
        $avgScore = $ratingsCount > 0 ? round($ratingsSum / $ratingsCount, 1) : 5.0;

        // จัดอันดับช่าง
        $topTechs = [];
        if (!empty($techCompleted)) {
            arsort($techCompleted);
            foreach ($techCompleted as $name => $c) {
                $topTechs[] = ['name' => $name, 'completed' => $c, 'rating' => 4.9];
            }
        } else {
            $topTechs = [
                ['name' => 'ช่างวิชัย (Tech)', 'completed' => $completedTotal, 'rating' => 5.0]
            ];
        }

        // อัตราการเคลียร์ตั๋ว (Intake vs Clearance Rate)
        $clearanceRate = $total > 0 ? round(($completedTotal / $total) * 100) : 0;

        return [
            'total' => $total,
            'counts' => $counts,
            'pending_total' => $pendingTotal,
            'completed_total' => $completedTotal,
            'urgent_count' => $urgentCount,
            'clearance_rate' => $clearanceRate,
            'satisfaction_score' => $avgScore,
            'categories' => $categories,
            'top_technicians' => $topTechs
        ];
    }
}