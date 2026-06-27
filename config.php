<?php
// config.php - การตั้งค่าและการยึนยันตัวตนกับ Google API

require_once __DIR__ . '/vendor/autoload.php';

// ตั้งค่า Header ให้เป็น JSON และเปิดการเข้าถึง CORS (กรณีทดสอบข้าม Domain)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// 1. ระบุ Spreadsheet ID และค่าสิทธิ์การเข้าถึง
define('SPREADSHEET_ID', '1fB71rPysVt5JOtuRgKgM5Or1paKRcI5ayeXHdWKBURo');
define('KEY_FILE_PATH', __DIR__ . '/service-account.json');

// 2. ฟังก์ชันสำหรับรับอินสแตนซ์ของ Google Sheets Service
function getSheetsService() {
    if (!file_exists(KEY_FILE_PATH)) {
        throw new Exception("ไม่พบไฟล์ยืนยันตัวตน service-account.json กรุณาตรวจสอบตำแหน่งไฟล์");
    }

    $client = new \Google\Client();
    $client->setAuthConfig(KEY_FILE_PATH);
    $client->addScope(\Google\Service\Sheets::SPREADSHEETS);
    
    // ตั้งชื่อแอปพลิเคชัน
    $client->setApplicationName("SchoolPersonnelDatabase");

    return new \Google\Service\Sheets($client);
}

// 3. ฟังก์ชันสำหรับหาชื่อชีตแรกโดยอัตโนมัติ
function getFirstSheetName($service, $spreadsheetId) {
    try {
        $spreadsheet = $service->spreadsheets->get($spreadsheetId);
        $sheets = $spreadsheet->getSheets();
        if (empty($sheets)) {
            throw new Exception("ไม่พบชีตใดๆ ใน Spreadsheet");
        }
        return $sheets[0]->getProperties()->getTitle();
    } catch (Exception $e) {
        // หากดึงค่าล้มเหลว ให้ใช้ค่าเริ่มต้นเป็น 'ชีต1'
        return 'ชีต1';
    }
}
