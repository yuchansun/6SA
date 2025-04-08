<?php
// 資料庫連接設定
$servername = "localhost"; // 資料庫伺服器
$username = "root"; // 資料庫使用者名稱
$password = ""; // 資料庫密碼
$dbname = "sa-6"; // 資料庫名稱

// 建立與資料庫的連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線是否成功
if ($conn->connect_error) {
    die("資料庫連接失敗: " . $conn->connect_error);
}

// 查詢資料：抓取成功大學、輔仁大學和中興大學的科系錄取人數
$sql = "SELECT school, dep, `110`, `111`, `112`, `113`, `114` FROM admi_thro_years WHERE school IN ('國防醫學院', '國防大學', '台灣大學', '清華大學', '中山大學', '成功大學')";
$result = $conn->query($sql);

$schools_data = [];

// 讀取查詢結果並處理資料
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $school = $row['school'];
        $dep = $row['dep'];
        $data = [
            'dep' => $dep,
            '110' => (int)$row['110'],
            '111' => (int)$row['111'],
            '112' => (int)$row['112'],
            '113' => (int)$row['113'],
            '114' => (int)$row['114']
        ];

        // 將資料按照學校和科系分組
        $schools_data[$school][$dep][] = $data;
    }
} else {
    echo "資料庫中沒有符合條件的資料。";
}

$conn->close(); 
?>





<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>各科系特殊選才錄取人數趨勢圖</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<style>
/* 設定每個圖表的外部邊距 */
        #chartsContainer {
            display: flex;
            flex-wrap: wrap; /* 讓圖表自動換行 */
            gap: 20px; /* 設定圖表間的間距 */
            margin: 0 auto;
            justify-content: center;
        }
        
        /* 每個圖表容器的邊距設置 */
        .chart-container {
            margin: 10px; /* 設定每個圖表的間距 */
            width: 400px;  /* 設定圖表容器的寬度 */
            height: 250px; /* 設定圖表容器的高度 */
        }

        canvas {
            width: 100% !important;  /* 保證canvas自適應容器 */
            height: 100% !important; /* 保證canvas自適應容器 */
        }
    </style>
<body>
    <h1>各科系特殊選才錄取人數趨勢圖</h1>

    <!-- 顯示圖表的容器 -->
    <div id="chartsContainer"></div>

    <script>
        // 從 PHP 獲取資料
        const schoolsData = <?php echo json_encode($schools_data); ?>;

        // 輸出資料檢查
        console.log(schoolsData);

        // 準備圖表的標籤
        const labels = ['110', '111', '112', '113', '114'];

        // 動態生成每個學校每個科系的圖表
        let chartIndex = 0;

        // 遍歷每個學校和科系，為每個科系創建一個圖表
        for (const school in schoolsData) {
            const schoolData = schoolsData[school];

            for (const dep in schoolData) {
                const depData = schoolData[dep];
                const data = depData.map(item => [item['110'], item['111'], item['112'], item['113'], item['114']]);

                // 創建一個新的容器顯示每個科系的圖表
                const chartContainer = document.createElement('div');
                chartContainer.innerHTML = `<h2>${school} - ${dep} 科系</h2><canvas id="chart${chartIndex}" width="400" height="200"></canvas>`;
                document.getElementById('chartsContainer').appendChild(chartContainer);

                // 生成圖表
                const ctx = document.getElementById(`chart${chartIndex}`).getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: `${school} - ${dep} 科系`,
                            data: data.flat(), // 展開資料陣列
                            borderColor: getRandomColor(),
                            fill: false,
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: '年份'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: '錄取人數'
                                },
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    min: 0,
                                    max: 100,
                                    callback: function(value) {
                                        return value.toFixed(0); // 確保顯示為整數
                                    }
                                }
                            }
                        }
                    }
                });

                chartIndex++;
            }
        }

        // 隨機顏色生成函數
        function getRandomColor() {
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }
    </script>
</body>
</html>
