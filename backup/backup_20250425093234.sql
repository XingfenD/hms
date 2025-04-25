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
  `ap_status` int(11) NOT NULL DEFAULT '0' COMMENT '0为已预约 1为正在进行 2为已结束',
  `ap_result` text,
  PRIMARY KEY (`ap_id`),
  UNIQUE KEY `ap_id_UNIQUE` (`ap_id`),
  KEY `fk_ap.patid_ref_ui_idx` (`pat_id`),
  KEY `fk_ap.docid_ref_doc_idx` (`doc_id`),
  KEY `fk_ap.apscid_ref_sc_idx` (`ap_sc_id`),
  CONSTRAINT `fk_ap.apscid_ref_sc` FOREIGN KEY (`ap_sc_id`) REFERENCES `schedule` (`schedule_id`),
  CONSTRAINT `fk_ap.docid_ref_doc` FOREIGN KEY (`doc_id`) REFERENCES `doctor` (`doc_id`),
  CONSTRAINT `fk_ap.patid_ref_ui` FOREIGN KEY (`pat_id`) REFERENCES `user_info` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment`
--

LOCK TABLES `appointment` WRITE;
/*!40000 ALTER TABLE `appointment` DISABLE KEYS */;
INSERT INTO `appointment` VALUES (1,1,3,1,'2025-03-01','13:43:23',0,''),(2,1,4,1,'2025-03-03','09:45:21',0,'');
/*!40000 ALTER TABLE `appointment` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department`
--

LOCK TABLES `department` WRITE;
/*!40000 ALTER TABLE `department` DISABLE KEYS */;
INSERT INTO `department` VALUES (3,'儿科'),(1,'内科'),(2,'外科'),(4,'妇产科'),(5,'眼科');
/*!40000 ALTER TABLE `department` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctor`
--

LOCK TABLES `doctor` WRITE;
/*!40000 ALTER TABLE `doctor` DISABLE KEYS */;
INSERT INTO `doctor` VALUES (1,2,1,1);
/*!40000 ALTER TABLE `doctor` ENABLE KEYS */;
UNLOCK TABLES;

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
  PRIMARY KEY (`drug_id`),
  UNIQUE KEY `drug_id_UNIQUE` (`drug_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drug_def`
--

LOCK TABLES `drug_def` WRITE;
/*!40000 ALTER TABLE `drug_def` DISABLE KEYS */;
INSERT INTO `drug_def` VALUES (1,'头孢',9.98,806),(2,'布洛芬颗粒',10.85,900),(3,'莲花清瘟胶囊',10.98,10000),(4,'左氧氟沙星片',20.84,9950),(5,'阿昔洛韦滴眼液',15.41,10000),(6,'蒙脱石散',12.00,430),(7,'麝香壮骨膏',9.00,10000),(8,'牛黄醒消丸',42.85,400);
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drug_record`
--

LOCK TABLES `drug_record` WRITE;
/*!40000 ALTER TABLE `drug_record` DISABLE KEYS */;
INSERT INTO `drug_record` VALUES (1,1,808,1,'2025-04-25 09:11:55'),(2,2,900,1,'2025-04-25 09:11:55'),(3,3,10000,1,'2025-04-25 09:11:55'),(4,4,9950,1,'2025-04-25 09:11:55'),(5,5,10000,1,'2025-04-25 09:11:55'),(6,6,430,1,'2025-04-25 09:11:55'),(7,7,10000,1,'2025-04-25 09:11:55'),(8,8,400,1,'2025-04-25 09:11:55'),(9,1,2,0,'2025-04-25 09:11:55');
/*!40000 ALTER TABLE `drug_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_record`
--

DROP TABLE IF EXISTS `exam_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_record`
--

LOCK TABLES `exam_record` WRITE;
/*!40000 ALTER TABLE `exam_record` DISABLE KEYS */;
INSERT INTO `exam_record` VALUES (1,1,1,1,0,'2025-04-25 09:11:55'),(2,1,1,1,1,'2025-04-25 09:11:55'),(3,1,1,1,2,'2025-04-25 09:11:55');
/*!40000 ALTER TABLE `exam_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_result`
--

DROP TABLE IF EXISTS `exam_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_result` (
  `exam_id` int(10) unsigned NOT NULL,
  `exam_result` text NOT NULL,
  PRIMARY KEY (`exam_id`),
  UNIQUE KEY `exam_id_UNIQUE` (`exam_id`),
  CONSTRAINT `fk_examres.examid_ref_exam` FOREIGN KEY (`exam_id`) REFERENCES `exam_record` (`exam_rcd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_result`
--

LOCK TABLES `exam_result` WRITE;
/*!40000 ALTER TABLE `exam_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `exam_result` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `examination_def`
--

LOCK TABLES `examination_def` WRITE;
/*!40000 ALTER TABLE `examination_def` DISABLE KEYS */;
INSERT INTO `examination_def` VALUES (1,'CT',150.00),(2,'核磁',200.00);
/*!40000 ALTER TABLE `examination_def` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescription_record`
--

DROP TABLE IF EXISTS `prescription_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescription_record`
--

LOCK TABLES `prescription_record` WRITE;
/*!40000 ALTER TABLE `prescription_record` DISABLE KEYS */;
INSERT INTO `prescription_record` VALUES (1,1,1,1,0,2,'2025-04-25 09:11:55'),(2,1,1,1,1,2,'2025-04-25 09:11:55'),(3,1,1,1,2,2,'2025-04-25 09:11:55');
/*!40000 ALTER TABLE `prescription_record` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule`
--

LOCK TABLES `schedule` WRITE;
/*!40000 ALTER TABLE `schedule` DISABLE KEYS */;
INSERT INTO `schedule` VALUES (1,1,'2025-04-18','14:00:00','20:00:00');
/*!40000 ALTER TABLE `schedule` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'admin','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',1),(2,'doctor_test1','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',3),(3,'patient_test1','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4),(4,'patient_test2','$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK',4);
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
INSERT INTO `user_info` VALUES (1,'管理员','00000000000',1,20),(2,'医生1','13011112222',1,60),(3,'病人1','12011112222',0,45),(4,'病人2','12011112223',1,58);
/*!40000 ALTER TABLE `user_info` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-25  9:32:34
