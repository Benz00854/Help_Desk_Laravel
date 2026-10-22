</main>
<footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-400">
    ระบบบริหารจัดการงานซ่อมบำรุง (Helpdesk Notification System) &copy; <?= date('Y') ?>
</footer>
</div>
    
    <!-- สคริปต์เดินเวลานาฬิกา -->
    <script>
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const el = document.getElementById('live-time-pill');
        if (el) el.innerText = `${hours}:${minutes} น.`;
    }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    <script src="/assets/js/ticket-ajax.js"></script>
</body>
    
</html>