<?php
// api.php - API endpoint สำหรับดึงและบันทึกข้อมูลบุคลากรใน Google Sheets

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// ยอมรับการร้องขอแบบ Preflight (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $service = getSheetsService();
    $sheetName = getFirstSheetName($service, SPREADSHEET_ID);
    
    // ดึงข้อมูลชีตทั้งหมดเพื่อหา Sheet ID สำหรับการลบแถว
    $spreadsheet = $service->spreadsheets->get(SPREADSHEET_ID);
    $sheets = $spreadsheet->getSheets();
    $sheetId = 0; // ค่าเริ่มต้น
    foreach ($sheets as $s) {
        if ($s->getProperties()->getTitle() === $sheetName) {
            $sheetId = $s->getProperties()->getSheetId();
            break;
        }
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    // ตรวจสอบ Action (กรณีส่งมาทาง POST/GET/DELETE)
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    if ($method === 'GET') {
        // --- READ: ดึงข้อมูลทั้งหมด ---
        // ดึง A1 ถึง H1000
        $range = "{$sheetName}!A1:H1000";
        $response = $service->spreadsheets_values->get(SPREADSHEET_ID, $range);
        $rows = $response->getValues();

        // โครงสร้างของหัวตารางที่ต้องการ
        $headers = ['ID', 'Name', 'Position', 'Department', 'Email', 'Phone', 'Status', 'JoinedDate'];

        // กรณีไม่มีข้อมูล หรือไม่มีหัวตารางเลย
        if (empty($rows)) {
            // เขียนหัวตารางให้ก่อนเลย
            $valueRange = new \Google\Service\Sheets\ValueRange([
                'values' => [$headers]
            ]);
            $service->spreadsheets_values->update(
                SPREADSHEET_ID,
                "{$sheetName}!A1",
                $valueRange,
                ['valueInputOption' => 'USER_ENTERED']
            );
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }

        // ตรวจสอบและเคลียร์ค่าเริ่มต้นของบอตที่เป็นข้อมูลขยะ (ถ้ามี)
        // ถ้าแถวที่ 1 คอลัมน์ที่ 2 ไม่ใช่ Name หรือไม่ตรงกับที่กำหนด ให้ล้างตารางแล้วสร้างใหม่
        if (count($rows) > 0 && $rows[0][0] !== 'ID' && strpos($rows[0][0], '/') !== false) {
            // เคลียร์และเขียนหัวตารางใหม่
            $clearRequest = new \Google\Service\Sheets\ClearValuesRequest();
            $service->spreadsheets_values->clear(SPREADSHEET_ID, "{$sheetName}!A1:Z1000", $clearRequest);
            
            $valueRange = new \Google\Service\Sheets\ValueRange([
                'values' => [$headers]
            ]);
            $service->spreadsheets_values->update(
                SPREADSHEET_ID,
                "{$sheetName}!A1",
                $valueRange,
                ['valueInputOption' => 'USER_ENTERED']
            );
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }

        // แปลงแถวข้อมูลดิบเป็น JSON Objects
        $personnelList = [];
        $headerRow = $rows[0];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // ข้ามแถวที่ไม่มี ID หรือข้อมูลว่างเปล่าจริงๆ
            if (empty($row) || !isset($row[0]) || trim($row[0]) === '') {
                continue;
            }

            // แมพข้อมูลตามคอลัมน์ (เผื่อสคริปต์ได้ค่ามาไม่ครบความกว้างของคอลัมน์)
            $personnelList[] = [
                'rowIndex'   => $i + 1, // เก็บหมายเลขแถวจริง (1-based index) ใน Sheets สำหรับใช้ Edit/Delete
                'id'         => isset($row[0]) ? $row[0] : '',
                'name'       => isset($row[1]) ? $row[1] : '',
                'position'   => isset($row[2]) ? $row[2] : '',
                'department' => isset($row[3]) ? $row[3] : '',
                'email'      => isset($row[4]) ? $row[4] : '',
                'phone'      => isset($row[5]) ? $row[5] : '',
                'status'     => isset($row[6]) ? $row[6] : 'ปฏิบัติงาน',
                'joinedDate' => isset($row[7]) ? $row[7] : '',
            ];
        }

        echo json_encode(['success' => true, 'data' => $personnelList]);
        exit;

    } elseif ($method === 'POST') {
        
        if ($action === 'create') {
            // --- CREATE: เพิ่มข้อมูลบุคลากรใหม่ ---
            $id = isset($input['id']) ? trim($input['id']) : '';
            $name = isset($input['name']) ? trim($input['name']) : '';
            $position = isset($input['position']) ? trim($input['position']) : '';
            $department = isset($input['department']) ? trim($input['department']) : '';
            $email = isset($input['email']) ? trim($input['email']) : '';
            $phone = isset($input['phone']) ? trim($input['phone']) : '';
            $status = isset($input['status']) ? trim($input['status']) : 'ปฏิบัติงาน';
            $joinedDate = isset($input['joinedDate']) ? trim($input['joinedDate']) : date('Y-m-d');

            if (empty($id) || empty($name)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ข้อมูลรหัสและชื่อ-นามสกุลจำเป็นต้องกรอก']);
                exit;
            }

            // ตรวจสอบรหัสบุคลากรซ้ำในชีตก่อน
            $checkRange = "{$sheetName}!A1:A1000";
            $checkResponse = $service->spreadsheets_values->get(SPREADSHEET_ID, $checkRange);
            $checkRows = $checkResponse->getValues();
            if (!empty($checkRows)) {
                for ($i = 1; $i < count($checkRows); $i++) {
                    if (isset($checkRows[$i][0]) && trim($checkRows[$i][0]) === $id) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => "รหัสบุคลากร '{$id}' นี้มีอยู่แล้วในระบบ"]);
                        exit;
                    }
                }
            }

            $values = [[$id, $name, $position, $department, $email, $phone, $status, $joinedDate]];
            $valueRange = new \Google\Service\Sheets\ValueRange([
                'values' => $values
            ]);

            $appendResponse = $service->spreadsheets_values->append(
                SPREADSHEET_ID,
                "{$sheetName}!A1",
                $valueRange,
                [
                    'valueInputOption' => 'USER_ENTERED',
                    'insertDataOption' => 'INSERT_ROWS'
                ]
            );

            echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลบุคลากรเรียบร้อยแล้ว']);
            exit;

        } elseif ($action === 'update') {
            // --- UPDATE: อัปเดตข้อมูลเดิม ---
            $rowIndex = isset($input['rowIndex']) ? (int)$input['rowIndex'] : 0;
            $id = isset($input['id']) ? trim($input['id']) : '';
            $name = isset($input['name']) ? trim($input['name']) : '';
            $position = isset($input['position']) ? trim($input['position']) : '';
            $department = isset($input['department']) ? trim($input['department']) : '';
            $email = isset($input['email']) ? trim($input['email']) : '';
            $phone = isset($input['phone']) ? trim($input['phone']) : '';
            $status = isset($input['status']) ? trim($input['status']) : 'ปฏิบัติงาน';
            $joinedDate = isset($input['joinedDate']) ? trim($input['joinedDate']) : '';

            if ($rowIndex < 2) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ไม่พบแถวที่ต้องการแก้ไข']);
                exit;
            }

            if (empty($id) || empty($name)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ข้อมูลรหัสและชื่อ-นามสกุลจำเป็นต้องกรอก']);
                exit;
            }

            // ตรวจสอบรหัสบุคลากรซ้ำ (ยกเว้นแถวตัวเอง)
            $checkRange = "{$sheetName}!A1:A1000";
            $checkResponse = $service->spreadsheets_values->get(SPREADSHEET_ID, $checkRange);
            $checkRows = $checkResponse->getValues();
            if (!empty($checkRows)) {
                for ($i = 1; $i < count($checkRows); $i++) {
                    $currentRowIndex = $i + 1;
                    if ($currentRowIndex !== $rowIndex && isset($checkRows[$i][0]) && trim($checkRows[$i][0]) === $id) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => "รหัสบุคลากร '{$id}' นี้ซ้ำกับบุคคลอื่น"]);
                        exit;
                    }
                }
            }

            $updateRange = "{$sheetName}!A{$rowIndex}:H{$rowIndex}";
            $values = [[$id, $name, $position, $department, $email, $phone, $status, $joinedDate]];
            
            $valueRange = new \Google\Service\Sheets\ValueRange([
                'values' => $values
            ]);

            $service->spreadsheets_values->update(
                SPREADSHEET_ID,
                $updateRange,
                $valueRange,
                ['valueInputOption' => 'USER_ENTERED']
            );

            echo json_encode(['success' => true, 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว']);
            exit;

        } elseif ($action === 'delete') {
            // --- DELETE: ลบแถวข้อมูลออก ---
            $rowIndex = isset($input['rowIndex']) ? (int)$input['rowIndex'] : 0;

            if ($rowIndex < 2) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ไม่พบแถวที่ต้องการลบ']);
                exit;
            }

            // ใช้ batchUpdate เพื่อทำการลบแถวทิ้งจริงๆ (และขยับแถวด้านล่างขึ้นมา)
            $batchUpdateRequest = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                'requests' => [
                    'deleteDimension' => [
                        'range' => [
                            'sheetId' => $sheetId,
                            'dimension' => 'ROWS',
                            'startIndex' => $rowIndex - 1, // 0-based index
                            'endIndex' => $rowIndex // exclusive
                        ]
                    ]
                ]
            ]);

            $service->spreadsheets->batchUpdate(SPREADSHEET_ID, $batchUpdateRequest);

            echo json_encode(['success' => true, 'message' => 'ลบข้อมูลบุคลากรเรียบร้อยแล้ว']);
            exit;
        }

        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ไม่มี Action ที่ร้องขอ']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'ไม่อนุญาตให้ใช้ HTTP Method นี้']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'เกิดข้อผิดพลาดภายในระบบ: ' . $e->getMessage()
    ]);
}
