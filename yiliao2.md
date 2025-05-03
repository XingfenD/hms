-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-04-29 06:40:33
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
-- 数据库： `yiliao2`
--

-- --------------------------------------------------------

--
-- 表的结构 `appointment`
--

CREATE TABLE `appointment` (
  `ap_id` int(10) UNSIGNED NOT NULL,
  `doc_id` int(10) UNSIGNED NOT NULL,
  `pat_id` int(10) UNSIGNED NOT NULL,
  `ap_sc_id` int(10) UNSIGNED NOT NULL,
  `ap_date` date NOT NULL,
  `ap_time` time NOT NULL,
  `ap_status` int(11) NOT NULL DEFAULT '0' COMMENT '0为已预约 1为正在进行 2为已结束,3为过号，4为患者已签到'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `appointment`
--

INSERT INTO `appointment` (`ap_id`, `doc_id`, `pat_id`, `ap_sc_id`, `ap_date`, `ap_time`, `ap_status`) VALUES
(1, 1, 3, 1, '2025-03-01', '13:43:23', 0),
(2, 1, 4, 1, '2025-03-03', '09:45:21', 0);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `appointments`
-- （参见下面的实际视图）
--
CREATE TABLE `appointments` (
`AppointmentID` int(10) unsigned
,`PatientID` int(10) unsigned
,`DoctorID` int(10) unsigned
,`DoctorOwnID` int(10) unsigned
,`PatientName` varchar(20)
,`DoctorName` varchar(20)
,`Title` varchar(10)
,`AppointmentDate` date
,`AppointmentTime` time
,`AppointmentStatus` int(11)
,`DepartmentID` int(10) unsigned
,`DepartmentName` varchar(20)
);

-- --------------------------------------------------------

--
-- 表的结构 `ap_info`
--

CREATE TABLE `ap_info` (
  `ap_id` int(10) UNSIGNED NOT NULL,
  `pat_requirements` text COMMENT '主诉',
  `pat_history` text COMMENT '过往病史',
  `pat_now_history` text COMMENT '现病史',
  `diagnosis` text COMMENT '初步诊断',
  `treat_method` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `auth_def`
--

CREATE TABLE `auth_def` (
  `auth_id` int(10) UNSIGNED NOT NULL,
  `auth_name` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `auth_def`
--

INSERT INTO `auth_def` (`auth_id`, `auth_name`) VALUES
(1, 'admin'),
(3, '医生'),
(4, '患者'),
(2, '药房管理员');

-- --------------------------------------------------------

--
-- 表的结构 `department`
--

CREATE TABLE `department` (
  `dep_id` int(10) UNSIGNED NOT NULL,
  `dep_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `department`
--

INSERT INTO `department` (`dep_id`, `dep_name`) VALUES
(3, '儿科'),
(1, '内科'),
(2, '外科'),
(4, '妇产科'),
(5, '眼科');

-- --------------------------------------------------------

--
-- 替换视图以便查看 `departments`
-- （参见下面的实际视图）
--
CREATE TABLE `departments` (
`DepartmentId` int(10) unsigned
,`Department` varchar(20)
);

-- --------------------------------------------------------

--
-- 表的结构 `doctor`
--

CREATE TABLE `doctor` (
  `doc_id` int(10) UNSIGNED NOT NULL COMMENT 'doctor在doctor表中单独的id与doctor的userid不一样',
  `doc_uid` int(10) UNSIGNED NOT NULL COMMENT 'doctor的userid',
  `dep_id` int(10) UNSIGNED NOT NULL,
  `title_id` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `doctor`
--

INSERT INTO `doctor` (`doc_id`, `doc_uid`, `dep_id`, `title_id`) VALUES
(1, 2, 1, 1);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `doctors`
-- （参见下面的实际视图）
--
CREATE TABLE `doctors` (
`DoctorID` int(10) unsigned
,`DoctorOwnID` int(10) unsigned
,`FullName` varchar(20)
,`DepartmentID` int(10) unsigned
,`DepartmentName` varchar(20)
,`Title` varchar(10)
);

-- --------------------------------------------------------

--
-- 表的结构 `doc_title`
--

CREATE TABLE `doc_title` (
  `title_id` int(10) UNSIGNED NOT NULL,
  `title_name` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `doc_title`
--

INSERT INTO `doc_title` (`title_id`, `title_name`) VALUES
(1, '专家医师'),
(2, '主任医师'),
(3, '副主任医师'),
(4, '普通医师');

-- --------------------------------------------------------

--
-- 替换视图以便查看 `drugs`
-- （参见下面的实际视图）
--
CREATE TABLE `drugs` (
`DrugID` int(10) unsigned
,`DrugName` varchar(45)
,`Price` decimal(10,2)
,`StockQuantity` int(11)
);

-- --------------------------------------------------------

--
-- 表的结构 `drug_def`
--

CREATE TABLE `drug_def` (
  `drug_id` int(10) UNSIGNED NOT NULL,
  `drug_name` varchar(45) NOT NULL,
  `drug_price` decimal(10,2) NOT NULL,
  `drug_store` int(11) NOT NULL DEFAULT '0',
  `drug_specification` varchar(45) DEFAULT NULL COMMENT '规格',
  `drug_manufacturer` varchar(45) DEFAULT NULL COMMENT '制造商',
  `drug_category` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '类别',
  `drug_instructions` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '说明'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `drug_def`
--

INSERT INTO `drug_def` (`drug_id`, `drug_name`, `drug_price`, `drug_store`, `drug_specification`, `drug_manufacturer`, `drug_category`, `drug_instructions`) VALUES
(1, '头孢', '9.98', 806, NULL, NULL, NULL, NULL),
(2, '布洛芬颗粒', '10.85', 900, NULL, NULL, NULL, NULL),
(3, '莲花清瘟胶囊', '10.98', 10000, NULL, NULL, NULL, NULL),
(4, '左氧氟沙星片', '20.84', 9950, NULL, NULL, NULL, NULL),
(5, '阿昔洛韦滴眼液', '15.41', 10000, NULL, NULL, NULL, NULL),
(6, '蒙脱石散', '12.00', 430, NULL, NULL, NULL, NULL),
(7, '麝香壮骨膏', '9.00', 10000, NULL, NULL, NULL, NULL),
(8, '牛黄醒消丸', '42.85', 500, NULL, NULL, NULL, NULL),
(10, '板蓝根', '2.50', 0, '一天三次', '白云山', '内服', '清热解毒');

-- --------------------------------------------------------

--
-- 表的结构 `drug_record`
--

CREATE TABLE `drug_record` (
  `drug_rcd_id` int(10) UNSIGNED NOT NULL,
  `drug_id` int(10) UNSIGNED NOT NULL,
  `oper_amount` int(11) NOT NULL,
  `status_code` int(11) NOT NULL COMMENT '0是减少；1是增加；',
  `oper_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `drug_record`
--

INSERT INTO `drug_record` (`drug_rcd_id`, `drug_id`, `oper_amount`, `status_code`, `oper_time`) VALUES
(1, 1, 808, 1, '2025-04-28 16:37:02'),
(2, 2, 900, 1, '2025-04-28 16:37:02'),
(3, 3, 10000, 1, '2025-04-28 16:37:02'),
(4, 4, 9950, 1, '2025-04-28 16:37:02'),
(5, 5, 10000, 1, '2025-04-28 16:37:02'),
(6, 6, 430, 1, '2025-04-28 16:37:02'),
(7, 7, 10000, 1, '2025-04-28 16:37:02'),
(8, 8, 400, 1, '2025-04-28 16:37:02'),
(9, 1, 2, 0, '2025-04-28 16:37:02'),
(10, 8, 100, 0, '2025-04-29 14:21:49'),
(11, 8, 200, 1, '2025-04-29 14:22:06');

--
-- 触发器 `drug_record`
--
DELIMITER $$
CREATE TRIGGER `drug_record_BEFORE_INSERT` BEFORE INSERT ON `drug_record` FOR EACH ROW BEGIN
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
    

		
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 替换视图以便查看 `examination`
-- （参见下面的实际视图）
--
CREATE TABLE `examination` (
`ExaminationRecordID` int(10) unsigned
,`AppointmentID` int(10) unsigned
,`ExaminationItem` varchar(45)
,`ExaminationPrice` decimal(10,2)
,`ExaminationResult` text
);

-- --------------------------------------------------------

--
-- 表的结构 `examination_def`
--

CREATE TABLE `examination_def` (
  `exam_def_id` int(10) UNSIGNED NOT NULL,
  `exam_name` varchar(45) NOT NULL,
  `exam_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `examination_def`
--

INSERT INTO `examination_def` (`exam_def_id`, `exam_name`, `exam_price`) VALUES
(1, 'CT', '150.00'),
(2, '核磁', '200.00'),
(3, '体格检查', '20.00');

-- --------------------------------------------------------

--
-- 替换视图以便查看 `exam_info`
-- （参见下面的实际视图）
--
CREATE TABLE `exam_info` (
`ap_id` int(10) unsigned
,`exam_def_id` int(10) unsigned
,`exam_name` varchar(45)
,`exam_price` decimal(10,2)
,`exam_id` int(10) unsigned
,`exam_result` text
,`exam_finish_time` datetime
);

-- --------------------------------------------------------

--
-- 表的结构 `exam_record`
--

CREATE TABLE `exam_record` (
  `exam_rcd_id` int(10) UNSIGNED NOT NULL,
  `exam_id` int(10) UNSIGNED NOT NULL COMMENT '由触发器自动生成',
  `ap_id` int(10) UNSIGNED NOT NULL,
  `exam_def_id` int(10) UNSIGNED NOT NULL,
  `status_code` int(11) NOT NULL COMMENT '0是未检查；1是已付款；2是已付款已检查；3是未付款已检查',
  `oper_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `exam_record`
--

INSERT INTO `exam_record` (`exam_rcd_id`, `exam_id`, `ap_id`, `exam_def_id`, `status_code`, `oper_time`) VALUES
(1, 1, 1, 1, 0, '2025-04-28 16:37:02'),
(2, 1, 1, 1, 1, '2025-04-28 16:37:02'),
(3, 1, 1, 1, 2, '2025-04-28 16:37:02'),
(4, 3, 1, 2, 0, '2025-04-28 21:07:37'),
(5, 3, 1, 2, 1, '2025-04-28 21:07:40'),
(6, 5, 1, 2, 3, '2025-04-28 21:07:42'),
(7, 5, 1, 2, 0, '2025-04-28 21:10:47');

--
-- 触发器 `exam_record`
--
DELIMITER $$
CREATE TRIGGER `exam_record_BEFORE_INSERT` BEFORE INSERT ON `exam_record` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `exam_result`
--

CREATE TABLE `exam_result` (
  `exam_rcd_id` int(10) UNSIGNED NOT NULL,
  `exam_id` int(10) UNSIGNED NOT NULL,
  `exam_result` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `exam_result`
--

INSERT INTO `exam_result` (`exam_rcd_id`, `exam_id`, `exam_result`) VALUES
(3, 1, '非常ok');

--
-- 触发器 `exam_result`
--
DELIMITER $$
CREATE TRIGGER `exam_result_BEFORE_INSERT` BEFORE INSERT ON `exam_result` FOR EACH ROW BEGIN
	DECLARE new_rcd_id INT;
	SELECT exam_rcd_id INTO new_rcd_id FROM exam_record
    WHERE exam_id = NEW.exam_id AND status_code = 2;
    SET NEW.exam_rcd_id = new_rcd_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 替换视图以便查看 `patients`
-- （参见下面的实际视图）
--
CREATE TABLE `patients` (
`PatientID` int(10) unsigned
,`FullName` varchar(20)
,`Gender` int(11)
,`Age` int(11)
);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `payment`
-- （参见下面的实际视图）
--
CREATE TABLE `payment` (
`AppointmentID` int(10) unsigned
,`Payed` decimal(43,2)
,`Unpayed` decimal(45,2)
,`Total` decimal(43,2)
);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `prescriptions`
-- （参见下面的实际视图）
--
CREATE TABLE `prescriptions` (
`pres_id` int(10) unsigned
,`drug_id` int(10) unsigned
,`payed_amount` decimal(32,0)
,`recipe_amount` decimal(32,0)
,`total_amount` decimal(32,0)
);

-- --------------------------------------------------------

--
-- 表的结构 `prescription_record`
--

CREATE TABLE `prescription_record` (
  `pres_rcd_id` int(10) UNSIGNED NOT NULL,
  `pres_id` int(10) UNSIGNED NOT NULL COMMENT '通过触发器自动生成',
  `ap_id` int(10) UNSIGNED NOT NULL,
  `drug_id` int(10) UNSIGNED NOT NULL,
  `status_code` int(11) NOT NULL COMMENT '0是开药；1是已付款；2是已付款已发药；3是已发药未付款',
  `oper_amount` int(11) NOT NULL,
  `oper_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `prescription_record`
--

INSERT INTO `prescription_record` (`pres_rcd_id`, `pres_id`, `ap_id`, `drug_id`, `status_code`, `oper_amount`, `oper_time`) VALUES
(1, 1, 1, 1, 0, 2, '2025-04-28 16:37:02'),
(2, 1, 1, 1, 1, 2, '2025-04-28 16:37:02'),
(3, 1, 1, 1, 2, 2, '2025-04-28 16:37:02');

--
-- 触发器 `prescription_record`
--
DELIMITER $$
CREATE TRIGGER `prescription_record_BEFORE_INSERT` BEFORE INSERT ON `prescription_record` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 替换视图以便查看 `prescription_table`
-- （参见下面的实际视图）
--
CREATE TABLE `prescription_table` (
`PrescriptionID` int(10) unsigned
,`AppointmentID` int(10) unsigned
,`DrugName` varchar(45)
,`DrugQuantity` int(11)
,`DrugPrice` decimal(10,2)
,`DrugSumPrice` decimal(20,2)
);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `pres_info`
-- （参见下面的实际视图）
--
CREATE TABLE `pres_info` (
`ap_id` int(10) unsigned
,`drug_name` varchar(45)
,`drug_price` decimal(10,2)
,`drug_specification` varchar(45)
,`drug_manufacturer` varchar(45)
,`oper_amount` int(11)
,`use_method` varchar(45)
,`doc_comment` varchar(45)
);

-- --------------------------------------------------------

--
-- 表的结构 `pres_result`
--

CREATE TABLE `pres_result` (
  `pres_rcd_id` int(10) UNSIGNED NOT NULL,
  `pres_id` int(10) UNSIGNED NOT NULL,
  `use_method` varchar(45) DEFAULT NULL COMMENT '用法用量',
  `doc_comment` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 触发器 `pres_result`
--
DELIMITER $$
CREATE TRIGGER `pres_result_BEFORE_INSERT` BEFORE INSERT ON `pres_result` FOR EACH ROW BEGIN
	DECLARE new_rcd_id INT;
	SELECT pres_rcd_id INTO new_rcd_id FROM prescription_record
    WHERE pres_id = NEW.pres_id AND status_code = 2;
    SET NEW.pres_rcd_id = new_rcd_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `schedule`
--

CREATE TABLE `schedule` (
  `schedule_id` int(10) UNSIGNED NOT NULL,
  `doc_id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `schedule`
--

INSERT INTO `schedule` (`schedule_id`, `doc_id`, `date`, `start_time`, `end_time`) VALUES
(1, 1, '2025-04-18', '14:00:00', '20:00:00'),
(4, 1, '2025-05-01', '09:00:00', '12:00:00'),
(5, 1, '2025-05-01', '13:00:00', '17:00:00');

-- --------------------------------------------------------

--
-- 替换视图以便查看 `schedules`
-- （参见下面的实际视图）
--
CREATE TABLE `schedules` (
`ScheduleID` int(10) unsigned
,`DoctorOwnID` int(10) unsigned
,`DoctorID` int(10) unsigned
,`DoctorName` varchar(20)
,`ScheduleDate` date
,`StartTime` time
,`EndTime` time
,`DepartmentID` int(10) unsigned
,`DepartmentName` varchar(20)
);

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `user_acc` varchar(20) NOT NULL,
  `pass_hash` varchar(64) NOT NULL,
  `user_auth` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`user_id`, `user_acc`, `pass_hash`, `user_auth`) VALUES
(1, 'admin', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', 1),
(2, 'doctor_test1', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', 3),
(3, 'patient_test1', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', 4),
(4, 'patient_test2', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', 4);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `users`
-- （参见下面的实际视图）
--
CREATE TABLE `users` (
`UserID` int(10) unsigned
,`UserAccount` varchar(20)
,`Username` varchar(20)
,`PasswordHash` varchar(64)
,`UserType` varchar(10)
,`UserCell` varchar(11)
);

-- --------------------------------------------------------

--
-- 表的结构 `user_info`
--

CREATE TABLE `user_info` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `user_name` varchar(20) NOT NULL DEFAULT 'UNSET',
  `user_cell` varchar(11) NOT NULL,
  `user_gender` int(11) DEFAULT NULL,
  `user_age` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `user_info`
--

INSERT INTO `user_info` (`user_id`, `user_name`, `user_cell`, `user_gender`, `user_age`) VALUES
(1, '管理员', '00000000000', 1, 20),
(2, '医生1', '13011112222', 1, 60),
(3, '病人1', '12011112222', 0, 45),
(4, '病人2', '12011112223', 1, 58);

-- --------------------------------------------------------

--
-- 视图结构 `appointments`
--
DROP TABLE IF EXISTS `appointments`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `appointments`  AS  with `apdocinfo` as (select `ap`.`ap_id` AS `ap_id`,`ap`.`doc_id` AS `doc_id`,`dt`.`title_name` AS `doc_title`,`ui`.`user_name` AS `doc_name`,`d`.`dep_id` AS `dep_id`,`dep`.`dep_name` AS `dep_name` from ((((`appointment` `ap` left join `doctor` `d` on((`ap`.`doc_id` = `d`.`doc_id`))) left join `user_info` `ui` on((`d`.`doc_uid` = `ui`.`user_id`))) left join `doc_title` `dt` on((`dt`.`title_id` = `d`.`title_id`))) left join `department` `dep` on((`dep`.`dep_id` = `d`.`dep_id`)))), `appatinfo` as (select `ap`.`ap_id` AS `ap_id`,`ap`.`pat_id` AS `pat_id`,`ui`.`user_name` AS `pat_name` from (`appointment` `ap` left join `user_info` `ui` on((`ui`.`user_id` = `ap`.`pat_id`)))) select `ap`.`ap_id` AS `AppointmentID`,`ap`.`pat_id` AS `PatientID`,`apdocinfo`.`doc_id` AS `DoctorID`,`ap`.`doc_id` AS `DoctorOwnID`,`appatinfo`.`pat_name` AS `PatientName`,`apdocinfo`.`doc_name` AS `DoctorName`,`apdocinfo`.`doc_title` AS `Title`,`ap`.`ap_date` AS `AppointmentDate`,`ap`.`ap_time` AS `AppointmentTime`,`ap`.`ap_status` AS `AppointmentStatus`,`apdocinfo`.`dep_id` AS `DepartmentID`,`apdocinfo`.`dep_name` AS `DepartmentName` from ((`appointment` `ap` left join `apdocinfo` on((`apdocinfo`.`ap_id` = `ap`.`ap_id`))) left join `appatinfo` on((`appatinfo`.`ap_id` = `ap`.`ap_id`))) ;

-- --------------------------------------------------------

--
-- 视图结构 `departments`
--
DROP TABLE IF EXISTS `departments`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `departments`  AS  select `department`.`dep_id` AS `DepartmentId`,`department`.`dep_name` AS `Department` from `department` ;

-- --------------------------------------------------------

--
-- 视图结构 `doctors`
--
DROP TABLE IF EXISTS `doctors`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `doctors`  AS  select `doc`.`doc_uid` AS `DoctorID`,`doc`.`doc_id` AS `DoctorOwnID`,`ui`.`user_name` AS `FullName`,`dep`.`dep_id` AS `DepartmentID`,`dep`.`dep_name` AS `DepartmentName`,`t`.`title_name` AS `Title` from (((`doctor` `doc` left join `user_info` `ui` on((`doc`.`doc_uid` = `ui`.`user_id`))) left join `department` `dep` on((`doc`.`dep_id` = `dep`.`dep_id`))) left join `doc_title` `t` on((`doc`.`title_id` = `t`.`title_id`))) ;

-- --------------------------------------------------------

--
-- 视图结构 `drugs`
--
DROP TABLE IF EXISTS `drugs`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `drugs`  AS  select `drug_def`.`drug_id` AS `DrugID`,`drug_def`.`drug_name` AS `DrugName`,`drug_def`.`drug_price` AS `Price`,`drug_def`.`drug_store` AS `StockQuantity` from `drug_def` ;

-- --------------------------------------------------------

--
-- 视图结构 `examination`
--
DROP TABLE IF EXISTS `examination`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `examination`  AS  select `e`.`exam_id` AS `ExaminationRecordID`,`e`.`ap_id` AS `AppointmentID`,`e_def`.`exam_name` AS `ExaminationItem`,`e_def`.`exam_price` AS `ExaminationPrice`,`e_res`.`exam_result` AS `ExaminationResult` from ((`exam_record` `e` left join `examination_def` `e_def` on((`e`.`exam_def_id` = `e_def`.`exam_def_id`))) left join `exam_result` `e_res` on((`e`.`exam_id` = `e_res`.`exam_id`))) where (`e`.`status_code` = 0) ;

-- --------------------------------------------------------

--
-- 视图结构 `exam_info`
--
DROP TABLE IF EXISTS `exam_info`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `exam_info`  AS  select `ercd`.`ap_id` AS `ap_id`,`edef`.`exam_def_id` AS `exam_def_id`,`edef`.`exam_name` AS `exam_name`,`edef`.`exam_price` AS `exam_price`,`ercd`.`exam_id` AS `exam_id`,`eres`.`exam_result` AS `exam_result`,`ercd`.`oper_time` AS `exam_finish_time` from ((`exam_record` `ercd` left join `exam_result` `eres` on((`eres`.`exam_rcd_id` = `ercd`.`exam_rcd_id`))) left join `examination_def` `edef` on((`edef`.`exam_def_id` = `ercd`.`exam_def_id`))) where (`ercd`.`status_code` = 2) ;

-- --------------------------------------------------------

--
-- 视图结构 `patients`
--
DROP TABLE IF EXISTS `patients`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `patients`  AS  select `u`.`user_id` AS `PatientID`,`ui`.`user_name` AS `FullName`,`ui`.`user_gender` AS `Gender`,`ui`.`user_age` AS `Age` from ((`user` `u` left join `user_info` `ui` on((`u`.`user_id` = `ui`.`user_id`))) join `auth_def` `ad` on((`u`.`user_auth` = `ad`.`auth_id`))) where (`ad`.`auth_name` = '患者') ;

-- --------------------------------------------------------

--
-- 视图结构 `payment`
--
DROP TABLE IF EXISTS `payment`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `payment`  AS  with `drugtotalsumunpayed` as (select `get_all_drug_sum_for_ap`.`ap_id` AS `ap_id`,sum(`get_all_drug_sum_for_ap`.`single_drug_sum_unpayed`) AS `drug_sum_unpayed` from (select `p`.`ap_id` AS `ap_id`,(`d`.`drug_price` * `p`.`oper_amount`) AS `single_drug_sum_unpayed` from (`prescription_record` `p` join `drug_def` `d` on((`d`.`drug_id` = `p`.`drug_id`))) where (`p`.`status_code` = 0)) `get_all_drug_sum_for_ap` group by `get_all_drug_sum_for_ap`.`ap_id`), `examtotalsumunpayed` as (select `examsumprice`.`ap_id` AS `ap_id`,sum(`examsumprice`.`ExaminationPrice`) AS `exam_sum_unpayed` from (select `e`.`ap_id` AS `ap_id`,`e_def`.`exam_price` AS `ExaminationPrice` from (`exam_record` `e` left join `examination_def` `e_def` on((`e`.`exam_def_id` = `e_def`.`exam_def_id`))) where (`e`.`status_code` = 0)) `examsumprice` group by `examsumprice`.`ap_id`), `drugtotalsumpayed` as (select `get_all_drug_sum_for_ap`.`ap_id` AS `ap_id`,sum(`get_all_drug_sum_for_ap`.`single_drug_sum_unpayed`) AS `drug_sum_payed` from (select `p`.`ap_id` AS `ap_id`,(`d`.`drug_price` * `p`.`oper_amount`) AS `single_drug_sum_unpayed` from (`prescription_record` `p` join `drug_def` `d` on((`d`.`drug_id` = `p`.`drug_id`))) where (`p`.`status_code` = 1)) `get_all_drug_sum_for_ap` group by `get_all_drug_sum_for_ap`.`ap_id`), `examtotalsumpayed` as (select `examsumprice`.`ap_id` AS `ap_id`,sum(`examsumprice`.`ExaminationPrice`) AS `exam_sum_payed` from (select `e`.`ap_id` AS `ap_id`,`e_def`.`exam_price` AS `ExaminationPrice` from (`exam_record` `e` left join `examination_def` `e_def` on((`e`.`exam_def_id` = `e_def`.`exam_def_id`))) where (`e`.`status_code` = 1)) `examsumprice` group by `examsumprice`.`ap_id`) select `ap`.`ap_id` AS `AppointmentID`,(`drugtotalsumpayed`.`drug_sum_payed` + `examtotalsumpayed`.`exam_sum_payed`) AS `Payed`,(((`drugtotalsumunpayed`.`drug_sum_unpayed` + `examtotalsumunpayed`.`exam_sum_unpayed`) - `drugtotalsumpayed`.`drug_sum_payed`) - `examtotalsumpayed`.`exam_sum_payed`) AS `Unpayed`,(`drugtotalsumunpayed`.`drug_sum_unpayed` + `examtotalsumunpayed`.`exam_sum_unpayed`) AS `Total` from ((((`appointment` `ap` join `examtotalsumunpayed` on((`examtotalsumunpayed`.`ap_id` = `ap`.`ap_id`))) join `drugtotalsumunpayed` on((`drugtotalsumunpayed`.`ap_id` = `ap`.`ap_id`))) join `drugtotalsumpayed` on((`drugtotalsumpayed`.`ap_id` = `ap`.`ap_id`))) join `examtotalsumpayed` on((`examtotalsumpayed`.`ap_id` = `ap`.`ap_id`))) ;

-- --------------------------------------------------------

--
-- 视图结构 `prescriptions`
--
DROP TABLE IF EXISTS `prescriptions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `prescriptions`  AS  select `prescription_record`.`pres_id` AS `pres_id`,`prescription_record`.`drug_id` AS `drug_id`,sum((case when (`prescription_record`.`status_code` = 1) then `prescription_record`.`oper_amount` else 0 end)) AS `payed_amount`,sum((case when (`prescription_record`.`status_code` = 2) then `prescription_record`.`oper_amount` else 0 end)) AS `recipe_amount`,sum((case when (`prescription_record`.`status_code` = 0) then `prescription_record`.`oper_amount` else 0 end)) AS `total_amount` from `prescription_record` group by `prescription_record`.`pres_id`,`prescription_record`.`drug_id` ;

-- --------------------------------------------------------

--
-- 视图结构 `prescription_table`
--
DROP TABLE IF EXISTS `prescription_table`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `prescription_table`  AS  select `p`.`pres_id` AS `PrescriptionID`,`p`.`ap_id` AS `AppointmentID`,`d`.`drug_name` AS `DrugName`,`p`.`oper_amount` AS `DrugQuantity`,`d`.`drug_price` AS `DrugPrice`,(`d`.`drug_price` * `p`.`oper_amount`) AS `DrugSumPrice` from (`prescription_record` `p` join `drug_def` `d` on((`d`.`drug_id` = `p`.`drug_id`))) where (`p`.`status_code` = 0) ;

-- --------------------------------------------------------

--
-- 视图结构 `pres_info`
--
DROP TABLE IF EXISTS `pres_info`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `pres_info`  AS  with `drug_amount` as (select `prescription_record`.`ap_id` AS `ap_id`,`prescription_record`.`drug_id` AS `drug_id`,`prescription_record`.`pres_id` AS `pres_id`,`prescription_record`.`oper_amount` AS `oper_amount` from `prescription_record` where (`prescription_record`.`status_code` = 0)) select `rcd`.`ap_id` AS `ap_id`,`drug_def`.`drug_name` AS `drug_name`,`drug_def`.`drug_price` AS `drug_price`,`drug_def`.`drug_specification` AS `drug_specification`,`drug_def`.`drug_manufacturer` AS `drug_manufacturer`,`drug_amount`.`oper_amount` AS `oper_amount`,`res`.`use_method` AS `use_method`,`res`.`doc_comment` AS `doc_comment` from (((`prescription_record` `rcd` left join `drug_def` on((`drug_def`.`drug_id` = `rcd`.`drug_id`))) left join `drug_amount` on((`drug_amount`.`pres_id` = `rcd`.`pres_id`))) left join `pres_result` `res` on((`res`.`pres_rcd_id` = `rcd`.`pres_rcd_id`))) where (`rcd`.`status_code` = 2) ;

-- --------------------------------------------------------

--
-- 视图结构 `schedules`
--
DROP TABLE IF EXISTS `schedules`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `schedules`  AS  select `sc`.`schedule_id` AS `ScheduleID`,`sc`.`doc_id` AS `DoctorOwnID`,`d`.`doc_uid` AS `DoctorID`,`ui`.`user_name` AS `DoctorName`,`sc`.`date` AS `ScheduleDate`,`sc`.`start_time` AS `StartTime`,`sc`.`end_time` AS `EndTime`,`d`.`dep_id` AS `DepartmentID`,`dep`.`dep_name` AS `DepartmentName` from (((`schedule` `sc` left join `doctor` `d` on((`d`.`doc_id` = `sc`.`doc_id`))) left join `user_info` `ui` on((`d`.`doc_uid` = `ui`.`user_id`))) left join `department` `dep` on((`dep`.`dep_id` = `d`.`dep_id`))) ;

-- --------------------------------------------------------

--
-- 视图结构 `users`
--
DROP TABLE IF EXISTS `users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `users`  AS  select `u`.`user_id` AS `UserID`,`u`.`user_acc` AS `UserAccount`,`ui`.`user_name` AS `Username`,`u`.`pass_hash` AS `PasswordHash`,`ad`.`auth_name` AS `UserType`,`ui`.`user_cell` AS `UserCell` from ((`user` `u` left join `user_info` `ui` on((`u`.`user_id` = `ui`.`user_id`))) join `auth_def` `ad` on((`u`.`user_auth` = `ad`.`auth_id`))) ;

--
-- 转储表的索引
--

--
-- 表的索引 `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`ap_id`),
  ADD UNIQUE KEY `ap_id_UNIQUE` (`ap_id`),
  ADD KEY `fk_ap.patid_ref_ui_idx` (`pat_id`),
  ADD KEY `fk_ap.docid_ref_doc_idx` (`doc_id`),
  ADD KEY `fk_ap.apscid_ref_sc_idx` (`ap_sc_id`);

--
-- 表的索引 `ap_info`
--
ALTER TABLE `ap_info`
  ADD PRIMARY KEY (`ap_id`),
  ADD UNIQUE KEY `ap_id_UNIQUE` (`ap_id`);

--
-- 表的索引 `auth_def`
--
ALTER TABLE `auth_def`
  ADD PRIMARY KEY (`auth_id`),
  ADD UNIQUE KEY `auth_id_UNIQUE` (`auth_id`),
  ADD UNIQUE KEY `auth_name_UNIQUE` (`auth_name`);

--
-- 表的索引 `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`dep_id`),
  ADD UNIQUE KEY `dep_id_UNIQUE` (`dep_id`),
  ADD UNIQUE KEY `dep_name_UNIQUE` (`dep_name`);

--
-- 表的索引 `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`doc_id`),
  ADD UNIQUE KEY `doc_id_UNIQUE` (`doc_id`),
  ADD UNIQUE KEY `doc_uid_UNIQUE` (`doc_uid`),
  ADD KEY `fk_doc.depid_ref_dep_idx` (`dep_id`),
  ADD KEY `fk_doc.titleid_ref_doctitle_idx` (`title_id`);

--
-- 表的索引 `doc_title`
--
ALTER TABLE `doc_title`
  ADD PRIMARY KEY (`title_id`),
  ADD UNIQUE KEY `title_name_UNIQUE` (`title_name`),
  ADD UNIQUE KEY `titile_id_UNIQUE` (`title_id`);

--
-- 表的索引 `drug_def`
--
ALTER TABLE `drug_def`
  ADD PRIMARY KEY (`drug_id`),
  ADD UNIQUE KEY `drug_id_UNIQUE` (`drug_id`);

--
-- 表的索引 `drug_record`
--
ALTER TABLE `drug_record`
  ADD PRIMARY KEY (`drug_rcd_id`),
  ADD UNIQUE KEY `drug_rcd_id_UNIQUE` (`drug_rcd_id`),
  ADD KEY `fk_drugrcd.drugid_ref_def_idx` (`drug_id`);

--
-- 表的索引 `examination_def`
--
ALTER TABLE `examination_def`
  ADD PRIMARY KEY (`exam_def_id`),
  ADD UNIQUE KEY `exam_def_id_UNIQUE` (`exam_def_id`),
  ADD UNIQUE KEY `exam_name_UNIQUE` (`exam_name`);

--
-- 表的索引 `exam_record`
--
ALTER TABLE `exam_record`
  ADD PRIMARY KEY (`exam_rcd_id`),
  ADD UNIQUE KEY `exam_rcd_id_UNIQUE` (`exam_rcd_id`),
  ADD KEY `fk_examrcd.examdefid_ref_def_idx` (`exam_def_id`),
  ADD KEY `fk_examrcd.apid_ref_ap_idx` (`ap_id`);

--
-- 表的索引 `exam_result`
--
ALTER TABLE `exam_result`
  ADD PRIMARY KEY (`exam_rcd_id`,`exam_id`),
  ADD UNIQUE KEY `exam_id_UNIQUE` (`exam_id`),
  ADD UNIQUE KEY `exam_rcd_id_UNIQUE` (`exam_rcd_id`);

--
-- 表的索引 `prescription_record`
--
ALTER TABLE `prescription_record`
  ADD PRIMARY KEY (`pres_rcd_id`),
  ADD UNIQUE KEY `pres_rcd_id_UNIQUE` (`pres_rcd_id`),
  ADD KEY `fk_resrcd.drugid_ref_drug_idx` (`drug_id`),
  ADD KEY `fk_presrcd.apid_ref_ap_idx` (`ap_id`);

--
-- 表的索引 `pres_result`
--
ALTER TABLE `pres_result`
  ADD PRIMARY KEY (`pres_rcd_id`,`pres_id`),
  ADD UNIQUE KEY `pres_rcd_id_UNIQUE` (`pres_rcd_id`),
  ADD UNIQUE KEY `pres_id_UNIQUE` (`pres_id`);

--
-- 表的索引 `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD UNIQUE KEY `schedule_id_UNIQUE` (`schedule_id`),
  ADD KEY `fk_docid_ref_doctor_idx` (`doc_id`);

--
-- 表的索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_id_UNIQUE` (`user_id`),
  ADD UNIQUE KEY `user_acc_UNIQUE` (`user_acc`),
  ADD KEY `fk_user.auth_ref_auth_idx` (`user_auth`);

--
-- 表的索引 `user_info`
--
ALTER TABLE `user_info`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_id_UNIQUE` (`user_id`),
  ADD UNIQUE KEY `user_cell_UNIQUE` (`user_cell`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `appointment`
--
ALTER TABLE `appointment`
  MODIFY `ap_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `auth_def`
--
ALTER TABLE `auth_def`
  MODIFY `auth_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `department`
--
ALTER TABLE `department`
  MODIFY `dep_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `doctor`
--
ALTER TABLE `doctor`
  MODIFY `doc_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'doctor在doctor表中单独的id与doctor的userid不一样', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `doc_title`
--
ALTER TABLE `doc_title`
  MODIFY `title_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `drug_def`
--
ALTER TABLE `drug_def`
  MODIFY `drug_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `drug_record`
--
ALTER TABLE `drug_record`
  MODIFY `drug_rcd_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `examination_def`
--
ALTER TABLE `examination_def`
  MODIFY `exam_def_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `exam_record`
--
ALTER TABLE `exam_record`
  MODIFY `exam_rcd_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `prescription_record`
--
ALTER TABLE `prescription_record`
  MODIFY `pres_rcd_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `schedule`
--
ALTER TABLE `schedule`
  MODIFY `schedule_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 限制导出的表
--

--
-- 限制表 `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `fk_ap.apscid_ref_sc` FOREIGN KEY (`ap_sc_id`) REFERENCES `schedule` (`schedule_id`),
  ADD CONSTRAINT `fk_ap.docid_ref_doc` FOREIGN KEY (`doc_id`) REFERENCES `doctor` (`doc_id`),
  ADD CONSTRAINT `fk_ap.patid_ref_ui` FOREIGN KEY (`pat_id`) REFERENCES `user_info` (`user_id`);

--
-- 限制表 `ap_info`
--
ALTER TABLE `ap_info`
  ADD CONSTRAINT `fk_apinfo.apid_ref_ap` FOREIGN KEY (`ap_id`) REFERENCES `appointment` (`ap_id`) ON DELETE CASCADE;

--
-- 限制表 `doctor`
--
ALTER TABLE `doctor`
  ADD CONSTRAINT `fk_doc.depid_ref_dep` FOREIGN KEY (`dep_id`) REFERENCES `department` (`dep_id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_doc.docid_ref_user` FOREIGN KEY (`doc_uid`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_doc.titleid_ref_doctitle` FOREIGN KEY (`title_id`) REFERENCES `doc_title` (`title_id`) ON DELETE RESTRICT;

--
-- 限制表 `drug_record`
--
ALTER TABLE `drug_record`
  ADD CONSTRAINT `fk_drugrcd.drugid_ref_def` FOREIGN KEY (`drug_id`) REFERENCES `drug_def` (`drug_id`) ON DELETE RESTRICT;

--
-- 限制表 `exam_record`
--
ALTER TABLE `exam_record`
  ADD CONSTRAINT `fk_examrcd.apid_ref_ap` FOREIGN KEY (`ap_id`) REFERENCES `appointment` (`ap_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_examrcd.examdefid_ref_def` FOREIGN KEY (`exam_def_id`) REFERENCES `examination_def` (`exam_def_id`) ON DELETE RESTRICT;

--
-- 限制表 `exam_result`
--
ALTER TABLE `exam_result`
  ADD CONSTRAINT `fk_examres.examid_ref_exam` FOREIGN KEY (`exam_rcd_id`) REFERENCES `exam_record` (`exam_rcd_id`);

--
-- 限制表 `prescription_record`
--
ALTER TABLE `prescription_record`
  ADD CONSTRAINT `fk_presrcd.apid_ref_ap` FOREIGN KEY (`ap_id`) REFERENCES `appointment` (`ap_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_presrcd.drugid_ref_drug` FOREIGN KEY (`drug_id`) REFERENCES `drug_def` (`drug_id`) ON DELETE RESTRICT;

--
-- 限制表 `pres_result`
--
ALTER TABLE `pres_result`
  ADD CONSTRAINT `fk_presres.rcd_id_ref_presrcd` FOREIGN KEY (`pres_rcd_id`) REFERENCES `prescription_record` (`pres_rcd_id`);

--
-- 限制表 `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `fk_docid_ref_doctor` FOREIGN KEY (`doc_id`) REFERENCES `doctor` (`doc_id`);

--
-- 限制表 `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_user.auth_ref_auth` FOREIGN KEY (`user_auth`) REFERENCES `auth_def` (`auth_id`) ON DELETE RESTRICT;

--
-- 限制表 `user_info`
--
ALTER TABLE `user_info`
  ADD CONSTRAINT `fk_user_info_ref_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
