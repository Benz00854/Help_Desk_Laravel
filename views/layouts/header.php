<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบแจ้งซ่อมและจัดการงานบริการ (IT Helpdesk)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
        }
    </style>
</head>

<body class="bg-white text-slate-800 min-h-screen flex">

    <!-- ========================================================= -->
    <!-- 1. SIDEBAR ด้านซ้าย -->
    <!-- ========================================================= -->
    <aside class="w-64 bg-white border-r border-slate-200 flex-shrink-0 flex flex-col min-h-screen z-20 shadow-sm">

        <!-- โลโก้ระบบแจ้งซ่อม -->
        <div class="p-6 flex items-center space-x-3 border-b border-slate-100">
            <div
                class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-700 via-red-800 to-red-950 text-white flex items-center justify-center text-sm font-black shadow-sm tracking-wider">
                HD
            </div>
            <div>
                <div class="text-slate-900 font-bold text-base leading-tight">ระบบแจ้งซ่อม</div>
                <div class="text-xs text-red-700 font-medium">IT Helpdesk Portal</div>
            </div>
        </div>

        <!-- รายการเมนูนำทาง -->
        <nav class="flex-1 px-4 space-y-6 overflow-y-auto text-xs py-5">

            <!-- เมนูหลัก -->
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-2">เมนูหลัก</div>

                <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                    <a href="/admin/dashboard"
                        class="flex items-center space-x-3 px-3 py-2.5 rounded-xl font-semibold transition <?= strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'bg-red-50 text-red-800 border border-red-200/80 shadow-sm' : 'text-slate-600 hover:bg-red-50/50 hover:text-red-800' ?>">
                        <span>แดชบอร์ดสถิติ</span>
                    </a>
                <?php endif; ?>

                <a href="/tickets"
                    class="flex items-center space-x-3 px-3 py-2.5 rounded-xl font-semibold transition <?= (strpos($_SERVER['REQUEST_URI'], 'tickets') !== false && strpos($_SERVER['REQUEST_URI'], 'create') === false) ? 'bg-red-50 text-red-800 border border-red-200/80 shadow-sm' : 'text-slate-600 hover:bg-red-50/50 hover:text-red-800' ?>">
                    <span>รายการแจ้งซ่อม</span>
                </a>

                <?php if (($_SESSION['user']['role'] ?? '') === 'user'): ?>
                    <a href="/tickets/create"
                        class="flex items-center space-x-3 px-3 py-2.5 rounded-xl font-semibold transition <?= strpos($_SERVER['REQUEST_URI'], 'create') !== false ? 'bg-red-50 text-red-800 border border-red-200/80 shadow-sm' : 'text-slate-600 hover:bg-red-50/50 hover:text-red-800' ?>">
                        <span>+ แจ้งซ่อมใหม่</span>
                    </a>
                <?php endif; ?>
            </div>

        </nav>
    </aside>

    <!-- ========================================================= -->
    <!-- 2. MAIN CONTENT AREA + HEADER BAR ขวาบน -->
    <!-- ========================================================= -->
    <div class="flex-1 flex flex-col min-w-0 bg-white">

        <!-- Header บาร์บนสุด -->
        <header
            class="bg-white border-b border-slate-200 px-8 py-3.5 flex justify-between items-center sticky top-0 z-40 shadow-sm">

            <div class="flex items-center space-x-3">
                <div>
                    <h1 class="text-base font-bold text-slate-900">IT Helpdesk Management</h1>
                    <p class="text-xs text-red-800/80 font-medium">ระบบติดตามและจัดการงานซ่อมบำรุง</p>
                </div>
            </div>

            <div class="flex items-center space-x-3 text-xs">

                <span
                    class="inline-flex items-center px-3 py-1 bg-red-50 text-red-800 rounded-full font-medium border border-red-200/80">
                    <span class="w-2 h-2 rounded-full bg-red-600 mr-1.5 animate-pulse"></span> ออนไลน์ปกติ
                </span>

                <span id="live-time-pill"
                    class="px-3 py-1 bg-red-50 text-red-800 border border-red-200/80 rounded-full font-mono font-medium">
                    20:09
                </span>

                <!-- ตัวสลับ Role ด่วนตาม Requirement [DONE] Navbar, Tailwind CSS, ตัวสลับ Role -->
                <div class="hidden sm:flex items-center space-x-1 bg-slate-50 p-1 rounded-full border border-slate-200">
                    <span class="text-[10px] text-slate-400 font-semibold px-2">Role:</span>
                    <a href="/switch-role/user" title="สลับเป็นผู้ใช้งาน (User)"
                        class="px-2.5 py-1 rounded-full text-[11px] font-semibold transition <?= ($_SESSION['user']['role'] ?? '') === 'user' ? 'bg-red-800 text-white shadow-sm' : 'text-slate-600 hover:text-red-800' ?>">
                        User
                    </a>
                    <a href="/switch-role/technician" title="สลับเป็นช่าง (Tech)"
                        class="px-2.5 py-1 rounded-full text-[11px] font-semibold transition <?= ($_SESSION['user']['role'] ?? '') === 'technician' ? 'bg-red-800 text-white shadow-sm' : 'text-slate-600 hover:text-red-800' ?>">
                        Tech
                    </a>
                    <a href="/switch-role/admin" title="สลับเป็นผู้ดูแลระบบ (Admin)"
                        class="px-2.5 py-1 rounded-full text-[11px] font-semibold transition <?= ($_SESSION['user']['role'] ?? '') === 'admin' ? 'bg-red-800 text-white shadow-sm' : 'text-slate-600 hover:text-red-800' ?>">
                        Admin
                    </a>
                </div>

                <!-- ===================================================== -->
                <!-- ปุ่มโปรไฟล์ผู้ใช้ + DROPDOWN เมนูขวาบน -->
                <!-- ===================================================== -->
                <div class="relative">

                    <button id="userMenuBtn" onclick="toggleUserDropdown(event)"
                        class="flex items-center space-x-2 pl-2 pr-3 py-1.5 bg-slate-50 hover:bg-red-50/50 border border-slate-200 hover:border-red-200 rounded-full transition focus:outline-none">
                        <div
                            class="w-7 h-7 rounded-full bg-gradient-to-tr from-red-800 to-red-950 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                            <?= mb_substr($_SESSION['user']['name'] ?? 'U', 0, 1, 'UTF-8') ?>
                        </div>
                        <span class="font-semibold text-slate-800 max-w-[110px] truncate text-xs">
                            <?= htmlspecialchars($_SESSION['user']['name'] ?? 'ผู้ใช้งาน') ?>
                        </span>
                        <span class="text-[10px] text-red-800">▼</span>
                    </button>

                    <!-- กล่อง Dropdown ที่เด้งลงมา -->
                    <div id="userDropdown"
                        class="hidden absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-200 py-3 z-50 animate-fade-in">

                        <!-- ข้อมูลส่วนตัวเบื้องต้นของผู้ใช้ -->
                        <div class="px-4 py-2 border-b border-slate-100 mb-2">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-gradient-to-tr from-red-800 to-red-950 text-white flex items-center justify-center font-bold text-sm">
                                    <?= mb_substr($_SESSION['user']['name'] ?? 'U', 0, 1, 'UTF-8') ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-900 truncate text-xs">
                                        <?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate">
                                        <?= htmlspecialchars($_SESSION['user']['email'] ?? 'user@helpdesk.com') ?>
                                    </div>
                                    <span class="inline-block px-2 py-0.5 mt-1 text-[10px] font-semibold rounded-full 
                                        <?= match ($_SESSION['user']['role'] ?? '') {
                                            'admin' => 'bg-red-100 text-red-800 border border-red-200',
                                            'technician' => 'bg-red-50 text-red-700 border border-red-100',
                                            default => 'bg-slate-100 text-slate-700 border border-slate-200'
                                        } ?>">
                                        <?= match ($_SESSION['user']['role'] ?? '') {
                                            'admin' => 'ผู้ดูแลระบบ (Admin)',
                                            'technician' => 'ช่างเทคนิค (Tech)',
                                            default => 'ผู้ใช้งาน (User)'
                                        } ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- ตัวสลับ Role ภายใน Dropdown -->
                        <div class="px-4 py-2 border-b border-slate-100">
                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-1.5">สลับบทบาท (Switch Role)</div>
                            <div class="grid grid-cols-3 gap-1">
                                <a href="/switch-role/user" class="text-center py-1 text-[11px] rounded font-medium <?= ($_SESSION['user']['role'] ?? '') === 'user' ? 'bg-red-800 text-white font-bold' : 'text-slate-600 hover:bg-red-50 hover:text-red-800' ?>">User</a>
                                <a href="/switch-role/technician" class="text-center py-1 text-[11px] rounded font-medium <?= ($_SESSION['user']['role'] ?? '') === 'technician' ? 'bg-red-800 text-white font-bold' : 'text-slate-600 hover:bg-red-50 hover:text-red-800' ?>">Tech</a>
                                <a href="/switch-role/admin" class="text-center py-1 text-[11px] rounded font-medium <?= ($_SESSION['user']['role'] ?? '') === 'admin' ? 'bg-red-800 text-white font-bold' : 'text-slate-600 hover:bg-red-50 hover:text-red-800' ?>">Admin</a>
                            </div>
                        </div>

                        <!-- เมนูเข้าสู่หน้าโปรไฟล์และรายการงาน -->
                        <div class="px-2 py-1 space-y-0.5">
                            <a href="/profile"
                                class="flex items-center space-x-2.5 px-3 py-2 text-slate-700 hover:bg-red-50 hover:text-red-800 font-semibold rounded-xl text-xs transition">
                                <span>ข้อมูลส่วนตัว (Profile)</span>
                            </a>
                            <a href="/tickets"
                                class="flex items-center space-x-2.5 px-3 py-2 text-slate-600 hover:bg-red-50/50 hover:text-red-800 font-medium rounded-xl text-xs transition">
                                <span>รายการงานของฉัน</span>
                            </a>
                        </div>

                        <!-- ปุ่มออกจากระบบ -->
                        <div class="mt-2 pt-2 border-t border-slate-100 px-2">
                            <a href="/logout"
                                class="flex items-center space-x-2.5 px-3 py-2 text-red-800 hover:bg-red-50 font-bold rounded-xl text-xs transition">
                                <span>ออกจากระบบ (Logout)</span>
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </header>

        <script>
            function toggleUserDropdown(event) {
                event.stopPropagation();
                const dropdown = document.getElementById('userDropdown');
                dropdown.classList.toggle('hidden');
            }

            document.addEventListener('click', function (event) {
                const dropdown = document.getElementById('userDropdown');
                const btn = document.getElementById('userMenuBtn');
                if (dropdown && !dropdown.contains(event.target) && !btn.contains(event.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        </script>

        <main class="flex-1 p-8 max-w-[1600px] w-full mx-auto">