-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-04-18 12:33:16
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `sa-6`
--

-- --------------------------------------------------------

--
-- 資料表結構 `account`
--

CREATE TABLE `account` (
  `User_ID` int(20) NOT NULL,
  `My_favourite` varchar(100) DEFAULT NULL,
  `Password` varchar(60) NOT NULL,
  `Record` varchar(255) DEFAULT NULL,
  `Photo` varchar(100) NOT NULL,
  `Nickname` varchar(60) NOT NULL,
  `Roles` varchar(15) NOT NULL,
  `E-mail` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `account`
--

INSERT INTO `account` (`User_ID`, `My_favourite`, `Password`, `Record`, `Photo`, `Nickname`, `Roles`, `E-mail`) VALUES
(1, '醫學系', 'password123', '登入紀錄1', 'photo1.jpg', '小明', '學生', 'xiaoming@gmail.com'),
(2, '國際事務學系', 'password456', '登入紀錄2', 'photo2.jpg', '小王', '學生', 'xiaowang@gmail.com'),
(3, '資訊工程學系', 'password789', '登入紀錄3', 'photo3.jpg', '小李', '學生', 'xiaoli@gmail.com'),
(4, '電子工程學系', 'password101', '登入紀錄4', 'photo4.jpg', '小張', '學生', 'xiaozhang@gmail.com'),
(5, '國際事務學系', 'password112', '登入紀錄5', 'photo5.jpg', '小陳', '學生', 'xiaohen@gmail.com'),
(6, '機械工程學系', 'password131', '登入紀錄6', 'photo6.jpg', '小李', '學生', 'xiaoli2@gmail.com'),
(7, '人類發展學系', 'password415', '登入紀錄7', 'photo7.jpg', '小黃', '學生', 'xiaohuang@gmail.com'),
(20, NULL, 'aaa', NULL, '', '小瑞', '學生', 'andryco035@gmail.com');

-- --------------------------------------------------------

--
-- 資料表結構 `admi_thro_years`
--

CREATE TABLE `admi_thro_years` (
  `sch_num` varchar(20) NOT NULL,
  `school` varchar(20) NOT NULL,
  `dep` varchar(20) NOT NULL,
  `110` int(80) NOT NULL,
  `111` int(80) NOT NULL,
  `112` int(80) NOT NULL,
  `113` int(80) NOT NULL,
  `114` int(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `admi_thro_years`
--

INSERT INTO `admi_thro_years` (`sch_num`, `school`, `dep`, `110`, `111`, `112`, `113`, `114`) VALUES
('S001', '國防醫學院', '醫學系', 15, 16, 13, 11, 11),
('S002', '國防大學', '國際事務', 8, 5, 3, 3, 6),
('S003', '台灣大學', '資工', 18, 15, 12, 10, 9),
('S004', '清華大學', '電子工程', 6, 9, 4, 7, 7),
('S005', '中山大學', '國際事務', 1, 5, 6, 5, 8),
('S006', '成功大學', '機械工程', 5, 3, 3, 5, 2);

-- --------------------------------------------------------

--
-- 資料表結構 `comments`
--

CREATE TABLE `comments` (
  `Comment_ID` int(11) NOT NULL,
  `Post_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Content` text NOT NULL,
  `Comment_Time` datetime NOT NULL DEFAULT current_timestamp(),
  `Likes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `comments`
--

INSERT INTO `comments` (`Comment_ID`, `Post_ID`, `User_ID`, `Content`, `Comment_Time`, `Likes`) VALUES
(9, 8, 7, '老實說，你很多獎沒錯，絕對可以過書審取得報明資格，但再能不能進入面試階段很難說，因為我看不出這些獎項證明了你是哪方面的特殊人才，簡單的說你有廣度但沒有深度，這樣很難勝出', '2025-04-18 18:04:31', 0),
(12, 10, 7, '恭喜你！也辛苦了！特選這條路真的蠻累但是很值得！', '2025-04-18 18:08:29', 0),
(13, 12, 7, '雖然不是廣電ㄉ但還是跟你們說聲加油！', '2025-04-18 18:08:45', 0),
(14, 9, 7, '特殊選才有面試的話就把面試準備好，如果教授們喜歡你，你就一定上了！如果只要書面成績的話，就把對自己有利的東西都放上去，然後自傳也打好看一點，如果沒上就當只是替自己個申準備就好', '2025-04-18 18:09:22', 0),
(16, 8, 1, '好強 但可能沒啥用', '2025-04-18 18:23:59', 0),
(17, 12, 1, '這邊也是今年高職生投世新廣電🙋‍♂️ 準備書審那時候整個蠟燭兩頭燒⋯', '2025-04-18 18:24:13', 0),
(19, 9, 1, '低收+單親+新住民才是重點 其他可以不用 確定不考慮台大嗎？', '2025-04-18 18:24:56', 0),
(20, 12, 5, '哈嘍哈嘍，我也是這屆有報特殊選才的普高三牲！我是報公廣系的，我也是沒有特殊身分的，但有舉辦大型活動的經驗醬，所以剛剛好符合資格，我也好緊張啊啊～希望我們可以一起成為那個被選中的幸運鵝～', '2025-04-18 18:26:15', 0),
(21, 11, 5, '同樣是特選生！我是上頭上這間的不分系>< 必須感嘆你分享得好詳盡，很認同特選的甘苦談！謝謝你的用心分享，最後想特別跟你說辛苦了，特選的準備真的很不容易～🥹', '2025-04-18 18:26:30', 0),
(22, 10, 5, '哈囉我也是劇場設計的，要認識一下嗎', '2025-04-18 18:27:11', 0),
(23, 8, 5, '證照、幹部稍微不會加分', '2025-04-18 18:27:56', 0),
(24, 8, 5, '不是 你也做太多事了吧 我都分不出來哪一個有用哪一個沒用', '2025-04-18 18:29:04', 0),
(25, 9, 5, '我自己有看過動機的特殊選才跟電資學院的特殊選才大佬 那些人無一不是參加過很多國內外的比賽的選手 所以如果要上一些財金 假定是計財的話 應該也是需要很多對金融的興趣 抑或是對跨領域有熱忱 如果他的備審沒看出以上兩者的任何關聯 應該就沒機會了吧', '2025-04-18 18:29:24', 0),
(26, 12, 4, '別緊張 好好讀學測統測比較實在', '2025-04-18 18:30:23', 0);

-- --------------------------------------------------------

--
-- 資料表結構 `discussion_area`
--

CREATE TABLE `discussion_area` (
  `Discussion_forum_id` int(20) NOT NULL,
  `Message_area_id` int(20) NOT NULL,
  `User_id` int(20) NOT NULL,
  `Content` varchar(225) NOT NULL,
  `OrderTime` datetime NOT NULL,
  `Title` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `discussion_area`
--

INSERT INTO `discussion_area` (`Discussion_forum_id`, `Message_area_id`, `User_id`, `Content`, `OrderTime`, `Title`) VALUES
(1, 1, 1, '這個科系非常有潛力，學校提供很多實習機會。', '2025-03-31 09:00:00', '科系討論'),
(2, 2, 2, '對於這個科系的就業前景，我有一些疑問。', '2025-03-31 10:00:00', '科系討論'),
(3, 3, 3, '學校的課程設計有很多實作機會，值得推薦。', '2025-03-31 11:00:00', '課程討論'),
(4, 4, 4, '這所學校的環境很好，學習氛圍很正向。', '2025-03-31 12:00:00', '學校環境討論'),
(5, 5, 5, '教授很有經驗，教學風格也很棒。', '2025-03-31 13:00:00', '教授討論'),
(6, 6, 6, '學校有很多國際交流的機會，對未來發展有很大幫助。', '2025-03-31 14:00:00', '國際交流討論'),
(7, 7, 7, '學校的學生會非常活躍，對學生的幫助很大。', '2025-03-31 15:00:00', '學生會討論');

-- --------------------------------------------------------

--
-- 資料表結構 `disc_cluster_introduction`
--

CREATE TABLE `disc_cluster_introduction` (
  `Disc_Cluster_name` varchar(20) NOT NULL,
  `Name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `disc_cluster_introduction`
--

INSERT INTO `disc_cluster_introduction` (`Disc_Cluster_name`, `Name`) VALUES
('人文學群', '人文學群包括語言、歷史、哲學等學科。'),
('國際關係', '國際關係學群涵蓋外交、國際事務、國際貿易等科系。'),
('工程學群', '工程學群包含資訊工程、電子工程、機械工程等科系。'),
('社會學群', '社會學群涵蓋心理學、人類發展、社會學等學科。'),
('科技學群', '科技學群包括電子、資訊、生物科技等領域的學科。'),
('管理學群', '管理學群包括企業管理、財務管理、人力資源管理等。'),
('醫學類', '醫學類學群包括醫學、牙醫、藥學等相關科系。');

-- --------------------------------------------------------

--
-- 資料表結構 `latest news`
--

CREATE TABLE `latest news` (
  `title` varchar(20) NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `links` varchar(2083) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `latest news`
--

INSERT INTO `latest news` (`title`, `content`, `links`) VALUES
('114特殊選才簡章更新完畢！', '今年特殊選才簡章已經公告完畢，台大、清大、政大名額增加，特殊選才資格門檻降低！114特殊選才共59所大學參與，核定名額達到2,156名！114特殊選才共計有29個校系資安外加名額，對資安有興趣的同學別錯過利用特殊選才免試入學的機會！', 'https://www.reallygood.com.tw/newExam/inside?str=932DEFBF9A06471E3A1436C3808D1BB7'),
('114特殊選才簡章更新完畢！', '今年特殊選才簡章已經公告完畢，台大、清大、政大名額增加，特殊選才資格門檻降低！114特殊選才共59所大學參與，核定名額達到2,156名！114特殊選才共計有29個校系資安外加名額，對資安有興趣的同學別錯過利用特殊選才免試入學的機會！', 'https://www.reallygood.com.tw/newExam/inside?str=932DEFBF9A06471E3A1436C3808D1BB7');

-- --------------------------------------------------------

--
-- 資料表結構 `latest_news`
--

CREATE TABLE `latest_news` (
  `title` varchar(35) NOT NULL,
  `content` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `latest_news`
--

INSERT INTO `latest_news` (`title`, `content`, `link`) VALUES
('114 特殊選才簡章最新公告！全網最完整書審面試、考古題整理', '2024 年特殊選才即將開始，各個學校也陸陸續續公布特殊選才簡章。這篇文章幫大家蒐集114 年特殊選才簡章，分享學長姐們的準備方式，另外也幫大家整理出幾個特殊選才社群資源及網路上實用的資源、特殊選才考古題等，以幫助你在書審及面試中取得最佳成績。', 'https://blog.luckertw.com/114-special-recruit/'),
('114特殊選才簡章更新完畢！', '今年特殊選才簡章已經公告完畢，114特殊選才共59所大學參與，核定名額達到2,156名！114特殊選才共計有29個校系資安外加名額，對資安有興趣的同學別錯過利用特殊選才免試入學的機會！', 'https://www.reallygood.com.tw/newExam/inside?str=932DEFBF9A06471E3A1436C3808D1BB7');

-- --------------------------------------------------------

--
-- 資料表結構 `likes`
--

CREATE TABLE `likes` (
  `Like_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Post_ID` int(11) DEFAULT NULL,
  `Comment_ID` int(11) DEFAULT NULL,
  `Like_Time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `message_area`
--

CREATE TABLE `message_area` (
  `Message_area_id` int(20) NOT NULL,
  `Content` varchar(225) NOT NULL,
  `User_id` int(20) NOT NULL,
  `OrderTime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `message_area`
--

INSERT INTO `message_area` (`Message_area_id`, `Content`, `User_id`, `OrderTime`) VALUES
(1, '我對這個科系很有興趣！', 1, '2025-03-31 09:00:00'),
(2, '這所學校的環境很棒！', 2, '2025-03-31 10:00:00'),
(3, '有沒有人了解這個科系的就業前景？', 3, '2025-03-31 11:00:00'),
(4, '這裡的學長姐都很友善，推薦來讀！', 4, '2025-03-31 12:00:00'),
(5, '有沒有學長姐願意分享學習經驗？', 5, '2025-03-31 13:00:00'),
(6, '這所學校的教授很專業！', 6, '2025-03-31 14:00:00'),
(7, '這所學校的課程設計很實用！', 7, '2025-03-31 15:00:00');

-- --------------------------------------------------------

--
-- 資料表結構 `my_favorites`
--

CREATE TABLE `my_favorites` (
  `User_ID` int(20) NOT NULL,
  `Sch_num` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `my_favorites`
--

INSERT INTO `my_favorites` (`User_ID`, `Sch_num`) VALUES
(1, 'S001'),
(1, 'S002'),
(2, 'S003'),
(2, 'S004'),
(3, 'S005'),
(3, 'S006'),
(4, 'S007');

-- --------------------------------------------------------

--
-- 資料表結構 `posts`
--

CREATE TABLE `posts` (
  `Post_ID` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Content` text NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Post_Time` datetime NOT NULL DEFAULT current_timestamp(),
  `Likes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `posts`
--

INSERT INTO `posts` (`Post_ID`, `Title`, `Content`, `User_ID`, `Post_Time`, `Likes`) VALUES
(8, '#升學 特殊選才', '想請問各位學長姐，不分系的特殊選才經歷，以我這樣的資料有機會通過第一階段嗎？ 想挑戰清大不分系/交大不分系/成大不分系 高中三年多元成果 👉2024韓國世界發明創新競賽(WICO) 1.發明金牌獎 2.大會特別獎 3.青少年優等獎的創下賽事三冠王 👉TEDxYouth@MingdaoHS第九屆講者 •校排前1%（高職） •全國CTDSF國標舞國手選拔賽冠軍 •寰宇走讀 閱讀台灣計畫 • 111學年度台中市模範生 • 金馬獎60-61屆聯名商品文案設計 • 金馬60-61屆聯名商品商品設計 • 2023明道文學獎佳作 • 2024新加坡萊佛士學術深度交流 • 班級幹部：班長/副班長/風紀 股長 •學生會公關行銷組 • 新竹玻璃工藝博物館藝術展國際志工 • 228國際標準舞世界亞洲巡迴賽 志工 • 2023全國林燈盃英文簡報大賽 優等 • 校內英文演講比賽 冠軍 • 吉他社聯展 • 2023明道之星 季軍 • 2023全國新農業行銷競賽 佳作 • 班聯會組員 • 校內作文競賽 佳作 • 商管群學生會 • 明道招生廣告 • 霧峰區農會100周年慶開幕表演 • 112學年度商管群聖誕企劃提案競賽 亞軍 • 112學年度商業資料蒐集比賽 冠軍 • 112學年度嶺東科技大學 會計專業能力競賽 優勝 • 2024成年禮禮生 • 企業實習 • 2024年第七屆臺中市英文菁英盃 專業英文聽力與詞彙能力大賽 資訊類-金手獎 檢定證照： • 會計資訊 • 會計人工 • 會計三級 • 丙級軟體 • Excel證照 • PPT證照 • Word證照 • TQC數字輸入實用級 • TQC 中文輸入實用級 • PVQC 資訊-專業級 • 全民英檢中級初試 • TOEIC 775分', 5, '2025-04-18 17:45:11', 4),
(9, '關於特殊選才...', '差我10歲的弟弟目前是高三生(學測生)，他正在煩惱他目前該做些什麼，他也跟我討論過他決定用用看特殊選才 想問大家關於特選有什麼建議，我當初是考指考上去的，關於現在的招生狀況也不太了解，不太敢給老弟一些可能是錯的建議 但我還是覺得我弟對特殊選才的定義是不是有點會錯意，畢竟是“特殊才能”，我也有跟他說過你還是乖乖用個申好了，他卻說試試看就知道了， 反正用過就有機會 我弟弟是低收、單親又新住民子女(直接講不知道適不適當，他居然覺得他有這三個條件又加上他那幾張感覺不起眼的獎狀(一兩張管樂比賽和幾張營隊活動)，可以上得了他想要的好學校==他想要讀財金系特別是中山其次就是私立的淡江逢甲BTW.不是伸手族，沒有要你打範例給我看，只是想知道特殊選才的備審內容大概的方向，謝謝各位大大🙏🏻🙏🏻', 1, '2025-04-18 17:50:20', 3),
(10, '113北藝大劇場設計特殊選才分享', '有鑑於網路上劇設特殊選才的相關資訊不多，因此來分享自己的經驗供想報考的學弟妹參考 特選其實一開始沒有在我的計畫內（原本是打算報獨招&個申），是簡章出來的時候看到自己剛好符合資格才想說試看看的，也很幸運的考上了～ 特選的時間其實比較早，而且時間上也會跟學測衝刺期撞到，建議有想走特選的大家可以儘早準備，才不會跟我一樣累個半死🌟申請資格 比賽的話我是用「全國學生戲劇比賽 全國賽優等」的資格去報的，我們學校組隊算是半地社的形式 另外我還有參加過「青少年花樣戲劇節」，這個比賽是可以單人報名然後跨校組隊的～如果學校本身資源相對不夠的話還蠻推薦參加花樣的，可以累積比賽經驗也有免費的課程可以上 🌟作品集 作品集因為是臨時決定要報特選所以做得滿趕的，老實説我自己沒有很滿意🤒 我自己是把作品集切成 個人經歷/立體作品/平面作品/活動經驗 這幾部分 ➡️個人經歷： 就是寫自我介紹、經歷、就讀動機等等簡章上面要求的內容 ➡️立體作品： 我放了三個作品，分別是紙窗花+燈光、類似立體繪本的手提箱、紙雕，而手提箱是我帶去面試的作品 ➡️平面作品： 一些平常累積的作品跟社團美宣的東西 ➡️活動經驗： 我自己是放夏日戲劇學校的活動經驗跟舞台劇欣賞心得，心得的部分我有特地挑比較不同類型的劇來寫 教授有說我沒寫學生戲劇比賽的部分蠻可惜的，所以如果考特選應該儘量要把自己報考時符合資格的比賽經驗拿來寫！ 🌟面試 ➡️服裝： 我是穿我自己習慣的衣服（襯衫洋裝+長褲），原本有想要去買正式一點的衣服但被畫室老師駁回ㄌ，事實上面試當天大家也都是穿習慣的衣服居多，不要太邋遢就好～～ ➡️面試內容： 特選面試順序是直接照著准考證號排的（然後准考證號應該是按報名順序排） 考生會在同一個教室等叫號 ⭢叫到後帶著東西在走廊等 ⭢進去面試間 ⭢結束後拿東西直接離開 Q：你應該很緊張吧？不要緊張我們深呼吸喔～準備好就可以開始自我介紹 A：自我介紹 Q：看你是台中女中的，我們之前也有面試過你的學姐，他們都表現得還不錯（謝謝校名加持？！） 然後印象中教授有問說我們學校是不是對課外活動或藝文相關沒這麼重視 A：對，我們學校比較不會主動放資源在這塊上面，但是因為校風相對自由所以大家其實多少都會玩社團或是參與一些活動，如果學生主動申請學校還是多少會給一些幫助 Q：戲劇比賽相關的細節、資金籌措、學校有沒有給資源 A：比賽主要是我們學生自己在處理的，雖然是地下社團但社團組長會幫我們一些忙，資金部分我們一開始以為要自己出錢但後來發現可以請家長會贊助 Q：因為特選不看學測成績所以我們會比較注重你的在校成績，看你附上的成績單，你的國英數成績怎麼只有六十幾分 A：（被問到成績有點意外所以有稍微猶豫一下要怎麼回答，最後選擇誠實講）因為我花蠻多時間在玩課外活動的，加上因為我們學校排名比較前面所以考題通常會出的比較難一點，因此成績沒這麼好看 Q：喔但我們有看到你後來高二成績有比較上來（謝謝教授給我台階下XD） 之後會請你介紹你帶來的作品，我在介紹的時候有說這個作品靈感取材自生活中的哪些經驗（立體繪本、TRPG相關） Q：你的理念跟想法很好，作品看起來怎樣其實相對其次（因為當時為了趕特選其實作品完成度不算很高） Q：（這部分我不太記得確切的內容了）你作品集的作品其實都是比較近期的，你是有去畫室畫圖嗎、你平時會畫圖嗎/畫什麼樣的圖 A：對我有為了準備作品去畫室、平常畫的東西比較偏人物類 Q：看你也有報名獨招，如果特選沒上獨招會來嗎 A：（很斬釘截鐵的說）會！ 可能還有一些細節記的比較不清楚了QQ，但面試大致上是這樣 ➡️個人心得： 在準備面試時我有把基本題（會想選哪一組、最近有沒有看什麼舞台劇）的答案都想好也有演練過，但就事後來看幾乎沒被問到🤣不過我自認臨場應變還算可以，所以蠻多問題也是想到什麼講什麼 教授人很好不會很嚴肅！其實比較算是在聊天的感覺，問題的話我覺得就儘量誠實回答就好 以及原本事後想說毫不猶豫的回答獨招會一定再來，會不會讓教授想說反正這個人還會再出現就先把我刷掉，但就結果看來應該是沒什麼影響（？） - 我自己最後的成績是 書審資料：94.13 ➡️百分比實得 37.65 面試：94.75➡️百分比實得 56.85 總成績：94.5➡️正取2 （最低錄取標準是92.85） 大概是這樣子！如果有想問的問題都可以問我，我會盡可能回答，希望能幫到大家的忙～祝大家考試順利！', 6, '2025-04-18 17:51:09', 0),
(11, '｜114特殊選才｜經驗分享｜', '目前（2025）我是一位高三學生，現在也臨近學測。過去我在參加了許多的競賽跟活動之後，得到了些許的成就跟結果，也因此我決定要走上「特選」這條路。 因為這陣子在學校時許多朋友都會用調侃的語氣稱我大學生，一開始可能會有些不太舒服，但久了其實也就沒什麼差了😌畢竟這也是我付出了百節公假、假日還有睡眠時間等等換來的成果。 特選這條路真的就比較輕鬆嗎？ 其實完全不是大眾所想的那樣 生活中和我比較要好的朋友和師長能大概了解我在過去的生活是如何 這邊可能我先介紹一下自己！ 我整理了一些我的經歷 1.特殊選才報名的校系及放榜結果 2.我的個人經歷簡介 先大概介紹一下特選的制度 書審（一階） -> 面試(二階）這裡部分校系另有筆試 -> 放榜 1.特殊選才報名的校系及放榜結果（北～南排序） * 國立臺灣海洋大學-機械與機電工程學系 ->進入面試，但放棄 * 國立中央大學-土木工程學系 -> 正取 * 國立清華大學-動力機械工程學系 -> 正取 * 國立清華大學-清華學院學士班甲組 -> 未進入面試 * 國立中興大學-土木工程學系 -> 備取1 * 國立中山大學-機械與機電工程學系 -> 未進入面試 2.我的個人經歷簡介 科學研究： 國際： * 2024年 臺灣國際科學展覽會 工程學科-三等獎（未選上出國代表） 全國： * 2021年 START! 智慧小車競賽 參賽 * 2021年 全國海洋能源創意實作競賽 亞軍 * 2022年 全國海洋能源創意實作競賽 亞軍 * 2022年 START! 智慧小車競賽 特優 * 2023年 全國海洋大數據競賽 佳作 * 2023年 永續能源創意實作競賽 入選參賽 * 2024年 全國高中職創意發明競賽 優等獎 * 2024年 全國海洋能源創意實作競賽 亞軍 * 2024年 永續能源創意實作競賽 銀牌獎 * 2024年 全國海洋大數據競賽 佳作 校內： * 111學年 校內數理實驗班成果發表 第二名 * 112學年 校內數理實驗班成果發表 簡報組 第二名 * 112學年 校內數理實驗班成果發表 海報組 第一名 特殊經歷： * 112年 青少年科學人才培育計畫 (2023/5~10) * 112年度 基隆市氣候行動青年公民營 (2023/7) * 112學年 總成績全校前20% * 2024年 日本廣島吳市訪問團(2024/9) 語文素養： * 112學年 校內國語文競賽 朗讀組 第二名 * 基隆市國語文競賽 朗讀組 參賽 * 111學年 校內國語文競賽 朗讀組 第六名 幹部經歷： * 班長 (111學年上學期) * 漫研社公關 (112學年) * 歷史小老師 (111學年) 總結上述，大概可以看出我的經歷是比較側重在科學研究方面，其他領域就比較沒有那麼深入了解（這也大概率是不分系沒進面試的原因XD) 好回歸主題！ 其實準備這些競賽跟活動，也花費了我很多上課跟休息的時間。我在高二上學期時因為要準備科展，請了約300節的公假，也是那陣子跟班上同學變得比較疏遠…但這也因此更加凸顯出了我的不同。課餘的時間我進入大學實驗室學習建模軟體和模擬分析的軟體、學習如何撰寫計劃書、學習運用工具製作出機構等等，這些都是在高中課堂中不太會學習到的，更甚是我就讀的社區高中。 可能沒有親自體驗過，也沒辦法真的了解到其中的難處（這裡沒有否定其他人的努力！只是一般走特選這條路，普遍會收到一些不好的評論…），但各行各業都有自己辛苦的地方，想必認真讀書的各位一定也有遇到難題解不開或是成績提不上的困擾啦🥲每個人其實都是差不多的，在經歷了許多不同的困難，最終也會得到不同的結果！ 你還在猶豫要不要走特選這條路嗎？ 猶豫了代表就是想要！況且你已經付出了這麼多經歷才加競賽和活動，給自己一個機會去讓其他人看看自己的成果。 當然該讀的書還是要讀啦，我自己最初也是一邊準備學測，一邊準備備審資料的。特選就是，你比別人多了一個機會，所以別輕易的就放棄，但也別輕易的把其他機會給放掉！ 這邊也另外小小分享一下，我在準備特選時遇到的困難。 因為我就讀的是社區高中，學校沒有特別推特殊選才，也沒有太多的相關資源讓學生去了解這個管道。也因此在準備特選時，我甚至只和老師模擬面試過一次！ 但只能說我也挺幸運，在過去的競賽活動中認識了許多學長姐，他們幾乎都有經歷過特選這道關卡，也因此我去向他們尋求很多備審跟面試上的協助（這邊真的很感謝學長姐們的無私分享😭）；以及我認識到的許多同屆朋友們！在報名、準備備審時遇到什麼困難，還有面試時互相幫忙、加油。 真的要先感謝特選大家庭的溫暖，而這也促使我想讓更多人知道、發現特殊選才，也想幫助更多和我一樣身處在資源不足的環境下的學生們。 這部分其實是我在之前參加綠洲計畫活動的有感而發！學長姐們的一連串分享，還有其他的活動，真的讓我深深佩服他們。 這邊也可以給個小建議，如果你在猶豫要不要踏上特選這條路，不妨去找學長姐們聊聊，或者是多參加一些活動。很多時候，只要你靜下來好好思考，就會有答案！雖然如果是我的話，先去試就對了，不要等錯過了才來後悔。 - 其實這邊打到最後可能連我自己也不知道想表達什麼…但還是想讓其他人知道特殊選才的不易跟其他資訊！ 現在的我可能也不是特別的專業，但還是想盡一點我的微薄之力去幫助到一些人～ 有覺得哪裡不妥或是需要修改，還是有其他想詢問的都可以底下留言！ （第一次發文還是有點忐忑的，虛心接受任何的指教🙇） - 若有其他特選相關問題，歡迎來詢問！我會盡力回答的', 2, '2025-04-18 17:59:31', 0),
(12, '世新廣電特殊選才', '我是今年有報名世新廣電特選的高職生， 本身是媒體傳播班的，學習期間也有許多作品， 想問這邊有沒有學長姐是廣電特選成功上岸ㄉ⋯， 雖然距離寄出資料已經有一段時間了， 但還是想跟相關經驗的學長姐聊聊看 或是也有寄出資料的同屆也可以～ （真的好緊張\\r\\n', 7, '2025-04-18 18:00:20', 0);

-- --------------------------------------------------------

--
-- 資料表結構 `school_introduction`
--

CREATE TABLE `school_introduction` (
  `SchoolName` varchar(20) NOT NULL,
  `Sch_Intro` varchar(255) DEFAULT NULL,
  `Dep_num` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `school_type` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `school_introduction`
--

INSERT INTO `school_introduction` (`SchoolName`, `Sch_Intro`, `Dep_num`, `image_url`, `website`, `school_type`) VALUES
('中山大學', '中山大學在國際學術界有良好的口碑，尤其在國際關係方面。', 8, 'assets/img/school_image/中山大學.jpeg', 'https://www.nsysu.edu.tw', '國立'),
('台灣大學', '台灣大學是台灣最具聲望的大學之一，提供多元的學科與研究領域。', 15, 'assets/img/school_image/台灣大學新.jpeg', 'https://www.ntu.edu.tw', '國立'),
('國防大學', '國防大學是專門培養國軍指揮官的軍事院校，培養精英指揮人才。', 3, 'assets/img/school_image/國防大學.jpg', 'https://www.ndu.edu.tw', '國立'),
('國防醫學院', '國防醫學院致力於國軍醫療與健康服務的發展，並提供優質的醫學與生物醫學科學教育。', 5, 'assets/img/school_image/國防醫學院.jpg', 'https://www.ndmctsgh.edu.tw/', '國立'),
('成功大學', '成功大學有豐富的工程及科學學科，並致力於創新研究。', 10, 'assets/img/school_image/成功大學.png', 'https://www.ncku.edu.tw', '國立'),
('東華大學', '東華大學是一所重視人文與社會科學的學校，提供多樣化的學術領域。', 7, 'assets/img/school_image/東華大學.jpg', 'https://www.ndhu.edu.tw', '國立'),
('清華大學', '清華大學是台灣頂尖的學術機構，注重創新與國際合作。', 12, 'assets/img/school_image/清華大學.jpg', 'https://www.nthu.edu.tw', '國立');

-- --------------------------------------------------------

--
-- 資料表結構 `sch_description`
--

CREATE TABLE `sch_description` (
  `Sch_num` varchar(20) NOT NULL,
  `School_Name` varchar(20) NOT NULL,
  `p_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Department` varchar(20) NOT NULL,
  `Region` varchar(10) NOT NULL,
  `address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Disc_Cluster` varchar(10) NOT NULL,
  `requirement` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Exam_Item` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Talent` varchar(10) DEFAULT NULL,
  `ID` varchar(10) DEFAULT NULL,
  `Plan` varchar(10) DEFAULT NULL,
  `Quota` int(11) NOT NULL,
  `exam_date` date DEFAULT NULL,
  `Contact` varchar(225) DEFAULT NULL,
  `link` varchar(225) DEFAULT NULL,
  `note` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `sch_description`
--

INSERT INTO `sch_description` (`Sch_num`, `School_Name`, `p_type`, `Department`, `Region`, `address`, `Disc_Cluster`, `requirement`, `Exam_Item`, `Talent`, `ID`, `Plan`, `Quota`, `exam_date`, `Contact`, `link`, `note`) VALUES
('S001', '國防醫學院', '國立', '醫學系', '台北', '', '醫學類', '', '', '醫療技能', '學生', '全日制', 30, NULL, '02-12345678', 'www.ndm.edu.tw', NULL),
('S002', '國防大學', '國立', '國際事務學系', '台北', '', '國際關係', '', '', '領導能力', '學生', '全日制', 25, NULL, '02-23456789', 'www.ndu.edu.tw', NULL),
('S003', '台灣大學', '國立', '資訊工程學系', '台北', '', '工程學群', '', '', '邏輯推理', '學生', '全日制', 40, NULL, '02-34567890', 'www.ntu.edu.tw', NULL),
('S004', '清華大學', '國立', '電子工程學系', '新竹', '', '科技學群', '', '', '數學能力', '學生', '全日制', 35, NULL, '03-45678901', 'www.nthu.edu.tw', NULL),
('S005', '中山大學', '國立', '國際事務學系', '高雄', '', '國際關係', '', '', '組織管理', '學生', '全日制', 20, NULL, '07-56789012', 'www.nsysu.edu.tw', NULL),
('S006', '成功大學', '國立', '機械工程學系', '台南', '', '工程學群', '', '', '創新能力', '學生', '全日制', 50, NULL, '06-67890123', 'www.ncku.edu.tw', NULL),
('S007', '東華大學', '國立', '人類發展學系', '花蓮', '', '社會學群', '', '', '溝通技巧', '學生', '全日制', 15, NULL, '03-78901234', 'www.dhu.edu.tw', NULL),
('S008', '國防大學', '國立', '資管系', '桃園', '', '資訊學群', '', '', '資工', '一般', '特殊選才', 5, NULL, '0277777777777', 'https://www.ndu.edu.tw/', NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `todos`
--

CREATE TABLE `todos` (
  `todo_id` int(11) NOT NULL,
  `Sch_num` varchar(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `todos`
--

INSERT INTO `todos` (`todo_id`, `Sch_num`, `title`, `start_time`, `end_time`) VALUES
(1, 'S001', '登錄報名資料', '2024-11-08 09:00:00', '2024-11-22 17:00:00'),
(2, 'S001', '上傳審查資料', '2024-11-08 09:00:00', '2024-11-22 17:00:00'),
(3, 'S001', '繳費', '2024-11-08 09:00:00', '2024-11-22 17:00:00'),
(4, 'S001', '自行上網查詢初審是否合格', '2024-11-26 12:00:00', NULL),
(5, 'S001', '口試資格公告', NULL, NULL),
(6, 'S001', '准考證列印', NULL, NULL),
(7, 'S001', '口試', NULL, NULL),
(8, 'S001', '放榜', NULL, NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `to_do_items`
--

CREATE TABLE `to_do_items` (
  `Sch_num` varchar(20) NOT NULL,
  `Data_review` varchar(255) DEFAULT NULL,
  `Interview` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `to_do_items`
--

INSERT INTO `to_do_items` (`Sch_num`, `Data_review`, `Interview`) VALUES
('S001', '審查資料中', '安排面試'),
('S002', '資料已核准', '待安排面試'),
('S003', '資料審查中', '面試已安排'),
('S004', '資料已核准', '面試進行中'),
('S005', '資料審查中', '尚未安排面試'),
('S006', '資料已核准', '面試已完成'),
('S007', '資料審查中', '面試待定');

-- --------------------------------------------------------

--
-- 資料表結構 `user_todos`
--

CREATE TABLE `user_todos` (
  `id` int(11) NOT NULL,
  `user_id` int(20) NOT NULL,
  `todo_id` int(11) NOT NULL,
  `is_done` tinyint(1) DEFAULT 0,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `user_todos`
--

INSERT INTO `user_todos` (`id`, `user_id`, `todo_id`, `is_done`, `updated_at`) VALUES
(1, 21, 1, 0, '2025-04-17 23:41:32'),
(2, 21, 2, 0, '2025-04-17 23:41:32'),
(3, 21, 3, 0, '2025-04-17 23:41:32'),
(4, 21, 4, 0, '2025-04-17 23:41:32'),
(5, 21, 5, 0, '2025-04-17 23:41:32'),
(6, 21, 6, 0, '2025-04-17 23:41:32'),
(7, 21, 7, 0, '2025-04-17 23:41:32'),
(8, 21, 8, 0, '2025-04-17 23:41:32');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`User_ID`);

--
-- 資料表索引 `admi_thro_years`
--
ALTER TABLE `admi_thro_years`
  ADD PRIMARY KEY (`sch_num`);

--
-- 資料表索引 `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`Comment_ID`),
  ADD KEY `Post_ID` (`Post_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- 資料表索引 `discussion_area`
--
ALTER TABLE `discussion_area`
  ADD PRIMARY KEY (`Discussion_forum_id`),
  ADD KEY `Message_area_id` (`Message_area_id`),
  ADD KEY `User_id` (`User_id`);

--
-- 資料表索引 `disc_cluster_introduction`
--
ALTER TABLE `disc_cluster_introduction`
  ADD PRIMARY KEY (`Disc_Cluster_name`);

--
-- 資料表索引 `latest_news`
--
ALTER TABLE `latest_news`
  ADD PRIMARY KEY (`title`);

--
-- 資料表索引 `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`Like_ID`),
  ADD UNIQUE KEY `unique_user_post` (`User_ID`,`Post_ID`),
  ADD UNIQUE KEY `unique_user_comment` (`User_ID`,`Comment_ID`),
  ADD KEY `Post_ID` (`Post_ID`),
  ADD KEY `Comment_ID` (`Comment_ID`);

--
-- 資料表索引 `message_area`
--
ALTER TABLE `message_area`
  ADD PRIMARY KEY (`Message_area_id`),
  ADD KEY `User_id` (`User_id`);

--
-- 資料表索引 `my_favorites`
--
ALTER TABLE `my_favorites`
  ADD PRIMARY KEY (`User_ID`,`Sch_num`),
  ADD KEY `Sch_num` (`Sch_num`);

--
-- 資料表索引 `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`Post_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- 資料表索引 `school_introduction`
--
ALTER TABLE `school_introduction`
  ADD PRIMARY KEY (`SchoolName`);

--
-- 資料表索引 `sch_description`
--
ALTER TABLE `sch_description`
  ADD PRIMARY KEY (`Sch_num`),
  ADD KEY `School_Name` (`School_Name`);

--
-- 資料表索引 `todos`
--
ALTER TABLE `todos`
  ADD PRIMARY KEY (`todo_id`);

--
-- 資料表索引 `to_do_items`
--
ALTER TABLE `to_do_items`
  ADD PRIMARY KEY (`Sch_num`);

--
-- 資料表索引 `user_todos`
--
ALTER TABLE `user_todos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `todo_id` (`todo_id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `account`
--
ALTER TABLE `account`
  MODIFY `User_ID` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `comments`
--
ALTER TABLE `comments`
  MODIFY `Comment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `discussion_area`
--
ALTER TABLE `discussion_area`
  MODIFY `Discussion_forum_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `likes`
--
ALTER TABLE `likes`
  MODIFY `Like_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `message_area`
--
ALTER TABLE `message_area`
  MODIFY `Message_area_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `posts`
--
ALTER TABLE `posts`
  MODIFY `Post_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `todos`
--
ALTER TABLE `todos`
  MODIFY `todo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `user_todos`
--
ALTER TABLE `user_todos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `admi_thro_years`
--
ALTER TABLE `admi_thro_years`
  ADD CONSTRAINT `fk_sch_num` FOREIGN KEY (`sch_num`) REFERENCES `sch_description` (`Sch_num`);

--
-- 資料表的限制式 `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`Post_ID`) REFERENCES `posts` (`Post_ID`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `account` (`User_ID`);

--
-- 資料表的限制式 `discussion_area`
--
ALTER TABLE `discussion_area`
  ADD CONSTRAINT `discussion_area_ibfk_1` FOREIGN KEY (`Message_area_id`) REFERENCES `message_area` (`Message_area_id`),
  ADD CONSTRAINT `discussion_area_ibfk_2` FOREIGN KEY (`User_id`) REFERENCES `account` (`User_ID`);

--
-- 資料表的限制式 `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `account` (`User_ID`),
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`Post_ID`) REFERENCES `posts` (`Post_ID`),
  ADD CONSTRAINT `likes_ibfk_3` FOREIGN KEY (`Comment_ID`) REFERENCES `comments` (`Comment_ID`);

--
-- 資料表的限制式 `sch_description`
--
ALTER TABLE `sch_description`
  ADD CONSTRAINT `sch_description_ibfk_1` FOREIGN KEY (`School_Name`) REFERENCES `school_introduction` (`SchoolName`);

--
-- 資料表的限制式 `user_todos`
--
ALTER TABLE `user_todos`
  ADD CONSTRAINT `user_todos_ibfk_1` FOREIGN KEY (`todo_id`) REFERENCES `todos` (`todo_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
