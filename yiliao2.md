-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-04-18 14:11:10
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
  `ap_date` date NOT NULL,
  `ap_time` time NOT NULL,
  `ap_status` int(11) NOT NULL DEFAULT '0' COMMENT '0为已预约 1为正在进行 2为已结束',
  `ap_result` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `appointment`
--

INSERT INTO `appointment` (`ap_id`, `doc_id`, `pat_id`, `ap_date`, `ap_time`, `ap_status`, `ap_result`) VALUES
(1, 1, 3, '2025-03-01', '13:43:23', 0, NULL),
(2, 1, 4, '2025-03-03', '09:45:21', 0, NULL);

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
`Department` varchar(20)
,`DepartmentId` int(10) unsigned
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
`DepartmentID` int(10) unsigned
,`DepartmentName` varchar(20)
,`DoctorID` int(10) unsigned
,`FullName` varchar(20)
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
  `drug_store` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `drug_def`
--

INSERT INTO `drug_def` (`drug_id`, `drug_name`, `drug_price`, `drug_store`) VALUES
(1, '头孢', '9.98', 808),
(2, '布洛芬颗粒', '10.85', 900),
(3, '莲花清瘟胶囊', '10.98', 10000),
(4, '左氧氟沙星片', '20.84', 9950),
(5, '阿昔洛韦滴眼液', '15.41', 10000),
(6, '蒙脱石散', '12.00', 430),
(7, '麝香壮骨膏', '9.00', 10000),
(8, '牛黄醒消丸', '42.85', 400);

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
(1, 1, 2, 0, '2025-04-18 17:58:29');

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
`AppointmentID` int(10) unsigned
,`ExaminationItem` varchar(45)
,`ExaminationPrice` decimal(10,2)
,`ExaminationRecordID` int(10) unsigned
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
(2, '核磁', '200.00');

-- --------------------------------------------------------

--
-- 表的结构 `exam_record`
--

CREATE TABLE `exam_record` (
  `exam_rcd_id` int(10) UNSIGNED NOT NULL,
  `exam_id` int(10) UNSIGNED NOT NULL,
  `ap_id` int(10) UNSIGNED NOT NULL COMMENT '应该通过触发器自动生成',
  `exam_def_id` int(10) UNSIGNED NOT NULL,
  `status_code` int(11) NOT NULL COMMENT '0是未检查；1是已付款；2是已付款已检查；3是未付款已检查',
  `oper_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `exam_record`
--

INSERT INTO `exam_record` (`exam_rcd_id`, `exam_id`, `ap_id`, `exam_def_id`, `status_code`, `oper_time`) VALUES
(1, 1, 1, 1, 0, '2025-04-18 17:58:29'),
(2, 1, 1, 1, 1, '2025-04-18 17:58:29'),
(3, 1, 1, 1, 2, '2025-04-18 17:58:29');

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
  `exam_id` int(10) UNSIGNED NOT NULL,
  `exam_result` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 替换视图以便查看 `patients`
-- （参见下面的实际视图）
--
CREATE TABLE `patients` (
`Age` int(11)
,`FullName` varchar(20)
,`Gender` int(11)
,`PatientID` int(10) unsigned
);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `payment`
-- （参见下面的实际视图）
--
CREATE TABLE `payment` (
`AppointmentID` int(10) unsigned
,`Payed` decimal(43,2)
,`Total` decimal(43,2)
,`Unpayed` decimal(45,2)
);

-- --------------------------------------------------------

--
-- 表的结构 `prescription_record`
--

CREATE TABLE `prescription_record` (
  `pres_rcd_id` int(10) UNSIGNED NOT NULL,
  `pres_id` int(10) UNSIGNED NOT NULL,
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
(1, 1, 1, 1, 0, 2, '2025-04-18 17:58:29'),
(2, 1, 1, 1, 1, 2, '2025-04-18 17:58:29'),
(3, 1, 1, 1, 2, 2, '2025-04-18 17:58:29');

--
-- 触发器 `prescription_record`
--
DELIMITER $$
CREATE TRIGGER `prescription_record_BEFORE_INSERT` BEFORE INSERT ON `prescription_record` FOR EACH ROW BEGIN
	DECLARE new_pres_id INT;
    SET NEW.oper_time = NOW();
    IF NEW.status_code = 2 THEN
        INSERT INTO drug_record (`drug_id`, `oper_amount`,`status_code`, `oper_time`)
        VALUES (NEW.drug_id, NEW.oper_amount, 0, NOW());
	ELSEIF NEW.status_code = 0 THEN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 替换视图以便查看 `prescription_table`
-- （参见下面的实际视图）
--
CREATE TABLE `prescription_table` (
`AppointmentID` int(10) unsigned
,`DrugName` varchar(45)
,`DrugPrice` decimal(10,2)
,`DrugQuantity` int(11)
,`DrugSumPrice` decimal(20,2)
,`PrescriptionID` int(10) unsigned
);

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
(3, 1, '2025-05-01', '14:00:00', '17:30:00'),
(4, 1, '2025-05-01', '18:00:00', '07:00:00'),
(5, 1, '2025-05-02', '18:00:00', '07:00:00');

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
(1, '0000000', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', 1),
(2, 'doctor_test1', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', 3),
(3, 'patient_test1', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', 4),
(4, 'patient_test2', '$2y$10$2.9cN8J0FEtHjitIYQD.eed94N5.WJrb/WSuHn7OEORc6D2w6wfaK', 4);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `users`
-- （参见下面的实际视图）
--
CREATE TABLE `users` (
`PasswordHash` varchar(64)
,`UserCell` varchar(11)
,`UserID` int(10) unsigned
,`Username` varchar(20)
,`UserType` varchar(10)
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
-- 视图结构 `departments`
--
DROP TABLE IF EXISTS `departments`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `departments`  AS  select `department`.`dep_id` AS `DepartmentId`,`department`.`dep_name` AS `Department` from `department` ;

-- --------------------------------------------------------

--
-- 视图结构 `doctors`
--
DROP TABLE IF EXISTS `doctors`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `doctors`  AS  select `doc`.`doc_id` AS `DoctorID`,`ui`.`user_name` AS `FullName`,`dep`.`dep_id` AS `DepartmentID`,`dep`.`dep_name` AS `DepartmentName`,`t`.`title_name` AS `Title` from (((`doctor` `doc` left join `user_info` `ui` on((`doc`.`doc_uid` = `ui`.`user_id`))) left join `department` `dep` on((`doc`.`dep_id` = `dep`.`dep_id`))) left join `doc_title` `t` on((`doc`.`title_id` = `t`.`title_id`))) ;

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
-- 视图结构 `prescription_table`
--
DROP TABLE IF EXISTS `prescription_table`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `prescription_table`  AS  select `p`.`pres_id` AS `PrescriptionID`,`p`.`ap_id` AS `AppointmentID`,`d`.`drug_name` AS `DrugName`,`p`.`oper_amount` AS `DrugQuantity`,`d`.`drug_price` AS `DrugPrice`,(`d`.`drug_price` * `p`.`oper_amount`) AS `DrugSumPrice` from (`prescription_record` `p` join `drug_def` `d` on((`d`.`drug_id` = `p`.`drug_id`))) where (`p`.`status_code` = 0) ;

-- --------------------------------------------------------

--
-- 视图结构 `users`
--
DROP TABLE IF EXISTS `users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `users`  AS  select `u`.`user_id` AS `UserID`,`ui`.`user_name` AS `Username`,`u`.`pass_hash` AS `PasswordHash`,`ad`.`auth_name` AS `UserType`,`ui`.`user_cell` AS `UserCell` from ((`user` `u` left join `user_info` `ui` on((`u`.`user_id` = `ui`.`user_id`))) join `auth_def` `ad` on((`u`.`user_auth` = `ad`.`auth_id`))) ;

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
  ADD KEY `fk_ap.docid_ref_doc_idx` (`doc_id`);

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
  ADD PRIMARY KEY (`exam_id`),
  ADD UNIQUE KEY `exam_id_UNIQUE` (`exam_id`);

--
-- 表的索引 `prescription_record`
--
ALTER TABLE `prescription_record`
  ADD PRIMARY KEY (`pres_rcd_id`),
  ADD UNIQUE KEY `pres_rcd_id_UNIQUE` (`pres_rcd_id`),
  ADD KEY `fk_resrcd.drugid_ref_drug_idx` (`drug_id`),
  ADD KEY `fk_presrcd.apid_ref_ap_idx` (`ap_id`);

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
  MODIFY `drug_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `drug_record`
--
ALTER TABLE `drug_record`
  MODIFY `drug_rcd_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `examination_def`
--
ALTER TABLE `examination_def`
  MODIFY `exam_def_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `exam_record`
--
ALTER TABLE `exam_record`
  MODIFY `exam_rcd_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- 限制导出的表
--

--
-- 限制表 `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `fk_ap.docid_ref_doc` FOREIGN KEY (`doc_id`) REFERENCES `doctor` (`doc_id`),
  ADD CONSTRAINT `fk_ap.patid_ref_ui` FOREIGN KEY (`pat_id`) REFERENCES `user_info` (`user_id`);

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
  ADD CONSTRAINT `fk_examres.examid_ref_exam` FOREIGN KEY (`exam_id`) REFERENCES `exam_record` (`exam_rcd_id`);

--
-- 限制表 `prescription_record`
--
ALTER TABLE `prescription_record`
  ADD CONSTRAINT `fk_presrcd.apid_ref_ap` FOREIGN KEY (`ap_id`) REFERENCES `appointment` (`ap_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_presrcd.drugid_ref_drug` FOREIGN KEY (`drug_id`) REFERENCES `drug_def` (`drug_id`) ON DELETE RESTRICT;

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
