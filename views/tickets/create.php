<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow-md border border-slate-200">
    <h2 class="text-xl font-bold text-slate-900 mb-2">แบบฟอร์มแจ้งซ่อมอุปกรณ์ / ระบบ</h2>
    <p class="text-xs text-red-800/80 font-medium mb-6">กรอกข้อมูลให้ครบถ้วนเพื่อความรวดเร็วในการส่งช่างเข้าตรวจสอบ</p>

    <form action="/tickets/create" method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">หัวข้อปัญหา <span
                    class="text-red-700">*</span></label>
            <input type="text" name="title" required placeholder="เช่น หน้าจอคอมพิวเตอร์เปิดไม่ติด"
                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-red-800 focus:border-red-800 outline-none transition">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">หมวดหมู่</label>
                <select name="category"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-red-800 focus:border-red-800 outline-none transition">
                    <option value="Hardware">Hardware / อุปกรณ์ชำรุด</option>
                    <option value="Network">Network / อินเทอร์เน็ต</option>
                    <option value="Printer">Printer / สแกนเนอร์</option>
                    <option value="Software">Software / โปรแกรมใช้งาน</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">ระดับความเร่งด่วน</label>
                <select name="priority"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-red-800 focus:border-red-800 outline-none transition">
                    <option value="Low">ต่ำ (Low)</option>
                    <option value="Medium" selected>ปานกลาง (Medium)</option>
                    <option value="High">สูง (High)</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">แนบรูปภาพหน้างาน (Activity Diagram: MIME
                Validation)</label>
            <input type="file" name="repair_image" accept="image/*"
                class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-800 hover:file:bg-red-100 cursor-pointer">
        </div>
        <button type="submit"
            class="w-full py-2.5 bg-red-800 hover:bg-red-900 active:bg-red-950 text-white font-semibold rounded-xl text-xs shadow-md transition">
            ส่งข้อมูลแจ้งซ่อม
        </button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>