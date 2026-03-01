/*
SQLyog Community v13.2.0 (64 bit)
MySQL - 12.0.2-MariaDB : Database - minor_project
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`minor_project` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci */;

USE `minor_project`;

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `categories` */

insert  into `categories`(`id`,`name`,`created_at`) values 
(1,Electronics,2025-12-26 09:00:15),
(2,Furniture,2025-12-26 09:00:15),
(3,Stationery,2025-12-26 09:00:15),
(4,Sports Equipment,2025-12-26 09:00:15),
(5,Lab Equipment,2025-12-26 09:00:15),
(6,Books,2025-12-26 09:00:15),
(7,Other,2025-12-26 09:00:15);

/*Table structure for table `contact` */

DROP TABLE IF EXISTS `contact`;

CREATE TABLE `contact` (
  `id` int(10) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `subject` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `message` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `contact` */

insert  into `contact`(`id`,`name`,`email`,`subject`,`message`,`created_at`) values 
(0,Devbarat Prasad Patel,mind59024@gmail.com,'Improvement ','I want to see products listed on this platform ',2025-12-21 12:15:30),
(1003,Toon,manupatel@gmail.com,'improvement','viusavbiubv',2025-12-21 12:15:30);

/*Table structure for table `departments` */

DROP TABLE IF EXISTS `departments`;

CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `departments` */

insert  into `departments`(`id`,`name`,`created_at`) values 
(1,Computer Engineering,2025-12-26 09:00:15),
(2,Civil Engineering,2025-12-26 09:00:15),
(3,Electrical Engineering,2025-12-26 09:00:15),
(4,Electronics Engineering,2025-12-26 09:00:15),
(5,Architecture,2025-12-26 09:00:15),
(6,Administration,2025-12-26 09:00:15),
(7,Library,2025-12-26 09:00:15),
(8,Laboratory,2025-12-26 09:00:15);

/*Table structure for table `issued_items_dets` */

DROP TABLE IF EXISTS `issued_items_dets`;

CREATE TABLE `issued_items_dets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `issue_id` bigint(20) NOT NULL,
  `issue_date` date NOT NULL,
  `issued_to` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL,
  `faculty` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL,
  `year_part` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL,
  `purpose` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL,
  `item_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `specification` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `remarks` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `issue_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT 'issued',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `Instructor_Name` varchar(265) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `issued_items_dets` */

insert  into `issued_items_dets`(`id`,`user_id`,`issue_id`,`issue_date`,`issued_to`,`faculty`,`year_part`,`purpose`,`item_name`,`quantity`,`specification`,`remarks`,`issue_status`,`created_at`,`Instructor_Name`) values 
(12,1008,1767277171,2026-01-01,Manu Patel,DCOM,I/II,On Remand,T-scale ,5,Agni ,dd,In_approval,2026-01-01 20:04:31,NULL),
(13,1008,1767277295,2026-01-01,Manu Patel,DCOM,I/II,On Remand,T-scale ,5,Agni ,dd,In_approval,2026-01-01 20:06:35,NULL),
(14,1008,1767277562,2026-01-01,Manu Patel,DCOM,I/II,On Remand,T-scale ,5,Agni ,dd,In_approval,2026-01-01 20:11:02,NULL),
(15,1003,1767278614,2026-01-01,Toon,DCOM,III/I,For Pratical,M416,5,ki,fod,In_approval,2026-01-01 20:28:34,NULL),
(22,1008,1767413451,2026-01-03,Manu Patel,DCOM,I/II,On Remand,T-scale ,5,Agni ,dd,Approved,2026-01-03 09:55:51,NULL),
(23,1000,1767501532,2026-01-04,Devbarat Prasad Patel,DCOM,III/I,Use In mechanical Lab,Screw Driver,1,10mm,for 1 days ,Approved,2026-01-04 10:23:52,NULL),
(24,1000,1767501532,2026-01-04,Devbarat Prasad Patel,DCOM,III/I,Use In mechanical Lab,Mouse ,1,Dell,for 2 days,Approved,2026-01-04 10:23:52,NULL),
(25,1011,1769230061,2026-01-24,Devbarat Prasad Patel,DEEx,II/II,Use In mechanical Lab,T-scale ,55,10mm,For use in the lab,In_approval,2026-01-24 10:32:41,NULL);

/*Table structure for table `notifications` */

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `type` varchar(50) DEFAULT 'general',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `notifications` */

insert  into `notifications`(`id`,`user_id`,`message`,`type`,`link`,`is_read`,`created_at`) values 
(1,1008,New Rent Request from ,rent_request,rent_requests.php,1,2026-01-01 19:14:11),
(2,1008,New Rent Request from Manu Patel,rent_request,rent_requests.php,1,2026-01-01 19:52:13),
(3,1008,New Rent Request from Manu Patel (Issue #1767276748),rent_request,view_request.php?id=9,1,2026-01-01 19:57:28),
(4,1008,New Rent Request from Manu Patel (Issue #1767276842),rent_request,view_request.php?id=10,1,2026-01-01 19:59:02),
(5,1008,New Rent Request from Manu Patel (Issue #1767277171),rent_request,view_request.php?id=12,0,2026-01-01 20:04:31),
(6,1008,New Rent Request from Manu Patel (Issue #1767277295),rent_request,view_request.php?id=13,1,2026-01-01 20:06:35),
(7,1008,New Rent Request from Manu Patel (Issue #1767277562),rent_request,view_request.php?id=14,1,2026-01-01 20:11:02),
(8,1003,New Rent Request from Toon (Issue #1767278614),rent_request,view_request.php?id=15,1,2026-01-01 20:28:34),
(9,1003,New Rent Request from Toon (Issue #1767278943),rent_request,view_request.php?id=16,0,2026-01-01 20:34:03),
(10,1003,New Rent Request from Toon (Issue #1767279089),rent_request,view_request.php?id=18,0,2026-01-01 20:36:29),
(11,1003,New Rent Request from Toon (Issue #1767279367),rent_request,view_request.php?id=20,1,2026-01-01 20:41:07),
(12,1008,New Rent Request from Manu Patel (Issue #1767413451),rent_request,view_request.php?id=22,1,2026-01-03 09:55:51),
(13,1000,New Rent Request from Devbarat Prasad Patel (Issue #1767501532),rent_request,view_request.php?id=23,1,2026-01-04 10:23:52),
(14,1011,New Rent Request from Devbarat Prasad Patel (Issue #1769230061),rent_request,view_request.php?id=25,0,2026-01-24 10:32:41);

/*Table structure for table `products` */

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `related_department` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `product_image` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `products` */

insert  into `products`(`product_id`,`product_name`,`category`,`quantity`,`related_department`,`product_image`,`created_at`) values 
(24,T-scale ,staisnory,5,General,24.jpg,2025-12-20 14:34:13),
(25,T-scale ,staisnory,5,Defense,25.png,2025-12-22 11:55:41);

/*Table structure for table `purchases` */

DROP TABLE IF EXISTS `purchases`;

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` enum('pending','completed') DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `purchases` */

insert  into `purchases`(`id`,`product_name`,`category`,`department`,`quantity`,`price`,`total`,`supplier`,`notes`,`date`,`status`,`created_at`) values 
(1,Belnut,Lab Equipment,Electronics Engineering,1,250.00,250.00,,,2025-12-27,completed,2025-12-27 14:13:26),
(2,T-scale,Stationery,Administration,1,100.00,100.00,Shree Hamro Pustak Bhandar,for testing purpose,2026-01-04,completed,2026-01-04 10:29:48);

/*Table structure for table `return_items` */

DROP TABLE IF EXISTS `return_items`;

CREATE TABLE `return_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_id` int(11) DEFAULT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `condition_note` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `return_id` (`return_id`),
  CONSTRAINT `return_items_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `return_items` */

/*Table structure for table `return_requests` */

DROP TABLE IF EXISTS `return_requests`;

CREATE TABLE `return_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `issue_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `return_requests` */

/*Table structure for table `returns` */

DROP TABLE IF EXISTS `returns`;

CREATE TABLE `returns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `issue_id` int(11) DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `returned_by` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `returns` */

/*Table structure for table `system_settings` */

DROP TABLE IF EXISTS `system_settings`;

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `institute_name` varchar(255) DEFAULT 'Birgunj Institute of Technology',
  `store_name` varchar(255) DEFAULT 'StoreMart',
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `system_settings` */

/*Table structure for table `task` */

DROP TABLE IF EXISTS `task`;

CREATE TABLE `task` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` varbinary(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `task` */

insert  into `task`(`id`,`title`,`description`,`date`) values 
(2,Home,Complete the homework any how ,2025-12-01);

/*Table structure for table `transactions` */

DROP TABLE IF EXISTS `transactions`;

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `issue_qty` int(11) NOT NULL,
  `transaction_date` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `transactions` */

/*Table structure for table `user` */

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `faculty` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `year_part` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `contact` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `resettoken` varchar(255) DEFAULT NULL,
  `resettokenexpire` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1012 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `user` */

insert  into `user`(`id`,`name`,`address`,`email`,`faculty`,`year_part`,`contact`,`password`,`role`,`resettoken`,`resettokenexpire`) values 
(1000,Devbarat Prasad Patel,Bahudramai-07, Phulkaul, Parsa,mind59024@gmail.com,DCOM,III/I,9811144402,$2y$12$C4Sxl0XRQ0cJBg/dgwL2DOcOM25ok3EJRGo80UJOdzKBak.F4K9Uq,Admin,177c29142a7154857fbd6b1f21f66553,2026-01-24),
(1001,Lilapati Kumari Sah ,Bahudramai-07, Phulkaul, Parsa,lilapati@gmail.com,Civil,III/II,9811144402,123456,User,NULL,NULL),
(1003,Toon,Birgunj ,toonmitra355@gmail.com,DCOM,III/I,9800001,123456,user,NULL,NULL),
(1004,Ram kumar Sah,Bahudramai-07, Phulkaul, Parsa,r@gmail.com,DEEx,III/II,9811144402,123456,user,NULL,NULL),
(1006,akriti,address,a@gmail.com,computer,III/II,9825116556,123456,Admin,NULL,NULL),
(1007,Laukesh Jaiswal Sir,Bahudramai-07, Phulkaul, Parsa,l@gmail.com,Computer,III/II,9811144402,12345678,user,NULL,NULL),
(1008,Manu Patel,Bahudramai_7,phulkaul,101@gmail.com,DCOM,I/II,9816247609,12345678,user,NULL,NULL),
(1009,Nepal Coding School,Bahudramai_7,phulkaul,nepalcodingschool@gmail.com,DCOM,III/I,9825035012,12345678,user,NULL,NULL),
(1010,Test User,Test Address,test@example.com,DCOM,I/I,9812345678,$2y$12$i2MB8OnmsPImJuEiD8CkFuqY/P1XmL1sYujbFhhRb7R7A.FMk4fXe,user,NULL,NULL),
(1011,Devbarat Prasad Patel,Bahudramai-07, Phulkaul, Parsa,GoluG@gmail.com,DEEx,II/II,9811144402,$2y$12$o.xrQ/GrwFxZ6mMZN0ylROwKq3FBr7u082tQLAPbiGebI/LoRKRGi,user,NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
