<?php
namespace App\Controllers;

require_once __DIR__ . '/../Services/Notifications/NotificationChannelInterface.php';
require_once __DIR__ . '/../Services/Notifications/EmailNotificationService.php';

use App\Services\Notifications\EmailNotificationService;

class TicketController
{
    private EmailNotificationService $emailService;

    public function __construct()
    {
        $this->emailService = new EmailNotificationService();
    }

    public function index()
    {
        $allTickets = $_SESSION['tickets'] ?? [];
        $currentRole = $_SESSION['user']['role'] ?? 'user';

        // ระบบกรองตั๋ว: all, open, assigned, active, completed
        $filter = $_GET['filter'] ?? 'all';
        $tickets = [];

        foreach ($allTickets as $id => $t) {
            $status = strtoupper(str_replace(['_', ' '], '', $t['status'] ?? 'OPEN'));
            if ($filter === 'open' && $status === 'OPEN') {
                $tickets[$id] = $t;
            } elseif ($filter === 'assigned' && $status === 'ASSIGNED') {
                $tickets[$id] = $t;
            } elseif ($filter === 'active' && in_array($status, ['OPEN', 'ASSIGNED', 'INPROGRESS'])) {
                $tickets[$id] = $t;
            } elseif ($filter === 'completed' && in_array($status, ['RESOLVED', 'CLOSED'])) {
                $tickets[$id] = $t;
            } elseif ($filter === 'all') {
                $tickets[$id] = $t;
            }
        }

        require __DIR__ . '/../../views/tickets/index.php';
    }

    public function create()
    {
        require __DIR__ . '/../../views/tickets/create.php';
    }

    public function store(array $post, array $files)
    {
        $title = trim($post['title'] ?? '');
        $category = $post['category'] ?? 'General';
        $priority = $post['priority'] ?? 'Medium';

        if (empty($title)) {
            $_SESSION['error'] = 'กรุณาระบุหัวข้อปัญหา';
            header('Location: /tickets/create');
            exit;
        }

        // บันทึกภาพถ่ายตอนแจ้งซ่อมจากผู้ใช้ (Create Ticket)
        $imageName = null;
        if (!empty($files['repair_image']['name'])) {
            $uploadDir = __DIR__ . '/../../storage/uploads/ticket-images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($files['repair_image']['name'], PATHINFO_EXTENSION);
            $imageName = 'ticket_' . time() . '.' . $ext;
            move_uploaded_file($files['repair_image']['tmp_name'], $uploadDir . $imageName);
        }

        $newId = count($_SESSION['tickets']) + 101;
        $creatorName = $_SESSION['user']['name'] ?? 'ผู้ใช้งาน';
        $creatorEmail = $_SESSION['user']['email'] ?? 'user@helpdesk.com'; // บันทึกอีเมลจริงของผู้สมัครไว้ในตั๋ว

        $_SESSION['tickets'][$newId] = [
            'id' => $newId,
            'title' => $title,
            'category' => $category,
            'priority' => $priority,
            'status' => 'OPEN',
            'creator_name' => $creatorName,
            'creator_email' => $creatorEmail,
            'created_at' => date('Y-m-d H:i'),
            'assigned_tech' => 'ยังไม่ได้มอบหมาย',
            'tech_id' => null,
            'image' => $imageName,
            'repair_image' => null,
            'comments' => [],
            'rating' => null,
            'feedback' => null
        ];

        // -------------------------------------------------------------
        // ส่งอีเมลแจ้งเตือนทีมช่าง/Admin ว่ามีงานแจ้งซ่อมใหม่เข้ามา
        // -------------------------------------------------------------
        $this->emailService->send(
            'tech-team@helpdesk.com',
            "แจ้งซ่อมใหม่: #{$newId} - {$title}",
            "มีงานแจ้งซ่อมใหม่หมายเลข <strong>#{$newId}</strong><br>
             <strong>หัวข้อ:</strong> {$title}<br>
             <strong>หมวดหมู่:</strong> {$category} | <strong>ความเร่งด่วน:</strong> {$priority}<br>
             <strong>ผู้แจ้ง:</strong> {$creatorName} ({$creatorEmail})<br><br>
             กรุณาเข้าสู่ระบบเพื่อตรวจสอบและรับงาน"
        );

        $_SESSION['success'] = "สร้างตั๋วแจ้งซ่อมหมายเลข #$newId สำเร็จ และส่งอีเมลแจ้งเตือนช่างเรียบร้อยแล้ว!";
        header("Location: /tickets/$newId");
        exit;
    }

    public function show(int $id)
    {
        if (!isset($_SESSION['tickets'][$id])) {
            http_response_code(404);
            die("ไม่พบตั๋วหมายเลข #$id");
        }
        $ticket = $_SESSION['tickets'][$id];
        require __DIR__ . '/../../views/tickets/show.php';
    }

    public function updateStatus(int $id)
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['tickets'][$id])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Ticket not found']);
            exit;
        }

        $newStatus = $_POST['status'] ?? null;
        $commentText = trim($_POST['comment'] ?? '');

        if ($newStatus) {
            $_SESSION['tickets'][$id]['status'] = $newStatus;
        }

        // จัดการอัปโหลดภาพผลงานการซ่อมของช่าง (Attach Repair Photo)
        $repairImageName = null;
        if (!empty($_FILES['repair_photo']['name'])) {
            $uploadDir = __DIR__ . '/../../storage/uploads/ticket-images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES['repair_photo']['name'], PATHINFO_EXTENSION);
            $repairImageName = 'repair_' . time() . '_' . $id . '.' . $ext;
            if (move_uploaded_file($_FILES['repair_photo']['tmp_name'], $uploadDir . $repairImageName)) {
                $_SESSION['tickets'][$id]['repair_image'] = $repairImageName;
            }
        }

        $commentObj = null;
        if (!empty($commentText)) {
            $commentObj = [
                'user' => $_SESSION['user']['name'] ?? 'ช่าง',
                'role' => ucfirst($_SESSION['user']['role'] ?? 'Technician'),
                'text' => $commentText,
                'time' => date('H:i')
            ];
            $_SESSION['tickets'][$id]['comments'][] = $commentObj;
        }

        // ---------------------------------------------------------------------
        // จุดสำคัญ: ส่งอีเมลแจ้งเตือนตรงไปยัง "อีเมลจริงของผู้แจ้ง"
        // ---------------------------------------------------------------------
        $emailNotified = false;
        if ($newStatus === 'RESOLVED') {
            $ticket = $_SESSION['tickets'][$id];
            $recipientEmail = $ticket['creator_email'] ?? 'user@helpdesk.com'; // ดึงอีเมลจริงของผู้สร้างตั๋วใบนี้
            $creatorName = $ticket['creator_name'] ?? 'ผู้ใช้งาน';
            $ticketTitle = $ticket['title'] ?? '';
            $techName = $_SESSION['user']['name'] ?? 'ช่างผู้รับผิดชอบ';

            $this->emailService->send(
                $recipientEmail, // ส่งตรงเข้า Inbox ของผู้แจ้งจริง
                "งานซ่อมเสร็จสิ้นแล้ว: ตั๋ว #{$id} ({$ticketTitle})",
                "เรียน คุณ <strong>{$creatorName}</strong>,<br><br>
                 {$techName} ได้ดำเนินการซ่อมแซมอุปกรณ์ตามตั๋วหมายเลข <strong>#{$id}</strong> เรียบร้อยแล้ว<br>
                 <strong>หัวข้อปัญหา:</strong> {$ticketTitle}<br>
                 <strong>บันทึกจากช่าง:</strong> " . (!empty($commentText) ? htmlspecialchars($commentText) : 'ซ่อมเสร็จสิ้นเรียบร้อย') . "<br><br>
                 กรุณาเข้าสู่ระบบเพื่อตรวจสอบอุปกรณ์ และกดประเมินความพึงพอใจเพื่อปิดงาน ขอบคุณครับ"
            );
            $emailNotified = true;
        }

        echo json_encode([
            'success' => true,
            'message' => $emailNotified ? 'อัปเดตสถานะและส่งอีเมลแจ้งผู้ใช้เรียบร้อยแล้ว' : 'อัปเดตสถานะสำเร็จ',
            'status' => $_SESSION['tickets'][$id]['status'],
            'repair_image' => $_SESSION['tickets'][$id]['repair_image'] ?? $repairImageName,
            'comment' => $commentObj,
            'email_sent' => $emailNotified
        ]);
        exit;
    }

    public function rate(int $id, array $post)
    {
        $rating = (int) ($post['rating'] ?? 5);
        $feedback = trim($post['feedback'] ?? '');

        if (isset($_SESSION['tickets'][$id])) {
            $_SESSION['tickets'][$id]['status'] = 'CLOSED';
            $_SESSION['tickets'][$id]['rating'] = $rating;
            $_SESSION['tickets'][$id]['feedback'] = $feedback;
            $_SESSION['success'] = 'ขอบคุณสำหรับการประเมินความพึงพอใจ!';
        }
        header("Location: /tickets/$id");
        exit;
    }

    public function destroy(int $id)
    {
        if (!isset($_SESSION['tickets'][$id])) {
            $_SESSION['error'] = 'ไม่พบตั๋วที่ต้องการลบ';
            header('Location: /tickets');
            exit;
        }

        $currentUser = $_SESSION['user'] ?? [];
        $ticket = $_SESSION['tickets'][$id];
        $isAdmin = ($currentUser['role'] ?? '') === 'admin';
        $isOwner = ($currentUser['name'] ?? '') === ($ticket['creator_name'] ?? '');

        // เฉพาะแอดมิน หรือเจ้าของตั๋วตอนที่สถานะยังเป็น OPEN เท่านั้นที่มีสิทธิ์ลบ
        if (!$isAdmin && !($isOwner && strtoupper($ticket['status']) === 'OPEN')) {
            $_SESSION['error'] = 'คุณไม่มีสิทธิ์ลบตั๋วนี้';
            header("Location: /tickets/$id");
            exit;
        }

        unset($_SESSION['tickets'][$id]);
        $_SESSION['success'] = "ลบตั๋วแจ้งซ่อม #$id สำเร็จแล้ว";
        header('Location: /tickets');
        exit;
    }

    public function assign(int $id, array $post)
    {
        if (!isset($_SESSION['tickets'][$id])) {
            $_SESSION['error'] = 'ไม่พบตั๋วที่ต้องการมอบหมาย';
            header('Location: /tickets');
            exit;
        }

        $techName = trim($post['tech_name'] ?? 'ช่างวิชัย (Tech)');
        $techId = (int) ($post['tech_id'] ?? 2);
        $techEmail = trim($post['tech_email'] ?? 'tech@helpdesk.com');

        $_SESSION['tickets'][$id]['assigned_tech'] = $techName;
        $_SESSION['tickets'][$id]['tech_id'] = $techId;
        $_SESSION['tickets'][$id]['tech_email'] = $techEmail;
        $_SESSION['tickets'][$id]['status'] = 'ASSIGNED';

        // ส่งอีเมลแจ้งเตือนช่างที่ได้รับมอบหมายงาน
        $ticket = $_SESSION['tickets'][$id];
        $this->emailService->send(
            $techEmail,
            "มอบหมายงานใหม่: ตั๋ว #{$id} ({$ticket['title']})",
            "คุณได้รับมอบหมายงานซ่อมตั๋วหมายเลข <strong>#{$id}</strong><br>
             <strong>หัวข้อ:</strong> {$ticket['title']}<br>
             <strong>ผู้แจ้ง:</strong> {$ticket['creator_name']}<br><br>
             กรุณาเข้าสู่ระบบเพื่อดำเนินการซ่อมแซม"
        );

        $_SESSION['success'] = "มอบหมายงานตั๋ว #$id ให้กับ {$techName} เรียบร้อยแล้ว";
        header("Location: /tickets/$id");
        exit;
    }
}