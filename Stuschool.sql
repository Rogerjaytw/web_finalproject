-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： localhost:8889
-- 產生時間： 2024 年 12 月 19 日 11:56
-- 伺服器版本： 8.0.35
-- PHP 版本： 8.2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `Stuschool`
--

-- --------------------------------------------------------

--
-- 資料表結構 `admin`
--

CREATE TABLE `admin` (
  `eid` char(4) NOT NULL,
  `psw` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 傾印資料表的資料 `admin`
--

INSERT INTO `admin` (`eid`, `psw`) VALUES
('E001', '123'),
('E002', '234'),
('E003', '345'),
('E004', '456'),
('E005', '567');

-- --------------------------------------------------------

--
-- 資料表結構 `classes`
--

CREATE TABLE `classes` (
  `sid` char(4) NOT NULL,
  `c_no` char(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `courses`
--

CREATE TABLE `courses` (
  `c_no` char(5) NOT NULL,
  `title` varchar(30) DEFAULT NULL,
  `credits` int DEFAULT NULL,
  `semester` varchar(4) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 傾印資料表的資料 `courses`
--

INSERT INTO `courses` (`c_no`, `title`, `credits`, `semester`, `day_of_week`, `start_time`, `end_time`) VALUES
('CS101', '計算機概論', 3, '1121', 'Monday', '09:00:00', '12:00:00'),
('CS102', '程式設計', 3, '1121', 'Tuesday', '13:00:00', '16:00:00'),
('CS104', '網頁設計', 3, '1121', 'Monday', '09:00:00', '12:00:00'),
('CS201', '資料結構', 3, '1121', 'Wednesday', '09:00:00', '12:00:00'),
('CS202', '資料庫系統', 3, '1121', 'Thursday', '13:00:00', '16:00:00'),
('CS301', '演算法', 3, '1121', 'Friday', '09:00:00', '12:00:00');

-- --------------------------------------------------------

--
-- 資料表結構 `enrollments`
--

CREATE TABLE `enrollments` (
  `sid` char(4) NOT NULL,
  `c_no` char(5) NOT NULL,
  `semester` varchar(4) NOT NULL,
  `status` enum('已選','已退') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 傾印資料表的資料 `enrollments`
--

INSERT INTO `enrollments` (`sid`, `c_no`, `semester`, `status`) VALUES
('0001', 'CS101', '1121', '已選'),
('0001', 'CS301', '1121', '已選'),
('0002', 'CS101', '1121', '已選'),
('0002', 'CS201', '1121', '已選'),
('0003', 'CS102', '1121', '已選'),
('0004', 'CS104', '1121', '已選'),
('0005', 'CS101', '1121', '已選'),
('0005', 'CS102', '1121', '已選');

-- --------------------------------------------------------

--
-- 資料表結構 `instructors`
--

CREATE TABLE `instructors` (
  `eid` char(4) NOT NULL,
  `name` varchar(12) DEFAULT NULL,
  `tel` varchar(15) DEFAULT NULL,
  `job_rank` varchar(10) DEFAULT NULL,
  `department` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 傾印資料表的資料 `instructors`
--

INSERT INTO `instructors` (`eid`, `name`, `tel`, `job_rank`, `department`) VALUES
('E001', '張一', '0911111111', '教授', 'CS'),
('E002', '李二', '0922222222', '副教授', 'CS'),
('E003', '王三', '0933333333', '助理教授', 'CS'),
('E004', '陳四', '0944444444', '教授', 'CS'),
('E005', '林五', '0955555555', '副教授', 'CS');

-- --------------------------------------------------------

--
-- 資料表結構 `instructor_courses`
--

CREATE TABLE `instructor_courses` (
  `eid` char(4) NOT NULL,
  `c_no` char(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 傾印資料表的資料 `instructor_courses`
--

INSERT INTO `instructor_courses` (`eid`, `c_no`) VALUES
('E001', 'CS101'),
('E002', 'CS102'),
('E004', 'CS104'),
('E003', 'CS201'),
('E004', 'CS202'),
('E002', 'CS301'),
('E005', 'CS301');

-- --------------------------------------------------------

--
-- 資料表結構 `record`
--

CREATE TABLE `record` (
  `r_id` int NOT NULL,
  `sid` char(4) DEFAULT NULL,
  `award_type` enum('嘉獎','小功','大功','警告','小過','大過') DEFAULT NULL,
  `award_date` datetime DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 傾印資料表的資料 `record`
--

INSERT INTO `record` (`r_id`, `sid`, `award_type`, `award_date`, `description`, `quantity`) VALUES
(1, '0001', '嘉獎', '2023-10-15 00:00:00', '擔任班級幹部表現優良', 2),
(2, '0002', '小功', '2023-11-20 00:00:00', '參與校外比賽獲獎', 1),
(3, '0003', '警告', '2023-12-01 00:00:00', '上課遲到', 1),
(4, '0001', '大功', '2024-01-10 00:00:00', '參與國際競賽獲得佳作', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `students`
--

CREATE TABLE `students` (
  `sid` char(4) NOT NULL,
  `name` varchar(12) DEFAULT NULL,
  `tel` varchar(15) DEFAULT NULL,
  `birthday` datetime DEFAULT NULL,
  `GPA` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 傾印資料表的資料 `students`
--

INSERT INTO `students` (`sid`, `name`, `tel`, `birthday`, `GPA`) VALUES
('0001', 'Roger', '0912345567', '2005-04-08 00:00:00', 87.6667),
('0002', '徐御丰', '0912345678', '2005-05-15 00:00:00', 88.5),
('0003', '豬哥亮', '0923456789', '2005-06-20 00:00:00', NULL),
('0004', '李多慧', '0934567890', '2005-07-25 00:00:00', NULL),
('0005', '恐龍', '0945678901', '2005-08-30 00:00:00', NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `student_grades`
--

CREATE TABLE `student_grades` (
  `sid` char(4) NOT NULL,
  `c_no` char(5) NOT NULL,
  `exam_type` enum('期中','期末','平時成績') NOT NULL,
  `score` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 傾印資料表的資料 `student_grades`
--

INSERT INTO `student_grades` (`sid`, `c_no`, `exam_type`, `score`) VALUES
('0001', 'CS101', '期中', 85),
('0001', 'CS101', '期末', 90),
('0001', 'CS102', '期中', 88),
('0002', 'CS101', '期中', 92),
('0002', 'CS201', '期中', 85);

--
-- 觸發器 `student_grades`
--
DELIMITER $$
CREATE TRIGGER `update_gpa` AFTER INSERT ON `student_grades` FOR EACH ROW BEGIN
    DECLARE new_gpa FLOAT;
    -- 計算該學生的最新 GPA
    SELECT AVG(score) INTO new_gpa
    FROM student_grades
    WHERE sid = NEW.sid;

    -- 更新 students 表中的 GPA 欄位
    UPDATE students
    SET GPA = new_gpa
    WHERE sid = NEW.sid;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 資料表結構 `usr`
--

CREATE TABLE `usr` (
  `sid` char(4) NOT NULL,
  `psw` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 傾印資料表的資料 `usr`
--

INSERT INTO `usr` (`sid`, `psw`) VALUES
('0001', '123'),
('0002', '234'),
('0003', '345'),
('0004', '456'),
('0005', '567');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`eid`);

--
-- 資料表索引 `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`sid`,`c_no`),
  ADD KEY `c_no` (`c_no`);

--
-- 資料表索引 `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`c_no`),
  ADD KEY `idx_course_id` (`semester`);

--
-- 資料表索引 `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`sid`,`c_no`,`semester`),
  ADD UNIQUE KEY `unique_student_course` (`sid`,`c_no`),
  ADD KEY `c_no` (`c_no`),
  ADD KEY `semester` (`semester`);

--
-- 資料表索引 `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`eid`);

--
-- 資料表索引 `instructor_courses`
--
ALTER TABLE `instructor_courses`
  ADD PRIMARY KEY (`eid`,`c_no`),
  ADD KEY `ic_course_fk` (`c_no`);

--
-- 資料表索引 `record`
--
ALTER TABLE `record`
  ADD PRIMARY KEY (`r_id`),
  ADD KEY `sid` (`sid`);

--
-- 資料表索引 `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`sid`);

--
-- 資料表索引 `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`sid`,`c_no`,`exam_type`),
  ADD KEY `c_no` (`c_no`);

--
-- 資料表索引 `usr`
--
ALTER TABLE `usr`
  ADD PRIMARY KEY (`sid`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `record`
--
ALTER TABLE `record`
  MODIFY `r_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`eid`) REFERENCES `instructors` (`eid`);

--
-- 資料表的限制式 `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`sid`) REFERENCES `students` (`sid`),
  ADD CONSTRAINT `classes_ibfk_3` FOREIGN KEY (`c_no`) REFERENCES `courses` (`c_no`);

--
-- 資料表的限制式 `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`sid`) REFERENCES `students` (`sid`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`c_no`) REFERENCES `courses` (`c_no`),
  ADD CONSTRAINT `enrollments_ibfk_3` FOREIGN KEY (`semester`) REFERENCES `courses` (`semester`);

--
-- 資料表的限制式 `instructor_courses`
--
ALTER TABLE `instructor_courses`
  ADD CONSTRAINT `ic_course_fk` FOREIGN KEY (`c_no`) REFERENCES `courses` (`c_no`),
  ADD CONSTRAINT `ic_instructor_fk` FOREIGN KEY (`eid`) REFERENCES `instructors` (`eid`);

--
-- 資料表的限制式 `record`
--
ALTER TABLE `record`
  ADD CONSTRAINT `record_ibfk_1` FOREIGN KEY (`sid`) REFERENCES `students` (`sid`);

--
-- 資料表的限制式 `student_grades`
--
ALTER TABLE `student_grades`
  ADD CONSTRAINT `student_grades_ibfk_1` FOREIGN KEY (`sid`) REFERENCES `students` (`sid`),
  ADD CONSTRAINT `student_grades_ibfk_2` FOREIGN KEY (`c_no`) REFERENCES `courses` (`c_no`);

--
-- 資料表的限制式 `usr`
--
ALTER TABLE `usr`
  ADD CONSTRAINT `usr_ibfk_1` FOREIGN KEY (`sid`) REFERENCES `students` (`sid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
