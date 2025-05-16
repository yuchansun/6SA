import json
import mysql.connector
from mysql.connector import Error

json_path = 'Moderna/系統資料/JSON/114.json'
sql_path = 'update_sch_description.sql'

# 學校名稱對照表
school_name_map = {
    '國立臺灣大學': '台灣大學',
    '國立臺灣師範大學': '台灣師範大學',
    '國立中興大學': '中興大學',
    '國立成功大學': '成功大學',
    '國立清華大學': '清華大學',
    '國立中央大學': '中央大學'
}

# 建立資料庫連線
try:
    connection = mysql.connector.connect(
        host='localhost',
        database='sa-6',
        user='root',
        password=''
    )

    if connection.is_connected():
        cursor = connection.cursor(dictionary=True)
        
        # 取得所有校系編號的對應關係
        cursor.execute("SELECT School_Name, Department, Sch_num FROM sch_description")
        sch_mapping = {}
        for row in cursor.fetchall():
            key = (row['School_Name'], row['Department'])
            sch_mapping[key] = row['Sch_num']

except Error as e:
    print(f"資料庫連線錯誤: {e}")
    exit(1)

finally:
    if 'connection' in locals() and connection.is_connected():
        cursor.close()
        connection.close()

# 讀取JSON檔案
with open(json_path, 'r', encoding='utf-8') as f:
    data = json.load(f)

cases = []
for entry in data:
    school = entry.get('學校')
    dept = entry.get('系所')
    plan = entry.get('計畫類別')
    sch_num = entry.get('校系編號')  # 直接從JSON讀取校系編號
    
    if school and dept and plan and sch_num:
        # 轉換學校名稱
        mapped_school = school_name_map.get(school, school)
        
        # SQL escape 單引號
        school_sql = mapped_school.replace("'", "''")
        dept_sql = dept.replace("'", "''")
        plan_sql = plan.replace("'", "''")
        sch_num_sql = sch_num.replace("'", "''")
        
        cases.append(f"    WHEN Sch_num = '{sch_num_sql}' THEN CONCAT(COALESCE(note, ''), '\n計畫類別：{plan_sql}')")

case_sql = '\n'.join(cases)

sql = f"""-- 自動產生：更新sch_description資料表中的note欄位，只加計畫類別\nUPDATE sch_description \nSET note = CASE\n{case_sql}\n    ELSE note\nEND;\n"""

with open(sql_path, 'w', encoding='utf-8') as f:
    f.write(sql)

print('SQL產生完畢，已寫入', sql_path) 