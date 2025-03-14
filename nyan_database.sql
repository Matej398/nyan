-- Nyan Game Database Schema and Data
-- This file contains the structure and data for the leaderboard table

-- Drop table if it exists
DROP TABLE IF EXISTS `leaderboard`;

-- Create the leaderboard table
CREATE TABLE `leaderboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(15) NOT NULL,
  `score` int(11) NOT NULL,
  `timestamp` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO `leaderboard` VALUES
(1,'matej',1,'2025-03-04 18:38:22'),
(2,'matej',6,'2025-03-04 18:38:44'),
(3,'doris',6,'2025-03-04 18:39:38'),
(4,'matej',16,'2025-03-04 18:40:28'),
(5,'matej',20,'2025-03-04 18:45:00'),
(6,'MATEJ',19,'2025-03-04 18:45:59'),
(7,'Nyanonymous',10,'2025-03-04 18:48:38'),
(8,'Nyanonymous',0,'2025-03-04 18:49:49'),
(9,'MATEJ',42,'2025-03-04 18:51:11'),
(10,'MATEJ',109,'2025-03-04 18:54:58'),
(11,'Nyanonymous',0,'2025-03-04 19:58:31'),
(12,'Nyanonymous',0,'2025-03-04 21:18:15'),
(13,'Nyanonymous',0,'2025-03-04 21:18:22'),
(14,'Nyanonymous',3,'2025-03-04 21:18:34'),
(15,'Doris',10,'2025-03-04 21:19:17'),
(16,'Doris',4,'2025-03-04 22:28:40'),
(17,'Nyanonymous',0,'2025-03-04 22:29:03'),
(18,'Nyanonymous',6,'2025-03-04 22:29:23'),
(19,'DORIS',31,'2025-03-04 22:57:30'),
(20,'Nyanonymous',0,'2025-03-05 09:54:52'),
(21,'Nyanonymous',7,'2025-03-05 11:56:27'),
(22,'Nyanonymous',15,'2025-03-05 11:57:01'),
(23,'Nyanonymous',3,'2025-03-05 11:57:15'),
(24,'Nyanonymous',12,'2025-03-05 11:57:42'),
(25,'Nyanonymous',8,'2025-03-05 11:58:03'),
(26,'Nyanonymous',5,'2025-03-05 11:58:17'),
(27,'Nyanonymous',21,'2025-03-05 11:58:59'); 