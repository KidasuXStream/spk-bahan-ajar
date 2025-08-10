-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20250718.d42db65a1e
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 07, 2025 at 02:48 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spk-bahan-ajar`
--

-- --------------------------------------------------------

--
-- Table structure for table `ahp_comparisons`
--

CREATE TABLE `ahp_comparisons` (
  `id` bigint UNSIGNED NOT NULL,
  `ahp_session_id` bigint UNSIGNED NOT NULL,
  `kriteria_1_id` bigint UNSIGNED NOT NULL,
  `kriteria_2_id` bigint UNSIGNED NOT NULL,
  `nilai` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ahp_results`
--

CREATE TABLE `ahp_results` (
  `id` bigint UNSIGNED NOT NULL,
  `ahp_session_id` bigint UNSIGNED NOT NULL,
  `kriteria_id` bigint UNSIGNED NOT NULL,
  `bobot` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ahp_sessions`
--

CREATE TABLE `ahp_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `tahun_ajaran` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester` enum('Ganjil','Genap') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ahp_sessions`
--

INSERT INTO `ahp_sessions` (`id`, `tahun_ajaran`, `semester`, `created_at`, `updated_at`) VALUES
(1, '2025/2026', 'Ganjil', NULL, NULL),
(2, '2025/2026', 'Genap', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3', 'i:1;', 1754490925),
('laravel-cache-livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3:timer', 'i:1754490925;', 1754490925),
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:78:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:9:\"view_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:13:\"view_any_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:11:\"create_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:11:\"update_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:11:\"delete_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:15:\"delete_any_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:13:\"view_kriteria\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:17:\"view_any_kriteria\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:15:\"create_kriteria\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:15:\"update_kriteria\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:16:\"restore_kriteria\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:20:\"restore_any_kriteria\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:18:\"replicate_kriteria\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:16:\"reorder_kriteria\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:15:\"delete_kriteria\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:19:\"delete_any_kriteria\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:21:\"force_delete_kriteria\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:25:\"force_delete_any_kriteria\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:27:\"view_pengajuan::bahan::ajar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:4;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:31:\"view_any_pengajuan::bahan::ajar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:4;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:9:\"view_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:13:\"view_any_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:11:\"create_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:11:\"update_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:12:\"restore_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:16:\"restore_any_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:14:\"replicate_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:12:\"reorder_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:11:\"delete_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:15:\"delete_any_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:17:\"force_delete_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:21:\"force_delete_any_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:29:\"create_pengajuan::bahan::ajar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:29:\"update_pengajuan::bahan::ajar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:4;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:30:\"restore_pengajuan::bahan::ajar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:34:\"restore_any_pengajuan::bahan::ajar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:32:\"replicate_pengajuan::bahan::ajar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:30:\"reorder_pengajuan::bahan::ajar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:29:\"delete_pengajuan::bahan::ajar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:33:\"delete_any_pengajuan::bahan::ajar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:35:\"force_delete_pengajuan::bahan::ajar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:39:\"force_delete_any_pengajuan::bahan::ajar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:20:\"view_ahp::comparison\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:24:\"view_any_ahp::comparison\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:22:\"create_ahp::comparison\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:22:\"update_ahp::comparison\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:23:\"restore_ahp::comparison\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:27:\"restore_any_ahp::comparison\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:25:\"replicate_ahp::comparison\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:23:\"reorder_ahp::comparison\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:22:\"delete_ahp::comparison\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:26:\"delete_any_ahp::comparison\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:28:\"force_delete_ahp::comparison\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:32:\"force_delete_any_ahp::comparison\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:16:\"view_ahp::result\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:20:\"view_any_ahp::result\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:18:\"create_ahp::result\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:18:\"update_ahp::result\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:19:\"restore_ahp::result\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:23:\"restore_any_ahp::result\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:21:\"replicate_ahp::result\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:19:\"reorder_ahp::result\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:18:\"delete_ahp::result\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:22:\"delete_any_ahp::result\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:24:\"force_delete_ahp::result\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:28:\"force_delete_any_ahp::result\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:17:\"view_ahp::session\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:21:\"view_any_ahp::session\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:19:\"create_ahp::session\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:69;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:19:\"update_ahp::session\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:70;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:20:\"restore_ahp::session\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:71;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:24:\"restore_any_ahp::session\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:72;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:22:\"replicate_ahp::session\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:73;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:20:\"reorder_ahp::session\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:74;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:19:\"delete_ahp::session\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:75;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:23:\"delete_any_ahp::session\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:76;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:25:\"force_delete_ahp::session\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:77;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:29:\"force_delete_any_ahp::session\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super_admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:13:\"Tim Pengadaan\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:7:\"Kaprodi\";s:1:\"c\";s:3:\"web\";}}}', 1754579462);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kriterias`
--

CREATE TABLE `kriterias` (
  `id` bigint UNSIGNED NOT NULL,
  `kode_kriteria` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_kriteria` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kriterias`
--

INSERT INTO `kriterias` (`id`, `kode_kriteria`, `nama_kriteria`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'C1', 'Harga', 'Harga dari sebuah barang keperluan bahan ajar ', '2025-08-04 19:06:40', '2025-08-04 19:06:40'),
(2, 'C2', 'Jumlah', 'Jumlah dari sebuah barang keperluan bahan ajar', '2025-08-04 19:10:42', '2025-08-04 19:10:42'),
(3, 'C3', 'Stok', 'Stok dari bahan ajar yang ada', '2025-08-04 19:18:36', '2025-08-04 19:18:36'),
(4, 'C4', 'Urgensi', 'Tingkat keperluan akan bahan ajar', '2025-08-04 19:20:46', '2025-08-04 19:20:46');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_07_22_150432_create_kriterias_table', 2),
(5, '2025_07_22_161526_create_pengajuan_bahan_ajars_table', 3),
(6, '2025_07_30_014311_create_notifications_table', 4),
(7, '2025_08_01_154145_create_ahp_sessions_table', 5),
(8, '2025_08_01_154156_create_ahp_results_table', 5),
(9, '2025_08_01_154209_create_ahp_comparisons_table', 6),
(10, '2025_08_02_154836_add_fields_to_users_table', 7),
(11, '2025_08_02_160831_create_permission_tables', 8),
(12, '2025_08_03_194954_add_ahp_session_id_and_keterangan_penolakan_to_pengajuan_bahan_ajars_table', 9);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(3, 'App\\Models\\User', 3),
(4, 'App\\Models\\User', 4);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_bahan_ajars`
--

CREATE TABLE `pengajuan_bahan_ajars` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `nama_barang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `spesifikasi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` int NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `masa_pakai` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stok` int NOT NULL,
  `status_pengajuan` enum('diajukan','acc_kaprodi','ditolak','diproses') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'diajukan',
  `alasan_penolakan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `urgensi` enum('tinggi','sedang','rendah') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ahp_session_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view_role', 'web', '2025-08-02 09:08:56', '2025-08-02 09:08:56'),
(2, 'view_any_role', 'web', '2025-08-02 09:08:56', '2025-08-02 09:08:56'),
(3, 'create_role', 'web', '2025-08-02 09:08:56', '2025-08-02 09:08:56'),
(4, 'update_role', 'web', '2025-08-02 09:08:56', '2025-08-02 09:08:56'),
(5, 'delete_role', 'web', '2025-08-02 09:08:56', '2025-08-02 09:08:56'),
(6, 'delete_any_role', 'web', '2025-08-02 09:08:56', '2025-08-02 09:08:56'),
(7, 'view_kriteria', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(8, 'view_any_kriteria', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(9, 'create_kriteria', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(10, 'update_kriteria', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(11, 'restore_kriteria', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(12, 'restore_any_kriteria', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(13, 'replicate_kriteria', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(14, 'reorder_kriteria', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(15, 'delete_kriteria', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(16, 'delete_any_kriteria', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(17, 'force_delete_kriteria', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(18, 'force_delete_any_kriteria', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(19, 'view_pengajuan::bahan::ajar', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(20, 'view_any_pengajuan::bahan::ajar', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(21, 'view_user', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(22, 'view_any_user', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(23, 'create_user', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(24, 'update_user', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(25, 'restore_user', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(26, 'restore_any_user', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(27, 'replicate_user', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(28, 'reorder_user', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(29, 'delete_user', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(30, 'delete_any_user', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(31, 'force_delete_user', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(32, 'force_delete_any_user', 'web', '2025-08-02 09:14:34', '2025-08-02 09:14:34'),
(33, 'create_pengajuan::bahan::ajar', 'web', '2025-08-02 09:15:18', '2025-08-02 09:15:18'),
(34, 'update_pengajuan::bahan::ajar', 'web', '2025-08-02 09:15:44', '2025-08-02 09:15:44'),
(35, 'restore_pengajuan::bahan::ajar', 'web', '2025-08-02 09:15:44', '2025-08-02 09:15:44'),
(36, 'restore_any_pengajuan::bahan::ajar', 'web', '2025-08-02 09:15:44', '2025-08-02 09:15:44'),
(37, 'replicate_pengajuan::bahan::ajar', 'web', '2025-08-02 09:15:44', '2025-08-02 09:15:44'),
(38, 'reorder_pengajuan::bahan::ajar', 'web', '2025-08-02 09:15:44', '2025-08-02 09:15:44'),
(39, 'delete_pengajuan::bahan::ajar', 'web', '2025-08-02 09:15:44', '2025-08-02 09:15:44'),
(40, 'delete_any_pengajuan::bahan::ajar', 'web', '2025-08-02 09:15:44', '2025-08-02 09:15:44'),
(41, 'force_delete_pengajuan::bahan::ajar', 'web', '2025-08-02 09:15:44', '2025-08-02 09:15:44'),
(42, 'force_delete_any_pengajuan::bahan::ajar', 'web', '2025-08-02 09:15:44', '2025-08-02 09:15:44'),
(43, 'view_ahp::comparison', 'web', '2025-08-03 20:05:40', '2025-08-03 20:05:40'),
(44, 'view_any_ahp::comparison', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(45, 'create_ahp::comparison', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(46, 'update_ahp::comparison', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(47, 'restore_ahp::comparison', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(48, 'restore_any_ahp::comparison', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(49, 'replicate_ahp::comparison', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(50, 'reorder_ahp::comparison', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(51, 'delete_ahp::comparison', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(52, 'delete_any_ahp::comparison', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(53, 'force_delete_ahp::comparison', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(54, 'force_delete_any_ahp::comparison', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(55, 'view_ahp::result', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(56, 'view_any_ahp::result', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(57, 'create_ahp::result', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(58, 'update_ahp::result', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(59, 'restore_ahp::result', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(60, 'restore_any_ahp::result', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(61, 'replicate_ahp::result', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(62, 'reorder_ahp::result', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(63, 'delete_ahp::result', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(64, 'delete_any_ahp::result', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(65, 'force_delete_ahp::result', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(66, 'force_delete_any_ahp::result', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(67, 'view_ahp::session', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(68, 'view_any_ahp::session', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(69, 'create_ahp::session', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(70, 'update_ahp::session', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(71, 'restore_ahp::session', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(72, 'restore_any_ahp::session', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(73, 'replicate_ahp::session', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(74, 'reorder_ahp::session', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(75, 'delete_ahp::session', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(76, 'delete_any_ahp::session', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(77, 'force_delete_ahp::session', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41'),
(78, 'force_delete_any_ahp::session', 'web', '2025-08-03 20:05:41', '2025-08-03 20:05:41');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2025-08-02 09:08:56', '2025-08-02 09:08:56'),
(3, 'Kaprodi', 'web', '2025-08-02 09:16:18', '2025-08-02 09:16:18'),
(4, 'Tim Pengadaan', 'web', '2025-08-02 09:17:00', '2025-08-02 09:17:00');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(19, 3),
(20, 3),
(33, 3),
(34, 3),
(7, 4),
(8, 4),
(9, 4),
(10, 4),
(11, 4),
(12, 4),
(13, 4),
(14, 4),
(15, 4),
(16, 4),
(17, 4),
(18, 4),
(19, 4),
(20, 4),
(34, 4),
(43, 4),
(44, 4),
(45, 4),
(46, 4),
(47, 4),
(48, 4),
(49, 4),
(50, 4),
(51, 4),
(52, 4),
(53, 4),
(54, 4),
(55, 4),
(56, 4),
(57, 4),
(58, 4),
(59, 4),
(60, 4),
(61, 4),
(62, 4),
(63, 4),
(64, 4),
(65, 4),
(66, 4),
(67, 4),
(68, 4),
(69, 4),
(70, 4),
(71, 4),
(72, 4),
(73, 4),
(74, 4),
(75, 4),
(76, 4),
(77, 4),
(78, 4);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('oVrj5YXebVovfgU1dn6st3jFw1YhIPTwt4dNsBrr', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiTFp3YVpVeHI2Ym9RaUE0UjJjSlRPcHlZQ0J1QWY3S3hiNVlmNjRxYyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQwOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vdXNlcnMvY3JlYXRlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJEhaV0FyZnhxZmdVT0o0bkpGNWNiOE96UncvdXJqWUVnM01LR0JMNjUyc1lBeUtoMy9VMUV1IjtzOjg6ImZpbGFtZW50IjthOjA6e319', 1754500858);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nidn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prodi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `nidn`, `nip`, `prodi`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@example.com', NULL, NULL, NULL, NULL, '$2y$12$HZWArfxqfgUOJ4nJF5cb8OzRw/urjYEg3MKGBL652sYAyKh3/U1Eu', NULL, '2025-07-22 07:47:56', '2025-07-22 07:47:56'),
(3, 'Test Kaprodi', 'tkaprodi@test.test', '23456', '34567', 'trpl', NULL, '$2y$12$7HG54CCWjdf4lY4WYk9geeEwjQ3KF4sDcS9u9mIeyBcOSWMswSgnG', NULL, '2025-08-04 19:26:26', '2025-08-04 19:26:26'),
(4, 'Test Tim Pengadaan', 'pengadaan@test.test', '34567', '45678', NULL, NULL, '$2y$12$aHMgqbxQSyoH4QTs0u9I..BGOVo2gg.eoT8ITBARNxKAjAZZ3Tfxq', NULL, NULL, '2025-08-04 20:27:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ahp_comparisons`
--
ALTER TABLE `ahp_comparisons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ahp_comparisons_ahp_session_id_foreign` (`ahp_session_id`),
  ADD KEY `ahp_comparisons_kriteria_1_id_foreign` (`kriteria_1_id`),
  ADD KEY `ahp_comparisons_kriteria_2_id_foreign` (`kriteria_2_id`);

--
-- Indexes for table `ahp_results`
--
ALTER TABLE `ahp_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ahp_results_ahp_session_id_foreign` (`ahp_session_id`),
  ADD KEY `ahp_results_kriteria_id_foreign` (`kriteria_id`);

--
-- Indexes for table `ahp_sessions`
--
ALTER TABLE `ahp_sessions`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `kriterias`
--
ALTER TABLE `kriterias`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pengajuan_bahan_ajars`
--
ALTER TABLE `pengajuan_bahan_ajars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengajuan_bahan_ajars_user_id_foreign` (`user_id`),
  ADD KEY `pengajuan_bahan_ajars_ahp_session_id_foreign` (`ahp_session_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

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
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ahp_comparisons`
--
ALTER TABLE `ahp_comparisons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ahp_results`
--
ALTER TABLE `ahp_results`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ahp_sessions`
--
ALTER TABLE `ahp_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kriterias`
--
ALTER TABLE `kriterias`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pengajuan_bahan_ajars`
--
ALTER TABLE `pengajuan_bahan_ajars`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ahp_comparisons`
--
ALTER TABLE `ahp_comparisons`
  ADD CONSTRAINT `ahp_comparisons_ahp_session_id_foreign` FOREIGN KEY (`ahp_session_id`) REFERENCES `ahp_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ahp_comparisons_kriteria_1_id_foreign` FOREIGN KEY (`kriteria_1_id`) REFERENCES `kriterias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ahp_comparisons_kriteria_2_id_foreign` FOREIGN KEY (`kriteria_2_id`) REFERENCES `kriterias` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ahp_results`
--
ALTER TABLE `ahp_results`
  ADD CONSTRAINT `ahp_results_ahp_session_id_foreign` FOREIGN KEY (`ahp_session_id`) REFERENCES `ahp_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ahp_results_kriteria_id_foreign` FOREIGN KEY (`kriteria_id`) REFERENCES `kriterias` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `pengajuan_bahan_ajars`
--
ALTER TABLE `pengajuan_bahan_ajars`
  ADD CONSTRAINT `pengajuan_bahan_ajars_ahp_session_id_foreign` FOREIGN KEY (`ahp_session_id`) REFERENCES `ahp_sessions` (`id`),
  ADD CONSTRAINT `pengajuan_bahan_ajars_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
