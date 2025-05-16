import json

def check_json_file(year):
    try:
        with open(f"Moderna/系統資料/JSON/{year}.json", "r", encoding="utf-8") as f:
            data = json.load(f)
            print(f"\n=== {year}年資料結構檢查 ===")
            print(f"資料類型: {type(data)}")
            
            if isinstance(data, dict):
                print("主要鍵值:")
                for key in data.keys():
                    print(f"- {key}")
                if "Table 1" in data:
                    print("\nTable 1 的第一筆資料範例:")
                    first_item = data["Table 1"][0]
                    for key, value in first_item.items():
                        print(f"{key}: {value}")
            elif isinstance(data, list):
                print("\n第一筆資料範例:")
                first_item = data[0]
                for key, value in first_item.items():
                    print(f"{key}: {value}")
            
    except FileNotFoundError:
        print(f"找不到 {year} 年度資料")
    except json.JSONDecodeError as e:
        print(f"JSON格式錯誤：{str(e)}")
    except Exception as e:
        print(f"發生錯誤：{str(e)}")

# 特別檢查113年的資料
check_json_file(113) 