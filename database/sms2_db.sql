-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: sms2_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `sms2_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sms2_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `sms2_db`;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `user_name` varchar(150) DEFAULT NULL,
  `role_key` varchar(40) DEFAULT NULL,
  `action` varchar(40) NOT NULL,
  `module_key` varchar(60) DEFAULT NULL,
  `detail` varchar(500) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_logs_user` (`user_id`),
  KEY `idx_logs_action` (`action`),
  KEY `idx_logs_created` (`created_at`),
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_throttles`
--

DROP TABLE IF EXISTS `login_throttles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_throttles` (
  `id` int(10) unsigned NOT NULL,
  `throttle_key` char(64) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_throttles`
--

LOCK TABLES `login_throttles` WRITE;
/*!40000 ALTER TABLE `login_throttles` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_throttles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_requests`
--

DROP TABLE IF EXISTS `password_reset_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `module_key` varchar(60) NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `requested_password_hash` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `admin_id` int(10) unsigned DEFAULT NULL,
  `admin_note` varchar(500) DEFAULT NULL,
  `temp_password_set` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_prr_user` (`user_id`),
  KEY `idx_prr_status` (`status`),
  KEY `idx_prr_module` (`module_key`),
  KEY `fk_prr_admin` (`admin_id`),
  CONSTRAINT `fk_prr_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_prr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_requests`
--

LOCK TABLES `password_reset_requests` WRITE;
/*!40000 ALTER TABLE `password_reset_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reset_user` (`user_id`),
  KEY `idx_reset_token` (`token_hash`),
  KEY `idx_reset_expires` (`expires_at`),
  CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_key` varchar(40) NOT NULL,
  `module_key` varchar(60) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_role_module` (`role_key`,`module_key`),
  KEY `idx_perm_module` (`module_key`),
  CONSTRAINT `fk_perm_role` FOREIGN KEY (`role_key`) REFERENCES `roles` (`role_key`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1018 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (194,'superadmin','user-management',1,'2026-08-08 22:07:16'),(195,'superadmin','student_portal',0,'2026-08-08 22:07:21'),(196,'admission','enrollment',1,'2026-08-18 00:19:23'),(197,'registrar','registrar',1,'2026-08-08 22:07:16'),(198,'registrar','curriculum',1,'2026-08-08 22:07:16'),(199,'registrar','scheduling',1,'2026-08-08 22:07:16'),(200,'finance','payment',1,'2026-08-08 22:07:16'),(201,'hr','faculty',1,'2026-08-08 22:07:16'),(202,'adviser','faculty',1,'2026-08-08 22:07:16'),(204,'it_office','lms',1,'2026-08-08 22:07:16'),(205,'osa','cocurricular',1,'2026-08-08 22:07:16'),(206,'qa','accreditation',1,'2026-08-08 22:07:16'),(207,'crad_officer','crad',1,'2026-08-08 22:07:16'),(208,'research_coordinator','crad',1,'2026-08-08 22:07:16'),(209,'student','student_portal',1,'2026-08-08 22:07:16'),(247,'research_director','faculty',1,'2026-08-09 19:31:14'),(353,'research_grant','crad_grant',1,'2026-08-10 20:01:49'),(618,'panel','faculty',1,'2026-08-15 17:07:16'),(715,'admin','user-management',1,'2026-08-18 00:20:58'),(716,'admin','enrollment',1,'2026-08-18 00:20:58'),(717,'admin','registrar',1,'2026-08-18 00:20:58'),(718,'admin','curriculum',1,'2026-08-18 00:20:58'),(719,'admin','scheduling',1,'2026-08-18 00:20:58'),(720,'admin','payment',1,'2026-08-18 00:20:58'),(721,'admin','faculty',1,'2026-08-18 00:20:58'),(722,'admin','accreditation',1,'2026-08-18 00:20:58'),(723,'admin','cocurricular',1,'2026-08-18 00:20:58'),(724,'admin','lms',1,'2026-08-18 00:20:58'),(725,'admin','crad',1,'2026-08-18 00:20:58'),(726,'admin','student_portal',0,'2026-08-18 00:20:58'),(817,'grammarian','faculty',1,'2026-08-18 00:36:15'),(838,'sms_admin','enrollment',1,'2026-08-18 00:47:00'),(839,'sms_admin','registrar',1,'2026-08-18 00:47:00'),(840,'sms_admin','curriculum',1,'2026-08-18 00:47:00'),(841,'sms_admin','accreditation',1,'2026-08-18 00:47:00'),(842,'sms_admin','payment',1,'2026-08-18 00:38:50'),(843,'sms_admin','faculty',1,'2026-08-18 00:47:00'),(844,'sms_admin','scheduling',1,'2026-08-18 00:47:00'),(845,'sms_admin','cocurricular',1,'2026-08-18 00:47:00'),(846,'sms_admin','lms',1,'2026-08-18 00:47:00'),(847,'sms_admin','crad',1,'2026-08-18 00:47:00'),(987,'review_committee','faculty',1,'2026-08-19 19:22:13');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `role_key` varchar(40) NOT NULL,
  `label` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_key` (`role_key`)
) ENGINE=InnoDB AUTO_INCREMENT=1184 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','Super Admin','Legacy super admin access',1,'2026-07-22 22:24:44'),(2,'registrar','Registrar','Enrollment, records, scheduling',1,'2026-07-22 22:24:44'),(3,'finance','Finance','Payments and receivables',1,'2026-07-22 22:24:44'),(4,'hr','Dean','Dean and faculty processes',1,'2026-07-22 22:24:44'),(5,'it_office','IT Office','LMS and IT modules',1,'2026-07-22 22:24:44'),(6,'osa','OSA','Student affairs / co-curricular',1,'2026-07-22 22:24:44'),(7,'qa','QA Office','Accreditation and quality',1,'2026-07-22 22:24:44'),(8,'crad_officer','CRAD Officer','Research and development',1,'2026-07-22 22:24:44'),(9,'student','Student','Student portal only',1,'2026-07-22 22:24:44'),(10,'superadmin','Super Admin','Full system access',1,'2026-08-08 17:25:19'),(11,'admission','Admission','Admission office access',1,'2026-08-08 17:25:19'),(56,'research_coordinator','Research Coordinator','Research coordination access',1,'2026-08-08 18:13:51'),(102,'adviser','Adviser','Research adviser faculty account',1,'2026-08-08 21:35:14'),(213,'research_director','Research Director','Research defense scheduling director account',1,'2026-08-09 19:31:14'),(384,'research_grant','CRAD Officer','Research grant management access',1,'2026-08-10 20:01:49'),(788,'panel','Panel Member','Research defense panel account',1,'2026-08-15 17:07:16'),(1048,'grammarian','Grammarian','Research grammar and manuscript evaluation account',1,'2026-08-18 00:36:15'),(1081,'sms_admin','Admin','General administrator account',1,'2026-08-18 00:38:50'),(1153,'review_committee','Review Committee','Review committee evaluation account',1,'2026-08-19 19:19:22');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_otps`
--

DROP TABLE IF EXISTS `security_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `security_otps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `purpose` varchar(40) NOT NULL,
  `code_hash` char(64) NOT NULL,
  `module_key` varchar(60) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_otps`
--

LOCK TABLES `security_otps` WRITE;
/*!40000 ALTER TABLE `security_otps` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES ('csrf_enabled','1','2026-07-22 22:24:44'),('lockout_minutes','1','2026-07-23 08:05:06'),('lockout_seconds','15','2026-07-23 08:05:06'),('lockout_unit','seconds','2026-07-23 08:05:06'),('lockout_value','15','2026-07-23 08:05:06'),('mail_admin_email','admin@bestlink.edu.ph','2026-08-22 15:44:13'),('mail_from_email','noreply@bestlink.edu.ph','2026-07-23 10:33:25'),('mail_from_name','SMS 2','2026-07-23 10:33:25'),('mail_show_link_on_failure','0','2026-07-23 10:34:27'),('max_failed_logins','3','2026-07-23 07:33:05'),('min_password_length','8','2026-07-22 22:24:44'),('module_kick_epoch_crad','1784849304','2026-07-23 15:28:24'),('module_maintenance_crad','0','2026-07-23 15:29:22'),('module_maintenance_msg_crad','The system is currently under maintenance. Some services may be temporarily unavailable.\r\n\r\nThank you for your patience and understanding.','2026-07-23 15:05:14'),('password_expiry_days','0','2026-07-22 22:24:44'),('require_password_change_first_login','0','2026-07-22 22:24:44'),('session_timeout_minutes','30','2026-07-22 22:24:44'),('smtp_encryption','tls','2026-07-23 10:33:25'),('smtp_host','','2026-08-22 15:44:13'),('smtp_password','','2026-08-22 15:44:13'),('smtp_port','587','2026-07-23 10:33:25'),('smtp_username','','2026-08-22 15:44:13');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_authenticators`
--

DROP TABLE IF EXISTS `user_authenticators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_authenticators` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `secret` varchar(512) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `pending_secret` varchar(512) DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_authenticators`
--

LOCK TABLES `user_authenticators` WRITE;
/*!40000 ALTER TABLE `user_authenticators` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_authenticators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_passkeys`
--

DROP TABLE IF EXISTS `user_passkeys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_passkeys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `credential_id` varchar(255) NOT NULL,
  `public_key` text NOT NULL,
  `sign_count` int(10) unsigned NOT NULL DEFAULT 0,
  `device_name` varchar(120) NOT NULL DEFAULT 'Passkey',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_passkeys`
--

LOCK TABLES `user_passkeys` WRITE;
/*!40000 ALTER TABLE `user_passkeys` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_passkeys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `role_key` varchar(40) NOT NULL,
  `student_id` varchar(40) DEFAULT NULL,
  `status` enum('active','inactive','locked','suspended') NOT NULL DEFAULT 'active',
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `failed_login_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `password_changed_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `last_seen_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role` (`role_key`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_student_id` (`student_id`),
  KEY `idx_users_last_seen` (`last_seen_at`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_key`) REFERENCES `roles` (`role_key`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=854 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'superadmin','superadmin@bestlink.edu.ph','$2y$10$Ji7G26YUMawdXvR4BEN3wu62vvlmw8kX9iv5JoCvzzSgQYpEU6GzW','Super Admin','superadmin',NULL,'active',0,0,NULL,'2026-08-22 15:36:47',NULL,NULL,NULL,NULL,'2026-07-22 22:53:59','2026-08-22 15:36:47'),(2,'registrar','registrar@bestlink.edu.ph','$2y$10$al7mLocsbCU7McMqUdxzMOwCdJTq49PO1k2pZr8BfGR1RNKeh5GzC','Registrar','registrar',NULL,'active',0,0,NULL,'2026-08-22 15:36:48',NULL,NULL,NULL,NULL,'2026-07-22 22:53:59','2026-08-22 15:36:48'),(3,'cradofficer','cradofficer@bestlink.edu.ph','$2y$10$uiGAKoHFVgiXhHnofDCyG.3dMdcgimQGlmbtktq9OcWmXu//F1CpO','CRAD Officer','crad_officer',NULL,'active',0,0,NULL,'2026-08-22 15:36:48',NULL,NULL,NULL,NULL,'2026-07-22 22:53:59','2026-08-22 15:36:48'),(4,'finance','finance@bestlink.edu.ph','$2y$10$B51J0FqmcD20gfie4foEau5iIJRUrssFdeAxLwifRoAa/otj.lX2i','Finance','finance',NULL,'active',0,0,NULL,'2026-08-22 15:36:51',NULL,NULL,NULL,NULL,'2026-07-22 22:54:00','2026-08-22 15:36:51'),(5,'studentaffairs','studentaffairs@bestlink.edu.ph','$2y$10$xYSaRrOz.f3kqhqVL5ahH.DWyGnHJbHpDd2xMGziK91mXK/UOTSPa','Student Affairs','osa',NULL,'active',0,0,NULL,'2026-08-22 15:36:51',NULL,NULL,NULL,NULL,'2026-07-22 22:54:00','2026-08-22 15:36:51'),(6,'itofficer','itofficer@bestlink.edu.ph','$2y$10$CeSVrHyrIyjT6WYEnsiBK.IUFbpfqKpzWGb/naVdCDp3o3rpzRAEi','IT Officer','it_office',NULL,'active',0,0,NULL,'2026-08-22 15:36:52',NULL,NULL,NULL,NULL,'2026-07-22 22:54:00','2026-08-22 15:36:52'),(7,'qualityassurance','qualityassurance@bestlink.edu.ph','$2y$10$dgFan.9Nn2BYVAfxp6xzKOJBNbaJIosk77xpkzWJf.oL9pkx13XbW','Quality Assurance','qa',NULL,'active',0,0,NULL,'2026-08-22 15:36:52',NULL,NULL,NULL,NULL,'2026-07-22 22:54:00','2026-08-22 15:36:52'),(8,'dean','dean@bestlink.edu.ph','$2y$10$6K0Riq57hjdc2kPK30Z75OjqlJuYKcBO/QBIBPavMlDFR8GRwjvYO','Dean','hr',NULL,'active',0,0,NULL,'2026-08-22 15:36:52',NULL,NULL,NULL,NULL,'2026-07-22 22:54:00','2026-08-22 15:36:52'),(9,'s230000001','s230000001@bestlink.edu.ph','$2y$10$JBT2poGZJ3VaaSK6vEG4ReeiBrpyWL5XW4Xzu7AVzc30AkWvnEKCG','Student User','student','S230000001','active',0,0,NULL,'2026-08-22 15:36:53',NULL,NULL,NULL,NULL,'2026-07-22 22:54:00','2026-08-22 15:36:53'),(20,'admission','admission@bestlink.edu.ph','$2y$10$jbms3KLO7N5qBcVb1T9MCubFERuNA2uGzRl0LHPnHRNs4uk8.FIN.','Admission','admission',NULL,'active',0,0,NULL,'2026-08-22 15:36:48',NULL,NULL,NULL,NULL,'2026-08-08 17:25:20','2026-08-22 15:36:48'),(40,'researchcoordinator','researchcoordinator@bestlink.edu.ph','$2y$10$GX0PwtLdzAzGVt3gzNPwYu0t2YvFCF2JWnTs0FKcVF4Xe944x3j4e','Mrs. Kris Guevarra','research_coordinator',NULL,'active',0,0,NULL,'2026-08-22 15:36:49',NULL,NULL,NULL,NULL,'2026-08-08 18:09:48','2026-08-22 15:36:49'),(54,'rsantos','rsantos@bestlink.edu.ph','$2y$10$5NwrXkqBSCDKiQq6VVOb4OkjgFUJTSCn2UgwZCHeBGqUK/bmLF.JK','Dr. Roberto M. Santos','adviser',NULL,'active',0,0,NULL,'2026-08-22 15:36:50',NULL,NULL,NULL,NULL,'2026-08-08 21:35:14','2026-08-22 15:36:50'),(116,'researchdirector','researchdirector@bestlink.edu.ph','$2y$10$TGAkH/Mt7V5t657M.8ck.uVzXkrrabNEGqxWtS.lOTBITLmxghDVO','Research Director','research_director',NULL,'active',0,0,NULL,'2026-08-22 15:36:49',NULL,NULL,NULL,NULL,'2026-08-09 19:31:14','2026-08-22 15:36:49'),(222,'researchgrant','researchgrant@bestlink.edu.ph','$2y$10$XaHF/b6nQzA3etAU7vHs2eMJCQ9S02FwjtuF1CidXGN9GVul2s9dO','Research Grant','research_grant',NULL,'active',0,0,NULL,'2026-08-22 15:36:49',NULL,NULL,NULL,NULL,'2026-08-10 20:01:49','2026-08-22 15:36:49'),(491,'jobert.valentino','jobert.valentino@bestlink.edu.ph','$2y$10$2rZGxKL1JuxxGW5y9fBMU.4IQmGFOFmHWCgnhHGz1AU6Z/nKPgShm','Dr. Jobert Valentino','panel',NULL,'active',0,0,NULL,'2026-08-22 15:36:50',NULL,NULL,NULL,NULL,'2026-08-15 17:07:16','2026-08-22 15:36:50'),(492,'jonathan.estrada','jonathan.estrada@bestlink.edu.ph','$2y$10$o981mrfQmNeCGvYCq.8bC.Vx5AqBfDG8FGLMkVnefPdJqxVdXLrtC','Dr. Jonathan Estrada','panel',NULL,'active',0,0,NULL,'2026-08-22 15:36:51',NULL,NULL,NULL,NULL,'2026-08-15 17:07:16','2026-08-22 15:36:51'),(493,'michelle.guevarra','michelle.guevarra@bestlink.edu.ph','$2y$10$1YATDeFDu1nWirHtUvjHQOZHrz8HGMFGyCn5DuGKFTlKFK5clIsHu','Dr. Michelle Guevarra','panel',NULL,'active',0,0,NULL,'2026-08-22 15:36:51',NULL,NULL,NULL,NULL,'2026-08-15 17:07:16','2026-08-22 15:36:51'),(729,'grammarian','grammarian@bestlink.edu.ph','$2y$10$2DjZhSKVIreGqNAb.cG1tOcQhp3AOVQKRjj/P4KbuOS72x4eSdyAO','Grammarian','grammarian',NULL,'active',0,0,NULL,'2026-08-22 15:36:50',NULL,NULL,NULL,NULL,'2026-08-18 00:36:15','2026-08-22 15:36:50'),(758,'admin','admin@bestlink.edu.ph','$2y$10$PqA56LN8N/Pq0iey2tqQcehUtPCtu44RQXMkOPhCo92Zm7GaTDOQC','Admin','sms_admin',NULL,'active',0,0,NULL,'2026-08-22 15:36:48',NULL,NULL,NULL,NULL,'2026-08-18 00:38:50','2026-08-22 15:36:48'),(845,'reviewcommitee','reviewcommitee@bestlink.edu.ph','$2y$10$TYGN5VahX3dagHlFmGMQYeDUS3noqZpQVRuQ8yBVWvR4n1cLQMc4y','Review Commitee','review_committee',NULL,'active',0,0,NULL,'2026-08-22 15:36:50',NULL,NULL,NULL,NULL,'2026-08-19 19:23:19','2026-08-22 15:36:50');
-- Keep the classroom-transfer credentials aligned with database/seed_accounts.php.
-- These updates also clear lockouts created while an old/local password was tried.
UPDATE `users` SET `password_hash`='$2y$10$y5OSkfqLxBxxXmYBav8dYuH.jxRDYhOUvL0MKTEoo/9PjcVhy/0R2', `failed_login_attempts`=0, `locked_until`=NULL WHERE `username`='superadmin';
UPDATE `users` SET `password_hash`='$2y$10$dXtL8C0OYaoHvBuWq9SwTeGLtV5C5dBm1cW8sHR.OpyFoliAKj5aW', `failed_login_attempts`=0, `locked_until`=NULL WHERE `username`='admin';
UPDATE `users` SET `password_hash`='$2y$10$SvFhHG9rYfX2mic3MH1/PeP8StHNZfIjBk20NNpXaHAvy2.FhOYfi', `failed_login_attempts`=0, `locked_until`=NULL WHERE `username`='admission';
UPDATE `users` SET `password_hash`='$2y$10$RaL64RvSU3HxomX/1m/UC.cH2WmBuUuRS6ijwxfq3ooOEU7NJnveq', `failed_login_attempts`=0, `locked_until`=NULL WHERE `username`='registrar';
UPDATE `users` SET `password_hash`='$2y$10$jlVYE9OQKegHH67cFXPp2uK2/WjlSDon5zNIBei1ziG8tb0mVYcu.', `failed_login_attempts`=0, `locked_until`=NULL WHERE `username`='grammarian';
UPDATE `users` SET `password_hash`='$2y$10$8Fs/Q0XaBOS3A6uYHGDmFeSLCENDLwpgZ8LY86nyRcNUZrNbRNOsK', `failed_login_attempts`=0, `locked_until`=NULL WHERE `username`='finance';
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'sms2_db'
--

--
-- Dumping routines for database 'sms2_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-22 16:53:16
