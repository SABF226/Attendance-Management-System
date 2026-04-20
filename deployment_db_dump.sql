SET FOREIGN_KEY_CHECKS=0;
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: english_club
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

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
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `field` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` VALUES (1,'Abdoul Ben F. SANON','CS 27','+226 06262545','sabfsanon@gmail.com','2026-03-07 13:20:51','2026-04-16 22:20:20'),(2,'Sephora Kabore','ME 28','+226 06 06 05 34','serakbr226@gmail.com','2026-03-07 13:33:04','2026-03-07 13:33:04'),(3,'Andrea Kindo','CS28','+226 06063534','kianda@gmail.com','2026-03-07 13:37:07','2026-04-16 19:06:54'),(4,'Francine Marie Jeseph Yameogo','cs27','+226 06 04 05 34','francine@gmail.com','2026-03-07 14:02:04','2026-03-07 14:02:04'),(29,'KABORE Wendpende Sephora','ME 28','07160026',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(30,'BAZIE Grace Pelagie','CS 28','66864582',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(31,'KOURAOGO Oceane','CS 28','53107624',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(32,'ONADJA P. Nouna Fanta','ME 28','54321478',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(33,'YAMEOGO B. A. Laetitia','EE 28','78958230',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(34,'BADOLO Hilaire Lenaic','EE 28','71960682',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(35,'ZAGHRE Fusita','EE 27','66997798',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(36,'BELEM Salimata','EE 27','64178029',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(38,'NABI Nader','EE 28','06816536',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(39,'KONDOMBO Abdoul','ME 27','75333804',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(40,'MATGIA Nassuema','ME 27','05448562',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(41,'BAMA Y. Amical','CS 26','56277862',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(42,'TAHIRI E. Gimbria','YE (28)','76390864',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(43,'ZONGO Elodie','EE 28','65859299',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(44,'KABRE Hafsa','ME 26','79386837',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(45,'YAMEOGO S. Camille','ME 26','51545456',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(46,'KABORE Richard','ME 26','66496366',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(47,'SAWADOGO P. Jacqueline','EE 28','64864413',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(48,'KINDA Anne P.','EE 28','01626096',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(49,'WANGRAWA W. Simone','ME 28','03463832',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(50,'COMPAORE Maimouna','ME 28','54223010',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(51,'BAKYONO B. Michel','ME 27','77492477',NULL,'2026-03-07 15:43:53','2026-03-07 15:43:53'),(53,'Nana Marc','cs27','+226 55851935','nanamarc5547@gmail.com','2026-03-07 16:30:05','2026-03-07 16:30:05'),(54,'Zongo Adèle ','Me 27','72223541','zongoadele069@gmail.com','2026-03-08 01:26:39','2026-04-16 19:31:53'),(55,'some augustine','CS28','65382953','someaugustine@gmail.com','2026-04-16 19:12:49','2026-04-16 19:12:49'),(56,'OUEDRAOGO LATIFATOU','CS28','73440070','latifa@bit.bf','2026-04-16 19:15:26','2026-04-16 19:15:26'),(57,'SORGHO aibou','ME 28','77349873','ec@bit.bf','2026-04-16 19:19:54','2026-04-16 19:19:54'),(58,'SALGHO ALIDA','CS28','06789640','ec1@bit.bf','2026-04-16 19:49:46','2026-04-16 19:49:46'),(59,'SORGHO Aibou','ME 28','77349873','ec2@bit.bf','2026-04-16 19:50:43','2026-04-16 19:50:43'),(60,'NAON LYDIE','CS28','74423113','1ec@bit.bf','2026-04-16 19:51:21','2026-04-16 19:51:21'),(61,'WANGRAWA VIVIANE','ME 28','03463832','2ec@bit.bf','2026-04-16 19:52:12','2026-04-16 19:52:12'),(62,'GUEBRE DJAMINATOU','EE 28','73634856','3ec@bit.bf','2026-04-16 19:52:47','2026-04-16 19:52:47'),(63,'ZONGO DJAMILATOU','CS28','64614381','ec5@bit.bf','2026-04-16 19:53:27','2026-04-16 19:53:27'),(64,'MARE ROKIA','EE28','57797539','ec6@bit.bf','2026-04-16 19:53:59','2026-04-16 19:53:59'),(65,'COMPAORE MAIMOUNA','ME 28','54220310','ec7@bit.bf','2026-04-16 19:54:48','2026-04-16 19:54:48'),(66,'ZAGRE FAOUSIATOU','ME28','05941206','ec8@bit.bf','2026-04-16 19:55:26','2026-04-16 19:55:26'),(67,'Kiendrebeogo Arnaud','cs27','07635860','5ec@bit.bf','2026-04-16 19:57:23','2026-04-16 19:57:23'),(68,'TAO OUZAI','Me 27','56233085','6ec@bit.bf','2026-04-16 19:58:12','2026-04-16 19:58:12'),(69,'MAIGA NASSOUMA','ME 28','05448567','7ec@bit.bf','2026-04-16 19:59:04','2026-04-16 19:59:04'),(70,'KOUDOUGOU AMINATA','Me 27','56468837','8ec@bit.bf','2026-04-16 19:59:38','2026-04-16 19:59:38'),(71,'TINDANO TIANSIEBA','EE28','05779246','tec@bit.bf','2026-04-16 20:00:18','2026-04-16 20:00:18'),(72,'Test Member','Computer Science','123456789','test@example.com','2026-04-16 22:57:20','2026-04-16 22:57:20');
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-17  0:55:09
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: english_club
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

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
-- Table structure for table `attendance_sessions`
--

DROP TABLE IF EXISTS `attendance_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_date` date NOT NULL,
  `session_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `session_time` time DEFAULT NULL,
  `session_team` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_sessions`
--

LOCK TABLES `attendance_sessions` WRITE;
/*!40000 ALTER TABLE `attendance_sessions` DISABLE KEYS */;
INSERT INTO `attendance_sessions` VALUES (1,'2026-03-07','EngClub','2026-03-07 13:29:35',NULL,NULL),(2,'2026-03-05','English Club Session 2 of March','2026-03-07 16:00:57',NULL,NULL),(3,'2026-03-02','English Club 1st Session of March','2026-03-07 16:31:50',NULL,NULL),(4,'2026-04-16','BIT English Club - Mandora Session 2 of April ','2026-04-16 19:03:52',NULL,NULL),(6,'2026-04-16','Mandora Team A  1st Session of April ','2026-04-16 22:12:51','18:15:00','Mandora Team A of April '),(7,'2026-04-16','Test Session','2026-04-16 22:52:02','14:00:00','Team Alpha');
/*!40000 ALTER TABLE `attendance_sessions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-17  0:55:09
-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: english_club
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

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
-- Table structure for table `attendance_records`
--

DROP TABLE IF EXISTS `attendance_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `member_id` int NOT NULL,
  `status` enum('present','absent','excused') DEFAULT 'present',
  `notes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_session_member` (`session_id`,`member_id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_records_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=227 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_records`
--

LOCK TABLES `attendance_records` WRITE;
/*!40000 ALTER TABLE `attendance_records` DISABLE KEYS */;
INSERT INTO `attendance_records` VALUES (1,1,1,'excused','Chairman'),(2,1,2,'absent','Communicator'),(3,1,3,'present','General Secretay'),(4,1,4,'present','Communicator'),(5,2,3,'present','General Secretay'),(6,2,34,'present','TeamA1'),(7,2,51,'present',''),(8,2,41,'present','TeamA2'),(9,2,30,'present',''),(10,2,36,'present','TeamC2'),(11,2,50,'present',''),(12,2,4,'present','Communicator'),(13,2,46,'present',''),(14,2,29,'present',''),(15,2,44,'present',''),(16,2,48,'present',''),(17,2,39,'present',''),(18,2,31,'present',''),(19,2,40,'present',''),(20,2,38,'present',''),(21,2,32,'present',''),(22,2,1,'present','Executive Chairman'),(24,2,47,'present',''),(25,2,2,'present',''),(26,2,42,'present',''),(27,2,49,'present',''),(28,2,33,'present',''),(30,2,45,'present',''),(31,2,35,'present',''),(32,2,43,'present',''),(33,3,3,'present',''),(34,3,34,'present',''),(35,3,51,'present',''),(36,3,41,'present',''),(37,3,30,'present',''),(38,3,36,'present',''),(39,3,50,'present',''),(40,3,4,'present',''),(41,3,46,'present',''),(42,3,29,'present',''),(43,3,44,'present','Team A1'),(44,3,48,'present',''),(45,3,39,'present',''),(46,3,31,'present',''),(47,3,40,'present',''),(48,3,38,'present',''),(49,3,53,'present',''),(50,3,32,'present',''),(51,3,1,'present',''),(53,3,47,'present',''),(54,3,2,'present',''),(55,3,42,'present',''),(56,3,49,'present',''),(57,3,33,'present',''),(59,3,45,'excused',''),(60,3,35,'absent',''),(61,3,43,'present',''),(62,1,34,'present',''),(63,1,51,'present',''),(64,1,41,'present',''),(65,1,30,'present',''),(66,1,36,'present',''),(67,1,50,'present',''),(68,1,46,'present',''),(69,1,29,'present',''),(70,1,44,'present',''),(71,1,48,'present',''),(72,1,39,'present',''),(73,1,31,'present',''),(74,1,40,'present',''),(75,1,38,'present',''),(76,1,53,'present',''),(77,1,32,'present',''),(79,1,47,'present',''),(80,1,42,'present',''),(81,1,49,'present',''),(82,1,33,'present',''),(84,1,45,'present',''),(85,1,35,'present',''),(86,1,54,'present',''),(87,1,43,'present',''),(88,4,3,'present','General Secretay'),(89,4,34,'absent',''),(90,4,51,'absent',''),(91,4,41,'absent',''),(92,4,30,'present',''),(93,4,36,'present',''),(94,4,50,'present',''),(95,4,65,'present',''),(96,4,4,'present',''),(97,4,62,'present',''),(98,4,46,'absent',''),(99,4,29,'present',''),(100,4,44,'present',''),(101,4,67,'present',''),(102,4,48,'absent',''),(103,4,39,'absent',''),(104,4,70,'present',''),(105,4,31,'present',''),(106,4,69,'present',''),(107,4,64,'present',''),(108,4,40,'excused',''),(109,4,38,'absent',''),(110,4,53,'present',''),(111,4,60,'present',''),(112,4,32,'absent',''),(113,4,56,'present',''),(114,4,1,'present',''),(115,4,58,'present',''),(117,4,47,'absent',''),(118,4,2,'absent',''),(119,4,55,'present',''),(120,4,57,'present',''),(121,4,59,'present',''),(122,4,42,'absent',''),(123,4,68,'present',''),(124,4,71,'present',''),(125,4,61,'present',''),(126,4,49,'excused',''),(127,4,33,'present',''),(129,4,45,'absent',''),(130,4,35,'excused',''),(131,4,66,'present',''),(132,4,54,'present',''),(133,4,63,'present',''),(134,4,43,'absent',''),(135,6,3,'present',''),(136,6,34,'present',''),(137,6,51,'present',''),(138,6,41,'present',''),(139,6,30,'present',''),(140,6,36,'present',''),(141,6,50,'present',''),(142,6,65,'present',''),(143,6,4,'present',''),(144,6,62,'present',''),(145,6,46,'present',''),(146,6,29,'excused',''),(147,6,44,'excused',''),(148,6,67,'absent',''),(149,6,48,'present',''),(150,6,39,'present',''),(151,6,70,'absent',''),(152,6,31,'present',''),(153,6,69,'present',''),(154,6,64,'present',''),(155,6,40,'present',''),(156,6,38,'present',''),(157,6,53,'present',''),(158,6,60,'present',''),(159,6,32,'present',''),(160,6,56,'present',''),(161,6,1,'excused','Executive Chairman'),(162,6,58,'present',''),(164,6,47,'present',''),(165,6,2,'present','Communicator Officer'),(166,6,55,'present',''),(167,6,57,'excused',''),(168,6,59,'present',''),(169,6,42,'present',''),(170,6,68,'present',''),(171,6,71,'present',''),(172,6,61,'present',''),(173,6,49,'present',''),(174,6,33,'present',''),(176,6,45,'present',''),(177,6,35,'present',''),(178,6,66,'present',''),(179,6,54,'present',''),(180,6,63,'present',''),(181,6,43,'present',''),(182,7,1,'present',''),(183,7,3,'present',''),(184,7,34,'absent',''),(185,7,51,'present',''),(186,7,41,'present',''),(187,7,30,'present',''),(188,7,36,'present',''),(189,7,50,'present',''),(190,7,65,'present',''),(191,7,4,'present',''),(192,7,62,'present',''),(193,7,46,'present',''),(194,7,29,'present',''),(195,7,44,'present',''),(196,7,67,'present',''),(197,7,48,'present',''),(198,7,39,'present',''),(199,7,70,'present',''),(200,7,31,'present',''),(201,7,69,'present',''),(202,7,64,'present',''),(203,7,40,'present',''),(204,7,38,'present',''),(205,7,53,'present',''),(206,7,60,'present',''),(207,7,32,'present',''),(208,7,56,'present',''),(209,7,58,'present',''),(210,7,47,'present',''),(211,7,2,'present',''),(212,7,55,'present',''),(213,7,57,'present',''),(214,7,59,'present',''),(215,7,42,'present',''),(216,7,68,'present',''),(217,7,71,'present',''),(218,7,61,'present',''),(219,7,49,'present',''),(220,7,33,'present',''),(221,7,45,'present',''),(222,7,35,'present',''),(223,7,66,'present',''),(224,7,54,'present',''),(225,7,63,'present',''),(226,7,43,'present','');
/*!40000 ALTER TABLE `attendance_records` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-17  0:55:09
SET FOREIGN_KEY_CHECKS=1;
