CREATE TABLE `appointments` (
  `AppointmentID` int(11) NOT NULL AUTO_INCREMENT,
  `PatientID` int(11) DEFAULT NULL,
  `DoctorID` int(11) DEFAULT NULL,
  `AppointmentDate` date DEFAULT NULL,
  `AppointmentTime` enum('08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','18:00') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `AppointmentType` varchar(50) DEFAULT NULL,
  `AppointmentStatus` varchar(50) DEFAULT NULL,
  `DepartmentID` int(11) DEFAULT NULL,
  PRIMARY KEY (`AppointmentID`),
  KEY `PatientID` (`PatientID`),
  KEY `DoctorID` (`DoctorID`),
  KEY `DepartmentID` (`DepartmentID`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`),
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`),
  CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`DepartmentID`) REFERENCES `departments` (`DepartmentID`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO appointments (AppointmentID, PatientID, DoctorID, AppointmentDate, AppointmentTime, AppointmentType, AppointmentStatus, DepartmentID) VALUES ('61', '90001', '10004', '2024-06-14', '18:00', '初诊', '已就诊', '3');
INSERT INTO appointments (AppointmentID, PatientID, DoctorID, AppointmentDate, AppointmentTime, AppointmentType, AppointmentStatus, DepartmentID) VALUES ('63', '90007', '10011', '2024-06-03', '08:30', '初诊', '已就诊', '3');
INSERT INTO appointments (AppointmentID, PatientID, DoctorID, AppointmentDate, AppointmentTime, AppointmentType, AppointmentStatus, DepartmentID) VALUES ('64', '1001', '10001', '2024-06-03', '18:00', '初诊', '已就诊', '1');
INSERT INTO appointments (AppointmentID, PatientID, DoctorID, AppointmentDate, AppointmentTime, AppointmentType, AppointmentStatus, DepartmentID) VALUES ('66', '90003', '10004', '2024-06-14', '18:00', '初诊', '已就诊', '3');
INSERT INTO appointments (AppointmentID, PatientID, DoctorID, AppointmentDate, AppointmentTime, AppointmentType, AppointmentStatus, DepartmentID) VALUES ('68', '1001', '10004', '2024-06-14', '18:00', '初诊', '已取消', '3');

CREATE TABLE `departments` (
  `DepartmentID` int(11) NOT NULL AUTO_INCREMENT,
  `Department` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`DepartmentID`),
  UNIQUE KEY `DepartmentName` (`Department`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO departments (DepartmentID, Department) VALUES ('3', '儿科');
INSERT INTO departments (DepartmentID, Department) VALUES ('1', '内科');
INSERT INTO departments (DepartmentID, Department) VALUES ('2', '外科');
INSERT INTO departments (DepartmentID, Department) VALUES ('4', '妇产科');
INSERT INTO departments (DepartmentID, Department) VALUES ('5', '眼科');

CREATE TABLE `doctors` (
  `DoctorID` int(11) NOT NULL AUTO_INCREMENT,
  `FullName` varchar(100) NOT NULL,
  `DepartmentID` int(11) NOT NULL,
  `Title` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`DoctorID`)
) ENGINE=InnoDB AUTO_INCREMENT=123457 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO doctors (DoctorID, FullName, DepartmentID, Title) VALUES ('10001', '王超', '1', '');
INSERT INTO doctors (DoctorID, FullName, DepartmentID, Title) VALUES ('10002', '李华', '1', '');
INSERT INTO doctors (DoctorID, FullName, DepartmentID, Title) VALUES ('10003', '王蒙', '2', '');
INSERT INTO doctors (DoctorID, FullName, DepartmentID, Title) VALUES ('10004', '包利民', '3', '');
INSERT INTO doctors (DoctorID, FullName, DepartmentID, Title) VALUES ('10005', '张鹏', '2', '');
INSERT INTO doctors (DoctorID, FullName, DepartmentID, Title) VALUES ('10006', '郑桐', '5', '');
INSERT INTO doctors (DoctorID, FullName, DepartmentID, Title) VALUES ('10007', '何平', '4', '');
INSERT INTO doctors (DoctorID, FullName, DepartmentID, Title) VALUES ('10008', '魏鹏宇', '4', '');
INSERT INTO doctors (DoctorID, FullName, DepartmentID, Title) VALUES ('10009', '张学庆', '5', '');
INSERT INTO doctors (DoctorID, FullName, DepartmentID, Title) VALUES ('10011', '医生', '3', '');

CREATE TABLE `drugs` (
  `DrugID` int(11) NOT NULL AUTO_INCREMENT,
  `DrugName` varchar(255) NOT NULL,
  `Price` float NOT NULL,
  `StockQuantity` int(11) DEFAULT '0',
  PRIMARY KEY (`DrugID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('1', '头孢', '9.98', '99950');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('2', '布洛芬颗粒', '10.85', '9980');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('3', '莲花清瘟胶囊', '10.98', '10000');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('4', '左氧氟沙星片', '20.84', '10000');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('5', '阿昔洛韦滴眼液', '15.41', '10000');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('6', '蒙脱石散', '12', '465');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('7', '麝香壮骨膏', '9', '10000');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('8', '牛黄醒消丸', '42.85', '450');

CREATE TABLE `patients` (
  `PatientID` int(11) NOT NULL AUTO_INCREMENT,
  `FullName` varchar(100) NOT NULL,
  `Gender` enum('男','女','其他') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Age` int(11) NOT NULL,
  PRIMARY KEY (`PatientID`)
) ENGINE=InnoDB AUTO_INCREMENT=90008 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES ('1001', '患者1', '男', '20');
INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES ('90001', '张秋爽', '女', '45');
INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES ('90002', '李晓莹', '女', '36');
INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES ('90003', '赵天一', '男', '10');
INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES ('90004', '吴晗', '男', '20');
INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES ('90007', '患者', '男', '20');

CREATE TABLE `prescriptions` (
  `PrescriptionID` int(11) NOT NULL AUTO_INCREMENT,
  `DoctorID` int(11) DEFAULT NULL,
  `PatientID` int(11) DEFAULT NULL,
  `DrugID` int(11) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `SumPrice` float NOT NULL,
  `PaymentStatus` varchar(50) DEFAULT '未缴费',
  `PrescriptionDate` date DEFAULT NULL,
  PRIMARY KEY (`PrescriptionID`),
  KEY `DoctorID` (`DoctorID`),
  KEY `PatientID` (`PatientID`),
  KEY `DrugID` (`DrugID`),
  CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`),
  CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`),
  CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`DrugID`) REFERENCES `drugs` (`DrugID`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('3', '10003', '90001', '1', '50', '499', '已发药', '2024-06-01');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('4', '10003', '90001', '1', '50', '499', '已支付', '2024-06-01');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('5', '10003', '90001', '2', '50', '542.5', '已支付', '2024-06-01');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('6', '10003', '90002', '1', '20', '199.6', '已发药', '2024-06-01');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('7', '10003', '90002', '1', '50', '499', '已发药', '2024-06-01');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('8', '10003', '90002', '6', '30', '360', '已支付', '2024-06-01');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('9', '10003', '90002', '2', '50', '542.5', '已支付', '2024-06-01');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('10', '10003', '90002', '1', '20', '199.6', '已支付', '2024-06-01');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('11', '10003', '90002', '1', '20', '199.6', '已支付', '2024-06-01');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('12', '10003', '90002', '6', '5', '60', '已支付', '2024-06-01');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('13', '10004', '90001', '8', '50', '499', '已支付', '2024-06-14');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('14', '10004', '90001', '1', '5', '49.9', '已支付', '2024-06-14');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('15', '10011', '90007', '1', '10', '99.8', '已发药', '2024-06-03');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('16', '10001', '1001', '1', '10', '108.5', '已发药', '2024-06-03');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('17', '10001', '1001', '2', '10', '108.5', '已发药', '2024-06-03');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('18', '10004', '90001', '1', '20', '199.6', '已支付', '2024-06-14');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('19', '10004', '90003', '1', '20', '217', '未支付', '2024-06-14');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('20', '10004', '90003', '2', '10', '108.5', '未支付', '2024-06-14');
INSERT INTO prescriptions (PrescriptionID, DoctorID, PatientID, DrugID, Quantity, SumPrice, PaymentStatus, PrescriptionDate) VALUES ('21', '10011', '90007', '3', '1', '10.98', '未支付', '2024-06-03');

CREATE TABLE `schedules` (
  `ScheduleID` int(11) NOT NULL AUTO_INCREMENT,
  `DoctorID` int(11) NOT NULL,
  `ScheduleDate` date NOT NULL,
  `StartTime` time NOT NULL,
  `EndTime` time NOT NULL,
  `Shift` varchar(20) NOT NULL,
  `RelatedScheduleID` int(11) DEFAULT NULL,
  `DepartmentID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ScheduleID`),
  KEY `DoctorID` (`DoctorID`),
  CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO schedules (ScheduleID, DoctorID, ScheduleDate, StartTime, EndTime, Shift, RelatedScheduleID, DepartmentID) VALUES ('43', '10003', '2024-06-22', '14:00:00', '17:30:00', '下午班', '', '2');
INSERT INTO schedules (ScheduleID, DoctorID, ScheduleDate, StartTime, EndTime, Shift, RelatedScheduleID, DepartmentID) VALUES ('44', '10004', '2024-06-14', '18:00:00', '23:59:59', '晚班', '45', '3');
INSERT INTO schedules (ScheduleID, DoctorID, ScheduleDate, StartTime, EndTime, Shift, RelatedScheduleID, DepartmentID) VALUES ('45', '10004', '2024-06-15', '00:00:00', '07:00:00', '晚班', '44', '3');
INSERT INTO schedules (ScheduleID, DoctorID, ScheduleDate, StartTime, EndTime, Shift, RelatedScheduleID, DepartmentID) VALUES ('46', '10001', '2024-06-03', '08:30:00', '12:00:00', '早班', '', '1');
INSERT INTO schedules (ScheduleID, DoctorID, ScheduleDate, StartTime, EndTime, Shift, RelatedScheduleID, DepartmentID) VALUES ('47', '10001', '2024-06-03', '14:00:00', '17:30:00', '下午班', '', '1');
INSERT INTO schedules (ScheduleID, DoctorID, ScheduleDate, StartTime, EndTime, Shift, RelatedScheduleID, DepartmentID) VALUES ('48', '10002', '2024-06-03', '08:30:00', '12:00:00', '早班', '', '1');
INSERT INTO schedules (ScheduleID, DoctorID, ScheduleDate, StartTime, EndTime, Shift, RelatedScheduleID, DepartmentID) VALUES ('49', '10011', '2024-06-03', '08:30:00', '12:00:00', '早班', '', '3');
INSERT INTO schedules (ScheduleID, DoctorID, ScheduleDate, StartTime, EndTime, Shift, RelatedScheduleID, DepartmentID) VALUES ('50', '10001', '2024-06-04', '18:00:00', '23:59:59', '晚班', '51', '1');
INSERT INTO schedules (ScheduleID, DoctorID, ScheduleDate, StartTime, EndTime, Shift, RelatedScheduleID, DepartmentID) VALUES ('51', '10001', '2024-06-05', '00:00:00', '07:00:00', '晚班', '50', '1');
INSERT INTO schedules (ScheduleID, DoctorID, ScheduleDate, StartTime, EndTime, Shift, RelatedScheduleID, DepartmentID) VALUES ('54', '10001', '2026-04-16', '08:30:00', '12:00:00', '早班', '', '1');

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(50) NOT NULL,
  `PasswordHash` varchar(64) NOT NULL,
  `UserType` enum('doctor','patient','admin') NOT NULL,
  `UserCell` int(11) DEFAULT NULL,
  PRIMARY KEY (`UserID`,`UserType`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB AUTO_INCREMENT=546214 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('1', 'admin', '$2y$10$LUkl8OrqME4cLcCtNLFwSOposF4Yqm.6tDincfbiHURnAFurg//ku', 'admin', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('1001', '患者1', '$2y$10$gLXsp0AWfOdzurn.I7ZlzOy1b2DlFHsiOpuMfmI8HJp2shmPKLO1G', 'patient', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10001', '王超', '$2y$10$yZxgijWeD69Mmd3CenRgL.Ixz1qsy3a/VNx2Oqk/5rGdslAkgymFK', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10002', '李华', '$2y$10$4p4u6d2hQZtqCJGOIswcJOyfHSle1.Bhs7XZdvYrEemGRxx4M3vsa', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10003', '王蒙', '$2y$10$pai1UXsOy85K98zcqjdNFeB/GcL48.3WN.aLBtSYthiVVX3UCAZ0S', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10004', '包利民', '$2y$10$Lb6UdKIKbK297n9d.2eZQul1uBhRVU2.lvAeM3PaoBk7gQ94Wexrm', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10005', '张鹏', '$2y$10$z0YcT.BFC2wMF70b31lDR.x.v9ySxD6dqa1EYnlqP30IEQElZSUuu', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10006', '郑桐', '$2y$10$uG2rt/zpsGhe0iV9eRn9Zun3FOvG363ZDheUPJ8QUuWBJR6EcbScq', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10007', '何平', '$2y$10$KajkOCe3QqSQBR6gY6EXSOE3THVtz/kXN2evukggLJBnM6EhLdCFC', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10008', '魏鹏宇', '$2y$10$Z0QqpcwXSx5GIYksogfvTeLDBCRVfCh3WVUa0QqAbgNFg5CQ6KiOi', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10009', '张学庆', '$2y$10$k6KcehyXLmWCwoUwcKFDZeCg2qoxmqqpjfEFjgPx6QC8se9Eofvsu', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10011', '医生', '$2y$10$bOUwYU/3.88ldxE/sSxzqOh6NselBYuKO98HJsplTl/o2NrgEkSr6', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('90001', '张秋爽', '$2y$10$RYl3psnH1.q0ftYdD3ulgeQdxE0klhClOLU6oLn5yc/AZoBJlKJVi', 'patient', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('90002', '李晓莹', '$2y$10$0MoSPHQaS8R2O.JUc65UIukKm3m2a5LS7XXxXfLNCxdRgbkbfG1SS', 'patient', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('90003', '赵天一', '$2y$10$5GMwEHlM1N3n6E9BJDlbVuD2gUbV13u74V1kUklBKzrkbp/RXtnRa', 'patient', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('90004', '吴晗', '$2y$10$6Gqjk.yYoDHwHKAqcKkUg.2iSkRhLdT9TtxvPDzpI6SITbxnT.Nym', 'patient', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('90007', '患者', '$2y$10$DUB9wBMOG8i.Lk6WDGuvnOPrsWiaUZnVwO59zj3GAAv0tmoYeCX4u', 'patient', '');

