<?php
namespace App\Services\Notifications;

class EmailNotificationService implements NotificationChannelInterface
{
    // =========================================================================
    // ตั้งค่าบัญชี GMAIL สำหรับใช้ส่งเมลจริงตรงนี้ครับ
    // =========================================================================
    private string $smtpHost = 'ssl://smtp.gmail.com';
    private int $smtpPort = 465;
    private string $smtpUser = 'your-email@gmail.com';       // ใส่อีเมล Gmail ของคุณที่นี่
    private string $smtpPass = 'xxxx xxxx xxxx xxxx';      // ใส่รหัสผ่านแอป 16 หลักที่ได้จาก Google
    private string $senderName = 'IT Helpdesk System';
    private string $logPath;

    public function __construct()
    {
        $this->logPath = __DIR__ . '/../../../storage/logs/mail.log';
    }

    public function send(string $to, string $subject, string $message): bool
    {
        // บันทึกลง Log เก็บประวัติไว้เสมอ
        $this->logMail($to, $subject, $message);

        // ถ้ายังไม่ได้กรอกอีเมลจริง ให้ข้ามการส่งจริงไปก่อน (ไม่ให้ Error)
        if ($this->smtpUser === 'your-email@gmail.com' || empty($this->smtpPass)) {
            return true;
        }

        try {
            return $this->sendViaSmtp($to, $subject, $message);
        } catch (\Exception $e) {
            error_log("SMTP Error: " . $e->getMessage());
            return false;
        }
    }

    // ฟังก์ชันเชื่อมต่อกับ Gmail SMTP Socket ส่งเมลจริงเข้า Inbox
    private function sendViaSmtp(string $to, string $subject, string $message): bool
    {
        $socket = @stream_socket_client($this->smtpHost . ':' . $this->smtpPort, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
        if (!$socket) {
            error_log("Cannot connect to SMTP: $errstr ($errno)");
            return false;
        }

        $this->readResponse($socket);

        // Handshake EHLO
        fputs($socket, "EHLO localhost\r\n");
        $this->readResponse($socket);

        // Authentication
        fputs($socket, "AUTH LOGIN\r\n");
        $this->readResponse($socket);

        fputs($socket, base64_encode($this->smtpUser) . "\r\n");
        $this->readResponse($socket);

        fputs($socket, base64_encode(str_replace(' ', '', $this->smtpPass)) . "\r\n");
        $authResp = $this->readResponse($socket);
        if (strpos($authResp, '235') === false) {
            fclose($socket);
            error_log("SMTP Auth Failed: $authResp");
            return false;
        }

        // Sender & Recipient
        fputs($socket, "MAIL FROM: <{$this->smtpUser}>\r\n");
        $this->readResponse($socket);

        fputs($socket, "RCPT TO: <{$to}>\r\n");
        $this->readResponse($socket);

        // Send Data
        fputs($socket, "DATA\r\n");
        $this->readResponse($socket);

        // MIME Header & Body HTML
        $headers = "From: =?UTF-8?B?" . base64_encode($this->senderName) . "?= <{$this->smtpUser}>\r\n";
        $headers .= "To: <{$to}>\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";

        $htmlContent = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;'>
            <div style='background-color: #2563eb; color: #ffffff; padding: 18px 24px;'>
                <h2 style='margin: 0; font-size: 20px;'>IT Helpdesk Notification</h2>
            </div>
            <div style='padding: 24px; background-color: #ffffff;'>
                <h3 style='color: #1e293b; margin-top: 0;'>{$subject}</h3>
                <div style='color: #475569; font-size: 15px;'>{$message}</div>
                <div style='margin-top: 28px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;'>
                    อีเมลแจ้งเตือนอัตโนมัติจากระบบบริการสารสนเทศ IT Helpdesk System
                </div>
            </div>
        </div>";

        fputs($socket, $headers . "\r\n" . $htmlContent . "\r\n.\r\n");
        $this->readResponse($socket);

        fputs($socket, "QUIT\r\n");
        fclose($socket);

        return true;
    }

    private function readResponse($socket): string
    {
        $response = '';
        while ($str = fgets($socket, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ")
                break;
        }
        return $response;
    }

    private function logMail(string $to, string $subject, string $message): void
    {
        $dir = dirname($this->logPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $logContent = "[{$timestamp}] TO: {$to} | SUBJECT: {$subject}\n";
        file_put_contents($this->logPath, $logContent, FILE_APPEND);
    }
}