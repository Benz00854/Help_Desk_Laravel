<?php
namespace App\Services\Notifications;

interface NotificationChannelInterface
{
    /**
     * ส่งข้อความแจ้งเตือน
     *
     * @param string $to อีเมลผู้รับ
     * @param string $subject หัวข้อเรื่อง
     * @param string $message รายละเอียดเนื้อหา
     * @return bool
     */
    public function send(string $to, string $subject, string $message): bool;
}