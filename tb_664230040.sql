-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2025 at 06:38 AM
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
-- Database: `db6646_040`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_664230040`
--

CREATE TABLE `tb_664230040` (
  `id` int(5) NOT NULL COMMENT 'ลำดับ',
  `std_id` varchar(9) NOT NULL COMMENT 'รหัสนักศึกษา',
  `f_name` varchar(100) NOT NULL COMMENT 'ชื่อ',
  `L_name` varchar(100) NOT NULL COMMENT 'สกุล',
  `mail` varchar(100) NOT NULL COMMENT 'อีเมล',
  `tel` varchar(20) NOT NULL COMMENT 'เบอร์โทร',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'เวลาสร้าง',
  `class` varchar(5) NOT NULL COMMENT 'ห้อง'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_664230040`
--

INSERT INTO `tb_664230040` (`id`, `std_id`, `f_name`, `L_name`, `mail`, `tel`, `created_at`, `class`) VALUES
(6, '66', '66', '66', '66@g.c', '66', '2025-10-02 04:19:42', '66');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_664230040`
--
ALTER TABLE `tb_664230040`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_664230040`
--
ALTER TABLE `tb_664230040`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT COMMENT 'ลำดับ', AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
