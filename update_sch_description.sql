-- 自動產生：更新sch_description資料表中的note欄位，只加計畫類別
UPDATE sch_description 
SET note = CASE

    ELSE note
END;
