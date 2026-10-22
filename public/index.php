<?php
// public/index.php
session_start();

// ข้อมูลจำลองตั๋วเริ่มต้น (กรณีเข้าสู่ระบบครั้งแรก)
if (!isset($_SESSION['tickets'])) {
    $_SESSION['tickets'] = [
        101 => [
            'id' => 101,
            'title' => 'คอมพิวเตอร์เปิดไม่ติด มีไฟกระพริบสีส้ม',
            'category' => 'Hardware',
            'priority' => 'High',
            'status' => 'OPEN',
            'creator_name' => 'สมชาย รักงาน (User)',
            'creator_email' => 'user@helpdesk.com',
            'created_at' => date('Y-m-d H:i', strtotime('-2 hours')),
            'assigned_tech' => 'ยังไม่ได้มอบหมาย',
            'tech_email' => null,
            'tech_id' => null,
            'image' => null,
            'repair_image' => null,
            'comments' => [
                ['user' => 'สมชาย รักงาน', 'role' => 'User', 'text' => 'ลองกดสวิตช์หลังเครื่องแล้วยังไม่ติดครับ', 'time' => date('H:i', strtotime('-1 hour'))]
            ],
            'rating' => null,
            'feedback' => null
        ],
        102 => [
            'id' => 102,
            'title' => 'เครื่องพิมพ์กระดาษติด ชั้น 3 ฝ่ายการเงิน',
            'category' => 'Printer',
            'priority' => 'Medium',
            'status' => 'IN_PROGRESS',
            'creator_name' => 'วิภา ฝ่ายบัญชี',
            'creator_email' => 'user@helpdesk.com',
            'created_at' => date('Y-m-d H:i', strtotime('-1 day')),
            'assigned_tech' => 'ช่างวิชัย (Tech)',
            'tech_email' => 'tech@helpdesk.com',
            'tech_id' => 2,
            'image' => null,
            'repair_image' => null,
            'comments' => [
                ['user' => 'ช่างวิชัย', 'role' => 'Technician', 'text' => 'กำลังรื้อดึงเศษกระดาษที่ติดลูกกลิ้งครับ', 'time' => date('H:i', strtotime('-30 mins'))]
            ],
            'rating' => null,
            'feedback' => null
        ]
    ];
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// ให้ PHP built-in web server ให้บริการ static files ใน public/ ได้โดยตรง
if (php_sapi_name() === 'cli-server') {
    $publicFile = __DIR__ . $uri;
    if (is_file($publicFile)) {
        return false;
    }
}

// -------------------------------------------------------------
// Route ให้บริการรูปภาพจาก storage/uploads/ticket-images/
// -------------------------------------------------------------
if (preg_match('#^/storage/uploads/ticket-images/(.+)$#', $uri, $matches)) {
    $filePath = __DIR__ . '/../storage/uploads/ticket-images/' . basename($matches[1]);
    if (file_exists($filePath)) {
        $mime = mime_content_type($filePath);
        header("Content-Type: $mime");
        readfile($filePath);
        exit;
    }
}

// สลับบทบาทด่วนสำหรับทดสอบระบบ (User / Technician / Admin)
if (preg_match('#^/switch-role/([a-zA-Z]+)$#', $uri, $matches)) {
    $role = strtolower($matches[1]);
    $roleUsers = [
        'user' => [
            'id' => 1,
            'name' => 'สมชาย รักงาน (User)',
            'email' => 'user@helpdesk.com',
            'role' => 'user'
        ],
        'technician' => [
            'id' => 2,
            'name' => 'ช่างวิชัย (Tech)',
            'email' => 'tech@helpdesk.com',
            'role' => 'technician'
        ],
        'admin' => [
            'id' => 3,
            'name' => 'ผู้ดูแลระบบ (Admin)',
            'email' => 'admin@helpdesk.com',
            'role' => 'admin'
        ]
    ];
    if (isset($roleUsers[$role])) {
        $_SESSION['user'] = $roleUsers[$role];
        $_SESSION['success'] = "สลับบทบาทเป็น {$roleUsers[$role]['name']} สำเร็จ!";
    }
    header('Location: ' . ($role === 'admin' ? '/admin/dashboard' : '/tickets'));
    exit;
}

// โหลด Classes ทั้งหมด
require_once __DIR__ . '/../src/Core/Auth.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/TicketController.php';
require_once __DIR__ . '/../src/Controllers/DashboardController.php';
require_once __DIR__ . '/../src/Services/DashboardService.php';

use App\Core\Auth;
use App\Controllers\AuthController;
use App\Controllers\TicketController;
use App\Controllers\DashboardController;

$authController = new AuthController();
$ticketController = new TicketController();
$dashboardController = new DashboardController();

// =============================================================
// Public Authentication Routes (ไม่ต้องล็อกอินก็เข้าได้)
// =============================================================
if ($uri === '/login') {
    if ($method === 'POST') {
        $authController->login($_POST);
    } else {
        $authController->showLogin();
    }
    exit;
}

if ($uri === '/register') {
    if ($method === 'POST') {
        $authController->register($_POST);
    } else {
        $authController->showRegister();
    }
    exit;
}

if ($uri === '/logout') {
    $authController->logout();
    exit;
}

// =============================================================
// Route Guard: ถ้ายังไม่ล็อกอิน ให้ Redirect ไปหน้า /login
// =============================================================
if (!Auth::check()) {
    header('Location: /login');
    exit;
}

// =============================================================
// Protected Routes (ต้องผ่านการล็อกอินแล้วเท่านั้น)
// =============================================================

// 1. หน้ารายการตั๋วหลัก
if ($uri === '/' || $uri === '/tickets') {
    $ticketController->index();
}
// 2. หน้าสร้างตั๋วใหม่
elseif ($uri === '/tickets/create') {
    if ($method === 'POST') {
        $ticketController->store($_POST, $_FILES);
    } else {
        $ticketController->create();
    }
}
// 3. หน้ารายละเอียดตั๋วรายใบ
elseif (preg_match('#^/tickets/(\d+)$#', $uri, $matches)) {
    $ticketController->show((int) $matches[1]);
}
// 4. ลบตั๋วแจ้งซ่อม
elseif (preg_match('#^/tickets/(\d+)/delete$#', $uri, $matches) && $method === 'POST') {
    $ticketController->destroy((int) $matches[1]);
}
// 5. แอดมินมอบหมายงานให้ช่าง
elseif (preg_match('#^/tickets/(\d+)/assign$#', $uri, $matches) && $method === 'POST') {
    $ticketController->assign((int) $matches[1], $_POST);
}
// 6. ช่างอัปเดตสถานะ + แนบรูปภาพผ่าน AJAX
elseif (preg_match('#^/api/tickets/(\d+)/status$#', $uri, $matches)) {
    $ticketController->updateStatus((int) $matches[1]);
}
// 7. ผู้ใช้ตรวจรับงานและให้คะแนนดาว
elseif (preg_match('#^/api/tickets/(\d+)/rate$#', $uri, $matches)) {
    $ticketController->rate((int) $matches[1], $_POST);
}
// 8. หน้าข้อมูลส่วนตัว (User Profile)
elseif ($uri === '/profile') {
    if ($method === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!empty($name)) {
            $_SESSION['user']['name'] = $name;
        }
        if (!empty($email)) {
            $oldEmail = $_SESSION['user']['email'] ?? '';
            $_SESSION['user']['email'] = $email;
            if (!empty($oldEmail) && isset($_SESSION['registered_users'][$oldEmail])) {
                $userData = $_SESSION['registered_users'][$oldEmail];
                unset($_SESSION['registered_users'][$oldEmail]);
                $userData['name'] = $name;
                $userData['email'] = $email;
                if (!empty($password)) {
                    $userData['password'] = $password;
                }
                $_SESSION['registered_users'][$email] = $userData;
            }
        }
        if (!empty($password) && isset($_SESSION['registered_users'][$_SESSION['user']['email']])) {
            $_SESSION['registered_users'][$_SESSION['user']['email']]['password'] = $password;
        }

        $_SESSION['success'] = 'อัปเดตข้อมูลโปรไฟล์เรียบร้อยแล้ว!';
        header('Location: /profile');
        exit;
    } else {
        require __DIR__ . '/../views/profile.php';
        exit;
    }
}
// 9. หน้า Admin Dashboard (เฉพาะ Admin เท่านั้น)
elseif ($uri === '/admin/dashboard') {
    if (Auth::role() !== 'admin') {
        http_response_code(403);
        die("<div style='font-family:sans-serif; text-align:center; padding:50px;'><h1>403 Forbidden</h1><p>เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถเข้าหน้านี้ได้</p><a href='/tickets'>กลับหน้ารายการตั๋ว</a></div>");
    }
    $dashboardController->index();
}
// 10. หน้า 404
else {
    http_response_code(404);
    echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'><h1>404 Not Found</h1><p>ไม่พบหน้าที่คุณต้องการ</p><a href='/'>กลับหน้าแรก</a></div>";
}