<?php
namespace App\Controllers;

require_once __DIR__ . '/../Services/Notifications/NotificationChannelInterface.php';
require_once __DIR__ . '/../Services/Notifications/EmailNotificationService.php';

use App\Core\Auth;
use App\Services\Notifications\EmailNotificationService;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            header('Location: /tickets');
            exit;
        }
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        require __DIR__ . '/../../views/auth/login.php';
    }

    public function login(array $post): void
    {
        $email = trim($post['email'] ?? '');
        $password = trim($post['password'] ?? '');

        if (Auth::login($email, $password)) {
            if (Auth::role() === 'admin') {
                header('Location: /admin/dashboard');
            } else {
                header('Location: /tickets');
            }
            exit;
        }

        $_SESSION['login_error'] = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
        header('Location: /login');
        exit;
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            header('Location: /tickets');
            exit;
        }
        $error = $_SESSION['register_error'] ?? null;
        unset($_SESSION['register_error']);
        require __DIR__ . '/../../views/auth/register.php';
    }

    public function register(array $post): void
    {
        $name = trim($post['name'] ?? '');
        $email = trim($post['email'] ?? '');
        $password = trim($post['password'] ?? '');

        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['register_error'] = 'กรุณากรอกข้อมูลให้ครบทุกช่อง';
            header('Location: /register');
            exit;
        }

        if (Auth::register($name, $email, $password)) {
            // ส่งอีเมลต้อนรับยืนยันการสมัครสมาชิกเข้าเมลจริงของผู้ใช้
            $emailService = new EmailNotificationService();
            $emailService->send(
                $email,
                "ยินดีต้อนรับสู่ระบบ IT Helpdesk",
                "สวัสดีคุณ <strong>{$name}</strong>,<br><br>
                 ยินดีต้อนรับเข้าสู่ระบบ IT Helpdesk บัญชีของคุณพร้อมใช้งานแล้ว<br>
                 คุณสามารถเข้าสู่ระบบเพื่อแจ้งซ่อมอุปกรณ์และติดตามสถานะงานได้ตลอด 24 ชั่วโมงครับ"
            );

            $_SESSION['success'] = "สมัครสมาชิกสำเร็จและเข้าสู่ระบบเรียบร้อยแล้ว!";
            header('Location: /tickets');
            exit;
        }

        $_SESSION['register_error'] = 'อีเมลนี้ถูกใช้งานแล้วในระบบ';
        header('Location: /register');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }
}