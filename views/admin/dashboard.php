<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- Chart.js สำหรับเรนเดอร์กราฟ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- ========================================================= -->
<!-- 1. แบนเนอร์สีแดงเข้มต้อนรับ -->
<!-- ========================================================= -->
<div class="bg-gradient-to-r from-red-950 via-red-800 to-red-900 rounded-3xl p-6 text-white shadow-lg mb-6 relative overflow-hidden">
    <div class="relative z-10">
        <h1 class="text-2xl font-bold tracking-tight">ยินดีต้อนรับ, Admin!</h1>
        <p class="text-red-100 text-xs mt-1">ภาพรวมระบบจัดการการแจ้งซ่อม — แสดงข้อมูลสถิติและการดำเนินงานทั้งหมดในระบบ</p>
        
        <div class="flex items-center space-x-4 mt-4 pt-3 border-t border-red-400/30 text-xs text-red-200">
            <span class="flex items-center">
                <span class="w-2 h-2 rounded-full bg-red-400 mr-1.5 animate-ping"></span> 
                อัปเดตล่าสุด: <?= date('H:i') ?> น.
            </span>
            <span>•</span>
            <span>ตั๋วทั้งหมดในระบบ: <strong class="text-white"><?= $stats['total'] ?></strong> รายการ</span>
        </div>
    </div>
    <!-- ลวดลายกราฟิกด้านหลัง -->
    <div class="absolute -right-6 -bottom-10 opacity-10 text-9xl select-none font-bold">
        RMS
    </div>
</div>

<!-- ========================================================= -->
<!-- 2. แถบแจ้งเตือนระดับบริหาร (Warning Status) -->
<!-- ========================================================= -->
<div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 shadow-sm flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-xl bg-red-50 text-red-800 flex items-center justify-center font-bold text-xs border border-red-200">
            STAT
        </div>
        <div>
            <div class="flex items-center space-x-2">
                <span class="font-bold text-slate-800 text-sm">
                    <?= $stats['urgent_count'] > 0 ? "พบตั๋วเร่งด่วน {$stats['urgent_count']} รายการที่ต้องเร่งดำเนินการ" : "ระบบการดำเนินงานเป็นปกติ ไม่มีตั๋วคั่งค้างวิกฤต" ?>
                </span>
                <?php if ($stats['urgent_count'] > 0): ?>
                        <span class="px-2 py-0.5 bg-red-100 text-red-800 text-[10px] font-bold rounded-full border border-red-200 animate-pulse">● Warning Status</span>
                <?php else: ?>
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-800 text-[10px] font-bold rounded-full border border-slate-200">● Good Status</span>
                <?php endif; ?>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">
                อัตราการเคลียร์ตั๋วเทียบตั๋วใหม่ <?= $stats['clearance_rate'] ?>% • ตั๋วค้างในคิวทั้งหมด <?= $stats['pending_total'] ?> รายการ • ช่างพร้อมปฏิบัติงาน
            </p>
        </div>
    </div>

    <div class="flex items-center space-x-6 text-xs border-l border-slate-100 pl-6">
        <div>
            <div class="text-[10px] uppercase font-semibold text-slate-400">INTAKE VS CLEARANCE</div>
            <div class="font-bold text-slate-800 text-sm mt-0.5 flex items-center space-x-1">
                <span><?= $stats['clearance_rate'] ?>%</span>
                <span class="text-red-800 text-xs font-semibold">Rate</span>
            </div>
        </div>
        <div>
            <div class="text-[10px] uppercase font-semibold text-slate-400">NET BACKLOG DELTA</div>
            <div class="font-bold text-red-900 text-sm mt-0.5">
                <?= $stats['pending_total'] > 0 ? "+" . $stats['pending_total'] : "0" ?> ตั๋ว
            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- 3. KPI STAT CARDS 6 ใบ -->
<!-- ========================================================= -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    
    <!-- 1. TOTAL TICKETS -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between hover:border-red-300 transition">
        <div class="flex justify-between items-start">
            <span class="text-[10px] font-bold text-slate-400 tracking-wider">TOTAL TICKETS</span>
            <span class="text-slate-400 text-xs font-bold">TOTAL</span>
        </div>
        <div class="mt-2">
            <div class="text-2xl font-black text-slate-900"><?= $stats['total'] ?></div>
            <div class="text-[10px] text-slate-400 truncate mt-0.5">ตั๋วทั้งหมดในระบบ</div>
        </div>
    </div>

    <!-- 2. OPEN / PENDING -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between hover:border-red-300 transition">
        <div class="flex justify-between items-start">
            <span class="text-[10px] font-bold text-red-800 tracking-wider">OPEN / PENDING</span>
            <span class="w-5 h-5 rounded-full bg-red-50 text-red-800 border border-red-200 text-xs flex items-center justify-center font-bold">!</span>
        </div>
        <div class="mt-2">
            <div class="text-2xl font-black text-red-800"><?= $stats['pending_total'] ?></div>
            <div class="text-[10px] text-slate-400 truncate mt-0.5">งานที่ยังไม่ได้เริ่มแก้</div>
        </div>
    </div>

    <!-- 3. IN PROGRESS -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between hover:border-red-300 transition">
        <div class="flex justify-between items-start">
            <span class="text-[10px] font-bold text-red-700 tracking-wider">IN PROGRESS</span>
            <span class="text-red-700 text-xs font-bold">WIP</span>
        </div>
        <div class="mt-2">
            <div class="text-2xl font-black text-red-700"><?= $stats['counts']['IN_PROGRESS'] ?></div>
            <div class="text-[10px] text-slate-400 truncate mt-0.5">งานที่ช่างกำลังทำ</div>
        </div>
    </div>

    <!-- 4. URGENT -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between hover:border-red-300 transition">
        <div class="flex justify-between items-start">
            <span class="text-[10px] font-bold text-red-900 tracking-wider">URGENT</span>
            <span class="text-red-900 text-xs font-bold">HIGH</span>
        </div>
        <div class="mt-2">
            <div class="text-2xl font-black text-red-900"><?= $stats['urgent_count'] ?></div>
            <div class="text-[10px] text-slate-400 truncate mt-0.5">งานระดับเร่งด่วนสูง</div>
        </div>
    </div>

    <!-- 5. RESOLVED -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between hover:border-red-300 transition">
        <div class="flex justify-between items-start">
            <span class="text-[10px] font-bold text-slate-500 tracking-wider">RESOLVED</span>
            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-800 border border-slate-200 text-xs flex items-center justify-center font-bold">OK</span>
        </div>
        <div class="mt-2">
            <div class="text-2xl font-black text-slate-800"><?= $stats['completed_total'] ?></div>
            <div class="text-[10px] text-slate-400 truncate mt-0.5">งานที่แก้ไขสำเร็จแล้ว</div>
        </div>
    </div>

    <!-- 6. CSAT / RATING -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between hover:border-red-300 transition">
        <div class="flex justify-between items-start">
            <span class="text-[10px] font-bold text-red-800 tracking-wider">CSAT / RATING</span>
            <span class="text-red-800 text-xs font-bold">SCORE</span>
        </div>
        <div class="mt-2">
            <div class="text-2xl font-black text-slate-900 flex items-baseline space-x-1">
                <span><?= $stats['satisfaction_score'] ?></span>
                <span class="text-red-800 text-xs font-bold">/ 5</span>
            </div>
            <div class="text-[10px] text-slate-400 truncate mt-0.5">คะแนนความพึงพอใจ</div>
        </div>
    </div>

</div>

<!-- ========================================================= -->
<!-- 4. กราฟแนวโน้ม 7 วัน & สัดส่วนสถานะตั๋ว -->
<!-- ========================================================= -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    
    <!-- ฝั่งซ้าย: TICKET VOLUME & RESOLUTION TREND -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
            <div>
                <h3 class="font-bold text-sm text-slate-800 tracking-wide uppercase">TICKET VOLUME & RESOLUTION TREND</h3>
                <p class="text-xs text-slate-400">เปรียบเทียบตั๋วเปิดใหม่ vs งานที่ช่างซ่อมเสร็จสิ้นรายวัน (7 วันล่าสุด)</p>
            </div>
            <div class="flex items-center space-x-4 text-xs">
                <span class="flex items-center space-x-1.5">
                    <span class="w-3 h-3 rounded-full bg-red-800"></span>
                    <span class="text-slate-700 font-medium">ตั๋วเปิดใหม่</span>
                </span>
                <span class="flex items-center space-x-1.5">
                    <span class="w-3 h-3 rounded-full bg-slate-500"></span>
                    <span class="text-slate-700 font-medium">ซ่อมเสร็จสิ้น</span>
                </span>
            </div>
        </div>

        <div class="h-64">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <!-- ฝั่งขวา: TICKET STATUS BREAKDOWN โดนัท -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <div>
            <h3 class="font-bold text-sm text-slate-800 tracking-wide uppercase">TICKET STATUS BREAKDOWN</h3>
            <p class="text-xs text-slate-400 mb-4">สัดส่วนตามสถานะวงจรชีวิตตั๋ว</p>

            <div class="max-w-[180px] mx-auto my-2">
                <canvas id="statusDonut"></canvas>
            </div>
        </div>

        <!-- รายการสรุปด้านล่างโดนัท -->
        <div class="grid grid-cols-2 gap-2 text-xs pt-4 border-t border-slate-100">
            <div class="flex items-center justify-between p-1.5 bg-slate-50 rounded-lg">
                <span class="flex items-center text-slate-700 font-medium">
                    <span class="w-2 h-2 rounded-full bg-red-800 mr-1.5"></span> Open
                </span>
                <strong class="text-slate-900"><?= $stats['counts']['OPEN'] ?></strong>
            </div>
            <div class="flex items-center justify-between p-1.5 bg-slate-50 rounded-lg">
                <span class="flex items-center text-slate-700 font-medium">
                    <span class="w-2 h-2 rounded-full bg-red-600 mr-1.5"></span> Assigned
                </span>
                <strong class="text-slate-900"><?= $stats['counts']['ASSIGNED'] ?></strong>
            </div>
            <div class="flex items-center justify-between p-1.5 bg-slate-50 rounded-lg">
                <span class="flex items-center text-slate-700 font-medium">
                    <span class="w-2 h-2 rounded-full bg-rose-500 mr-1.5"></span> In Progress
                </span>
                <strong class="text-slate-900"><?= $stats['counts']['IN_PROGRESS'] ?></strong>
            </div>
            <div class="flex items-center justify-between p-1.5 bg-slate-50 rounded-lg">
                <span class="flex items-center text-slate-700 font-medium">
                    <span class="w-2 h-2 rounded-full bg-slate-500 mr-1.5"></span> Resolved
                </span>
                <strong class="text-slate-900"><?= $stats['completed_total'] ?></strong>
            </div>
        </div>
    </div>

</div>

<!-- ========================================================= -->
<!-- 5. หมวดหมู่ที่ถูกแจ้งบ่อย & อันดับช่าง -->
<!-- ========================================================= -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- หมวดหมู่ที่ถูกแจ้งบ่อย -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <h3 class="font-bold text-sm text-slate-800 tracking-wide uppercase flex items-center space-x-2 mb-1">
            <span>หมวดหมู่ที่ถูกแจ้งบ่อย</span>
        </h3>
        <p class="text-xs text-slate-400 mb-4">สถิติการแจ้งซ่อมแยกตามหมวดหมู่อุปกรณ์</p>

        <div class="space-y-4 text-xs">
            <?php foreach ($stats['categories'] as $catName => $catCount):
                $percent = $stats['total'] > 0 ? round(($catCount / $stats['total']) * 100) : 0;
                ?>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-medium text-slate-700"><?= $catName ?></span>
                        <span class="font-bold text-slate-900"><?= $catCount ?> รายการ (<?= $percent ?>%)</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-gradient-to-r from-red-700 to-red-900 h-2.5 rounded-full transition-all duration-500" style="width: <?= max($percent, 5) ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- อันดับช่างยอดเยี่ยม (Top Technicians) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <div>
            <h3 class="font-bold text-sm text-slate-800 tracking-wide uppercase flex items-center space-x-2 mb-1">
                <span>TECHNICIAN PERFORMANCE</span>
            </h3>
            <p class="text-xs text-slate-400 mb-4">เปรียบเทียบผลงานการปิดงานและคะแนนความพึงพอใจของช่าง</p>

            <div class="divide-y divide-slate-100">
                <?php foreach ($stats['top_technicians'] as $idx => $tech): ?>
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-3">
                            <span class="w-7 h-7 rounded-full bg-red-50 text-red-800 border border-red-200 font-bold flex items-center justify-center text-xs">
                                <?= $idx + 1 ?>
                            </span>
                            <div>
                                <div class="font-bold text-slate-800"><?= htmlspecialchars($tech['name']) ?></div>
                                <div class="text-[10px] text-slate-400">ช่างเทคนิคประจำแผนก</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2.5 py-1 bg-red-50 text-red-800 font-bold rounded-lg border border-red-200">
                                <?= $tech['completed'] ?> งาน
                            </span>
                            <div class="text-[11px] text-red-900 font-semibold mt-1">
                                คะแนน <?= $tech['rating'] ?> / 5.0
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<!-- ========================================================= -->
<!-- 6. สคริปต์วาดกราฟ Chart.js (ลื่นไหลเหมือนในภาพ) -->
<!-- ========================================================= -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. กราฟเส้นแนวโน้ม 7 วัน (TICKET VOLUME & RESOLUTION TREND)
    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: ['11/09', '12/09', '13/09', '14/09', '15/09', '16/09', 'Today'],
            datasets: [
                {
                    label: 'ตั๋วเปิดใหม่',
                    data: [0, 0, 0, <?= max(1, $stats['total'] - 2) ?>, <?= max(1, $stats['total'] - 1) ?>, <?= $stats['total'] ?>, <?= $stats['total'] ?>],
                    borderColor: '#991b1b',
                    backgroundColor: 'rgba(153, 27, 27, 0.08)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#991b1b'
                },
                {
                    label: 'ซ่อมเสร็จสิ้น',
                    data: [0, 0, 0, 0, 0, <?= $stats['completed_total'] ?>, <?= $stats['completed_total'] ?>],
                    borderColor: '#64748b',
                    backgroundColor: 'rgba(100, 116, 139, 0.10)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#64748b'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, font: { size: 10 } },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });

    // 2. กราฟโดนัท (TICKET STATUS BREAKDOWN)
    const ctxDonut = document.getElementById('statusDonut').getContext('2d');
    new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            labels: ['Open', 'Assigned', 'In Progress', 'Resolved'],
            datasets: [{
                data: [
                    <?= max($stats['counts']['OPEN'], 0) ?>,
                    <?= max($stats['counts']['ASSIGNED'], 0) ?>,
                    <?= max($stats['counts']['IN_PROGRESS'], 0) ?>,
                    <?= max($stats['completed_total'], 0) ?>
                ],
                backgroundColor: ['#991b1b', '#b91c1c', '#e11d48', '#64748b'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            cutout: '70%'
        }
    });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>