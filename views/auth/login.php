<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Helpdesk System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
        }
    </style>
</head>

<body class="bg-white min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-200 p-8">
        <div class="text-center mb-6">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-gradient-to-br from-red-700 via-red-800 to-red-950 text-white flex items-center justify-center font-black text-base shadow-md mb-3 tracking-wider">HD</div>
            <h1 class="text-2xl font-bold text-slate-900">เข้าสู่ระบบ IT Helpdesk</h1>
            <p class="text-xs text-red-800/80 font-medium mt-1">ระบบแจ้งซ่อมและติดตามงานบริการสารสนเทศ</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 text-xs rounded-xl flex items-center font-medium">
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">อีเมลผู้ใช้งาน</label>
                <input type="email" id="email" name="email" required placeholder="name@helpdesk.com"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-red-800 focus:border-red-800 outline-none transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">รหัสผ่าน</label>
                <input type="password" id="password" name="password" required placeholder="••••••••"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-red-800 focus:border-red-800 outline-none transition">
            </div>

            <button type="submit"
                class="w-full py-2.5 bg-red-800 hover:bg-red-900 active:bg-red-950 text-white font-semibold rounded-xl text-xs shadow-md transition">
                เข้าสู่ระบบ
            </button>
        </form>

        <!-- ลิงก์ไปหน้าสมัครสมาชิก -->
        <div class="mt-4 text-center text-xs text-slate-500">
            ยังไม่มีบัญชีผู้ใช้งาน?
            <a href="/register" class="text-red-800 hover:text-red-900 hover:underline font-semibold">สมัครสมาชิกที่นี่</a>
        </div>

        <!-- แผงทดสอบ: คลิกเดียวเพื่อกรอกข้อมูลบัญชีทดสอบ 3 Roles -->
        <div class="mt-8 pt-6 border-t border-slate-100">
            <p class="text-xs font-semibold text-slate-400 uppercase text-center mb-3">บัญชีทดสอบด่วน (Quick Demo)</p>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" onclick="fillDemo('user@helpdesk.com', 'password123')"
                    class="px-2 py-1.5 bg-red-50 hover:bg-red-100 text-red-800 border border-red-200 text-xs font-semibold rounded-lg text-center transition">
                    User
                </button>
                <button type="button" onclick="fillDemo('tech@helpdesk.com', 'password123')"
                    class="px-2 py-1.5 bg-red-100 hover:bg-red-200 text-red-900 border border-red-300 text-xs font-semibold rounded-lg text-center transition">
                    Tech
                </button>
                <button type="button" onclick="fillDemo('admin@helpdesk.com', 'password123')"
                    class="px-2 py-1.5 bg-red-800 hover:bg-red-900 text-white text-xs font-bold rounded-lg text-center transition shadow-sm">
                    Admin
                </button>
            </div>
            <p class="text-[11px] text-slate-400 text-center mt-2">รหัสผ่านของทุกบัญชี: <code
                    class="text-red-900 font-mono font-semibold">password123</code></p>
        </div>
    </div>

    <script>
        function fillDemo(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
        }
    </script>
</body>

</html>