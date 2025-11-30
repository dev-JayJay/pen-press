-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 07:20 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `penpress_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('sports','business','features') NOT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `reporter_id`, `title`, `description`, `category`, `status`, `created_at`) VALUES
(1, 18, 'check', 'ksksksk', '', 'pending', '2025-11-20 15:42:06'),
(2, 18, 'this', 'that', '', 'completed', '2025-11-20 16:08:56'),
(3, 18, 'this is the task', 'this is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the taskthis is the task', '', 'completed', '2025-11-20 16:20:40'),
(4, 18, 'this is new', 'this is newthis is newthis is new', 'sports', 'completed', '2025-11-20 17:05:34');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `created_at`) VALUES
(1, 5, 2, 'hello myom', '2025-11-17 17:28:02'),
(2, 2, 5, 'hello sir', '2025-11-17 17:28:33'),
(3, 2, 2, 'hey', '2025-11-17 17:29:02'),
(4, 5, 2, 'hello', '2025-11-20 14:49:24'),
(5, 5, 5, 'hello sir', '2025-11-20 14:49:39'),
(6, 18, 5, 'hello myom', '2025-11-20 16:52:33'),
(7, 2, 18, 'hello Jay how are you doing', '2025-11-20 16:54:01'),
(8, 18, 2, 'i am doing fine thank you sir', '2025-11-20 16:55:24'),
(9, 5, 18, 'this is not myom', '2025-11-20 16:57:45'),
(10, 5, 18, 'this is editor in chife', '2025-11-20 16:58:08'),
(11, 5, 2, 'myom this is editor in chife', '2025-11-20 16:58:18'),
(12, 5, 2, 'can you confirm', '2025-11-20 16:58:25'),
(13, 2, 5, 'yes sir i can see that you are the editor in chife', '2025-11-20 16:59:04'),
(14, 2, 5, 'test confirm', '2025-11-20 16:59:16'),
(15, 18, 5, 'okay sir i can confirm', '2025-11-20 16:59:57');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `body` text NOT NULL,
  `category` enum('sport','business','features') NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `assignment_id` int(11) DEFAULT NULL,
  `status` enum('draft','submitted','approved','published','rejected') DEFAULT 'draft',
  `review_comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reporter_id` int(11) DEFAULT NULL,
  `edited_by` int(11) DEFAULT NULL,
  `eic_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `eic_comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `summary`, `body`, `category`, `image_path`, `author_id`, `assignment_id`, `status`, `review_comment`, `created_at`, `updated_at`, `reporter_id`, `edited_by`, `eic_status`, `eic_comment`) VALUES
(1, 'checking', 'checking-691b5427341c6', 'jsjsjsjssj', 'jjdjdjdjd', 'sport', 'uploads/news/691b54272f67e.jpg', 2, NULL, 'approved', '', '2025-11-17 16:58:15', '2025-11-17 17:29:55', NULL, NULL, 'pending', NULL),
(2, 'checking', 'checking-691b545b122a9', 'jsjsjsjssj', 'jjdjdjdjd', 'sport', 'uploads/news/691b545b11c77.jpg', 2, NULL, 'approved', '', '2025-11-17 16:59:07', '2025-11-17 17:17:03', NULL, NULL, 'pending', NULL),
(3, 'this is the note', 'this-is-the-note-691b6e916e9c5', 'not summary', 'not summary', 'sport', 'uploads/news/691b6e916e681.png', 2, NULL, 'rejected', '', '2025-11-17 18:50:57', '2025-11-17 21:26:02', NULL, NULL, 'pending', NULL),
(4, 'i woter the new', 'i-woter-the-new-691b9165c9ee5', 'this is the new summary', 'this is the bosy of the neww', 'business', 'uploads/news/691b9165bdb5e.jpg', 6, NULL, 'approved', 'nice', '2025-11-17 21:19:33', '2025-11-20 13:11:23', NULL, NULL, 'pending', NULL),
(8, 'this', '', 'this', 'this', 'sport', 'uploads/news/691f450a486b7.png', 18, 3, 'approved', 'good', '2025-11-20 16:28:16', '2025-11-20 22:20:54', NULL, 2, 'approved', ''),
(10, 'The NEW NEWS i agent edit after edit again', 'the-new-news-691f4cc6a435e', 'The NEW NEWSThe NEW NEWS i agent editi agent editi agent editi agent editi agent editi agent editi agent edit', 'The NEW NEWSThe NEW NEWS i agent editi agent editi agent editi agent edit', 'sport', 'uploads/news/691f4cc6a436e.png', 18, 4, 'approved', '', '2025-11-20 17:15:50', '2025-11-20 22:20:41', NULL, 2, 'approved', 'nice');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `payload` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reactions`
--

CREATE TABLE `reactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `type` enum('like') DEFAULT 'like',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reactions`
--

INSERT INTO `reactions` (`id`, `user_id`, `news_id`, `type`, `created_at`) VALUES
(1, 1, 1, 'like', '2025-11-17 18:58:09'),
(2, 1, 2, '', '2025-11-17 19:01:38'),
(3, 1, 4, 'like', '2025-11-17 21:30:31'),
(4, 1, 8, 'like', '2025-11-20 16:44:53');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('reader','editor','editor_in_chief','reporter') NOT NULL,
  `editor_type` enum('sport','business','features') DEFAULT NULL,
  `admission_no` varchar(50) DEFAULT NULL,
  `faculty` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `level` varchar(20) DEFAULT NULL,
  `passport_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `editor_type`, `admission_no`, `faculty`, `department`, `level`, `passport_path`, `created_at`) VALUES
(1, 'Fatima', 'Usman', 'jaytrycode@gmail.com', '$2y$10$Mdf0kNl/mbidRQ.JAiwWDOQfv8tL1O55Yh8DBhOiDx7becKLr8LR2', 'reader', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-17 16:13:51'),
(2, 'Myom', 'Bulus', 'jaytrycode1@gmail.com', '$2y$10$rJjjhK0zysZO9TC.SpFipOwzOoVtaRwK5MX1Was0/Ix4p/X19sj/O', 'editor', 'sport', '9383838393', 'ssksksksks', 'computersecien', '100', 'uploads/passports/691b51f97ccec.png', '2025-11-17 16:48:57'),
(5, 'Admin', 'Chief', 'eic@penpress.com', '$2y$10$2Vxhp6SqQookbXOg1zPabeQGnva2DW0cDnHX6WbxyhXHtHNyHnijm', 'editor_in_chief', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-17 17:14:17'),
(6, 'Mathew', 'Bulus', 'jay@gmail.com', '$2y$10$Mg/Z9ob/7KWv0LyczIKSSOrxDOOYtHeFXmDj2GfnKRROnza863j6S', 'editor', 'business', '9383838393', 'ssksksksks', 'computersecien', '100', 'uploads/passports/691b90652b966.png', '2025-11-17 21:15:17'),
(7, 'feature', 'editor', 'jayeditor@gmail.com', '$2y$10$QJkYVIplrJVqh/nOV828se9ksFpbHBV0r6NEM6Ul7oa/KGSka74He', 'editor', 'features', '9383838393', 'ssksksksks', 'computersecien', '100', 'uploads/passports/691b908d8684f.jpg', '2025-11-17 21:15:57'),
(18, 'Repoter', 'Jay', 'repoterJay2@gmail.com', '$2y$10$EQ.tMQCjntbJfLBvBGt8m.n99lYbI8JaYsqfETMZ0Ln8RmBEPQSWO', 'reporter', NULL, '9383838393', 'ssksksksks', 'computersecien', '100', NULL, '2025-11-20 15:41:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reporter_id` (`reporter_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `news_ibfk_2` (`assignment_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `news_id` (`news_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reactions`
--
ALTER TABLE `reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `news_ibfk_2` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reactions`
--
ALTER TABLE `reactions`
  ADD CONSTRAINT `reactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reactions_ibfk_2` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
