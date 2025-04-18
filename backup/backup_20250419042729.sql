CREATE TABLE `appointment` (
  `ap_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int(10) unsigned NOT NULL,
  `pat_id` int(10) unsigned NOT NULL,
  `ap_date` date NOT NULL,
  `ap_time` time NOT NULL,
  `ap_status` int(11) NOT NULL DEFAULT '0' COMMENT '0为已预约 1为正在进行 2为已结束',
  `ap_result` text,
  PRIMARY KEY (`ap_id`),
  UNIQUE KEY `ap_id_UNIQUE` (`ap_id`),
  KEY `fk_ap.patid_ref_ui_idx` (`pat_id`),
  KEY `fk_ap.docid_ref_doc_idx` (`doc_id`),
  CONSTRAINT `fk_ap.docid_ref_doc` FOREIGN KEY (`doc_id`) REFERENCES `doctor` (`doc_id`),
  CONSTRAINT `fk_ap.patid_ref_ui` FOREIGN KEY (`pat_id`) REFERENCES `user_info` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO appointment (ap_id, doc_id, pat_id, ap_date, ap_time, ap_status, ap_result) VALUES ('1', '1', '3', '2025-03-01', '13:43:23', '0', '');
INSERT INTO appointment (ap_id, doc_id, pat_id, ap_date, ap_time, ap_status, ap_result) VALUES ('2', '1', '4', '2025-03-03', '09:45:21', '0', '');

CREATE TABLE `auth_def` (
  `auth_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `auth_name` varchar(10) NOT NULL,
  PRIMARY KEY (`auth_id`),
  UNIQUE KEY `auth_id_UNIQUE` (`auth_id`),
  UNIQUE KEY `auth_name_UNIQUE` (`auth_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

INSERT INTO auth_def (auth_id, auth_name) VALUES ('1', 'admin');
INSERT INTO auth_def (auth_id, auth_name) VALUES ('3', '医生');
INSERT INTO auth_def (auth_id, auth_name) VALUES ('4', '患者');
INSERT INTO auth_def (auth_id, auth_name) VALUES ('2', '药房管理员');

CREATE TABLE `department` (
  `dep_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dep_name` varchar(20) NOT NULL,
  PRIMARY KEY (`dep_id`),
  UNIQUE KEY `dep_id_UNIQUE` (`dep_id`),
  UNIQUE KEY `dep_name_UNIQUE` (`dep_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

INSERT INTO department (dep_id, dep_name) VALUES ('3', '儿科');
INSERT INTO department (dep_id, dep_name) VALUES ('1', '内科');
INSERT INTO department (dep_id, dep_name) VALUES ('2', '外科');
INSERT INTO department (dep_id, dep_name) VALUES ('4', '妇产科');
INSERT INTO department (dep_id, dep_name) VALUES ('5', '眼科');

;

INSERT INTO departments (DepartmentId, Department) VALUES ('3', '儿科');
INSERT INTO departments (DepartmentId, Department) VALUES ('1', '内科');
INSERT INTO departments (DepartmentId, Department) VALUES ('2', '外科');
INSERT INTO departments (DepartmentId, Department) VALUES ('4', '妇产科');
INSERT INTO departments (DepartmentId, Department) VALUES ('5', '眼科');

CREATE TABLE `doc_title` (
  `title_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title_name` varchar(10) NOT NULL,
  PRIMARY KEY (`title_id`),
  UNIQUE KEY `title_name_UNIQUE` (`title_name`),
  UNIQUE KEY `titile_id_UNIQUE` (`title_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

INSERT INTO doc_title (title_id, title_name) VALUES ('1', '专家医师');
INSERT INTO doc_title (title_id, title_name) VALUES ('2', '主任医师');
INSERT INTO doc_title (title_id, title_name) VALUES ('3', '副主任医师');
INSERT INTO doc_title (title_id, title_name) VALUES ('4', '普通医师');

CREATE TABLE `doctor` (
  `doc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'doctor在doctor表中单独的id与doctor的userid不一样',
  `doc_uid` int(10) unsigned NOT NULL COMMENT 'doctor的userid',
  `dep_id` int(10) unsigned NOT NULL,
  `title_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`doc_id`),
  UNIQUE KEY `doc_id_UNIQUE` (`doc_id`),
  UNIQUE KEY `doc_uid_UNIQUE` (`doc_uid`),
  KEY `fk_doc.depid_ref_dep_idx` (`dep_id`),
  KEY `fk_doc.titleid_ref_doctitle_idx` (`title_id`),
  CONSTRAINT `fk_doc.depid_ref_dep` FOREIGN KEY (`dep_id`) REFERENCES `department` (`dep_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_doc.docid_ref_user` FOREIGN KEY (`doc_uid`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_doc.titleid_ref_doctitle` FOREIGN KEY (`title_id`) REFERENCES `doc_title` (`title_id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO doctor (doc_id, doc_uid, dep_id, title_id) VALUES ('1', '2', '1', '1');

;

INSERT INTO doctors (DoctorID, FullName, DepartmentID, DepartmentName, Title) VALUES ('1', '医生1', '1', '内科', '专家医师');

CREATE TABLE `drug_def` (
  `drug_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `drug_name` varchar(45) NOT NULL,
  `drug_price` decimal(10,2) NOT NULL,
  `drug_store` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`drug_id`),
  UNIQUE KEY `drug_id_UNIQUE` (`drug_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

INSERT INTO drug_def (drug_id, drug_name, drug_price, drug_store) VALUES ('1', '头孢', '9.98', '808');
INSERT INTO drug_def (drug_id, drug_name, drug_price, drug_store) VALUES ('2', '布洛芬颗粒', '10.85', '900');
INSERT INTO drug_def (drug_id, drug_name, drug_price, drug_store) VALUES ('3', '莲花清瘟胶囊', '10.98', '10000');
INSERT INTO drug_def (drug_id, drug_name, drug_price, drug_store) VALUES ('4', '左氧氟沙星片', '20.84', '9950');
INSERT INTO drug_def (drug_id, drug_name, drug_price, drug_store) VALUES ('5', '阿昔洛韦滴眼液', '15.41', '10000');
INSERT INTO drug_def (drug_id, drug_name, drug_price, drug_store) VALUES ('6', '蒙脱石散', '12.00', '430');
INSERT INTO drug_def (drug_id, drug_name, drug_price, drug_store) VALUES ('7', '麝香壮骨膏', '9.00', '10000');
INSERT INTO drug_def (drug_id, drug_name, drug_price, drug_store) VALUES ('8', '牛黄醒消丸', '42.85', '400');

CREATE TABLE `drug_record` (
  `drug_rcd_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `drug_id` int(10) unsigned NOT NULL,
  `oper_amount` int(11) NOT NULL,
  `status_code` int(11) NOT NULL COMMENT '0是减少；1是增加；',
  `oper_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`drug_rcd_id`),
  UNIQUE KEY `drug_rcd_id_UNIQUE` (`drug_rcd_id`),
  KEY `fk_drugrcd.drugid_ref_def_idx` (`drug_id`),
  CONSTRAINT `fk_drugrcd.drugid_ref_def` FOREIGN KEY (`drug_id`) REFERENCES `drug_def` (`drug_id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO drug_record (drug_rcd_id, drug_id, oper_amount, status_code, oper_time) VALUES ('1', '1', '2', '0', '2025-04-18 17:58:29');

;

INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('1', '头孢', '9.98', '808');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('2', '布洛芬颗粒', '10.85', '900');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('3', '莲花清瘟胶囊', '10.98', '10000');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('4', '左氧氟沙星片', '20.84', '9950');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('5', '阿昔洛韦滴眼液', '15.41', '10000');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('6', '蒙脱石散', '12.00', '430');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('7', '麝香壮骨膏', '9.00', '10000');
INSERT INTO drugs (DrugID, DrugName, Price, StockQuantity) VALUES ('8', '牛黄醒消丸', '42.85', '400');

CREATE TABLE `exam_record` (
  `exam_rcd_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int(10) unsigned NOT NULL,
  `ap_id` int(10) unsigned NOT NULL COMMENT '应该通过触发器自动生成',
  `exam_def_id` int(10) unsigned NOT NULL,
  `status_code` int(11) NOT NULL COMMENT '0是未检查；1是已付款；2是已付款已检查；3是未付款已检查',
  `oper_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`exam_rcd_id`),
  UNIQUE KEY `exam_rcd_id_UNIQUE` (`exam_rcd_id`),
  KEY `fk_examrcd.examdefid_ref_def_idx` (`exam_def_id`),
  KEY `fk_examrcd.apid_ref_ap_idx` (`ap_id`),
  CONSTRAINT `fk_examrcd.apid_ref_ap` FOREIGN KEY (`ap_id`) REFERENCES `appointment` (`ap_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_examrcd.examdefid_ref_def` FOREIGN KEY (`exam_def_id`) REFERENCES `examination_def` (`exam_def_id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO exam_record (exam_rcd_id, exam_id, ap_id, exam_def_id, status_code, oper_time) VALUES ('1', '1', '1', '1', '0', '2025-04-18 17:58:29');
INSERT INTO exam_record (exam_rcd_id, exam_id, ap_id, exam_def_id, status_code, oper_time) VALUES ('2', '1', '1', '1', '1', '2025-04-18 17:58:29');
INSERT INTO exam_record (exam_rcd_id, exam_id, ap_id, exam_def_id, status_code, oper_time) VALUES ('3', '1', '1', '1', '2', '2025-04-18 17:58:29');

CREATE TABLE `exam_result` (
  `exam_id` int(10) unsigned NOT NULL,
  `exam_result` text NOT NULL,
  PRIMARY KEY (`exam_id`),
  UNIQUE KEY `exam_id_UNIQUE` (`exam_id`),
  CONSTRAINT `fk_examres.examid_ref_exam` FOREIGN KEY (`exam_id`) REFERENCES `exam_record` (`exam_rcd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


;

INSERT INTO examination (ExaminationRecordID, AppointmentID, ExaminationItem, ExaminationPrice, ExaminationResult) VALUES ('1', '1', 'CT', '150.00', '');

CREATE TABLE `examination_def` (
  `exam_def_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exam_name` varchar(45) NOT NULL,
  `exam_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`exam_def_id`),
  UNIQUE KEY `exam_def_id_UNIQUE` (`exam_def_id`),
  UNIQUE KEY `exam_name_UNIQUE` (`exam_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO examination_def (exam_def_id, exam_name, exam_price) VALUES ('1', 'CT', '150.00');
INSERT INTO examination_def (exam_def_id, exam_name, exam_price) VALUES ('2', '核磁', '200.00');

;

INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES ('3', '病人1', '0', '45');
INSERT INTO patients (PatientID, FullName, Gender, Age) VALUES ('4', '病人2', '1', '58');

;

INSERT INTO payment (AppointmentID, Payed, Unpayed, Total) VALUES ('1', '169.96', '0.00', '169.96');

CREATE TABLE `prescription_record` (
  `pres_rcd_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pres_id` int(10) unsigned NOT NULL,
  `ap_id` int(10) unsigned NOT NULL,
  `drug_id` int(10) unsigned NOT NULL,
  `status_code` int(11) NOT NULL COMMENT '0是开药；1是已付款；2是已付款已发药；3是已发药未付款',
  `oper_amount` int(11) NOT NULL,
  `oper_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pres_rcd_id`),
  UNIQUE KEY `pres_rcd_id_UNIQUE` (`pres_rcd_id`),
  KEY `fk_resrcd.drugid_ref_drug_idx` (`drug_id`),
  KEY `fk_presrcd.apid_ref_ap_idx` (`ap_id`),
  CONSTRAINT `fk_presrcd.apid_ref_ap` FOREIGN KEY (`ap_id`) REFERENCES `appointment` (`ap_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_presrcd.drugid_ref_drug` FOREIGN KEY (`drug_id`) REFERENCES `drug_def` (`drug_id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO prescription_record (pres_rcd_id, pres_id, ap_id, drug_id, status_code, oper_amount, oper_time) VALUES ('1', '1', '1', '1', '0', '2', '2025-04-18 17:58:29');
INSERT INTO prescription_record (pres_rcd_id, pres_id, ap_id, drug_id, status_code, oper_amount, oper_time) VALUES ('2', '1', '1', '1', '1', '2', '2025-04-18 17:58:29');
INSERT INTO prescription_record (pres_rcd_id, pres_id, ap_id, drug_id, status_code, oper_amount, oper_time) VALUES ('3', '1', '1', '1', '2', '2', '2025-04-18 17:58:29');

;

INSERT INTO prescription_table (PrescriptionID, AppointmentID, DrugName, DrugQuantity, DrugPrice, DrugSumPrice) VALUES ('1', '1', '头孢', '2', '9.98', '19.96');

CREATE TABLE `schedule` (
  `schedule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`schedule_id`),
  UNIQUE KEY `schedule_id_UNIQUE` (`schedule_id`),
  KEY `fk_docid_ref_doctor_idx` (`doc_id`),
  CONSTRAINT `fk_docid_ref_doctor` FOREIGN KEY (`doc_id`) REFERENCES `doctor` (`doc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

INSERT INTO schedule (schedule_id, doc_id, date, start_time, end_time) VALUES ('3', '1', '2025-05-01', '14:00:00', '17:30:00');
INSERT INTO schedule (schedule_id, doc_id, date, start_time, end_time) VALUES ('4', '1', '2025-05-01', '18:00:00', '07:00:00');
INSERT INTO schedule (schedule_id, doc_id, date, start_time, end_time) VALUES ('5', '1', '2025-05-02', '18:00:00', '07:00:00');

CREATE TABLE `user` (
  `user_id` int(10) unsigned NOT NULL,
  `user_acc` varchar(20) NOT NULL,
  `pass_hash` varchar(64) NOT NULL,
  `user_auth` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_id_UNIQUE` (`user_id`),
  UNIQUE KEY `user_acc_UNIQUE` (`user_acc`),
  KEY `fk_user.auth_ref_auth_idx` (`user_auth`),
  CONSTRAINT `fk_user.auth_ref_auth` FOREIGN KEY (`user_auth`) REFERENCES `auth_def` (`auth_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO user (user_id, user_acc, pass_hash, user_auth) VALUES ('1', '0000000', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', '1');
INSERT INTO user (user_id, user_acc, pass_hash, user_auth) VALUES ('2', 'doctor_test1', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', '3');
INSERT INTO user (user_id, user_acc, pass_hash, user_auth) VALUES ('3', 'patient_test1', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', '4');
INSERT INTO user (user_id, user_acc, pass_hash, user_auth) VALUES ('4', 'patient_test2', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', '4');

CREATE TABLE `user_info` (
  `user_id` int(10) unsigned NOT NULL,
  `user_name` varchar(20) NOT NULL DEFAULT 'UNSET',
  `user_cell` varchar(11) NOT NULL,
  `user_gender` int(11) DEFAULT NULL,
  `user_age` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_id_UNIQUE` (`user_id`),
  UNIQUE KEY `user_cell_UNIQUE` (`user_cell`),
  CONSTRAINT `fk_user_info_ref_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO user_info (user_id, user_name, user_cell, user_gender, user_age) VALUES ('1', '管理员', '00000000000', '1', '20');
INSERT INTO user_info (user_id, user_name, user_cell, user_gender, user_age) VALUES ('2', '医生1', '13011112222', '1', '60');
INSERT INTO user_info (user_id, user_name, user_cell, user_gender, user_age) VALUES ('3', '病人1', '12011112222', '0', '45');
INSERT INTO user_info (user_id, user_name, user_cell, user_gender, user_age) VALUES ('4', '病人2', '12011112223', '1', '58');

;

INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('1', '管理员', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', 'admin', '00000000000');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('2', '医生1', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', '医生', '13011112222');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('3', '病人1', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', '患者', '12011112222');
INSERT INTO users (UserID, Username, PasswordHash, UserType, UserCell) VALUES ('4', '病人2', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', '患者', '12011112223');

