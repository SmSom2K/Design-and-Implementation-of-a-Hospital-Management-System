-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 31, 2025 at 06:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hospital_db`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `accepted_appointments`
-- (See below for the actual view)
--
CREATE TABLE `accepted_appointments` (
`id` int(11)
,`doctor_id` int(11)
,`patient_id` int(11)
,`patient_user_id` int(11)
,`patient_name` varchar(100)
,`appointment_time` datetime
,`purpose` varchar(255)
,`status` varchar(50)
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `action_type`, `description`, `timestamp`) VALUES
(140, 'add_doctor', 'Doctor <strong>sami sami</strong> added to <em>asdasd</em> department.', '2025-05-13 19:13:19'),
(141, 'add_patient', 'Patient <strong>sami sami1</strong> added and assigned to doctor ID <em>0</em>.', '2025-05-13 19:13:29'),
(142, 'delete_patient', 'Deleted patient sami sami1 (User ID: 92)', '2025-05-13 19:15:48'),
(143, 'add_patient', 'Patient <strong>sami sami1</strong> added and assigned to doctor ID <em>0</em>.', '2025-05-13 19:16:22'),
(144, 'add_patient', 'Patient <strong>sami sami</strong> added and assigned to doctor ID <em>0</em>.', '2025-05-13 19:21:13'),
(145, 'delete_patient', 'Deleted patient sami sami1 (User ID: 93)', '2025-05-13 19:29:51'),
(146, 'add_patient', 'Patient <strong>sami sami</strong> added and assigned to doctor ID <em>0</em>.', '2025-05-13 19:31:26'),
(147, 'assign', 'Assigned patient ID 95 to doctor ID 91.', '2025-05-13 19:55:30'),
(148, 'assign', 'Assigned patient ID 94 to doctor ID 91.', '2025-05-13 19:55:32');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_time` datetime NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `doctor_id`, `patient_id`, `appointment_time`, `purpose`, `status`, `created_at`) VALUES
(27, 91, 95, '2025-05-14 19:52:00', 'asdasd', 'accepted', '2025-05-13 18:49:05');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(200) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `dateAssigned` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `doctor_id`, `patient_id`, `dateAssigned`) VALUES
(33, 0, 94, '2025-05-13 19:21:13'),
(34, 0, 95, '2025-05-13 19:31:26'),
(35, 91, 95, '2025-05-13 19:55:30'),
(36, 91, 94, '2025-05-13 19:55:32');

-- --------------------------------------------------------

--
-- Table structure for table `diagnoses`
--

CREATE TABLE `diagnoses` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL,
  `severity` enum('mild','moderate','severe','critical') NOT NULL,
  `treatment_plan` text NOT NULL,
  `medications` text DEFAULT NULL,
  `follow_up` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diagnoses`
--

INSERT INTO `diagnoses` (`id`, `patient_id`, `doctor_id`, `title`, `description`, `date`, `severity`, `treatment_plan`, `medications`, `follow_up`, `created_at`) VALUES
(18, 95, 91, 'asd', 'asd', '2025-05-14', 'mild', 'asd', 'asd', '2025-05-15', '2025-05-13 18:56:17');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_activity_log`
--

CREATE TABLE `doctor_activity_log` (
  `id` int(11) NOT NULL,
  `doctor_email` varchar(100) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_activity_log`
--

INSERT INTO `doctor_activity_log` (`id`, `doctor_email`, `patient_id`, `description`, `created_at`) VALUES
(30, 'loudjsami@gmail.com', NULL, 'Added diagnosis “asd” (severity=mild) for patient_id=95', '2025-05-13 18:56:17'),
(31, 'loudjsami@gmail.com', 95, 'Added a note for Patient sami sami (ID 95)', '2025-05-13 18:56:20'),
(32, 'loudjsami@gmail.com', NULL, 'Uploaded document “asd” for patient_id=95', '2025-05-13 18:56:25'),
(33, 'loudjsami@gmail.com', NULL, 'Accepted appointment_id=27', '2025-05-13 18:56:59');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `parent_type` enum('medical_record','diagnosis','note') NOT NULL,
  `parent_id` int(11) NOT NULL,
  `doc_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `parent_type`, `parent_id`, `doc_type`, `description`, `file_path`, `uploaded_by`, `uploaded_at`) VALUES
(26, 'diagnosis', 18, NULL, NULL, 'api_doctor/uploads/diagnoses/1747162577_FB_IMG_17466260968386310.jpg', 91, '2025-05-13 19:56:17'),
(27, 'medical_record', 95, 'asd', 'ads', '/hospital/Hospital-Management-Html-master/uploads/doc_682395d93f507.jpg', 91, '2025-05-13 19:56:25');

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `record_date` datetime NOT NULL,
  `record_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_records`
--

INSERT INTO `medical_records` (`id`, `patient_id`, `record_date`, `record_type`, `description`) VALUES
(9, 95, '2025-05-13 19:56:25', 'asd', 'ads');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `user_id`, `note`, `created_at`) VALUES
(13, 95, 'asd', '2025-05-13 18:56:20');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `name`, `age`, `gender`, `phone`, `blood_type`, `status`, `user_id`) VALUES
(27, 'sami sami', 1, 'male', 'asdasd', 'asdad', 'asdasd', 94),
(28, 'sami sami', 1, 'male', '0665497251', 'asd', 'asd', 95);

-- --------------------------------------------------------

--
-- Stand-in structure for view `pending_appointments`
-- (See below for the actual view)
--
CREATE TABLE `pending_appointments` (
`id` int(11)
,`doctor_id` int(11)
,`patient_id` int(11)
,`patient_user_id` int(11)
,`patient_name` varchar(100)
,`appointment_time` datetime
,`purpose` varchar(255)
,`status` varchar(50)
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `NAME` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `PASSWORD` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'patient',
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `NAME`, `email`, `PASSWORD`, `role`, `department`, `phone`, `status`, `username`) VALUES
(86, 'sami sami', 'admin@gmail.com', '$2y$10$BLttURz8Q.C/haLFMdWPWO9rKKxXednFcZ5SPJli.MoDzj7sqXcmS', 'admin', NULL, NULL, NULL, NULL),
(91, 'sami sami', 'loudjsami@gmail.com', '$2y$10$RwVgTFqTVvKWuOWEd4kMLOrSLKXBIU4pxu5av/H.sIUY8BBiuw3pm', 'doctor', 'asdasd', '0665497251', 'asdasd', 'asdasd'),
(94, 'sami sami', 'loudjsami@gmail.com', '$2y$10$0IgLsMpMRdN2uwMV4c4qTOitW1kdPVAzNNMvKZguLYw54Dm2WNa5i', 'patient', NULL, NULL, NULL, 'asdd'),
(95, 'sami sami', 'loudjsami@2gmail.com', '$2y$10$wltOQYxzsZCxwhdhFBqsiOIRkUNS6OKyxqWBeGkqUtFr/aOukyDKi', 'patient', NULL, NULL, NULL, 'asdasd');

-- --------------------------------------------------------

--
-- Structure for view `accepted_appointments`
--
DROP TABLE IF EXISTS `accepted_appointments`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `accepted_appointments`  AS SELECT `a`.`id` AS `id`, `a`.`doctor_id` AS `doctor_id`, `a`.`patient_id` AS `patient_id`, `p`.`user_id` AS `patient_user_id`, `u`.`NAME` AS `patient_name`, `a`.`appointment_time` AS `appointment_time`, `a`.`purpose` AS `purpose`, `a`.`status` AS `status`, `a`.`created_at` AS `created_at` FROM ((`appointments` `a` join `patients` `p` on(`p`.`id` = `a`.`patient_id`)) join `users` `u` on(`u`.`id` = `p`.`user_id`)) WHERE `a`.`status` = 'accepted' ORDER BY `a`.`appointment_time` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `pending_appointments`
--
DROP TABLE IF EXISTS `pending_appointments`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `pending_appointments`  AS SELECT `a`.`id` AS `id`, `a`.`doctor_id` AS `doctor_id`, `a`.`patient_id` AS `patient_id`, `p`.`user_id` AS `patient_user_id`, `u`.`NAME` AS `patient_name`, `a`.`appointment_time` AS `appointment_time`, `a`.`purpose` AS `purpose`, `a`.`status` AS `status`, `a`.`created_at` AS `created_at` FROM ((`appointments` `a` join `patients` `p` on(`p`.`id` = `a`.`patient_id`)) join `users` `u` on(`u`.`id` = `p`.`user_id`)) WHERE `a`.`status` = 'pending' ORDER BY `a`.`appointment_time` DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `diagnoses`
--
ALTER TABLE `diagnoses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `doctor_activity_log`
--
ALTER TABLE `doctor_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_email` (`doctor_email`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_docs_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `diagnoses`
--
ALTER TABLE `diagnoses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `doctor_activity_log`
--
ALTER TABLE `doctor_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `diagnoses`
--
ALTER TABLE `diagnoses`
  ADD CONSTRAINT `diagnoses_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `diagnoses_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_docs_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
