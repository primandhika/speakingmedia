-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 19 Jun 2025 pada 09.30
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bicaranta_db`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CleanupOldSessions` ()   BEGIN
    -- Hapus activity log lebih dari 1 tahun
    DELETE FROM activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
    
    -- Hapus learning session yang tidak selesai lebih dari 7 hari
    DELETE FROM learning_sessions 
    WHERE session_end IS NULL 
    AND session_start < DATE_SUB(NOW(), INTERVAL 7 DAY);
    
    -- Update statistics
    OPTIMIZE TABLE activity_log;
    OPTIMIZE TABLE learning_sessions;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` varchar(10) DEFAULT NULL,
  `activity_type` enum('login','logout','material_click','search','page_view') DEFAULT NULL,
  `activity_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`activity_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `activity_type`, `activity_data`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, '00029', 'login', '{\"timestamp\": \"2024-06-18 09:00:00\"}', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2025-06-18 06:50:46'),
(2, '00029', 'material_click', '{\"material\": \"keterampilan-bicara\", \"page\": \"index\"}', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2025-06-18 06:50:46'),
(3, '00029', 'search', '{\"query\": \"bicara\", \"results\": 3}', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2025-06-18 06:50:46'),
(4, '12345', 'login', '{\"timestamp\": \"2024-06-18 14:15:00\"}', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', '2025-06-18 06:50:46'),
(5, '12345', 'material_click', '{\"material\": \"public-speaking\", \"page\": \"index\"}', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', '2025-06-18 06:50:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `assessments`
--

CREATE TABLE `assessments` (
  `id` int(11) NOT NULL,
  `material_key` varchar(50) NOT NULL,
  `submaterial_key` varchar(50) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `assessment_type` enum('quiz','exercise','project','reflection') DEFAULT 'quiz',
  `questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`questions`)),
  `passing_score` int(11) DEFAULT 70,
  `max_attempts` int(11) DEFAULT 3,
  `time_limit` int(11) DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `assessment_results`
--

CREATE TABLE `assessment_results` (
  `id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `user_id` varchar(10) DEFAULT NULL,
  `progress_id` varchar(20) DEFAULT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`)),
  `score` int(11) DEFAULT 0,
  `max_score` int(11) DEFAULT 100,
  `time_taken` int(11) DEFAULT NULL,
  `attempt_number` int(11) DEFAULT 1,
  `is_passed` tinyint(1) DEFAULT 0,
  `feedback` text DEFAULT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `instructor_materials`
--

CREATE TABLE `instructor_materials` (
  `id` int(11) NOT NULL,
  `instructor_id` varchar(10) DEFAULT NULL,
  `material_key` varchar(50) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `instructor_materials`
--

INSERT INTO `instructor_materials` (`id`, `instructor_id`, `material_key`, `assigned_at`, `is_primary`) VALUES
(1, 'INST001', 'public-speaking', '2025-06-18 06:55:26', 1),
(2, 'INST001', 'presentasi', '2025-06-18 06:55:26', 1),
(3, 'INST001', 'keterampilan-bicara', '2025-06-18 06:55:26', 0),
(4, 'INST002', 'retorika', '2025-06-18 06:55:26', 1),
(5, 'INST002', 'komunikasi-persuasif', '2025-06-18 06:55:26', 1),
(6, 'INST002', 'debat-argumen', '2025-06-18 06:55:26', 1),
(7, 'INST003', 'storytelling', '2025-06-18 06:55:26', 1),
(8, 'INST003', 'presentasi', '2025-06-18 06:55:26', 0),
(9, 'INST003', 'keterampilan-bicara', '2025-06-18 06:55:26', 0),
(10, 'INST004', 'komunikasi-nonverbal', '2025-06-18 06:55:26', 1),
(11, 'INST004', 'keterampilan-bicara', '2025-06-18 06:55:26', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `learning_sessions`
--

CREATE TABLE `learning_sessions` (
  `id` int(11) NOT NULL,
  `user_id` varchar(10) DEFAULT NULL,
  `progress_id` varchar(20) DEFAULT NULL,
  `material_key` varchar(50) NOT NULL,
  `submaterial_key` varchar(50) DEFAULT NULL,
  `session_start` timestamp NOT NULL DEFAULT current_timestamp(),
  `session_end` timestamp NULL DEFAULT NULL,
  `time_spent` int(11) DEFAULT 0,
  `completion_percentage` int(11) DEFAULT 0,
  `last_position` text DEFAULT NULL,
  `device_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`device_info`)),
  `is_completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `material_key` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `difficulty` enum('Pemula','Menengah','Lanjutan') DEFAULT 'Pemula',
  `duration` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `materials`
--

INSERT INTO `materials` (`id`, `material_key`, `name`, `description`, `icon`, `difficulty`, `duration`, `is_active`, `created_at`, `deleted_at`) VALUES
(1, 'keterampilan-bicara', 'Keterampilan Bicara Dasar', 'Dasar-dasar berbicara yang efektif dan percaya diri', 'bi-mic', 'Pemula', '2-3 jam', 1, '2025-06-18 06:50:46', NULL),
(2, 'retorika', 'Retorika', 'Seni persuasi dan argumentasi klasik', 'bi-mortarboard', 'Menengah', '3-4 jam', 1, '2025-06-18 06:50:46', NULL),
(3, 'public-speaking', 'Public Speaking', 'Berbicara di depan umum dengan percaya diri', 'bi-people', 'Menengah', '4-5 jam', 1, '2025-06-18 06:50:46', NULL),
(4, 'komunikasi-persuasif', 'Komunikasi Persuasif', 'Teknik mempengaruhi dan meyakinkan audiens', 'bi-arrow-through-heart', 'Lanjutan', '3-4 jam', 1, '2025-06-18 06:50:46', NULL),
(5, 'storytelling', 'Storytelling', 'Bercerita yang menarik dan berkesan', 'bi-book', 'Pemula', '2-3 jam', 1, '2025-06-18 06:50:46', NULL),
(6, 'presentasi', 'Teknik Presentasi', 'Presentasi yang efektif dan menarik', 'bi-easel', 'Menengah', '3-4 jam', 1, '2025-06-18 06:50:46', NULL),
(7, 'debat-argumen', 'Debat & Argumentasi', 'Teknik berdebat dan membangun argumen kuat', 'bi-chat-square-quote', 'Lanjutan', '4-5 jam', 1, '2025-06-18 06:50:46', NULL),
(8, 'komunikasi-nonverbal', 'Komunikasi Nonverbal', 'Bahasa tubuh dan komunikasi tanpa kata', 'bi-person-arms-up', 'Pemula', '2-3 jam', 1, '2025-06-18 06:50:46', NULL);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `material_statistics`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `material_statistics` (
`material_key` varchar(50)
,`name` varchar(100)
,`total_submaterials` bigint(21)
,`total_students` bigint(21)
,`avg_progress` decimal(14,4)
,`completed_students` bigint(21)
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `role` enum('admin','instructor','student') DEFAULT NULL,
  `permission` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `permissions`
--

INSERT INTO `permissions` (`id`, `role`, `permission`, `description`, `created_at`) VALUES
(1, 'admin', 'manage_users', 'Mengelola data pengguna', '2025-06-18 06:55:26'),
(2, 'admin', 'manage_materials', 'Mengelola materi pembelajaran', '2025-06-18 06:55:26'),
(3, 'admin', 'view_analytics', 'Melihat analytics dan laporan', '2025-06-18 06:55:26'),
(4, 'admin', 'manage_system', 'Mengelola pengaturan sistem', '2025-06-18 06:55:26'),
(5, 'admin', 'manage_instructors', 'Mengelola data pengajar', '2025-06-18 06:55:26'),
(6, 'instructor', 'view_students', 'Melihat data siswa', '2025-06-18 06:55:26'),
(7, 'instructor', 'manage_assigned_materials', 'Mengelola materi yang ditugaskan', '2025-06-18 06:55:26'),
(8, 'instructor', 'view_student_progress', 'Melihat progress siswa', '2025-06-18 06:55:26'),
(9, 'instructor', 'create_content', 'Membuat konten pembelajaran', '2025-06-18 06:55:26'),
(10, 'student', 'access_materials', 'Mengakses materi pembelajaran', '2025-06-18 06:55:26'),
(11, 'student', 'track_progress', 'Tracking progress belajar', '2025-06-18 06:55:26'),
(12, 'student', 'view_profile', 'Melihat profil sendiri', '2025-06-18 06:55:26');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `user_id` varchar(10) DEFAULT NULL,
  `session_start` timestamp NOT NULL DEFAULT current_timestamp(),
  `session_end` timestamp NULL DEFAULT NULL,
  `duration` int(11) DEFAULT 0,
  `page_views` int(11) DEFAULT 0,
  `materials_accessed` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `session_start`, `session_end`, `duration`, `page_views`, `materials_accessed`) VALUES
(1, '00029', '2024-06-18 02:00:00', '2024-06-18 03:30:00', 5400, 12, '[\"keterampilan-bicara\", \"retorika\", \"storytelling\"]'),
(2, '12345', '2024-06-18 07:15:00', '2024-06-18 08:45:00', 5400, 8, '[\"public-speaking\", \"presentasi\"]'),
(3, '67890', '2024-06-18 12:30:00', '2024-06-18 14:00:00', 5400, 15, '[\"retorika\", \"komunikasi-persuasif\", \"debat-argumen\"]'),
(4, '11111', '2024-06-17 09:00:00', '2024-06-17 10:30:00', 5400, 10, '[\"storytelling\", \"presentasi\"]'),
(5, '22222', '2024-06-17 13:00:00', '2024-06-17 14:15:00', 4500, 7, '[\"keterampilan-bicara\", \"komunikasi-nonverbal\"]');

-- --------------------------------------------------------

--
-- Struktur dari tabel `submaterials`
--

CREATE TABLE `submaterials` (
  `id` int(11) NOT NULL,
  `material_key` varchar(50) NOT NULL,
  `submaterial_key` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `level` int(11) DEFAULT 1,
  `icon` varchar(50) DEFAULT 'bi-circle',
  `duration` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `submaterials`
--

INSERT INTO `submaterials` (`id`, `material_key`, `submaterial_key`, `title`, `description`, `level`, `icon`, `duration`, `is_active`, `sort_order`, `created_at`, `deleted_at`) VALUES
(1, 'keterampilan-bicara', 'pengorganisasian-ide', 'Pengorganisasian Ide', 'Dalam sub materi ini kamu akan mempelajari cara menyampaikan informasi secara logis dan runtut, mengatur struktur presentasi dengan pengantar, isi, dan penutup yang efektif.', 1, 'bi-diagram-3', '15-20 menit', 1, 1, '2025-06-19 04:42:40', NULL),
(2, 'keterampilan-bicara', 'kejelasan-penyampaian', 'Kejelasan Penyampaian', 'Dalam sub materi ini kamu akan mempelajari teknik pelafalan, intonasi, dan artikulasi yang jelas untuk memastikan pesan tersampaikan dengan baik kepada audiens.', 2, 'bi-volume-up', '20-25 menit', 1, 2, '2025-06-19 04:42:40', NULL),
(3, 'keterampilan-bicara', 'penguasaan-materi', 'Penguasaan Materi', 'Dalam sub materi ini kamu akan mempelajari cara menguasai materi presentasi hingga mampu menjelaskan tanpa membaca teks dan memberikan contoh konkret.', 3, 'bi-book', '25-30 menit', 1, 3, '2025-06-19 04:42:40', NULL),
(4, 'keterampilan-bicara', 'bahasa-efektif', 'Bahasa yang Efektif', 'Dalam sub materi ini kamu akan mempelajari penggunaan struktur kalimat dan kosa kata yang tepat sesuai kaidah bahasa Indonesia dan konteks audiens.', 4, 'bi-chat-square-text', '20-25 menit', 1, 4, '2025-06-19 04:42:40', NULL),
(5, 'keterampilan-bicara', 'kontak-interaksi', 'Kontak dan Interaksi', 'Dalam sub materi ini kamu akan mempelajari cara menjalin kontak mata dengan audiens dan menciptakan interaksi dua arah yang efektif.', 5, 'bi-people', '25-30 menit', 1, 5, '2025-06-19 04:42:40', NULL),
(6, 'keterampilan-bicara', 'penggunaan-media', 'Penggunaan Media', 'Dalam sub materi ini kamu akan mempelajari cara menggunakan media pendukung seperti slide dan gambar secara efektif tanpa mengalihkan perhatian.', 6, 'bi-laptop', '20-25 menit', 1, 6, '2025-06-19 04:42:40', NULL),
(7, 'keterampilan-bicara', 'manajemen-waktu', 'Manajemen Waktu', 'Dalam sub materi ini kamu akan mempelajari cara mengatur durasi presentasi dan menyampaikan isi dalam waktu yang tersedia dengan efisien.', 7, 'bi-clock', '15-20 menit', 1, 7, '2025-06-19 04:42:40', NULL),
(8, 'keterampilan-bicara', 'penutupan-kuat', 'Penutupan yang Kuat', 'Dalam sub materi ini kamu akan mempelajari cara memberikan kesimpulan yang kuat, inspiratif, dan memberikan kesan mendalam kepada audiens.', 8, 'bi-flag', '20-25 menit', 1, 8, '2025-06-19 04:42:40', NULL),
(9, 'public-speaking', 'persiapan-mental', 'Persiapan Mental', 'Dalam sub materi ini kamu akan mempelajari teknik mengatasi demam panggung dan membangun kepercayaan diri sebelum berbicara di depan umum.', 1, 'bi-heart', '20-25 menit', 1, 1, '2025-06-19 04:42:40', NULL),
(10, 'public-speaking', 'analisis-audiens', 'Analisis Audiens', 'Dalam sub materi ini kamu akan mempelajari cara mengidentifikasi karakteristik audiens dan menyesuaikan gaya komunikasi yang tepat.', 2, 'bi-people-fill', '25-30 menit', 1, 2, '2025-06-19 04:42:40', NULL),
(11, 'public-speaking', 'struktur-presentasi', 'Struktur Presentasi', 'Dalam sub materi ini kamu akan mempelajari cara merancang struktur presentasi publik yang menarik dan mudah diikuti.', 3, 'bi-diagram-2', '30-35 menit', 1, 3, '2025-06-19 04:42:40', NULL),
(12, 'public-speaking', 'teknik-pembukaan', 'Teknik Pembukaan', 'Dalam sub materi ini kamu akan mempelajari berbagai teknik pembukaan yang efektif untuk menarik perhatian audiens sejak awal.', 4, 'bi-play-circle', '20-25 menit', 1, 4, '2025-06-19 04:42:40', NULL),
(13, 'public-speaking', 'bahasa-tubuh', 'Bahasa Tubuh', 'Dalam sub materi ini kamu akan mempelajari cara menggunakan gestur, postur, dan ekspresi wajah untuk mendukung pesan verbal.', 5, 'bi-person-arms-up', '25-30 menit', 1, 5, '2025-06-19 04:42:40', NULL),
(14, 'public-speaking', 'mengelola-qa', 'Mengelola Sesi Q&A', 'Dalam sub materi ini kamu akan mempelajari strategi menghadapi pertanyaan dari audiens dengan percaya diri dan profesional.', 6, 'bi-question-circle', '25-30 menit', 1, 6, '2025-06-19 04:42:40', NULL),
(15, 'retorika', 'sejarah-retorika', 'Sejarah dan Filosofi Retorika', 'Dalam sub materi ini kamu akan mempelajari asal-usul retorika dari zaman Aristoteles hingga perkembangannya di era modern.', 1, 'bi-book-half', '30-35 menit', 1, 1, '2025-06-19 04:42:40', NULL),
(16, 'retorika', 'ethos-pathos-logos', 'Ethos, Pathos, dan Logos', 'Dalam sub materi ini kamu akan mempelajari tiga pilar retorika Aristoteles: kredibilitas pembicara, emosi audiens, dan logika argumen.', 2, 'bi-triangle', '35-40 menit', 1, 2, '2025-06-19 04:42:40', NULL),
(17, 'retorika', 'struktur-argumen', 'Struktur Argumen', 'Dalam sub materi ini kamu akan mempelajari cara membangun argumen yang kuat dengan premis, bukti, dan kesimpulan yang logis.', 3, 'bi-diagram-3-fill', '30-35 menit', 1, 3, '2025-06-19 04:42:40', NULL),
(18, 'retorika', 'teknik-persuasi', 'Teknik Persuasi', 'Dalam sub materi ini kamu akan mempelajari berbagai teknik persuasi untuk mempengaruhi sikap dan perilaku audiens secara etis.', 4, 'bi-arrow-through-heart-fill', '40-45 menit', 1, 4, '2025-06-19 04:42:40', NULL),
(19, 'retorika', 'gaya-bahasa', 'Gaya Bahasa Retoris', 'Dalam sub materi ini kamu akan mempelajari penggunaan metafora, analogi, repetisi, dan figure of speech lainnya.', 5, 'bi-fonts', '25-30 menit', 1, 5, '2025-06-19 04:42:40', NULL);

--
-- Trigger `submaterials`
--
DELIMITER $$
CREATE TRIGGER `check_submaterial_level_sequence` BEFORE INSERT ON `submaterials` FOR EACH ROW BEGIN
    DECLARE max_level INT;
    SELECT COALESCE(MAX(level), 0) INTO max_level 
    FROM submaterials 
    WHERE material_key = NEW.material_key;
    
    IF NEW.level > max_level + 1 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Level submaterial harus berurutan';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `submaterial_content`
--

CREATE TABLE `submaterial_content` (
  `id` int(11) NOT NULL,
  `material_key` varchar(50) NOT NULL,
  `submaterial_key` varchar(50) NOT NULL,
  `content_type` enum('text','video','audio','image','quiz','exercise') DEFAULT 'text',
  `title` varchar(200) NOT NULL,
  `content_data` longtext DEFAULT NULL,
  `content_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_required` tinyint(1) DEFAULT 1,
  `estimated_time` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `submaterial_content`
--

INSERT INTO `submaterial_content` (`id`, `material_key`, `submaterial_key`, `content_type`, `title`, `content_data`, `content_url`, `display_order`, `is_required`, `estimated_time`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'keterampilan-bicara', 'pengorganisasian-ide', 'video', 'Video: Cara Mengorganisasi Ide dalam Presentasi', NULL, 'https://www.youtube.com/shorts/Zc787B-W59M', 1, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 07:17:00'),
(2, 'keterampilan-bicara', 'pengorganisasian-ide', 'text', 'Materi: Struktur Ide yang Efektif', '<h5>Pengorganisasian Ide dalam Berbicara</h5>', NULL, 2, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 07:17:26'),
(3, 'keterampilan-bicara', 'kejelasan-penyampaian', 'video', 'Video: Teknik Articulation dan Intonasi', NULL, 'https://www.youtube.com/embed/F6Ox6W_aJJg', 1, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 06:59:19'),
(4, 'keterampilan-bicara', 'kejelasan-penyampaian', 'text', 'Materi: Pelafalan dan Intonasi yang Jelas', '<h5>Kejelasan dalam Penyampaian</h5>\r\n<p>Kejelasan penyampaian meliputi tiga aspek utama:</p>\r\n<h6>1. Pelafalan (Articulation)</h6>\r\n<ul>\r\n<li>Ucapkan setiap kata dengan jelas</li>\r\n<li>Perhatikan bunyi konsonan dan vokal</li>\r\n<li>Hindari mumbling atau berbicara terlalu cepat</li>\r\n</ul>\r\n<h6>2. Intonasi</h6>\r\n<ul>\r\n<li>Variasikan nada suara sesuai konteks</li>\r\n<li>Gunakan penekanan pada kata penting</li>\r\n<li>Sesuaikan dengan emosi yang ingin disampaikan</li>\r\n</ul>\r\n<h6>3. Volume dan Tempo</h6>\r\n<ul>\r\n<li>Pastikan suara terdengar jelas oleh seluruh audiens</li>\r\n<li>Atur kecepatan bicara yang comfortable</li>\r\n<li>Berikan jeda untuk emphasis</li>\r\n</ul>', NULL, 2, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 06:59:19'),
(5, 'keterampilan-bicara', 'penguasaan-materi', 'video', 'Video: Cara Menguasai Materi Presentasi', NULL, 'https://www.youtube.com/embed/VpZmIiIXuZ0', 1, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 06:59:19'),
(6, 'keterampilan-bicara', 'penguasaan-materi', 'text', 'Materi: Teknik Menguasai Content', '<h5>Penguasaan Materi Presentasi</h5>\r\n<p>Menguasai materi adalah fondasi dari presentasi yang sukses:</p>\r\n<h6>1. Persiapan Mendalam</h6>\r\n<ul>\r\n<li>Riset topic secara menyeluruh</li>\r\n<li>Kumpulkan data dan fakta pendukung</li>\r\n<li>Pahami berbagai perspektif</li>\r\n</ul>\r\n<h6>2. Teknik Mengingat</h6>\r\n<ul>\r\n<li>Buat outline yang mudah diingat</li>\r\n<li>Gunakan teknik mnemonik</li>\r\n<li>Latihan berulang dengan variasi</li>\r\n</ul>\r\n<h6>3. Antisipasi Pertanyaan</h6>\r\n<ul>\r\n<li>Siapkan jawaban untuk pertanyaan umum</li>\r\n<li>Pahami limitasi pengetahuan Anda</li>\r\n<li>Siap mengakui jika tidak tahu</li>\r\n</ul>', NULL, 2, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 06:59:19'),
(7, 'public-speaking', 'persiapan-mental', 'video', 'Video: Mengatasi Demam Panggung', NULL, 'https://www.youtube.com/embed/Unzc731iCUY', 1, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 06:59:19'),
(8, 'public-speaking', 'persiapan-mental', 'text', 'Materi: Teknik Mengelola Kecemasan', '<h5>Persiapan Mental untuk Public Speaking</h5>\r\n<p>Mengatasi kecemasan dan membangun kepercayaan diri adalah langkah pertama yang penting:</p>\r\n<h6>1. Teknik Relaksasi</h6>\r\n<ul>\r\n<li>Latihan pernapasan dalam (4-7-8 breathing)</li>\r\n<li>Progressive muscle relaxation</li>\r\n<li>Visualisasi positif</li>\r\n</ul>\r\n<h6>2. Persiapan Mental</h6>\r\n<ul>\r\n<li>Positive self-talk</li>\r\n<li>Mental rehearsal skenario terbaik</li>\r\n<li>Focus pada pesan, bukan pada diri sendiri</li>\r\n</ul>\r\n<h6>3. Persiapan Fisik</h6>\r\n<ul>\r\n<li>Latihan postur tubuh yang confident</li>\r\n<li>Warming up suara dan artikulasi</li>\r\n<li>Kenali venue dan audience sebelumnya</li>\r\n</ul>', NULL, 2, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 06:59:19'),
(9, 'public-speaking', 'analisis-audiens', 'video', 'Video: Memahami dan Menganalisis Audiens', NULL, 'https://www.youtube.com/embed/19yTjO_GJ4U', 1, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 06:59:19'),
(10, 'public-speaking', 'analisis-audiens', 'text', 'Materi: Strategi Analisis Audiens', '<h5>Analisis Audiens yang Efektif</h5>\r\n<p>Memahami audiens adalah kunci komunikasi yang efektif:</p>\r\n<h6>1. Demografi Audiens</h6>\r\n<ul>\r\n<li>Usia, latar belakang pendidikan</li>\r\n<li>Profesi dan pengalaman</li>\r\n<li>Budaya dan nilai-nilai</li>\r\n</ul>\r\n<h6>2. Kebutuhan dan Ekspektasi</h6>\r\n<ul>\r\n<li>Apa yang mereka harapkan dari presentasi?</li>\r\n<li>Masalah apa yang ingin mereka selesaikan?</li>\r\n<li>Level pengetahuan tentang topik</li>\r\n</ul>\r\n<h6>3. Adaptasi Gaya Komunikasi</h6>\r\n<ul>\r\n<li>Sesuaikan bahasa dan terminologi</li>\r\n<li>Pilih contoh yang relevan</li>\r\n<li>Atur tingkat formalitas</li>\r\n</ul>', NULL, 2, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 06:59:19'),
(11, 'retorika', 'sejarah-retorika', 'video', 'Video: Sejarah Retorika dari Aristoteles', NULL, 'https://www.youtube.com/embed/CjxIcOKgMro', 1, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 06:59:19'),
(12, 'retorika', 'sejarah-retorika', 'text', 'Materi: Evolusi Retorika dalam Sejarah', '<h5>Sejarah dan Filosofi Retorika</h5>\r\n<p>Retorika memiliki sejarah panjang sejak zaman Yunani kuno:</p>\r\n<h6>1. Era Klasik (Yunani Kuno)</h6>\r\n<ul>\r\n<li>Aristoteles: Bapak retorika modern dengan konsep Ethos, Pathos, Logos</li>\r\n<li>Cicero: Pengembangan retorika di Roma</li>\r\n<li>Quintilian: Sistematisasi pendidikan retorika</li>\r\n</ul>\r\n<h6>2. Era Medieval</h6>\r\n<ul>\r\n<li>Retorika dalam sistem pendidikan Trivium</li>\r\n<li>Grammar, Logic, Rhetoric sebagai fondasi pendidikan</li>\r\n<li>Pengaruh gereja dalam perkembangan retorika</li>\r\n</ul>\r\n<h6>3. Era Modern</h6>\r\n<ul>\r\n<li>New Rhetoric movement (Perelman & Olbrechts-Tyteca)</li>\r\n<li>Retorika digital dan media sosial</li>\r\n<li>Applied rhetoric dalam bisnis dan politik</li>\r\n</ul>', NULL, 2, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 06:59:19'),
(13, 'retorika', 'ethos-pathos-logos', 'video', 'Video: Tiga Pilar Retorika Aristoteles', NULL, 'https://www.youtube.com/embed/OGmMk1D9_iQ', 1, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 06:59:19'),
(14, 'retorika', 'ethos-pathos-logos', 'text', 'Materi: Memahami Ethos, Pathos, dan Logos', '<h5>Tiga Pilar Retorika Aristoteles</h5>\r\n<p>Aristoteles mengidentifikasi tiga mode persuasi yang masih relevan hingga kini:</p>\r\n<h6>1. Ethos (Kredibilitas)</h6>\r\n<ul>\r\n<li>Membangun kepercayaan melalui karakter</li>\r\n<li>Menunjukkan expertise dan kompetensi</li>\r\n<li>Integritas dan kejujuran dalam penyampaian</li>\r\n</ul>\r\n<h6>2. Pathos (Emosi)</h6>\r\n<ul>\r\n<li>Menggunakan emosi untuk mempengaruhi</li>\r\n<li>Storytelling yang menyentuh perasaan</li>\r\n<li>Memahami emosi dan kebutuhan audiens</li>\r\n</ul>\r\n<h6>3. Logos (Logika)</h6>\r\n<ul>\r\n<li>Menggunakan alasan dan bukti yang kuat</li>\r\n<li>Struktur argumen yang logis</li>\r\n<li>Data, statistik, dan fakta yang valid</li>\r\n</ul>', NULL, 2, 1, 0, 1, '2025-06-19 06:59:19', '2025-06-19 06:59:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `submaterial_prerequisites`
--

CREATE TABLE `submaterial_prerequisites` (
  `id` int(11) NOT NULL,
  `material_key` varchar(50) NOT NULL,
  `submaterial_key` varchar(50) NOT NULL,
  `prerequisite_material_key` varchar(50) NOT NULL,
  `prerequisite_submaterial_key` varchar(50) NOT NULL,
  `is_required` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `submaterial_progress`
--

CREATE TABLE `submaterial_progress` (
  `id` int(11) NOT NULL,
  `user_id` varchar(10) DEFAULT NULL,
  `progress_id` varchar(20) DEFAULT NULL,
  `material_key` varchar(50) NOT NULL,
  `submaterial_key` varchar(50) NOT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `time_spent` int(11) DEFAULT 0,
  `last_position` text DEFAULT NULL,
  `completion_percentage` int(11) DEFAULT 0,
  `attempt_count` int(11) DEFAULT 0,
  `first_accessed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Bicaranta', 'string', 'Nama website', 1, '2025-06-19 06:07:21', '2025-06-19 06:09:54'),
(2, 'default_material_duration', '30', 'integer', 'Durasi default material dalam menit', 0, '2025-06-19 06:07:21', '2025-06-19 06:07:21'),
(3, 'max_progress_ids_per_session', '5', 'integer', 'Maksimal progress ID per session', 0, '2025-06-19 06:07:21', '2025-06-19 06:07:21'),
(4, 'enable_assessment', 'true', 'boolean', 'Aktifkan fitur assessment', 0, '2025-06-19 06:07:21', '2025-06-19 06:07:21'),
(5, 'lock_mechanism', 'sequential', 'string', 'Tipe lock mechanism: sequential, prerequisite, free', 0, '2025-06-19 06:07:21', '2025-06-19 06:07:21'),
(6, 'auto_complete_threshold', '80', 'integer', 'Persentase minimum untuk auto complete', 0, '2025-06-19 06:07:21', '2025-06-19 06:07:21');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(10) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','instructor','student') DEFAULT 'student',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `verification_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `login_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `user_id`, `name`, `phone`, `profile_picture`, `bio`, `email`, `password`, `role`, `status`, `verification_token`, `created_at`, `updated_at`, `last_login`, `login_count`, `is_active`, `deleted_at`) VALUES
(1, '00029', 'Demo User', NULL, NULL, NULL, 'demo@bicaranta.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NULL, '2025-06-18 06:50:46', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(2, '12345', 'Ahmad Rizki', NULL, NULL, NULL, 'ahmad@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instructor', 'active', NULL, '2025-06-18 06:50:46', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(3, '67890', 'Siti Nurhaliza', NULL, NULL, NULL, 'siti@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NULL, '2025-06-18 06:50:46', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(4, '11111', 'Budi Santoso', NULL, NULL, NULL, 'budi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NULL, '2025-06-18 06:50:46', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(5, '22222', 'Dina Marlina', NULL, NULL, NULL, 'dina@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NULL, '2025-06-18 06:50:46', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(6, 'ADMIN01', 'Super Admin', '081234567890', NULL, NULL, 'admin@bicaranta.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NULL, '2025-06-18 06:55:26', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(7, 'ADMIN02', 'System Admin', '081234567891', NULL, NULL, 'sysadmin@bicaranta.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NULL, '2025-06-18 06:55:26', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(8, 'INST001', 'Dr. Budi Dharma', '081234567892', NULL, 'Doktor Komunikasi dengan 15 tahun pengalaman mengajar Public Speaking', 'budi.dharma@bicaranta.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instructor', 'active', NULL, '2025-06-18 06:55:26', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(9, 'INST002', 'Prof. Sari Melati', '081234567893', NULL, 'Profesor Retorika dan Komunikasi Persuasif', 'sari.melati@bicaranta.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instructor', 'active', NULL, '2025-06-18 06:55:26', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(10, 'INST003', 'Drs. Agus Wijaya', '081234567894', NULL, 'Praktisi Storytelling dan Presentasi Bisnis', 'agus.wijaya@bicaranta.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instructor', 'active', NULL, '2025-06-18 06:55:26', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(11, 'INST004', 'Dr. Lisa Permata', '081234567895', NULL, 'Spesialis Komunikasi Non-verbal dan Bahasa Tubuh', 'lisa.permata@bicaranta.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instructor', 'active', NULL, '2025-06-18 06:55:26', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(12, 'STU001', 'Maya Sari', '081234567896', NULL, NULL, 'maya.sari@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NULL, '2025-06-18 06:55:26', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(13, 'STU002', 'Rian Pratama', '081234567897', NULL, NULL, 'rian.pratama@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NULL, '2025-06-18 06:55:26', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(14, 'STU003', 'Novi Andriani', '081234567898', NULL, NULL, 'novi.andriani@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NULL, '2025-06-18 06:55:26', '2025-06-18 06:55:26', NULL, 0, 1, NULL),
(15, 'STU004', 'Fajar Nugroho', '081234567899', NULL, NULL, 'fajar.nugroho@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NULL, '2025-06-18 06:55:26', '2025-06-18 06:55:26', NULL, 0, 1, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_progress`
--

CREATE TABLE `user_progress` (
  `id` int(11) NOT NULL,
  `user_id` varchar(10) DEFAULT NULL,
  `material_key` varchar(50) DEFAULT NULL,
  `clicks` int(11) DEFAULT 0,
  `progress_percentage` int(11) DEFAULT 0,
  `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
  `last_accessed` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user_progress`
--

INSERT INTO `user_progress` (`id`, `user_id`, `material_key`, `clicks`, `progress_percentage`, `status`, `last_accessed`) VALUES
(1, '00029', 'keterampilan-bicara', 15, 75, 'in_progress', '2025-06-18 06:50:46'),
(2, '00029', 'retorika', 8, 40, 'in_progress', '2025-06-18 06:50:46'),
(3, '00029', 'storytelling', 22, 100, 'completed', '2025-06-18 06:50:46'),
(4, '00029', 'komunikasi-nonverbal', 3, 15, 'in_progress', '2025-06-18 06:50:46'),
(5, '12345', 'keterampilan-bicara', 12, 60, 'in_progress', '2025-06-18 06:50:46'),
(6, '12345', 'public-speaking', 25, 100, 'completed', '2025-06-18 06:50:46'),
(7, '12345', 'presentasi', 18, 90, 'in_progress', '2025-06-18 06:50:46'),
(8, '67890', 'retorika', 20, 100, 'completed', '2025-06-18 06:50:46'),
(9, '67890', 'komunikasi-persuasif', 16, 80, 'in_progress', '2025-06-18 06:50:46'),
(10, '67890', 'debat-argumen', 5, 25, 'in_progress', '2025-06-18 06:50:46'),
(11, '11111', 'storytelling', 30, 100, 'completed', '2025-06-18 06:50:46'),
(12, '11111', 'presentasi', 28, 100, 'completed', '2025-06-18 06:50:46'),
(13, '11111', 'public-speaking', 14, 70, 'in_progress', '2025-06-18 06:50:46'),
(14, '22222', 'keterampilan-bicara', 24, 100, 'completed', '2025-06-18 06:50:46'),
(15, '22222', 'komunikasi-nonverbal', 19, 95, 'in_progress', '2025-06-18 06:50:46'),
(16, '22222', 'retorika', 7, 35, 'in_progress', '2025-06-18 06:50:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_progress_backup`
--

CREATE TABLE `user_progress_backup` (
  `id` int(11) NOT NULL,
  `user_id` varchar(10) DEFAULT NULL,
  `material_key` varchar(50) DEFAULT NULL,
  `clicks` int(11) DEFAULT 0,
  `progress_percentage` int(11) DEFAULT 0,
  `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
  `last_accessed` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `user_progress_detail`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `user_progress_detail` (
`user_id` varchar(10)
,`name` varchar(100)
,`material_key` varchar(50)
,`material_name` varchar(100)
,`progress_percentage` int(11)
,`status` enum('not_started','in_progress','completed')
,`total_submaterials_accessed` bigint(21)
,`submaterials_completed` bigint(21)
,`last_accessed` timestamp
);

-- --------------------------------------------------------

--
-- Struktur untuk view `material_statistics`
--
DROP TABLE IF EXISTS `material_statistics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `material_statistics`  AS SELECT `m`.`material_key` AS `material_key`, `m`.`name` AS `name`, count(distinct `s`.`id`) AS `total_submaterials`, count(distinct `up`.`user_id`) AS `total_students`, avg(`up`.`progress_percentage`) AS `avg_progress`, count(distinct case when `up`.`status` = 'completed' then `up`.`user_id` end) AS `completed_students` FROM ((`materials` `m` left join `submaterials` `s` on(`m`.`material_key` = `s`.`material_key` and `s`.`is_active` = 1)) left join `user_progress` `up` on(`m`.`material_key` = `up`.`material_key`)) WHERE `m`.`is_active` = 1 GROUP BY `m`.`material_key`, `m`.`name` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `user_progress_detail`
--
DROP TABLE IF EXISTS `user_progress_detail`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_progress_detail`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`name` AS `name`, `m`.`material_key` AS `material_key`, `m`.`name` AS `material_name`, `up`.`progress_percentage` AS `progress_percentage`, `up`.`status` AS `status`, count(`sp`.`id`) AS `total_submaterials_accessed`, count(case when `sp`.`is_completed` = 1 then 1 end) AS `submaterials_completed`, `up`.`last_accessed` AS `last_accessed` FROM (((`users` `u` join `user_progress` `up` on(`u`.`user_id` = `up`.`user_id`)) join `materials` `m` on(`up`.`material_key` = `m`.`material_key`)) left join `submaterial_progress` `sp` on(`u`.`user_id` = `sp`.`user_id` and `m`.`material_key` = `sp`.`material_key`)) WHERE `u`.`is_active` = 1 AND `m`.`is_active` = 1 GROUP BY `u`.`user_id`, `u`.`name`, `m`.`material_key`, `m`.`name`, `up`.`progress_percentage`, `up`.`status`, `up`.`last_accessed` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_user` (`user_id`),
  ADD KEY `idx_activity_type` (`activity_type`),
  ADD KEY `idx_activity_log_date` (`created_at`,`user_id`);

--
-- Indeks untuk tabel `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_assessments_material` (`material_key`,`submaterial_key`),
  ADD KEY `idx_assessments_type` (`assessment_type`,`is_active`);

--
-- Indeks untuk tabel `assessment_results`
--
ALTER TABLE `assessment_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `idx_assessment_results_user` (`user_id`,`assessment_id`),
  ADD KEY `idx_assessment_results_progress` (`progress_id`,`assessment_id`),
  ADD KEY `idx_assessment_results_score` (`score`,`is_passed`);

--
-- Indeks untuk tabel `instructor_materials`
--
ALTER TABLE `instructor_materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_instructor_material` (`instructor_id`,`material_key`),
  ADD KEY `material_key` (`material_key`),
  ADD KEY `idx_instructor_materials_instructor` (`instructor_id`);

--
-- Indeks untuk tabel `learning_sessions`
--
ALTER TABLE `learning_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_key` (`material_key`),
  ADD KEY `idx_learning_sessions_user` (`user_id`,`material_key`),
  ADD KEY `idx_learning_sessions_progress` (`progress_id`,`material_key`),
  ADD KEY `idx_learning_sessions_date` (`session_start`,`user_id`);

--
-- Indeks untuk tabel `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_key` (`material_key`),
  ADD KEY `idx_materials_active` (`is_active`,`material_key`),
  ADD KEY `idx_materials_difficulty_duration` (`difficulty`,`is_active`);

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role`,`permission`),
  ADD KEY `idx_permissions_role` (`role`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sessions_user` (`user_id`);

--
-- Indeks untuk tabel `submaterials`
--
ALTER TABLE `submaterials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_submaterial` (`material_key`,`submaterial_key`),
  ADD KEY `idx_material_key` (`material_key`),
  ADD KEY `idx_level` (`level`),
  ADD KEY `idx_sort_order` (`sort_order`),
  ADD KEY `idx_submaterials_active` (`material_key`,`is_active`,`level`,`sort_order`),
  ADD KEY `idx_submaterials_level_order` (`material_key`,`level`,`sort_order`,`is_active`);

--
-- Indeks untuk tabel `submaterial_content`
--
ALTER TABLE `submaterial_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_submaterial_content` (`material_key`,`submaterial_key`,`display_order`),
  ADD KEY `idx_content_submaterial` (`material_key`,`submaterial_key`,`display_order`),
  ADD KEY `idx_content_type` (`content_type`,`is_active`),
  ADD KEY `idx_submaterial_content_active` (`is_active`,`material_key`,`submaterial_key`),
  ADD KEY `idx_submaterial_content_order` (`material_key`,`submaterial_key`,`display_order`);

--
-- Indeks untuk tabel `submaterial_prerequisites`
--
ALTER TABLE `submaterial_prerequisites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_prerequisite` (`material_key`,`submaterial_key`,`prerequisite_material_key`,`prerequisite_submaterial_key`),
  ADD KEY `idx_prerequisites` (`material_key`,`submaterial_key`),
  ADD KEY `idx_prerequisite_check` (`prerequisite_material_key`,`prerequisite_submaterial_key`);

--
-- Indeks untuk tabel `submaterial_progress`
--
ALTER TABLE `submaterial_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_submaterial` (`user_id`,`material_key`,`submaterial_key`),
  ADD UNIQUE KEY `unique_progress_submaterial` (`progress_id`,`material_key`,`submaterial_key`),
  ADD KEY `idx_user_material` (`user_id`,`material_key`),
  ADD KEY `idx_progress_material` (`progress_id`,`material_key`),
  ADD KEY `idx_submaterial_progress_completed` (`is_completed`,`user_id`);

--
-- Indeks untuk tabel `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_settings_key` (`setting_key`,`is_public`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_verification_token` (`verification_token`);

--
-- Indeks untuk tabel `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_material` (`user_id`,`material_key`),
  ADD KEY `idx_user_progress_user` (`user_id`),
  ADD KEY `idx_user_progress_material` (`material_key`),
  ADD KEY `idx_user_progress_status` (`status`,`user_id`);

--
-- Indeks untuk tabel `user_progress_backup`
--
ALTER TABLE `user_progress_backup`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_material` (`user_id`,`material_key`),
  ADD KEY `idx_user_progress_user` (`user_id`),
  ADD KEY `idx_user_progress_material` (`material_key`),
  ADD KEY `idx_user_progress_status` (`status`,`user_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `assessments`
--
ALTER TABLE `assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `assessment_results`
--
ALTER TABLE `assessment_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `instructor_materials`
--
ALTER TABLE `instructor_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `learning_sessions`
--
ALTER TABLE `learning_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `submaterials`
--
ALTER TABLE `submaterials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `submaterial_content`
--
ALTER TABLE `submaterial_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `submaterial_prerequisites`
--
ALTER TABLE `submaterial_prerequisites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `submaterial_progress`
--
ALTER TABLE `submaterial_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `user_progress_backup`
--
ALTER TABLE `user_progress_backup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`material_key`) REFERENCES `materials` (`material_key`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `assessment_results`
--
ALTER TABLE `assessment_results`
  ADD CONSTRAINT `assessment_results_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_results_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `instructor_materials`
--
ALTER TABLE `instructor_materials`
  ADD CONSTRAINT `instructor_materials_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `instructor_materials_ibfk_2` FOREIGN KEY (`material_key`) REFERENCES `materials` (`material_key`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `learning_sessions`
--
ALTER TABLE `learning_sessions`
  ADD CONSTRAINT `learning_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `learning_sessions_ibfk_2` FOREIGN KEY (`material_key`) REFERENCES `materials` (`material_key`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `submaterial_content`
--
ALTER TABLE `submaterial_content`
  ADD CONSTRAINT `submaterial_content_ibfk_1` FOREIGN KEY (`material_key`) REFERENCES `materials` (`material_key`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `submaterial_prerequisites`
--
ALTER TABLE `submaterial_prerequisites`
  ADD CONSTRAINT `submaterial_prerequisites_ibfk_1` FOREIGN KEY (`material_key`) REFERENCES `materials` (`material_key`) ON DELETE CASCADE,
  ADD CONSTRAINT `submaterial_prerequisites_ibfk_2` FOREIGN KEY (`prerequisite_material_key`) REFERENCES `materials` (`material_key`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`material_key`) REFERENCES `materials` (`material_key`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
