-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-04-13 21:05:54
-- 服务器版本： 8.0.17
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
  `AppointmentID` int(11) NOT NULL,
  `PatientID` int(11) DEFAULT NULL,
  `DoctorID` int(11) DEFAULT NULL,
  `AppointmentDate` date DEFAULT NULL,
  `AppointmentTime` enum('08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','18:00') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `AppointmentType` varchar(50) DEFAULT NULL,
  `AppointmentStatus` varchar(50) DEFAULT NULL,
  `DepartmentID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `appointments`
--

INSERT INTO `appointments` (`AppointmentID`, `PatientID`, `DoctorID`, `AppointmentDate`, `AppointmentTime`, `AppointmentType`, `AppointmentStatus`, `DepartmentID`) VALUES
(61, 90001, 10004, '2024-06-14', '18:00', '初诊', '已就诊', 3),
(63, 90007, 10011, '2024-06-03', '08:30', '初诊', '已就诊', 3),
(64, 1001, 10001, '2024-06-03', '18:00', '初诊', '已就诊', 1),
(66, 90003, 10004, '2024-06-14', '18:00', '初诊', '已就诊', 3);

-- --------------------------------------------------------

--
-- 表的结构 `departments`
--

CREATE TABLE `departments` (
  `DepartmentID` int(11) NOT NULL,
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
  `DoctorID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `DepartmentID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `doctors`
--

INSERT INTO `doctors` (`DoctorID`, `FullName`, `DepartmentID`) VALUES
(10001, '王超', 1),
(10002, '李华', 1),
(10003, '王蒙', 2),
(10004, '包利民', 3),
(10005, '张鹏', 2),
(10006, '郑桐', 5),
(10007, '何平', 4),
(10008, '魏鹏宇', 4),
(10009, '张学庆', 5),
(10011, '医生', 3);

-- --------------------------------------------------------

--
-- 表的结构 `drugs`
--

CREATE TABLE `drugs` (
  `DrugID` int(11) NOT NULL,
  `DrugName` varchar(255) NOT NULL,
  `Price` float NOT NULL,
  `StockQuantity` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `drugs`
--

INSERT INTO `drugs` (`DrugID`, `DrugName`, `Price`, `StockQuantity`) VALUES
(1, '头孢', 9.98, 99950),
(2, '布洛芬颗粒', 10.85, 9980),
(3, '莲花清瘟胶囊', 10.98, 10000),
(4, '左氧氟沙星片', 20.84, 10000),
(5, '阿昔洛韦滴眼液', 15.41, 10000),
(6, '蒙脱石散', 12, 465),
(7, '麝香壮骨膏', 9, 10000),
(8, '牛黄醒消丸', 42.85, 450);

-- --------------------------------------------------------

--
-- 表的结构 `patients`
--

CREATE TABLE `patients` (
  `PatientID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Gender` enum('男','女','其他') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Age` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `patients`
--

INSERT INTO `patients` (`PatientID`, `FullName`, `Gender`, `Age`) VALUES
(1001, '患者1', '男', 20),
(90001, '张秋爽', '女', 45),
(90002, '李晓莹', '女', 36),
(90003, '赵天一', '男', 10),
(90004, '吴晗', '男', 20),
(90005, '刘佳烁', '女', 25),
(90007, '患者', '男', 20);

-- --------------------------------------------------------

--
-- 表的结构 `prescriptions`
--

CREATE TABLE `prescriptions` (
  `PrescriptionID` int(11) NOT NULL,
  `DoctorID` int(11) DEFAULT NULL,
  `PatientID` int(11) DEFAULT NULL,
  `DrugID` int(11) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `SumPrice` float NOT NULL,
  `PaymentStatus` varchar(50) DEFAULT '未缴费',
  `PrescriptionDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `prescriptions`
--

INSERT INTO `prescriptions` (`PrescriptionID`, `DoctorID`, `PatientID`, `DrugID`, `Quantity`, `SumPrice`, `PaymentStatus`, `PrescriptionDate`) VALUES
(3, 10003, 90001, 1, 50, 499, '已发药', '2024-06-01'),
(4, 10003, 90001, 1, 50, 499, '已支付', '2024-06-01'),
(5, 10003, 90001, 2, 50, 542.5, '已支付', '2024-06-01'),
(6, 10003, 90002, 1, 20, 199.6, '已发药', '2024-06-01'),
(7, 10003, 90002, 1, 50, 499, '已发药', '2024-06-01'),
(8, 10003, 90002, 6, 30, 360, '已支付', '2024-06-01'),
(9, 10003, 90002, 2, 50, 542.5, '已支付', '2024-06-01'),
(10, 10003, 90002, 1, 20, 199.6, '已支付', '2024-06-01'),
(11, 10003, 90002, 1, 20, 199.6, '已支付', '2024-06-01'),
(12, 10003, 90002, 6, 5, 60, '已支付', '2024-06-01'),
(13, 10004, 90001, 8, 50, 499, '已支付', '2024-06-14'),
(14, 10004, 90001, 1, 5, 49.9, '已支付', '2024-06-14'),
(15, 10011, 90007, 1, 10, 99.8, '已发药', '2024-06-03'),
(16, 10001, 1001, 1, 10, 108.5, '已发药', '2024-06-03'),
(17, 10001, 1001, 2, 10, 108.5, '已发药', '2024-06-03'),
(18, 10004, 90001, 1, 20, 199.6, '已支付', '2024-06-14'),
(19, 10004, 90003, 1, 20, 217, '未支付', '2024-06-14'),
(20, 10004, 90003, 2, 10, 108.5, '未支付', '2024-06-14'),
(21, 10011, 90007, 3, 1, 10.98, '未支付', '2024-06-03');

-- --------------------------------------------------------

--
-- 表的结构 `schedules`
--

CREATE TABLE `schedules` (
  `ScheduleID` int(11) NOT NULL,
  `DoctorID` int(11) NOT NULL,
  `ScheduleDate` date NOT NULL,
  `StartTime` time NOT NULL,
  `EndTime` time NOT NULL,
  `Shift` varchar(20) NOT NULL,
  `RelatedScheduleID` int(11) DEFAULT NULL,
  `DepartmentID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `schedules`
--

INSERT INTO `schedules` (`ScheduleID`, `DoctorID`, `ScheduleDate`, `StartTime`, `EndTime`, `Shift`, `RelatedScheduleID`, `DepartmentID`) VALUES
(43, 10003, '2024-06-22', '14:00:00', '17:30:00', '下午班', NULL, 2),
(44, 10004, '2024-06-14', '18:00:00', '23:59:59', '晚班', 45, 3),
(45, 10004, '2024-06-15', '00:00:00', '07:00:00', '晚班', 44, 3),
(46, 10001, '2024-06-03', '08:30:00', '12:00:00', '早班', NULL, 1),
(47, 10001, '2024-06-03', '14:00:00', '17:30:00', '下午班', NULL, 1),
(48, 10002, '2024-06-03', '08:30:00', '12:00:00', '早班', NULL, 1),
(49, 10011, '2024-06-03', '08:30:00', '12:00:00', '早班', NULL, 3),
(50, 10001, '2024-06-04', '18:00:00', '23:59:59', '晚班', 51, 1),
(51, 10001, '2024-06-05', '00:00:00', '07:00:00', '晚班', 50, 1);

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `PasswordHash` varchar(64) NOT NULL,
  `UserType` enum('doctor','patient','admin') NOT NULL,
  `UserCell` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`UserID`, `Username`, `PasswordHash`, `UserType`, `UserCell`) VALUES
(1, 'admin', '$2y$10$LUkl8OrqME4cLcCtNLFwSOposF4Yqm.6tDincfbiHURnAFurg//ku', 'admin', NULL),
(1001, '患者1', '$2y$10$gLXsp0AWfOdzurn.I7ZlzOy1b2DlFHsiOpuMfmI8HJp2shmPKLO1G', 'patient', NULL),
(10001, '王超', '$2y$10$yZxgijWeD69Mmd3CenRgL.Ixz1qsy3a/VNx2Oqk/5rGdslAkgymFK', 'doctor', NULL),
(10002, '李华', '$2y$10$4p4u6d2hQZtqCJGOIswcJOyfHSle1.Bhs7XZdvYrEemGRxx4M3vsa', 'doctor', NULL),
(10003, '王蒙', '$2y$10$pai1UXsOy85K98zcqjdNFeB/GcL48.3WN.aLBtSYthiVVX3UCAZ0S', 'doctor', NULL),
(10004, '包利民', '$2y$10$Lb6UdKIKbK297n9d.2eZQul1uBhRVU2.lvAeM3PaoBk7gQ94Wexrm', 'doctor', NULL),
(10005, '张鹏', '$2y$10$z0YcT.BFC2wMF70b31lDR.x.v9ySxD6dqa1EYnlqP30IEQElZSUuu', 'doctor', NULL),
(10006, '郑桐', '$2y$10$uG2rt/zpsGhe0iV9eRn9Zun3FOvG363ZDheUPJ8QUuWBJR6EcbScq', 'doctor', NULL),
(10007, '何平', '$2y$10$KajkOCe3QqSQBR6gY6EXSOE3THVtz/kXN2evukggLJBnM6EhLdCFC', 'doctor', NULL),
(10008, '魏鹏宇', '$2y$10$Z0QqpcwXSx5GIYksogfvTeLDBCRVfCh3WVUa0QqAbgNFg5CQ6KiOi', 'doctor', NULL),
(10009, '张学庆', '$2y$10$k6KcehyXLmWCwoUwcKFDZeCg2qoxmqqpjfEFjgPx6QC8se9Eofvsu', 'doctor', NULL),
(10011, '医生', '$2y$10$bOUwYU/3.88ldxE/sSxzqOh6NselBYuKO98HJsplTl/o2NrgEkSr6', 'doctor', NULL),
(90001, '张秋爽', '$2y$10$RYl3psnH1.q0ftYdD3ulgeQdxE0klhClOLU6oLn5yc/AZoBJlKJVi', 'patient', NULL),
(90002, '李晓莹', '$2y$10$0MoSPHQaS8R2O.JUc65UIukKm3m2a5LS7XXxXfLNCxdRgbkbfG1SS', 'patient', NULL),
(90003, '赵天一', '$2y$10$5GMwEHlM1N3n6E9BJDlbVuD2gUbV13u74V1kUklBKzrkbp/RXtnRa', 'patient', NULL),
(90004, '吴晗', '$2y$10$6Gqjk.yYoDHwHKAqcKkUg.2iSkRhLdT9TtxvPDzpI6SITbxnT.Nym', 'patient', NULL),
(90005, '刘佳烁', '$2y$10$9QGzRceebu9Vz7btTpNfCu/fyWx6kX8PZWCsEZxoFued3tEvZ1QdW', 'patient', NULL),
(90006, '李赞', '$2y$10$XatsUAUi.P4i.P/7mn4oA.LM7t7wMvHEl0Ihjn2vMal2wzonOMnJG', 'patient', NULL),
(90007, '患者', '$2y$10$DUB9wBMOG8i.Lk6WDGuvnOPrsWiaUZnVwO59zj3GAAv0tmoYeCX4u', 'patient', NULL),
(90008, 'xvjie', '$2y$10$13ngZ2XxT6dqlw6.9ObrMuyzZYYO6RLR6nETphzre67uRn4XBboIS', 'patient', 2147483647);

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
  ADD PRIMARY KEY (`DrugID`);

--
-- 表的索引 `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`PatientID`);

--
-- 表的索引 `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`PrescriptionID`),
  ADD KEY `DoctorID` (`DoctorID`),
  ADD KEY `PatientID` (`PatientID`),
  ADD KEY `DrugID` (`DrugID`);

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
  MODIFY `AppointmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- 使用表AUTO_INCREMENT `departments`
--
ALTER TABLE `departments`
  MODIFY `DepartmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `doctors`
--
ALTER TABLE `doctors`
  MODIFY `DoctorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123457;

--
-- 使用表AUTO_INCREMENT `drugs`
--
ALTER TABLE `drugs`
  MODIFY `DrugID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `patients`
--
ALTER TABLE `patients`
  MODIFY `PatientID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90008;

--
-- 使用表AUTO_INCREMENT `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `PrescriptionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- 使用表AUTO_INCREMENT `schedules`
--
ALTER TABLE `schedules`
  MODIFY `ScheduleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=546214;

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
-- 限制表 `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`),
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`),
  ADD CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`DrugID`) REFERENCES `drugs` (`DrugID`);

--
-- 限制表 `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
