<?php
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
        // 取得校系標號
        $sch_num = $_POST['Sch_num'];
        
        // 先檢查校系標號是否重複
        $check_num = "SELECT Sch_num FROM sch_description WHERE Sch_num = ?";
        $stmt_num = $conn->prepare($check_num);
        $stmt_num->bind_param("s", $sch_num);
        $stmt_num->execute();
        $result_num = $stmt_num->get_result();
        if ($result_num->num_rows > 0) {
            throw new Exception("主鍵重複，不可新增");
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
            note, requirement, Exam_Item, Talent, is_deleted
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssisssssss", 
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
            $_POST['Talent']
        );
        
        if ($stmt->execute()) {
            // 新增歷年錄取人數
            $sql_admission = "INSERT INTO admi_thro_years (
                sch_num, School_Name, dep, `110`, `111`, `112`, `113`, `114`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt_admission = $conn->prepare($sql_admission);
            $stmt_admission->bind_param("sssiiiii", 
                $sch_num,
                $_POST['School_Name'],
                $_POST['Department'],
                $_POST['110'],
                $_POST['111'],
                $_POST['112'],
                $_POST['113'],
                $_POST['114']
            );
            
            if ($stmt_admission->execute()) {
                $response['success'] = true;
                $response['message'] = '校系資料新增成功';
                $response['redirect'] = 'about.php?admin=1';
            } else {
                throw new Exception($stmt_admission->error);
            }
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        $response['message'] = '新增失敗：' . $e->getMessage();
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
                                <label class="form-label">學校名稱</label>
                                <input type="text" class="form-control" name="School_Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">科系名稱</label>
                                <input type="text" class="form-control" name="Department" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">地區</label>
                                <input type="text" class="form-control" name="Region" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">地址</label>
                                <input type="text" class="form-control" name="address" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">學群</label>
                                <input type="text" class="form-control" name="Disc_Cluster" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">招生名額</label>
                                <input type="number" class="form-control" name="Quota" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">考試日期</label>
                                <input type="date" class="form-control" name="exam_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">聯絡方式</label>
                                <input type="text" class="form-control" name="Contact">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">簡章連結</label>
                                <input type="url" class="form-control" name="link">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">備註</label>
                                <textarea class="form-control" name="note"></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">校系標號</label>
                                <input type="text" class="form-control" name="Sch_num" required>
                            </div>
                        </div>

                        <!-- 報考資訊區塊 -->
                        <h3 class="mb-3 mt-4">報考資訊</h3>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">報考資格</label>
                                <textarea class="form-control" name="requirement" rows="4" required></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">考試項目</label>
                                <textarea class="form-control" name="Exam_Item" rows="4" required></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">特殊才能</label>
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
                    alert(data.message);
                    window.location.href = data.redirect;
                } else {
                    alert('新增失敗：' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('新增失敗，請稍後再試');
            });
        });
    </script>
</body>
</html> 