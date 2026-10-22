<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2 space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <span class="text-xs font-mono text-slate-400">TICKET #<?= $ticket['id'] ?></span>
                    <h1 class="text-2xl font-bold text-slate-900 mt-1"><?= htmlspecialchars($ticket['title']) ?></h1>
                </div>
                <div class="flex items-center space-x-2">
                    <span id="ticket-status-badge" class="px-3 py-1 text-sm font-semibold rounded-full 
                        <?= match (strtoupper(str_replace(['_', ' '], '', $ticket['status']))) {
                            'OPEN' => 'bg-red-50 text-red-800 border border-red-200',
                            'ASSIGNED' => 'bg-amber-50 text-amber-800 border border-amber-200',
                            'INPROGRESS' => 'bg-red-100 text-red-900 border border-red-300 font-bold',
                            'RESOLVED' => 'bg-slate-100 text-slate-800 border border-slate-300',
                            'CLOSED' => 'bg-slate-100 text-slate-700 border border-slate-200',
                            default => 'bg-slate-100 text-slate-700 border border-slate-200'
                        } ?>">
                        <?= $ticket['status'] ?>
                    </span>

                    <!-- ปุ่มลบตั๋วในหน้ารายละเอียด -->
                    <?php
                    $isAdmin = ($_SESSION['user']['role'] ?? '') === 'admin';
                    $isOwner = ($_SESSION['user']['name'] ?? '') === ($ticket['creator_name'] ?? '');
                    if ($isAdmin || ($isOwner && strtoupper($ticket['status']) === 'OPEN')):
                        ?>
                        <form action="/tickets/<?= $ticket['id'] ?>/delete" method="POST"
                            onsubmit="return confirm('ยืนยันการลบตั๋ว #<?= $ticket['id'] ?> ใช่หรือไม่?');">
                            <button type="submit" class="px-2.5 py-1 text-xs text-red-800 hover:text-red-900 hover:bg-red-50 font-bold rounded-lg border border-red-200 transition"
                                title="ลบตั๋วนี้">
                                ลบ
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs text-slate-600 pt-4 border-t border-slate-100">
                <div>ผู้แจ้ง: <span
                        class="font-semibold text-slate-800"><?= htmlspecialchars($ticket['creator_name']) ?></span></div>
                <div>ช่างผู้ดูแล: <span
                        class="font-semibold text-slate-800"><?= htmlspecialchars($ticket['assigned_tech']) ?></span>
                </div>
                <div>หมวดหมู่: <span
                        class="font-semibold text-slate-800"><?= htmlspecialchars($ticket['category']) ?></span></div>
                <div>เวลาที่แจ้ง: <span class="font-semibold text-slate-800"><?= $ticket['created_at'] ?></span></div>
            </div>

            <!-- ภาพถ่ายตอนแจ้งซ่อม (ถ้ามี) -->
            <?php if (!empty($ticket['image'])): ?>
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <span class="text-xs font-semibold text-slate-500 uppercase block mb-2">ภาพถ่ายตอนแจ้งซ่อม
                        (จากผู้ใช้):</span>
                    <img src="/storage/uploads/ticket-images/<?= htmlspecialchars($ticket['image']) ?>" alt="รูปภาพปัญหา"
                        class="max-h-48 rounded-xl border border-slate-200 shadow-sm">
                </div>
            <?php endif; ?>

            <!-- ภาพถ่ายผลงานหลังซ่อมเสร็จ (Attach Repair Photo) -->
            <div id="repair-result-box"
                class="<?= empty($ticket['repair_image']) ? 'hidden' : '' ?> mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                <span class="text-xs font-bold text-red-900 uppercase block mb-2">ภาพถ่ายผลงานหลังซ่อมเสร็จ
                    (จากช่าง):</span>
                <img id="repair-result-img"
                    src="<?= !empty($ticket['repair_image']) ? '/storage/uploads/ticket-images/' . htmlspecialchars($ticket['repair_image']) : '' ?>"
                    alt="ผลงานการซ่อม" class="max-h-56 rounded-xl border border-red-200 shadow-sm">
            </div>
        </div>

        <!-- รายการ Comment Timeline -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h3 class="font-bold text-slate-900 mb-4 text-sm">บันทึกการซ่อมและความคิดเห็น</h3>
            <div id="comments-container" class="space-y-3">
                <?php if (empty($ticket['comments'])): ?>
                    <p id="no-comments" class="text-xs text-slate-400">ยังไม่มีบันทึกข้อความ</p>
                <?php else: ?>
                    <?php foreach ($ticket['comments'] as $c): ?>
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl">
                            <div class="flex justify-between text-xs text-slate-500 mb-1">
                                <span class="font-bold text-slate-800"><?= htmlspecialchars($c['user']) ?>
                                    (<?= $c['role'] ?>)</span>
                                <span><?= $c['time'] ?></span>
                            </div>
                            <p class="text-xs text-slate-800"><?= htmlspecialchars($c['text']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- กล่องประเมินดาวสำหรับ User เมื่อ RESOLVED -->
        <?php if (strtoupper($ticket['status']) === 'RESOLVED' && ($_SESSION['user']['role'] ?? '') === 'user'): ?>
            <div class="bg-red-50 border border-red-200 p-6 rounded-2xl">
                <h3 class="font-bold text-red-900 mb-2 text-sm">งานซ่อมเสร็จสิ้น - กรุณาตรวจสอบและประเมิน</h3>
                <form action="/api/tickets/<?= $ticket['id'] ?>/rate" method="POST" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-red-900 mb-1">คะแนนความพึงพอใจ (1-5)</label>
                        <select name="rating" class="px-3 py-1.5 border border-red-200 rounded-xl text-xs bg-white text-slate-800 outline-none focus:ring-2 focus:ring-red-800">
                            <option value="5">ดีมาก (5)</option>
                            <option value="4">ดี (4)</option>
                            <option value="3">ปานกลาง (3)</option>
                            <option value="2">ต้องปรับปรุง (2)</option>
                            <option value="1">ไม่พอใจ (1)</option>
                        </select>
                    </div>
                    <textarea name="feedback" placeholder="ความคิดเห็นเพิ่มเติม..."
                        class="w-full p-2.5 border border-red-200 rounded-xl text-xs bg-white outline-none focus:ring-2 focus:ring-red-800"></textarea>
                    <button type="submit"
                        class="px-4 py-2 bg-red-800 hover:bg-red-900 active:bg-red-950 text-white font-semibold rounded-xl text-xs transition shadow-sm">
                        ยืนยันปิดงาน (Closed)
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- กล่องแสดงผลการประเมินเมื่องานปิดแล้ว (CLOSED) -->
        <?php if (strtoupper($ticket['status']) === 'CLOSED' && !empty($ticket['rating'])): ?>
            <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm">
                <div class="text-xs font-semibold text-slate-400 uppercase mb-1">ผลการประเมินจากผู้ใช้งาน:</div>
                <div class="text-red-800 font-bold text-base mb-1">
                    คะแนน: <?= (int) $ticket['rating'] ?> / 5
                </div>
                <?php if (!empty($ticket['feedback'])): ?>
                    <p class="text-xs text-slate-600 italic">"<?= htmlspecialchars($ticket['feedback']) ?>"</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- แผง Action ของช่าง -->
    <?php if (($_SESSION['user']['role'] ?? '') === 'technician' || ($_SESSION['user']['role'] ?? '') === 'admin'): ?>
        <div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 sticky top-24">
                <h3 class="font-bold text-slate-900 mb-2 text-sm">Technician Action Panel</h3>

                <?php if (($_SESSION['user']['role'] ?? '') === 'admin' && strtoupper($ticket['status']) !== 'CLOSED'): ?>
                    <div class="mb-4 pb-4 border-b border-slate-100">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">มอบหมายช่าง (Admin Assign)</label>
                        <form action="/tickets/<?= $ticket['id'] ?>/assign" method="POST" class="space-y-2">
                            <select name="tech_name" class="w-full px-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-slate-50 outline-none focus:ring-2 focus:ring-red-800">
                                <option value="ช่างวิชัย (Tech)" <?= ($ticket['assigned_tech'] ?? '') === 'ช่างวิชัย (Tech)' ? 'selected' : '' ?>>ช่างวิชัย (Tech) - tech@helpdesk.com</option>
                                <option value="ช่างสมเกียรติ (Tech)" <?= ($ticket['assigned_tech'] ?? '') === 'ช่างสมเกียรติ (Tech)' ? 'selected' : '' ?>>ช่างสมเกียรติ (Tech) - it-support@helpdesk.com</option>
                            </select>
                            <input type="hidden" name="tech_id" value="2">
                            <input type="hidden" name="tech_email" value="tech@helpdesk.com">
                            <button type="submit" class="w-full py-2 bg-red-800 hover:bg-red-900 active:bg-red-950 text-white text-xs font-semibold rounded-xl shadow transition">
                                ยืนยันการมอบหมายงาน
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <?php if (strtoupper($ticket['status']) === 'CLOSED'): ?>
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center text-slate-500 text-xs font-medium">
                        ตั๋วนี้ปิดงานสมบูรณ์แล้ว (Closed)<br>ไม่สามารถแก้ไขเพิ่มเติมได้
                    </div>
                <?php else: ?>
                    <p class="text-xs text-red-800/80 font-medium mb-4">อัปเดตสถานะและแนบภาพถ่ายงานซ่อม (Diagram 4 & 7)</p>
                    <form id="quick-status-form" data-ticket-id="<?= $ticket['id'] ?>" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">เปลี่ยนสถานะ</label>
                            <select id="select-status"
                                class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-slate-50 outline-none focus:ring-2 focus:ring-red-800">
                                <option value="IN_PROGRESS" <?= strtoupper($ticket['status']) === 'IN_PROGRESS' ? 'selected' : '' ?>>กำลังซ่อม (IN_PROGRESS)</option>
                                <option value="RESOLVED" <?= strtoupper($ticket['status']) === 'RESOLVED' ? 'selected' : '' ?>>
                                    ซ่อมเสร็จแล้ว (RESOLVED)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">แนบรูปภาพผลงาน (Attach Repair Photo)</label>
                            <input type="file" id="repair-photo" accept="image/*"
                                class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-800 hover:file:bg-red-100 cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">บันทึกรายละเอียดการซ่อม</label>
                            <textarea id="status-comment" rows="3" placeholder="ระบุสิ่งที่ดำเนินการแก้ไข..."
                                class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs outline-none bg-slate-50 focus:bg-white focus:ring-2 focus:ring-red-800"></textarea>
                        </div>
                        <button type="submit"
                            class="w-full py-2.5 bg-red-800 hover:bg-red-900 active:bg-red-950 text-white text-xs font-semibold rounded-xl shadow transition">
                            บันทึกการเปลี่ยนแปลง
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>