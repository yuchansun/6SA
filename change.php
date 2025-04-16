<?php
// 新增 posts 表
CREATE TABLE `posts` (
  `Post_ID` INT AUTO_INCREMENT PRIMARY KEY,
  `Title` VARCHAR(255) NOT NULL,
  `Content` TEXT NOT NULL,
  `User_ID` INT NOT NULL,
  `Post_Time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Likes` INT DEFAULT 0,
  FOREIGN KEY (`User_ID`) REFERENCES `account`(`User_ID`)
);

// 新增 comments 表
CREATE TABLE `comments` (
  `Comment_ID` INT AUTO_INCREMENT PRIMARY KEY,
  `Post_ID` INT NOT NULL,
  `User_ID` INT NOT NULL,
  `Content` TEXT NOT NULL,
  `Comment_Time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Likes` INT DEFAULT 0,
  FOREIGN KEY (`Post_ID`) REFERENCES `posts`(`Post_ID`),
  FOREIGN KEY (`User_ID`) REFERENCES `account`(`User_ID`)
);
?>