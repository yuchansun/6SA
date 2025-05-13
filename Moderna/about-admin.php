<?php
session_start();
require_once 'db.php';

// 檢查是否為管理者
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== '管理者') {
    header('Location: index.php');
    exit;
}

// 獲取校系編號
$sch_num = $_GET['sch_num'] ?? '';

if (empty($sch_num)) {
    die("查無學校資訊");
}

// 獲取校系資料
$sql = "SELECT sd.*, aty.110, aty.111, aty.112, aty.113, aty.114 
        FROM sch_description sd 
        LEFT JOIN admi_thro_years aty ON sd.Sch_num = aty.sch_num 
        WHERE sd.Sch_num = ? AND sd.is_deleted = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $sch_num);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("查無學校資訊或該校系已被刪除");
}

// 處理 AJAX 請求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        $response = ['success' => false, 'message' => ''];
        
        try {
            switch ($_POST['action']) {
                case 'delete':
                    $sql = "UPDATE sch_description SET is_deleted = 1 WHERE Sch_num = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $sch_num);
                    $stmt->execute();
                    $response['success'] = true;
                    $response['message'] = '校系資料已刪除';
                    break;
                    
                case 'update_basic':
                    // 檢查必填欄位
                    $requiredFields = [
                        'School_Name' => '學校名稱',
                        'Department' => '科系名稱',
                        'Region' => '地區',
                        'address' => '地址',
                        'Disc_Cluster' => '學群',
                        'Quota' => '招生名額',
                        'exam_date' => '考試日期',
                        'Contact' => '聯絡方式',
                        'link' => '簡章連結'
                    ];
                    
                    $emptyFields = [];
                    foreach ($requiredFields as $field => $fieldName) {
                        if (empty($_POST[$field])) {
                            $emptyFields[] = $fieldName;
                        }
                    }
                    
                    if (!empty($emptyFields)) {
                        $response['success'] = false;
                        $response['message'] = implode('、', $emptyFields) . '需要填寫相對應資料';
                        break;
                    }

                    $sql = "UPDATE sch_description SET 
                            School_Name = ?, 
                            Department = ?, 
                            Region = ?, 
                            address = ?, 
                            Disc_Cluster = ?, 
                            Quota = ?, 
                            exam_date = ?, 
                            Contact = ?, 
                            link = ?, 
                            note = ? 
                            WHERE Sch_num = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssissss", 
                        $_POST['School_Name'],
                        $_POST['Department'],
                        $_POST['Region'],
                        $_POST['address'],
                        $_POST['Disc_Cluster'],
                        $_POST['Quota'],
                        $_POST['exam_date'],
                        $_POST['Contact'],
                        $_POST['link'],
                        $_POST['note'],
                        $sch_num
                    );
                    $stmt->execute();
                    $response['success'] = true;
                    $response['message'] = '基本資料更新成功';
                    break;

                case 'update_requirement':
                    $sql = "UPDATE sch_description SET 
                            requirement = ?, 
                            Exam_Item = ?, 
                            Talent = ? 
                            WHERE Sch_num = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssss", 
                        $_POST['requirement'],
                        $_POST['Exam_Item'],
                        $_POST['Talent'],
                        $sch_num
                    );
                    $stmt->execute();
                    $response['success'] = true;
                    $response['message'] = '報考資訊更新成功';
                    break;

                case 'update_admission':
                    $sql = "INSERT INTO admi_thro_years (sch_num, School_Name, dep, 110, 111, 112, 113, 114) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE 
                            110 = VALUES(110), 
                            111 = VALUES(111), 
                            112 = VALUES(112), 
                            113 = VALUES(113), 
                            114 = VALUES(114)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssiiiii", 
                        $sch_num,
                        $_POST['School_Name'],
                        $_POST['Department'],
                        $_POST['110'],
                        $_POST['111'],
                        $_POST['112'],
                        $_POST['113'],
                        $_POST['114']
                    );
                    $stmt->execute();
                    $response['success'] = true;
                    $response['message'] = '錄取人數更新成功';
                    break;
            }
        } catch (Exception $e) {
            $response['message'] = '更新失敗：' . $e->getMessage();
        }
        
        echo json_encode($response);
        exit;
    }
}

// 引入 header.php
include('header.php');
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>校系資料管理</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* 導覽列背景色修正為深藍色 */
        #header,
        #header .navbar,
        #header .navbar .dropdown-menu {
            background-color: #234959 !important;
        }
        #header .navbar a,
        #header .navbar .dropdown-item {
            color: #fff !important;
        }
        #header .navbar a:hover,
        #header .navbar .dropdown-item:hover {
            color: #ffd700 !important;
            background-color: #1a3440 !important;
        }
        /* 其餘原有樣式保留 */
        #header {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 9999 !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        }
        .main {
            padding-top: 80px !important;
        }
        .page-title {
            z-index: 1 !important;
        }
        .container {
            position: relative !important;
            z-index: 1 !important;
        }
        #header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #234959;
            z-index: -1;
        }
    </style>
</head>

<body class="about-page">
    <main class="main">
        <div class="page-title dark-background">
            <div class="container position-relative">
                <h1><?= htmlspecialchars($data['School_Name']) . ' - ' . htmlspecialchars($data['Department']) ?> 資料管理</h1>
            </div>
        </div>

        <div class="container mt-4">
            <!-- 刪除按鈕 -->
            <div class="text-end mb-4">
                <button type="button" class="btn btn-danger" onclick="deleteDepartment()">刪除校系資料</button>
            </div>
            
            <!-- 基本資料區塊 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>基本資料</h3>
                </div>
                <div class="card-body">
                    <form id="basicForm" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="update_basic">
                        <input type="hidden" name="ajax" value="1">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">校系標號</label>
                                <input type="text" class="form-control" name="Sch_num" value="<?= htmlspecialchars($data['Sch_num']) ?>" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">學校名稱 *</label>
                                <input type="text" class="form-control" name="School_Name" value="<?= htmlspecialchars($data['School_Name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">科系名稱 *</label>
                                <input type="text" class="form-control" name="Department" value="<?= htmlspecialchars($data['Department']) ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">地區 *</label>
                                <input type="text" class="form-control" name="Region" value="<?= htmlspecialchars($data['Region']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">地址 *</label>
                                <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($data['address']) ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">學群 *</label>
                                <input type="text" class="form-control" name="Disc_Cluster" value="<?= htmlspecialchars($data['Disc_Cluster']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">招生名額 *</label>
                                <input type="number" class="form-control" name="Quota" value="<?= htmlspecialchars($data['Quota']) ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">考試日期 *</label>
                                <input type="date" class="form-control" name="exam_date" value="<?= htmlspecialchars($data['exam_date']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">聯絡方式 *</label>
                                <input type="text" class="form-control" name="Contact" value="<?= htmlspecialchars($data['Contact']) ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">簡章連結 *</label>
                                <input type="url" class="form-control" name="link" value="<?= htmlspecialchars($data['link']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">備註</label>
                                <textarea class="form-control" name="note"><?= htmlspecialchars($data['note']) ?></textarea>
                            </div>
                        </div>

                        <div class="text-end">
                            <small class="text-muted mb-2 d-block">* 為必填欄位</small>
                            <button type="submit" class="btn btn-primary">更新基本資料</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 報考資訊區塊 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>報考資訊</h3>
                </div>
                <div class="card-body">
                    <form id="requirementForm" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="update_requirement">
                        <input type="hidden" name="ajax" value="1">
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">報考資格</label>
                                <textarea class="form-control" name="requirement" rows="4" required><?= htmlspecialchars($data['requirement']) ?></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">考試項目</label>
                                <textarea class="form-control" name="Exam_Item" rows="4" required><?= htmlspecialchars($data['Exam_Item']) ?></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">特殊才能</label>
                                <textarea class="form-control" name="Talent" rows="4" required><?= htmlspecialchars($data['Talent']) ?></textarea>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">更新報考資訊</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 錄取人數區塊 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>錄取人數</h3>
                </div>
                <div class="card-body">
                    <form id="admissionForm" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="update_admission">
                        <input type="hidden" name="ajax" value="1">
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">110學年度</label>
                                <input type="number" class="form-control" name="110" value="<?= htmlspecialchars($data['110'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">111學年度</label>
                                <input type="number" class="form-control" name="111" value="<?= htmlspecialchars($data['111'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">112學年度</label>
                                <input type="number" class="form-control" name="112" value="<?= htmlspecialchars($data['112'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">113學年度</label>
                                <input type="number" class="form-control" name="113" value="<?= htmlspecialchars($data['113'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">114學年度</label>
                                <input type="number" class="form-control" name="114" value="<?= htmlspecialchars($data['114'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">更新錄取人數</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include('footer.php'); ?>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // 處理表單提交
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                    } else {
                        alert('更新失敗：' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('更新失敗，請稍後再試');
                });
            });
        });

        // 刪除校系資料
        function deleteDepartment() {
            if (confirm('確定要刪除此校系資料嗎？此操作無法復原。')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('ajax', '1');
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.href = 'about.php?admin=1';
                    } else {
                        alert('刪除失敗：' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('刪除失敗，請稍後再試');
                });
            }
        }
    </script>
</body>
</html>
