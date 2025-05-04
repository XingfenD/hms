-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: localhost    Database: yiliao2
-- ------------------------------------------------------
-- Server version	8.0.17

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ap_info`
--

DROP TABLE IF EXISTS `ap_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ap_info` (
  `ap_id` int(10) unsigned NOT NULL,
  `pat_requirements` text COMMENT '主诉',
  `pat_history` text COMMENT '过往病史',
  `pat_now_history` text COMMENT '现病史',
  `diagnosis` text COMMENT '初步诊断',
  `treat_method` text,
  PRIMARY KEY (`ap_id`),
  UNIQUE KEY `ap_id_UNIQUE` (`ap_id`),
  CONSTRAINT `fk_apinfo.apid_ref_ap` FOREIGN KEY (`ap_id`) REFERENCES `appointment` (`ap_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_info`
--

LOCK TABLES `ap_info` WRITE;
/*!40000 ALTER TABLE `ap_info` DISABLE KEYS */;
INSERT INTO `ap_info` VALUES (3,'头疼，浑身乏力','有过感冒病史','最近两天开始头疼，伴有发热','感冒','多喝水，休息，服用感冒药'),(4,'咳嗽，嗓子疼','无特殊病史','咳嗽三天，嗓子疼加剧','上呼吸道感染','服用消炎药，止咳药'),(5,'肚子疼','有过肠胃炎病史','今天早上开始肚子疼，伴有腹泻','肠胃炎','注意饮食，服用止泻药'),(6,'牙疼','无特殊病史','牙疼两天，影响进食','牙髓炎','建议去牙科治疗');
/*!40000 ALTER TABLE `ap_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointment`
--

DROP TABLE IF EXISTS `appointment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointment` (
  `ap_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int(10) unsigned NOT NULL,
  `pat_id` int(10) unsigned NOT NULL,
  `ap_sc_id` int(10) unsigned NOT NULL,
  `ap_date` date NOT NULL,
  `ap_time` time NOT NULL,
  `ap_status` int(11) NOT NULL DEFAULT '0' COMMENT '0为已预约 1为正在进行 2为已结束,3为过号，4为患者已签到',
  PRIMARY KEY (`ap_id`),
  UNIQUE KEY `ap_id_UNIQUE` (`ap_id`),
  KEY `fk_ap.patid_ref_ui_idx` (`pat_id`),
  KEY `fk_ap.docid_ref_doc_idx` (`doc_id`),
  KEY `fk_ap.apscid_ref_sc_idx` (`ap_sc_id`),
  CONSTRAINT `fk_ap.apscid_ref_sc` FOREIGN KEY (`ap_sc_id`) REFERENCES `schedule` (`schedule_id`),
  CONSTRAINT `fk_ap.docid_ref_doc` FOREIGN KEY (`doc_id`) REFERENCES `doctor` (`doc_id`),
  CONSTRAINT `fk_ap.patid_ref_ui` FOREIGN KEY (`pat_id`) REFERENCES `user_info` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment`
--

LOCK TABLES `appointment` WRITE;
/*!40000 ALTER TABLE `appointment` DISABLE KEYS */;
INSERT INTO `appointment` VALUES (1,1,3,1,'2025-03-01','13:43:23',0),(2,1,4,1,'2025-03-03','09:45:21',0),(3,1,3,4,'2025-05-01','09:30:00',0),(4,1,4,5,'2025-05-01','14:30:00',0),(5,1,3,1,'2025-05-02','15:00:00',1),(6,1,4,4,'2025-05-03','10:00:00',2),(7,1,3,1,'2025-04-18','14:15:00',0),(8,1,4,1,'2025-04-18','15:00:00',0),(9,1,3,1,'2025-04-18','15:45:00',0),(10,1,4,1,'2025-04-18','16:30:00',0),(11,1,3,1,'2025-04-18','17:15:00',0),(12,1,3,4,'2025-05-01','09:15:00',0),(13,1,4,4,'2025-05-01','10:00:00',0),(14,1,3,4,'2025-05-01','10:45:00',0),(15,1,4,4,'2025-05-01','11:30:00',0),(16,1,3,4,'2025-05-01','11:45:00',0),(17,1,3,5,'2025-05-01','13:15:00',0),(18,1,4,5,'2025-05-01','14:00:00',0),(19,1,3,5,'2025-05-01','14:45:00',0),(20,1,4,5,'2025-05-01','15:30:00',0),(21,1,3,5,'2025-05-01','16:15:00',0),(22,1,3,1,'2025-04-18','18:00:00',0),(23,1,4,1,'2025-04-18','18:45:00',0),(24,1,3,1,'2025-04-18','19:30:00',0),(25,1,4,1,'2025-04-18','19:45:00',0),(26,1,3,1,'2025-04-18','19:55:00',0);
/*!40000 ALTER TABLE `appointment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!50001 DROP VIEW IF EXISTS `appointments`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `appointments` AS SELECT 
 1 AS `AppointmentID`,
 1 AS `PatientID`,
 1 AS `DoctorID`,
 1 AS `DoctorOwnID`,
 1 AS `PatientName`,
 1 AS `DoctorName`,
 1 AS `Title`,
 1 AS `AppointmentDate`,
 1 AS `AppointmentTime`,
 1 AS `AppointmentStatus`,
 1 AS `DepartmentID`,
 1 AS `DepartmentName`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `auth_def`
--

DROP TABLE IF EXISTS `auth_def`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_def` (
  `auth_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `auth_name` varchar(10) NOT NULL,
  PRIMARY KEY (`auth_id`),
  UNIQUE KEY `auth_id_UNIQUE` (`auth_id`),
  UNIQUE KEY `auth_name_UNIQUE` (`auth_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_def`
--

LOCK TABLES `auth_def` WRITE;
/*!40000 ALTER TABLE `auth_def` DISABLE KEYS */;
INSERT INTO `auth_def` VALUES (1,'admin'),(3,'医生'),(4,'患者'),(2,'药房管理员');
/*!40000 ALTER TABLE `auth_def` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department`
--

DROP TABLE IF EXISTS `department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `department` (
  `dep_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dep_name` varchar(20) NOT NULL,
  PRIMARY KEY (`dep_id`),
  UNIQUE KEY `dep_id_UNIQUE` (`dep_id`),
  UNIQUE KEY `dep_name_UNIQUE` (`dep_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department`
--

LOCK TABLES `department` WRITE;
/*!40000 ALTER TABLE `department` DISABLE KEYS */;
INSERT INTO `department` VALUES (3,'儿科'),(1,'内科'),(7,'口腔科'),(2,'外科'),(4,'妇产科'),(6,'皮肤科'),(5,'眼科'),(8,'骨科');
/*!40000 ALTER TABLE `department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!50001 DROP VIEW IF EXISTS `departments`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `departments` AS SELECT 
 1 AS `DepartmentId`,
 1 AS `Department`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `doc_title`
--

DROP TABLE IF EXISTS `doc_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doc_title` (
  `title_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title_name` varchar(10) NOT NULL,
  PRIMARY KEY (`title_id`),
  UNIQUE KEY `title_name_UNIQUE` (`title_name`),
  UNIQUE KEY `titile_id_UNIQUE` (`title_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doc_title`
--

LOCK TABLES `doc_title` WRITE;
/*!40000 ALTER TABLE `doc_title` DISABLE KEYS */;
INSERT INTO `doc_title` VALUES (1,'专家医师'),(2,'主任医师'),(3,'副主任医师'),(4,'普通医师');
/*!40000 ALTER TABLE `doc_title` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctor`
--

DROP TABLE IF EXISTS `doctor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctor`
--

LOCK TABLES `doctor` WRITE;
/*!40000 ALTER TABLE `doctor` DISABLE KEYS */;
INSERT INTO `doctor` VALUES (1,2,1,1),(3,15,1,1),(4,16,2,2),(5,17,3,3),(6,18,4,4),(7,19,5,1),(8,20,1,2),(9,21,2,3),(10,22,3,4),(11,23,4,1),(12,24,5,2);
/*!40000 ALTER TABLE `doctor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `doctors`
--

DROP TABLE IF EXISTS `doctors`;
/*!50001 DROP VIEW IF EXISTS `doctors`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `doctors` AS SELECT 
 1 AS `DoctorID`,
 1 AS `DoctorOwnID`,
 1 AS `FullName`,
 1 AS `DepartmentID`,
 1 AS `DepartmentName`,
 1 AS `Title`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `drug_def`
--

DROP TABLE IF EXISTS `drug_def`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `drug_def` (
  `drug_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `drug_name` varchar(45) NOT NULL,
  `drug_price` decimal(10,2) NOT NULL,
  `drug_store` int(11) NOT NULL DEFAULT '0',
  `drug_specification` varchar(45) DEFAULT NULL COMMENT '规格',
  `drug_manufacturer` varchar(45) DEFAULT NULL COMMENT '制造商',
  `drug_category` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '类别',
  `drug_instructions` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '说明',
  PRIMARY KEY (`drug_id`),
  UNIQUE KEY `drug_id_UNIQUE` (`drug_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drug_def`
--

LOCK TABLES `drug_def` WRITE;
/*!40000 ALTER TABLE `drug_def` DISABLE KEYS */;
INSERT INTO `drug_def` VALUES (1,'头孢',9.98,806,NULL,NULL,NULL,NULL),(2,'布洛芬颗粒',10.85,900,NULL,NULL,NULL,NULL),(3,'莲花清瘟胶囊',10.98,10000,NULL,NULL,NULL,NULL),(4,'左氧氟沙星片',20.84,9950,NULL,NULL,NULL,NULL),(5,'阿昔洛韦滴眼液',15.41,10000,NULL,NULL,NULL,NULL),(6,'蒙脱石散',12.00,430,NULL,NULL,NULL,NULL),(7,'麝香壮骨膏',9.00,10000,NULL,NULL,NULL,NULL),(8,'牛黄醒消丸',42.85,500,NULL,NULL,NULL,NULL),(10,'板蓝根',2.50,0,'一天三次','白云山','内服','清热解毒'),(11,'板蓝根',2.50,0,'一天三次','白云山','内服',''),(12,'阿莫西林胶囊',15.00,500,'0.25g*24粒','扬子江药业','抗生素','用于细菌感染'),(13,'止咳糖浆',20.00,300,'100ml','太极药业','止咳药','用于止咳化痰'),(14,'健胃消食片',10.00,800,'0.8g*36片','江中制药','助消化药','用于消化不良'),(15,'皮炎平软膏',12.00,600,'20g','三九药业','皮肤科用药','用于皮肤瘙痒');
/*!40000 ALTER TABLE `drug_def` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drug_record`
--

DROP TABLE IF EXISTS `drug_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drug_record`
--

LOCK TABLES `drug_record` WRITE;
/*!40000 ALTER TABLE `drug_record` DISABLE KEYS */;
INSERT INTO `drug_record` VALUES (1,1,808,1,'2025-04-28 16:37:02'),(2,2,900,1,'2025-04-28 16:37:02'),(3,3,10000,1,'2025-04-28 16:37:02'),(4,4,9950,1,'2025-04-28 16:37:02'),(5,5,10000,1,'2025-04-28 16:37:02'),(6,6,430,1,'2025-04-28 16:37:02'),(7,7,10000,1,'2025-04-28 16:37:02'),(8,8,400,1,'2025-04-28 16:37:02'),(9,1,2,0,'2025-04-28 16:37:02'),(10,8,100,0,'2025-04-29 14:21:49'),(11,8,200,1,'2025-04-29 14:22:06');
/*!40000 ALTER TABLE `drug_record` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `drug_record_BEFORE_INSERT` BEFORE INSERT ON `drug_record` FOR EACH ROW BEGIN
	DECLARE v_drug_store INT DEFAULT 0;
    SET NEW.oper_time = NOW();
    SELECT drug_store INTO v_drug_store
    FROM drug_def
    WHERE drug_id = NEW.drug_id;
    
    IF NEW.status_code = 0 THEN 
		IF v_drug_store < NEW.oper_amount THEN
			SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'Drug store amount is less than the required oper amount';
		ELSE
            UPDATE drug_def
			SET drug_store = v_drug_store - NEW.oper_amount
			WHERE drug_def.drug_id = NEW.drug_id;
		END IF;
	ELSE
		UPDATE drug_def
		SET drug_store = v_drug_store + NEW.oper_amount
		WHERE drug_def.drug_id = NEW.drug_id;
    END IF;
    

		
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Temporary view structure for view `drugs`
--

DROP TABLE IF EXISTS `drugs`;
/*!50001 DROP VIEW IF EXISTS `drugs`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `drugs` AS SELECT 
 1 AS `DrugID`,
 1 AS `DrugName`,
 1 AS `Price`,
 1 AS `StockQuantity`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `exam_info`
--

DROP TABLE IF EXISTS `exam_info`;
/*!50001 DROP VIEW IF EXISTS `exam_info`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `exam_info` AS SELECT 
 1 AS `ap_id`,
 1 AS `exam_def_id`,
 1 AS `exam_name`,
 1 AS `exam_price`,
 1 AS `exam_id`,
 1 AS `exam_result`,
 1 AS `exam_finish_time`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `exam_record`
--

DROP TABLE IF EXISTS `exam_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_record` (
  `exam_rcd_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int(10) unsigned NOT NULL COMMENT '由触发器自动生成',
  `ap_id` int(10) unsigned NOT NULL,
  `exam_def_id` int(10) unsigned NOT NULL,
  `status_code` int(11) NOT NULL COMMENT '0是未检查；1是已付款；2是已付款已检查；3是未付款已检查',
  `oper_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`exam_rcd_id`),
  UNIQUE KEY `exam_rcd_id_UNIQUE` (`exam_rcd_id`),
  KEY `fk_examrcd.examdefid_ref_def_idx` (`exam_def_id`),
  KEY `fk_examrcd.apid_ref_ap_idx` (`ap_id`),
  CONSTRAINT `fk_examrcd.apid_ref_ap` FOREIGN KEY (`ap_id`) REFERENCES `appointment` (`ap_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_examrcd.examdefid_ref_def` FOREIGN KEY (`exam_def_id`) REFERENCES `examination_def` (`exam_def_id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_record`
--

LOCK TABLES `exam_record` WRITE;
/*!40000 ALTER TABLE `exam_record` DISABLE KEYS */;
INSERT INTO `exam_record` VALUES (1,1,1,1,0,'2025-04-28 16:37:02'),(2,1,1,1,1,'2025-04-28 16:37:02'),(3,1,1,1,2,'2025-04-28 16:37:02'),(4,3,1,2,0,'2025-04-28 21:07:37'),(5,3,1,2,1,'2025-04-28 21:07:40'),(6,5,1,2,3,'2025-04-28 21:07:42'),(7,5,1,2,0,'2025-04-28 21:10:47'),(8,6,3,4,0,'2025-05-03 17:19:34'),(9,6,3,4,1,'2025-05-03 17:19:34'),(10,6,3,4,2,'2025-05-03 17:19:34'),(11,7,4,5,0,'2025-05-03 17:19:34'),(12,7,4,5,1,'2025-05-03 17:19:34'),(13,8,5,6,0,'2025-05-03 17:19:34');
/*!40000 ALTER TABLE `exam_record` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `exam_record_BEFORE_INSERT` BEFORE INSERT ON `exam_record` FOR EACH ROW BEGIN
	DECLARE new_exam_id INT;
    SET NEW.oper_time = NOW();
    IF NEW.status_code = 0 THEN
		SELECT COALESCE(MAX(exam_id), 0) INTO new_exam_id FROM exam_record;
		SET NEW.exam_id = new_exam_id + 1;
	ELSE 
		SELECT exam_id INTO new_exam_id
        FROM exam_record
        WHERE 
			exam_record.ap_id = NEW.ap_id AND 
            exam_record.exam_def_id = NEW.exam_def_id AND 
            exam_record.status_code = 0;
		IF new_exam_id = NULL THEN
			SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'Forbidden';
		END IF;
		SET NEW.exam_id = new_exam_id;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `exam_result`
--

DROP TABLE IF EXISTS `exam_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_result` (
  `exam_rcd_id` int(10) unsigned NOT NULL,
  `exam_id` int(10) unsigned NOT NULL,
  `exam_result` text NOT NULL,
  PRIMARY KEY (`exam_rcd_id`,`exam_id`),
  UNIQUE KEY `exam_id_UNIQUE` (`exam_id`),
  UNIQUE KEY `exam_rcd_id_UNIQUE` (`exam_rcd_id`),
  CONSTRAINT `fk_examres.examid_ref_exam` FOREIGN KEY (`exam_rcd_id`) REFERENCES `exam_record` (`exam_rcd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_result`
--

LOCK TABLES `exam_result` WRITE;
/*!40000 ALTER TABLE `exam_result` DISABLE KEYS */;
INSERT INTO `exam_result` VALUES (3,1,'非常ok');
/*!40000 ALTER TABLE `exam_result` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `exam_result_BEFORE_INSERT` BEFORE INSERT ON `exam_result` FOR EACH ROW BEGIN
	DECLARE new_rcd_id INT;
	SELECT exam_rcd_id INTO new_rcd_id FROM exam_record
    WHERE exam_id = NEW.exam_id AND status_code = 2;
    SET NEW.exam_rcd_id = new_rcd_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Temporary view structure for view `examination`
--

DROP TABLE IF EXISTS `examination`;
/*!50001 DROP VIEW IF EXISTS `examination`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `examination` AS SELECT 
 1 AS `ExaminationRecordID`,
 1 AS `AppointmentID`,
 1 AS `ExaminationItem`,
 1 AS `ExaminationPrice`,
 1 AS `ExaminationResult`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `examination_def`
--

DROP TABLE IF EXISTS `examination_def`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `examination_def` (
  `exam_def_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exam_name` varchar(45) NOT NULL,
  `exam_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`exam_def_id`),
  UNIQUE KEY `exam_def_id_UNIQUE` (`exam_def_id`),
  UNIQUE KEY `exam_name_UNIQUE` (`exam_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `examination_def`
--

LOCK TABLES `examination_def` WRITE;
/*!40000 ALTER TABLE `examination_def` DISABLE KEYS */;
INSERT INTO `examination_def` VALUES (1,'CT',150.00),(2,'核磁',200.00),(3,'体格检查',20.00),(4,'血常规',30.00),(5,'尿常规',25.00),(6,'B超',180.00);
/*!40000 ALTER TABLE `examination_def` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `patients`
--

DROP TABLE IF EXISTS `patients`;
/*!50001 DROP VIEW IF EXISTS `patients`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `patients` AS SELECT 
 1 AS `PatientID`,
 1 AS `FullName`,
 1 AS `Gender`,
 1 AS `Age`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `payment`
--

DROP TABLE IF EXISTS `payment`;
/*!50001 DROP VIEW IF EXISTS `payment`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `payment` AS SELECT 
 1 AS `AppointmentID`,
 1 AS `Payed`,
 1 AS `Unpayed`,
 1 AS `Total`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `pres_info`
--

DROP TABLE IF EXISTS `pres_info`;
/*!50001 DROP VIEW IF EXISTS `pres_info`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `pres_info` AS SELECT 
 1 AS `ap_id`,
 1 AS `drug_name`,
 1 AS `drug_price`,
 1 AS `drug_specification`,
 1 AS `drug_manufacturer`,
 1 AS `oper_amount`,
 1 AS `use_method`,
 1 AS `doc_comment`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `pres_result`
--

DROP TABLE IF EXISTS `pres_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pres_result` (
  `pres_rcd_id` int(10) unsigned NOT NULL,
  `pres_id` int(10) unsigned NOT NULL,
  `use_method` varchar(45) DEFAULT NULL COMMENT '用法用量',
  `doc_comment` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`pres_rcd_id`,`pres_id`),
  UNIQUE KEY `pres_rcd_id_UNIQUE` (`pres_rcd_id`),
  UNIQUE KEY `pres_id_UNIQUE` (`pres_id`),
  CONSTRAINT `fk_presres.rcd_id_ref_presrcd` FOREIGN KEY (`pres_rcd_id`) REFERENCES `prescription_record` (`pres_rcd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pres_result`
--

LOCK TABLES `pres_result` WRITE;
/*!40000 ALTER TABLE `pres_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `pres_result` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `pres_result_BEFORE_INSERT` BEFORE INSERT ON `pres_result` FOR EACH ROW BEGIN
	DECLARE new_rcd_id INT;
	SELECT pres_rcd_id INTO new_rcd_id FROM prescription_record
    WHERE pres_id = NEW.pres_id AND status_code = 2;
    SET NEW.pres_rcd_id = new_rcd_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `prescription_record`
--

DROP TABLE IF EXISTS `prescription_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prescription_record` (
  `pres_rcd_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pres_id` int(10) unsigned NOT NULL COMMENT '通过触发器自动生成',
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescription_record`
--

LOCK TABLES `prescription_record` WRITE;
/*!40000 ALTER TABLE `prescription_record` DISABLE KEYS */;
INSERT INTO `prescription_record` VALUES (4,2,2,10,0,10,'2025-05-01 18:19:13');
/*!40000 ALTER TABLE `prescription_record` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `prescription_record_BEFORE_INSERT` BEFORE INSERT ON `prescription_record` FOR EACH ROW BEGIN
	DECLARE new_pres_id INT;
    SET NEW.oper_time = NOW();
	IF NEW.status_code = 0 THEN
		SELECT COALESCE(MAX(pres_id), 0) INTO new_pres_id FROM prescription_record;
		SET NEW.pres_id = new_pres_id + 1;
	ELSE 
		SELECT pres_id INTO new_pres_id
        FROM prescription_record
        WHERE 
			prescription_record.ap_id = NEW.ap_id AND 
			prescription_record.drug_id = NEW.drug_id AND
            prescription_record.status_code = 0;
		IF new_pres_id = NULL THEN
			SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'Forbidden';
		END IF;
		SET NEW.pres_id = new_pres_id;
    END IF;
	IF NEW.status_code = 2 THEN
        INSERT INTO drug_record (`drug_id`, `oper_amount`,`status_code`, `oper_time`)
        VALUES (NEW.drug_id, NEW.oper_amount, 0, NOW());
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Temporary view structure for view `prescription_table`
--

DROP TABLE IF EXISTS `prescription_table`;
/*!50001 DROP VIEW IF EXISTS `prescription_table`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `prescription_table` AS SELECT 
 1 AS `PrescriptionID`,
 1 AS `AppointmentID`,
 1 AS `DrugName`,
 1 AS `DrugQuantity`,
 1 AS `DrugPrice`,
 1 AS `DrugSumPrice`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
/*!50001 DROP VIEW IF EXISTS `prescriptions`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `prescriptions` AS SELECT 
 1 AS `pres_id`,
 1 AS `drug_id`,
 1 AS `payed_amount`,
 1 AS `recipe_amount`,
 1 AS `total_amount`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule`
--

LOCK TABLES `schedule` WRITE;
/*!40000 ALTER TABLE `schedule` DISABLE KEYS */;
INSERT INTO `schedule` VALUES (1,1,'2025-04-18','14:00:00','20:00:00'),(4,1,'2025-05-01','09:00:00','12:00:00'),(5,1,'2025-05-01','13:00:00','17:00:00'),(6,1,'2025-06-01','14:00:00','20:00:00'),(7,1,'2025-06-02','14:00:00','20:00:00'),(8,1,'2025-06-03','14:00:00','20:00:00'),(9,1,'2025-06-04','14:00:00','20:00:00'),(10,1,'2025-06-05','14:00:00','20:00:00'),(11,1,'2025-06-06','09:00:00','12:00:00'),(12,1,'2025-06-07','09:00:00','12:00:00'),(13,1,'2025-06-08','09:00:00','12:00:00'),(14,1,'2025-06-09','09:00:00','12:00:00'),(15,1,'2025-06-10','09:00:00','12:00:00'),(16,1,'2025-06-11','13:00:00','17:00:00'),(17,1,'2025-06-12','13:00:00','17:00:00'),(18,1,'2025-06-13','13:00:00','17:00:00'),(19,1,'2025-06-14','13:00:00','17:00:00'),(20,1,'2025-06-15','13:00:00','17:00:00'),(21,1,'2025-06-16','14:00:00','20:00:00'),(22,1,'2025-06-17','09:00:00','12:00:00'),(23,1,'2025-06-18','13:00:00','17:00:00'),(24,1,'2025-06-19','14:00:00','20:00:00'),(25,1,'2025-06-20','09:00:00','12:00:00');
/*!40000 ALTER TABLE `schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!50001 DROP VIEW IF EXISTS `schedules`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `schedules` AS SELECT 
 1 AS `ScheduleID`,
 1 AS `DoctorOwnID`,
 1 AS `DoctorID`,
 1 AS `DoctorName`,
 1 AS `ScheduleDate`,
 1 AS `StartTime`,
 1 AS `EndTime`,
 1 AS `DepartmentID`,
 1 AS `DepartmentName`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_acc` varchar(20) NOT NULL,
  `pass_hash` varchar(64) NOT NULL,
  `user_auth` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_id_UNIQUE` (`user_id`),
  UNIQUE KEY `user_acc_UNIQUE` (`user_acc`),
  KEY `fk_user.auth_ref_auth_idx` (`user_auth`),
  CONSTRAINT `fk_user.auth_ref_auth` FOREIGN KEY (`user_auth`) REFERENCES `auth_def` (`auth_id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'admin','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',1),(2,'doctor_test1','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',3),(3,'patient_test1','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4),(4,'patient_test2','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4),(5,'patient_test5','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4),(6,'patient_test6','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4),(7,'patient_test7','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4),(8,'patient_test8','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4),(9,'patient_test9','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4),(10,'patient_test10','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4),(11,'patient_test11','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4),(12,'patient_test12','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4),(13,'patient_test13','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4),(14,'patient_test14','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4),(15,'doctor_test2','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',3),(16,'doctor_test3','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',3),(17,'doctor_test4','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',3),(18,'doctor_test5','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',3),(19,'doctor_test6','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',3),(20,'doctor_test7','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',3),(21,'doctor_test8','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',3),(22,'doctor_test9','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',3),(23,'doctor_test10','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',3),(24,'doctor_test11','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',3);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_info`
--

DROP TABLE IF EXISTS `user_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_info`
--

LOCK TABLES `user_info` WRITE;
/*!40000 ALTER TABLE `user_info` DISABLE KEYS */;
INSERT INTO `user_info` VALUES (1,'管理员','00000000000',1,20),(2,'医生1','13011112222',1,60),(3,'病人1','12011112222',0,45),(4,'病人2','12011112223',1,58),(5,'病人5','12011112224',0,25),(6,'病人6','12011112225',1,30),(7,'病人7','12011112226',0,35),(8,'病人8','12011112227',1,40),(9,'病人9','12011112228',0,45),(10,'病人10','12011112229',1,50),(11,'病人11','12011112230',0,55),(12,'病人12','12011112231',1,60),(13,'病人13','12011112232',0,65),(14,'病人14','12011112233',1,70),(15,'医生2','13011112234',1,30),(16,'医生3','13011112235',0,35),(17,'医生4','13011112236',1,40),(18,'医生5','13011112237',0,45),(19,'医生6','13011112238',1,50),(20,'医生7','13011112239',0,55),(21,'医生8','13011112240',1,60),(22,'医生9','13011112241',0,65),(23,'医生10','13011112242',1,70),(24,'医生11','13011112243',0,75);
/*!40000 ALTER TABLE `user_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `users`
--

DROP TABLE IF EXISTS `users`;
/*!50001 DROP VIEW IF EXISTS `users`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `users` AS SELECT 
 1 AS `UserID`,
 1 AS `UserAccount`,
 1 AS `Username`,
 1 AS `PasswordHash`,
 1 AS `UserType`,
 1 AS `UserCell`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `appointments`
--

/*!50001 DROP VIEW IF EXISTS `appointments`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `appointments` AS with `apdocinfo` as (select `ap`.`ap_id` AS `ap_id`,`ap`.`doc_id` AS `doc_id`,`dt`.`title_name` AS `doc_title`,`ui`.`user_name` AS `doc_name`,`d`.`dep_id` AS `dep_id`,`dep`.`dep_name` AS `dep_name` from ((((`appointment` `ap` left join `doctor` `d` on((`ap`.`doc_id` = `d`.`doc_id`))) left join `user_info` `ui` on((`d`.`doc_uid` = `ui`.`user_id`))) left join `doc_title` `dt` on((`dt`.`title_id` = `d`.`title_id`))) left join `department` `dep` on((`dep`.`dep_id` = `d`.`dep_id`)))), `appatinfo` as (select `ap`.`ap_id` AS `ap_id`,`ap`.`pat_id` AS `pat_id`,`ui`.`user_name` AS `pat_name` from (`appointment` `ap` left join `user_info` `ui` on((`ui`.`user_id` = `ap`.`pat_id`)))) select `ap`.`ap_id` AS `AppointmentID`,`ap`.`pat_id` AS `PatientID`,`apdocinfo`.`doc_id` AS `DoctorID`,`ap`.`doc_id` AS `DoctorOwnID`,`appatinfo`.`pat_name` AS `PatientName`,`apdocinfo`.`doc_name` AS `DoctorName`,`apdocinfo`.`doc_title` AS `Title`,`ap`.`ap_date` AS `AppointmentDate`,`ap`.`ap_time` AS `AppointmentTime`,`ap`.`ap_status` AS `AppointmentStatus`,`apdocinfo`.`dep_id` AS `DepartmentID`,`apdocinfo`.`dep_name` AS `DepartmentName` from ((`appointment` `ap` left join `apdocinfo` on((`apdocinfo`.`ap_id` = `ap`.`ap_id`))) left join `appatinfo` on((`appatinfo`.`ap_id` = `ap`.`ap_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `departments`
--

/*!50001 DROP VIEW IF EXISTS `departments`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `departments` AS select `department`.`dep_id` AS `DepartmentId`,`department`.`dep_name` AS `Department` from `department` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `doctors`
--

/*!50001 DROP VIEW IF EXISTS `doctors`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `doctors` AS select `doc`.`doc_uid` AS `DoctorID`,`doc`.`doc_id` AS `DoctorOwnID`,`ui`.`user_name` AS `FullName`,`dep`.`dep_id` AS `DepartmentID`,`dep`.`dep_name` AS `DepartmentName`,`t`.`title_name` AS `Title` from (((`doctor` `doc` left join `user_info` `ui` on((`doc`.`doc_uid` = `ui`.`user_id`))) left join `department` `dep` on((`doc`.`dep_id` = `dep`.`dep_id`))) left join `doc_title` `t` on((`doc`.`title_id` = `t`.`title_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `drugs`
--

/*!50001 DROP VIEW IF EXISTS `drugs`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `drugs` AS select `drug_def`.`drug_id` AS `DrugID`,`drug_def`.`drug_name` AS `DrugName`,`drug_def`.`drug_price` AS `Price`,`drug_def`.`drug_store` AS `StockQuantity` from `drug_def` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `exam_info`
--

/*!50001 DROP VIEW IF EXISTS `exam_info`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `exam_info` AS select `ercd`.`ap_id` AS `ap_id`,`edef`.`exam_def_id` AS `exam_def_id`,`edef`.`exam_name` AS `exam_name`,`edef`.`exam_price` AS `exam_price`,`ercd`.`exam_id` AS `exam_id`,`eres`.`exam_result` AS `exam_result`,`ercd`.`oper_time` AS `exam_finish_time` from ((`exam_record` `ercd` left join `exam_result` `eres` on((`eres`.`exam_rcd_id` = `ercd`.`exam_rcd_id`))) left join `examination_def` `edef` on((`edef`.`exam_def_id` = `ercd`.`exam_def_id`))) where (`ercd`.`status_code` = 2) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `examination`
--

/*!50001 DROP VIEW IF EXISTS `examination`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `examination` AS select `e`.`exam_id` AS `ExaminationRecordID`,`e`.`ap_id` AS `AppointmentID`,`e_def`.`exam_name` AS `ExaminationItem`,`e_def`.`exam_price` AS `ExaminationPrice`,`e_res`.`exam_result` AS `ExaminationResult` from ((`exam_record` `e` left join `examination_def` `e_def` on((`e`.`exam_def_id` = `e_def`.`exam_def_id`))) left join `exam_result` `e_res` on((`e`.`exam_id` = `e_res`.`exam_id`))) where (`e`.`status_code` = 0) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `patients`
--

/*!50001 DROP VIEW IF EXISTS `patients`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `patients` AS select `u`.`user_id` AS `PatientID`,`ui`.`user_name` AS `FullName`,`ui`.`user_gender` AS `Gender`,`ui`.`user_age` AS `Age` from ((`user` `u` left join `user_info` `ui` on((`u`.`user_id` = `ui`.`user_id`))) join `auth_def` `ad` on((`u`.`user_auth` = `ad`.`auth_id`))) where (`ad`.`auth_name` = '患者') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `payment`
--

/*!50001 DROP VIEW IF EXISTS `payment`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `payment` AS with `drugtotalsumunpayed` as (select `get_all_drug_sum_for_ap`.`ap_id` AS `ap_id`,sum(`get_all_drug_sum_for_ap`.`single_drug_sum_unpayed`) AS `drug_sum_unpayed` from (select `p`.`ap_id` AS `ap_id`,(`d`.`drug_price` * `p`.`oper_amount`) AS `single_drug_sum_unpayed` from (`prescription_record` `p` join `drug_def` `d` on((`d`.`drug_id` = `p`.`drug_id`))) where (`p`.`status_code` = 0)) `get_all_drug_sum_for_ap` group by `get_all_drug_sum_for_ap`.`ap_id`), `examtotalsumunpayed` as (select `examsumprice`.`ap_id` AS `ap_id`,sum(`examsumprice`.`ExaminationPrice`) AS `exam_sum_unpayed` from (select `e`.`ap_id` AS `ap_id`,`e_def`.`exam_price` AS `ExaminationPrice` from (`exam_record` `e` left join `examination_def` `e_def` on((`e`.`exam_def_id` = `e_def`.`exam_def_id`))) where (`e`.`status_code` = 0)) `examsumprice` group by `examsumprice`.`ap_id`), `drugtotalsumpayed` as (select `get_all_drug_sum_for_ap`.`ap_id` AS `ap_id`,sum(`get_all_drug_sum_for_ap`.`single_drug_sum_unpayed`) AS `drug_sum_payed` from (select `p`.`ap_id` AS `ap_id`,(`d`.`drug_price` * `p`.`oper_amount`) AS `single_drug_sum_unpayed` from (`prescription_record` `p` join `drug_def` `d` on((`d`.`drug_id` = `p`.`drug_id`))) where (`p`.`status_code` = 1)) `get_all_drug_sum_for_ap` group by `get_all_drug_sum_for_ap`.`ap_id`), `examtotalsumpayed` as (select `examsumprice`.`ap_id` AS `ap_id`,sum(`examsumprice`.`ExaminationPrice`) AS `exam_sum_payed` from (select `e`.`ap_id` AS `ap_id`,`e_def`.`exam_price` AS `ExaminationPrice` from (`exam_record` `e` left join `examination_def` `e_def` on((`e`.`exam_def_id` = `e_def`.`exam_def_id`))) where (`e`.`status_code` = 1)) `examsumprice` group by `examsumprice`.`ap_id`) select `ap`.`ap_id` AS `AppointmentID`,(`drugtotalsumpayed`.`drug_sum_payed` + `examtotalsumpayed`.`exam_sum_payed`) AS `Payed`,(((`drugtotalsumunpayed`.`drug_sum_unpayed` + `examtotalsumunpayed`.`exam_sum_unpayed`) - `drugtotalsumpayed`.`drug_sum_payed`) - `examtotalsumpayed`.`exam_sum_payed`) AS `Unpayed`,(`drugtotalsumunpayed`.`drug_sum_unpayed` + `examtotalsumunpayed`.`exam_sum_unpayed`) AS `Total` from ((((`appointment` `ap` join `examtotalsumunpayed` on((`examtotalsumunpayed`.`ap_id` = `ap`.`ap_id`))) join `drugtotalsumunpayed` on((`drugtotalsumunpayed`.`ap_id` = `ap`.`ap_id`))) join `drugtotalsumpayed` on((`drugtotalsumpayed`.`ap_id` = `ap`.`ap_id`))) join `examtotalsumpayed` on((`examtotalsumpayed`.`ap_id` = `ap`.`ap_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `pres_info`
--

/*!50001 DROP VIEW IF EXISTS `pres_info`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `pres_info` AS with `drug_amount` as (select `prescription_record`.`ap_id` AS `ap_id`,`prescription_record`.`drug_id` AS `drug_id`,`prescription_record`.`pres_id` AS `pres_id`,`prescription_record`.`oper_amount` AS `oper_amount` from `prescription_record` where (`prescription_record`.`status_code` = 0)) select `rcd`.`ap_id` AS `ap_id`,`drug_def`.`drug_name` AS `drug_name`,`drug_def`.`drug_price` AS `drug_price`,`drug_def`.`drug_specification` AS `drug_specification`,`drug_def`.`drug_manufacturer` AS `drug_manufacturer`,`drug_amount`.`oper_amount` AS `oper_amount`,`res`.`use_method` AS `use_method`,`res`.`doc_comment` AS `doc_comment` from (((`prescription_record` `rcd` left join `drug_def` on((`drug_def`.`drug_id` = `rcd`.`drug_id`))) left join `drug_amount` on((`drug_amount`.`pres_id` = `rcd`.`pres_id`))) left join `pres_result` `res` on((`res`.`pres_rcd_id` = `rcd`.`pres_rcd_id`))) where (`rcd`.`status_code` = 2) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `prescription_table`
--

/*!50001 DROP VIEW IF EXISTS `prescription_table`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `prescription_table` AS select `p`.`pres_id` AS `PrescriptionID`,`p`.`ap_id` AS `AppointmentID`,`d`.`drug_name` AS `DrugName`,`p`.`oper_amount` AS `DrugQuantity`,`d`.`drug_price` AS `DrugPrice`,(`d`.`drug_price` * `p`.`oper_amount`) AS `DrugSumPrice` from (`prescription_record` `p` join `drug_def` `d` on((`d`.`drug_id` = `p`.`drug_id`))) where (`p`.`status_code` = 0) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `prescriptions`
--

/*!50001 DROP VIEW IF EXISTS `prescriptions`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `prescriptions` AS select `prescription_record`.`pres_id` AS `pres_id`,`prescription_record`.`drug_id` AS `drug_id`,sum((case when (`prescription_record`.`status_code` = 1) then `prescription_record`.`oper_amount` else 0 end)) AS `payed_amount`,sum((case when (`prescription_record`.`status_code` = 2) then `prescription_record`.`oper_amount` else 0 end)) AS `recipe_amount`,sum((case when (`prescription_record`.`status_code` = 0) then `prescription_record`.`oper_amount` else 0 end)) AS `total_amount` from `prescription_record` group by `prescription_record`.`pres_id`,`prescription_record`.`drug_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `schedules`
--

/*!50001 DROP VIEW IF EXISTS `schedules`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `schedules` AS select `sc`.`schedule_id` AS `ScheduleID`,`sc`.`doc_id` AS `DoctorOwnID`,`d`.`doc_uid` AS `DoctorID`,`ui`.`user_name` AS `DoctorName`,`sc`.`date` AS `ScheduleDate`,`sc`.`start_time` AS `StartTime`,`sc`.`end_time` AS `EndTime`,`d`.`dep_id` AS `DepartmentID`,`dep`.`dep_name` AS `DepartmentName` from (((`schedule` `sc` left join `doctor` `d` on((`d`.`doc_id` = `sc`.`doc_id`))) left join `user_info` `ui` on((`d`.`doc_uid` = `ui`.`user_id`))) left join `department` `dep` on((`dep`.`dep_id` = `d`.`dep_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `users`
--

/*!50001 DROP VIEW IF EXISTS `users`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `users` AS select `u`.`user_id` AS `UserID`,`u`.`user_acc` AS `UserAccount`,`ui`.`user_name` AS `Username`,`u`.`pass_hash` AS `PasswordHash`,`ad`.`auth_name` AS `UserType`,`ui`.`user_cell` AS `UserCell` from ((`user` `u` left join `user_info` `ui` on((`u`.`user_id` = `ui`.`user_id`))) join `auth_def` `ad` on((`u`.`user_auth` = `ad`.`auth_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-03 21:44:07
