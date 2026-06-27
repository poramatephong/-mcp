// app.js - ควบคุมการทำงานฝั่งไคลเอนต์และเชื่อมต่อ API

let personnelData = []; // เก็บอาร์เรย์รายชื่อบุคลากรทั้งหมด

// อ้างอิง DOM Elements
const themeToggle = document.getElementById('theme-toggle');
const personnelTableBody = document.getElementById('personnel-data');
const searchInput = document.getElementById('search-input');
const filterDept = document.getElementById('filter-dept');
const filterStatus = document.getElementById('filter-status');
const btnAddModal = document.getElementById('btn-add-modal');
const btnCloseModal = document.getElementById('btn-close-modal');
const btnCancel = document.getElementById('btn-cancel');
const personnelModal = document.getElementById('personnel-modal');
const personnelForm = document.getElementById('personnel-form');
const modalTitle = document.getElementById('modal-title');
const toastBox = document.getElementById('toast-box');

// Stats Counters
const statTotal = document.getElementById('stat-total');
const statActive = document.getElementById('stat-active');
const statLeave = document.getElementById('stat-leave');
const statOther = document.getElementById('stat-other');

// 1. ระบบจัดการ Theme (สว่าง/มืด)
function initTheme() {
  const currentTheme = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', currentTheme);
  themeToggle.checked = (currentTheme === 'light');
}

themeToggle.addEventListener('change', (e) => {
  const newTheme = e.target.checked ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', newTheme);
  localStorage.setItem('theme', newTheme);
});

// 2. แสดง Toast Notification
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `toast toast-${type} glass`;
  
  // สร้าง Icon ตามประเภท
  const icon = type === 'success' ? '✅' : '❌';
  toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
  
  toastBox.appendChild(toast);
  
  // Slide in
  setTimeout(() => toast.classList.add('active'), 50);
  
  // Remove after 3.5 seconds
  setTimeout(() => {
    toast.classList.remove('active');
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

// 3. ฟังก์ชันดึงข้อมูลจาก API
async function loadPersonnel() {
  // แสดง Loading state
  personnelTableBody.innerHTML = `
    <tr>
      <td colspan="8" class="loading-row">
        <div class="spinner"></div>
        กำลังโหลดข้อมูลจาก Google Sheets...
      </td>
    </tr>
  `;

  try {
    const response = await fetch('api.php');
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const result = await response.json();
    
    if (result.success) {
      personnelData = result.data;
      updateDashboardStats();
      populateDeptFilter();
      renderTable(personnelData);
    } else {
      showToast(result.message || 'โหลดข้อมูลล้มเหลว', 'error');
    }
  } catch (error) {
    console.error('Error fetching data:', error);
    showToast('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์หลังบ้านได้', 'error');
    personnelTableBody.innerHTML = `
      <tr>
        <td colspan="8" class="empty-row" style="color: var(--color-move);">
          ⚠️ การเชื่อมต่อล้มเหลว: ${error.message}
        </td>
      </tr>
    `;
  }
}

// 4. อัปเดตสถิติ Dashboard
function updateDashboardStats() {
  const total = personnelData.length;
  const active = personnelData.filter(p => p.status === 'ปฏิบัติงาน').length;
  const leave = personnelData.filter(p => p.status === 'ลาพัก').length;
  const other = personnelData.filter(p => p.status === 'ย้าย' || p.status === 'ลาออก').length;

  // ค่อยๆ รันตัวเลข (Counter effect)
  animateCounter(statTotal, total);
  animateCounter(statActive, active);
  animateCounter(statLeave, leave);
  animateCounter(statOther, other);
}

function animateCounter(element, target) {
  let current = parseInt(element.textContent) || 0;
  if (current === target) return;
  const step = target > current ? 1 : -1;
  const timer = setInterval(() => {
    current += step;
    element.textContent = current;
    if (current === target) clearInterval(timer);
  }, 30);
}

// 5. สร้างรายการกลุ่มสาระในตัวกรองแผนกอัตโนมัติ
function populateDeptFilter() {
  const depts = [...new Set(personnelData.map(p => p.department).filter(Boolean))];
  const currentVal = filterDept.value;
  
  filterDept.innerHTML = '<option value="">กลุ่มสาระ / ฝ่ายทั้งหมด</option>';
  depts.sort().forEach(dept => {
    const option = document.createElement('option');
    option.value = dept;
    option.textContent = dept;
    filterDept.appendChild(option);
  });
  
  // คืนค่าที่เลือกไว้ก่อนหน้าถ้ามี
  filterDept.value = currentVal;
}

// 6. ฟังก์ชันเรนเดอร์ตารางรายชื่อ
function renderTable(data) {
  if (data.length === 0) {
    personnelTableBody.innerHTML = `
      <tr>
        <td colspan="8" class="empty-row">🔍 ไม่พบข้อมูลบุคลากรในระบบ</td>
      </tr>
    `;
    return;
  }

  personnelTableBody.innerHTML = '';
  
  data.forEach(p => {
    const tr = document.createElement('tr');
    
    // ตั้งค่าคลาสสีของ Badge
    let badgeClass = 'badge-resign';
    if (p.status === 'ปฏิบัติงาน') badgeClass = 'badge-active';
    else if (p.status === 'ลาพัก') badgeClass = 'badge-leave';
    else if (p.status === 'ย้าย') badgeClass = 'badge-move';

    tr.innerHTML = `
      <td style="font-weight: 600;">${escapeHTML(p.id)}</td>
      <td style="font-weight: 500;">${escapeHTML(p.name)}</td>
      <td>${escapeHTML(p.position || '-')}</td>
      <td>${escapeHTML(p.department || '-')}</td>
      <td>${escapeHTML(p.email || '-')}</td>
      <td>${escapeHTML(p.phone || '-')}</td>
      <td>
        <span class="badge ${badgeClass}">
          <span class="badge-dot"></span>
          ${escapeHTML(p.status)}
        </span>
      </td>
      <td>
        <div class="action-buttons">
          <button class="btn-icon btn-edit" data-row="${p.rowIndex}" title="แก้ไขข้อมูล">
            <!-- Edit Icon -->
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
            </svg>
          </button>
          <button class="btn-icon btn-icon-delete btn-delete" data-row="${p.rowIndex}" data-name="${p.name}" title="ลบข้อมูล">
            <!-- Delete Icon -->
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
            </svg>
          </button>
        </div>
      </td>
    `;
    personnelTableBody.appendChild(tr);
  });

  // ผูกปุ่มการจัดการ
  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => openEditModal(parseInt(btn.getAttribute('data-row'))));
  });

  document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', () => deletePersonnel(parseInt(btn.getAttribute('data-row')), btn.getAttribute('data-name')));
  });
}

// 7. จัดการค้นหาและฟิลเตอร์สด (Client-side Filtering เพื่อความเร็ว)
function handleSearchAndFilter() {
  const query = searchInput.value.toLowerCase().trim();
  const deptValue = filterDept.value;
  const statusValue = filterStatus.value;

  const filtered = personnelData.filter(p => {
    const matchesSearch = 
      p.id.toLowerCase().includes(query) ||
      p.name.toLowerCase().includes(query) ||
      (p.position || '').toLowerCase().includes(query) ||
      (p.phone || '').includes(query);
      
    const matchesDept = !deptValue || p.department === deptValue;
    const matchesStatus = !statusValue || p.status === statusValue;

    return matchesSearch && matchesDept && matchesStatus;
  });

  renderTable(filtered);
}

searchInput.addEventListener('input', handleSearchAndFilter);
filterDept.addEventListener('change', handleSearchAndFilter);
filterStatus.addEventListener('change', handleSearchAndFilter);

// 8. จัดการหน้าต่าง Modal (เพิ่ม / แก้ไข)
function openAddModal() {
  modalTitle.textContent = 'เพิ่มข้อมูลบุคลากร';
  personnelForm.reset();
  document.getElementById('form-row-index').value = '';
  document.getElementById('form-id').disabled = false; // ปลดล็อกฟิลด์ ID ตอนสร้างใหม่
  // ตั้งค่าวันที่ปัจจุบันใน Joined Date เริ่มต้น
  document.getElementById('form-joined').value = new Date().toISOString().substring(0, 10);
  
  personnelModal.classList.add('active');
}

function openEditModal(rowIndex) {
  const person = personnelData.find(p => p.rowIndex === rowIndex);
  if (!person) return;

  modalTitle.textContent = 'แก้ไขข้อมูลบุคลากร';
  document.getElementById('form-row-index').value = rowIndex;
  document.getElementById('form-id').value = person.id;
  document.getElementById('form-id').disabled = true; // ล็อก ID ไม่ให้แก้ไข
  document.getElementById('form-name').value = person.name;
  document.getElementById('form-position').value = person.position;
  document.getElementById('form-dept').value = person.department;
  document.getElementById('form-email').value = person.email;
  document.getElementById('form-phone').value = person.phone;
  document.getElementById('form-status').value = person.status;
  document.getElementById('form-joined').value = person.joinedDate;

  personnelModal.classList.add('active');
}

function closeModal() {
  personnelModal.classList.remove('active');
}

btnAddModal.addEventListener('click', openAddModal);
btnCloseModal.addEventListener('click', closeModal);
btnCancel.addEventListener('click', closeModal);

// ปิด Modal เมื่อคลิกด้านนอกกล่อง
personnelModal.addEventListener('click', (e) => {
  if (e.target === personnelModal) closeModal();
});

// 9. จัดการส่งแบบฟอร์ม (Form Submission)
personnelForm.addEventListener('submit', async (e) => {
  e.preventDefault();

  const rowIndex = document.getElementById('form-row-index').value;
  const isEdit = rowIndex !== '';
  const action = isEdit ? 'update' : 'create';

  const formData = {
    id: document.getElementById('form-id').value.trim(),
    name: document.getElementById('form-name').value.trim(),
    position: document.getElementById('form-position').value.trim(),
    department: document.getElementById('form-dept').value.trim(),
    email: document.getElementById('form-email').value.trim(),
    phone: document.getElementById('form-phone').value.trim(),
    status: document.getElementById('form-status').value,
    joinedDate: document.getElementById('form-joined').value
  };

  if (isEdit) {
    formData.rowIndex = parseInt(rowIndex);
  }

  // ปิดปุ่มส่งฟอร์มเพื่อป้องกันการส่งซ้ำ
  const btnSubmit = document.getElementById('btn-submit');
  btnSubmit.disabled = true;
  btnSubmit.textContent = 'กำลังบันทึก...';

  try {
    const response = await fetch(`api.php?action=${action}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    });

    const result = await response.json();
    
    if (result.success) {
      showToast(result.message || 'บันทึกสำเร็จ');
      closeModal();
      loadPersonnel(); // รีโหลดข้อมูลตารางใหม่
    } else {
      showToast(result.message || 'ไม่สามารถบันทึกข้อมูลได้', 'error');
    }
  } catch (error) {
    console.error('Error saving:', error);
    showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
  } finally {
    btnSubmit.disabled = false;
    btnSubmit.textContent = 'บันทึกข้อมูล';
  }
});

// 10. ฟังก์ชันการลบข้อมูล (Delete)
async function deletePersonnel(rowIndex, name) {
  if (!confirm(`คุณแน่ใจหรือไม่ว่าต้องการลบรายชื่อ "${name}" ออกจากระบบฐานข้อมูล?`)) {
    return;
  }

  try {
    const response = await fetch('api.php?action=delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ rowIndex })
    });

    const result = await response.json();

    if (result.success) {
      showToast('ลบข้อมูลบุคลากรเรียบร้อยแล้ว', 'success');
      loadPersonnel(); // รีโหลดข้อมูลใหม่
    } else {
      showToast(result.message || 'ลบข้อมูลล้มเหลว', 'error');
    }
  } catch (error) {
    console.error('Error deleting:', error);
    showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
  }
}

// ฟังก์ชันช่วยหลีกเลี่ยงการโจมตี XSS
function escapeHTML(str) {
  if (!str) return '';
  return str.replace(/[&<>'"]/g, 
    tag => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      "'": '&#39;',
      '"': '&quot;'
    }[tag] || tag)
  );
}

// 11. เริ่มโหลดระบบเมื่อเปิดหน้าเว็บ
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  loadPersonnel();
});
