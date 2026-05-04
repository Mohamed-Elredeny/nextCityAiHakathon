-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2026 at 12:25 AM
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
-- Database: `hackathon_dashboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('acfdd18ea7f4a2ba74132ba977dc207204142994', 'i:1;', 1777825686),
('acfdd18ea7f4a2ba74132ba977dc207204142994:timer', 'i:1777825686;', 1777825686),
('community-comment:209', 'i:1;', 1777828639),
('community-comment:209:timer', 'i:1777828639;', 1777828639),
('community-comment:325', 'i:2;', 1777832854),
('community-comment:325:timer', 'i:1777832854;', 1777832854),
('community-post:325', 'i:3;', 1777828571),
('community-post:325:timer', 'i:1777828571;', 1777828571),
('community-post:333', 'i:1;', 1777832823),
('community-post:333:timer', 'i:1777832823;', 1777832823),
('spatie.permission.cache', 'a:3:{s:5:\"alias\";a:0:{}s:11:\"permissions\";a:0:{}s:5:\"roles\";a:0:{}}', 1778019041);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `community_comments`
--

CREATE TABLE `community_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `community_post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `mentioned_user_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`mentioned_user_ids`)),
  `mentioned_team_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`mentioned_team_ids`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `community_comments`
--

INSERT INTO `community_comments` (`id`, `community_post_id`, `user_id`, `body`, `mentioned_user_ids`, `mentioned_team_ids`, `created_at`, `updated_at`, `edited_at`) VALUES
(1, 1, 227, 'okay!', NULL, NULL, '2026-04-30 12:05:41', '2026-04-30 12:05:41', NULL),
(2, 6, 227, '111', NULL, NULL, '2026-05-01 12:15:14', '2026-05-01 12:15:14', NULL),
(5, 8, 227, 'hi', NULL, NULL, '2026-05-01 12:53:05', '2026-05-01 12:53:05', NULL),
(6, 8, 242, 'hhhhhhhhhhhhhhi', NULL, NULL, '2026-05-01 12:53:23', '2026-05-01 12:53:23', NULL),
(7, 9, 209, 'really ? i do recoment', '[324]', NULL, '2026-05-03 17:16:19', '2026-05-03 17:16:19', NULL),
(8, 10, 325, 'no', NULL, NULL, '2026-05-03 18:26:34', '2026-05-03 18:26:34', NULL),
(9, 10, 325, 'noo', '[324]', NULL, '2026-05-03 18:26:50', '2026-05-03 18:26:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `community_posts`
--

CREATE TABLE `community_posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `likes_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `comments_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `mentioned_user_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`mentioned_user_ids`)),
  `mentioned_team_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`mentioned_team_ids`)),
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `community_post_attachments`
--

CREATE TABLE `community_post_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `community_post_id` bigint(20) UNSIGNED NOT NULL,
  `path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size` bigint(20) UNSIGNED NOT NULL,
  `kind` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `community_post_likes`
--

CREATE TABLE `community_post_likes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `community_post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `editions`
--

CREATE TABLE `editions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `editions`
--

INSERT INTO `editions` (`id`, `year`, `name`, `starts_at`, `ends_at`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2026, 'Next City AI Hackathon 2026', '2026-05-07 08:30:00', '2026-05-08 16:00:00', 1, '2026-04-30 09:01:38', '2026-04-30 09:01:38');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(3, 'default', '{\"uuid\":\"b9c31b0e-7c09-4cf9-b4cc-3b18536a1359\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:5:{s:4:\\\"type\\\";s:12:\\\"post_mention\\\";s:7:\\\"post_id\\\";i:6;s:10:\\\"post_title\\\";s:18:\\\"Debug mention test\\\";s:8:\\\"actor_id\\\";i:209;s:10:\\\"actor_name\\\";s:30:\\\"Youssef Mohamed Moharm Mohamed\\\";}s:2:\\\"id\\\";s:36:\\\"c1545f54-953e-4e7b-be70-466f8d33e2ab\\\";}s:4:\\\"data\\\";a:5:{s:4:\\\"type\\\";s:12:\\\"post_mention\\\";s:7:\\\"post_id\\\";i:6;s:10:\\\"post_title\\\";s:18:\\\"Debug mention test\\\";s:8:\\\"actor_id\\\";i:209;s:10:\\\"actor_name\\\";s:30:\\\"Youssef Mohamed Moharm Mohamed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777631222, 1777631222),
(4, 'default', '{\"uuid\":\"3d137c69-871c-4621-aac4-4d4472258a12\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:209;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:6;s:10:\\\"post_title\\\";s:18:\\\"Debug mention test\\\";s:10:\\\"comment_id\\\";i:2;s:8:\\\"actor_id\\\";i:227;s:10:\\\"actor_name\\\";s:21:\\\"Mahmoud Mohamed Kamel\\\";}s:2:\\\"id\\\";s:36:\\\"d2f9a198-5927-48ab-9cdf-5b4ea6e731e9\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:6;s:10:\\\"post_title\\\";s:18:\\\"Debug mention test\\\";s:10:\\\"comment_id\\\";i:2;s:8:\\\"actor_id\\\";i:227;s:10:\\\"actor_name\\\";s:21:\\\"Mahmoud Mohamed Kamel\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777637715, 1777637715),
(5, 'default', '{\"uuid\":\"7689d372-4ff5-4eda-b6df-6100fb37e4bc\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:5:{s:4:\\\"type\\\";s:9:\\\"post_like\\\";s:7:\\\"post_id\\\";i:7;s:10:\\\"post_title\\\";s:15:\\\"Notif test post\\\";s:8:\\\"actor_id\\\";i:209;s:10:\\\"actor_name\\\";s:30:\\\"Youssef Mohamed Moharm Mohamed\\\";}s:2:\\\"id\\\";s:36:\\\"ce685d5a-0fd4-4557-b164-d69b34fc82a4\\\";}s:4:\\\"data\\\";a:5:{s:4:\\\"type\\\";s:9:\\\"post_like\\\";s:7:\\\"post_id\\\";i:7;s:10:\\\"post_title\\\";s:15:\\\"Notif test post\\\";s:8:\\\"actor_id\\\";i:209;s:10:\\\"actor_name\\\";s:30:\\\"Youssef Mohamed Moharm Mohamed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777637916, 1777637916),
(6, 'default', '{\"uuid\":\"bd7c1cf8-f4cf-4d41-a055-217c50d15b58\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:7;s:10:\\\"post_title\\\";s:15:\\\"Notif test post\\\";s:10:\\\"comment_id\\\";i:3;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}s:2:\\\"id\\\";s:36:\\\"dfa5fe41-18ae-4934-aa9d-75ed8dd4f31c\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:7;s:10:\\\"post_title\\\";s:15:\\\"Notif test post\\\";s:10:\\\"comment_id\\\";i:3;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777637916, 1777637916),
(7, 'default', '{\"uuid\":\"2f7790e7-0e65-4d2c-9245-3b4ee40aabef\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:7;s:10:\\\"post_title\\\";s:15:\\\"Notif test post\\\";s:10:\\\"comment_id\\\";i:4;s:8:\\\"actor_id\\\";i:211;s:10:\\\"actor_name\\\";s:34:\\\"Zeinab Ahmed Ebrahim Osman Ebrahim\\\";}s:2:\\\"id\\\";s:36:\\\"2a8dc54b-c9d5-4129-9c7f-d2c358565c94\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:7;s:10:\\\"post_title\\\";s:15:\\\"Notif test post\\\";s:10:\\\"comment_id\\\";i:4;s:8:\\\"actor_id\\\";i:211;s:10:\\\"actor_name\\\";s:34:\\\"Zeinab Ahmed Ebrahim Osman Ebrahim\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777637916, 1777637916),
(8, 'default', '{\"uuid\":\"895f0542-ab95-48d1-bc90-af2e95f50f15\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:210;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"thread_reply\\\";s:7:\\\"post_id\\\";i:7;s:10:\\\"post_title\\\";s:15:\\\"Notif test post\\\";s:10:\\\"comment_id\\\";i:4;s:8:\\\"actor_id\\\";i:211;s:10:\\\"actor_name\\\";s:34:\\\"Zeinab Ahmed Ebrahim Osman Ebrahim\\\";}s:2:\\\"id\\\";s:36:\\\"ac43fa7a-01dd-456e-9e35-f1044610e643\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"thread_reply\\\";s:7:\\\"post_id\\\";i:7;s:10:\\\"post_title\\\";s:15:\\\"Notif test post\\\";s:10:\\\"comment_id\\\";i:4;s:8:\\\"actor_id\\\";i:211;s:10:\\\"actor_name\\\";s:34:\\\"Zeinab Ahmed Ebrahim Osman Ebrahim\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777637916, 1777637916),
(9, 'default', '{\"uuid\":\"1a7868f2-e1f9-415c-8c77-0d19ea190e9d\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:209;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:5:{s:4:\\\"type\\\";s:9:\\\"post_like\\\";s:7:\\\"post_id\\\";i:6;s:10:\\\"post_title\\\";s:18:\\\"Debug mention test\\\";s:8:\\\"actor_id\\\";i:242;s:10:\\\"actor_name\\\";s:20:\\\"Marawan Khaled Ahmed\\\";}s:2:\\\"id\\\";s:36:\\\"65dfc9f6-8737-43e6-82d3-ad0e2fe0304e\\\";}s:4:\\\"data\\\";a:5:{s:4:\\\"type\\\";s:9:\\\"post_like\\\";s:7:\\\"post_id\\\";i:6;s:10:\\\"post_title\\\";s:18:\\\"Debug mention test\\\";s:8:\\\"actor_id\\\";i:242;s:10:\\\"actor_name\\\";s:20:\\\"Marawan Khaled Ahmed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777639886, 1777639886),
(10, 'default', '{\"uuid\":\"4553fdcc-9fa5-494b-a66c-8875eb3960fe\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:209;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:5:{s:4:\\\"type\\\";s:9:\\\"post_like\\\";s:7:\\\"post_id\\\";i:6;s:10:\\\"post_title\\\";s:18:\\\"Debug mention test\\\";s:8:\\\"actor_id\\\";i:242;s:10:\\\"actor_name\\\";s:20:\\\"Marawan Khaled Ahmed\\\";}s:2:\\\"id\\\";s:36:\\\"4acd0bba-f035-4da0-befe-e038791b4175\\\";}s:4:\\\"data\\\";a:5:{s:4:\\\"type\\\";s:9:\\\"post_like\\\";s:7:\\\"post_id\\\";i:6;s:10:\\\"post_title\\\";s:18:\\\"Debug mention test\\\";s:8:\\\"actor_id\\\";i:242;s:10:\\\"actor_name\\\";s:20:\\\"Marawan Khaled Ahmed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777639890, 1777639890),
(11, 'default', '{\"uuid\":\"dc60e53f-2b84-48a4-95bb-6cdd0f767e67\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:227;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:5:{s:4:\\\"type\\\";s:9:\\\"post_like\\\";s:7:\\\"post_id\\\";i:1;s:10:\\\"post_title\\\";s:5:\\\"12344\\\";s:8:\\\"actor_id\\\";i:242;s:10:\\\"actor_name\\\";s:20:\\\"Marawan Khaled Ahmed\\\";}s:2:\\\"id\\\";s:36:\\\"5008178f-8365-42e1-886d-fc157c3fa52f\\\";}s:4:\\\"data\\\";a:5:{s:4:\\\"type\\\";s:9:\\\"post_like\\\";s:7:\\\"post_id\\\";i:1;s:10:\\\"post_title\\\";s:5:\\\"12344\\\";s:8:\\\"actor_id\\\";i:242;s:10:\\\"actor_name\\\";s:20:\\\"Marawan Khaled Ahmed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777639891, 1777639891),
(12, 'default', '{\"uuid\":\"01de9b8b-a3f9-4f2f-8d01-d989fe8a1204\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:242;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:5:{s:4:\\\"type\\\";s:9:\\\"post_like\\\";s:7:\\\"post_id\\\";i:8;s:10:\\\"post_title\\\";s:4:\\\"1221\\\";s:8:\\\"actor_id\\\";i:227;s:10:\\\"actor_name\\\";s:21:\\\"Mahmoud Mohamed Kamel\\\";}s:2:\\\"id\\\";s:36:\\\"a34db5b3-e302-4db1-b46d-1336c67fadaa\\\";}s:4:\\\"data\\\";a:5:{s:4:\\\"type\\\";s:9:\\\"post_like\\\";s:7:\\\"post_id\\\";i:8;s:10:\\\"post_title\\\";s:4:\\\"1221\\\";s:8:\\\"actor_id\\\";i:227;s:10:\\\"actor_name\\\";s:21:\\\"Mahmoud Mohamed Kamel\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777639973, 1777639973),
(13, 'default', '{\"uuid\":\"430e72d9-8ffb-46ce-8398-61e84651c2be\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:242;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:8;s:10:\\\"post_title\\\";s:4:\\\"1221\\\";s:10:\\\"comment_id\\\";i:5;s:8:\\\"actor_id\\\";i:227;s:10:\\\"actor_name\\\";s:21:\\\"Mahmoud Mohamed Kamel\\\";}s:2:\\\"id\\\";s:36:\\\"17a07630-4736-481c-b46b-2c897141f958\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:8;s:10:\\\"post_title\\\";s:4:\\\"1221\\\";s:10:\\\"comment_id\\\";i:5;s:8:\\\"actor_id\\\";i:227;s:10:\\\"actor_name\\\";s:21:\\\"Mahmoud Mohamed Kamel\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777639985, 1777639985),
(14, 'default', '{\"uuid\":\"a0dde9ef-dc04-442d-a712-4344a04d195a\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:227;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"thread_reply\\\";s:7:\\\"post_id\\\";i:8;s:10:\\\"post_title\\\";s:4:\\\"1221\\\";s:10:\\\"comment_id\\\";i:6;s:8:\\\"actor_id\\\";i:242;s:10:\\\"actor_name\\\";s:20:\\\"Marawan Khaled Ahmed\\\";}s:2:\\\"id\\\";s:36:\\\"bd006f0d-5160-4db4-85dc-fe0e0a70a9b1\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"thread_reply\\\";s:7:\\\"post_id\\\";i:8;s:10:\\\"post_title\\\";s:4:\\\"1221\\\";s:10:\\\"comment_id\\\";i:6;s:8:\\\"actor_id\\\";i:242;s:10:\\\"actor_name\\\";s:20:\\\"Marawan Khaled Ahmed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777640003, 1777640003),
(15, 'default', '{\"uuid\":\"34419af9-2c0e-4e6e-8a86-e5c35796c1f9\",\"displayName\":\"App\\\\Events\\\\ScoreLocked\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\ScoreLocked\\\":4:{s:9:\\\"editionId\\\";i:1;s:6:\\\"teamId\\\";i:123;s:5:\\\"round\\\";s:6:\\\"round1\\\";s:13:\\\"weightedTotal\\\";d:9.3;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777818902, 1777818902),
(16, 'default', '{\"uuid\":\"3718eebb-7356-4a22-8089-a6913f0a15c2\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:332;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:2;s:7:\\\"team_id\\\";i:122;s:9:\\\"team_name\\\";s:8:\\\"Allmanda\\\";s:16:\\\"response_message\\\";N;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}s:2:\\\"id\\\";s:36:\\\"3683ba7e-bd63-4666-90ef-f80852b6af2f\\\";}s:4:\\\"data\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:2;s:7:\\\"team_id\\\";i:122;s:9:\\\"team_name\\\";s:8:\\\"Allmanda\\\";s:16:\\\"response_message\\\";N;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777820599, 1777820599),
(17, 'default', '{\"uuid\":\"6ae30147-3962-470f-acef-b14714dcfcea\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:332;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:11;s:7:\\\"team_id\\\";i:122;s:9:\\\"team_name\\\";s:8:\\\"Allmanda\\\";s:16:\\\"response_message\\\";N;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}s:2:\\\"id\\\";s:36:\\\"e5aa68c3-34ce-4d8a-9633-6ccc657f856d\\\";}s:4:\\\"data\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:11;s:7:\\\"team_id\\\";i:122;s:9:\\\"team_name\\\";s:8:\\\"Allmanda\\\";s:16:\\\"response_message\\\";N;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777822335, 1777822335),
(18, 'default', '{\"uuid\":\"68747c91-b2af-4aab-85d4-fbd01431bbfd\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:332;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:13;s:7:\\\"team_id\\\";i:122;s:9:\\\"team_name\\\";s:8:\\\"Allmanda\\\";s:16:\\\"response_message\\\";N;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}s:2:\\\"id\\\";s:36:\\\"73ac33bf-2aee-422d-bb16-b8e7e42e3bf7\\\";}s:4:\\\"data\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:13;s:7:\\\"team_id\\\";i:122;s:9:\\\"team_name\\\";s:8:\\\"Allmanda\\\";s:16:\\\"response_message\\\";N;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777822409, 1777822409),
(19, 'default', '{\"uuid\":\"56d809ca-ddba-4245-8aaa-3abb4e31ad06\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:332;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:15;s:7:\\\"team_id\\\";i:122;s:9:\\\"team_name\\\";s:8:\\\"Allmanda\\\";s:16:\\\"response_message\\\";N;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}s:2:\\\"id\\\";s:36:\\\"9405c6bf-d0b1-4971-9a04-ad85c4e1e9d8\\\";}s:4:\\\"data\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:15;s:7:\\\"team_id\\\";i:122;s:9:\\\"team_name\\\";s:8:\\\"Allmanda\\\";s:16:\\\"response_message\\\";N;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777822487, 1777822487),
(20, 'default', '{\"uuid\":\"9656b61e-db6b-4ce4-88ce-25cdfb45b4de\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:332;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:17;s:7:\\\"team_id\\\";i:122;s:9:\\\"team_name\\\";s:8:\\\"Allmanda\\\";s:16:\\\"response_message\\\";N;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}s:2:\\\"id\\\";s:36:\\\"2d2d2aff-ce31-4b12-af55-4c9c2984c133\\\";}s:4:\\\"data\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:17;s:7:\\\"team_id\\\";i:122;s:9:\\\"team_name\\\";s:8:\\\"Allmanda\\\";s:16:\\\"response_message\\\";N;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777822553, 1777822553),
(21, 'default', '{\"uuid\":\"f230f817-fb4b-4108-a4ec-c0c8e36bc362\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:332;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:26;s:7:\\\"team_id\\\";i:122;s:9:\\\"team_name\\\";s:8:\\\"Allmanda\\\";s:16:\\\"response_message\\\";N;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}s:2:\\\"id\\\";s:36:\\\"b97deb88-3ff5-417d-9990-b1f39207a962\\\";}s:4:\\\"data\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:26;s:7:\\\"team_id\\\";i:122;s:9:\\\"team_name\\\";s:8:\\\"Allmanda\\\";s:16:\\\"response_message\\\";N;s:8:\\\"actor_id\\\";i:210;s:10:\\\"actor_name\\\";s:21:\\\"Mohamed Saeed Mohamed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777822804, 1777822804),
(22, 'default', '{\"uuid\":\"5095acf1-bf38-4dd0-b3f8-048957b51a24\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:211;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:21:\\\"application_withdrawn\\\";s:14:\\\"application_id\\\";i:18;s:7:\\\"team_id\\\";i:123;s:9:\\\"team_name\\\";s:11:\\\"Aqua guards\\\";s:8:\\\"actor_id\\\";i:333;s:10:\\\"actor_name\\\";s:13:\\\"Ahmed UX Lead\\\";}s:2:\\\"id\\\";s:36:\\\"ff8b0e4f-54e8-404a-b336-9fdd5ce17ca5\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:21:\\\"application_withdrawn\\\";s:14:\\\"application_id\\\";i:18;s:7:\\\"team_id\\\";i:123;s:9:\\\"team_name\\\";s:11:\\\"Aqua guards\\\";s:8:\\\"actor_id\\\";i:333;s:10:\\\"actor_name\\\";s:13:\\\"Ahmed UX Lead\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777823648, 1777823648),
(23, 'default', '{\"uuid\":\"781bd58c-eba1-4ca1-82f5-29aa995301a0\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:209;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:20:\\\"application_received\\\";s:14:\\\"application_id\\\";i:27;s:7:\\\"team_id\\\";i:121;s:9:\\\"team_name\\\";s:8:\\\"AIriScan\\\";s:8:\\\"actor_id\\\";i:333;s:10:\\\"actor_name\\\";s:13:\\\"Ahmed UX Lead\\\";}s:2:\\\"id\\\";s:36:\\\"974d12b9-4f22-49de-8174-85d8211473d5\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:20:\\\"application_received\\\";s:14:\\\"application_id\\\";i:27;s:7:\\\"team_id\\\";i:121;s:9:\\\"team_name\\\";s:8:\\\"AIriScan\\\";s:8:\\\"actor_id\\\";i:333;s:10:\\\"actor_name\\\";s:13:\\\"Ahmed UX Lead\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777826134, 1777826134),
(24, 'default', '{\"uuid\":\"fdf437c1-3117-4c66-8eb9-ab3552fe0ae9\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:209;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:21:\\\"application_withdrawn\\\";s:14:\\\"application_id\\\";i:27;s:7:\\\"team_id\\\";i:121;s:9:\\\"team_name\\\";s:8:\\\"AIriScan\\\";s:8:\\\"actor_id\\\";i:333;s:10:\\\"actor_name\\\";s:13:\\\"Ahmed UX Lead\\\";}s:2:\\\"id\\\";s:36:\\\"85cd0ce4-7869-4d91-a5e3-4c7b1e469bdf\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:21:\\\"application_withdrawn\\\";s:14:\\\"application_id\\\";i:27;s:7:\\\"team_id\\\";i:121;s:9:\\\"team_name\\\";s:8:\\\"AIriScan\\\";s:8:\\\"actor_id\\\";i:333;s:10:\\\"actor_name\\\";s:13:\\\"Ahmed UX Lead\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777826161, 1777826161),
(25, 'default', '{\"uuid\":\"2e0d37bf-cd20-49d4-9acb-065746098b5e\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:209;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:20:\\\"application_received\\\";s:14:\\\"application_id\\\";i:28;s:7:\\\"team_id\\\";i:121;s:9:\\\"team_name\\\";s:8:\\\"AIriScan\\\";s:8:\\\"actor_id\\\";i:333;s:10:\\\"actor_name\\\";s:13:\\\"Ahmed UX Lead\\\";}s:2:\\\"id\\\";s:36:\\\"c5221ef0-a3f0-4194-94b6-186dc912d6f6\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:20:\\\"application_received\\\";s:14:\\\"application_id\\\";i:28;s:7:\\\"team_id\\\";i:121;s:9:\\\"team_name\\\";s:8:\\\"AIriScan\\\";s:8:\\\"actor_id\\\";i:333;s:10:\\\"actor_name\\\";s:13:\\\"Ahmed UX Lead\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777826213, 1777826213),
(26, 'default', '{\"uuid\":\"399f8b17-92a4-4fc3-84a2-6417a7c6c3a4\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:333;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:28;s:7:\\\"team_id\\\";i:121;s:9:\\\"team_name\\\";s:8:\\\"AIriScan\\\";s:16:\\\"response_message\\\";s:3:\\\"hi?\\\";s:8:\\\"actor_id\\\";i:209;s:10:\\\"actor_name\\\";s:30:\\\"Youssef Mohamed Moharm Mohamed\\\";}s:2:\\\"id\\\";s:36:\\\"8d5b400d-729b-4579-99a4-56d88cb77d9a\\\";}s:4:\\\"data\\\";a:7:{s:4:\\\"type\\\";s:20:\\\"application_approved\\\";s:14:\\\"application_id\\\";i:28;s:7:\\\"team_id\\\";i:121;s:9:\\\"team_name\\\";s:8:\\\"AIriScan\\\";s:16:\\\"response_message\\\";s:3:\\\"hi?\\\";s:8:\\\"actor_id\\\";i:209;s:10:\\\"actor_name\\\";s:30:\\\"Youssef Mohamed Moharm Mohamed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777826296, 1777826296),
(27, 'default', '{\"uuid\":\"9f62d3fb-577d-4e78-8fb6-a7ba537ac7d5\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:325;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:5:{s:4:\\\"type\\\";s:9:\\\"post_like\\\";s:7:\\\"post_id\\\";i:9;s:10:\\\"post_title\\\";s:6:\\\"HiHiHi\\\";s:8:\\\"actor_id\\\";i:209;s:10:\\\"actor_name\\\";s:30:\\\"Youssef Mohamed Moharm Mohamed\\\";}s:2:\\\"id\\\";s:36:\\\"60cfefb9-aeac-4e03-9ef0-f0fa5c0a1f17\\\";}s:4:\\\"data\\\";a:5:{s:4:\\\"type\\\";s:9:\\\"post_like\\\";s:7:\\\"post_id\\\";i:9;s:10:\\\"post_title\\\";s:6:\\\"HiHiHi\\\";s:8:\\\"actor_id\\\";i:209;s:10:\\\"actor_name\\\";s:30:\\\"Youssef Mohamed Moharm Mohamed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777828530, 1777828530),
(28, 'default', '{\"uuid\":\"37b8bba0-1f96-432d-87e5-e3a86234525e\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:325;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:9;s:10:\\\"post_title\\\";s:6:\\\"HiHiHi\\\";s:10:\\\"comment_id\\\";i:7;s:8:\\\"actor_id\\\";i:209;s:10:\\\"actor_name\\\";s:30:\\\"Youssef Mohamed Moharm Mohamed\\\";}s:2:\\\"id\\\";s:36:\\\"25157fb6-46f6-4f26-b5b9-762dd6f5d24d\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:9;s:10:\\\"post_title\\\";s:6:\\\"HiHiHi\\\";s:10:\\\"comment_id\\\";i:7;s:8:\\\"actor_id\\\";i:209;s:10:\\\"actor_name\\\";s:30:\\\"Youssef Mohamed Moharm Mohamed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777828579, 1777828579);
INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(29, 'default', '{\"uuid\":\"b75d0346-b851-43ca-993b-9559661f484a\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:324;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:15:\\\"comment_mention\\\";s:7:\\\"post_id\\\";i:9;s:10:\\\"post_title\\\";s:6:\\\"HiHiHi\\\";s:10:\\\"comment_id\\\";i:7;s:8:\\\"actor_id\\\";i:209;s:10:\\\"actor_name\\\";s:30:\\\"Youssef Mohamed Moharm Mohamed\\\";}s:2:\\\"id\\\";s:36:\\\"d4d87ce0-5ae9-4340-9c07-85302b639e47\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:15:\\\"comment_mention\\\";s:7:\\\"post_id\\\";i:9;s:10:\\\"post_title\\\";s:6:\\\"HiHiHi\\\";s:10:\\\"comment_id\\\";i:7;s:8:\\\"actor_id\\\";i:209;s:10:\\\"actor_name\\\";s:30:\\\"Youssef Mohamed Moharm Mohamed\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777828579, 1777828579),
(30, 'default', '{\"uuid\":\"80dbec3a-22b5-472d-92c4-c3ba32eef33e\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:325;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:5:{s:4:\\\"type\\\";s:12:\\\"post_mention\\\";s:7:\\\"post_id\\\";i:10;s:10:\\\"post_title\\\";s:14:\\\"I HAVE AN IDEA\\\";s:8:\\\"actor_id\\\";i:333;s:10:\\\"actor_name\\\";s:13:\\\"Ahmed UX Lead\\\";}s:2:\\\"id\\\";s:36:\\\"6c704eb2-9a39-4b9f-8ead-df3264dd2675\\\";}s:4:\\\"data\\\";a:5:{s:4:\\\"type\\\";s:12:\\\"post_mention\\\";s:7:\\\"post_id\\\";i:10;s:10:\\\"post_title\\\";s:14:\\\"I HAVE AN IDEA\\\";s:8:\\\"actor_id\\\";i:333;s:10:\\\"actor_name\\\";s:13:\\\"Ahmed UX Lead\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777832763, 1777832763),
(31, 'default', '{\"uuid\":\"58cd9777-d86c-4ec7-8772-b31dd2bc5d6e\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:333;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:10;s:10:\\\"post_title\\\";s:14:\\\"I HAVE AN IDEA\\\";s:10:\\\"comment_id\\\";i:8;s:8:\\\"actor_id\\\";i:325;s:10:\\\"actor_name\\\";s:9:\\\"Judge Two\\\";}s:2:\\\"id\\\";s:36:\\\"c906dace-43be-4145-9e2b-e1f58ebd7298\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:10;s:10:\\\"post_title\\\";s:14:\\\"I HAVE AN IDEA\\\";s:10:\\\"comment_id\\\";i:8;s:8:\\\"actor_id\\\";i:325;s:10:\\\"actor_name\\\";s:9:\\\"Judge Two\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777832794, 1777832794),
(32, 'default', '{\"uuid\":\"18c84468-8b99-4849-b68b-6822f95624df\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:333;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:10;s:10:\\\"post_title\\\";s:14:\\\"I HAVE AN IDEA\\\";s:10:\\\"comment_id\\\";i:9;s:8:\\\"actor_id\\\";i:325;s:10:\\\"actor_name\\\";s:9:\\\"Judge Two\\\";}s:2:\\\"id\\\";s:36:\\\"c2c2851f-38e2-45df-b468-76760c446b4f\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:12:\\\"post_comment\\\";s:7:\\\"post_id\\\";i:10;s:10:\\\"post_title\\\";s:14:\\\"I HAVE AN IDEA\\\";s:10:\\\"comment_id\\\";i:9;s:8:\\\"actor_id\\\";i:325;s:10:\\\"actor_name\\\";s:9:\\\"Judge Two\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777832810, 1777832810),
(33, 'default', '{\"uuid\":\"b6e99691-38cf-41f8-a166-894c65eb3249\",\"displayName\":\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":14:{s:5:\\\"event\\\";O:60:\\\"Illuminate\\\\Notifications\\\\Events\\\\BroadcastNotificationCreated\\\":3:{s:10:\\\"notifiable\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:324;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:39:\\\"App\\\\Notifications\\\\CommunityNotification\\\":2:{s:7:\\\"payload\\\";a:6:{s:4:\\\"type\\\";s:15:\\\"comment_mention\\\";s:7:\\\"post_id\\\";i:10;s:10:\\\"post_title\\\";s:14:\\\"I HAVE AN IDEA\\\";s:10:\\\"comment_id\\\";i:9;s:8:\\\"actor_id\\\";i:325;s:10:\\\"actor_name\\\";s:9:\\\"Judge Two\\\";}s:2:\\\"id\\\";s:36:\\\"cab4546b-fd77-4103-bd5f-c6b09d79c8a4\\\";}s:4:\\\"data\\\";a:6:{s:4:\\\"type\\\";s:15:\\\"comment_mention\\\";s:7:\\\"post_id\\\";i:10;s:10:\\\"post_title\\\";s:14:\\\"I HAVE AN IDEA\\\";s:10:\\\"comment_id\\\";i:9;s:8:\\\"actor_id\\\";i:325;s:10:\\\"actor_name\\\";s:9:\\\"Judge Two\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"}}', 0, NULL, 1777832810, 1777832810);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `judge_assignments`
--

CREATE TABLE `judge_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judge_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `round` enum('round1','finals') NOT NULL,
  `recused` tinyint(1) NOT NULL DEFAULT 0,
  `recused_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mentor_assignments`
--

CREATE TABLE `mentor_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mentor_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mentor_notes`
--

CREATE TABLE `mentor_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mentor_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mentor_rotation_slots`
--

CREATE TABLE `mentor_rotation_slots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mentor_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `slot_start` datetime NOT NULL,
  `slot_end` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_26_233954_create_permission_tables', 1),
(5, '2026_04_27_000001_create_editions_themes_phases_tables', 1),
(6, '2026_04_27_000002_create_teams_tables', 1),
(7, '2026_04_27_000003_create_submissions_tables', 1),
(8, '2026_04_27_000004_create_judging_tables', 1),
(9, '2026_04_27_000005_create_pitch_voting_mentor_tables', 1),
(10, '2026_04_27_100000_add_guest_voting_to_peoples_choice_votes', 1),
(11, '2026_04_29_000001_add_channel_to_team_comments', 1),
(12, '2026_04_29_000002_add_round_to_submissions', 1),
(13, '2026_04_29_000003_add_profile_to_users', 1),
(14, '2026_04_29_000004_add_identity_to_teams', 1),
(15, '2026_04_30_000001_add_recruitment_to_teams', 2),
(16, '2026_04_30_000002_create_team_applications_table', 2),
(17, '2026_04_30_000003_create_community_posts_tables', 2),
(18, '2026_04_30_000004_create_community_post_attachments_table', 3),
(19, '2026_04_30_000005_create_notifications_table', 4),
(20, '2026_04_30_000006_add_mentions_to_community_content', 4),
(21, '2026_04_30_000007_add_edited_at_to_community', 5),
(22, '2026_05_03_000001_add_role_categories', 6),
(23, '2026_05_05_000001_add_registration_status_to_users', 7);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 468),
(3, 'App\\Models\\User', 467),
(4, 'App\\Models\\User', 343),
(4, 'App\\Models\\User', 344),
(4, 'App\\Models\\User', 350),
(4, 'App\\Models\\User', 351),
(4, 'App\\Models\\User', 352),
(4, 'App\\Models\\User', 354),
(4, 'App\\Models\\User', 355),
(4, 'App\\Models\\User', 360),
(4, 'App\\Models\\User', 362),
(4, 'App\\Models\\User', 363),
(4, 'App\\Models\\User', 366),
(4, 'App\\Models\\User', 367),
(4, 'App\\Models\\User', 368),
(4, 'App\\Models\\User', 371),
(4, 'App\\Models\\User', 372),
(4, 'App\\Models\\User', 373),
(4, 'App\\Models\\User', 374),
(4, 'App\\Models\\User', 375),
(4, 'App\\Models\\User', 377),
(4, 'App\\Models\\User', 378),
(4, 'App\\Models\\User', 379),
(4, 'App\\Models\\User', 380),
(4, 'App\\Models\\User', 381),
(4, 'App\\Models\\User', 382),
(4, 'App\\Models\\User', 383),
(4, 'App\\Models\\User', 384),
(5, 'App\\Models\\User', 345),
(5, 'App\\Models\\User', 346),
(5, 'App\\Models\\User', 347),
(5, 'App\\Models\\User', 348),
(5, 'App\\Models\\User', 349),
(5, 'App\\Models\\User', 353),
(5, 'App\\Models\\User', 356),
(5, 'App\\Models\\User', 357),
(5, 'App\\Models\\User', 358),
(5, 'App\\Models\\User', 359),
(5, 'App\\Models\\User', 361),
(5, 'App\\Models\\User', 364),
(5, 'App\\Models\\User', 365),
(5, 'App\\Models\\User', 369),
(5, 'App\\Models\\User', 370),
(5, 'App\\Models\\User', 376),
(5, 'App\\Models\\User', 385),
(5, 'App\\Models\\User', 386),
(5, 'App\\Models\\User', 387),
(5, 'App\\Models\\User', 388),
(5, 'App\\Models\\User', 389),
(5, 'App\\Models\\User', 390),
(5, 'App\\Models\\User', 391),
(5, 'App\\Models\\User', 392),
(5, 'App\\Models\\User', 393),
(5, 'App\\Models\\User', 394),
(5, 'App\\Models\\User', 395),
(5, 'App\\Models\\User', 396),
(5, 'App\\Models\\User', 397),
(5, 'App\\Models\\User', 398),
(5, 'App\\Models\\User', 399),
(5, 'App\\Models\\User', 400),
(5, 'App\\Models\\User', 401),
(5, 'App\\Models\\User', 402),
(5, 'App\\Models\\User', 403),
(5, 'App\\Models\\User', 404),
(5, 'App\\Models\\User', 405),
(5, 'App\\Models\\User', 406),
(5, 'App\\Models\\User', 407),
(5, 'App\\Models\\User', 408),
(5, 'App\\Models\\User', 409),
(5, 'App\\Models\\User', 410),
(5, 'App\\Models\\User', 411),
(5, 'App\\Models\\User', 412),
(5, 'App\\Models\\User', 413),
(5, 'App\\Models\\User', 414),
(5, 'App\\Models\\User', 415),
(5, 'App\\Models\\User', 416),
(5, 'App\\Models\\User', 417),
(5, 'App\\Models\\User', 418),
(5, 'App\\Models\\User', 419),
(5, 'App\\Models\\User', 420),
(5, 'App\\Models\\User', 421),
(5, 'App\\Models\\User', 422),
(5, 'App\\Models\\User', 423),
(5, 'App\\Models\\User', 424),
(5, 'App\\Models\\User', 425),
(5, 'App\\Models\\User', 426),
(5, 'App\\Models\\User', 427),
(5, 'App\\Models\\User', 428),
(5, 'App\\Models\\User', 429),
(5, 'App\\Models\\User', 430),
(5, 'App\\Models\\User', 431),
(5, 'App\\Models\\User', 432),
(5, 'App\\Models\\User', 433),
(5, 'App\\Models\\User', 434),
(5, 'App\\Models\\User', 435),
(5, 'App\\Models\\User', 436),
(5, 'App\\Models\\User', 437),
(5, 'App\\Models\\User', 438),
(5, 'App\\Models\\User', 439),
(5, 'App\\Models\\User', 440),
(5, 'App\\Models\\User', 441),
(5, 'App\\Models\\User', 442),
(5, 'App\\Models\\User', 443),
(5, 'App\\Models\\User', 444),
(5, 'App\\Models\\User', 445),
(5, 'App\\Models\\User', 446),
(5, 'App\\Models\\User', 447),
(5, 'App\\Models\\User', 448),
(5, 'App\\Models\\User', 449),
(5, 'App\\Models\\User', 450),
(5, 'App\\Models\\User', 451),
(5, 'App\\Models\\User', 452),
(5, 'App\\Models\\User', 453),
(5, 'App\\Models\\User', 454),
(5, 'App\\Models\\User', 455),
(5, 'App\\Models\\User', 456),
(5, 'App\\Models\\User', 457),
(5, 'App\\Models\\User', 458),
(5, 'App\\Models\\User', 459),
(5, 'App\\Models\\User', 460),
(5, 'App\\Models\\User', 461),
(5, 'App\\Models\\User', 462),
(5, 'App\\Models\\User', 463),
(5, 'App\\Models\\User', 464),
(5, 'App\\Models\\User', 465),
(5, 'App\\Models\\User', 466);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `peoples_choice_votes`
--

CREATE TABLE `peoples_choice_votes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `voter_name` varchar(255) DEFAULT NULL,
  `voter_email` varchar(255) DEFAULT NULL,
  `voter_token` varchar(64) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `voted_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `phases`
--

CREATE TABLE `phases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `edition_id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `state` enum('pending','active','closed') NOT NULL DEFAULT 'pending',
  `auto_transition` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `phases`
--

INSERT INTO `phases` (`id`, `edition_id`, `key`, `label`, `starts_at`, `ends_at`, `state`, `auto_transition`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'registration', 'Registration', '2026-04-26 00:00:00', '2026-05-07 08:30:00', 'pending', 1, 1, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(2, 1, 'theme_lock_window', 'Problem Theme Lock-In', '2026-05-07 09:45:00', '2026-05-07 10:00:00', 'pending', 1, 2, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(3, 1, 'sprint_1', 'Development Sprint 1', '2026-05-07 10:00:00', '2026-05-07 12:00:00', 'pending', 1, 3, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(4, 1, 'mentor_speed', 'Mentor Speed Rounds', '2026-05-07 13:00:00', '2026-05-07 13:30:00', 'pending', 1, 4, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(5, 1, 'sprint_2', 'Development Sprint 2', '2026-05-07 13:30:00', '2026-05-07 15:30:00', 'pending', 1, 5, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(6, 1, 'submission_window', 'Submission Window', '2026-05-07 15:30:00', '2026-05-07 15:45:00', 'pending', 1, 6, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(7, 1, 'submission_closed', 'Submissions Closed', '2026-05-07 15:45:00', '2026-05-08 09:20:00', 'pending', 1, 7, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(8, 1, 'round1_pitching', 'Round 1 Pitches', '2026-05-03 17:49:21', '2026-05-05 18:49:21', 'active', 1, 8, '2026-04-30 09:01:38', '2026-05-03 15:49:21'),
(9, 1, 'judging_break', 'Judges\' Deliberation', '2026-05-08 11:30:00', '2026-05-08 12:00:00', 'pending', 1, 9, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(10, 1, 'finalist_announce', 'Finalist Announcement', '2026-05-08 12:00:00', '2026-05-08 12:15:00', 'pending', 1, 10, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(11, 1, 'finals_submission_window', 'Finals Submission Window', '2026-05-08 12:15:00', '2026-05-08 12:55:00', 'pending', 1, 11, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(12, 1, 'finalist_pitching', 'Finalist Pitches', '2026-05-08 13:00:00', '2026-05-08 14:30:00', 'pending', 1, 12, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(13, 1, 'awards', 'Awards Ceremony', '2026-05-08 15:00:00', '2026-05-08 16:00:00', 'pending', 1, 13, '2026-04-30 09:01:38', '2026-04-30 09:01:38');

-- --------------------------------------------------------

--
-- Table structure for table `pitch_schedule`
--

CREATE TABLE `pitch_schedule` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `round` enum('round1','finals') NOT NULL,
  `slot_index` smallint(5) UNSIGNED NOT NULL,
  `scheduled_start` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(2, 'judge', 'web', '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(3, 'mentor', 'web', '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(4, 'team_leader', 'web', '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(5, 'team_member', 'web', '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(6, 'voter', 'web', '2026-04-30 09:01:38', '2026-04-30 09:01:38');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judge_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `round` enum('round1','finals') NOT NULL,
  `innovation` decimal(4,2) DEFAULT NULL,
  `technical` decimal(4,2) DEFAULT NULL,
  `impact` decimal(4,2) DEFAULT NULL,
  `ux` decimal(4,2) DEFAULT NULL,
  `pitch` decimal(4,2) DEFAULT NULL,
  `business` decimal(4,2) DEFAULT NULL,
  `weighted_total` decimal(5,2) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('m6y81FHlCFxPggT4n1h7RM3Kc0nNiF8ZcIbC0aKj', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYTNYejIxeE9JQ2Q3VU5STlEyV3lhdUJsUkNzODRkelM3RkZ4UktIZCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3RlciI7fX0=', 1777933057);

-- --------------------------------------------------------

--
-- Table structure for table `special_award_nominations`
--

CREATE TABLE `special_award_nominations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judge_id` bigint(20) UNSIGNED NOT NULL,
  `award_key` varchar(255) NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `round` enum('round1','finals') NOT NULL DEFAULT 'finals',
  `justification` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `round` enum('round1','finals') NOT NULL DEFAULT 'round1',
  `report_pdf_path` varchar(255) DEFAULT NULL,
  `slides_url` varchar(255) DEFAULT NULL,
  `repo_url` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `ai_disclosure_text` text DEFAULT NULL,
  `status` enum('draft','submitted','validated','flagged','accepted','rejected') NOT NULL DEFAULT 'draft',
  `submitted_at` datetime DEFAULT NULL,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reject_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submission_validations`
--

CREATE TABLE `submission_validations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `submission_id` bigint(20) UNSIGNED NOT NULL,
  `check_key` varchar(255) NOT NULL,
  `status` enum('pending','pass','fail') NOT NULL DEFAULT 'pending',
  `message` text DEFAULT NULL,
  `checked_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `edition_id` bigint(20) UNSIGNED NOT NULL,
  `theme_id` bigint(20) UNSIGNED DEFAULT NULL,
  `leader_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `tagline` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `banner_path` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `status` enum('active','withdrawn','disqualified') NOT NULL DEFAULT 'active',
  `is_finalist` tinyint(1) NOT NULL DEFAULT 0,
  `all_first_timers` tinyint(1) NOT NULL DEFAULT 0,
  `is_recruiting` tinyint(1) NOT NULL DEFAULT 0,
  `recruitment_message` text DEFAULT NULL,
  `looking_for_skills` varchar(255) DEFAULT NULL,
  `needed_roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`needed_roles`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `edition_id`, `theme_id`, `leader_id`, `name`, `tagline`, `logo_path`, `banner_path`, `slug`, `status`, `is_finalist`, `all_first_timers`, `is_recruiting`, `recruitment_message`, `looking_for_skills`, `needed_roles`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 343, 'Lacoste', NULL, NULL, NULL, 'lacoste', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:44', '2026-05-04 20:35:44'),
(2, 1, NULL, 344, 'RVM', 'Smart RVM using edge AI and IoT to classify recyclables, reduce contamination, and boost engagement via gamification, with offline-first operation for reliable campus deployment.', NULL, NULL, 'rvm', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:44', '2026-05-04 20:35:44'),
(3, 1, NULL, 350, 'Genesis', 'Industrial systems struggle with dynamic energy costs and rigid control. Opti-Twin uses AI and a Digital Twin to optimize energy, machine health, and production in real time effici…', NULL, NULL, 'genesis', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:46', '2026-05-04 20:35:46'),
(4, 1, NULL, 351, 'Synapse404', 'AI-powered student health companion that tracks habits, provides personalized recommendations, and uses gamification to improve lifestyle, focus, and overall well-being.', NULL, NULL, 'synapse404', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:47', '2026-05-04 20:35:47'),
(5, 1, NULL, 352, 'The survivers', 'AI-powered system that dynamically optimizes traffic signals using real-time data from sensors and cameras to reduce congestion, lower emissions, and improve urban mobility in citi…', NULL, NULL, 'the-survivers', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:47', '2026-05-04 20:35:47'),
(6, 1, NULL, 354, 'Nomeda', 'MSTE is an emotion-aware AI platform using audio, visual, and biometric data to deliver empathetic, evidence-based mental health support with real-time insights.', NULL, NULL, 'nomeda', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:48', '2026-05-04 20:35:48'),
(7, 1, NULL, 355, 'Rasid - رَاصِد', 'Egypt loses 0.46 km² of coastline yearly, with 84M people at risk by 2100. Rasid uses satellite imagery and ML to monitor erosion in real-time and deliver early warnings before it’…', NULL, NULL, 'rasid-rasd', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:48', '2026-05-04 20:35:48'),
(8, 1, NULL, 360, 'Octelligence', '1. Need for Automation 2. Early Detection and Prevention of Vision Loss. 3. Increased Accuracy Over Manual Assessments 4. False Negatives 5. Patient Empowerment and Education', NULL, NULL, 'octelligence', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:50', '2026-05-04 20:35:50'),
(9, 1, NULL, 362, 'Hack Elite', '​AI-driven HealthTech platform for mental and chronic care. Features educational content and an AI companion for daily routine advice and therapy-like support to improve patient we…', NULL, NULL, 'hack-elite', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:51', '2026-05-04 20:35:51'),
(10, 1, NULL, 363, 'biomedical coders', 'Anemia affects millions worldwide. This project uses AI, computer vision, and machine learning to analyze eye images for early, non-invasive anemia detection.', NULL, NULL, 'biomedical-coders', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:51', '2026-05-04 20:35:51'),
(11, 1, NULL, 366, 'VisionX Team', 'AI platform connecting restaurants with suppliers to optimize supply chains, predict demand, reduce food waste, and improve inventory efficiency.', NULL, NULL, 'visionx-team', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:52', '2026-05-04 20:35:52'),
(12, 1, NULL, 367, 'FlowAxis', 'AI-powered traffic management system for Egypt’s North Coast. Predicts congestion hotspots using ML, shows real-time heatmaps, and provides smart alerts for admins and route sugges…', NULL, NULL, 'flowaxis', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:52', '2026-05-04 20:35:52'),
(13, 1, NULL, 368, 'HealthCode', 'Crowded coastal cities face sudden health risks with no unified medical data. Care is reactive, not predictive leading to delayed response, overloaded clinics, and preventable emer…', NULL, NULL, 'healthcode', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:53', '2026-05-04 20:35:53'),
(14, 1, NULL, 371, 'PharmaChain AI', 'PharmaChain AI is an intelligent platform that connects pharmacies with suppliers through a unified digital ordering system. With an integrated smart forecasting solution.', NULL, NULL, 'pharmachain-ai', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:54', '2026-05-04 20:35:54'),
(15, 1, NULL, 372, 'Allmanda', 'Natural products for skin and hair care with economic value', NULL, NULL, 'allmanda', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:54', '2026-05-04 20:35:54'),
(16, 1, NULL, 373, 'Shimaa Elsaied Ibrahim Muhammad', '.', NULL, NULL, 'shimaa-elsaied-ibrahim-muhammad', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:54', '2026-05-04 20:35:54'),
(17, 1, NULL, 374, 'Nour Maged Ahmed', '.', NULL, NULL, 'nour-maged-ahmed', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:55', '2026-05-04 20:35:55'),
(18, 1, NULL, 375, 'Aqua guards', 'Smart AI-powered wearable band that detects drowning or distress in real time and alerts lifeguards with location, enabling faster response and improving beach safety in crowded ar…', NULL, NULL, 'aqua-guards', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:55', '2026-05-04 20:35:55'),
(19, 1, NULL, 377, 'TABx', 'The idea revolves around an AI-powered virtual work environment where students solve real tasks, collaborate with AI teammates, and gain practical job-ready skills through simulati…', NULL, NULL, 'tabx', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:56', '2026-05-04 20:35:56'),
(20, 1, NULL, 378, 'AIriScan', 'Millions delay eye disease diagnosis due to limited access to doctors and neglect. Our AI detects early eye conditions and connects users to nearby doctors and pharmacies instantly', NULL, NULL, 'airiscan', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:56', '2026-05-04 20:35:56'),
(21, 1, NULL, 379, 'Health Hackers', 'Health apps feel repetitive and isolating, causing users to lose motivation. Our solution is a gamified social platform with challenges and rewards to make healthy habits engaging.', NULL, NULL, 'health-hackers', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:57', '2026-05-04 20:35:57'),
(22, 1, NULL, 380, 'Loop', 'A real-time mobile app tracking buses, shuttles & light rail via GPS. Features live arrivals, occupancy alerts, trip planning & smart sensor integration for seamless, sustainable u…', NULL, NULL, 'loop', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:57', '2026-05-04 20:35:57'),
(23, 1, NULL, 381, 'NextGen AI', 'An AI-powered smart traffi management system that uses cameras and sensors to reduce traffic congestion, optimize traffic lights, and improve emergency response time.', NULL, NULL, 'nextgen-ai', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:58', '2026-05-04 20:35:58'),
(24, 1, NULL, 382, 'EcoMind AI', 'Smart BioBasket: A smart framework that classifies urban organic waste at the source. It evaluates waste for bio-conversion and provides real-time data to optimize city logistics a…', NULL, NULL, 'ecomind-ai', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:58', '2026-05-04 20:35:58'),
(25, 1, NULL, 383, 'VitalSync', 'VitalSync is an AI-powered preventive healthcare platform that unifies health data, detects risks early, and provides personalized insights to improve long-term wellness.', NULL, NULL, 'vitalsync', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:59', '2026-05-04 20:35:59'),
(26, 1, NULL, 384, 'Aoun team', 'J', NULL, NULL, 'aoun-team', 'active', 0, 0, 0, NULL, NULL, NULL, '2026-05-04 20:35:59', '2026-05-04 20:35:59');

-- --------------------------------------------------------

--
-- Table structure for table `team_applications`
--

CREATE TABLE `team_applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message` text DEFAULT NULL,
  `skills` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `response_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_comments`
--

CREATE TABLE `team_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `channel` enum('team','mentor','judge') NOT NULL DEFAULT 'team',
  `body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_in_team` varchar(255) DEFAULT NULL,
  `role_category` varchar(32) DEFAULT NULL,
  `is_leader` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `team_id`, `user_id`, `role_in_team`, `role_category`, `is_leader`, `created_at`, `updated_at`) VALUES
(1, 1, 343, 'Developer', NULL, 1, '2026-05-04 20:35:44', '2026-05-04 20:35:44'),
(2, 2, 344, 'Developer', NULL, 1, '2026-05-04 20:35:44', '2026-05-04 20:35:44'),
(3, 2, 345, 'Developer', NULL, 0, '2026-05-04 20:35:45', '2026-05-04 20:35:45'),
(4, 2, 346, 'Developer', NULL, 0, '2026-05-04 20:35:45', '2026-05-04 20:35:45'),
(5, 2, 347, 'Developer', NULL, 0, '2026-05-04 20:35:45', '2026-05-04 20:35:45'),
(6, 2, 348, 'Researcher', NULL, 0, '2026-05-04 20:35:46', '2026-05-04 20:35:46'),
(7, 2, 349, 'Developer', NULL, 0, '2026-05-04 20:35:46', '2026-05-04 20:35:46'),
(8, 3, 350, 'Developer', NULL, 1, '2026-05-04 20:35:46', '2026-05-04 20:35:46'),
(9, 4, 351, 'Developer', NULL, 1, '2026-05-04 20:35:47', '2026-05-04 20:35:47'),
(10, 5, 352, 'Developer', NULL, 1, '2026-05-04 20:35:47', '2026-05-04 20:35:47'),
(11, 5, 353, 'Developer', NULL, 0, '2026-05-04 20:35:47', '2026-05-04 20:35:47'),
(12, 6, 354, 'Developer', NULL, 1, '2026-05-04 20:35:48', '2026-05-04 20:35:48'),
(13, 7, 355, 'Developer', NULL, 1, '2026-05-04 20:35:48', '2026-05-04 20:35:48'),
(14, 7, 356, 'Data Scientist', NULL, 0, '2026-05-04 20:35:48', '2026-05-04 20:35:48'),
(15, 7, 357, 'Data Scientist', NULL, 0, '2026-05-04 20:35:49', '2026-05-04 20:35:49'),
(16, 7, 358, 'Data Scientist', NULL, 0, '2026-05-04 20:35:49', '2026-05-04 20:35:49'),
(17, 7, 359, 'Data Scientist', NULL, 0, '2026-05-04 20:35:50', '2026-05-04 20:35:50'),
(18, 8, 360, 'Developer', NULL, 1, '2026-05-04 20:35:50', '2026-05-04 20:35:50'),
(19, 8, 361, 'Researcher', NULL, 0, '2026-05-04 20:35:50', '2026-05-04 20:35:50'),
(20, 9, 362, 'Developer', NULL, 1, '2026-05-04 20:35:51', '2026-05-04 20:35:51'),
(21, 10, 363, 'Developer', NULL, 1, '2026-05-04 20:35:51', '2026-05-04 20:35:51'),
(22, 10, 364, 'Designer', NULL, 0, '2026-05-04 20:35:51', '2026-05-04 20:35:51'),
(23, 10, 365, 'Data Scientist', NULL, 0, '2026-05-04 20:35:52', '2026-05-04 20:35:52'),
(24, 11, 366, 'Business Lead', NULL, 1, '2026-05-04 20:35:52', '2026-05-04 20:35:52'),
(25, 12, 367, 'Developer', NULL, 1, '2026-05-04 20:35:52', '2026-05-04 20:35:52'),
(26, 13, 368, 'Data Scientist', NULL, 1, '2026-05-04 20:35:53', '2026-05-04 20:35:53'),
(27, 13, 369, 'Designer', NULL, 0, '2026-05-04 20:35:53', '2026-05-04 20:35:53'),
(28, 13, 370, 'Data Scientist', NULL, 0, '2026-05-04 20:35:53', '2026-05-04 20:35:53'),
(29, 14, 371, 'Designer', NULL, 1, '2026-05-04 20:35:54', '2026-05-04 20:35:54'),
(30, 15, 372, 'Business Lead', NULL, 1, '2026-05-04 20:35:54', '2026-05-04 20:35:54'),
(31, 16, 373, 'Designer', NULL, 1, '2026-05-04 20:35:54', '2026-05-04 20:35:54'),
(32, 17, 374, 'Researcher', NULL, 1, '2026-05-04 20:35:55', '2026-05-04 20:35:55'),
(33, 18, 375, 'Researcher', NULL, 1, '2026-05-04 20:35:55', '2026-05-04 20:35:55'),
(34, 18, 376, 'Data Scientist', NULL, 0, '2026-05-04 20:35:56', '2026-05-04 20:35:56'),
(35, 19, 377, 'Data Scientist', NULL, 1, '2026-05-04 20:35:56', '2026-05-04 20:35:56'),
(36, 20, 378, 'Data Scientist', NULL, 1, '2026-05-04 20:35:56', '2026-05-04 20:35:56'),
(37, 21, 379, 'Developer', NULL, 1, '2026-05-04 20:35:57', '2026-05-04 20:35:57'),
(38, 22, 380, 'Developer', NULL, 1, '2026-05-04 20:35:57', '2026-05-04 20:35:57'),
(39, 23, 381, 'Data Scientist', NULL, 1, '2026-05-04 20:35:58', '2026-05-04 20:35:58'),
(40, 24, 382, 'Researcher', NULL, 1, '2026-05-04 20:35:58', '2026-05-04 20:35:58'),
(41, 25, 383, 'Data Scientist', NULL, 1, '2026-05-04 20:35:59', '2026-05-04 20:35:59'),
(42, 26, 384, 'Developer', NULL, 1, '2026-05-04 20:35:59', '2026-05-04 20:35:59'),
(43, 26, 385, 'Developer', NULL, 0, '2026-05-04 20:35:59', '2026-05-04 20:35:59');

-- --------------------------------------------------------

--
-- Table structure for table `team_workspace_drafts`
--

CREATE TABLE `team_workspace_drafts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `section_key` varchar(255) NOT NULL,
  `body` longtext DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE `themes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `edition_id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `edition_id`, `key`, `name`, `description`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'mobility', 'Mobility & Smart Traffic', 'AI-driven traffic management, congestion prediction, adaptive signal control, autonomous vehicle integration, and accessible urban mobility solutions.', 1, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(2, 1, 'disaster_resilience', 'Disaster Resilience', 'Early warning systems, AI-powered emergency response coordination, real-time risk mapping, crisis simulation, and post-disaster recovery optimization.', 2, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(3, 1, 'predictive_maintenance', 'Predictive Maintenance', 'AI for infrastructure health monitoring, fault detection in utilities and transportation assets, lifecycle prediction, and smart maintenance scheduling.', 3, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(4, 1, 'smart_mobility_maas', 'Smart Mobility & MaaS', 'Mobility as a Service (MaaS) platforms, multimodal trip planning, dynamic routing, ride-sharing optimization, and micro-mobility integration.', 4, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(5, 1, 'circular_economy', 'Circular Economy', 'AI for waste stream optimization, resource recovery, reverse logistics, urban mining, sustainable supply chains, and consumption pattern analysis.', 5, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(6, 1, 'tourism', 'Tourism & Hospitality Innovation', 'Smart visitor management, personalized tourism experiences, AI-powered cultural heritage preservation, hospitality demand forecasting, and sustainable tourism flows.', 6, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(7, 1, 'healthcare', 'Smart Healthcare & Health Infrastructure', 'Telemedicine platforms, AI diagnostics, hospital resource optimization, public health surveillance, mental health tools, and health equity analytics.', 7, '2026-04-30 09:01:38', '2026-04-30 09:01:38'),
(8, 1, 'smart_buildings', 'Smart Buildings & Built Environment', 'AI for building energy management, occupancy sensing, HVAC optimization, digital twins of urban infrastructure, and accessible smart space design.', 8, '2026-04-30 09:01:38', '2026-04-30 09:01:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `institution` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `headline` varchar(255) DEFAULT NULL,
  `primary_role` varchar(32) DEFAULT NULL,
  `social_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_links`)),
  `registration_status` varchar(20) NOT NULL DEFAULT 'approved',
  `requested_role` varchar(32) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `national_id` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `phone`, `institution`, `bio`, `avatar_path`, `headline`, `primary_role`, `social_links`, `registration_status`, `requested_role`, `approved_at`, `national_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'ACIE Admin', 'admin@acie.local', NULL, '$2y$12$jI4cl5bCiTwzk0gynzN4ZuJZq.fsCXKXTydOXJXpJA4hSO/dgAJhG', NULL, 'Alamein Center for Innovation and Entrepreneurship', NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-04-30 09:01:39', '2026-04-30 09:01:39'),
(343, 'Mahmoud Mohamed Kamel', 'mahmoud.kamel.2023@aiu.edu.eg', NULL, '$2y$12$lHxGdV.yI.xJLzXqX75ALObLtDOARixE8m1mi/tvTH9VP3Ll/SH52', '22101160', 'Alamein international university', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Software Development;Data Science;UI/UX Design;Business / Marketing\n\nIdea: No problem statement yet.', NULL, 'Developer · Advanced · Artificial intelligence science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:44', '2026-05-04 20:35:44'),
(344, 'Ahmed Yasser Ibrahim Elsayed', 'ahmed.yasser.2024@aiu.edu.eg', NULL, '$2y$12$gElZczK1r.8JKwTAHfqe.OntIb2JnMwxQqlwoHVWPqGxc0Ob12JF.', '01273380071', 'AIU', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Software Development\n\nIdea: Smart RVM using edge AI and IoT to classify recyclables, reduce contamination, and boost engagement via gamification, with offline-first operation for reliable campus deployment.', NULL, 'Developer · Intermediate · Computer engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:44', '2026-05-04 20:35:44'),
(345, 'Marawan Khaled Ahmed', 'marawankhaled7777@gmail.com', NULL, '$2y$12$3ELPBI.wsCExOn6ca2bSrOJ4FTJsjFMDEd18/eURTlV661Nqbai0e', '01212840173', 'Alamein international university', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Software Development;Data Science;Business / Marketing\n\nIdea: Smart RVM using edge AI and IoT to classify recyclables, reduce contamination, and boost engagement via gamification, with offline-first operation for reliable campus deployment.', NULL, 'Developer · Intermediate · Computer engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:45', '2026-05-04 20:35:45'),
(346, 'Muhammed Adel Abdulrahman', 'muhammed.elsaid.2024@aiu.edu.eg', NULL, '$2y$12$sb/4NIab39ztymsbZKOV0ORA2agWQWZFQgoCIIGgeszX1rl.bGxU.', '01153888321', 'AlAlamein International University', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning; IoT / Hardware;Software Development;Data Science\n\nIdea: Smart RVM using edge AI and IoT to classify recyclables, reduce contamination, and boost engagement via gamification, with offline-first operation for reliable campus deployment.', NULL, 'Developer · Intermediate · Computer Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:45', '2026-05-04 20:35:45'),
(347, 'Mohamed Mostafa Awad Bekhit', 'mohmamed.bekhit.2024@aiu.edu.eg', NULL, '$2y$12$Vp5lysGk/pqEqgDe1cfwc.nFGKVKBUFlHjhXVAdgzvKhjUlLmbezm', '01224012641', 'Alamein International University', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Data Science;Software Development;UI/UX Design; IoT / Hardware\n\nIdea: Smart RVM using edge AI and IoT to classify recyclables, reduce contamination, and boost engagement via gamification, with offline-first operation for reliable campus deployment.', NULL, 'Developer · Intermediate · Computer Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:45', '2026-05-04 20:35:45'),
(348, 'Ahmed Khaled Ahmed Said', 'ahmed.said.2024@aiu.edu.eg', NULL, '$2y$12$A7v0NHUU8M8QFG5HzaLuvOQGM9GEXB79aymxDBQzvibE6tgoDCx4S', '01274206608', 'Alamein International University', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning\n\nIdea: Smart RVM using edge AI and IoT to classify recyclables, reduce contamination, and boost engagement via gamification, with offline-first operation for reliable campus deployment.', NULL, 'Researcher · Intermediate · computer engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:46', '2026-05-04 20:35:46'),
(349, 'Yasseen Ahmed Elsayed Abdelghany', 'yasseen.abdulghany.2024@aiu.edu.eg', NULL, '$2y$12$uqJzDGr2neMCD3adUpsYNOBoWythXdP/Yv.4gQ5x9Jb9b53.rmLz6', '01503080095', 'Alamein international university', 'Track: Industry Challenges\nSkills: AI / Machine Learning;Software Development; IoT / Hardware\n\nIdea: Smart RVM using edge AI and IoT to classify recyclables, reduce contamination, and boost engagement via gamification, with offline-first operation for reliable campus deployment.', NULL, 'Developer · Intermediate · Computer engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:46', '2026-05-04 20:35:46'),
(350, 'Ahmed Ebrahem Hamdy Abdelzaher', 'ahmedebrahem7111@gmail.com', NULL, '$2y$12$KKGPu2ZndZGHvZkxpKW/tOaqV/idTiDAU241WvzyYkluoVefHxo2G', '01004696345', 'aiu', 'Track: Industry Challenges\nSkills: AI / Machine Learning;Software Development\n\nIdea: Industrial systems struggle with dynamic energy costs and rigid control. Opti-Twin uses AI and a Digital Twin to optimize energy, machine health, and production in real time efficiently', NULL, 'Developer · Intermediate · computer science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:46', '2026-05-04 20:35:46'),
(351, 'Omar Reda Mansour Elshaer', 'omar.khamis.2025@aiu.edu.eg', NULL, '$2y$12$DsfkLrLrY.eRICAmj3XMnuA44iAYmxjjxvRfJSeJR7i0OhcGzuFMK', '01286354705', 'Alamein International University', 'Track: Health Promotion\nSkills: Software Development;UI/UX Design;Data Science;AI\n\nIdea: AI-powered student health companion that tracks habits, provides personalized recommendations, and uses gamification to improve lifestyle, focus, and overall well-being.', NULL, 'Developer · Intermediate · Computer science - Software Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:47', '2026-05-04 20:35:47'),
(352, 'Yousef Mahmoud Wahied Elserafy', 'youssef.waheed.2025@aiu.edu.eg', NULL, '$2y$12$NXK.0y/s1gi6YIshiFvtXum6uSVd3EKdTbuHjy4z6vhmHo24EysU2', '01066533307', '10', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Business / Marketing\n\nIdea: AI-powered system that dynamically optimizes traffic signals using real-time data from sensors and cameras to reduce congestion, lower emissions, and improve urban mobility in cities like Alexandria.', NULL, 'Developer · Beginner · Computer science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:47', '2026-05-04 20:35:47'),
(353, 'Abdulrahman Mohammed', 'abdelrahman77mohammad@gmail.com', NULL, '$2y$12$SMyWWhTOKfgJTly2xOcrEuqYBukWDTJmrj6tZ5bgmPX3zUedlb6re', '01044423574', 'No company', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning\n\nIdea: AI-powered system that dynamically optimizes traffic signals using real-time data from sensors and cameras to reduce congestion, lower emissions, and improve urban mobility in cities like Alexandria.', NULL, 'Developer · Beginner · Computer science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:47', '2026-05-04 20:35:47'),
(354, 'Abdallah Basem Zain', 'abdallahbasemzain@outlook.com', NULL, '$2y$12$RarSNgGHkG70b1p7LfgE7uX6rlAY1qh0DjhRyAizk.bMV60YjOIO2', '01551934703', 'Alamein International University', 'Track: Smart Healthcare\nSkills: AI / Machine Learning;Software Development;Data Science\n\nIdea: MSTE is an emotion-aware AI platform using audio, visual, and biometric data to deliver empathetic, evidence-based mental health support with real-time insights.', NULL, 'Developer · Intermediate · Artificial intelligence science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:48', '2026-05-04 20:35:48'),
(355, 'Shrouq Waleed Saeed Mohamed Hussein', 'shrouqwaleed7@gmail.com', NULL, '$2y$12$3LUCNZeUfOUdDYlN.kGeEe4cUA0B8etHfh4mfWbBcifDhMZr4/NUq', '+201155524719', 'Alamein International University', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Data Science;Software Development;UI/UX Design; IoT / Hardware;Healthcare / Medical\n\nIdea: Egypt loses 0.46 km² of coastline yearly, with 84M people at risk by 2100. Rasid uses satellite imagery and ML to monitor erosion in real-time and deliver early warnings before it’s too late.', NULL, 'Developer · Advanced · Artificial Intelligence', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:48', '2026-05-04 20:35:48'),
(356, 'Mahmoud Mohamed Fathy', 'mahmoud.fathy.2024@aiu.edu.eg', NULL, '$2y$12$WSmI2jZjW4JGaPzalI5HAe.eOHoAUJzKWdPgVhTbp8kPKHlpOi4yq', '01062927729', 'Alalamin international universty', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Data Science;Software Development;UI/UX Design;Healthcare / Medical\n\nIdea: Rasid uses satellite imagery and AI to monitor coastal erosion in Egypt, predict high-risk zones, and provide prioritized solutions to help decision-makers act early and reduce future damage.', NULL, 'Data Scientist · Intermediate · Artificial Intelligence Science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:48', '2026-05-04 20:35:48'),
(357, 'Youssef Adel Boshra', 'youssef.nashed.2024@aiu.edu.eg', NULL, '$2y$12$4sOTnGdDWIPBKJiV7esCpurJOwdiszKgfrHAM3b7NBvtPklq7QmLu', '01202764040', 'Alamein international university', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Software Development\n\nIdea: Rasid uses satellite imagery and AI to monitor coastal erosion in Egypt, predict high-risk zones, and provide prioritized solutions to help decision-makers act early and reduce future damage', NULL, 'Data Scientist · Intermediate · Computer science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:49', '2026-05-04 20:35:49'),
(358, 'Yassin Ahmed Mohamed Kamaleldeen Nasra', 'yassin.kamaleldeen.2024@aiu.edu.eg', NULL, '$2y$12$qHg0tm.YvjA9miETIq5YiuOSeh2Dsxfdv0lqBKsfBcNNw3Fi3q1dO', '01021867005', 'Alamein International University', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Data Science;Software Development\n\nIdea: Egypt is ranked 5th globally as the most economically vulnerable country to sea-level rise losing approximately 0.46 km² of coastline every year with 4.66 km² lost in the past decade alone.', NULL, 'Data Scientist · Intermediate · Computer Science AI Major', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:49', '2026-05-04 20:35:49'),
(359, 'Gamal Khaled Abouelhamd Hussein', 'gamal.hussien.2024@aiu.edu.eg', NULL, '$2y$12$Hrekc5/nckK5Y5PyBjPy/eNUlWOBxRpTxjh6M7Vvk2Mcre9ZdhAs.', '01550332600', 'Alamein International University', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Data Science;Software Development;Business / Marketing; IoT / Hardware\n\nIdea: AI addressing coastline losing.', NULL, 'Data Scientist · Advanced · Artificial Intelligence', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:49', '2026-05-04 20:35:49'),
(360, 'Nour Walid Mohamed Abdelhalim', 'nourwaled245@gmail.com', NULL, '$2y$12$C3LdvZDsVlTJtHycClBoXuxzDW8kGlpaeNR.TgN/O04cWnpjC4TOO', '01026205013', 'Alamein international university', 'Track: Smart Healthcare\nSkills: Software Development;Healthcare / Medical;AI / Machine Learning\n\nIdea: 1. Need for Automation 2. Early Detection and Prevention of Vision Loss. 3. Increased Accuracy Over Manual Assessments 4. False Negatives 5. Patient Empowerment and Education', NULL, 'Developer · Intermediate · Computer science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:50', '2026-05-04 20:35:50'),
(361, 'Abdallah Ahmed Mahmoud Lasheen', 'abdalla.lasheen.2023@aiu.edu.eg', NULL, '$2y$12$XyrB/pOn8Q3v.ixseiyUsu8ct4rEJKgTiVMwXviurCBr76KHcMUSq', '01003527328', 'AIU', 'Track: Smart Healthcare\nSkills: AI / Machine Learning;Healthcare / Medical\n\nIdea: Late diagnosis of AMD/DME causes blindness. Octelligence uses AI to predict disease progression from OCT scans, enabling early intervention to preserve vision and improve patient outcomes.', NULL, 'Researcher · Advanced · AIS', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:50', '2026-05-04 20:35:50'),
(362, 'Mohamed Maher Muossa', 'm0.maher02006@gmail.com', NULL, '$2y$12$hE.i1UXf.hvnEUlcuHxTU.hgVJSsiCvyUUcnwMFqfRkKuP3W.L48W', '01555042359', 'AIU', 'Track: Smart Healthcare\nSkills: Software Development\n\nIdea: ​AI-driven HealthTech platform for mental and chronic care. Features educational content and an AI companion for daily routine advice and therapy-like support to improve patient well-being', NULL, 'Developer · Beginner · Computer Science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:51', '2026-05-04 20:35:51'),
(363, 'Jana Mamdouh Hassan Moustafa Hegazy', 'jana.moustafa.2024@aiu.edu.eg', NULL, '$2y$12$TLVXRU5iRaBwZOX2n8KX2Op.W/0X0xMsH7QOHJFvn9mGj8u2FFCoa', '01211656988', 'Alamein international university', 'Track: Smart Healthcare\nSkills: AI / Machine Learning;Data Science;Software Development;UI/UX Design;Healthcare / Medical\n\nIdea: Anemia affects millions worldwide. This project uses AI, computer vision, and machine learning to analyze eye images for early, non-invasive anemia detection.', NULL, 'Developer · Intermediate · Computer science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:51', '2026-05-04 20:35:51'),
(364, 'Anas Abdelmoneim Khameis Radwan', 'anas.mohamed.2025@aiu.edu.eg', NULL, '$2y$12$F0rJA19GId2ftxON8oTI4.NIg7xO912AUZoKCu09EDY3xavCXnwti', '01554219242', 'Alamein international university', 'Track: Health Promotion\nSkills: AI / Machine Learning;UI/UX Design\n\nIdea: Anemia affects millions worldwide. This project uses Al, computer vision, and machine learning to analyze eye images for early, non-invasive anemia detection.', NULL, 'Designer · Intermediate · Computer engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:51', '2026-05-04 20:35:51'),
(365, 'Jana Hazem Hegazi Mohamed', 'jana.hazem.2024@aiu.edu.eg', NULL, '$2y$12$E1Awr1hzrPpIFo.w7Ke2suDcaZRVqrdDg9i.RHVHVO0aoBuVwjV6q', '01020605479', 'Alamien international university', 'Track: Smart Healthcare\nSkills: AI / Machine Learning;Data Science;Software Development;UI/UX Design; IoT / Hardware\n\nIdea: Anemia affects millions worldwide. This project uses AI, computer vision and machine learning to analyze eye images for early, non-invasive anemia detection.', NULL, 'Data Scientist · Intermediate · computer science and engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:51', '2026-05-04 20:35:51'),
(366, 'Omar Ahmed Rabiee', 'omar.abdulkarim.2025@aiu.edu.eg', NULL, '$2y$12$M6D9WSnUKqY82NWwLIQ35eFu5E8NoyPOcXPxrDSWwLqQDMTX7zeXC', '01115840330', 'Acwad Technology', 'Track: Industry Challenges\nSkills: Software Development\n\nIdea: AI platform connecting restaurants with suppliers to optimize supply chains, predict demand, reduce food waste, and improve inventory efficiency.', NULL, 'Business Lead · Intermediate · Computer Engineering / Software Engineer', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:52', '2026-05-04 20:35:52'),
(367, 'Kerolos Nader', 'kfparody809@gmail.com', NULL, '$2y$12$4jaVN0ExpFgyoEEe.O.S3.iMKF3brveef2zAVxJCt8UxPP.ACJ5ZG', '01032564982', 'Alamein International University', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Data Science\n\nIdea: AI-powered traffic management system for Egypt’s North Coast. Predicts congestion hotspots using ML, shows real-time heatmaps, and provides smart alerts for admins and route suggestions for guests.', NULL, 'Developer · Intermediate · Computer science and engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:52', '2026-05-04 20:35:52'),
(368, 'Hana Sherif Abdelmoneim Mohamed Elenshasy', 'hana.elanshassy2@gmail.com', NULL, '$2y$12$kEgzeBgMe3q2wDuBLFtcMe4Fe9sIJ0pnCP4LYGHMisLlFPb8peB5e', '01211876634', 'Alamein International University', 'Track: Smart Healthcare\nSkills: AI / Machine Learning;Software Development;Data Science\n\nIdea: Crowded coastal cities face sudden health risks with no unified medical data. Care is reactive, not predictive leading to delayed response, overloaded clinics, and preventable emergencies', NULL, 'Data Scientist · Intermediate · Artificial Intelligence Science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:52', '2026-05-04 20:35:52'),
(369, 'Ammar Mahmoud Eid Ahmed', 'ammar.ahmed.2024@aiu.edu.eg', NULL, '$2y$12$a5UkC/bnKvZ4cOExyaXCLeaoiWZ4JZ1pE6A47xErdyzT1dlxMsTFi', '01062065198', 'AIU', 'Track: Smart Healthcare\nSkills: Software Development;AI / Machine Learning;UI/UX Design;Business / Marketing;Software engineering , Software Testing\n\nIdea: Crowded coastal cities face sudden health risks with no unified medical data. Care is reactive, not predictive leading to delayed response, overloaded clinics, and preventable emergencies', NULL, 'Designer · Intermediate · Software engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:53', '2026-05-04 20:35:53'),
(370, 'Hany Ziad Abdelaal Mohamed', 'hany.mohamad.2024@aiu.edu.eg', NULL, '$2y$12$Di/dBO17avDe5/lkDqJNW.0OiAxDkaL2unyghFvgByg7lx7fvV5z.', '01275316703', 'Alamein International University', 'Track: Smart Healthcare\nSkills: AI / Machine Learning;Data Science;Software Development\n\nIdea: crowded coastal cities face sudden health risks with no unified medical data. Care is reactive, not predictive leading to delayed response, overloaded clinics, and preventable emergencies', NULL, 'Data Scientist · Advanced · Artificial intelligence science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:53', '2026-05-04 20:35:53'),
(371, 'Mostafa Amr Adly Hassan Adham', 'mostafaamr14@gmail.com', NULL, '$2y$12$KPWP82SZbx7nunh9Cdn7WOasTYpP6EEBOExr.WrHc33xhC.4d9br2', '01286832456', 'PharmaChain AI', 'Track: Smart Healthcare\nSkills: Healthcare / Medical;Business / Marketing\n\nIdea: PharmaChain AI is an intelligent platform that connects pharmacies with suppliers through a unified digital ordering system. With an integrated smart forecasting solution.', NULL, 'Designer · Advanced · Pharmacy', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:54', '2026-05-04 20:35:54'),
(372, 'Mohamed Saeed Mohamed', 'mohamed.abdelwahab@aiu.edu.eg', NULL, '$2y$12$fkTm/oQVxTYV4SUeJRve5.CRrrq7bKTYvrXNh883ytfLFIn6QV/RK', '01501690011', 'Allmanda Cosmetics', 'Track: Smart Healthcare\nSkills: Business / Marketing;Healthcare / Medical\n\nIdea: Natural products for skin and hair care with economic value', NULL, 'Business Lead · Intermediate · Pharm D', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:54', '2026-05-04 20:35:54'),
(373, 'Nour Maged Ahmed', 'nourmaged208@gmail.com', NULL, '$2y$12$1zT/r0YMy9HULbOt8tCBLeWBDZjtzBbbBz6r4/NRMbp41nPuF3hdG', '01272298808', 'جامعه العلمين', 'Track: Health Promotion\nSkills: Healthcare / Medical\n\nIdea: .', NULL, 'Designer · Intermediate · صيدلة', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:54', '2026-05-04 20:35:54'),
(374, 'Shimaa Elsaied Ibrahim Muhammad', 'shaimaa.mohamed.2024@aiu.edu.eg', NULL, '$2y$12$e7K4gRN4gc2oOGrnKY3.0.W3IY9y/wXEO9TRaxjUZLGeFgbQps/y.', '01116429774', 'Alamein international University', 'Track: Health Promotion\nSkills: Healthcare / Medical\n\nIdea: .', NULL, 'Researcher · Intermediate · Clinical pharmacy', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:55', '2026-05-04 20:35:55'),
(375, 'Zeinab Ahmed Ebrahim Osman Ebrahim', 'zeinab.osman.2025@aiu.edu.eg', NULL, '$2y$12$K6brHemz90XX1vOoh.tRJutWnIgplgKcl/TEZP5Qv8bJz/8s.QuXC', '01204910524', 'Alamein International University', 'Track: Smart Healthcare\nSkills: AI / Machine Learning;Healthcare / Medical;Data Science;Business / Marketing\n\nIdea: Smart AI-powered wearable band that detects drowning or distress in real time and alerts lifeguards with location, enabling faster response and improving beach safety in crowded areas.', NULL, 'Researcher · Intermediate · Biomedical Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:55', '2026-05-04 20:35:55'),
(376, 'Mazen Mohamed El-Sayed Abdelsalam', 'mazen.abdelsalam.2024@aiu.edu.eg', NULL, '$2y$12$7And1ljcKK1.m3E9FBE/D.uV1qAumHzYiHuK0CWvU9Vl7mTdraTru', '01229427498', 'Al Alamein International University', 'Track: Smart Healthcare\nSkills: 3D Modeling - programming - CAD Softwares - Microsoft Office\n\nIdea: Smart AI-powered wearable band that detects drowning or distress in real time and alerts lifeguards with location, enabling faster response and improving beach safety in crowded areas', NULL, 'Data Scientist · Intermediate · Mechatronics Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:56', '2026-05-04 20:35:56'),
(377, 'Basmala Samir Mustafa', 'basmala.samir.2025@aiu.edu.eg', NULL, '$2y$12$EbZeODzO2hB6jp3bptySduQWQHTK7PTrNNSKWr9KndLLGMlEtVmM2', '01095885753', 'Alamein International Univercity', 'Track: Industry Challenges\nSkills: AI / Machine Learning;Data Science;Software Development\n\nIdea: The idea revolves around an AI-powered virtual work environment where students solve real tasks, collaborate with AI teammates, and gain practical job-ready skills through simulation.', NULL, 'Data Scientist · Intermediate · AI Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:56', '2026-05-04 20:35:56'),
(378, 'Youssef Mohamed Moharm Mohamed', 'yy4472285@gmail.com', NULL, '$2y$12$M.LkI71EiInh3sxpfdwdXe0rzWTp78Npr9qj5np.vU3aCqWrPS5MG', '01229392276', 'Alamein international university', 'Track: Smart Healthcare\nSkills: AI / Machine Learning;Data Science;Software Development;UI/UX Design;Healthcare / Medical\n\nIdea: Millions delay eye disease diagnosis due to limited access to doctors and neglect. Our AI detects early eye conditions and connects users to nearby doctors and pharmacies instantly', NULL, 'Data Scientist · Intermediate · Computer Science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:56', '2026-05-04 20:35:56'),
(379, 'Nourhan Nasr Mohammed', 'nourhan.gamal.2026@aiu.edu.eg', NULL, '$2y$12$JBAmRdT/2ip7zPL9Oufu8.rDaVPWH9kngOBgJQsx2q1PKBKo/YWQu', '+201110494037', 'Alamian international University', 'Track: Health Promotion\nSkills: Software Development;UI/UX Design; IoT / Hardware;Business / Marketing\n\nIdea: Health apps feel repetitive and isolating, causing users to lose motivation. Our solution is a gamified social platform with challenges and rewards to make healthy habits engaging.', NULL, 'Developer · Intermediate · computer Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:57', '2026-05-04 20:35:57'),
(380, 'Mohamed Mohamed Alsariti', 'mohamed.alsariti.2023@aiu.edu.eg', NULL, '$2y$12$S8myvMkmJJCizkiaPbxMeOpNePlc7UQp9OpW20el0o9nG36PWQx5O', '01094868049', 'Aiu', 'Track: Smart Cities & Urban Innovation\nSkills: Software Development;UI/UX Design;Business / Marketing\n\nIdea: A real-time mobile app tracking buses, shuttles & light rail via GPS. Features live arrivals, occupancy alerts, trip planning & smart sensor integration for seamless, sustainable urban mobility.', NULL, 'Developer · Advanced · computer science (software)', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:57', '2026-05-04 20:35:57'),
(381, 'Abdalla Tarek', 'abdalla.ebrahim.2025@aiu.edu.eg', NULL, '$2y$12$SFtS5c30N58eh9KfP9GlDujAGFYhb2qU5mws4gyT73dOh.QfLszbu', '01069490031', 'Aihack', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Software Development\n\nIdea: An AI-powered smart traffi management system that uses cameras and sensors to reduce traffic congestion, optimize traffic lights, and improve emergency response time.', NULL, 'Data Scientist · Intermediate · Ai', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:58', '2026-05-04 20:35:58'),
(382, 'Rowan Mohamed Yehia Aly', 'rowan.elsayed.2023@aiu.edu.eg', NULL, '$2y$12$7lJyHmkh8EPW0nv7HPSmi.2UGRREAY1KVxV2jRBlFLI1bnotfTBaO', '01022850683', 'Alamein international University', 'Track: Smart Healthcare\nSkills: Data Science;Business / Marketing;Healthcare / Medical;AI / Machine Learning\n\nIdea: Smart BioBasket: A smart framework that classifies urban organic waste at the source. It evaluates waste for bio-conversion and provides real-time data to optimize city logistics and sustainability.', NULL, 'Researcher · Intermediate · faculty of Basic Sciences Department of Molecular Biotechnology (with interest in AI applications in Smart Cities & Sustainability)', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:58', '2026-05-04 20:35:58'),
(383, 'Rodaina Mohamed Mustafa Hassan Ali', 'rodaina.hassan.2024@aiu.edu.eg', NULL, '$2y$12$5bkk/XQBs4tK0./1V/stFeMOaWlVXij3yp2sK/OPJShhL7rMLD80m', '01281877442', 'Al Alamein International University', 'Track: Health Promotion\nSkills: AI / Machine Learning;Software Development;Data Science;Healthcare / Medical\n\nIdea: VitalSync is an AI-powered preventive healthcare platform that unifies health data, detects risks early, and provides personalized insights to improve long-term wellness.', NULL, 'Data Scientist · Intermediate · Artificial Intelligence Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:58', '2026-05-04 20:35:58'),
(384, 'Hamdy Sameh Hamdy', 'hamdy.hendawy.2024@aiu.edu.eg', NULL, '$2y$12$uighMNwPb7WVw4x4sEnZJO5bLAPBpUU6x9VWTnFOtfjffHJWj9J1.', '01147018349', 'Aoun', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Software Development;Business / Marketing;Data Science\n\nIdea: J', NULL, 'Developer · Intermediate · Computer science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:59', '2026-05-04 20:35:59'),
(385, 'Mohamed Abdelhady Mohamed Elsogher', 'mohamed.hady.dev@gmail.com', NULL, '$2y$12$3jMZ4/0TfsfW0EKMWVmdw.eE4T.qlQdxIpJy3Y8cpPFSsD9.S/dva', '01070129735', 'student at Alamein International University (AIU)', 'Track: Smart Cities & Urban Innovation\nSkills: Software Development;Business / Marketing;AI / Machine Learning\n\nIdea: Aoun is an on-demand app connecting customers with verified workers for customized tasks like cleaning and pet care. We offer flexible services, fair pricing, and secure jobs for skilled workers', NULL, 'Developer · Beginner · Computer science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:35:59', '2026-05-04 20:35:59'),
(386, 'Ibrahim Elsayed Elrouby', 'ibrahim_alsaied@outlook.com', NULL, '$2y$12$vkq4oWg3DcGINKw8QS4c7.XAiXEjx2Om/Jvz2jm8PNiWajL0hx7ae', '+201102864081', 'AIU', 'Track: Smart Cities & Urban Innovation\nSkills: UI/UX Design;Software Development;Business / Marketing;AI / Machine Learning\n\nIdea: Using AI to automate and optimize urban planning and construction workflows', NULL, 'Developer · Intermediate · Interior Arch.', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:00', '2026-05-04 20:36:00'),
(387, 'Mohamed Ayman Mohamed Morad', 'moradayman1880@gmail.com', NULL, '$2y$12$6FfVGphYq4v8f.tvJxDINu5lIbmpso/b.jsbPJrLEUT7bDx7GT2BG', '01001970932', 'Aiu', 'Track: Smart Cities & Urban Innovation\nSkills: Business / Marketing\n\nIdea: Creating tools with ai for better things', NULL, 'Developer · Beginner · Accounting information system', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:00', '2026-05-04 20:36:00'),
(388, 'Yousef Maher Elnajar', 'yousef.maher.alnajar@gmail.com', NULL, '$2y$12$DWENdrQu7nZ5EU3fi8uE.eX8X1DZ.buHXvXzLEvulkl3.dbnKV1q2', '01013379185', 'N', 'Track: Industry Challenges\nSkills: AI / Machine Learning;Data Science;Business / Marketing\n\nIdea: Automation of tasks that takes immense work and money will be automated through a workflow , whatever the task is the ai will do it', NULL, 'Business Lead · Intermediate · N', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:00', '2026-05-04 20:36:00'),
(389, 'Bassant Ekramy Ebrahim Kholif', 'bassant.kholif.2025@aiu.edu.eg', NULL, '$2y$12$CrvhEKUhMT9vEnEorxYnzurAXRphK7QxEzfQMWLncJlBWk4KpNPXK', '01287075929', 'Alamein International University', 'Track: Smart Healthcare\nSkills: AI / Machine Learning\n\nIdea: Misdiagnosis by doctors : I input all my symptoms into an AI model that constantly monitors my health and psychological state, enabling it to prescribe medications more effectively.', NULL, 'Developer · Beginner · Computer science and engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:01', '2026-05-04 20:36:01'),
(390, 'Ethar Ahmed Mohamed Abdeltawab', 'ethar.abdeltawab.2024@aiu.edu.eg', NULL, '$2y$12$cXrJVqENQHqCn.Zoy.zhcex5Ub5OFLQKTWK4Sj./uIlFG6fqNba2.', '01207361087', 'Alamein International University', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Software Development;Business / Marketing;Data Science; IoT / Hardware\n\nIdea: Monitoring of Resources (Water, Energy, Waste) New Alamein City lacks a fully integrated system to monitor and manage essential resources such as water, energy, and waste in real time.', NULL, 'Researcher · Intermediate · Artificial intelligence science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:01', '2026-05-04 20:36:01'),
(391, 'Zeinab Ashraf Elsayed Abbas', 'zeinabashraf836@gmail.com', NULL, '$2y$12$ebi.NycrMSgNcd5QLkROTedSQHDzlUpiwaFNZdDgAMEspXAi1rP/K', '01094446036', '.', 'Track: Smart Healthcare\nSkills: Healthcare / Medical\n\nIdea: An AI app evaluates bleeding from text or images, provides first aid instructions, locates hospitals, and alerts emergency services if needed, while improving over time using anonymized data.', NULL, 'Beginner · Clinical pharmacy', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:02', '2026-05-04 20:36:02'),
(392, 'Yehia Mohamed Ahmed', 'yehia.hassan.2025@aiu.edu.eg', NULL, '$2y$12$d9t2of5fK6ZgH2XhIHeqIOroQb3V6a.IVMhsxDc/jkEvKBhqQwtYa', '01016805916', 'Aiu', 'Track: Industry Challenges\nSkills: Business / Marketing\n\nIdea: N/A', NULL, 'Intermediate · Business', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:02', '2026-05-04 20:36:02'),
(393, 'Abdelrhman Wael Mohamed', 'abdelrahman.abdrabo.2025@aiu.edu.eg', NULL, '$2y$12$KUB7deTPaYWLuCRZX8R3ReJZJssqFGlAA3qqce2yRzB8BAH3M.Bk.', '01283693349', 'Alamein international university', 'Track: Industry Challenges\nSkills: AI / Machine Learning;Healthcare / Medical; IoT / Hardware\n\nIdea: Developing an IoT-integrated AI system that uses regression algorithms to predict industrial equipment failure, reducing downtime and maintenance costs through real-time sensor data analysis', NULL, 'Intermediate · Computer science (Ai)', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:02', '2026-05-04 20:36:02'),
(394, 'Aya Hossam Khalaf Abdelhakeem', 'ayahossam142006@gmail.com', NULL, '$2y$12$F0RMlAdXy6IQLQ8CNJmYkenCzR6iJ6gW1kuV2hKbTYHvxXerh4Ywe', '01010736429', 'Alamein International University', 'Track: Smart Healthcare\nSkills: Business / Marketing;Healthcare / Medical\n\nIdea: ..', NULL, 'Intermediate · sciences', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:03', '2026-05-04 20:36:03'),
(395, 'Ghada Hamdy Badr', 'ghada.fathalla.2024@aiu.edu.eg', NULL, '$2y$12$1QR3fXoYxUFMVE00SoBn3uU8b35PQ6VFbXs2wwXcQTi5UaZRbP/ve', '01211354641', 'AIU', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Software Development\n\nIdea: An AI platform to analyze real-time city data, reduce traffic congestion, optimize energy use, and provide citizens with live updates for smarter, more efficient urban living.', NULL, 'Developer · Intermediate · computer engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:03', '2026-05-04 20:36:03'),
(396, 'Marwan Hossam Eldin Ismail', 'marwan.ismail.2024@aiu.edu.eg', NULL, '$2y$12$g3oaLK6P8h8s2INRkVQMce3hvXkI31eAHs4VqoKfiyXrP29SI6xme', '01010011149', 'Alamein international university', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Data Science; IoT / Hardware\n\nIdea: Traffic congestion and energy waste reduce city efficiency. An AI-powered IoT system can monitor, analyze, and optimize urban systems in real time.', NULL, 'Intermediate · AI Computer engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:03', '2026-05-04 20:36:03'),
(397, 'Omarhany54 Hany', 'ohmarha5554@gmail.com', NULL, '$2y$12$HwznCTtsb2iweYexqBMtQe5QitYuHII29pq0wiC.tQ5yFzTnGprTS', '01285552868', 'Alamein university', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning\n\nIdea: .', NULL, 'Researcher · Intermediate · Computer science AIS', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:04', '2026-05-04 20:36:04'),
(398, 'Walaa Mohamed Ragaey', 'wlaa.abdelmged.2022@aiu.edu.eg', NULL, '$2y$12$EbBNQPBjHNIkDlaaQIorFe41qskzmssn0mJxrhc7tiobtCt9Jj6t6', '01140311585', 'AIU', 'Track: Smart Healthcare\nSkills: Healthcare / Medical;Business / Marketing\n\nIdea: The gap between patients and doctors The gap meaning that patients information and their history which influence in their current health problems So i want to make a systems collect all patient data', NULL, 'Business Lead · Beginner · Pharmacy', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:04', '2026-05-04 20:36:04'),
(399, 'Tamer Sameh Youssef Saleeb', 'tamermatar9@gmail.com', NULL, '$2y$12$0ux7JmEWhmR7W6KyRYm1QONvfdZXtpcNZyGaSw6XRWyrMyn2d9cJS', '01555391042', 'AIU', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Data Science;Software Development;UI/UX Design; IoT / Hardware\n\nIdea: Sentinel Mesh is an integrated hardware and software solution that combines a LoRa-based mesh network with Edge AI to provide secure, real-time urban and industrial analytics.', NULL, 'Intermediate · Faculty of Computer Science and Engineering: Field of Cybersecurity', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:04', '2026-05-04 20:36:04'),
(400, 'Mahmoud Ahmed Mahmoud Mohamed Morsy', 'mahmoud.morsy.2026@aiu.edu.eg', NULL, '$2y$12$gNgyFzSam4gEp9EF5BoOyO9rhid4D1mu9HosrgenBtf.9NSHc2z8.', '01223638506', 'alamien international universty', 'Track: Smart Cities & Urban Innovation\nSkills: Business / Marketing\n\nIdea: .', NULL, 'Business Lead · Intermediate · business administration', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:05', '2026-05-04 20:36:05'),
(401, 'Amr Mohamed Kerwash', 'amramrkerwash@gmail.com', NULL, '$2y$12$xOFEjNErOzir3lxzRw1PEeye4xDWrGGLHnfifi6rvtAbRoVTHFqzK', '01104898075', 'AIU (Alamein International University)', 'Track: Smart Cities & Urban Innovation\nSkills: Business / Marketing\n\nIdea: Smart parking system using sensors and a mobile app that shows real-time available spots, reduces traffic congestion, and saves drivers time in crowded cities.', NULL, 'Researcher · Beginner · Computer science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:05', '2026-05-04 20:36:05'),
(402, 'Hassan Yasser Elgouhary', 'hassan.ali.2025@aiu.edu.eg', NULL, '$2y$12$rnG8SKfCZ.tKyeJ26CNVF.o195YcKg0Qxedm01aq9hISObF8DzWnu', '01066741454', 'Hassan yasser', 'Track: Smart Healthcare\nSkills: Software Development\n\nIdea: Smart Healthcare uses technology to monitor patients in real time, track vital signs, and alert doctors. It improves access, enables early diagnosis, and enhances efficient, continuous patient care', NULL, 'Developer · Intermediate · Faculty of computer science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:05', '2026-05-04 20:36:05'),
(403, 'Basmala Ayman Abdelwhab', 'basmala.ayman.2025@aiu.edu.eg', NULL, '$2y$12$AmzlOshwQ9RnYHgMghofyesBsfH8a7KuzKaOOFoQX32hzpvEq3WgO', '01123190195', 'Alamien international university', 'Track: Industry Challenges\nSkills: Business / Marketing;Data Science; IoT / Hardware\n\nIdea: .', NULL, 'Beginner · Electronics and Communications Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:06', '2026-05-04 20:36:06'),
(404, 'Shaimaa Khalil Mohamed Khalil Ibrahim', 'shaimaa.azizeg@gmail.com', NULL, '$2y$12$yXgxh2EVY4LLZh1Y/re7b.AzRwjSXgvIc4UZ8VEn7uGrEDXXRSofW', '01003296854', 'Alamein interesting university', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Interior design using Ai\n\nIdea: Consumption in alamein is very high at summer and no recycling facilities or sustainable development or housing that could support the city at all and the urban environment. The city is disorganized.', NULL, 'Designer · Intermediate · Arts and design/ interior designer', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:06', '2026-05-04 20:36:06'),
(405, 'روجيه جورج نعيم جرجس يوسف', 'rogernam4@gmail.com', NULL, '$2y$12$0xoUjX..PDtApRWVLztEfOka2Q5Imd1m0liS1DgFxG.CUIfTgM9GW', '01280167862', 'concentrix', 'Track: Smart Cities & Urban Innovation\nSkills: Business / Marketing;language\n\nIdea: Many individuals and businesses struggle with traffic congestion , leading to inefficiencies, lost time, and missed opportunities. Existing solutions are often big Streets', NULL, 'Business Lead · Beginner · french language / computer engineer', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:06', '2026-05-04 20:36:06'),
(406, 'Muhammad Nasr Houssien', 'muhammad.ahmed.2026@aiu.edu.eg', NULL, '$2y$12$op.UiYwrrUPmE2OzZsvsOuxfitnU28aFD5SDSwYcnUVA64ONDgtlC', '01097704138', 'Alamein International University', 'Track: Industry Challenges\nSkills: AI / Machine Learning;Software Development;UI/UX Design\n\nIdea: Manufacturing robots and devices that help promote community and environmental health', NULL, 'Beginner · Computer Science and Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:07', '2026-05-04 20:36:07'),
(407, 'Youhana Mobdea William', 'youhanamobdea@gmail.com', NULL, '$2y$12$jVzubY8FDj.yD9u7KXRDnOeFXGCDjVfRpl.sE5x8a/AB6ls.t2u4u', '01223673672', 'alamein international university (aiu)', 'Track: Industry Challenges\nSkills: IoT / Hardware;AI / Machine Learning\n\nIdea: .kfffffffffff', NULL, 'Developer · Beginner · computer science (software engeneering)', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:07', '2026-05-04 20:36:07'),
(408, 'Yousef Mohamed Fouad', 'youssef.fouad.2026@aiu.edu.eg', NULL, '$2y$12$U1guRBI/Xri8FBaZ87uaLe.U7uAh1shhWzNcJcXQlBVCf5v0L0Po2', '01212671057', 'Aiu', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Software Development;Business / Marketing\n\nIdea: I don\'t know more but I well go a head I have a good experience in java and Ai', NULL, 'Developer · Beginner · Computer science and Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:07', '2026-05-04 20:36:07'),
(409, 'Abdallah Saad Ali', 'abdallah.abdelkhalk.2023@aiu.edu.eg', NULL, '$2y$12$AnXm/j9Qeh.bY8fyHxS/huCN/MJEXstT0f9GBNTRoHPKXDXhqPKSm', '01030156634', 'Aiu', 'Track: Industry Challenges\nSkills: Healthcare / Medical\n\nIdea: .', NULL, 'Researcher · Beginner · PharmaD', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:08', '2026-05-04 20:36:08'),
(410, 'Sameh Shawky Abdelaziz Khalil', 'sameh.abdelaziz.2023@aiu.edu.eg', NULL, '$2y$12$ujApzHMpe8jFM4FO61i/1e3EwTSCrDSEMqHQyKtSx9JG2hgD.uaHW', '01044338685', 'AIU', 'Track: Smart Healthcare\nSkills: Healthcare / Medical;AI / Machine Learning\n\nIdea: To train an AI model to early predict occurrence of febrile neutropenia in cancer patients that are taking chemotherapy', NULL, 'Researcher · Intermediate · Clinical PharmD', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:08', '2026-05-04 20:36:08'),
(411, 'Mahmoud Elsjerbiny Hassan Ali', 'mahmoud.ali.2024@aiu.edu.eg', NULL, '$2y$12$twhDvTsXDS6GY8mi4UoQB.s5br3mGvA6d9ElSrtucQax434KXHPQy', '01555309651', 'Alamein International University AIU', 'Track: Health Promotion\nSkills: Healthcare / Medical; IoT / Hardware;Business / Marketing\n\nIdea: .', NULL, 'Designer · Intermediate · Biomedical Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:08', '2026-05-04 20:36:08'),
(412, 'Fady Salama Goda Mahmoud', 'fady.mahmoud.2025@aiu.edu.eg', NULL, '$2y$12$PUSGCYF35hf9PTUzMDvEueG.wAt79QSLeYLCSRWkFxtQBfcVLc2Cy', '01229620837', 'AQUA EGYPT', 'Track: Health Promotion\nSkills: Business / Marketing\n\nIdea: بسبب ندرة المياه للفرد على مستوى الدولة تواجه القرى السياحيه و الفنادق فى توفير المياه للمسطحات الخضراء وده بيزود التكلفه و الفكره أننا نبنى محطة معالجة مياه ، تعالج مياه الصرف تكون صالحه للزراعه', NULL, 'Developer · Intermediate · بدرس إدارة اعمال / بشتغل فى محطات معالجة المياه', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:09', '2026-05-04 20:36:09'),
(413, 'Mariam Nady Kamal', 'mariam.danial.2024@aiu.edu.eg', NULL, '$2y$12$p6kK5T/TG/jtldFYUwUTvuKYAfXNzUXNg4bmekSMsb5QncEREIY0S', '01273245305', 'Alamein International University (AIU)', 'Track: Health Promotion\nSkills: AI / Machine Learning\n\nIdea: Developing a Rule-Based Expert System for medical classification. It uses Robbins Basic Pathology to transform clinical findings into logic-driven diagnostic suggestions.', NULL, 'Researcher · Advanced · Ai', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:09', '2026-05-04 20:36:09'),
(414, 'Adham Sabry Mohamed Sayed Mansour', 'adham.mansour.2026@aiu.edu.eg', NULL, '$2y$12$W.Jbh38Ytfp6nCUt/1AN4eMdINxtkf0p951U4L.BN8xPeMrFK8ZTa', '01210438018', 'AIU', 'Track: Smart Healthcare\nSkills: AI / Machine Learning;Software Development;Business / Marketing\n\nIdea: Rain turbines in pipes for power, then filtering for safe drinking water.', NULL, 'Business Lead · Beginner · Advanced Basic Sciences', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:09', '2026-05-04 20:36:09'),
(415, 'Mariam Mohamed Abd Elrady', 'mariam.abdelrady.2026@aiu.edu.eg', NULL, '$2y$12$cYzZnLm4cKNaWTSHFJxT0Oe98cIA7oDs6Pg6vkMTvONYkeglQjtlS', '01225503525', 'Aiu', 'Track: Smart Cities & Urban Innovation\nSkills: Business / Marketing\n\nIdea: .', NULL, 'Beginner · Advanced basic science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:09', '2026-05-04 20:36:09'),
(416, 'Rodayna Muhammed', 'rodayna.amin.2026@aiu.edu.eg', NULL, '$2y$12$fdUxpOwLr6GhOwM8GnaLRu.ZovAnYl94A/kJzwtfBz9xiGNkptIgS', '01211462321', 'Aiu', 'Track: Smart Cities & Urban Innovation\nSkills: UI/UX Design\n\nIdea: أرغب في العمل على علي تنظيم الحياه اليوميه', NULL, 'Designer · Beginner · Art& dasign', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:10', '2026-05-04 20:36:10'),
(417, 'Yassine Yasser', 'yassine.tawfik.2025@aiu.edu.eg', NULL, '$2y$12$Gm.fgSUu811lbX5LBoen0.kb.j2o8xXRpPiDuH9QKrZf5OqCPxUtu', '01220608089', 'Aiu', 'Track: Smart Cities & Urban Innovation\nSkills: Business / Marketing;AI / Machine Learning;Software Development\n\nIdea: .', NULL, 'Developer · Beginner · Computer engineer', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:10', '2026-05-04 20:36:10'),
(418, 'Shahd Mohamed Monir', 'shahd.muhamed.2025@aiu.com', NULL, '$2y$12$t7JyOJCT4F9D3v6zAtxcqelScafVqsXBD3lCcYB6CYG4l/SEUsLRO', '01204362424', 'جامعة العلمين', 'Track: Smart Cities & Urban Innovation\nSkills: Business / Marketing\n\nIdea: إدارة القمامة: تواجه العديد من المناطق في مصر مشكلة في جمع وإدارة النفايات، مما يؤدي إلى تراكم القمامة وانتشار التلوث. ويرجع ذلك إلى نقص الأنظمة الذكية لمتابعة الحاويات وتنظيم عمليات الجمع بكفاءة.', NULL, 'Beginner · هندسه عماره', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:10', '2026-05-04 20:36:10'),
(419, 'Mohamed Ramadan El Feky', 'mohamed.fathi.2026@aiu.edu.eg', NULL, '$2y$12$013NESP0Mnet.4CqF1jkueFneRfJxfghnX9JmhYcw9jeGDpVfjbFm', '01050475766', 'AIU', 'Track: Smart Cities & Urban Innovation\nSkills: Business / Marketing\n\nIdea: A smart waste management system using AI and sensors to optimize collection routes and reduce environmental pollution', NULL, 'Researcher · Intermediate · Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:11', '2026-05-04 20:36:11'),
(420, 'Nour Al Din Aly', 'nouraldin.ali.2026@aiu.edu.eg', NULL, '$2y$12$cFZTCVulSgc8T4y/bK9XHOXMKcNTSHVgZFBdzv9ymi5A0t7LuF./O', '01118745080', 'Alamein international university', 'Track: Smart Cities & Urban Innovation\nSkills: Business / Marketing\n\nIdea: Implementing a decentralized, AI-driven smart grid for street lighting that adjusts intensity based on real-time pedestrian and vehicle traffic to maximize energy savings.', NULL, 'Business Lead · Intermediate · Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:11', '2026-05-04 20:36:11'),
(421, 'Osama Ahmed Ganer Hassan', 'osama.hassan.2023@aiu.edu.eg', NULL, '$2y$12$1duwQR4cg/KNp1S6SRNA5epTFif5K90NdchOkYsb3qfCf5Nma18Me', '01281842765', 'Aiu', 'Track: Smart Cities & Urban Innovation\nSkills: Business / Marketing\n\nIdea: AI-powered smart home system that optimizes energy usage using IoT sensors and predictive models. It reduces consumption, lowers costs, and supports smart city sustainability.', NULL, 'Developer · Intermediate · Mechatronics', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:11', '2026-05-04 20:36:11'),
(422, 'Ahmed Ramzy Mohamed', 'ahmedramzy43e43@gmail.com', NULL, '$2y$12$DCa4iliAvr2pXszn4IhXquoWfyyPB1ET129xtjZOaTHd/QeLQsFjq', '01091576626', 'AIU', 'Track: Smart Cities & Urban Innovation\nSkills: Architect\n\nIdea: .', NULL, 'Designer · Beginner · Architect', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:12', '2026-05-04 20:36:12'),
(423, 'Mohamed Ali Al-Sokary', 'mohamed.alsokary.2022@aiu.edu.eg', NULL, '$2y$12$/FAQZRZudpfWPcp1nDJddeoppC3Kcwkv/Qgs6WC3sZMnKB430Ii9S', '01060437812', 'AIU', 'Track: Smart Healthcare\nSkills: Healthcare / Medical;AI / Machine Learning;Software Development;Business / Marketing\n\nIdea: .', NULL, 'Researcher · Intermediate · Pharmacy', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:12', '2026-05-04 20:36:12'),
(424, 'Hala Dweir', 'halaamgaddweir@gmail.com', NULL, '$2y$12$f/BmUZNOlENzwP.Ldnok8uTWWQg50eXJDdiXvD8N4sDNRIeEuuLOy', '+20 155 650 4577', 'Hla', 'Track: Smart Cities & Urban Innovation\nSkills: UI/UX Design;Software Development\n\nIdea: ….', NULL, 'Developer · Beginner · Cs', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:12', '2026-05-04 20:36:12'),
(425, 'Noreen Osama Youssry Abdelwahab', 'noreen.abdelwahab.2024@aiu.edu.eg', NULL, '$2y$12$BhWo/7m4ogGzGvSwHH0adu4MZfWQtYbGtiCb.d.JzYsIogSauAuXi', '01559733797', 'Alamein International University', 'Track: Smart Healthcare\nSkills: AI / Machine Learning;Data Science\n\nIdea: Crowded coastal cities face sudden health risks with no unified medical data. Care is reactive, not predictive, leading to delayed response, overloaded clinics, and preventable emergencies', NULL, 'Business Lead · Intermediate · AI', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:13', '2026-05-04 20:36:13'),
(426, 'Khaled Ashraf Mohamed Elgreitly', 'khaled.elgritly@aiu.edu.eg', NULL, '$2y$12$Bc3ZoEzgipjEspJgbqs9M.CCBBGHlXBUMyi7vJpFQ/.YpDh6z30Oy', '01210581313', 'aiu', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Software Development;UI/UX Design\n\nIdea: AI smart city system using my improved ( YOLO++ ) + Faster R-CNN hybrid model for real-time traffic, accident, parking, and surveillance detection with high speed and accuracy.', NULL, 'Researcher · Intermediate · computer science in software engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:13', '2026-05-04 20:36:13'),
(427, 'Abdelmonem Walid Abdelmonem Ebrahim Fahmy', 'abdelmoneim.fahmy.2023@aiu.edu.eg', NULL, '$2y$12$rdL3pLyBTCeciKPsMk6BneRji4I42fyPhSuWsXNfkAiC1bl/DMAa.', '01285649719', 'Faculty of Pharmacy, Alamein University', 'Track: Smart Healthcare\nSkills: AI / Machine Learning;Business / Marketing;Healthcare / Medical\n\nIdea: Using AI to improve medication safety, reduce prescription errors, and enhance patient care in healthcare systems.', NULL, 'Business Lead · Beginner · Pharmacy', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:13', '2026-05-04 20:36:13'),
(428, 'Tasneem Hesham Abdelwanes Hashem Temraz', 'tasneem.abdelwanes.2023@aiu.edu.eg', NULL, '$2y$12$tJxXQ9NcRsQfycZCK1IcXugDfTsAWDGLbipajpxdYbj1rsPpNvZUK', '01276791136', 'Liminal (Graduation Project)', 'Track: Health Promotion\nSkills: Interior & Spatial Design\n\nIdea: LIMINAL is a healing space for women after cancer, using interior design to rebuild confidence through calming environments, sensory design, and therapeutic spaces.', NULL, 'Intermediate · Atrs & Design (interior Design)', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:14', '2026-05-04 20:36:14'),
(429, 'Ranaa Ahmed Abdelsalam Elzanaty', 'ranaa.elzanaty.2025@aiu.edu.eg', NULL, '$2y$12$JTalxVncSrtDi215G07QVOv.8PFRg92hHDPpS7kThA9VUyzJwvnRi', '01143831208', 'Alamein International University', 'Track: Smart Healthcare\nSkills: IoT / Hardware;AI / Machine Learning;Business / Marketing;UI/UX Design\n\nIdea: Smart AI-powered wearable band that detects drowning or distress in real time and alerts lifeguards with location, enabling faster response and improving beach safety in crowded areas', NULL, 'Business Lead · Intermediate · Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:14', '2026-05-04 20:36:14');
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `phone`, `institution`, `bio`, `avatar_path`, `headline`, `primary_role`, `social_links`, `registration_status`, `requested_role`, `approved_at`, `national_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(430, 'Ahmed Ezzelragal Abdallah Salama', 'ahmed.taha.2024@aiu.edu.eg', NULL, '$2y$12$ZVRPEosbPK8LxZ6ve7r.Pe4Db5W3JMg5LtSM88.0EC5oks5hEEXuy', '01022023881', 'Alamein International', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning\n\nIdea: Smart AI-powered wearable band that detects drowning or distress in real time and alerts lifeguards with location, enabling faster response and improving beach safety in crowded areas', NULL, 'Designer · Intermediate · Engineer', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:14', '2026-05-04 20:36:14'),
(431, 'Habiba Fathy Mohamed Fahmy Dawood', 'habiba.fahmy.2025@aiu.edu.eg', NULL, '$2y$12$SpCmrxcFqUSEIji.eUlC2.gU8yttOVTvjPq12REJ50wX2qRnvht72', '01091120344', 'Alamain international university', 'Track: Smart Healthcare\nSkills: Data Science;Software Development\n\nIdea: Smart AI-powered wearable band that detects drowning or distress in real time and alerts lifeguards with location, enabling faster response and improving beach safety in crowded areas', NULL, 'Designer · Beginner · Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:15', '2026-05-04 20:36:15'),
(432, 'Yassin Hassan Saaed Saad El Hoshy', 'yassin.elhoshy@gmail.com', NULL, '$2y$12$3Q1t7BJmG/zXaa3I2h8x1eCxP6tx77xU6UT4gCq8CkrMvP5k0Q8RW', '01026946649', 'AIU', 'Track: Smart Cities & Urban Innovation\nSkills: Software Development;Data Science;AI / Machine Learning\n\nIdea: AI platform that predicts traffic, reduces congestion, and optimizes public transport routes in real time using citywide sensor and GPS data', NULL, 'Developer · Beginner · Computer engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:15', '2026-05-04 20:36:15'),
(433, 'Ahmed Tariq Alsaiid', 'ahmed.alsaiid.2024@aiu.edu.eg', NULL, '$2y$12$y0pOxDFbe48hSSv4xvMQ7usWIQsiiJGLu/O2Qsw0X4Ng72VoQBbjK', '01013466100', 'DoctorGo', 'Track: Smart Healthcare\nSkills: AI / Machine Learning;UI/UX Design;Software Development\n\nIdea: DoctorGo is a startup idea to make healthcare in Egypt easier to access, less confusing, and more coordinated. So far, we’ve shaped the problem and started building the solution.', NULL, 'Business Lead · Intermediate · AI', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:15', '2026-05-04 20:36:15'),
(434, 'Shimaa Khaled Abdelhalim', 'shimaakh2111@gmail.com', NULL, '$2y$12$.cC7M/oRQuquckUq0W738eUchKRW6GxcFx9R4YVSyQhK5fWGLAeCO', '01118406835', 'No company but I,am working with clinic', 'Track: Health Promotion\nSkills: Business / Marketing\n\nIdea: I have a background in nutrition, having completed a diploma during my fourth year and gained hands-on experience working in clinics, including Khaled Care.', NULL, 'Developer · Intermediate · Nutrition', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:16', '2026-05-04 20:36:16'),
(435, 'Mariam Ehab Elhamamsy', 'maryam.ahmed.2025@aiu.edu.eg', NULL, '$2y$12$HlRbA9qGNSmFUV9uVGUjMOoQMi3D1OsV4iFJEkpIVwIY.QPv59zl2', '01067293973', 'Alamein international university', 'Track: Health Promotion\nSkills: Healthcare / Medical\n\nIdea: An app that tracks menstrual health and provides personalized health tips, awareness, and early warnings for potential issues.', NULL, 'Developer · Intermediate · Clinical pharmacy', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:16', '2026-05-04 20:36:16'),
(436, 'Basmala Ayman Saad', 'basmala.mohammed.2026@aiu.edu.eg', NULL, '$2y$12$.h/h5prBZ5RC4.a5K5MJIOEXPAkElrISabNQIL5wuOCLu.itp2Hx2', '01204776671', '.....', 'Track: Smart Healthcare\nSkills: Healthcare / Medical\n\nIdea: لأن الصحه اهم حاجه في حياتنا ف نحاول نخترع أساليب متقدمه للرعايه الصحيه و خاصه ل كبار السن و زياده التوعية عند الناس', NULL, 'Researcher · Beginner · pharmacy', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:16', '2026-05-04 20:36:16'),
(437, 'Bahy Youssef Aziz Youssef', 'bahyyousef1@gmail.com', NULL, '$2y$12$tbfpSghJ0EisbaSZGRJKbeY.ecJNFZB37WrpX.qSHyUWu35uDxkFW', '01200095508', 'PharmaChain AI', 'Track: Smart Healthcare\nSkills: Business / Marketing;Healthcare / Medical;AI / Machine Learning\n\nIdea: 1- Pharmacy stock shortages or overstocking due to poor demand forecasting 2- Manual and time-consuming Procurement 3- Lack of supplier visibility leaves pharmacists dependent on limited stores.', NULL, 'Business Lead · Advanced · Pharmacy', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:17', '2026-05-04 20:36:17'),
(438, 'Jana Tarek Elmanzalawy', 'greymanzalawy@gmail.com', NULL, '$2y$12$3v9CADzPlKvqRfaEYCfQTez/9VG.bYPOt8WkefKblthiTseyK1oUK', '01151635396', '..', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning\n\nIdea: .', NULL, 'Developer · Beginner · Ai', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:17', '2026-05-04 20:36:17'),
(439, 'Mohamed Sobhi Mehina', 'mohamed.mheina.2023@aiu.edu.eg', NULL, '$2y$12$HNOQmAkE2I77q9D2XeCMROKu25jxz05g7uQRAprWlsyVtKVw07Jku', '01030853940', 'No', 'Track: Smart Cities & Urban Innovation\nSkills: IoT / Hardware\n\nIdea: Dijd', NULL, 'Developer · Intermediate · Computer Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:18', '2026-05-04 20:36:18'),
(440, 'Salsabeel Mostafa Kamel Ali Sharaf', 'salsabeelsharaf252@gmail.com', NULL, '$2y$12$ryjUeQXcTsOsIi.IeOHrKOjHPXoMJVX40TYtSrGqfQh.P7BYeppOC', '01284202986', 'alamin international university', 'Track: Health Promotion\nSkills: Healthcare / Medical;AI / Machine Learning\n\nIdea: misuse medicines poor guidance unsafe use wrong administration injections orally incorrect dosage lack of awareness patient education clear instructions treatment outcomes error prevention', NULL, 'Researcher · Intermediate · pharmacy', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:18', '2026-05-04 20:36:18'),
(441, 'Fatima Elzahraa Alaa Amin', 'fatima.amin.2025@aiu.edu.eg', NULL, '$2y$12$Xj5Z2SUFbhZtS.NRP5/7ReFUXgXADD5yX.nuFNu0Tgh.//uqo4RFK', '01006948027', 'Engineering science', 'Track: Smart Cities & Urban Innovation\nSkills: Healthcare / Medical; IoT / Hardware;Business / Marketing\n\nIdea: AI-driven IoT ecosystem transforming urban spaces into smart bio-hubs. Using precision sensors to monitor plant health in real-time, we bridge the gap between engineering and sustainable nature.', NULL, 'Business Lead · Intermediate · Biomedical engineer', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:18', '2026-05-04 20:36:18'),
(442, 'Mahmoud Samy Abdelaal', 'mahmoudsamy01033@gmail.com', NULL, '$2y$12$s2uYemKOueHkpau8Pd/RC.Owt7uRpuI1oiwGavRAOJNpV3HCzfZTS', '01033883909', 'Alamein', 'Track: Smart Healthcare\nSkills: Business / Marketing;Healthcare / Medical;Mathematics\n\nIdea: Exploring the impact of digital transformation on modern educational methods and student engagement.\"', NULL, 'Business Lead · Advanced · Biomedical engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:19', '2026-05-04 20:36:19'),
(443, 'Jana Mohamed Abdelmged', 'jeny.abusaad.2026@aiu.edu.eg', NULL, '$2y$12$.E/NBro.4/00mzocVryYq.FqSLesNIOG4hEQTlZBfnAHRLd1ZzXwe', '01204819639', 'I don\'t know.', 'Track: Smart Cities & Urban Innovation\nSkills: UI/UX Design\n\nIdea: None', NULL, 'Designer · Beginner · الفنون والتصميم تصميم العاب', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:19', '2026-05-04 20:36:19'),
(444, 'Sohaila Eid Elsaeid Mohammed Nafea', 'sohaila.nafea.2023@aiu.edu.eg', NULL, '$2y$12$5nbF4U1OtRQMDm/CYQA76e.Jqx8VI9H5jYRoGFEY.WrSK.OGDewiq', '01550343705', 'Alamein international university', 'Track: Smart Healthcare\nSkills: AI / Machine Learning;Healthcare / Medical\n\nIdea: An intelligent system to optimize hospital workflows and resource allocation. By predicting patient inflow and automating triage, we can significantly reduce wait times and improve care quality.', NULL, 'Beginner · Dentistry', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:19', '2026-05-04 20:36:19'),
(445, 'Mostafa Mahmoud Elsayed Bdir', 'mostafa.bdir.2023@aiu.edu.eg', NULL, '$2y$12$Br2obMiPKXC98Vbr.PLw6OUDkYxV.tN29f./tuYep6G1OCfS71H8S', '01015580686', 'alamein international university', 'Track: Industry Challenges\nSkills: AI / Machine Learning;Business / Marketing;UI/UX Design\n\nIdea: كسب خبرات و معلومات من المطورين والشركات القائمة على التطوير', NULL, 'Beginner · Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:19', '2026-05-04 20:36:19'),
(446, 'زياد محمود عبدالباقي احمد', 'zyadshl40@gmail.com', NULL, '$2y$12$SuspaBrDYHnxulOfdPqLi.3QTfshASN4Rl4ZplQIgGzTtxROcCOr.', '01068843043', 'AIU', 'Track: Smart Healthcare\nSkills: Healthcare / Medical\n\nIdea: As I said I have no experience so i can\'t answer', NULL, 'Beginner · dentistry', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:20', '2026-05-04 20:36:20'),
(447, 'Abdallah Yehia Metwally', 'yehiaabdallah076@gmail.com', NULL, '$2y$12$vlTVTocYhQ0Ojbafkqixu.A4V6iIAnS5FQhteGSpA3rDHBQrV969a', '01018336040', 'Alamein University', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Software Development; IoT / Hardware\n\nIdea: .', NULL, 'Developer · Beginner · Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:20', '2026-05-04 20:36:20'),
(448, 'Ahmed Ibrahim Ismail Hassan Haggag', 'ahmed.haggag.2025@aiu.edu.eg', NULL, '$2y$12$vj5ejwhsU.OVrWNJIR7Xhew/0gIG.j.zMiXgAKuaBKODyCwLY0pva', '01040144246', 'I', 'Track: Industry Challenges\nSkills: Business / Marketing\n\nIdea: People\'s lack of understanding of artificial intelligence and how to exploit it', NULL, 'Designer · Beginner · Study', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:20', '2026-05-04 20:36:20'),
(449, 'Mariem Ahmed Aboalsoud', 'mariem.adelibrahim.2025@aiu.edu.eg', NULL, '$2y$12$jh0XcoPsa.mwEP8yuUfgyO0wtVd6qtN4g/iu1B0AFXt6PMKSDkJH6', '01065244088', 'Nextcity AI Hack', 'Track: Industry Challenges\nSkills: AI / Machine Learning;Software Development;Healthcare / Medical\n\nIdea: Creating a way to differentiate between similar drugs using a creative approach.', NULL, 'Beginner · Pharmacy', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:21', '2026-05-04 20:36:21'),
(450, 'Kenzy Adel Zakaria', 'kenzy.atta.2026@aiu.edu.eg', NULL, '$2y$12$19WpIPaVA0aLFzGA5G.lmeu.aLOvspVT7gd5CPOO4/PSXVsaTYyMS', '01012440094', 'Aiu', 'Track: Health Promotion\nSkills: Healthcare / Medical\n\nIdea: AI-powered app that encourages healthy habits through personalized reminders, tracking nutrition, activity, and mental well-being.', NULL, 'Data Scientist · Beginner · Dentistry', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:21', '2026-05-04 20:36:21'),
(451, 'Moemen Shazly Gomaa', 'moemen.ahmed.2025@aiu.edu.eg', NULL, '$2y$12$004rUqrFZnG5WpcaAImog.EXFZ3jnC0P8uSUYtigiU7k5pvuekXz2', '01206217101', 'Alamein International University', 'Track: Smart Cities & Urban Innovation\nSkills: Software Development\n\nIdea: AI system for smart parking that helps drivers find available parking spots in real time, reducing traffic congestion and fuel waste in crowded cities.', NULL, 'Developer · Beginner · Computer science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:21', '2026-05-04 20:36:21'),
(452, 'Ahmed Nasser', 'ahmed.nasser.2024@aiu.edu.eg', NULL, '$2y$12$fGjbOBsActLxoEQHHTu4YOhqmN/D4hyY/tGcbJAwYQFW4vfWpXhgi', '01225306728', 'Dentistry', 'Track: Industry Challenges\nSkills: Business / Marketing;Data Science\n\nIdea: AI tool for small businesses that creates social media posts, suggests posting times, provides simple analytics, and offers ready templates to improve online presence.', NULL, 'Business Lead · Intermediate · Dentist', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:22', '2026-05-04 20:36:22'),
(453, 'Noureldin Magdy Ahmed Refaie', 'noureldin.refai.2025@aiu.edu.eg', NULL, '$2y$12$ZPvj3g5JvgHXt.Y20S2YYOAzkxSIwpMyFbhPrzOXiCFgPwEXp.3hG', '01150388686', 'AIU', 'Track: Smart Cities & Urban Innovation\nSkills: Video editing and videography\n\nIdea: Egypt’s North Coast faces traffic and service challenges. This project proposes an AI smart city system using real-time data, shown through a cinematic video of transformation into a smart city.', NULL, 'Designer · Advanced · Arts & Design', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:22', '2026-05-04 20:36:22'),
(454, 'Mohamed Khaled', 'mohamed.taliba.2025@aiu.edu.eg', NULL, '$2y$12$HDqNYx5xPuXfMcha4qaoeucM1Rw54At3EiLk39P1VlXBeVkAR5JBC', '01140350305', 'Kodexora', 'Track: Industry Challenges\nSkills: Business / Marketing;AI / Machine Learning;Data Science;Software Development;Healthcare / Medical; IoT / Hardware\n\nIdea: Problem: AI just replaces labor, ignoring waste. Solution: Use AI to optimize materials & energy.', NULL, 'Developer · Intermediate · Artificial intelligence engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:22', '2026-05-04 20:36:22'),
(455, 'Antonius Sameh Samer', 'antonius.mosad.2023@aiu.edu.eg', NULL, '$2y$12$510L0LjMNxXnJQWmW09cqOKa/kxzlKz4koKxYnZbSACNRwRE9h..W', '01211445859', 'Alamein International University', 'Track: Smart Cities & Urban Innovation\nSkills: AI / Machine Learning;Business / Marketing;Software Development\n\nIdea: A system for North Coast farmers that suggests suitable crops based on soil, water, and weather, and detects tomato diseases early from images to improve yield and reduce losses.', NULL, 'Researcher · Intermediate · AIS', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:23', '2026-05-04 20:36:23'),
(456, 'يوسف حسين فتحي حسين', 'yossef.wahdan.2026@aiu.edu.eg', NULL, '$2y$12$SGpD.J.Pq7jit1OlhWV9yOEVlDrVjCD1kwtbno0rRpBAB3PKKb8rS', '01096497166', 'alfares', 'Track: Smart Healthcare\nSkills: Software Development;Business / Marketing;Healthcare / Medical\n\nIdea: There is a huge crowd at bakeries, which leads to illness and wasted time. So I thought of creating an application to organize this, and I have already designed a website to include this idea.', NULL, 'Beginner · dentistry', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:23', '2026-05-04 20:36:23'),
(457, 'Maryam Elazomy', 'mariam.awad.2024@aiu.edu.eg', NULL, '$2y$12$6lojJjtISPHFyzcPq.NR/eZyJDlh0tRbRsiPsD9wVnWF0oVAyItI2', '01004830998', 'Alamein international university', 'Track: Health Promotion\nSkills: UI/UX Design;Business / Marketing;Healthcare / Medical\n\nIdea: AI-powered gamified platform that helps parents discover their child’s abilities early through interactive games, providing personalized insights and development paths.', NULL, 'Designer · Intermediate · Arts&design,major, game design and programming', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:23', '2026-05-04 20:36:23'),
(458, 'Karim Mostafa', 'karim.omar.2023@aiu.edu.eg', NULL, '$2y$12$UkAm89iY0OYh3AjPOcryV.2tY7wo6H5pHJhPEGf6xSE3TDM7Z1dZy', '01013627574', 'AIU', 'Track: Health Promotion\nSkills: Healthcare / Medical\n\nIdea: HOW TO LIVE WITHOUT DISEASES', NULL, 'Designer · Advanced · PHARMACY', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:24', '2026-05-04 20:36:24'),
(459, 'Jana Diaa Mohamed Ibrahiem', 'jana.ibrahim.2024@aiu.edu.eg', NULL, '$2y$12$RyTP7KzaCTKjTKMWDHX8quGU8os2AZunSeDScL4xPc971rMiqkpG2', '01503316277', 'i don\'t have', 'Track: Smart Cities & Urban Innovation\nSkills: interior design\n\nIdea: I create immersive interior designs by blending 3D modeling and manual rendering to balance aesthetics and function, focusing on material textures and lighting to transform spatial environments.', NULL, 'Designer · Beginner · interior design', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:24', '2026-05-04 20:36:24'),
(460, 'Ahmed Mohamed Mohamed Ali Amer', 'ahmed.amer.2024@aiu.edu.eg', NULL, '$2y$12$e1ROFPcu/a2Ko4ScYZbdAeIxhhRcjbOIR669jj.zL7fVf/P.8YeRu', '01017342218', 'Alamein International University (student)', 'Track: Health Promotion\nSkills: Business / Marketing;Data Science;Software Development;UI/UX Design\n\nIdea: x', NULL, 'Business Lead · Intermediate · Business - Major -- > Accounting & Information Systems', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:24', '2026-05-04 20:36:24'),
(461, 'Farah Shady El-Ghamry Ali Kandil', 'farah.qandeel.2024@aiu.edu.eg', NULL, '$2y$12$Tfz7Sc4I2Ilt7l2UR4IthuPu0CVdGwnOUEoqKlrssQalboQBKyeRK', '01152030506', '..', 'Track: Smart Healthcare\nSkills: Healthcare / Medical;AI / Machine Learning\n\nIdea: A pharmacy student eager to bridge clinical science and AI. I’m joining to discover the right starting point and collaborate on smart health ideas that make medication safer in future cities.', NULL, 'Researcher · Beginner · Clinical pharmacy', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:25', '2026-05-04 20:36:25'),
(462, 'Engy Elsherbiny', 'engy.elsherbiny.2024@aiu.edu.eg', NULL, '$2y$12$/q039Hd2aYcdlZgx890FUOb6Uh07uCg38MVCRvgWlmfL0wuAeIW6K', '01019196927', 'A Comprehensive Ranking of Genetic Disorders Arising from Consanguinity Using Bioinformatics Tools', 'Track: Smart Healthcare\nSkills: Business / Marketing;Healthcare / Medical\n\nIdea: Consanguineous marriages increase the likelihood of homozygous pathogenic mutations. Such mutations may lead to a wide range of genetic disorders.', NULL, 'Researcher · Intermediate · Science', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:25', '2026-05-04 20:36:25'),
(463, 'Rawan Khalid Mohsen Alshabini', 'rawankalshabiny@gmail.com', NULL, '$2y$12$P7ZcvLaXWZnD0nwmPTqhruFDzbzw1I060u80456PbHTHApeWcEVk6', '01120792946', 'University student', 'Track: Smart Healthcare\nSkills: Healthcare / Medical\n\nIdea: AI system using wearable and optical sensor data to detect early signs of chronic disease.Enables realtime, low-cost, non-invasive monitoring, improving early diagnosis and reducing healthcare burden.', NULL, 'Data Scientist · Intermediate · Biomedical engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:25', '2026-05-04 20:36:25'),
(464, 'Ashraf Alrashidi', 'ashraf.alrashidi.2026@aiu.edu.eg', NULL, '$2y$12$Hm4GSaje0fv5ptA.KhwwJuVeaqoeSHgj/MeFyxdFkVQ3LyIYBcz8i', '01032802206', 'Alamein International University (AIU)', 'Track: Health Promotion\nSkills: AI / Machine Learning;Software Development\n\nIdea: Etamen is an AI health app for those living alone. It auto-sends a GPS SOS SMS if a daily check-in is missed. It features AI prescription scanning, mood tracking, and eye care to speed up rescue.', NULL, 'Developer · Intermediate · Computer Science / Software Engineering', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:26', '2026-05-04 20:36:26'),
(465, 'Amany Gomaa Abdelkader Basioni', 'monygomaa04@gmail.com', NULL, '$2y$12$sRUZCznjNHu6OjdteWZaiu5nYzBK.rNAj5VgivCRCVykj00TeG8.K', '01019675523', 'El Alamein International University (AIU)', 'Track: Smart Healthcare\nSkills: Healthcare / Medical;Data Science;AI / Machine Learning\n\nIdea: AI tool for early disease risk prediction using patient symptoms and basic biomedical data to support faster diagnosis and reduce .delays in overloaded healthcare systems', NULL, 'Data Scientist · Intermediate · Science molecular biotechnology', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2026-05-04 20:36:26', '2026-05-04 20:36:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_log_user_id_foreign` (`user_id`),
  ADD KEY `audit_log_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  ADD KEY `audit_log_action_index` (`action`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `community_comments`
--
ALTER TABLE `community_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `community_comments_user_id_foreign` (`user_id`),
  ADD KEY `community_comments_community_post_id_created_at_index` (`community_post_id`,`created_at`);

--
-- Indexes for table `community_posts`
--
ALTER TABLE `community_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `community_posts_user_id_foreign` (`user_id`),
  ADD KEY `community_posts_category_created_at_index` (`category`,`created_at`);

--
-- Indexes for table `community_post_attachments`
--
ALTER TABLE `community_post_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `community_post_attachments_community_post_id_index` (`community_post_id`);

--
-- Indexes for table `community_post_likes`
--
ALTER TABLE `community_post_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `community_post_likes_community_post_id_user_id_unique` (`community_post_id`,`user_id`),
  ADD KEY `community_post_likes_user_id_foreign` (`user_id`);

--
-- Indexes for table `editions`
--
ALTER TABLE `editions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `editions_year_unique` (`year`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `judge_assignments`
--
ALTER TABLE `judge_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `judge_assignments_judge_id_team_id_round_unique` (`judge_id`,`team_id`,`round`),
  ADD KEY `judge_assignments_team_id_round_index` (`team_id`,`round`);

--
-- Indexes for table `mentor_assignments`
--
ALTER TABLE `mentor_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mentor_assignments_mentor_id_team_id_unique` (`mentor_id`,`team_id`),
  ADD KEY `mentor_assignments_team_id_foreign` (`team_id`);

--
-- Indexes for table `mentor_notes`
--
ALTER TABLE `mentor_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mentor_notes_mentor_id_foreign` (`mentor_id`),
  ADD KEY `mentor_notes_team_id_created_at_index` (`team_id`,`created_at`);

--
-- Indexes for table `mentor_rotation_slots`
--
ALTER TABLE `mentor_rotation_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mentor_rotation_slots_mentor_id_team_id_slot_start_unique` (`mentor_id`,`team_id`,`slot_start`),
  ADD KEY `mentor_rotation_slots_team_id_foreign` (`team_id`),
  ADD KEY `mentor_rotation_slots_mentor_id_slot_start_index` (`mentor_id`,`slot_start`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_read_at_index` (`notifiable_type`,`notifiable_id`,`read_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `peoples_choice_votes`
--
ALTER TABLE `peoples_choice_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `peoples_choice_votes_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `peoples_choice_votes_voter_email_unique` (`voter_email`),
  ADD UNIQUE KEY `peoples_choice_votes_voter_token_unique` (`voter_token`),
  ADD KEY `peoples_choice_votes_team_id_index` (`team_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `phases`
--
ALTER TABLE `phases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phases_edition_id_key_unique` (`edition_id`,`key`),
  ADD KEY `phases_edition_id_state_index` (`edition_id`,`state`);

--
-- Indexes for table `pitch_schedule`
--
ALTER TABLE `pitch_schedule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pitch_schedule_team_id_round_unique` (`team_id`,`round`),
  ADD UNIQUE KEY `pitch_schedule_round_slot_index_unique` (`round`,`slot_index`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `scores_judge_id_team_id_round_unique` (`judge_id`,`team_id`,`round`),
  ADD KEY `scores_team_id_round_locked_at_index` (`team_id`,`round`,`locked_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `special_award_nominations`
--
ALTER TABLE `special_award_nominations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `special_award_nominations_judge_id_award_key_round_unique` (`judge_id`,`award_key`,`round`),
  ADD KEY `special_award_nominations_team_id_foreign` (`team_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `submissions_team_round_unique` (`team_id`,`round`),
  ADD KEY `submissions_submitted_by_foreign` (`submitted_by`),
  ADD KEY `submissions_team_id_index` (`team_id`),
  ADD KEY `submissions_round_status_idx` (`round`,`status`);

--
-- Indexes for table `submission_validations`
--
ALTER TABLE `submission_validations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_validations_submission_id_check_key_index` (`submission_id`,`check_key`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teams_slug_unique` (`slug`),
  ADD KEY `teams_theme_id_foreign` (`theme_id`),
  ADD KEY `teams_leader_id_foreign` (`leader_id`),
  ADD KEY `teams_edition_id_status_index` (`edition_id`,`status`);

--
-- Indexes for table `team_applications`
--
ALTER TABLE `team_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_applications_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `team_applications_team_id_status_index` (`team_id`,`status`),
  ADD KEY `team_applications_user_id_status_index` (`user_id`,`status`);

--
-- Indexes for table `team_comments`
--
ALTER TABLE `team_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_comments_user_id_foreign` (`user_id`),
  ADD KEY `team_comments_team_id_created_at_index` (`team_id`,`created_at`),
  ADD KEY `team_comments_channel_idx` (`team_id`,`channel`,`created_at`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_members_team_id_user_id_unique` (`team_id`,`user_id`),
  ADD UNIQUE KEY `team_members_user_id_unique` (`user_id`);

--
-- Indexes for table `team_workspace_drafts`
--
ALTER TABLE `team_workspace_drafts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_workspace_drafts_team_id_section_key_unique` (`team_id`,`section_key`),
  ADD KEY `team_workspace_drafts_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `themes_edition_id_key_unique` (`edition_id`,`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_registration_status_index` (`registration_status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `community_comments`
--
ALTER TABLE `community_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `community_posts`
--
ALTER TABLE `community_posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `community_post_attachments`
--
ALTER TABLE `community_post_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `community_post_likes`
--
ALTER TABLE `community_post_likes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `editions`
--
ALTER TABLE `editions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `judge_assignments`
--
ALTER TABLE `judge_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mentor_assignments`
--
ALTER TABLE `mentor_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mentor_notes`
--
ALTER TABLE `mentor_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mentor_rotation_slots`
--
ALTER TABLE `mentor_rotation_slots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `peoples_choice_votes`
--
ALTER TABLE `peoples_choice_votes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `phases`
--
ALTER TABLE `phases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `pitch_schedule`
--
ALTER TABLE `pitch_schedule`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `special_award_nominations`
--
ALTER TABLE `special_award_nominations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submission_validations`
--
ALTER TABLE `submission_validations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `team_applications`
--
ALTER TABLE `team_applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_comments`
--
ALTER TABLE `team_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `team_workspace_drafts`
--
ALTER TABLE `team_workspace_drafts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=469;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `community_comments`
--
ALTER TABLE `community_comments`
  ADD CONSTRAINT `community_comments_community_post_id_foreign` FOREIGN KEY (`community_post_id`) REFERENCES `community_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `community_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `community_posts`
--
ALTER TABLE `community_posts`
  ADD CONSTRAINT `community_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `community_post_attachments`
--
ALTER TABLE `community_post_attachments`
  ADD CONSTRAINT `community_post_attachments_community_post_id_foreign` FOREIGN KEY (`community_post_id`) REFERENCES `community_posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `community_post_likes`
--
ALTER TABLE `community_post_likes`
  ADD CONSTRAINT `community_post_likes_community_post_id_foreign` FOREIGN KEY (`community_post_id`) REFERENCES `community_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `community_post_likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `judge_assignments`
--
ALTER TABLE `judge_assignments`
  ADD CONSTRAINT `judge_assignments_judge_id_foreign` FOREIGN KEY (`judge_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `judge_assignments_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mentor_assignments`
--
ALTER TABLE `mentor_assignments`
  ADD CONSTRAINT `mentor_assignments_mentor_id_foreign` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mentor_assignments_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mentor_notes`
--
ALTER TABLE `mentor_notes`
  ADD CONSTRAINT `mentor_notes_mentor_id_foreign` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mentor_notes_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mentor_rotation_slots`
--
ALTER TABLE `mentor_rotation_slots`
  ADD CONSTRAINT `mentor_rotation_slots_mentor_id_foreign` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mentor_rotation_slots_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `peoples_choice_votes`
--
ALTER TABLE `peoples_choice_votes`
  ADD CONSTRAINT `peoples_choice_votes_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `peoples_choice_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `phases`
--
ALTER TABLE `phases`
  ADD CONSTRAINT `phases_edition_id_foreign` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pitch_schedule`
--
ALTER TABLE `pitch_schedule`
  ADD CONSTRAINT `pitch_schedule_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_judge_id_foreign` FOREIGN KEY (`judge_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scores_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `special_award_nominations`
--
ALTER TABLE `special_award_nominations`
  ADD CONSTRAINT `special_award_nominations_judge_id_foreign` FOREIGN KEY (`judge_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `special_award_nominations_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `submissions_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submission_validations`
--
ALTER TABLE `submission_validations`
  ADD CONSTRAINT `submission_validations_submission_id_foreign` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_edition_id_foreign` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teams_leader_id_foreign` FOREIGN KEY (`leader_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teams_theme_id_foreign` FOREIGN KEY (`theme_id`) REFERENCES `themes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `team_applications`
--
ALTER TABLE `team_applications`
  ADD CONSTRAINT `team_applications_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `team_applications_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_comments`
--
ALTER TABLE `team_comments`
  ADD CONSTRAINT `team_comments_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_workspace_drafts`
--
ALTER TABLE `team_workspace_drafts`
  ADD CONSTRAINT `team_workspace_drafts_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_workspace_drafts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `themes`
--
ALTER TABLE `themes`
  ADD CONSTRAINT `themes_edition_id_foreign` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
