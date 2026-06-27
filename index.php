<!DOCTYPE html>
<html lang="th" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ระบบจัดการฐานข้อมูลบุคลากรในสถานศึกษา เชื่อมต่อ Google Sheets เป็นฐานข้อมูลแบบ Realtime">
    <title>ระบบฐานข้อมูลบุคลากรในสถานศึกษา (Google Sheets DB)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header -->
    <header class="glass">
        <div class="header-logo">
            <!-- Icon School -->
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                <path d="M21.42 11.23L12 16.37l-9.42-5.14L1 12.3l11 6 11-6z"/>
                <path d="M22 17h-2v3h-3v2h5v-5z"/>
            </svg>
            <h1>ระบบฐานข้อมูลบุคลากร</h1>
        </div>
        <div class="header-actions">
            <!-- Theme Toggle -->
            <span style="font-size: 0.85rem; color: var(--text-secondary);">โหมดสว่าง/มืด</span>
            <label class="theme-switch">
                <input type="checkbox" id="theme-toggle">
                <span class="slider">
                    <!-- Sun Icon -->
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <!-- Moon Icon -->
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </span>
            </label>
        </div>
    </header>

    <div class="container">
        
        <!-- Dashboard Stats -->
        <section class="stats-grid">
            <!-- Card 1 -->
            <div class="stat-card glass card-total">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-info">
                    <h3>บุคลากรทั้งหมด</h3>
                    <p id="stat-total">0</p>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="stat-card glass card-active">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <polyline points="17 11 19 13 23 9"></polyline>
                    </svg>
                </div>
                <div class="stat-info">
                    <h3>ปฏิบัติงานปกติ</h3>
                    <p id="stat-active">0</p>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="stat-card glass card-leave">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="stat-info">
                    <h3>ลาพัก / ลาศึกษาต่อ</h3>
                    <p id="stat-leave">0</p>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="stat-card glass card-other">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 16v1a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v1"></path>
                        <polyline points="18 8 22 12 18 16"></polyline>
                        <line x1="6" y1="12" x2="22" y2="12"></line>
                    </svg>
                </div>
                <div class="stat-info">
                    <h3>ย้าย / ลาออก</h3>
                    <p id="stat-other">0</p>
                </div>
            </div>
        </section>

        <!-- Controls (Search & Filter) -->
        <section class="controls-card glass">
            <div class="search-box">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                <input type="text" id="search-input" placeholder="ค้นหาด้วยรหัส, ชื่อ, ตำแหน่ง, เบอร์โทร..." autocomplete="off">
            </div>
            
            <div class="filter-group">
                <select id="filter-dept" class="filter-select">
                    <option value="">กลุ่มสาระ / ฝ่ายทั้งหมด</option>
                    <!-- โหลดแบบ Dynamic -->
                </select>
                
                <select id="filter-status" class="filter-select">
                    <option value="">สถานะทั้งหมด</option>
                    <option value="ปฏิบัติงาน">ปฏิบัติงาน</option>
                    <option value="ลาพัก">ลาพัก</option>
                    <option value="ย้าย">ย้าย</option>
                    <option value="ลาออก">ลาออก</option>
                </select>

                <button class="btn-primary" id="btn-add-modal">
                    <!-- Plus Icon -->
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                    </svg>
                    เพิ่มบุคลากร
                </button>
            </div>
        </section>

        <!-- Personnel List Table -->
        <section class="table-card glass">
            <div class="table-responsive">
                <table id="personnel-table">
                    <thead>
                        <tr>
                            <th>รหัส</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>ตำแหน่ง</th>
                            <th>กลุ่มสาระ/ฝ่าย</th>
                            <th>อีเมล</th>
                            <th>เบอร์โทร</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="personnel-data">
                        <!-- รอโหลดข้อมูลด้วย JS -->
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <!-- Modal Form (ใช้ร่วมกันทั้ง Add และ Edit) -->
    <div class="modal-overlay" id="personnel-modal">
        <div class="modal-box">
            <div class="modal-header">
                <h2 id="modal-title">เพิ่มข้อมูลบุคลากร</h2>
                <button class="modal-close" id="btn-close-modal">&times;</button>
            </div>
            <form id="personnel-form">
                <input type="hidden" id="form-row-index"> <!-- ใช้กรณีแก้ไขเท่านั้น -->
                <div class="modal-body">
                    <div class="form-grid">
                        
                        <div class="form-group">
                            <label for="form-id">รหัสบุคลากร <span style="color: red;">*</span></label>
                            <input type="text" id="form-id" required placeholder="เช่น SCH-001">
                        </div>
                        
                        <div class="form-group">
                            <label for="form-name">ชื่อ-นามสกุล <span style="color: red;">*</span></label>
                            <input type="text" id="form-name" required placeholder="เช่น นายวิทยา รักดี">
                        </div>

                        <div class="form-group">
                            <label for="form-position">ตำแหน่ง</label>
                            <input type="text" id="form-position" placeholder="เช่น ครู, เจ้าหน้าที่ธุรการ" list="position-suggestions">
                            <datalist id="position-suggestions">
                                <option value="ผู้อำนวยการโรงเรียน">
                                <option value="รองผู้อำนวยการโรงเรียน">
                                <option value="ครูชำนาญการพิเศษ">
                                <option value="ครูชำนาญการ">
                                <option value="ครูผู้ช่วย">
                                <option value="ครูอัตราจ้าง">
                                <option value="เจ้าหน้าที่ธุรการ">
                                <option value="นักการภารโรง">
                            </datalist>
                        </div>

                        <div class="form-group">
                            <label for="form-dept">กลุ่มสาระ / ฝ่ายงาน</label>
                            <input type="text" id="form-dept" placeholder="เช่น คณิตศาสตร์, บริหารงานบุคคล" list="dept-suggestions">
                            <datalist id="dept-suggestions">
                                <option value="กลุ่มสาระฯคณิตศาสตร์">
                                <option value="กลุ่มสาระฯวิทยาศาสตร์และเทคโนโลยี">
                                <option value="กลุ่มสาระฯภาษาไทย">
                                <option value="กลุ่มสาระฯภาษาต่างประเทศ">
                                <option value="กลุ่มสาระฯสังคมศึกษาฯ">
                                <option value="กลุ่มสาระฯสุขศึกษาและพลศึกษา">
                                <option value="กลุ่มสาระฯศิลปะ">
                                <option value="กลุ่มสาระฯการงานอาชีพ">
                                <option value="ฝ่ายบริหารงานวิชาการ">
                                <option value="ฝ่ายบริหารงานบุคคล">
                                <option value="ฝ่ายบริหารทั่วไป">
                            </datalist>
                        </div>

                        <div class="form-group">
                            <label for="form-email">อีเมล</label>
                            <input type="email" id="form-email" placeholder="example@school.ac.th">
                        </div>

                        <div class="form-group">
                            <label for="form-phone">เบอร์โทรศัพท์</label>
                            <input type="tel" id="form-phone" placeholder="089-xxxxxxx">
                        </div>

                        <div class="form-group">
                            <label for="form-status">สถานะ</label>
                            <select id="form-status">
                                <option value="ปฏิบัติงาน">ปฏิบัติงาน</option>
                                <option value="ลาพัก">ลาพัก</option>
                                <option value="ย้าย">ย้าย</option>
                                <option value="ลาออก">ลาออก</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="form-joined">วันที่เริ่มงาน</label>
                            <input type="date" id="form-joined">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btn-cancel">ยกเลิก</button>
                    <button type="submit" class="btn-primary" id="btn-submit">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container" id="toast-box"></div>

    <script src="app.js"></script>
</body>
</html>
