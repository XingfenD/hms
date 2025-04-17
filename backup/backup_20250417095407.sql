CREATE TABLE `appointments` (
  `AppointmentID` int(11) NOT NULL AUTO_INCREMENT,
  `PatientID` int(11) DEFAULT NULL,
  `DoctorID` int(11) DEFAULT NULL,
  `AppointmentDate` date DEFAULT NULL,
  `AppointmentTime` enum('08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','18:00') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
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

INSERT INTO appointments (AppointmentID, PatientID, DoctorID, AppointmentDate, AppointmentTime, AppointmentStatus, DepartmentID) VALUES ('66', '90002', '10003', '2024-06-22', '14:30', '已预约', '2');
INSERT INTO appointments (AppointmentID, PatientID, DoctorID, AppointmentDate, AppointmentTime, AppointmentStatus, DepartmentID) VALUES ('67', '90001', '10010', '2025-04-13', '14:00', '已就诊', '5');
INSERT INTO appointments (AppointmentID, PatientID, DoctorID, AppointmentDate, AppointmentTime, AppointmentStatus, DepartmentID) VALUES ('68', '90001', '10003', '2024-06-02', '14:00', '待就诊', '2');

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
INSERT INTO doctors (DoctorID, FullName, DepartmentID, Title) VALUES ('10010', '魏传博', '5', '');

CREATE TABLE `drugs` (
  `DrugID` int(11) NOT NULL AUTO_INCREMENT,
  `DrugName` varchar(255) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `StockQuantity` int(11) DEFAULT '0',
  PRIMARY KEY (`DrugID`),
  KEY `DrugName` (`DrugName`),
  KEY `Price` (`Price`),
  KEY `idx_drug_name_price` (`DrugName`,`Price`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('1', '头孢', '9.98', '810');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('2', '布洛芬颗粒', '10.85', '900');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('3', '莲花清瘟胶囊', '10.98', '10000');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('4', '左氧氟沙星片', '20.84', '9950');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('5', '阿昔洛韦滴眼液', '15.41', '10000');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('6', '蒙脱石散', '12.00', '430');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('7', '麝香壮骨膏', '9.00', '10000');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('8', '牛黄醒消丸', '42.85', '400');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('10', '测试1', '100.00', '100');

CREATE TABLE `examination` (
  `ExaminationID` int(11) NOT NULL AUTO_INCREMENT,
  `ExaminationName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`ExaminationID`),
  KEY `ExaminationName` (`ExaminationName`),
  KEY `Price` (`Price`),
  KEY `idx_examination_name_price` (`ExaminationName`,`Price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `examination_records` (
  `ExaminationRecordID` int(11) NOT NULL AUTO_INCREMENT,
  `ListID` int(11) NOT NULL,
  `ExaminationItem` varchar(255) NOT NULL,
  `ExaminationPrice` decimal(10,2) NOT NULL,
  `ExaminationResult` text,
  PRIMARY KEY (`ExaminationRecordID`),
  KEY `ListID` (`ListID`),
  KEY `ExaminationItem` (`ExaminationItem`,`ExaminationPrice`),
  CONSTRAINT `examination_records_ibfk_1` FOREIGN KEY (`ListID`) REFERENCES `list_table` (`ListID`),
  CONSTRAINT `examination_records_ibfk_2` FOREIGN KEY (`ExaminationItem`, `ExaminationPrice`) REFERENCES `examination` (`ExaminationName`, `Price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `list_table` (
  `ListID` int(11) NOT NULL AUTO_INCREMENT,
  `DoctorID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `PrescriptionDate` date NOT NULL,
  `TotalPrice` decimal(10,2) DEFAULT '0.00',
  `PaymentStatus` varchar(20) DEFAULT '未缴费',
  PRIMARY KEY (`ListID`),
  KEY `DoctorID` (`DoctorID`),
  KEY `PatientID` (`PatientID`),
  CONSTRAINT `list_table_ibfk_1` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`DoctorID`),
  CONSTRAINT `list_table_ibfk_2` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `patients` (
  `PatientID` int(11) NOT NULL AUTO_INCREMENT,
  `FullName` varchar(100) NOT NULL,
  `Gender` enum('男','女','其他') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Age` int(11) NOT NULL,
  PRIMARY KEY (`PatientID`)
) ENGINE=InnoDB AUTO_INCREMENT=90006 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES ('90001', '张秋爽', '女', '45');
INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES ('90002', '李晓莹', '女', '36');
INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES ('90003', '赵天一', '男', '10');
INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES ('90004', '吴晗', '男', '20');
INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES ('90005', '刘佳烁', '女', '25');

CREATE TABLE `prescription_table` (
  `PrescriptionID` int(11) NOT NULL AUTO_INCREMENT,
  `ListID` int(11) NOT NULL,
  `DrugName` varchar(255) NOT NULL,
  `DrugQuantity` int(11) NOT NULL,
  `DrugPrice` decimal(10,2) NOT NULL,
  `DrugSumPrice` decimal(10,2) NOT NULL,
  PRIMARY KEY (`PrescriptionID`),
  KEY `ListID` (`ListID`),
  KEY `DrugName` (`DrugName`,`DrugPrice`),
  CONSTRAINT `prescription_table_ibfk_1` FOREIGN KEY (`ListID`) REFERENCES `list_table` (`ListID`),
  CONSTRAINT `prescription_table_ibfk_2` FOREIGN KEY (`DrugName`, `DrugPrice`) REFERENCES `drugs` (`DrugName`, `Price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


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
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO schedules (ScheduleID, DoctorID, ScheduleDate, StartTime, EndTime, Shift, RelatedScheduleID, DepartmentID) VALUES ('51', '10010', '2025-04-13', '14:00:00', '17:30:00', '下午班', '', '5');

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(50) NOT NULL,
  `PasswordHash` varchar(64) NOT NULL,
  `UserType` enum('doctor','patient','admin') NOT NULL,
  `UserCell` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`UserID`,`UserType`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB AUTO_INCREMENT=546214 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('1', 'admin', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', 'admin', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10001', '王超', '$2y$10$yZxgijWeD69Mmd3CenRgL.Ixz1qsy3a/VNx2Oqk/5rGdslAkgymFK', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10002', '李华', '$2y$10$4p4u6d2hQZtqCJGOIswcJOyfHSle1.Bhs7XZdvYrEemGRxx4M3vsa', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10003', '王蒙', '$2y$10$pai1UXsOy85K98zcqjdNFeB/GcL48.3WN.aLBtSYthiVVX3UCAZ0S', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10004', '包利民', '$2y$10$Lb6UdKIKbK297n9d.2eZQul1uBhRVU2.lvAeM3PaoBk7gQ94Wexrm', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10005', '张鹏', '$2y$10$z0YcT.BFC2wMF70b31lDR.x.v9ySxD6dqa1EYnlqP30IEQElZSUuu', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10006', '郑桐', '$2y$10$uG2rt/zpsGhe0iV9eRn9Zun3FOvG363ZDheUPJ8QUuWBJR6EcbScq', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10007', '何平', '$2y$10$KajkOCe3QqSQBR6gY6EXSOE3THVtz/kXN2evukggLJBnM6EhLdCFC', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10008', '魏鹏宇', '$2y$10$Z0QqpcwXSx5GIYksogfvTeLDBCRVfCh3WVUa0QqAbgNFg5CQ6KiOi', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10009', '张学庆', '$2y$10$df2wT.rHe1BMtwj/e6AABeVHykXGKquwNafJcrL9vSkF1HTZQ5U6C', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('10010', '魏传博', '$2y$10$35SuUROtPj4E5HXwim8CHeIkdkDPCOkIn1QCoDN9iE9c6xHmae9CW', 'doctor', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('90001', '张秋爽', '$2y$10$RYl3psnH1.q0ftYdD3ulgeQdxE0klhClOLU6oLn5yc/AZoBJlKJVi', 'patient', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('90002', '李晓莹', '$2y$10$0MoSPHQaS8R2O.JUc65UIukKm3m2a5LS7XXxXfLNCxdRgbkbfG1SS', 'patient', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('90003', '赵天一', '$2y$10$5GMwEHlM1N3n6E9BJDlbVuD2gUbV13u74V1kUklBKzrkbp/RXtnRa', 'patient', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('90004', '吴晗', '$2y$10$6Gqjk.yYoDHwHKAqcKkUg.2iSkRhLdT9TtxvPDzpI6SITbxnT.Nym', 'patient', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('90005', '刘佳烁', '$2y$10$9QGzRceebu9Vz7btTpNfCu/fyWx6kX8PZWCsEZxoFued3tEvZ1QdW', 'patient', '');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('90006', '李赞', '$2y$10$XatsUAUi.P4i.P/7mn4oA.LM7t7wMvHEl0Ihjn2vMal2wzonOMnJG', 'patient', '');

