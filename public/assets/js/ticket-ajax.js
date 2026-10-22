// public/assets/js/ticket-ajax.js
document.addEventListener("DOMContentLoaded", () => {
  const statusForm = document.getElementById("quick-status-form");
  if (!statusForm) return;

  statusForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const ticketId = statusForm.dataset.ticketId;
    const newStatus = document.getElementById("select-status").value;
    const comment = document.getElementById("status-comment").value;
    const photoInput = document.getElementById("repair-photo");
    const submitBtn = statusForm.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.innerText = "กำลังบันทึกและอัปโหลด...";

    try {
      // ใช้ FormData เพื่อรองรับการส่งรูปภาพผลงาน (Attach Repair Photo)
      const formData = new FormData();
      formData.append("status", newStatus);
      formData.append("comment", comment);
      if (photoInput && photoInput.files[0]) {
        formData.append("repair_photo", photoInput.files[0]);
      }

      const response = await fetch(`/api/tickets/${ticketId}/status`, {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        // 1. อัปเดต Status Badge ทันที
        const badge = document.getElementById("ticket-status-badge");
        if (badge) {
          badge.innerText = result.status;
          badge.className = `px-3 py-1 text-sm font-semibold rounded-full ${getStatusBadgeClass(result.status)}`;
        }

        // 2. ถ้ามีรูปภาพผลงาน ให้แสดงกล่องภาพทันที
        if (result.repair_image) {
          const box = document.getElementById("repair-result-box");
          const img = document.getElementById("repair-result-img");
          if (box && img) {
            img.src = "/storage/uploads/ticket-images/" + result.repair_image;
            box.classList.remove("hidden");
          }
        }

        // 3. Append Comment เข้าไปใน Timeline
        if (result.comment) {
          const commentList = document.getElementById("comments-container");
          const emptyNote = document.getElementById("no-comments");
          if (emptyNote) emptyNote.remove();

          const newCommentHtml = `
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-lg animate-fade-in">
                            <div class="flex justify-between text-xs text-slate-500 mb-1">
                                <span class="font-semibold text-slate-700">${result.comment.user} (${result.comment.role})</span>
                                <span>${result.comment.time}</span>
                            </div>
                            <p class="text-sm text-slate-800">${result.comment.text}</p>
                        </div>
                    `;
          commentList.insertAdjacentHTML("beforeend", newCommentHtml);
        }

        // รีเซ็ตฟอร์ม
        document.getElementById("status-comment").value = "";
        if (photoInput) photoInput.value = "";
        showToast("อัปเดตสถานะและบันทึกผลงานสำเร็จ!");
      } else {
        alert("เกิดข้อผิดพลาด: " + (result.message || "ไม่สามารถอัปเดตได้"));
      }
    } catch (error) {
      console.error("Error updating status:", error);
      alert("ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้");
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerText = "บันทึกการเปลี่ยนแปลง";
    }
  });
});

function getStatusBadgeClass(status) {
  switch (status) {
    case "OPEN":
      return "bg-red-50 text-red-800 border border-red-200";
    case "ASSIGNED":
      return "bg-amber-50 text-amber-800 border border-amber-200";
    case "IN_PROGRESS":
      return "bg-red-100 text-red-900 border border-red-300 font-bold";
    case "RESOLVED":
      return "bg-slate-100 text-slate-800 border border-slate-300";
    case "CLOSED":
      return "bg-slate-100 text-slate-700 border border-slate-200";
    default:
      return "bg-slate-100 text-slate-600 border border-slate-200";
  }
}

function showToast(msg) {
  const toast = document.createElement("div");
  toast.className =
    "fixed bottom-4 right-4 bg-red-950 text-white px-5 py-3 rounded-2xl shadow-2xl text-xs z-50 border border-red-800 font-semibold";
  toast.innerText = msg;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}
