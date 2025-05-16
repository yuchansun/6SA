import pandas as pd
import json

# 讀取CSV檔案
df = pd.read_csv('sch_description_114_full.csv')

# 讀取JSON檔案
with open('Moderna/系統資料/JSON/114.json', 'r', encoding='utf-8') as f:
    json_data = json.load(f)

# 創建學校和系所對應計畫類別的字典
plan_dict = {}
for item in json_data:
    key = (item['學校'], item['系所'])
    plan_dict[key] = item['計畫類別']

# 更新note欄位
def update_note(row):
    key = (row['School'], row['Department'])
    if key in plan_dict:
        plan_type = plan_dict[key]
        if pd.isna(row['note']) or row['note'] == '':
            return f'【計畫類別】{plan_type}'
        else:
            return f"{row['note']}\n\n【計畫類別】{plan_type}"
    return row['note']

df['note'] = df.apply(update_note, axis=1)

# 儲存更新後的CSV檔案
df.to_csv('sch_description_114_full.csv', index=False) 