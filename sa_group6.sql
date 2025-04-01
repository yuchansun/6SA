-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1:3306
-- 產生時間： 2025-03-31 14:56:42
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
-- 資料庫： `sa_group6`
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
(7, '人類發展學系', 'password415', '登入紀錄7', 'photo7.jpg', '小黃', '學生', 'xiaohuang@gmail.com');

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
-- 資料表結構 `school_introduction`
--

CREATE TABLE `school_introduction` (
  `SchoolName` varchar(20) NOT NULL,
  `Sch_Intro` varchar(255) DEFAULT NULL,
  `Dep_num` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `school_introduction`
--

INSERT INTO `school_introduction` (`SchoolName`, `Sch_Intro`, `Dep_num`) VALUES
('中山大學', '中山大學在國際學術界有良好的口碑，尤其在國際關係方面。', 8),
('台灣大學', '台灣大學是台灣最具聲望的大學之一，提供多元的學科與研究領域。', 15),
('國防大學', '國防大學是專門培養國軍指揮官的軍事院校，培養精英指揮人才。', 3),
('國防醫學院', '國防醫學院致力於國軍醫療與健康服務的發展，並提供優質的醫學與生物醫學科學教育。', 5),
('成功大學', '成功大學有豐富的工程及科學學科，並致力於創新研究。', 10),
('東華大學', '東華大學是一所重視人文與社會科學的學校，提供多樣化的學術領域。', 7),
('清華大學', '清華大學是台灣頂尖的學術機構，注重創新與國際合作。', 12);

-- --------------------------------------------------------

--
-- 資料表結構 `sch_description`
--

CREATE TABLE `sch_description` (
  `Sch_num` varchar(20) NOT NULL,
  `School_Name` varchar(20) NOT NULL,
  `Department` varchar(20) NOT NULL,
  `Region` varchar(10) NOT NULL,
  `Disc_Cluster` varchar(10) NOT NULL,
  `Schol_Apti` varchar(10) DEFAULT NULL,
  `Talent` varchar(10) DEFAULT NULL,
  `ID` varchar(10) DEFAULT NULL,
  `Plan` varchar(10) DEFAULT NULL,
  `Quota` int(11) NOT NULL,
  `Contact` varchar(225) DEFAULT NULL,
  `link` varchar(225) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `sch_description`
--

INSERT INTO `sch_description` (`Sch_num`, `School_Name`, `Department`, `Region`, `Disc_Cluster`, `Schol_Apti`, `Talent`, `ID`, `Plan`, `Quota`, `Contact`, `link`) VALUES
('S001', '國防醫學院', '醫學系', '台北', '醫學類', '臨床興趣', '醫療技能', '學生', '全日制', 30, '02-12345678', 'www.ndm.edu.tw'),
('S002', '國防大學', '國際事務學系', '台北', '國際關係', '外交興趣', '領導能力', '學生', '全日制', 25, '02-23456789', 'www.ndu.edu.tw'),
('S003', '台灣大學', '資訊工程學系', '台北', '工程學群', '程式設計興趣', '邏輯推理', '學生', '全日制', 40, '02-34567890', 'www.ntu.edu.tw'),
('S004', '清華大學', '電子工程學系', '新竹', '科技學群', '電子興趣', '數學能力', '學生', '全日制', 35, '03-45678901', 'www.nthu.edu.tw'),
('S005', '中山大學', '國際事務學系', '高雄', '國際關係', '外交興趣', '組織管理', '學生', '全日制', 20, '07-56789012', 'www.nsysu.edu.tw'),
('S006', '成功大學', '機械工程學系', '台南', '工程學群', '機械設計興趣', '創新能力', '學生', '全日制', 50, '06-67890123', 'www.ncku.edu.tw'),
('S007', '東華大學', '人類發展學系', '花蓮', '社會學群', '心理學興趣', '溝通技巧', '學生', '全日制', 15, '03-78901234', 'www.dhu.edu.tw');

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

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`User_ID`);

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
-- 資料表索引 `to_do_items`
--
ALTER TABLE `to_do_items`
  ADD PRIMARY KEY (`Sch_num`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `account`
--
ALTER TABLE `account`
  MODIFY `User_ID` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `discussion_area`
--
ALTER TABLE `discussion_area`
  MODIFY `Discussion_forum_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `message_area`
--
ALTER TABLE `message_area`
  MODIFY `Message_area_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `discussion_area`
--
ALTER TABLE `discussion_area`
  ADD CONSTRAINT `discussion_area_ibfk_1` FOREIGN KEY (`Message_area_id`) REFERENCES `message_area` (`Message_area_id`),
  ADD CONSTRAINT `discussion_area_ibfk_2` FOREIGN KEY (`User_id`) REFERENCES `account` (`User_ID`);

--
-- 資料表的限制式 `message_area`
--
ALTER TABLE `message_area`
  ADD CONSTRAINT `message_area_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `account` (`User_ID`);

--
-- 資料表的限制式 `my_favorites`
--
ALTER TABLE `my_favorites`
  ADD CONSTRAINT `my_favorites_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `account` (`User_ID`),
  ADD CONSTRAINT `my_favorites_ibfk_2` FOREIGN KEY (`Sch_num`) REFERENCES `sch_description` (`Sch_num`);

--
-- 資料表的限制式 `sch_description`
--
ALTER TABLE `sch_description`
  ADD CONSTRAINT `sch_description_ibfk_1` FOREIGN KEY (`School_Name`) REFERENCES `school_introduction` (`SchoolName`);

--
-- 資料表的限制式 `to_do_items`
--
ALTER TABLE `to_do_items`
  ADD CONSTRAINT `to_do_items_ibfk_1` FOREIGN KEY (`Sch_num`) REFERENCES `sch_description` (`Sch_num`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
