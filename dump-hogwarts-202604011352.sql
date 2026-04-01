-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: mysql-8.0    Database: hogwarts
-- ------------------------------------------------------
-- Server version	8.0.43

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
-- Table structure for table `mastery`
--

DROP TABLE IF EXISTS `mastery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mastery` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int unsigned NOT NULL,
  `spell_id` int unsigned NOT NULL,
  `mastered_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`,`spell_id`),
  KEY `fk_mastery_spell` (`spell_id`),
  CONSTRAINT `fk_mastery_spell` FOREIGN KEY (`spell_id`) REFERENCES `spell` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mastery_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mastery`
--

LOCK TABLES `mastery` WRITE;
/*!40000 ALTER TABLE `mastery` DISABLE KEYS */;
INSERT INTO `mastery` VALUES (3,2,1,'1994-09-20'),(4,2,2,'1994-09-22'),(5,2,3,'1995-12-10'),(6,3,1,'1995-11-05'),(9,3,2,NULL),(10,8,1,NULL),(11,8,2,NULL);
/*!40000 ALTER TABLE `mastery` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `trg_after_insert_mastery` AFTER INSERT ON `mastery` FOR EACH ROW BEGIN
    UPDATE student
    SET spell_count = spell_count + 1
    WHERE id = NEW.student_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `spell`
--

DROP TABLE IF EXISTS `spell`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spell` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spell`
--

LOCK TABLES `spell` WRITE;
/*!40000 ALTER TABLE `spell` DISABLE KEYS */;
INSERT INTO `spell` VALUES (7,'Алохомора'),(6,'Вингардиум Левиоса'),(10,'Круцио'),(2,'Люмос'),(8,'Нокс'),(3,'Экспекто Патронум'),(1,'Экспеллиармус');
/*!40000 ALTER TABLE `spell` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `surname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `house` enum('Гриффиндор','Слизерин','Когтевран','Пуффендуй') COLLATE utf8mb4_unicode_ci NOT NULL,
  `course` tinyint unsigned DEFAULT NULL,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `spell_count` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_student_course` (`course`),
  CONSTRAINT `student_chk_1` CHECK ((`course` between 1 and 7))
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student`
--

LOCK TABLES `student` WRITE;
/*!40000 ALTER TABLE `student` DISABLE KEYS */;
INSERT INTO `student` VALUES (2,'Гермиона','Грейнджер','Гриффиндор',7,1,3),(3,'Рон','Уизли','Гриффиндор',7,0,1),(4,'Драко','Малфой','Слизерин',7,0,0),(6,'Полумна','Лавгуд','Когтевран',7,0,0),(7,'Гарри','Поттер','Гриффиндор',7,0,0),(8,'Джинни','Уизли','Гриффиндор',7,0,2),(9,'Чжоу','Чанг','Когтевран',7,0,0),(10,'фвафа','фыафыа','Гриффиндор',3,0,3);
/*!40000 ALTER TABLE `student` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-01 13:52:14
