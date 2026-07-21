-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql213.infinityfree.com
-- Generation Time: Jul 21, 2026 at 05:03 AM
-- Server version: 11.4.12-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `ID` int(10) UNSIGNED NOT NULL,
  `username` varchar(64) DEFAULT NULL,
  `UUID` char(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
  `DivID` tinyint(3) UNSIGNED NOT NULL DEFAULT 10,
  `RankID` tinyint(3) UNSIGNED NOT NULL DEFAULT 13,
  `TitleID` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `DisplayName` varchar(64) DEFAULT NULL,
  `email` varchar(120) NOT NULL,
  `password` char(128) DEFAULT NULL COMMENT '128 char long sha512 hash',
  `active` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0 inactive 1 active',
  `induction_date` date DEFAULT NULL,
  `dh` bit(1) NOT NULL DEFAULT b'0' COMMENT 'Administration/viewing privlages',
  `JoinDate` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Bug Fixed',
  `DOB` date NOT NULL DEFAULT '1990-01-07',
  `birth_place` varchar(64) NOT NULL DEFAULT 'Some Where',
  `height_metric` tinyint(3) UNSIGNED NOT NULL DEFAULT 6,
  `weight_metric` tinyint(3) UNSIGNED NOT NULL DEFAULT 6,
  `hair_color` varchar(3) NOT NULL DEFAULT 'blk',
  `species` varchar(10) NOT NULL DEFAULT 'Human',
  `gender` varchar(50) NOT NULL DEFAULT '1',
  `ethnic_origin` varchar(64) DEFAULT '1',
  `nationality` varchar(64) DEFAULT '1',
  `ident_marks` varchar(64) DEFAULT '1',
  `db_privlage_level` tinyint(3) UNSIGNED NOT NULL DEFAULT 255 COMMENT 'Not Used',
  `cCode` varchar(32) DEFAULT NULL COMMENT 'Authentication codes',
  `profile_img` varchar(255) DEFAULT NULL,
  `promotions_count` int(11) DEFAULT 0,
  `bio` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Profile information for Staff and Students.';

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `aid` int(10) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL COMMENT 'Asset UUID number for inworld assets',
  `type` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'See Asset Type table for possible values',
  `name` varchar(50) NOT NULL COMMENT 'Human readable Name for asset'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Contains Asset UUIDs for Sounds and Images for Classes';

-- --------------------------------------------------------

--
-- Table structure for table `asset_types`
--

CREATE TABLE `asset_types` (
  `atid` int(10) UNSIGNED NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Asset Type Codes';

--
-- Dumping data for table `asset_types`
--

INSERT INTO `asset_types` (`atid`, `type`) VALUES
(2, 'Sound'),
(1, 'Texture'),
(3, 'Object');

-- --------------------------------------------------------

--
-- Table structure for table `committee`
--

CREATE TABLE `committee` (
  `index` int(10) UNSIGNED NOT NULL,
  `aid` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Not Used, but kept';

-- --------------------------------------------------------

--
-- Table structure for table `Comms`
--

CREATE TABLE `Comms` (
  `comid` int(10) UNSIGNED NOT NULL,
  `accountid` int(10) UNSIGNED NOT NULL,
  `ObjectUUID` char(36) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `ClassID` int(10) UNSIGNED NOT NULL,
  `DivID` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `AutherID` int(10) UNSIGNED NOT NULL,
  `Class Name` tinytext NOT NULL,
  `Class Description` longtext NOT NULL,
  `Required Score` int(11) NOT NULL DEFAULT 80
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Acedemic Courses';

-- --------------------------------------------------------

--
-- Table structure for table `curriculum`
--

CREATE TABLE `curriculum` (
  `index` int(10) UNSIGNED NOT NULL,
  `classID` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `asset_id` int(10) UNSIGNED DEFAULT NULL,
  `lineNumber` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `displayText` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Curriculum for all Classes.';

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

CREATE TABLE `divisions` (
  `did` tinyint(3) UNSIGNED NOT NULL,
  `dname` varchar(50) NOT NULL DEFAULT '0',
  `alias` varchar(50) DEFAULT NULL,
  `FileNamePrefix` char(3) DEFAULT NULL,
  `colorX` int(10) UNSIGNED NOT NULL DEFAULT 255,
  `colorY` int(10) UNSIGNED NOT NULL DEFAULT 255,
  `ColorZ` int(10) UNSIGNED NOT NULL DEFAULT 255
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='List of Divisions';

--
-- Dumping data for table `divisions`
--

INSERT INTO `divisions` (`did`, `dname`, `alias`, `FileNamePrefix`, `colorX`, `colorY`, `ColorZ`) VALUES
(1, 'Academy Staff', 'acdemy', 'ACM', 212, 0, 255),
(2, 'Command', NULL, 'CMD', 255, 0, 0),
(3, 'Diplomacy', NULL, 'DIP', 255, 0, 0),
(4, 'Engineering', NULL, 'ENG', 255, 255, 0),
(5, 'Operations', NULL, 'OPS', 0, 0, 0),
(6, 'Science', NULL, 'SCI', 0, 29, 255),
(7, 'Security', NULL, 'SEC', 203, 208, 242),
(8, 'Academy Student', 'student', 'ACD', 0, 255, 0),
(9, 'Medical', NULL, 'MED', 0, 0, 255),
(10, 'Civilian', NULL, NULL, 255, 255, 255),
(11, 'Department of Temporal Investigations', 'temporal', 'TMP', 0, 50, 0),
(12, 'Information Technology Department', 'it', 'ITD', 0, 180, 180),
(13, 'JAG', 'jag', 'JAG', 255, 255, 255),
(14, 'Comms', NULL, 'COM', 255, 165, 0),
(16, 'Contributors', NULL, NULL, 255, 255, 255);

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `eid` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `question_number` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `question` varchar(255) NOT NULL,
  `a` varchar(255) NOT NULL,
  `b` varchar(255) NOT NULL,
  `c` varchar(255) NOT NULL,
  `d` varchar(255) NOT NULL,
  `answer` char(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gradebook`
--

CREATE TABLE `gradebook` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `courses` varchar(100) NOT NULL,
  `Grade` decimal(5,2) NOT NULL,
  `date_completed` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `Name` varchar(64) DEFAULT NULL,
  `Class Name` tinytext NOT NULL,
  `Grade` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `from_username` varchar(255) NOT NULL,
  `to_username` varchar(255) NOT NULL,
  `time_received` time NOT NULL DEFAULT curtime(),
  `date_received` date NOT NULL DEFAULT curdate(),
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_sender` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_receiver` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Rank`
--

CREATE TABLE `Rank` (
  `RankID` tinyint(3) UNSIGNED NOT NULL,
  `rname` varchar(50) DEFAULT '0',
  `RankLogo` varchar(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '★'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Rank table';

--
-- Dumping data for table `Rank`
--

INSERT INTO `Rank` (`RankID`, `rname`, `RankLogo`) VALUES
(1, 'Ensign', '●'),
(2, 'Lt. Junior Grade (Lt. JG)', '○●'),
(3, 'Lieutenant', '●●'),
(4, 'Lt. Commander', '○●●'),
(5, 'Commander', '●●●'),
(6, 'Captain', '●●●●'),
(7, 'Commodore', '▪'),
(8, 'Rear Admiral', '▪▪'),
(9, 'Vice Admiral', '▪▪▪'),
(10, 'Admiral', '▪▪▪▪'),
(11, 'Fleet Admiral', '▪▪▪▪▪'),
(12, 'Civilian', '═══════'),
(13, 'Cadet 1st Year', '▍'),
(14, 'Cadet 2nd Year', '▍▍'),
(15, 'Cadet 3rd Year', '▍▍▍'),
(16, 'Cadet 4th Year', '▍▍▍▍');

-- --------------------------------------------------------

--
-- Table structure for table `RankName`
--

CREATE TABLE `RankName` (
  `RankLogo` varchar(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `rname` varchar(50) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `tag_name` varchar(50) DEFAULT NULL,
  `active` tinyint(4) NOT NULL COMMENT '-1 inactive 0 cadet 1 active officer',
  `dname` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `course_id` varchar(100) NOT NULL,
  `question_number` varchar(255) NOT NULL,
  `user_answer` varchar(255) NOT NULL,
  `submitted_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

CREATE TABLE `security_logs` (
  `log_id` int(11) NOT NULL,
  `operator_username` varchar(100) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `target_module` varchar(100) NOT NULL,
  `target_identifier` varchar(150) NOT NULL,
  `change_telemetry` text NOT NULL,
  `log_timestamp` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `SJ_info`
--

CREATE TABLE `SJ_info` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `languages` varchar(255) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `hair` varchar(50) DEFAULT NULL,
  `eyes` varchar(50) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `medical_restrictions` text DEFAULT NULL,
  `other_info` text DEFAULT NULL,
  `other_id_marks_features` text DEFAULT NULL,
  `marital_status` varchar(50) DEFAULT NULL,
  `spouse` varchar(100) DEFAULT NULL,
  `children` text DEFAULT NULL,
  `mother` varchar(100) DEFAULT NULL,
  `father` varchar(100) DEFAULT NULL,
  `siblings` text DEFAULT NULL,
  `security_clearance` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Time Clock`
--

CREATE TABLE `Time Clock` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'User ID from accounts table',
  `time_in` int(10) UNSIGNED NOT NULL,
  `time_out` int(10) UNSIGNED DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Cross refrences clock times with AV UUIDs';

-- --------------------------------------------------------

--
-- Table structure for table `titler_urls`
--

CREATE TABLE `titler_urls` (
  `uuid` varchar(36) NOT NULL,
  `url` text NOT NULL,
  `last_seen` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Titles`
--

CREATE TABLE `Titles` (
  `tid` tinyint(3) UNSIGNED NOT NULL,
  `tag_name` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Common Group Tags';

--
-- Dumping data for table `Titles`
--

INSERT INTO `Titles` (`tid`, `tag_name`) VALUES
(1, 'Civilian'),
(2, 'Executive Assistant to The Commander-In-Chief'),
(3, 'Information Technology Specialist'),
(4, 'Chief of Security'),
(5, 'Chief of Engineering'),
(6, 'Chief of Operations'),
(7, 'Operations Officer'),
(8, 'Diplomat in Training'),
(9, 'Head of Group'),
(10, 'Information Service Technician'),
(11, 'Chief of Diplomacy'),
(12, 'Chief of Medical'),
(13, 'Academy Commandant'),
(14, 'Vice CnC'),
(15, 'Academy Instructor'),
(16, 'XO of Security'),
(17, 'XO of Diplomacy'),
(18, 'Commander in Chief'),
(20, 'Academy Student'),
(21, 'XO of Engineering'),
(22, 'Communications Bot'),
(23, 'Discharged'),
(24, 'Alternate Profile'),
(25, 'Judge Advocate General'),
(26, 'Group Management Bot'),
(27, 'XO of JAG'),
(28, 'Chief of Science'),
(29, 'Security Officer'),
(30, 'Beta Tester'),
(31, 'Academic Contributor');

-- --------------------------------------------------------

--
-- Table structure for table `versions`
--

CREATE TABLE `versions` (
  `vid` int(10) UNSIGNED NOT NULL,
  `ProdID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `AccountID` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Tracks accounts and product version information for the update system';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `UniqUUID` (`UUID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uname_uniq` (`username`),
  ADD KEY `FK_accounts_divisions` (`DivID`),
  ADD KEY `FK_accounts_Rank` (`RankID`),
  ADD KEY `FK_accounts_Titles` (`TitleID`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`aid`),
  ADD UNIQUE KEY `Index 2` (`uuid`),
  ADD KEY `FK_assets_asset_types` (`type`);

--
-- Indexes for table `asset_types`
--
ALTER TABLE `asset_types`
  ADD PRIMARY KEY (`atid`),
  ADD UNIQUE KEY `Index 2` (`type`);

--
-- Indexes for table `committee`
--
ALTER TABLE `committee`
  ADD PRIMARY KEY (`index`),
  ADD UNIQUE KEY `FK_committee_accounts` (`aid`);

--
-- Indexes for table `Comms`
--
ALTER TABLE `Comms`
  ADD PRIMARY KEY (`comid`),
  ADD UNIQUE KEY `Index 3` (`ObjectUUID`),
  ADD UNIQUE KEY `Index 4` (`url`),
  ADD KEY `FK_Comms_accounts` (`accountid`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`ClassID`),
  ADD UNIQUE KEY `ClassName` (`Class Name`(100)),
  ADD KEY `Divisions` (`DivID`),
  ADD KEY `FK_courses_accounts` (`AutherID`);

--
-- Indexes for table `curriculum`
--
ALTER TABLE `curriculum`
  ADD PRIMARY KEY (`index`),
  ADD UNIQUE KEY `Line` (`asset_id`,`displayText`,`lineNumber`),
  ADD KEY `FK_curriculum_courses` (`classID`);

--
-- Indexes for table `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`did`),
  ADD UNIQUE KEY `Index 3` (`dname`),
  ADD UNIQUE KEY `Index 2` (`alias`),
  ADD UNIQUE KEY `File name Prefix` (`FileNamePrefix`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`eid`),
  ADD UNIQUE KEY `Index 3` (`question_number`,`question`),
  ADD KEY `FK__courses` (`course_id`);

--
-- Indexes for table `gradebook`
--
ALTER TABLE `gradebook`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_course_constraint` (`username`,`courses`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Rank`
--
ALTER TABLE `Rank`
  ADD PRIMARY KEY (`RankID`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_course_question` (`username`,`course_id`,`question_number`),
  ADD UNIQUE KEY `user_course_unique` (`username`,`course_id`);

--
-- Indexes for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `SJ_info`
--
ALTER TABLE `SJ_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_username` (`username`);

--
-- Indexes for table `Time Clock`
--
ALTER TABLE `Time Clock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `titler_urls`
--
ALTER TABLE `titler_urls`
  ADD PRIMARY KEY (`uuid`);

--
-- Indexes for table `Titles`
--
ALTER TABLE `Titles`
  ADD PRIMARY KEY (`tid`);

--
-- Indexes for table `versions`
--
ALTER TABLE `versions`
  ADD PRIMARY KEY (`vid`),
  ADD KEY `FK_versions_products` (`ProdID`),
  ADD KEY `FK_versions_accounts` (`AccountID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `aid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `asset_types`
--
ALTER TABLE `asset_types`
  MODIFY `atid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `committee`
--
ALTER TABLE `committee`
  MODIFY `index` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Comms`
--
ALTER TABLE `Comms`
  MODIFY `comid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `ClassID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curriculum`
--
ALTER TABLE `curriculum`
  MODIFY `index` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `divisions`
--
ALTER TABLE `divisions`
  MODIFY `did` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `eid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gradebook`
--
ALTER TABLE `gradebook`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Rank`
--
ALTER TABLE `Rank`
  MODIFY `RankID` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `SJ_info`
--
ALTER TABLE `SJ_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Time Clock`
--
ALTER TABLE `Time Clock`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Titles`
--
ALTER TABLE `Titles`
  MODIFY `tid` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `versions`
--
ALTER TABLE `versions`
  MODIFY `vid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
