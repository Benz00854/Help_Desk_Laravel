<?php
namespace App\Core;

class Auth
{
    private static array $mockUsers = [
        'user@helpdesk.com' => [
            'id' => 1,
            'name' => 'สมชาย รักงาน (User)',
            'email' => 'user@helpdesk.com',
            'password' => 'password123',
            'role' => 'user'
        ],
        'tech@helpdesk.com' => [
            'id' => 2,
            'name' => 'ช่างวิชัย (Tech)',
            'email' => 'tech@helpdesk.com',
            'password' => 'password123',
            'role' => 'technician'
        ],
        'admin@helpdesk.com' => [
            'id' => 3,
            'name' => 'ผู้ดูแลระบบ (Admin)',
            'email' => 'admin@helpdesk.com',
            'password' => 'password123',
            'role' => 'admin'
        ]
    ];

    public static function login(string $email, string $password): bool
    {
        // ตรวจสอบจากผู้ใช้ที่เพิ่งสมัครใหม่ใน Session ก่อน
        $registered = $_SESSION['registered_users'] ?? [];
        if (isset($registered[$email]) && $registered[$email]['password'] === $password) {
            $user = $registered[$email];
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            return true;
        }

        // ตรวจสอบกับ Mock Users พื้นฐาน
        if (isset(self::$mockUsers[$email])) {
            $user = self::$mockUsers[$email];
            if ($user['password'] === $password) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
                return true;
            }
        }
        return false;
    }

    // ฟังก์ชันสมัครสมาชิกด้วยอีเมลจริง
    public static function register(string $name, string $email, string $password, string $role = 'user'): bool
    {
        if (!isset($_SESSION['registered_users'])) {
            $_SESSION['registered_users'] = [];
        }

        // เช็กว่ามีอีเมลนี้หรือยัง
        if (isset($_SESSION['registered_users'][$email]) || isset(self::$mockUsers[$email])) {
            return false;
        }

        $newId = count($_SESSION['registered_users']) + 10;
        $_SESSION['registered_users'][$email] = [
            'id' => $newId,
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ];

        // ล็อกอินให้อัตโนมัติหลังสมัครเสร็จ
        $_SESSION['user'] = [
            'id' => $newId,
            'name' => $name,
            'email' => $email,
            'role' => $role
        ];

        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']) && !empty($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }
}