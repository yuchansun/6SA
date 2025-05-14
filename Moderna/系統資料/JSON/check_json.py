import json

def check_json_file(year):
    try:
        with open(f"Moderna/系統資料/JSON/{year}.json", "r", encoding="utf-8") as f:
            data = json.load(f)
            print(f"\n=== {year}年度資料檢查 ===")
            print(f"資料類型: {type(data)}")
            if isinstance(data, list):
                print(f"資料筆數: {len(data)}")
                if len(data) > 0:
                    print("\n第一筆資料範例:")
                    print(f"學校: {data[0].get('學校', '無')}")
                    print(f"系所: {data[0].get('系所', '無')}")
                    print(f"招生名額: {data[0].get('招生名額', '無')}")
            elif isinstance(data, dict):
                print("\n資料範例:")
                print(f"學校: {data.get('學校', '無')}")
                print(f"系所: {data.get('系所', '無')}")
                print(f"招生名額: {data.get('招生名額', '無')}")
    except FileNotFoundError:
        print(f"\n=== {year}年度資料檢查 ===")
        print("找不到檔案")
    except json.JSONDecodeError:
        print(f"\n=== {year}年度資料檢查 ===")
        print("JSON格式錯誤")
    except Exception as e:
        print(f"\n=== {year}年度資料檢查 ===")
        print(f"發生錯誤: {str(e)}")

# 檢查110-113年度的資料
for year in range(110, 114):
    check_json_file(year) 