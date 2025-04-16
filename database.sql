-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-04-16 02:17:05
-- 服务器版本： 8.0.36
-- PHP 版本： 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `yiliao`
--

-- --------------------------------------------------------

--
-- 表的结构 `appointments`
--

CREATE TABLE `appointments` (
  `AppointmentID` int NOT NULL,
  `PatientID` int DEFAULT NULL,
  `DoctorID` int DEFAULT NULL,
  `AppointmentDate` date DEFAULT NULL,
  `AppointmentTime` enum('08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','18:00') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `AppointmentStatus` varchar(50) DEFAULT NULL,
  `DepartmentID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `appointments`
--

INSERT INTO `appointments` (`AppointmentID`, `PatientID`, `DoctorID`, `AppointmentDate`, `AppointmentTime`, `AppointmentStatus`, `DepartmentID`) VALUES
(66, 90002, 10003, '2024-06-22', '14:30', '已预约', 2),
(67, 90001, 10010, '2025-04-13', '14:00', '已就诊', 5);

--
-- 触发器 `appointments`
--
DELIMITER $$
CREATE TRIGGER `create_list_on_appointment_visited` AFTER UPDATE ON `appointments` FOR EACH ROW BEGIN
    IF NEW.AppointmentStatus = '已就诊' AND OLD.AppointmentStatus != '已就诊' THEN
        INSERT INTO list_table (DoctorID, PatientID, PrescriptionDate)
        VALUES (NEW.DoctorID, NEW.PatientID, NEW.AppointmentDate);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `departments`
--

CREATE TABLE `departments` (
  `DepartmentID` int NOT NULL,
  `Department` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `departments`
--

INSERT INTO `departments` (`DepartmentID`, `Department`) VALUES
(3, '儿科'),
(1, '内科'),
(2, '外科'),
(4, '妇产科'),
(5, '眼科');

-- --------------------------------------------------------

--
-- 表的结构 `doctors`
--

CREATE TABLE `doctors` (
  `DoctorID` int NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `DepartmentID` int NOT NULL,
  `Title` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `doctors`
--

INSERT INTO `doctors` (`DoctorID`, `FullName`, `DepartmentID`, `Title`) VALUES
(10001, '王超', 1, NULL),
(10002, '李华', 1, NULL),
(10003, '王蒙', 2, NULL),
(10004, '包利民', 3, NULL),
(10005, '张鹏', 2, NULL),
(10006, '郑桐', 5, NULL),
(10007, '何平', 4, NULL),
(10008, '魏鹏宇', 4, NULL),
(10009, '张学庆', 5, NULL),
(10010, '魏传博', 5, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `drugs`
--

CREATE TABLE `drugs` (
  `DrugID` int NOT NULL,
  `DrugName` varchar(255) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `StockQuantity` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `drugs`
--

INSERT INTO `drugs` (`DrugID`, `DrugName`, `Price`, `StockQuantity`) VALUES
(1, '头孢', '9.98', 810),
(2, '布洛芬颗粒', '10.85', 900),
(3, '莲花清瘟胶囊', '10.98', 10000),
(4, '左氧氟沙星片', '20.84', 9950),
(5, '阿昔洛韦滴眼液', '15.41', 10000),
(6, '蒙脱石散', '12.00', 430),
(7, '麝香壮骨膏', '9.00', 10000),
(8, '牛黄醒消丸', '42.85', 400),
(10, '测试1', '100.00', 100);

-- --------------------------------------------------------

--
-- 表的结构 `examination`
--

CREATE TABLE `examination` (
  `ExaminationID` int NOT NULL,
  `ExaminationName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `examination_records`
--

CREATE TABLE `examination_records` (
  `ExaminationRecordID` int NOT NULL,
  `ListID` int NOT NULL,
  `ExaminationItem` varchar(255) NOT NULL,
  `ExaminationPrice` decimal(10,2) NOT NULL,
  `ExaminationResult` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 触发器 `examination_records`
--
DELIMITER $$
CREATE TRIGGER `update_list_total_price_on_examination_insert` AFTER INSERT ON `examination_records` FOR EACH ROW BEGIN
    UPDATE list_table
    SET TotalPrice = TotalPrice + NEW.ExaminationPrice
    WHERE ListID = NEW.ListID;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `list_table`
--

CREATE TABLE `list_table` (
  `ListID` int NOT NULL,
  `DoctorID` int NOT NULL,
  `PatientID` int NOT NULL,
  `PrescriptionDate` date NOT NULL,
  `TotalPrice` decimal(10,2) DEFAULT '0.00',
  `PaymentStatus` varchar(20) DEFAULT '未缴费'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 触发器 `list_table`
--
DELIMITER $$
CREATE TRIGGER `insert_into_prescription_and_examination_on_list_insert` AFTER INSERT ON `list_table` FOR EACH ROW BEGIN
    INSERT INTO prescription_table (ListID, DrugName, DrugQuantity, DrugPrice, DrugSumPrice)
    VALUES (NEW.ListID, '', 0, 0.00, 0.00);

    INSERT INTO examination_records (ListID, ExaminationItem, ExaminationPrice, ExaminationResult)
    VALUES (NEW.ListID, '', 0.00, NULL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `patients`
--

CREATE TABLE `patients` (
  `PatientID` int NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Gender` enum('男','女','其他') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Age` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `patients`
--

INSERT INTO `patients` (`PatientID`, `FullName`, `Gender`, `Age`) VALUES
(90001, '张秋爽', '女', 45),
(90002, '李晓莹', '女', 36),
(90003, '赵天一', '男', 10),
(90004, '吴晗', '男', 20),
(90005, '刘佳烁', '女', 25);

-- --------------------------------------------------------

--
-- 表的结构 `prescription_table`
--

CREATE TABLE `prescription_table` (
  `PrescriptionID` int NOT NULL,
  `ListID` int NOT NULL,
  `DrugName` varchar(255) NOT NULL,
  `DrugQuantity` int NOT NULL,
  `DrugPrice` decimal(10,2) NOT NULL,
  `DrugSumPrice` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 触发器 `prescription_table`
--
DELIMITER $$
CREATE TRIGGER `update_list_total_price_on_prescription_insert` AFTER INSERT ON `prescription_table` FOR EACH ROW BEGIN
    UPDATE list_table
    SET TotalPrice = TotalPrice + NEW.DrugSumPrice
    WHERE ListID = NEW.ListID;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `schedules`
--

CREATE TABLE `schedules` (
  `ScheduleID` int NOT NULL,
  `DoctorID` int NOT NULL,
  `ScheduleDate` date NOT NULL,
  `StartTime` time NOT NULL,
  `EndTime` time NOT NULL,
  `Shift` varchar(20) NOT NULL,
  `RelatedScheduleID` int DEFAULT NULL,
  `DepartmentID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `schedules`
--

INSERT INTO `schedules` (`ScheduleID`, `DoctorID`, `ScheduleDate`, `StartTime`, `EndTime`, `Shift`, `RelatedScheduleID`, `DepartmentID`) VALUES
(51, 10010, '2025-04-13', '14:00:00', '17:30:00', '下午班', NULL, 5);

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `UserID` int NOT NULL,
  `Username` varchar(50) NOT NULL,
  `PasswordHash` varchar(64) NOT NULL,
  `UserType` enum('doctor','patient','admin') NOT NULL,
  `UserCell` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`UserID`, `Username`, `PasswordHash`, `UserType`, `UserCell`) VALUES
(1, 'admin', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', 'admin', NULL),
(10001, '王超', '$2y$10$yZxgijWeD69Mmd3CenRgL.Ixz1qsy3a/VNx2Oqk/5rGdslAkgymFK', 'doctor', NULL),
(10002, '李华', '$2y$10$4p4u6d2hQZtqCJGOIswcJOyfHSle1.Bhs7XZdvYrEemGRxx4M3vsa', 'doctor', NULL),
(10003, '王蒙', '$2y$10$pai1UXsOy85K98zcqjdNFeB/GcL48.3WN.aLBtSYthiVVX3UCAZ0S', 'doctor', NULL),
(10004, '包利民', '$2y$10$Lb6UdKIKbK297n9d.2eZQul1uBhRVU2.lvAeM3PaoBk7gQ94Wexrm', 'doctor', NULL),
(10005, '张鹏', '$2y$10$z0YcT.BFC2wMF70b31lDR.x.v9ySxD6dqa1EYnlqP30IEQElZSUuu', 'doctor', NULL),
(10006, '郑桐', '$2y$10$uG2rt/zpsGhe0iV9eRn9Zun3FOvG363ZDheUPJ8QUuWBJR6EcbScq', 'doctor', NULL),
(10007, '何平', '$2y$10$KajkOCe3QqSQBR6gY6EXSOE3THVtz/kXN2evukggLJBnM6EhLdCFC', 'doctor', NULL),
(10008, '魏鹏宇', '$2y$10$Z0QqpcwXSx5GIYksogfvTeLDBCRVfCh3WVUa0QqAbgNFg5CQ6KiOi', 'doctor', NULL),
(10009, '张学庆', '$2y$10$df2wT.rHe1BMtwj/e6AABeVHykXGKquwNafJcrL9vSkF1HTZQ5U6C', 'doctor', NULL),
(10010, '魏传博', '$2y$10$35SuUROtPj4E5HXwim8CHeIkdkDPCOkIn1QCoDN9iE9c6xHmae9CW', 'doctor', NULL),
(90001, '张秋爽', '$2y$10$RYl3psnH1.q0ftYdD3ulgeQdxE0klhClOLU6oLn5yc/AZoBJlKJVi', 'patient', NULL),
(90002, '李晓莹', '$2y$10$0MoSPHQaS8R2O.JUc65UIukKm3m2a5LS7XXxXfLNCxdRgbkbfG1SS', 'patient', NULL),
(90003, '赵天一', '$2y$10$5GMwEHlM1N3n6E9BJDlbVuD2gUbV13u74V1kUklBKzrkbp/RXtnRa', 'patient', NULL),
(90004, '吴晗', '$2y$10$6Gqjk.yYoDHwHKAqcKkUg.2iSkRhLdT9TtxvPDzpI6SITbxnT.Nym', 'patient', NULL),
(90005, '刘佳烁', '$2y$10$9QGzRceebu9Vz7btTpNfCu/fyWx6kX8PZWCsEZxoFued3tEvZ1QdW', 'patient', NULL),
(90006, '李赞', '$2y$10$XatsUAUi.P4i.P/7mn4oA.LM7t7wMvHEl0Ihjn2vMal2wzonOMnJG', 'patient', NULL);

--
-- 转储表的索引
--

--
-- 表的索引 `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`AppointmentID`),
  ADD KEY `PatientID` (`PatientID`),
  ADD KEY `DoctorID` (`DoctorID`),
  ADD KEY `DepartmentID` (`DepartmentID`);

--
-- 表的索引 `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`DepartmentID`),
  ADD UNIQUE KEY `DepartmentName` (`Department`);

--
-- 表的索引 `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`DoctorID`);

--
-- 表的索引 `drugs`
--
ALTER TABLE `drugs`
  ADD PRIMARY KEY (`DrugID`),
  ADD KEY `DrugName` (`DrugName`),
  ADD KEY `Price` (`Price`),
  ADD KEY `idx_drug_name_price` (`DrugName`,`Price`);

--
-- 表的索引 `examination`
--
ALTER TABLE `examination`
  ADD PRIMARY KEY (`ExaminationID`),
  ADD KEY `ExaminationName` (`ExaminationName`),
  ADD KEY `Price` (`Price`),
  ADD KEY `idx_examination_name_price` (`ExaminationName`,`Price`);

--
-- 表的索引 `examination_records`
--
ALTER TABLE `examination_records`
  ADD PRIMARY KEY (`ExaminationRecordID`),
  ADD KEY `ListID` (`ListID`),
  ADD KEY `ExaminationItem` (`ExaminationItem`,`ExaminationPrice`);

--
-- 表的索引 `list_table`
--
ALTER TABLE `list_table`
  ADD PRIMARY KEY (`ListID`),
  ADD KEY `DoctorID` (`DoctorID`),
  ADD KEY `PatientID` (`PatientID`);

--
-- 表的索引 `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`PatientID`);

--
-- 表的索引 `prescription_table`
--
ALTER TABLE `prescription_table`
  ADD PRIMARY KEY (`PrescriptionID`),
  ADD KEY `ListID` (`ListID`),
  ADD KEY `DrugName` (`DrugName`,`DrugPrice`);

--
-- 表的索引 `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`ScheduleID`),
  ADD KEY `DoctorID` (`DoctorID`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`,`UserType`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `appointments`
--
ALTER TABLE `appointments`
  MODIFY `AppointmentID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- 使用表AUTO_INCREMENT `departments`
--
ALTER TABLE `departments`
  MODIFY `DepartmentID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `doctors`
--
ALTER TABLE `doctors`
  MODIFY `DoctorID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123457;

--
-- 使用表AUTO_INCREMENT `drugs`
--
ALTER TABLE `drugs`
  MODIFY `DrugID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `examination`
--
ALTER TABLE `examination`
  MODIFY `ExaminationID` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `examination_records`
--
ALTER TABLE `examination_records`
  MODIFY `ExaminationRecordID` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `list_table`
--
ALTER TABLE `list_table`
  MODIFY `ListID` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `patients`
--
ALTER TABLE `patients`
  MODIFY `PatientID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90006;

--
-- 使用表AUTO_INCREMENT `prescription_table`
--
ALTER TABLE `prescription_table`
  MODIFY `PrescriptionID` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `schedules`
--
ALTER TABLE `schedules`
  MODIFY `ScheduleID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=546214;

--
-- 限制导出的表
--

--
-- 限制表 `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`DepartmentID`) REFERENCES `departments` (`DepartmentID`);

--
-- 限制表 `examination_records`
--
ALTER TABLE `examination_records`
  ADD CONSTRAINT `examination_records_ibfk_1` FOREIGN KEY (`ListID`) REFERENCES `list_table` (`ListID`),
  ADD CONSTRAINT `examination_records_ibfk_2` FOREIGN KEY (`ExaminationItem`,`ExaminationPrice`) REFERENCES `examination` (`ExaminationName`, `Price`);

--
-- 限制表 `list_table`
--
ALTER TABLE `list_table`
  ADD CONSTRAINT `list_table_ibfk_1` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`),
  ADD CONSTRAINT `list_table_ibfk_2` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`);

--
-- 限制表 `prescription_table`
--
ALTER TABLE `prescription_table`
  ADD CONSTRAINT `prescription_table_ibfk_1` FOREIGN KEY (`ListID`) REFERENCES `list_table` (`ListID`),
  ADD CONSTRAINT `prescription_table_ibfk_2` FOREIGN KEY (`DrugName`,`DrugPrice`) REFERENCES `drugs` (`DrugName`, `Price`);

--
-- 限制表 `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
