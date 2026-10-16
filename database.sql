-- ========================================================
-- UIU INTERNSHIP & CAREER PORTAL - DATABASE SCHEMA
-- Target Database: MySQL 5.7+ / MariaDB (XAMPP localhost)
-- Database Name: uiu_career_portal
-- ========================================================

CREATE DATABASE IF NOT EXISTS `uiu_career_portal` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `uiu_career_portal`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `placement_statistics`;
DROP TABLE IF EXISTS `offer_letters`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `conversations`;
DROP TABLE IF EXISTS `resumes`;
DROP TABLE IF EXISTS `applications`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `companies`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `batches`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- 1. Departments Table
-- --------------------------------------------------------
CREATE TABLE `departments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `departments` (`code`, `name`) VALUES
('CSE', 'Computer Science & Engineering'),
('EEE', 'Electrical & Electronic Engineering'),
('BBA', 'Bachelor of Business Administration'),
('Data Science', 'Data Science & Analytics'),
('Civil', 'Civil Engineering');

-- --------------------------------------------------------
-- 2. Batches Table
-- --------------------------------------------------------
CREATE TABLE `batches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `batch_name` VARCHAR(50) NOT NULL UNIQUE,
  `department_code` VARCHAR(20) NOT NULL,
  `graduation_year` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `batches` (`batch_name`, `department_code`, `graduation_year`) VALUES
('CSE-48', 'CSE', 2025),
('CSE-49', 'CSE', 2026),
('CSE-50', 'CSE', 2027),
('EEE-22', 'EEE', 2026),
('BBA-64', 'BBA', 2026);

-- --------------------------------------------------------
-- 3. Users Table (Core Auth with BCRYPT Hashes)
-- Default Password for all demo accounts: 'demo1234'
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'student', 'company', 'guest') NOT NULL DEFAULT 'student',
  `status` ENUM('active', 'pending', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `users` (`id`, `email`, `password`, `role`, `status`) VALUES
(1, 'career.services@uiu.ac.bd', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeVlYcR3q6VzEw5x9/rFw0QZ9tYF4bTKG', 'admin', 'active'),
(2, 'mugdha.rahman@uiu.edu.bd', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeVlYcR3q6VzEw5x9/rFw0QZ9tYF4bTKG', 'student', 'active'),
(3, 'tahsin.ahmed@uiu.edu.bd', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeVlYcR3q6VzEw5x9/rFw0QZ9tYF4bTKG', 'student', 'active'),
(4, 'hr@techflow.com.bd', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeVlYcR3q6VzEw5x9/rFw0QZ9tYF4bTKG', 'company', 'active'),
(5, 'careers@brainstation-23.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeVlYcR3q6VzEw5x9/rFw0QZ9tYF4bTKG', 'company', 'active'),
(6, 'talent@bracits.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeVlYcR3q6VzEw5x9/rFw0QZ9tYF4bTKG', 'company', 'active');

-- --------------------------------------------------------
-- 4. Students Profile Table
-- --------------------------------------------------------
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `student_id` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(150) NOT NULL,
  `department` VARCHAR(50) NOT NULL,
  `batch` VARCHAR(50) NOT NULL,
  `cgpa` DECIMAL(3,2) DEFAULT 3.75,
  `phone` VARCHAR(30) DEFAULT '+880 1712-345678',
  `linkedin` VARCHAR(255) DEFAULT 'https://linkedin.com/in/mugdharahman',
  `github` VARCHAR(255) DEFAULT 'https://github.com/mugdharahman',
  `address` VARCHAR(255) DEFAULT 'Badda, Dhaka, Bangladesh',
  `skills` TEXT,
  `avatar` VARCHAR(255) DEFAULT 'default-avatar.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `students` (`id`, `user_id`, `student_id`, `full_name`, `department`, `batch`, `cgpa`, `phone`, `skills`) VALUES
(1, 2, '011201049', 'Mugdha Rahman', 'CSE', 'CSE-49', 3.75, '+880 1712-345678', 'JavaScript, React, Node.js, PHP, MySQL, Python, TailwindCSS'),
(2, 3, '011201088', 'Tahsin Ahmed', 'CSE', 'CSE-48', 3.82, '+880 1819-876543', 'Java, Spring Boot, MySQL, Docker, Microservices, AWS');

-- --------------------------------------------------------
-- 5. Companies Table
-- --------------------------------------------------------
CREATE TABLE `companies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `sector` VARCHAR(100) NOT NULL,
  `industry` VARCHAR(100) NOT NULL,
  `location` VARCHAR(150) NOT NULL,
  `website` VARCHAR(255) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `verified` TINYINT(1) DEFAULT 0,
  `status` ENUM('Verified', 'Pending', 'Rejected') DEFAULT 'Pending',
  `health_score` INT DEFAULT 90,
  `logo` VARCHAR(255) DEFAULT 'default-company.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `companies` (`id`, `user_id`, `name`, `sector`, `industry`, `location`, `website`, `email`, `phone`, `description`, `verified`, `status`, `health_score`) VALUES
(1, 4, 'TechFlow Solutions', 'Software & IT', 'Enterprise Cloud & Web Systems', 'Banani, Dhaka', 'https://techflow.com.bd', 'hr@techflow.com.bd', '+880 2 9876543', 'Leading software technology consultancy building enterprise solutions for fintech and telecom across Southeast Asia.', 1, 'Verified', 96),
(2, 5, 'Brain Station 23', 'Software & IT', 'Custom Software & Mobile App Engineering', 'Mohakhali DOHS, Dhaka', 'https://brainstation-23.com', 'careers@brainstation-23.com', '+880 2 8834567', 'Premier enterprise software export company in Bangladesh with 800+ engineers delivering banking, fintech, and ERP platforms.', 1, 'Verified', 98),
(3, 6, 'BRAC IT Services', 'Financial Technology', 'Banking & Microfinance Tech', 'Gulshan-1, Dhaka', 'https://bracits.com', 'talent@bracits.com', '+880 2 5566778', 'Technology subsidiary of BRAC delivering modern core banking, digital inclusion, and scalable data infrastructure.', 1, 'Verified', 94),
(4, NULL, 'Chaldal Tech', 'E-Commerce & Logistics', 'Supply Chain & Consumer Tech', 'Jigatola, Dhaka', 'https://chaldal.com', 'jobs@chaldal.com', '+880 9 6432111', 'Bangladesh largest online grocery and warehouse automation technology company.', 1, 'Verified', 92),
(5, NULL, 'Therap Services', 'Healthcare Software', 'Global SaaS for Developmental Disabilities', 'Baridhara Diplomatic Zone, Dhaka', 'https://therapservices.net', 'recruitment@therapservices.net', '+880 2 8829900', 'US-based healthcare SaaS company with state-of-the-art engineering development center in Dhaka.', 1, 'Verified', 97);

-- --------------------------------------------------------
-- 6. Jobs & Internships Table
-- --------------------------------------------------------
CREATE TABLE `jobs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `department` VARCHAR(50) NOT NULL DEFAULT 'CSE',
  `type` ENUM('Full-time', 'Internship', 'Contract', 'Part-time') NOT NULL DEFAULT 'Full-time',
  `workplace` ENUM('On-site', 'Remote', 'Hybrid') NOT NULL DEFAULT 'Hybrid',
  `location` VARCHAR(150) NOT NULL DEFAULT 'Dhaka, Bangladesh',
  `salary` VARCHAR(100) NOT NULL,
  `stipend_numeric` INT NOT NULL DEFAULT 0,
  `deadline` DATE NOT NULL,
  `skills` TEXT NOT NULL,
  `description` TEXT NOT NULL,
  `requirements` TEXT NOT NULL,
  `responsibilities` TEXT NOT NULL,
  `status` ENUM('Approved', 'Pending', 'Rejected') NOT NULL DEFAULT 'Approved',
  `featured` TINYINT(1) DEFAULT 0,
  `applicants_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `jobs` (`id`, `company_id`, `title`, `department`, `type`, `workplace`, `location`, `salary`, `stipend_numeric`, `deadline`, `skills`, `description`, `requirements`, `responsibilities`, `status`, `featured`) VALUES
(1, 1, 'Software Engineering Intern', 'CSE', 'Internship', 'Hybrid', 'Banani, Dhaka', 'BDT 20,000 /mo', 20000, '2026-12-31', 'JavaScript, React, Node.js, Git', 'Join our core platform engineering team working on scalable cloud microservices. Great mentorship provided for UIU undergraduates.', 'Undergraduate in CSE or Data Science, basic knowledge of web development, familiar with Git.', 'Collaborate with senior engineers to implement UI components and API endpoints.', 'Approved', 1),
(2, 2, 'Associate Software Engineer (Full Stack)', 'CSE', 'Full-time', 'On-site', 'Mohakhali DOHS, Dhaka', 'BDT 55,000 - 75,000 /mo', 55000, '2026-11-30', 'Java, Spring Boot, MySQL, REST API', 'Exciting opening for ambitious graduating students and fresh alumni to build enterprise FinTech solutions.', 'B.Sc in CSE with minimum CGPA 3.50. Strong proficiency in Java or C# and relational databases.', 'Design, develop, and test enterprise microservices for tier-1 financial clients.', 'Approved', 1),
(3, 3, 'Junior Cloud & DevOps Engineer', 'CSE', 'Full-time', 'Hybrid', 'Gulshan-1, Dhaka', 'BDT 60,000 /mo', 60000, '2026-11-15', 'Docker, Linux, AWS, CI/CD, Python', 'Support high-availability microfinance cloud infrastructure serving millions of daily mobile users.', 'Strong fundamentals in Linux administration, Docker containerization, and networking basics.', 'Maintain CI/CD pipelines, monitor cloud cluster performance, assist with infrastructure as code.', 'Approved', 1),
(4, 4, 'Frontend Developer (React / JavaScript)', 'CSE', 'Full-time', 'Remote', 'Dhaka (Remote)', 'BDT 50,000 - 65,000 /mo', 50000, '2026-12-15', 'JavaScript, React, CSS3, TailwindCSS', 'Build customer-facing e-commerce storefronts and warehouse management interfaces.', 'Proven proficiency in modern JavaScript, HTML5, CSS3, responsive design and state management.', 'Implement sleek responsive user interfaces and optimize bundle performance.', 'Approved', 0),
(5, 5, 'Quality Assurance (QA) Intern', 'CSE', 'Internship', 'On-site', 'Baridhara, Dhaka', 'BDT 22,000 /mo', 22000, '2026-12-20', 'Selenium, Manual Testing, SQL, JIRA', 'Learn test automation and QA methodologies in a multinational healthcare SaaS environment.', 'Final year undergraduate in CSE with strong analytical mindset and attention to detail.', 'Execute automated and manual test cases, track software defects, verify fixes.', 'Approved', 1);

-- --------------------------------------------------------
-- 7. Applications Table
-- --------------------------------------------------------
CREATE TABLE `applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `job_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `status` ENUM('Applied', 'Under Review', 'Shortlisted', 'Interview Scheduled', 'Offered', 'Selected', 'Rejected') NOT NULL DEFAULT 'Applied',
  `applied_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `notes` TEXT,
  `interview_date` VARCHAR(100) NULL,
  `interview_venue` VARCHAR(255) NULL,
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_student_job_app` (`job_id`, `student_id`)
) ENGINE=InnoDB;

INSERT INTO `applications` (`id`, `job_id`, `student_id`, `status`, `notes`, `interview_date`, `interview_venue`) VALUES
(1, 1, 1, 'Shortlisted', 'Candidate cleared initial coding assessment with 98% score.', 'Next Thursday, 2:00 PM', 'Google Meet / Banani HQ'),
(2, 2, 1, 'Under Review', 'Application under review by Engineering Hiring Panel.', NULL, NULL),
(3, 3, 2, 'Applied', 'Candidate applied online via UIU Career Portal.', NULL, NULL);

-- --------------------------------------------------------
-- 8. Resumes Table
-- --------------------------------------------------------
CREATE TABLE `resumes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL UNIQUE,
  `template_id` VARCHAR(50) DEFAULT 'classic',
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `degree` VARCHAR(150) NOT NULL,
  `university` VARCHAR(150) NOT NULL,
  `cgpa` VARCHAR(20) NOT NULL,
  `skills` TEXT NOT NULL,
  `projects` TEXT NOT NULL,
  `certifications` TEXT NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `resumes` (`student_id`, `full_name`, `email`, `phone`, `degree`, `university`, `cgpa`, `skills`, `projects`, `certifications`) VALUES
(1, 'Mugdha Rahman', 'mugdha.rahman@uiu.edu.bd', '+880 1712-345678', 'B.Sc. in Computer Science and Engineering (CSE)', 'United International University (UIU)', '3.75', 'JavaScript, PHP, MySQL, React, Python, Git, TailwindCSS', 'UIU Career Portal | PHP, MySQL, Vanilla JS\nFull-stack enterprise campus employment platform.\n\nLibrary Management System | Django, PostgreSQL\nBook inventory and lending management with concurrent indexing.', 'AWS Cloud Practitioner Certified\nDean\'s List Honor Award for Academic Excellence');

-- --------------------------------------------------------
-- 9. Conversations & Messages Table (Live Chat)
-- --------------------------------------------------------
CREATE TABLE `conversations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `company_id` INT NOT NULL,
  `last_message` TEXT,
  `last_message_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `conversations` (`id`, `student_id`, `company_id`, `last_message`) VALUES
(1, 1, 1, 'We look forward to meeting you on Thursday at 2:00 PM.');

CREATE TABLE `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT NOT NULL,
  `sender_type` ENUM('student', 'company') NOT NULL,
  `sender_name` VARCHAR(150) NOT NULL,
  `message_text` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `messages` (`conversation_id`, `sender_type`, `sender_name`, `message_text`) VALUES
(1, 'company', 'TechFlow Solutions', 'Hello Mugdha, congratulations on being shortlisted for Software Engineering Intern role!'),
(1, 'student', 'Mugdha Rahman', 'Thank you so much! I am very excited and preparing for the technical round.'),
(1, 'company', 'TechFlow Solutions', 'We look forward to meeting you on Thursday at 2:00 PM.');

-- --------------------------------------------------------
-- 10. Offer Letters Table
-- --------------------------------------------------------
CREATE TABLE `offer_letters` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT NOT NULL UNIQUE,
  `student_id` INT NOT NULL,
  `company_id` INT NOT NULL,
  `job_title` VARCHAR(150) NOT NULL,
  `salary_stipend` VARCHAR(100) NOT NULL,
  `joining_date` DATE NOT NULL,
  `expiry_date` DATE NOT NULL,
  `terms` TEXT NOT NULL,
  `status` ENUM('Issued', 'Accepted', 'Declined', 'Expired') DEFAULT 'Issued',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `offer_letters` (`application_id`, `student_id`, `company_id`, `job_title`, `salary_stipend`, `joining_date`, `expiry_date`, `terms`, `status`) VALUES
(1, 1, 1, 'Software Engineering Intern', 'BDT 20,000 / month', '2026-11-01', '2026-10-25', 'This appointment is subject to academic verification by the United International University Directorate of Career Services.', 'Issued');

-- --------------------------------------------------------
-- 11. Placement Statistics Table (Admin Reports)
-- --------------------------------------------------------
CREATE TABLE `placement_statistics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_name` VARCHAR(150) NOT NULL,
  `student_id` VARCHAR(50) NOT NULL,
  `department` VARCHAR(50) NOT NULL,
  `batch` VARCHAR(50) NOT NULL,
  `company_name` VARCHAR(150) NOT NULL,
  `job_title` VARCHAR(150) NOT NULL,
  `salary_numeric` INT NOT NULL,
  `placement_year` INT NOT NULL,
  `placed_at` DATE NOT NULL
) ENGINE=InnoDB;

INSERT INTO `placement_statistics` (`student_name`, `student_id`, `department`, `batch`, `company_name`, `job_title`, `salary_numeric`, `placement_year`, `placed_at`) VALUES
('Raihan Kabir', '011191023', 'CSE', 'CSE-46', 'Brain Station 23', 'Software Engineer', 65000, 2024, '2024-03-15'),
('Sadia Jahan', '011192055', 'CSE', 'CSE-46', 'Therap Services', 'Software Engineer', 72000, 2024, '2024-04-01'),
('Arif Hasan', '011201011', 'CSE', 'CSE-47', 'TechFlow Solutions', 'Full Stack Developer', 60000, 2025, '2025-01-20'),
('Farhana Islam', '011201099', 'EEE', 'EEE-21', 'Energypac Power', 'Assistant Engineer', 48000, 2025, '2025-02-10'),
('Mahin Chowdhury', '011202033', 'BBA', 'BBA-62', 'Unilever Bangladesh', 'Management Trainee', 75000, 2025, '2025-03-01');

-- --------------------------------------------------------
-- 12. Contact Messages Table
-- --------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread', 'read', 'replied') DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `contact_messages` (`name`, `email`, `subject`, `message`) VALUES
('Tanvir Ahmed', 'tanvir.ahmed@uiu.edu.bd', 'Internship MoU Inquiry', 'Hello, our batch is interested in knowing the upcoming interview drive schedule with Brain Station 23.'),
('Farzana Yasmin', 'farzana.yasmin@gmail.com', 'Corporate Partnership Proposal', 'We would like to partner with UIU Career Services for on-campus graduate recruitment.');
