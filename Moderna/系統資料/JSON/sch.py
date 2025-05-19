import json
import pandas as pd

# 定義學校地址對應字典
school_addresses = {
    "國立臺灣大學": "臺北市大安區羅斯福路四段1號",
    "國立清華大學": "新竹市東區光復路二段101號",
    "國立陽明交通大學": "新竹市東區大學路1001號",
    "國立成功大學": "臺南市東區大學路1號",
    "國立政治大學": "臺北市文山區指南路二段64號",
    "國立中央大學": "桃園市中壢區中大路300號",
    "國立中興大學": "臺中市南區興大路145號",
    "國立中山大學": "高雄市鼓山區蓮海路70號",
    "國立臺灣師範大學": "臺北市大安區和平東路一段162號",
    "國立中正大學": "嘉義縣民雄鄉大學路一段168號",
    "國立臺灣海洋大學": "基隆市中正區北寧路2號",
    "國立高雄師範大學": "高雄市苓雅區和平一路116號",
    "國立彰化師範大學": "彰化縣彰化市進德路1號",
    "國立臺北教育大學": "臺北市大安區和平東路二段134號",
    "國立臺中教育大學": "臺中市西區民生路140號",
    "國立臺南大學": "臺南市中西區樹林街二段33號",
    "國立東華大學": "花蓮縣壽豐鄉大學路二段1號",
    "國立暨南國際大學": "南投縣埔里鎮大學路1號",
    "國立臺東大學": "臺東縣臺東市大學路二段369號",
    "國立宜蘭大學": "宜蘭縣宜蘭市神農路一段1號",
    "國立聯合大學": "苗栗縣苗栗市南勢里聯大2號",
    "國立金門大學": "金門縣金寧鄉大學路1號",
    "國立屏東大學": "屏東縣屏東市林森路1號",
    "國立臺北大學": "新北市三峽區大學路151號",
    "國立嘉義大學": "嘉義市東區學府路300號",
    "國立高雄大學": "高雄市楠梓區高雄大學路700號",
    "國立臺北科技大學": "臺北市大安區忠孝東路三段1號",
    "國立臺灣科技大學": "臺北市大安區基隆路四段43號",
    "國立雲林科技大學": "雲林縣斗六市大學路三段123號",
    "國立屏東科技大學": "屏東縣內埔鄉學府路1號",
    "國立高雄科技大學": "高雄市燕巢區大學路1號",
    "國立虎尾科技大學": "雲林縣虎尾鎮文化路64號",
    "國立勤益科技大學": "臺中市太平區中山路二段57號",
    "國立臺中科技大學": "臺中市北區三民路三段129號",
    "國立臺北商業大學": "臺北市中正區濟南路一段321號",
    "國立高雄餐旅大學": "高雄市小港區松和路1號",
    "國立臺東專科學校": "臺東縣臺東市正氣北路911號",
    "國立臺南護理專科學校": "臺南市中西區民族路二段78號",
    "國立臺中護理專科學校": "臺中市西區三民路一段193號",
    "國立臺北護理健康大學": "臺北市北投區明德路365號",
    "國立臺南藝術大學": "臺南市官田區大崎里66號",
    "國立臺灣藝術大學": "新北市板橋區大觀路一段59號",
    "國立臺北藝術大學": "臺北市北投區學園路1號",
    "國立體育大學": "桃園市龜山區文化一路250號",
    "國立臺灣體育運動大學": "臺中市北區雙十路一段16號",
    "國立空中大學": "新北市蘆洲區中正路172號"
}

# 定義系所與學群對應字典
department_clusters = {
    # 資訊學群
    "資訊工程": "資訊學群", "資訊科學": "資訊學群", "資訊管理": "資訊學群", "資訊傳播": "資訊學群",
    "資訊科技": "資訊學群", "資訊系統": "資訊學群", "資訊網路": "資訊學群", "資訊安全": "資訊學群",
    "資訊教育": "資訊學群", "資訊應用": "資訊學群", "資訊設計": "資訊學群", "資訊傳播科技": "資訊學群",
    
    # 工程學群
    "機械工程": "工程學群", "電機工程": "工程學群", "電子工程": "工程學群", "化學工程": "工程學群",
    "土木工程": "工程學群", "材料工程": "工程學群", "工業工程": "工程學群", "環境工程": "工程學群",
    "生物工程": "工程學群", "光電工程": "工程學群", "能源工程": "工程學群", "航太工程": "工程學群",
    
    # 數理化學群
    "數學": "數理化學群", "物理": "數理化學群", "化學": "數理化學群", "應用數學": "數理化學群",
    "應用物理": "數理化學群", "應用化學": "數理化學群", "統計": "數理化學群", "大氣科學": "數理化學群",
    
    # 醫藥衛生學群
    "醫學": "醫藥衛生學群", "藥學": "醫藥衛生學群", "護理": "醫藥衛生學群", "公共衛生": "醫藥衛生學群",
    "物理治療": "醫藥衛生學群", "職能治療": "醫藥衛生學群", "醫學檢驗": "醫藥衛生學群", "醫學影像": "醫藥衛生學群",
    "呼吸治療": "醫藥衛生學群", "營養": "醫藥衛生學群", "健康管理": "醫藥衛生學群",
    
    # 生命科學學群
    "生物": "生命科學學群", "生物科技": "生命科學學群", "生物醫學": "生命科學學群", "生物資訊": "生命科學學群",
    "生物工程": "生命科學學群", "生物資源": "生命科學學群", "生物技術": "生命科學學群",
    
    # 生物資源學群
    "農藝": "生物資源學群", "園藝": "生物資源學群", "森林": "生物資源學群", "動物科學": "生物資源學群",
    "植物病理": "生物資源學群", "昆蟲": "生物資源學群", "農業經濟": "生物資源學群",
    
    # 地球與環境學群
    "地質": "地球與環境學群", "地理": "地球與環境學群", "環境科學": "地球與環境學群", "海洋科學": "地球與環境學群",
    "大氣科學": "地球與環境學群", "地球科學": "地球與環境學群", "環境工程": "地球與環境學群",
    
    # 建築與設計學群
    "建築": "建築與設計學群", "都市計畫": "建築與設計學群", "景觀設計": "建築與設計學群", "工業設計": "建築與設計學群",
    "商業設計": "建築與設計學群", "視覺傳達": "建築與設計學群", "空間設計": "建築與設計學群",
    
    # 藝術學群
    "音樂": "藝術學群", "美術": "藝術學群", "戲劇": "藝術學群", "舞蹈": "藝術學群",
    "電影": "藝術學群", "設計": "藝術學群", "藝術": "藝術學群", "表演藝術": "藝術學群",
    
    # 社會與心理學群
    "社會學": "社會與心理學群", "心理學": "社會與心理學群", "社會工作": "社會與心理學群", "人類學": "社會與心理學群",
    "諮商心理": "社會與心理學群", "臨床心理": "社會與心理學群",
    
    # 大眾傳播學群
    "新聞": "大眾傳播學群", "廣播電視": "大眾傳播學群", "廣告": "大眾傳播學群", "傳播": "大眾傳播學群",
    "大眾傳播": "大眾傳播學群", "數位媒體": "大眾傳播學群", "媒體設計": "大眾傳播學群",
    
    # 外語學群
    "英文": "外語學群", "日文": "外語學群", "法文": "外語學群", "德文": "外語學群",
    "西班牙文": "外語學群", "俄文": "外語學群", "韓文": "外語學群", "翻譯": "外語學群",
    
    # 文史哲學群
    "中文": "文史哲學群", "歷史": "文史哲學群", "哲學": "文史哲學群", "台灣文學": "文史哲學群",
    "中國文學": "文史哲學群", "應用中文": "文史哲學群",
    
    # 教育學群
    "教育": "教育學群", "特殊教育": "教育學群", "幼兒教育": "教育學群", "教育心理": "教育學群",
    "教育行政": "教育學群", "教育科技": "教育學群",
    
    # 法政學群
    "法律": "法政學群", "政治": "法政學群", "公共行政": "法政學群", "國際關係": "法政學群",
    "外交": "法政學群", "財經法律": "法政學群",
    
    # 管理學群
    "企業管理": "管理學群", "財務管理": "管理學群", "會計": "管理學群", "國際企業": "管理學群",
    "行銷": "管理學群", "人力資源": "管理學群", "科技管理": "管理學群",
    
    # 財經學群
    "經濟": "財經學群", "金融": "財經學群", "保險": "財經學群", "財務金融": "財經學群",
    "國際貿易": "財經學群", "財政": "財經學群", "會計": "財經學群",
    
    # 遊憩與運動學群
    "休閒運動": "遊憩與運動學群", "運動管理": "遊憩與運動學群", "觀光": "遊憩與運動學群",
    "餐旅管理": "遊憩與運動學群", "休閒管理": "遊憩與運動學群"
}

# 定義系所與所需能力的對應關係
department_talents = {
    # 資訊學群
    "資訊工程": "邏輯推理、程式設計、數學能力、英文能力",
    "資訊科學": "邏輯推理、程式設計、數學能力、英文能力",
    "資訊管理": "邏輯推理、程式設計、管理能力、英文能力",
    "資訊傳播": "邏輯推理、程式設計、傳播能力、創意能力",
    
    # 工程學群
    "機械工程": "物理能力、數學能力、空間概念、動手能力",
    "電機工程": "物理能力、數學能力、邏輯推理、英文能力",
    "電子工程": "物理能力、數學能力、邏輯推理、英文能力",
    "化學工程": "化學能力、數學能力、實驗能力、英文能力",
    "土木工程": "物理能力、數學能力、空間概念、繪圖能力",
    
    # 數理化學群
    "數學": "數學能力、邏輯推理、抽象思考、英文能力",
    "物理": "物理能力、數學能力、邏輯推理、英文能力",
    "化學": "化學能力、數學能力、實驗能力、英文能力",
    
    # 醫藥衛生學群
    "醫學": "生物能力、化學能力、英文能力、溝通能力",
    "藥學": "化學能力、生物能力、英文能力、細心度",
    "護理": "生物能力、化學能力、溝通能力、同理心",
    
    # 生命科學學群
    "生物": "生物能力、化學能力、實驗能力、英文能力",
    "生物科技": "生物能力、化學能力、實驗能力、英文能力",
    
    # 建築與設計學群
    "建築": "空間概念、繪圖能力、創意能力、數學能力",
    "工業設計": "空間概念、繪圖能力、創意能力、美學素養",
    "商業設計": "創意能力、美學素養、溝通能力、電腦技能",
    
    # 藝術學群
    "音樂": "音樂能力、創意能力、表演能力、美學素養",
    "美術": "繪畫能力、創意能力、美學素養、觀察力",
    "戲劇": "表演能力、創意能力、溝通能力、表達能力",
    
    # 社會與心理學群
    "心理學": "溝通能力、同理心、觀察力、分析能力",
    "社會學": "溝通能力、觀察力、分析能力、寫作能力",
    
    # 大眾傳播學群
    "新聞": "寫作能力、溝通能力、觀察力、英文能力",
    "廣告": "創意能力、溝通能力、行銷能力、美學素養",
    
    # 外語學群
    "英文": "英文能力、溝通能力、寫作能力、文化素養",
    "日文": "日文能力、溝通能力、寫作能力、文化素養",
    
    # 文史哲學群
    "中文": "寫作能力、閱讀能力、表達能力、文化素養",
    "歷史": "閱讀能力、寫作能力、分析能力、文化素養",
    
    # 教育學群
    "教育": "溝通能力、表達能力、同理心、領導能力",
    "特殊教育": "溝通能力、同理心、耐心、觀察力",
    
    # 法政學群
    "法律": "邏輯推理、寫作能力、表達能力、英文能力",
    "政治": "邏輯推理、寫作能力、表達能力、英文能力",
    
    # 管理學群
    "企業管理": "領導能力、溝通能力、分析能力、英文能力",
    "財務管理": "數學能力、分析能力、細心度、英文能力",
    
    # 財經學群
    "經濟": "數學能力、邏輯推理、分析能力、英文能力",
    "金融": "數學能力、分析能力、細心度、英文能力",
    
    # 遊憩與運動學群
    "休閒運動": "體能、溝通能力、領導能力、服務精神",
    "觀光": "溝通能力、服務精神、英文能力、文化素養"
}

# 定義校系官方網站對應關係
department_websites = {
    # 國立臺灣大學
    ("國立臺灣大學", "資訊工程學系"): "https://www.csie.ntu.edu.tw/",
    ("國立臺灣大學", "電機工程學系"): "https://www.ee.ntu.edu.tw/",
    ("國立臺灣大學", "機械工程學系"): "https://www.me.ntu.edu.tw/",
    ("國立臺灣大學", "化學工程學系"): "https://www.che.ntu.edu.tw/",
    ("國立臺灣大學", "土木工程學系"): "https://www.ce.ntu.edu.tw/",
    ("國立臺灣大學", "資訊管理學系"): "https://www.im.ntu.edu.tw/",
    ("國立臺灣大學", "工商管理學系"): "https://www.ba.ntu.edu.tw/",
    ("國立臺灣大學", "財務金融學系"): "https://www.fin.ntu.edu.tw/",
    ("國立臺灣大學", "會計學系"): "https://www.acc.ntu.edu.tw/",
    ("國立臺灣大學", "經濟學系"): "https://www.econ.ntu.edu.tw/",
    ("國立臺灣大學", "法律學系"): "https://www.law.ntu.edu.tw/",
    ("國立臺灣大學", "政治學系"): "https://www.politics.ntu.edu.tw/",
    ("國立臺灣大學", "社會學系"): "https://www.sociology.ntu.edu.tw/",
    ("國立臺灣大學", "心理學系"): "https://www.psy.ntu.edu.tw/",
    ("國立臺灣大學", "中國文學系"): "https://www.cl.ntu.edu.tw/",
    ("國立臺灣大學", "外國語文學系"): "https://www.forex.ntu.edu.tw/",
    ("國立臺灣大學", "歷史學系"): "https://www.history.ntu.edu.tw/",
    ("國立臺灣大學", "哲學系"): "https://www.philo.ntu.edu.tw/",
    ("國立臺灣大學", "數學系"): "https://www.math.ntu.edu.tw/",
    ("國立臺灣大學", "物理學系"): "https://www.phys.ntu.edu.tw/",
    ("國立臺灣大學", "化學系"): "https://www.ch.ntu.edu.tw/",
    ("國立臺灣大學", "生物學系"): "https://www.bio.ntu.edu.tw/",
    ("國立臺灣大學", "醫學系"): "https://www.mc.ntu.edu.tw/",
    ("國立臺灣大學", "藥學系"): "https://www.pharm.ntu.edu.tw/",
    ("國立臺灣大學", "護理學系"): "https://www.mc.ntu.edu.tw/nursing/",
    ("國立臺灣大學", "公共衛生學系"): "https://www.publichealth.ntu.edu.tw/",
    ("國立臺灣大學", "建築與城鄉研究所"): "https://www.bp.ntu.edu.tw/",
    ("國立臺灣大學", "藝術史研究所"): "https://www.art.ntu.edu.tw/",
    ("國立臺灣大學", "戲劇學系"): "https://www.theatre.ntu.edu.tw/",
    ("國立臺灣大學", "音樂學系"): "https://www.music.ntu.edu.tw/",
    
    # 國立清華大學
    ("國立清華大學", "資訊工程學系"): "https://www.cs.nthu.edu.tw/",
    ("國立清華大學", "電機工程學系"): "https://www.ee.nthu.edu.tw/",
    ("國立清華大學", "機械工程學系"): "https://www.me.nthu.edu.tw/",
    ("國立清華大學", "化學工程學系"): "https://www.che.nthu.edu.tw/",
    ("國立清華大學", "材料科學工程學系"): "https://www.mse.nthu.edu.tw/",
    ("國立清華大學", "工業工程與工程管理學系"): "https://www.ie.nthu.edu.tw/",
    ("國立清華大學", "資訊管理學系"): "https://www.im.nthu.edu.tw/",
    ("國立清華大學", "科技管理學院"): "https://www.ctm.nthu.edu.tw/",
    ("國立清華大學", "經濟學系"): "https://www.econ.nthu.edu.tw/",
    ("國立清華大學", "計量財務金融學系"): "https://www.quant.nthu.edu.tw/",
    ("國立清華大學", "法律學系"): "https://www.law.nthu.edu.tw/",
    ("國立清華大學", "社會學系"): "https://www.sociology.nthu.edu.tw/",
    ("國立清華大學", "心理學系"): "https://www.psy.nthu.edu.tw/",
    ("國立清華大學", "中國文學系"): "https://www.cl.nthu.edu.tw/",
    ("國立清華大學", "外國語文學系"): "https://www.forex.nthu.edu.tw/",
    ("國立清華大學", "歷史研究所"): "https://www.history.nthu.edu.tw/",
    ("國立清華大學", "哲學研究所"): "https://www.philo.nthu.edu.tw/",
    ("國立清華大學", "數學系"): "https://www.math.nthu.edu.tw/",
    ("國立清華大學", "物理學系"): "https://www.phys.nthu.edu.tw/",
    ("國立清華大學", "化學系"): "https://www.chem.nthu.edu.tw/",
    ("國立清華大學", "生命科學系"): "https://www.life.nthu.edu.tw/",
    ("國立清華大學", "醫學科學系"): "https://www.med.nthu.edu.tw/",
    ("國立清華大學", "藝術與設計學系"): "https://www.art.nthu.edu.tw/",
    ("國立清華大學", "音樂學系"): "https://www.music.nthu.edu.tw/",
    
    # 國立陽明交通大學
    ("國立陽明交通大學", "資訊工程學系"): "https://www.cs.nycu.edu.tw/",
    ("國立陽明交通大學", "電機工程學系"): "https://www.ee.nycu.edu.tw/",
    ("國立陽明交通大學", "機械工程學系"): "https://www.me.nycu.edu.tw/",
    ("國立陽明交通大學", "材料科學與工程學系"): "https://www.mse.nycu.edu.tw/",
    ("國立陽明交通大學", "工業工程與管理學系"): "https://www.iem.nycu.edu.tw/",
    ("國立陽明交通大學", "資訊管理與財務金融學系"): "https://www.imf.nycu.edu.tw/",
    ("國立陽明交通大學", "管理科學系"): "https://www.ms.nycu.edu.tw/",
    ("國立陽明交通大學", "運輸與物流管理學系"): "https://www.tlm.nycu.edu.tw/",
    ("國立陽明交通大學", "應用數學系"): "https://www.math.nycu.edu.tw/",
    ("國立陽明交通大學", "應用化學系"): "https://www.chem.nycu.edu.tw/",
    ("國立陽明交通大學", "應用物理研究所"): "https://www.phys.nycu.edu.tw/",
    ("國立陽明交通大學", "生物科技學系"): "https://www.bio.nycu.edu.tw/",
    ("國立陽明交通大學", "醫學系"): "https://www.med.nycu.edu.tw/",
    ("國立陽明交通大學", "護理學系"): "https://www.nur.nycu.edu.tw/",
    ("國立陽明交通大學", "醫學生物技術暨檢驗學系"): "https://www.mbt.nycu.edu.tw/",
    ("國立陽明交通大學", "物理治療暨輔助科技學系"): "https://www.pt.nycu.edu.tw/",
    ("國立陽明交通大學", "生物醫學工程學系"): "https://www.bme.nycu.edu.tw/",
    ("國立陽明交通大學", "視覺文化研究所"): "https://www.vc.nycu.edu.tw/",
    ("國立陽明交通大學", "應用藝術研究所"): "https://www.art.nycu.edu.tw/",
    
    # 國立成功大學
    ("國立成功大學", "資訊工程學系"): "https://www.csie.ncku.edu.tw/",
    ("國立成功大學", "電機工程學系"): "https://www.ee.ncku.edu.tw/",
    ("國立成功大學", "機械工程學系"): "https://www.me.ncku.edu.tw/",
    ("國立成功大學", "化學工程學系"): "https://www.che.ncku.edu.tw/",
    ("國立成功大學", "土木工程學系"): "https://www.civil.ncku.edu.tw/",
    ("國立成功大學", "材料科學及工程學系"): "https://www.mse.ncku.edu.tw/",
    ("國立成功大學", "資源工程學系"): "https://www.re.ncku.edu.tw/",
    ("國立成功大學", "工業與資訊管理學系"): "https://www.iim.ncku.edu.tw/",
    ("國立成功大學", "資訊管理研究所"): "https://www.im.ncku.edu.tw/",
    ("國立成功大學", "企業管理學系"): "https://www.ba.ncku.edu.tw/",
    ("國立成功大學", "會計學系"): "https://www.acc.ncku.edu.tw/",
    ("國立成功大學", "財務金融研究所"): "https://www.fin.ncku.edu.tw/",
    ("國立成功大學", "經濟學系"): "https://www.econ.ncku.edu.tw/",
    ("國立成功大學", "法律學系"): "https://www.law.ncku.edu.tw/",
    ("國立成功大學", "政治學系"): "https://www.politics.ncku.edu.tw/",
    ("國立成功大學", "社會學系"): "https://www.sociology.ncku.edu.tw/",
    ("國立成功大學", "心理學系"): "https://www.psy.ncku.edu.tw/",
    ("國立成功大學", "中國文學系"): "https://www.chinese.ncku.edu.tw/",
    ("國立成功大學", "外國語文學系"): "https://www.forex.ncku.edu.tw/",
    ("國立成功大學", "歷史學系"): "https://www.history.ncku.edu.tw/",
    ("國立成功大學", "台灣文學系"): "https://www.twl.ncku.edu.tw/",
    ("國立成功大學", "數學系"): "https://www.math.ncku.edu.tw/",
    ("國立成功大學", "物理學系"): "https://www.phys.ncku.edu.tw/",
    ("國立成功大學", "化學系"): "https://www.chem.ncku.edu.tw/",
    ("國立成功大學", "生命科學系"): "https://www.life.ncku.edu.tw/",
    ("國立成功大學", "醫學系"): "https://www.med.ncku.edu.tw/",
    ("國立成功大學", "護理學系"): "https://www.nur.ncku.edu.tw/",
    ("國立成功大學", "藥學系"): "https://www.pharm.ncku.edu.tw/",
    ("國立成功大學", "公共衛生學系"): "https://www.ph.ncku.edu.tw/",
    ("國立成功大學", "建築學系"): "https://www.arch.ncku.edu.tw/",
    ("國立成功大學", "都市計劃學系"): "https://www.up.ncku.edu.tw/",
    ("國立成功大學", "工業設計學系"): "https://www.id.ncku.edu.tw/",
    ("國立成功大學", "創意產業設計研究所"): "https://www.cid.ncku.edu.tw/",
    ("國立成功大學", "藝術研究所"): "https://www.art.ncku.edu.tw/",
    ("國立成功大學", "音樂學系"): "https://www.music.ncku.edu.tw/"
}

def get_department_website(school, dept):
    # 檢查是否有系所特定的網站
    website = department_websites.get((school, dept))
    if website:
        return website
    
    # 如果沒有系所特定的網站，則返回學校首頁
    school_websites = {
        "國立臺灣大學": "https://www.ntu.edu.tw/",
        "國立清華大學": "https://www.nthu.edu.tw/",
        "國立陽明交通大學": "https://www.nycu.edu.tw/",
        "國立成功大學": "https://www.ncku.edu.tw/",
        "國立政治大學": "https://www.nccu.edu.tw/",
        "國立中央大學": "https://www.ncu.edu.tw/",
        "國立中興大學": "https://www.nchu.edu.tw/",
        "國立中山大學": "https://www.nsysu.edu.tw/",
        "國立臺灣師範大學": "https://www.ntnu.edu.tw/",
        "國立中正大學": "https://www.ccu.edu.tw/",
        "國立臺灣海洋大學": "https://www.ntou.edu.tw/",
        "國立高雄師範大學": "https://www.nknu.edu.tw/",
        "國立彰化師範大學": "https://www.ncue.edu.tw/",
        "國立臺北教育大學": "https://www.ntue.edu.tw/",
        "國立臺中教育大學": "https://www.ntcu.edu.tw/",
        "國立臺南大學": "https://www.nutn.edu.tw/",
        "國立東華大學": "https://www.ndhu.edu.tw/",
        "國立暨南國際大學": "https://www.ncnu.edu.tw/",
        "國立臺東大學": "https://www.nttu.edu.tw/",
        "國立宜蘭大學": "https://www.niu.edu.tw/",
        "國立聯合大學": "https://www.nuu.edu.tw/",
        "國立金門大學": "https://www.nqu.edu.tw/",
        "國立屏東大學": "https://www.nptu.edu.tw/",
        "國立臺北大學": "https://www.ntpu.edu.tw/",
        "國立嘉義大學": "https://www.ncyu.edu.tw/",
        "國立高雄大學": "https://www.nuk.edu.tw/",
        "國立臺北科技大學": "https://www.ntut.edu.tw/",
        "國立臺灣科技大學": "https://www.ntust.edu.tw/",
        "國立雲林科技大學": "https://www.yuntech.edu.tw/",
        "國立屏東科技大學": "https://www.npust.edu.tw/",
        "國立高雄科技大學": "https://www.nkust.edu.tw/",
        "國立虎尾科技大學": "https://www.nfu.edu.tw/",
        "國立勤益科技大學": "https://www.ncut.edu.tw/",
        "國立臺中科技大學": "https://www.nutc.edu.tw/",
        "國立臺北商業大學": "https://www.ntub.edu.tw/",
        "國立高雄餐旅大學": "https://www.nkuht.edu.tw/",
        "國立臺東專科學校": "https://www.ntc.edu.tw/",
        "國立臺南護理專科學校": "https://www.ntin.edu.tw/",
        "國立臺中護理專科學校": "https://www.ntcn.edu.tw/",
        "國立臺北護理健康大學": "https://www.ntunhs.edu.tw/",
        "國立臺南藝術大學": "https://www.tnnua.edu.tw/",
        "國立臺灣藝術大學": "https://www.ntua.edu.tw/",
        "國立臺北藝術大學": "https://www.tnua.edu.tw/",
        "國立體育大學": "https://www.ntsu.edu.tw/",
        "國立臺灣體育運動大學": "https://www.ntus.edu.tw/",
        "國立空中大學": "https://www.nou.edu.tw/"
    }
    
    return school_websites.get(school, "")

def get_department_talent(dept_name, cluster):
    # 移除系所名稱中的"系"、"所"、"學系"等字
    dept_name = dept_name.replace("系", "").replace("所", "").replace("學系", "")
    
    # 先檢查是否有系所特定的能力要求
    for key, value in department_talents.items():
        if key in dept_name:
            return value
    
    # 如果沒有系所特定的能力要求，則根據學群返回一般能力要求
    cluster_talents = {
        "資訊學群": "邏輯推理、程式設計、數學能力、英文能力",
        "工程學群": "物理能力、數學能力、邏輯推理、英文能力",
        "數理化學群": "數學能力、邏輯推理、實驗能力、英文能力",
        "醫藥衛生學群": "生物能力、化學能力、英文能力、溝通能力",
        "生命科學學群": "生物能力、化學能力、實驗能力、英文能力",
        "生物資源學群": "生物能力、化學能力、實驗能力、觀察力",
        "地球與環境學群": "地理能力、化學能力、觀察力、英文能力",
        "建築與設計學群": "空間概念、繪圖能力、創意能力、美學素養",
        "藝術學群": "創意能力、美學素養、表達能力、觀察力",
        "社會與心理學群": "溝通能力、同理心、觀察力、分析能力",
        "大眾傳播學群": "溝通能力、創意能力、寫作能力、表達能力",
        "外語學群": "語言能力、溝通能力、文化素養、表達能力",
        "文史哲學群": "閱讀能力、寫作能力、分析能力、文化素養",
        "教育學群": "溝通能力、表達能力、同理心、領導能力",
        "法政學群": "邏輯推理、寫作能力、表達能力、英文能力",
        "管理學群": "領導能力、溝通能力、分析能力、英文能力",
        "財經學群": "數學能力、分析能力、邏輯推理、英文能力",
        "遊憩與運動學群": "體能、溝通能力、服務精神、領導能力",
        "其他學群": "基礎學科能力、溝通能力、表達能力、學習能力"
    }
    
    return cluster_talents.get(cluster, "基礎學科能力、溝通能力、表達能力、學習能力")

def get_department_cluster(dept_name):
    # 移除系所名稱中的"系"、"所"、"學系"等字
    dept_name = dept_name.replace("系", "").replace("所", "").replace("學系", "")
    
    # 檢查系所名稱是否在對應字典中
    for key, value in department_clusters.items():
        if key in dept_name:
            return value
    
    return "其他學群"  # 如果找不到對應的學群，返回"其他學群"

# 讀取各年度資料
years_data = {}
for year in range(110, 115):
    try:
        with open(f"Moderna/系統資料/JSON/{year}.json", "r", encoding="utf-8") as f:
            data = json.load(f)
            if isinstance(data, list):
                years_data[str(year)] = data
            elif isinstance(data, dict):
                years_data[str(year)] = [data]
            else:
                years_data[str(year)] = []
            print(f"成功讀取 {year} 年度資料")
    except FileNotFoundError:
        print(f"找不到 {year} 年度資料")
        years_data[str(year)] = []

# 建立學校系所對應字典
school_dept_map = {}

# 處理 114 年度資料
with open("Moderna/系統資料/JSON/114.json", "r", encoding="utf-8") as f:
    raw_data = json.load(f)
print(f"原始JSON資料筆數: {len(raw_data)}筆")

records = []
for idx, entry in enumerate(raw_data, 1):
    sch_num = f"SCH114{idx:05d}"
    school = entry.get("學校", "").strip()
    dept = entry.get("系所", "").strip()
    ptype = entry.get("立別", "").strip()
    region = entry.get("區域別", "").strip()
    
    # 儲存學校系所對應關係
    school_dept_map[(school, dept)] = sch_num
    
    # 根據學校名稱判斷地址
    address = school_addresses.get(school, "")
    
    # 根據系所名稱判斷學群
    disc_cluster = get_department_cluster(dept)
    
    # 根據系所名稱和學群判斷所需能力
    talent = get_department_talent(dept, disc_cluster)
    
    # 根據學校和系所名稱判斷官方網站
    link = get_department_website(school, dept)
    
    requirement = (entry.get("招生對象") or "").replace("\n", " ").strip()
    exam_items = entry.get("考試項目")
    exam_item = "\n".join(exam_items) if isinstance(exam_items, list) else str(exam_items)
    quota = int(entry.get("招生名額") or 0)
    date = entry.get("日程公告", {})
    exam_date = ""
    for title, date_str in date.items():
        if "考試" in title and date_str:
            exam_date = date_str
            break
    contact = f"{entry.get('聯絡人') or ''} / {entry.get('聯絡電話') or ''}"
    note = (entry.get("報考須知") or "").strip()

    records.append({
        "Sch_num": sch_num,
        "School": school,
        "p_type": ptype,
        "Department": dept,
        "Region": region,
        "address": address,
        "Disc_Cluster": disc_cluster,
        "requirement": requirement,
        "Exam_Item": exam_item,
        "Talent": talent,
        "Quota": quota,
        "exam_date": exam_date,
        "Contact": contact,
        "link": link,
        "note": note
    })

df = pd.DataFrame(records)
print(f"學校資料筆數: {len(df)}筆")
df.to_csv("sch_description_114_full.csv", index=False)

# 將 df 重新命名為 df_sch
df_sch = df

# --- Part 2: 產出 todos.csv ---
sch_map = {(r["School"], r["Department"]): r["Sch_num"] for _, r in df_sch.iterrows()}
print(f"學校對應關係數量: {len(sch_map)}筆")

todos = []
todo_id = 100  # 從 100 開始，避免與現有資料衝突

for item in raw_data:
    school = item["學校"]
    dept = item["系所"]
    sch_num = sch_map.get((school, dept))
    if not sch_num:
        continue
    for title, date in item.get("日程公告", {}).items():
        if not date:
            continue
        todos.append({
            "todo_id": todo_id,
            "Sch_num": sch_num,
            "title": title,
            "start_time": f"{date} 00:00:00",
            "end_time": None
        })
        todo_id += 1

df_todos = pd.DataFrame(todos)
print(f"待辦事項筆數: {len(df_todos)}筆")
df_todos.to_csv("todos.csv", index=False, na_rep="NULL")

# --- Part 3: 產出歷年名額資訊 ---
# 建立歷年名額資料
years_records = []
for _, row in df.iterrows():
    school = row["School"]
    dept = row["Department"]
    sch_num = row["Sch_num"]
    
    # 建立年度名額字典
    quotas = []
    for year in range(110, 115):
        year_str = str(year)
        quota = 0
        # 在該年度的資料中尋找對應的學校和系所
        for item in years_data.get(year_str, []):
            if not isinstance(item, dict):
                continue
                
            # 移除多餘的空格和特殊字元
            item_school = item.get("學校", "").strip().replace(" ", "")
            item_dept = item.get("系所", "").strip().replace(" ", "")
            target_school = school.strip().replace(" ", "")
            target_dept = dept.strip().replace(" ", "")
            
            # 比對學校和系所名稱
            if item_school == target_school and item_dept == target_dept:
                quota = int(item.get("招生名額") or 0)
                print(f"找到 {year} 年度 {school} {dept} 的名額：{quota}")
                break
        
        quotas.append({
            "sch_num": sch_num,
            "year": year,
            "student_count": quota
        })

    years_records.append({
        "sch_num": sch_num,
        "School_Name": school,
        "dep": dept,
        "110": quotas[0]["student_count"],
        "111": quotas[1]["student_count"],
        "112": quotas[2]["student_count"],
        "113": quotas[3]["student_count"],
        "114": quotas[4]["student_count"]
    })

df_years = pd.DataFrame(years_records)
print(f"歷年名額資料筆數: {len(df_years)}筆")
df_years.to_csv("admi_thro_years_normalized.csv", index=False)

