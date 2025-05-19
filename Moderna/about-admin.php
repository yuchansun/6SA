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
$sql = "SELECT sd.*, 
        MAX(CASE WHEN aty.year = 110 THEN aty.student_count END) as '110',
        MAX(CASE WHEN aty.year = 111 THEN aty.student_count END) as '111',
        MAX(CASE WHEN aty.year = 112 THEN aty.student_count END) as '112',
        MAX(CASE WHEN aty.year = 113 THEN aty.student_count END) as '113',
        MAX(CASE WHEN aty.year = 114 THEN aty.student_count END) as '114'
        FROM sch_description sd 
        LEFT JOIN admi_thro_years_normalized aty ON sd.Sch_num = aty.sch_num 
        WHERE sd.Sch_num = ? AND sd.is_deleted = 0
        GROUP BY sd.Sch_num";
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
                        'Department' => '校系',
                        'Region' => '地區',
                        'address' => '地址',
                        'Disc_Cluster' => '學群',
                        'Quota' => '招生名額',
                        'exam_date' => '考試時間',
                        'Contact' => '電話',
                        'link' => '官方連結'
                    ];
                    
                    $emptyFields = [];
                    if (!isset($_POST['Sch_num']) || trim($_POST['Sch_num']) === '') {
                        $emptyFields[] = '校系標號';
                    }
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

                    // 檢查校系標號是否重複（不含自己）
                    $check_num = "SELECT Sch_num FROM sch_description WHERE Sch_num = ? AND Sch_num != ?";
                    $stmt_num = $conn->prepare($check_num);
                    $stmt_num->bind_param("ss", $_POST['Sch_num'], $sch_num);
                    $stmt_num->execute();
                    $result_num = $stmt_num->get_result();
                    if ($result_num->num_rows > 0) {
                        $response['success'] = false;
                        $response['message'] = '校系標號重複 請修改';
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
                            note = ?,
                            p_type = ?
                            WHERE Sch_num = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssisssss", 
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
                        $_POST['p_type'],
                        $sch_num
                    );
                    $stmt->execute();
                    $response['success'] = true;
                    $response['message'] = '基本資料更新成功';
                    break;

                case 'update_requirement':
                    // 必填欄位檢查
                    $requiredFields = [
                        'requirement' => '資格',
                        'Exam_Item' => '考試項目',
                        'Talent' => '能力'
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
                    // 先刪除該校系的所有錄取人數記錄
                    $sql = "DELETE FROM admi_thro_years_normalized WHERE sch_num = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $sch_num);
                    $stmt->execute();

                    // 插入新的錄取人數記錄
                    $years = [110, 111, 112, 113, 114];
                    $sql = "INSERT INTO admi_thro_years_normalized (sch_num, year, student_count) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);

                    foreach ($years as $year) {
                        if (isset($_POST[$year]) && $_POST[$year] !== '') {
                            $stmt->bind_param("sii", $sch_num, $year, $_POST[$year]);
                            $stmt->execute();
                        }
                    }

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                                <label class="form-label">校系標號 *</label>
                                <input type="text" class="form-control" name="Sch_num" value="<?php echo htmlspecialchars($data['Sch_num']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">公私立</label>
                                <select class="form-control" name="p_type">
                                    <option value="">請選擇</option>
                                    <option value="國立" <?php echo $data['p_type'] === '國立' ? 'selected' : ''; ?>>國立</option>
                                    <option value="私立" <?php echo $data['p_type'] === '私立' ? 'selected' : ''; ?>>私立</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">學校名稱 *</label>
                                <input type="text" class="form-control" name="School_Name" value="<?= htmlspecialchars($data['School_Name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">校系 *</label>
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
                                <label class="form-label">考試時間 *</label>
                                <input type="date" class="form-control" name="exam_date" value="<?= htmlspecialchars($data['exam_date']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">電話 *</label>
                                <input type="text" class="form-control" name="Contact" value="<?= htmlspecialchars($data['Contact']) ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">官方連結 *</label>
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
                                <label class="form-label">資格 *</label>
                                <textarea class="form-control" name="requirement" rows="4" required><?php echo htmlspecialchars($data['requirement']); ?></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">考試項目 *</label>
                                <textarea class="form-control" name="Exam_Item" rows="4" required><?php echo htmlspecialchars($data['Exam_Item']); ?></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">能力 *</label>
                                <textarea class="form-control" name="Talent" rows="4" required><?php echo htmlspecialchars($data['Talent']); ?></textarea>
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
                        Swal.fire({
                            title: '成功！',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: '確定'
                        }).then(() => {
                            // 若有需要可加上頁面刷新或跳轉
                        });
                    } else {
                        Swal.fire({
                            title: '錯誤！',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: '確定'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: '系統錯誤！',
                        text: '更新失敗，請稍後再試',
                        icon: 'error',
                        confirmButtonText: '確定'
                    });
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
                        Swal.fire({
                            title: '成功！',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: '確定'
                        }).then(() => {
                            window.location.href = 'about.php?admin=1';
                        });
                    } else {
                        Swal.fire({
                            title: '錯誤！',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: '確定'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: '系統錯誤！',
                        text: '刪除失敗，請稍後再試',
                        icon: 'error',
                        confirmButtonText: '確定'
                    });
                });
            }
        }
    </script>
</body>
</html>
