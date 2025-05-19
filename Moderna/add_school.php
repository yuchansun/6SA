<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db.php';

// 檢查是否為管理者
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== '管理者') {
    header('Location: index.php');
    exit;
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
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
            'link' => '簡章連結',
            'Sch_num' => '校系標號',
            'p_type' => '公私立',
            'Exam_Item' => '考試項目',
            'requirement' => '資格',
            'Talent' => '能力'
        ];
        
        $emptyFields = [];
        foreach ($requiredFields as $field => $fieldName) {
            if (empty($_POST[$field])) {
                $emptyFields[] = $fieldName;
            }
        }
        
        if (!empty($emptyFields)) {
            throw new Exception('新增失敗 應填入' . implode('、', $emptyFields) . '。');
        }

        // 取得校系標號
        $sch_num = $_POST['Sch_num'];
        
        // 先檢查校系標號是否重複
        $check_num = "SELECT Sch_num FROM sch_description WHERE Sch_num = ?";
        $stmt_num = $conn->prepare($check_num);
        $stmt_num->bind_param("s", $sch_num);
        $stmt_num->execute();
        $result_num = $stmt_num->get_result();
        if ($result_num->num_rows > 0) {
            throw new Exception("校系標號重複 請修改");
        }

        // 檢查歷年錄取人數表中是否已存在該校系編號
        $check_admission = "SELECT DISTINCT sch_num FROM admi_thro_years_normalized WHERE sch_num LIKE ?";
        $stmt_admission = $conn->prepare($check_admission);
        $search_pattern = $sch_num . '%';
        $stmt_admission->bind_param("s", $search_pattern);
        $stmt_admission->execute();
        $result_admission = $stmt_admission->get_result();
        if ($result_admission->num_rows > 0) {
            $existing_nums = [];
            while($row = $result_admission->fetch_assoc()) {
                $existing_nums[] = $row['sch_num'];
            }
            throw new Exception("校系編號 '{$sch_num}' 在歷年錄取人數表中已存在（包含：".implode(', ', $existing_nums)."），請使用其他編號");
        }
        
        // 先檢查學校是否存在，不存在則新增
        $check_school = "SELECT School_Name FROM school_introduction WHERE School_Name = ?";
        $stmt_check = $conn->prepare($check_school);
        $stmt_check->bind_param("s", $_POST['School_Name']);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($result->num_rows === 0) {
            // 學校不存在，新增學校資料
            $insert_school = "INSERT INTO school_introduction (School_Name) VALUES (?)";
            $stmt_school = $conn->prepare($insert_school);
            $stmt_school->bind_param("s", $_POST['School_Name']);
            if (!$stmt_school->execute()) {
                throw new Exception("新增學校資料失敗：" . $stmt_school->error);
            }
        }
        
        // 插入基本資料
        $sql = "INSERT INTO sch_description (
            Sch_num, School_Name, Department, Region, address, 
            Disc_Cluster, Quota, exam_date, Contact, link, 
            note, requirement, Exam_Item, Talent, p_type, is_deleted
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssissssssss", 
            $sch_num,
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
            $_POST['requirement'],
            $_POST['Exam_Item'],
            $_POST['Talent'],
            $_POST['p_type']
        );
        
        if ($stmt->execute()) {
            // 新增歷年錄取人數
            $years = [110, 111, 112, 113, 114];
            $sql_admission = "INSERT INTO admi_thro_years_normalized (sch_num, year, student_count) VALUES (?, ?, ?)";
            $stmt_admission = $conn->prepare($sql_admission);

            foreach ($years as $year) {
                if (isset($_POST[$year]) && $_POST[$year] !== '') {
                    // 使用原始校系編號，不加上年份
                    $stmt_admission->bind_param("sii", 
                        $sch_num,
                        $year,
                        $_POST[$year]
                    );
                    if (!$stmt_admission->execute()) {
                        throw new Exception("新增歷年錄取人數失敗：" . $stmt_admission->error);
                    }
                }
            }
            
            $response['success'] = true;
            $response['message'] = '校系資料新增成功';
            $response['redirect'] = 'about.php?admin=1';
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>新增校系</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    #header, .header {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      right: 0 !important;
      z-index: 9999 !important;
      width: 100vw !important;
      background-color: #234959 !important;
    }
    .main {
      padding-top: 80px !important;
    }
    </style>
</head>

<body class="about-page">
<?php include('header.php'); ?>

    <main class="main">
        <div class="page-title dark-background">
            <div class="container position-relative">
                <h1>新增校系資料</h1>
            </div>
        </div>

        <div class="container mt-4">
            <div class="card">
                <div class="card-body">
                    <form id="addSchoolForm" class="needs-validation" novalidate>
                        <!-- 基本資料區塊 -->
                        <h3 class="mb-3">基本資料</h3>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">校系標號 *</label>
                                <input type="text" class="form-control" name="Sch_num" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">公私立 *</label>
                                <select class="form-control" name="p_type" required>
                                    <option value="">請選擇</option>
                                    <option value="國立">國立</option>
                                    <option value="私立">私立</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">學校名稱 *</label>
                                <input type="text" class="form-control" name="School_Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">校系 *</label>
                                <input type="text" class="form-control" name="Department" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">地區 *</label>
                                <input type="text" class="form-control" name="Region" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">地址 *</label>
                                <input type="text" class="form-control" name="address" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">學群 *</label>
                                <input type="text" class="form-control" name="Disc_Cluster" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">招生名額 *</label>
                                <input type="number" class="form-control" name="Quota" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">考試時間 *</label>
                                <input type="date" class="form-control" name="exam_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">電話 *</label>
                                <input type="text" class="form-control" name="Contact" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">官方連結 *</label>
                                <input type="url" class="form-control" name="link" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">備註</label>
                                <textarea class="form-control" name="note"></textarea>
                            </div>
                        </div>
                        <div class="text-muted mb-3">
                            * 為必填欄位
                        </div>
                        <!-- 報考資訊區塊 -->
                        <h3 class="mb-3 mt-4">報考資訊</h3>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">資格 *</label>
                                <textarea class="form-control" name="requirement" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">考試項目 *</label>
                                <textarea class="form-control" name="Exam_Item" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">能力 *</label>
                                <textarea class="form-control" name="Talent" rows="4" required></textarea>
                            </div>
                        </div>
                        <!-- 歷年錄取人數區塊 -->
                        <h3 class="mb-3 mt-4">歷年錄取人數</h3>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">110學年度</label>
                                <input type="number" class="form-control" name="110" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">111學年度</label>
                                <input type="number" class="form-control" name="111" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">112學年度</label>
                                <input type="number" class="form-control" name="112" min="0">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">113學年度</label>
                                <input type="number" class="form-control" name="113" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">114學年度</label>
                                <input type="number" class="form-control" name="114" min="0">
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="about.php?admin=1" class="btn btn-secondary me-2">取消</a>
                            <button type="submit" class="btn btn-primary" style="background-color: var(--accent-color); border: none;">新增校系</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include('footer.php'); ?>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('addSchoolForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 使用 SweetAlert2 顯示成功訊息
                    Swal.fire({
                        title: '成功！',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: '確定'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = data.redirect;
                        }
                    });
                } else {
                    // 使用 SweetAlert2 顯示錯誤訊息
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
                    text: '系統發生錯誤，請稍後再試',
                    icon: 'error',
                    confirmButtonText: '確定'
                });
            });
        });
    </script>
</body>
</html> 