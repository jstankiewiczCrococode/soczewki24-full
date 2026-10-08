-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: prestashop
-- ------------------------------------------------------
-- Server version	8.0.46

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
-- Dumping data for table `31y6a_brandslider`
--

LOCK TABLES `31y6a_brandslider` WRITE;
/*!40000 ALTER TABLE `31y6a_brandslider` DISABLE KEYS */;
INSERT INTO `31y6a_brandslider` VALUES (1,2,0,1,'2026-09-17 17:22:52','2026-09-17 17:30:26'),(2,1,0,1,'2026-09-17 17:22:52','2026-09-17 17:30:32'),(3,7,0,0,'2026-09-18 13:18:24','2026-09-18 13:18:24'),(4,8,0,0,'2026-09-18 13:18:24','2026-09-18 13:18:24'),(5,6,0,0,'2026-09-18 13:18:24','2026-09-18 13:18:24'),(6,4,0,0,'2026-09-18 13:18:24','2026-09-18 13:18:24'),(7,5,0,0,'2026-09-18 13:18:24','2026-09-18 13:18:24'),(8,3,0,0,'2026-09-18 13:18:24','2026-09-18 13:18:24');
/*!40000 ALTER TABLE `31y6a_brandslider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `31y6a_homeitems`
--

LOCK TABLES `31y6a_homeitems` WRITE;
/*!40000 ALTER TABLE `31y6a_homeitems` DISABLE KEYS */;
INSERT INTO `31y6a_homeitems` VALUES (1,'#','03a4c04fbd83c574f246f4ec93210805.jpg','Soczewki kontaktowe',0,1,'2026-09-17 15:09:01','2026-09-17 15:33:24'),(2,'#','6b2a1796f6b18721b99343298ec30e32.png','Korekcja męska',0,1,'2026-09-17 15:09:58','2026-09-17 15:34:28'),(3,'#','d370ed75085e847ace6c85fdaa7da528.png','Korekcja damska',0,1,'2026-09-17 15:10:21','2026-09-17 15:34:39'),(4,'#','71c23b60e107f184e04fbc593efabbf0.png','Badanie wzroku',0,1,'2026-09-17 15:12:18','2026-09-17 15:34:48');
/*!40000 ALTER TABLE `31y6a_homeitems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `31y6a_s24_category_page`
--

LOCK TABLES `31y6a_s24_category_page` WRITE;
/*!40000 ALTER TABLE `31y6a_s24_category_page` DISABLE KEYS */;
INSERT INTO `31y6a_s24_category_page` VALUES (1,3,1,'s24categorypage/category-3-1-1790598636.jpg','Okulary przeciwsłoneczne','Okulary przeciwsłoneczne służą do ochrony oczu przed słońcem, ograniczenia olśnienia i poprawy komfortu widzenia na zewnątrz. Przydają się podczas jazdy, spacerów, odpoczynku nad wodą, aktywności w mieście i wakacyjnych wyjazdów. Najważniejsze jest to, żeby okulary realnie chroniły przed UVA i UVB, a nie tylko przyciemniały obraz. Dobry wybór zależy też od dopasowania oprawy, warunków użycia oraz ewentualnej wady wzroku.','Marki okularów przeciwsłonecznych','Okulary przeciwsłoneczne','Sklep internetowy Soczewki24.pl to miejsce, gdzie znajdziesz produkty dedykowane osobom, które mają różnorodne wady wzroku: krótkowzroczność, dalekowzroczność, astygmatyzm. Niezależnie od tego, jakie są Twoje preferencje – w naszym asortymencie dostępne są okulary korekcyjne, sprawdzone szkła kontaktowe, a także wiele innych produktów stworzonych do ich pielęgnacji. Wszystko po to, abyś mógł wybrać rozwiązanie dopasowane do swoich potrzeb. Każdy z proponowanych przez nas wariantów soczewek kontaktowych czy okularów koryguje wadę wzroku, zapewnia wyraźne widzenie oraz komfort przez cały dzień.');
/*!40000 ALTER TABLE `31y6a_s24_category_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `31y6a_s24footer_column`
--

LOCK TABLES `31y6a_s24footer_column` WRITE;
/*!40000 ALTER TABLE `31y6a_s24footer_column` DISABLE KEYS */;
INSERT INTO `31y6a_s24footer_column` VALUES (1,'Soczewki kontaktowe',1,1),(2,'Okulary przeciwsłoneczne',2,1),(3,'Okulary korekcyjne',3,1),(4,'Kontakt',4,1),(5,'Informacje',5,1);
/*!40000 ALTER TABLE `31y6a_s24footer_column` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `31y6a_s24footer_item`
--

LOCK TABLES `31y6a_s24footer_item` WRITE;
/*!40000 ALTER TABLE `31y6a_s24footer_item` DISABLE KEYS */;
INSERT INTO `31y6a_s24footer_item` VALUES (1,1,'Soczewki kolorowe','','link',1,1),(2,1,'Soczewki toryczne','','link',2,1),(3,1,'Soczewki sferyczne','','link',3,1),(4,1,'Soczewki progresywne','','link',4,1),(5,2,'Okulary przeciwsłoneczne damskie','','link',1,1),(6,2,'Okulary przeciwsłoneczne męskie','','link',2,1),(7,2,'Okulary przeciwsłoneczne unisex','','link',3,1),(8,2,'Nakładki przeciwsłoneczne','','link',4,1),(9,2,'Etui na okulary','','link',5,1),(10,3,'Okulary korekcyjne damskie','','link',1,1),(11,3,'Okulary korekcyjne męskie','','link',2,1),(12,3,'Okulary korekcyjne unisex','','link',3,1),(13,3,'Nakładki przeciwsłoneczne','','link',4,1),(14,3,'Etui na okulary','','link',5,1),(15,5,'O nas','','link',1,1),(16,5,'Blog','','link',2,1),(17,5,'Kariera','','link',3,1),(18,5,'Aplikacja','','link',4,1),(19,5,'Pakiet serwisowy','','link',5,1),(20,5,'Okulary w godzinę','','link',6,1),(21,5,'FAQ','','link',7,1),(25,4,'Formularz kontaktowy','#','link',1,1),(28,4,'+48 71 757 53 88','+48 71 757 53 88','tel',2,1),(31,4,'sklep@soczewki24.pl','sklep@soczewki24.pl','email',3,1),(32,4,'Salony','Salony','link',4,1);
/*!40000 ALTER TABLE `31y6a_s24footer_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `31y6a_s24footer_social`
--

LOCK TABLES `31y6a_s24footer_social` WRITE;
/*!40000 ALTER TABLE `31y6a_s24footer_social` DISABLE KEYS */;
INSERT INTO `31y6a_s24footer_social` VALUES (1,'facebook','https://www.facebook.com/soczewki24',1,1,'/modules/s24footer/views/img/social/social_6aad673c000b85.92944700.png'),(2,'instagram','https://www.instagram.com/soczewki24.pl',2,1,'/modules/s24footer/views/img/social/social_6aad6a87b1ea55.21578011.png'),(3,'x','https://x.com/?lang=pl',3,1,'/modules/s24footer/views/img/social/social_6aad6aabc588d6.37428077.png'),(4,'linkedin','https://www.linkedin.com/',4,1,'/modules/s24footer/views/img/social/social_6aad6ad236aef8.96808909.png'),(5,'youtube','https://www.youtube.com/channel/UCx5ahHc7F1EUUmphbH4GE7Q',5,1,'/modules/s24footer/views/img/social/social_6aad6af11010b6.38682834.png');
/*!40000 ALTER TABLE `31y6a_s24footer_social` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-01 15:40:28
