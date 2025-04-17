-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-04-16 11:35:07
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
  `Title` varchar(50) NOT NULL,
  `AppointmentDate` date DEFAULT NULL,
  `AppointmentTime` enum('08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','18:00') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `AppointmentStatus` varchar(50) DEFAULT NULL,
  `DepartmentID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `appointments`
--

INSERT INTO `appointments` (`AppointmentID`, `PatientID`, `DoctorID`, `Title`, `AppointmentDate`, `AppointmentTime`, `AppointmentStatus`, `DepartmentID`) VALUES
(1, 90001, 10010, '', '2025-04-16', '08:00', '已就诊', 5),
(2, 90001, 10010, '', '2025-04-16', '14:30', '已就诊', 5),
(3, 90001, 10010, '主任医师', '2025-05-01', '09:00', 'Scheduled', 5),
(4, 90001, 10010, '主任医师', '2025-05-01', '10:30', 'Scheduled', 5),
(5, 90001, 10010, '主任医师', '2025-05-01', '09:00', 'Scheduled', 5),
(6, 90001, 10010, '主任医师', '2025-05-01', '15:00', '已就诊', 5);

--
-- 触发器 `appointments`
--
DELIMITER $$
CREATE TRIGGER `after_update_appointments` AFTER UPDATE ON `appointments` FOR EACH ROW BEGIN
    DECLARE price DECIMAL(10,2);

    -- 只有当状态从非“已就诊”变为“已就诊”时触发
    IF NEW.AppointmentStatus = '已就诊' AND OLD.AppointmentStatus != '已就诊' THEN

        -- 根据医生职称设置价格
        IF NEW.Title = '主任医师' THEN
            SET price = 23.50;
        ELSEIF NEW.Title = '副主任医师' THEN
            SET price = 16.50;
        ELSEIF NEW.Title = '主治医师' THEN
            SET price = 6.50;
        ELSE
            SET price = 0.00; -- 如果职称不是这三种，设为0或自定义值
        END IF;

        -- 插入就诊记录
        INSERT INTO list_table (AppointmentID, DoctorID, PatientID, PrescriptionDate, Time, TotalPrice)
        VALUES (NEW.AppointmentID, NEW.DoctorID, NEW.PatientID, NEW.AppointmentDate, NOW(), price);

    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_insert_appointments` BEFORE INSERT ON `appointments` FOR EACH ROW BEGIN
    DECLARE doctor_title VARCHAR(255);  -- 声明一个临时变量

    -- 查询医生的职称，放入临时变量中
    SELECT Title INTO doctor_title
    FROM doctors
    WHERE doctors.DoctorID = NEW.DoctorID
    LIMIT 1;

    -- 将职称赋值给 NEW.Title
    SET NEW.Title = doctor_title;
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
(10010, '魏传博', 5, '主任医师');

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

--
-- 转存表中的数据 `examination`
--

INSERT INTO `examination` (`ExaminationID`, `ExaminationName`, `Price`) VALUES
(1, 'CT', '150.00'),
(2, '核磁', '200.00');

-- --------------------------------------------------------

--
-- 表的结构 `examination_records`
--

CREATE TABLE `examination_records` (
  `ExaminationRecordID` int NOT NULL,
  `AppointmentID` int NOT NULL,
  `ExaminationItem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `ExaminationPrice` decimal(10,2) DEFAULT NULL,
  `ExaminationResult` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `examination_records`
--

INSERT INTO `examination_records` (`ExaminationRecordID`, `AppointmentID`, `ExaminationItem`, `ExaminationPrice`, `ExaminationResult`) VALUES
(20, 1, 'CT', '150.00', NULL),
(21, 1, 'CT', '150.00', NULL);

--
-- 触发器 `examination_records`
--
DELIMITER $$
CREATE TRIGGER `insert_examination_price` BEFORE INSERT ON `examination_records` FOR EACH ROW BEGIN
    DECLARE exam_price DECIMAL(10, 2);
    -- 从examination表查询对应ExaminationName的Price
    SELECT Price INTO exam_price 
    FROM examination
    WHERE ExaminationName = NEW.ExaminationItem;
    -- 将查询到的价格更新到ExaminationPrice字段
    SET New.ExaminationPrice = exam_price;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_examination_to_list` AFTER INSERT ON `examination_records` FOR EACH ROW BEGIN
    DECLARE v_DoctorID INT;
    DECLARE v_PatientID INT;
    DECLARE v_PrescriptionDate DATE;
    DECLARE v_ExaminationPrice DECIMAL(10, 2);

    -- 查询DoctorID
    SELECT DoctorID INTO v_DoctorID 
    FROM appointments 
    WHERE AppointmentID = NEW.AppointmentID;

    -- 查询PatientID
    SELECT PatientID INTO v_PatientID 
    FROM appointments 
    WHERE AppointmentID = NEW.AppointmentID;

    -- 查询PrescriptionDate
    SELECT AppointmentDate INTO v_PrescriptionDate 
    FROM appointments 
    WHERE AppointmentID = NEW.AppointmentID;

    -- 获取ExaminationPrice，这里直接使用插入的ExaminationPrice
    SET v_ExaminationPrice = NEW.ExaminationPrice;

    -- 向list_table插入数据
    INSERT INTO list_table (
        AppointmentID, DoctorID, PatientID, PrescriptionDate, 
        Time, TotalPrice, PaymentStatus, CaseRecord
    )
    VALUES (
        NEW.AppointmentID, v_DoctorID, v_PatientID, v_PrescriptionDate,
        NOW(), v_ExaminationPrice, '未支付', ''
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `list_table`
--

CREATE TABLE `list_table` (
  `ListID` int NOT NULL,
  `AppointmentID` int NOT NULL,
  `DoctorID` int NOT NULL,
  `PatientID` int NOT NULL,
  `PrescriptionDate` date NOT NULL,
  `Time` time DEFAULT NULL,
  `TotalPrice` decimal(10,2) DEFAULT '0.00',
  `PaymentStatus` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `CaseRecord` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `list_table`
--

INSERT INTO `list_table` (`ListID`, `AppointmentID`, `DoctorID`, `PatientID`, `PrescriptionDate`, `Time`, `TotalPrice`, `PaymentStatus`, `CaseRecord`) VALUES
(42, 1, 10010, 90001, '2025-04-16', '19:23:33', '59.88', '未支付', ''),
(43, 1, 10010, 90001, '2025-04-16', '19:23:46', '49.90', '未支付', ''),
(44, 1, 10010, 90001, '2025-04-16', '19:23:56', '104.20', '未支付', ''),
(45, 1, 10010, 90001, '2025-04-16', '19:28:09', '150.00', '未支付', ''),
(46, 1, 10010, 90001, '2025-04-16', '19:28:19', '150.00', '已支付', '');

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
  `AppointmentID` int NOT NULL,
  `DrugName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `DrugQuantity` int DEFAULT NULL,
  `DrugPrice` decimal(10,2) DEFAULT NULL,
  `DrugSumPrice` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `prescription_table`
--

INSERT INTO `prescription_table` (`PrescriptionID`, `AppointmentID`, `DrugName`, `DrugQuantity`, `DrugPrice`, `DrugSumPrice`) VALUES
(33, 1, '头孢', 6, '9.98', '59.88'),
(34, 1, '头孢', 5, '9.98', '49.90'),
(35, 1, '左氧氟沙星片', 5, '20.84', '104.20');

--
-- 触发器 `prescription_table`
--
DELIMITER $$
CREATE TRIGGER `calculate_sum_price_update` BEFORE UPDATE ON `prescription_table` FOR EACH ROW BEGIN
    -- 当 DrugQuantity 有变化且不为空，或者 DrugPrice 有变化且不为空时重新计算 DrugSumPrice
    IF (NEW.DrugQuantity IS NOT NULL AND NEW.DrugQuantity <> OLD.DrugQuantity) OR (NEW.DrugPrice IS NOT NULL AND NEW.DrugPrice <> OLD.DrugPrice) THEN
        SET NEW.DrugSumPrice = NEW.DrugQuantity * NEW.DrugPrice;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_prescription_to_list` AFTER INSERT ON `prescription_table` FOR EACH ROW BEGIN
    DECLARE v_DoctorID INT;
    DECLARE v_PatientID INT;
    DECLARE v_PrescriptionDate DATE;
    DECLARE v_TotalPrice DECIMAL(10, 2);

    -- 查询appointments表中的信息
    SELECT DoctorID, PatientID, AppointmentDate
    INTO v_DoctorID, v_PatientID, v_PrescriptionDate
    FROM appointments
    WHERE AppointmentID = NEW.AppointmentID;

    -- 获取当前药品的价格
    SET v_TotalPrice = NEW.DrugSumPrice;

    -- 向list_table插入新记录
    INSERT INTO list_table (
        AppointmentID, DoctorID, PatientID, PrescriptionDate,
        Time, TotalPrice, PaymentStatus, CaseRecord
    )
    VALUES (
        NEW.AppointmentID, v_DoctorID, v_PatientID, v_PrescriptionDate,
        NOW(), v_TotalPrice, '未支付', ''
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_drug_price` BEFORE INSERT ON `prescription_table` FOR EACH ROW BEGIN
    DECLARE drug_price DECIMAL(10, 2);
    -- 从drugs表查询对应DrugName的Price
    SELECT Price INTO drug_price 
    FROM drugs
    WHERE DrugName = NEW.DrugName;
    -- 将查询到的价格赋值给新的DrugPrice字段值
    SET NEW.DrugPrice = drug_price;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_drug_sum_price` BEFORE INSERT ON `prescription_table` FOR EACH ROW BEGIN
    IF NEW.DrugQuantity IS NOT NULL THEN
        SET NEW.DrugSumPrice = NEW.DrugQuantity * NEW.DrugPrice;
    ELSE
        SET NEW.DrugSumPrice = NULL;
    END IF;
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
(1, 10010, '2025-04-16', '08:00:00', '11:30:00', '早上', NULL, 5),
(2, 10010, '2025-04-16', '14:00:00', '17:30:00', '下午', NULL, 5);

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
  ADD KEY `idx_drug_name` (`DrugName`);

--
-- 表的索引 `examination`
--
ALTER TABLE `examination`
  ADD PRIMARY KEY (`ExaminationID`),
  ADD KEY `ExaminationName` (`ExaminationName`);

--
-- 表的索引 `examination_records`
--
ALTER TABLE `examination_records`
  ADD PRIMARY KEY (`ExaminationRecordID`),
  ADD KEY `fk_examination_item` (`ExaminationItem`),
  ADD KEY `fk_examination_records_appointments` (`AppointmentID`);

--
-- 表的索引 `list_table`
--
ALTER TABLE `list_table`
  ADD PRIMARY KEY (`ListID`),
  ADD KEY `DoctorID` (`DoctorID`),
  ADD KEY `PatientID` (`PatientID`),
  ADD KEY `fk_list_appointment` (`AppointmentID`);

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
  ADD KEY `fk_drug_info` (`DrugName`,`DrugPrice`),
  ADD KEY `fk_prescription_table_appointments` (`AppointmentID`);

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
  MODIFY `AppointmentID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `ExaminationID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `examination_records`
--
ALTER TABLE `examination_records`
  MODIFY `ExaminationRecordID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- 使用表AUTO_INCREMENT `list_table`
--
ALTER TABLE `list_table`
  MODIFY `ListID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- 使用表AUTO_INCREMENT `patients`
--
ALTER TABLE `patients`
  MODIFY `PatientID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90006;

--
-- 使用表AUTO_INCREMENT `prescription_table`
--
ALTER TABLE `prescription_table`
  MODIFY `PrescriptionID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- 使用表AUTO_INCREMENT `schedules`
--
ALTER TABLE `schedules`
  MODIFY `ScheduleID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  ADD CONSTRAINT `fk_examination_item` FOREIGN KEY (`ExaminationItem`) REFERENCES `examination` (`ExaminationName`),
  ADD CONSTRAINT `fk_examination_records_appointments` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`);

--
-- 限制表 `list_table`
--
ALTER TABLE `list_table`
  ADD CONSTRAINT `fk_list_appointment` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`),
  ADD CONSTRAINT `list_table_ibfk_1` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`),
  ADD CONSTRAINT `list_table_ibfk_2` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`);

--
-- 限制表 `prescription_table`
--
ALTER TABLE `prescription_table`
  ADD CONSTRAINT `fk_drug_name` FOREIGN KEY (`DrugName`) REFERENCES `drugs` (`DrugName`),
  ADD CONSTRAINT `fk_prescription_table_appointments` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`);

--
-- 限制表 `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
