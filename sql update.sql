-- =====================================================
-- ONLINE COURSES SYSTEM SQL
-- =====================================================
-- This SQL adds online courses functionality to existing UNITED ACADEMY-UARD database
-- Compatible with existing database structure and follows same patterns

-- --------------------------------------------------------
-- Table structure for table `online_courses`
-- --------------------------------------------------------
CREATE TABLE `online_courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` text DEFAULT NULL,
  `long_description` longtext DEFAULT NULL,
  `cover_image` varchar(500) DEFAULT NULL,
  `category` enum('health','it','business','languages','professional') NOT NULL DEFAULT 'health',
  `level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `duration` varchar(50) DEFAULT NULL, -- e.g., "6 weeks", "3 months"
  `price` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'XAF',
  `language` varchar(50) DEFAULT NULL, -- Course language (English, French)
  `requirements` longtext DEFAULT NULL, -- Prerequisites
  `objectives` longtext DEFAULT NULL, -- Learning objectives
  `curriculum` longtext DEFAULT NULL, -- Course syllabus
  `instructor_name` varchar(100) DEFAULT NULL,
  `instructor_bio` text DEFAULT NULL,
  `instructor_image` varchar(500) DEFAULT NULL,
  `video_intro_url` varchar(500) DEFAULT NULL, -- Intro video URL
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `featured` tinyint(1) DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `max_students` int(10) UNSIGNED DEFAULT NULL,
  `current_enrollments` int(10) UNSIGNED DEFAULT 0,
  `order_position` int(11) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(500) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `course_applications`
-- --------------------------------------------------------
CREATE TABLE `course_applications` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `current_education` varchar(100) DEFAULT NULL,
  `work_experience` text DEFAULT NULL,
  `motivation` text DEFAULT NULL,
  `how_hear_about` varchar(50) DEFAULT NULL, -- How they heard about the course
  `preferred_schedule` varchar(50) DEFAULT NULL, -- Preferred learning schedule
  `has_computer` tinyint(1) DEFAULT NULL, -- Has computer/internet access
  `needs_financial_aid` tinyint(1) DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  `status` enum('pending','reviewed','accepted','rejected','enrolled') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `course_modules`
-- --------------------------------------------------------
CREATE TABLE `course_modules` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `module_title` varchar(255) NOT NULL,
  `module_description` longtext DEFAULT NULL,
  `module_order` int(11) DEFAULT 0,
  `duration_hours` decimal(5,2) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `reading_materials` longtext DEFAULT NULL, -- JSON array of materials
  `assignments` longtext DEFAULT NULL, -- JSON array of assignments
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `course_enrollments`
-- --------------------------------------------------------
CREATE TABLE `course_enrollments` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `student_email` varchar(100) NOT NULL,
  `student_phone` varchar(20) DEFAULT NULL,
  `enrollment_date` date NOT NULL,
  `status` enum('active','completed','dropped','suspended') DEFAULT 'active',
  `completion_percentage` decimal(5,2) DEFAULT 0.00,
  `certificate_issued` tinyint(1) DEFAULT 0,
  `certificate_url` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Add permissions for online courses management
-- --------------------------------------------------------
INSERT INTO `permissions` (`id`, `permission_name`, `permission_slug`, `module`, `description`, `created_at`) VALUES
(24, 'View Online Courses', 'view_online_courses', 'online_courses', 'Can view online courses list', '2026-05-12 17:00:00'),
(25, 'Create Online Courses', 'create_online_courses', 'online_courses', 'Can create new online courses', '2026-05-12 17:00:00'),
(26, 'Edit Online Courses', 'edit_online_courses', 'online_courses', 'Can edit online courses', '2026-05-12 17:00:00'),
(27, 'Delete Online Courses', 'delete_online_courses', 'online_courses', 'Can delete online courses', '2026-05-12 17:00:00'),
(28, 'Publish Online Courses', 'publish_online_courses', 'online_courses', 'Can publish/unpublish online courses', '2026-05-12 17:00:00'),
(29, 'View Course Applications', 'view_course_applications', 'online_courses', 'Can view course applications', '2026-05-12 17:00:00'),
(30, 'Manage Course Applications', 'manage_course_applications', 'online_courses', 'Can approve/reject course applications', '2026-05-12 17:00:00'),
(31, 'View Course Enrollments', 'view_course_enrollments', 'online_courses', 'Can view course enrollments', '2026-05-12 17:00:00'),
(32, 'Manage Course Enrollments', 'manage_course_enrollments', 'online_courses', 'Can manage student enrollments', '2026-05-12 17:00:00');

-- --------------------------------------------------------
-- Sample data can be added manually through admin interface
-- --------------------------------------------------------

-- --------------------------------------------------------
-- Add indexes for better performance
-- --------------------------------------------------------
ALTER TABLE `online_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_order_position` (`order_position`);

ALTER TABLE `course_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_submitted_at` (`submitted_at`);

ALTER TABLE `course_modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_module_order` (`module_order`),
  ADD KEY `idx_is_active` (`is_active`);

ALTER TABLE `course_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_student_email` (`student_email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_enrollment_date` (`enrollment_date`);

-- --------------------------------------------------------
-- Create views for better data management
-- --------------------------------------------------------
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_active_online_courses` AS
SELECT 
  `oc`.`id` AS `id`,
  `oc`.`title` AS `title`,
  `oc`.`slug` AS `slug`,
  `oc`.`short_description` AS `short_description`,
  `oc`.`cover_image` AS `cover_image`,
  `oc`.`category` AS `category`,
  `oc`.`level` AS `level`,
  `oc`.`duration` AS `duration`,
  `oc`.`price` AS `price`,
  `oc`.`currency` AS `currency`,
  `oc`.`language` AS `language`,
  `oc`.`start_date` AS `start_date`,
  `oc`.`end_date` AS `end_date`,
  `oc`.`max_students` AS `max_students`,
  `oc`.`current_enrollments` AS `current_enrollments`,
  `oc`.`status` AS `status`,
  `oc`.`featured` AS `featured`,
  `oc`.`order_position` AS `order_position`,
  `oc`.`created_at` AS `created_at`,
  `oc`.`updated_at` AS `updated_at`,
  (SELECT COUNT(*) FROM `course_applications` WHERE `course_id` = `oc`.`id` AND `status` = 'pending') AS `pending_applications`,
  (SELECT COUNT(*) FROM `course_enrollments` WHERE `course_id` = `oc`.`id` AND `status` = 'active') AS `active_enrollments`
FROM `online_courses` `oc` 
WHERE `oc`.`status` = 'published' 
ORDER BY `oc`.`order_position` ASC, `oc`.`created_at` DESC;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_course_applications` AS
SELECT 
  `ca`.`id` AS `id`,
  `ca`.`course_id` AS `course_id`,
  `ca`.`first_name` AS `first_name`,
  `ca`.`last_name` AS `last_name`,
  `ca`.`email` AS `email`,
  `ca`.`phone` AS `phone`,
  `ca`.`status` AS `status`,
  `ca`.`submitted_at` AS `submitted_at`,
  `ca`.`reviewed_at` AS `reviewed_at`,
  `oc`.`title` AS `course_title`,
  `oc`.`category` AS `course_category`,
  `oc`.`level` AS `course_level`,
  `oc`.`start_date` AS `course_start_date`
FROM `course_applications` `ca` 
LEFT JOIN `online_courses` `oc` ON `ca`.`course_id` = `oc`.`id` 
ORDER BY `ca`.`submitted_at` DESC;

-- =====================================================
-- END OF ONLINE COURSES SYSTEM SQL
-- =====================================================