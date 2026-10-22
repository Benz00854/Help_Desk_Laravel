<?php require __DIR__ . '/layouts/header.php'; ?>

<!-- แจ้งเตือนข้อความสำเร็จ -->
<?php if (!empty($_SESSION['success'])): ?>
    <div
        class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 text-xs rounded-2xl flex justify-between items-center shadow-sm font-medium">
        <span>
            <?= htmlspecialchars($_SESSION['success']) ?>
        </span>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<div class="max-w-4xl mx-auto space-y-6">

    <!-- ส่วนหัวหน้าโปรไฟล์ -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">ข้อมูลส่วนตัว (User Profile)</h1>
            <p class="text-xs text-red-800/80 font-medium mt-1">จัดการข้อมูลบัญชีผู้ใช้งาน และการตั้งค่าความปลอดภัย</p>
        </div>
        <a href="/tickets"
            class="px-4 py-2 bg-white border border-slate-200 hover:border-red-300 hover:text-red-800 text-slate-700 rounded-xl text-xs font-semibold transition shadow-sm">
            &larr; กลับหน้ารายการตั๋ว
        </a>
    </div>

    <!-- แผงบัตรข้อมูลผู้ใช้งาน -->
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
        <div
            class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6 pb-8 border-b border-slate-100">
            <!-- อวตารตัวใหญ่ -->
            <div
                class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-red-800 to-red-950 text-white flex items-center justify-center text-3xl font-black shadow-md">
                <?= mb_substr($_SESSION['user']['name'] ?? 'U', 0, 1, 'UTF-8') ?>
            </div>
            <div class="text-center sm:text-left flex-1">
                <h2 class="text-xl font-bold text-slate-900">
                    <?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>
                </h2>
                <p class="text-sm text-slate-400 mt-0.5">
                    <?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>
                </p>
                <div class="mt-3 flex flex-wrap justify-center sm:justify-start gap-2">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        <?= match ($_SESSION['user']['role'] ?? '') {
                            'admin' => 'bg-red-100 text-red-800 border border-red-200',
                            'technician' => 'bg-red-50 text-red-700 border border-red-100',
                            default => 'bg-slate-100 text-slate-700 border border-slate-200'
                        } ?>">
                        <?= match ($_SESSION['user']['role'] ?? '') {
                            'admin' => 'สิทธิ์ระดับ: ผู้ดูแลระบบ (Admin)',
                            'technician' => 'สิทธิ์ระดับ: ช่างเทคนิค (Technician)',
                            default => 'สิทธิ์ระดับ: ผู้ใช้งานทั่วไป (User)'
                        } ?>
                    </span>
                    <span
                        class="px-3 py-1 bg-red-50 text-red-800 text-xs font-semibold rounded-full border border-red-200">
                        สถานะบัญชี: ปกติ
                    </span>
                </div>
            </div>
        </div>

        <!-- ฟอร์มแก้ไขข้อมูลและเปลี่ยนรหัสผ่าน -->
        <form action="/profile" method="POST" class="pt-8 space-y-6">
            <h3 class="font-bold text-sm text-slate-800 tracking-wide uppercase">แก้ไขข้อมูลบัญชี</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">ชื่อ - นามสกุล</label>
                    <input type="text" name="name" required
                        value="<?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-red-800 focus:border-red-800 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">อีเมล (สำหรับรับแจ้งเตือน)</label>
                    <input type="email" name="email" required
                        value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-red-800 focus:border-red-800 outline-none transition">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <h3 class="font-bold text-sm text-slate-800 tracking-wide uppercase mb-1">ความปลอดภัย (เปลี่ยนรหัสผ่าน)
                </h3>
                <p class="text-[11px] text-slate-400 mb-4">* ปล่อยว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่านใหม่</p>

                <div class="max-w-md">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">รหัสผ่านใหม่</label>
                    <input type="password" name="password" placeholder="กรอกรหัสผ่านใหม่ (ถ้าต้องการเปลี่ยน)"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-red-800 focus:border-red-800 outline-none transition">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit"
                    class="px-6 py-2.5 bg-red-800 hover:bg-red-900 active:bg-red-950 text-white text-xs font-semibold rounded-xl shadow-md transition">
                    บันทึกการเปลี่ยนแปลง
                </button>
            </div>
        </form>
    </div>

</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>