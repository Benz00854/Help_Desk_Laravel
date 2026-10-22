<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- แจ้งเตือนสำเร็จ -->
<?php if (!empty($_SESSION['success'])): ?>
    <div
        class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 text-xs rounded-2xl flex justify-between items-center shadow-sm font-medium">
        <span><?= htmlspecialchars($_SESSION['success']) ?></span>
            <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<!-- ========================================================= -->
<!-- กล่องค้นหาและฟิลเตอร์ สีขาวคลีน -->
<!-- ========================================================= -->
<div
    class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6 flex flex-wrap items-center justify-between gap-3">

    <!-- ช่องค้นหา -->
    <div class="relative flex-1 min-w-[280px]">
        <input type="text" id="ticketSearchInput" placeholder="ค้นหา รหัส / หัวข้อ / ปัญหา..."
            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-red-800 focus:border-red-800 outline-none transition">
    </div>

    <!-- ดรอปดาวน์ฟิลเตอร์ -->
    <div class="flex items-center space-x-2 text-xs">
        <select id="categoryFilterSelect" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 outline-none focus:ring-2 focus:ring-red-800">
            <option value="">ทุกประเภทงาน</option>
            <option value="Hardware">Hardware</option>
            <option value="Software">Software</option>
            <option value="Network">Network</option>
            <option value="Printer">Printer</option>
        </select>
        <select class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 outline-none focus:ring-2 focus:ring-red-800">
            <option>ใหม่ล่าสุด</option>
            <option>เก่าสุด</option>
            <option>ความเร่งด่วน</option>
        </select>
        <label class="flex items-center space-x-1.5 text-slate-600 pl-2 cursor-pointer font-medium">
            <input type="checkbox" class="rounded text-red-800 focus:ring-red-800">
            <span>เฉพาะงานของฉัน</span>
        </label>
    </div>

    <!-- ปุ่มแจ้งซ่อมสีแดงเข้มสง่างาม -->
    <?php if (($_SESSION['user']['role'] ?? '') === 'user'): ?>
        <a href="/tickets/create"
            class="px-4 py-2.5 bg-red-800 hover:bg-red-900 active:bg-red-950 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center space-x-1.5">
            <span>+ แจ้งซ่อมใหม่</span>
        </a>
    <?php endif; ?>
</div>

<!-- ========================================================= -->
<!-- แท็บแคปซูลกรองสถานะ (Status Filter Pills) -->
<!-- ========================================================= -->
<?php $currentFilter = $_GET['filter'] ?? 'all'; ?>
<div class="flex flex-wrap gap-2 mb-6 text-xs font-semibold">
    <a href="/tickets?filter=all"
        class="px-4 py-2 rounded-full transition <?= $currentFilter === 'all' ? 'bg-red-800 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:border-red-300 hover:text-red-800' ?>">
        ทั้งหมด
    </a>
    <a href="/tickets?filter=open"
        class="px-4 py-2 rounded-full transition <?= $currentFilter === 'open' ? 'bg-red-800 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:border-red-300 hover:text-red-800' ?>">
        รอรับเรื่อง
    </a>
    <a href="/tickets?filter=assigned"
        class="px-4 py-2 rounded-full transition <?= $currentFilter === 'assigned' ? 'bg-red-800 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:border-red-300 hover:text-red-800' ?>">
        มอบหมายแล้ว
    </a>
    <a href="/tickets?filter=active"
        class="px-4 py-2 rounded-full transition <?= $currentFilter === 'active' ? 'bg-red-800 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:border-red-300 hover:text-red-800' ?>">
        กำลังซ่อม
    </a>
    <a href="/tickets?filter=completed"
        class="px-4 py-2 rounded-full transition <?= $currentFilter === 'completed' ? 'bg-red-800 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:border-red-300 hover:text-red-800' ?>">
        เสร็จสิ้นแล้ว
    </a>
</div>

<!-- ========================================================= -->
<!-- GRID การ์ดรายการตั๋วแจ้งซ่อม (4 คอลัมน์ คลีนตา) -->
<!-- ========================================================= -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    <?php if (empty($tickets)): ?>
        <div class="col-span-full py-16 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
            ไม่มีรายการแจ้งซ่อมในหมวดหมู่นี้
        </div>
    <?php else: ?>
            <?php foreach ($tickets as $t):
                $rawStatus = strtoupper(str_replace(['_', ' '], '', $t['status'] ?? 'OPEN'));

                // สีและไอคอนหมวดหมู่ตาม Dark Red Palette
                $categoryConfig = match ($t['category'] ?? 'Hardware') {
                    'Network' => ['icon' => 'NET', 'bg' => 'bg-red-100/70 text-red-900 border border-red-200'],
                    'Printer' => ['icon' => 'PRT', 'bg' => 'bg-slate-100 text-slate-800 border border-slate-200'],
                    'Software' => ['icon' => 'APP', 'bg' => 'bg-rose-50 text-rose-900 border border-rose-200'],
                    default => ['icon' => 'HW', 'bg' => 'bg-red-50 text-red-800 border border-red-200']
                };

                // ป้ายระดับความสำคัญ
                $priorityBadge = match (strtolower($t['priority'] ?? 'medium')) {
                    'high' => '<span class="px-2.5 py-0.5 text-[10px] bg-red-100 text-red-800 border border-red-300 rounded-full font-bold">ด่วนมาก</span>',
                    'medium' => '<span class="px-2.5 py-0.5 text-[10px] bg-red-50 text-red-800 border border-red-200 rounded-full font-medium">ปกติ</span>',
                    default => '<span class="px-2.5 py-0.5 text-[10px] bg-slate-50 text-slate-500 border border-slate-200 rounded-full">ต่ำ</span>'
                };

                // ป้ายสถานะงาน
                $statusPill = match ($rawStatus) {
                    'OPEN' => '<span class="px-3 py-1 text-[11px] font-semibold bg-red-50 text-red-800 border border-red-200 rounded-full">รอรับเรื่อง</span>',
                    'ASSIGNED' => '<span class="px-3 py-1 text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200 rounded-full">มอบหมายแล้ว</span>',
                    'INPROGRESS' => '<span class="px-3 py-1 text-[11px] font-bold bg-red-100 text-red-900 border border-red-300 rounded-full">กำลังซ่อม</span>',
                    'RESOLVED', 'CLOSED' => '<span class="px-3 py-1 text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-300 rounded-full">เสร็จสิ้น</span>',
                    default => '<span class="px-3 py-1 text-[11px] bg-slate-100 text-slate-600 rounded-full">ยกเลิก</span>'
                };
                ?>

            <!-- Card 1 ใบ -->
            <div
                data-ticket-card
                data-title="<?= htmlspecialchars(strtolower($t['title'])) ?>"
                data-id="<?= $t['id'] ?>"
                data-category="<?= htmlspecialchars($t['category'] ?? 'Hardware') ?>"
                class="bg-white rounded-2xl border border-slate-200 hover:border-red-300 shadow-sm hover:shadow-md transition-all flex flex-col justify-between overflow-hidden">

                <div class="p-4">
                    <!-- ส่วนบน: ไอคอน + รหัส + หัวข้อ -->
                    <div class="flex items-start space-x-3 mb-3">
                        <div
                            class="w-10 h-10 rounded-xl <?= $categoryConfig['bg'] ?> flex items-center justify-center text-xs font-bold shadow-sm flex-shrink-0">
                                    <?= $categoryConfig['icon'] ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[11px] font-mono text-slate-400">R26060<?= $t['id'] ?></div>
                            <a href="/tickets/<?= $t['id'] ?>"
                                class="text-sm font-bold text-slate-900 hover:text-red-800 line-clamp-1 transition">
                                        <?= htmlspecialchars($t['title']) ?>
                            </a>
                        </div>
                    </div>

                    <!-- สถานที่ & ป้ายความเร่งด่วน -->
                    <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                        <div class="flex items-center space-x-1 truncate">
                            <span class="truncate">แผนกทั่วไป / อาคาร 1</span>
                        </div>
                        <div><?= $priorityBadge ?></div>
                    </div>

                    <!-- คะแนนประเมิน (ถ้ามี) -->
                            <?php if (!empty($t['rating'])): ?>
                        <div class="text-amber-600 font-semibold text-xs mb-2">
                            คะแนนประเมิน: <?= (int) $t['rating'] ?>/5
                        </div>
                            <?php endif; ?>
                </div>

                <!-- ส่วนล่าง: ป้ายสถานะ + ชื่อช่างผู้ดูแล -->
                <div class="px-4 py-3 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between text-xs">
                    <div>
                                <?= $statusPill ?>
                    </div>

                    <div class="flex items-center space-x-1.5 text-slate-600">
                        <div
                            class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold flex items-center justify-center">
                            ช
                        </div>
                        <span class="text-[11px] font-medium truncate max-w-[90px]">
                                    <?= htmlspecialchars($t['assigned_tech'] ?? 'ยังไม่มอบหมาย') ?>
                        </span>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('ticketSearchInput');
    const categorySelect = document.getElementById('categoryFilterSelect');
    const cards = document.querySelectorAll('[data-ticket-card]');

    function filterCards() {
        const query = (searchInput.value || '').trim().toLowerCase();
        const selectedCat = (categorySelect.value || '').toLowerCase();

        cards.forEach(card => {
            const title = (card.dataset.title || '').toLowerCase();
            const id = (card.dataset.id || '').toLowerCase();
            const cat = (card.dataset.category || '').toLowerCase();

            const matchQuery = !query || title.includes(query) || id.includes(query);
            const matchCat = !selectedCat || cat === selectedCat;

            if (matchQuery && matchCat) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterCards);
    if (categorySelect) categorySelect.addEventListener('change', filterCards);
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>