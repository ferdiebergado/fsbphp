CREATE DATABASE IF NOT EXISTS fsb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fsb;
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `active` boolean default true,
  `role` int(2) default 9,
  `last_login` timestamp,
  `ipv4` varchar(16),
  `ipv6` varchar(32),
  `user_agent` varchar(191),
  `created_at` timestamp default current_timestamp,
  `updated_at` timestamp default current_timestamp ON UPDATE current_timestamp,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`email`)
) AUTO_INCREMENT=1;
