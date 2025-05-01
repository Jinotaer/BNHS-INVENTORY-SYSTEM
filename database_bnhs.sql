-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 01, 2025 at 08:01 AM
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
-- Database: `database_bnhs`
--

-- --------------------------------------------------------

--
-- Table structure for table `bnhs_admin`
--

CREATE TABLE `bnhs_admin` (
  `admin_id` int(15) NOT NULL,
  `admin_name` varchar(255) DEFAULT NULL,
  `admin_email` varchar(255) DEFAULT NULL,
  `admin_password` varchar(255) DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL ON UPDATE current_timestamp(6),
  `resetcode` int(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bnhs_admin`
--

INSERT INTO `bnhs_admin` (`admin_id`, `admin_name`, `admin_email`, `admin_password`, `created_at`, `resetcode`) VALUES
(2147483647, 'admin', '2301106754@student.buksu.edu.ph', 'a346bc80408d9b2a5063fd1bddb20e2d5586ec30', '2025-04-30 08:46:59.323739', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bnhs_staff`
--

CREATE TABLE `bnhs_staff` (
  `staff_id` int(15) NOT NULL,
  `staff_name` varchar(255) DEFAULT NULL,
  `staff_email` varchar(255) DEFAULT NULL,
  `staff_password` varchar(255) DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `resetcode` int(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bnhs_staff`
--

INSERT INTO `bnhs_staff` (`staff_id`, `staff_name`, `staff_email`, `staff_password`, `created_at`, `resetcode`) VALUES
(2134436578, 'stave saturn', 'taerjino51@gmail.com', 'b102ce1d5eebac2b6d74bda8c87c47a050c80491', '2025-04-30 16:28:53.622537', NULL),
(2147483647, 'Custodian', 'jjane0248@gmail.com', 'a346bc80408d9b2a5063fd1bddb20e2d5586ec30', '2025-04-30 08:43:27.623132', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `entities`
--

CREATE TABLE `entities` (
  `entity_id` int(11) NOT NULL,
  `entity_name` varchar(100) NOT NULL,
  `fund_cluster` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `entities`
--

INSERT INTO `entities` (`entity_id`, `entity_name`, `fund_cluster`, `created_at`, `updated_at`) VALUES
(20, 'Bukidnon National High Schoolsss', 'Division', '2025-04-29 13:07:36', '2025-04-30 09:33:50'),
(21, 'Bukidnon National High School', 'MCE', '2025-04-29 13:12:56', '2025-04-29 13:12:56'),
(22, 'Bukidnon National High School', 'MOE', '2025-04-29 13:17:31', '2025-04-29 13:17:31'),
(23, 'Bukidnon National High School', 'MOOE', '2025-04-29 13:23:29', '2025-04-29 13:23:29'),
(24, 'Bukidnon National High School', 'Divisionsss', '2025-04-29 14:23:17', '2025-04-29 14:23:17'),
(25, 'Bukidnon National High Schoolss', 'Divisionsss', '2025-04-29 14:23:55', '2025-04-29 14:23:55'),
(26, 'Bukidnon National High Schoolss', 'Division', '2025-04-29 14:27:27', '2025-04-29 14:27:27'),
(27, 'Bukidnon National High School', 'MCEsdsas', '2025-04-29 14:29:26', '2025-04-29 14:29:26'),
(28, 'Bukidnon National High School', 'Divisionssssss', '2025-04-29 14:29:40', '2025-04-29 14:29:40'),
(29, 'Bukidnon National High School', 'MCEsssss', '2025-04-29 14:34:01', '2025-04-29 14:34:01'),
(30, 'Bukidnon National High School', 'Division', '2025-04-30 11:29:25', '2025-04-30 11:29:25'),
(31, 'Bukidnon National High School', 'JHS', '2025-04-30 15:28:53', '2025-04-30 15:28:53');

-- --------------------------------------------------------

--
-- Table structure for table `iar_items`
--

CREATE TABLE `iar_items` (
  `iar_item_id` int(11) NOT NULL,
  `iar_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iar_items`
--

INSERT INTO `iar_items` (`iar_item_id`, `iar_id`, `item_id`, `quantity`, `unit_price`, `total_price`, `remarks`, `created_at`) VALUES
(60, 60, 133, 5, 4500.00, 22500.00, 'Non-Consumable', '2025-04-30 14:43:30'),
(61, 60, 134, 10, 200.00, 2000.00, 'Consumable', '2025-04-30 14:43:30'),
(62, 61, 135, 6, 350.00, 2100.00, 'Consumable', '2025-04-30 14:46:25'),
(63, 61, 136, 8, 120.00, 960.00, 'Consumable', '2025-04-30 14:46:25'),
(64, 62, 137, 4, 150.00, 600.00, 'Consumable', '2025-04-30 14:49:08'),
(65, 62, 138, 10, 1200.00, 12000.00, 'Non-Consumable', '2025-04-30 14:49:08'),
(66, 63, 139, 4, 800.00, 3200.00, 'Non-Consumable', '2025-04-30 14:52:25'),
(67, 63, 140, 15, 500.00, 7500.00, 'Consumable', '2025-04-30 14:52:25'),
(68, 64, 141, 6, 950.00, 5700.00, 'Non-Consumable', '2025-04-30 14:55:12'),
(69, 64, 142, 20, 80.00, 1600.00, 'Consumable', '2025-04-30 14:55:12');

-- --------------------------------------------------------

--
-- Table structure for table `ics_items`
--

CREATE TABLE `ics_items` (
  `ics_item_id` int(11) NOT NULL,
  `ics_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `article` varchar(255) NOT NULL,
  `remarks` text NOT NULL,
  `inventory_item_no` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ics_items`
--

INSERT INTO `ics_items` (`ics_item_id`, `ics_id`, `item_id`, `quantity`, `article`, `remarks`, `inventory_item_no`, `created_at`) VALUES
(44, 51, 143, 2, 'SEMI- EXPENDABLE IT EQUIPMENT', 'G', '2025-001', '2025-04-30 15:01:21'),
(45, 51, 144, 3, 'SEMI- EXPENDABLE OFFICE PROPERTY', 'Good condition', 'ICS-2025-002', '2025-04-30 15:01:21'),
(46, 52, 145, 4, 'SEMI-EXPENDABLE FURNITURE AND FIXTURES', 'Good condition', 'ICS-2025-003', '2025-04-30 15:04:15'),
(47, 52, 146, 5, 'SEMI-EXPENDABLE FURNITURE AND FIXTURES', 'Damaged', 'ICS-2025-004', '2025-04-30 15:04:15'),
(50, 56, 148, 6, 'SEMI-EXPENDABLE FURNITURE AND FIXTURES', 'Good condition', 'ICS-2025-005', '2025-04-30 15:11:18'),
(51, 56, 149, 10, 'SEMI- EXPENDABLE IT EQUIPMENT', 'Good condition', 'ICS-2025-006', '2025-04-30 15:11:18'),
(52, 57, 150, 1, 'SEMI- EXPENDABLE IT EQUIPMENT', 'Good condition', 'ICS-2025-007', '2025-04-30 15:13:01'),
(53, 57, 151, 2, 'SEMI- EXPENDABLE SCIENCE AND MATH EQUIPMENT', 'Good condition', 'ICS-2025-008', '2025-04-30 15:13:01'),
(54, 58, 152, 5, 'SEMI- EXPENDABLE OFFICE PROPERTY', 'Good condition', 'ICS-2025-009', '2025-04-30 15:14:51'),
(55, 58, 153, 2, 'SEMI- EXPENDABLE IT EQUIPMENT', 'Damaged', 'ICS-2025-010', '2025-04-30 15:14:51');

-- --------------------------------------------------------

--
-- Table structure for table `inspection_acceptance_reports`
--

CREATE TABLE `inspection_acceptance_reports` (
  `iar_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `iar_no` varchar(50) NOT NULL,
  `po_no_date` varchar(100) DEFAULT NULL,
  `req_office` varchar(100) DEFAULT NULL,
  `responsibility_center` varchar(100) DEFAULT NULL,
  `iar_date` date NOT NULL,
  `invoice_no_date` varchar(100) DEFAULT NULL,
  `receiver_name` varchar(100) NOT NULL,
  `teacher_id` varchar(50) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `date_inspected` date DEFAULT NULL,
  `inspectors` text DEFAULT NULL,
  `barangay_councilor` varchar(100) DEFAULT NULL,
  `pta_observer` varchar(100) DEFAULT NULL,
  `date_received` date DEFAULT NULL,
  `property_custodian` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inspection_acceptance_reports`
--

INSERT INTO `inspection_acceptance_reports` (`iar_id`, `entity_id`, `supplier_id`, `iar_no`, `po_no_date`, `req_office`, `responsibility_center`, `iar_date`, `invoice_no_date`, `receiver_name`, `teacher_id`, `position`, `date_inspected`, `inspectors`, `barangay_councilor`, `pta_observer`, `date_received`, `property_custodian`, `created_at`, `updated_at`) VALUES
(60, 30, 12, 'IAR-2025-001', 'PO-2025-001', 'STEM Department', '101-2025-01', '2025-04-30', '1', 'Maria Dela Cruz', 'TID-052214', 'Head Teacher III', '2025-04-30', 'Joan Savaege', 'Hon. Pedro M. Luna', 'Ana G. Valencia', '2025-04-30', 'Stefany Jane Bernabe', '2025-04-30 14:43:30', '2025-04-30 14:43:30'),
(61, 21, 13, 'IAR-2025-002', 'PO-25-002', 'HUMSS Department', '101-2025-02', '2025-04-30', '2', 'Anthony', 'TID-052215', 'Teacher 2', '2025-04-30', 'Leah Reyes', 'Brain Nelson', 'Lapids lar', '2025-04-30', 'Stefany Jane Bernabesss', '2025-04-30 14:46:25', '2025-04-30 14:46:25'),
(62, 22, 14, 'IAR-2025-018', 'PO-25-003', 'High School Department', '101-2025-03', '2025-04-30', '3', 'TOfff', 'TID-052216', 'Head Teacher III', '2025-04-30', 'Joan Savaege', 'Hon. Pedro M. Luna', 'Lapids Lar', '2025-04-30', 'Stefany Jane Bernabesss', '2025-04-30 14:49:08', '2025-04-30 14:49:08'),
(63, 22, 13, '23-04-004', 'PO-2025-004', 'ABM Department', '101-2025-04', '2025-04-30', '4', 'Angelie Cole', 'TID-052216', 'Teacher II', '2025-04-30', 'Joan Savaege', 'Hon. Pedro M. Luna', 'Ana G. Valencia', '2025-04-30', 'Stefany Jane Bernabesss', '2025-04-30 14:52:25', '2025-04-30 14:52:25'),
(64, 30, 14, 'IAR-2025-005', 'PO-25-005', 'GAS Department', '101-2025-05', '2025-04-30', '5', 'steve silmilyo', 'TID-052216', 'Teacher II', '2025-04-30', 'Joan Savage', 'Hon. Pedro M. Luna', 'Lapids Lar', '2025-04-30', 'Rodrigo A. Fernandez', '2025-04-30 14:55:12', '2025-04-30 14:55:12');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_custodian_slips`
--

CREATE TABLE `inventory_custodian_slips` (
  `ics_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `ics_no` varchar(50) NOT NULL,
  `end_user_name` varchar(100) NOT NULL,
  `end_user_position` varchar(100) DEFAULT NULL,
  `end_user_date` date DEFAULT NULL,
  `custodian_name` varchar(100) NOT NULL,
  `custodian_position` varchar(100) DEFAULT NULL,
  `custodian_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_custodian_slips`
--

INSERT INTO `inventory_custodian_slips` (`ics_id`, `entity_id`, `ics_no`, `end_user_name`, `end_user_position`, `end_user_date`, `custodian_name`, `custodian_position`, `custodian_date`, `created_at`, `updated_at`) VALUES
(51, 30, 'ICS-2025-001', 'Angela Reyes', 'Teacher II', '2025-04-30', 'STEFANY JANE B. LABADAN', 'Property Custodian', '2025-04-30', '2025-04-30 15:01:21', '2025-04-30 15:01:21'),
(52, 21, 'ICS-2025-002', 'toff vergara', 'Teacher II', '2025-04-30', 'STEFANY JANE B. LABADAN', 'Property Custodian', '2025-04-30', '2025-04-30 15:04:15', '2025-04-30 15:04:15'),
(53, 21, 'ICS-2025-003', 'Samantha Boone', 'Teacher II', '2025-04-30', 'STEFANY JANE B. LABADAN', 'Property Custodian', '2025-04-30', '2025-04-30 15:06:24', '2025-04-30 15:06:24'),
(54, 21, 'ICS-2025-004', 'Samantha Boone', 'Teacher II', '2025-04-30', 'STEFANY JANE B. LABADAN', 'Property Custodian', '2025-04-30', '2025-04-30 15:08:27', '2025-04-30 15:08:27'),
(56, 21, 'ICS-2025-005', 'Anthony Black', 'Teacher II', '2025-04-30', 'STEFANY JANE B. LABADAN', 'Property Custodian', '2025-04-30', '2025-04-30 15:11:18', '2025-04-30 15:11:18'),
(57, 22, 'ICS-2025-006', 'Samantha Boone', 'Teacher II', '2025-04-30', 'STEFANY JANE B. LABADAN', 'Property Custodian', '2025-04-30', '2025-04-30 15:13:01', '2025-04-30 15:13:01'),
(58, 30, 'ICS-2025-007', 'Dave Saturno', 'Teacher II', '2025-04-30', 'STEFANY JANE B. LABADAN', 'Property Custodian', '2025-04-30', '2025-04-30 15:14:51', '2025-04-30 15:14:51');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `stock_no` varchar(50) DEFAULT NULL,
  `item_description` text NOT NULL,
  `unit` varchar(20) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `estimated_useful_life` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `article` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `stock_no`, `item_description`, `unit`, `unit_cost`, `estimated_useful_life`, `created_at`, `updated_at`, `article`, `remarks`) VALUES
(133, '2025-001', 'Microscope', 'pieces', 4500.00, 5, '2025-04-30 14:43:30', '2025-04-30 14:43:30', NULL, NULL),
(134, '2025-002', 'Alcohol Lamp', 'pieces', 200.00, 5, '2025-04-30 14:43:30', '2025-04-30 14:43:30', NULL, NULL),
(135, '2025-003', 'Beakers (500ml)', 'pieces', 350.00, 5, '2025-04-30 14:46:25', '2025-04-30 14:46:25', NULL, NULL),
(136, '2025-004', 'Test Tube Rack', 'pieces', 120.00, 5, '2025-04-30 14:46:25', '2025-04-30 14:46:25', NULL, NULL),
(137, '2025-005', 'Whiteboard Markers', 'box', 150.00, 5, '2025-04-30 14:49:08', '2025-04-30 14:49:08', NULL, NULL),
(138, '2025-006', 'Scientific Calculator', 'pieces', 1200.00, 5, '2025-04-30 14:49:08', '2025-04-30 14:49:08', NULL, NULL),
(139, '2025-007', 'Dissecting Kits', 'box', 800.00, 5, '2025-04-30 14:52:25', '2025-04-30 14:52:25', NULL, NULL),
(140, '2025-008', 'Lab Coats', 'pieces', 500.00, 5, '2025-04-30 14:52:25', '2025-04-30 14:52:25', NULL, NULL),
(141, '2025-009', 'Thermometer (digital)', 'pieces', 950.00, 5, '2025-04-30 14:55:12', '2025-04-30 14:55:12', NULL, NULL),
(142, '2025-010', 'Litmus Paper (pack)', 'box', 80.00, 5, '2025-04-30 14:55:12', '2025-04-30 14:55:12', NULL, NULL),
(143, NULL, 'Digital Weighing Scale', 'pieces', 3200.00, 5, '2025-04-30 15:01:21', '2025-04-30 15:01:21', NULL, NULL),
(144, NULL, 'Electric Microscope', 'pieces', 45000.00, 0, '2025-04-30 15:01:21', '2025-04-30 15:01:21', NULL, NULL),
(145, NULL, 'Office Table', 'pieces', 2800.00, 10, '2025-04-30 15:04:15', '2025-04-30 15:04:15', NULL, NULL),
(146, NULL, 'Desktop Computer', 'box', 110000.00, 4, '2025-04-30 15:04:15', '2025-04-30 15:04:15', NULL, NULL),
(147, NULL, 'Office Table', '', 2800.00, 10, '2025-04-30 15:06:24', '2025-04-30 15:06:24', NULL, NULL),
(148, NULL, 'Swivel Chair', 'pieces', 1500.00, 10, '2025-04-30 15:11:18', '2025-04-30 15:11:18', NULL, NULL),
(149, NULL, 'Laboratory Glassware Set', 'box', 1000.00, 4, '2025-04-30 15:11:18', '2025-04-30 15:11:18', NULL, NULL),
(150, NULL, 'Projector', 'pieces', 18000.00, 4, '2025-04-30 15:13:01', '2025-04-30 15:13:01', NULL, NULL),
(151, NULL, 'Portable Whiteboard', 'pieces', 20000.00, 5, '2025-04-30 15:13:01', '2025-04-30 15:13:01', NULL, NULL),
(152, NULL, 'Extension Wire (5m)', 'pieces', 500.00, 3, '2025-04-30 15:14:51', '2025-04-30 15:14:51', NULL, NULL),
(153, NULL, 'Wireless Router', 'pieces', 30000.00, 10, '2025-04-30 15:14:51', '2025-04-30 15:14:51', NULL, NULL),
(154, 'RIS-001', 'Bond Paper (A4)', 'box', 0.00, NULL, '2025-04-30 15:18:43', '2025-04-30 15:18:43', NULL, NULL),
(155, 'RIS-002', 'Whiteboard Markers', 'box', 0.00, NULL, '2025-04-30 15:18:43', '2025-04-30 15:18:43', NULL, NULL),
(156, 'RIS-003', 'Printer Ink (Black)', 'box', 0.00, NULL, '2025-04-30 15:22:05', '2025-04-30 15:22:05', NULL, NULL),
(157, 'RIS-004', 'Staplers', 'pieces', 0.00, NULL, '2025-04-30 15:22:05', '2025-04-30 15:22:05', NULL, NULL),
(158, 'RIS-054', 'Masking Tape (2&quot;)', 'pieces', 0.00, NULL, '2025-04-30 15:22:05', '2025-04-30 15:22:05', NULL, NULL),
(159, 'RIS-007', 'Manila Paper', 'pieces', 0.00, NULL, '2025-04-30 15:26:46', '2025-04-30 15:26:46', NULL, NULL),
(160, 'RIS-008', 'Folder (Long, Kraft)', 'pieces', 0.00, NULL, '2025-04-30 15:26:46', '2025-04-30 15:26:46', NULL, NULL),
(161, 'RIS-009', 'Permanent Marker (Black)', 'pieces', 0.00, NULL, '2025-04-30 15:26:46', '2025-04-30 15:26:46', NULL, NULL),
(162, 'RIS-0010', 'Correction Tape', 'pieces', 0.00, NULL, '2025-04-30 15:28:53', '2025-04-30 15:28:53', NULL, NULL),
(163, 'RIS-0011', 'Pencil #2', 'box', 0.00, NULL, '2025-04-30 15:28:53', '2025-04-30 15:28:53', NULL, NULL),
(164, NULL, 'Laptop (Intel i5, 8GB RAM)', 'pieces', 32000.00, NULL, '2025-04-30 15:31:41', '2025-04-30 15:31:41', NULL, NULL),
(165, NULL, 'Electric Fan (Stand Type)', 'pieces', 2500.00, NULL, '2025-04-30 15:31:41', '2025-04-30 15:31:41', NULL, NULL),
(166, NULL, 'Filing Cabinet (4-layer)', 'pieces', 5200.00, NULL, '2025-04-30 15:31:41', '2025-04-30 15:31:41', NULL, NULL),
(167, NULL, 'Printer (LaserJet)', 'pieces', 12000.00, NULL, '2025-04-30 15:34:18', '2025-04-30 15:34:18', NULL, NULL),
(168, NULL, 'Extension Wire (10m)	(LaserJet)', 'pieces', 600.00, NULL, '2025-04-30 15:34:18', '2025-04-30 15:34:18', NULL, NULL),
(169, NULL, 'Monoblock Chairs (White)', 'pieces', 350.00, NULL, '2025-04-30 15:34:18', '2025-04-30 15:34:18', NULL, NULL),
(170, NULL, 'Wi-Fi Router', 'pieces', 2800.00, NULL, '2025-04-30 15:36:14', '2025-04-30 15:36:14', NULL, NULL),
(171, NULL, 'Digital Multimeter', 'pieces', 1300.00, NULL, '2025-04-30 15:36:14', '2025-04-30 15:36:14', NULL, NULL),
(172, NULL, 'Rechargeable Flashlight', 'pieces', 950.00, NULL, '2025-04-30 15:36:14', '2025-04-30 15:36:14', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `par_items`
--

CREATE TABLE `par_items` (
  `par_item_id` int(11) NOT NULL,
  `par_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `article` varchar(255) NOT NULL,
  `remarks` text NOT NULL,
  `property_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `par_items`
--

INSERT INTO `par_items` (`par_item_id`, `par_id`, `item_id`, `quantity`, `article`, `remarks`, `property_number`, `created_at`) VALUES
(21, 11, 164, 2, 'IT EQUIPMENT', 'Received in full', '2025-PAR-001', '2025-04-30 15:31:41'),
(22, 11, 165, 3, 'IT EQUIPMENT', 'Received in full', '2025-PAR-002', '2025-04-30 15:31:41'),
(23, 11, 166, 1, 'School Building', 'Good condition', '2025-PAR-003', '2025-04-30 15:31:41'),
(24, 12, 167, 1, 'LAND', 'Good condition', '2025-PAR-004', '2025-04-30 15:34:18'),
(25, 12, 168, 5, 'LAND', 'Good condition', '2025-PAR-005', '2025-04-30 15:34:18'),
(26, 12, 169, 20, 'LAND', 'Good condition', '2025-PAR-006', '2025-04-30 15:34:18'),
(27, 13, 170, 2, 'BUILDING', 'Operational and good condition', '2025-PAR-008', '2025-04-30 15:36:14'),
(28, 13, 171, 3, 'BUILDING', 'Operational and good condition', '2025-PAR-009', '2025-04-30 15:36:14'),
(29, 13, 172, 3, 'BUILDING', 'Operational and good condition', '2025-PAR-010', '2025-04-30 15:36:14');

-- --------------------------------------------------------

--
-- Table structure for table `property_acknowledgment_receipts`
--

CREATE TABLE `property_acknowledgment_receipts` (
  `par_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `par_no` varchar(50) NOT NULL,
  `date_acquired` date NOT NULL,
  `end_user_name` varchar(100) NOT NULL,
  `receiver_position` varchar(100) DEFAULT NULL,
  `receiver_date` date DEFAULT NULL,
  `custodian_name` varchar(100) NOT NULL,
  `custodian_position` varchar(100) DEFAULT NULL,
  `custodian_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_acknowledgment_receipts`
--

INSERT INTO `property_acknowledgment_receipts` (`par_id`, `entity_id`, `par_no`, `date_acquired`, `end_user_name`, `receiver_position`, `receiver_date`, `custodian_name`, `custodian_position`, `custodian_date`, `created_at`, `updated_at`) VALUES
(11, 21, 'PAR-2025-001', '2025-04-30', 'Angela Reyes', 'Science Teacher / Science Department', '2025-04-30', 'Rodrigo A. Fernandez', 'Admin Officer / Property &amp; Supply Office', '2025-04-30', '2025-04-30 15:31:41', '2025-04-30 15:31:41'),
(12, 21, 'PAR-2025-002', '2025-04-30', 'Anthony Black', 'Teacher II', '2025-04-30', 'STEFANY JANE B. LABADAN', 'Property Custodian', '2025-04-30', '2025-04-30 15:34:18', '2025-04-30 15:34:18'),
(13, 21, 'PAR-2025-003', '2025-04-30', 'qwe', 'Teacher II', '2025-04-30', 'STEFANY JANE B. LABADAN', 'Property Custodian', '2025-04-30', '2025-04-30 15:36:14', '2025-04-30 15:36:14');

-- --------------------------------------------------------

--
-- Table structure for table `requisition_and_issue_slips`
--

CREATE TABLE `requisition_and_issue_slips` (
  `ris_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `division` varchar(100) DEFAULT NULL,
  `office` varchar(100) DEFAULT NULL,
  `responsibility_code` varchar(50) DEFAULT NULL,
  `ris_no` varchar(50) NOT NULL,
  `purpose` text DEFAULT NULL,
  `requested_by_name` varchar(100) NOT NULL,
  `requested_by_designation` varchar(100) DEFAULT NULL,
  `requested_by_date` date DEFAULT NULL,
  `approved_by_name` varchar(100) DEFAULT NULL,
  `approved_by_designation` varchar(100) DEFAULT NULL,
  `approved_by_date` date DEFAULT NULL,
  `issued_by_name` varchar(100) DEFAULT NULL,
  `issued_by_designation` varchar(100) DEFAULT NULL,
  `issued_by_date` date DEFAULT NULL,
  `received_by_name` varchar(100) DEFAULT NULL,
  `received_by_designation` varchar(100) DEFAULT NULL,
  `received_by_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requisition_and_issue_slips`
--

INSERT INTO `requisition_and_issue_slips` (`ris_id`, `entity_id`, `division`, `office`, `responsibility_code`, `ris_no`, `purpose`, `requested_by_name`, `requested_by_designation`, `requested_by_date`, `approved_by_name`, `approved_by_designation`, `approved_by_date`, `issued_by_name`, `issued_by_designation`, `issued_by_date`, `received_by_name`, `received_by_designation`, `received_by_date`, `created_at`, `updated_at`) VALUES
(22, 30, 'Malaybalay', 'GAS Department', '101-4567-01', 'RIS-2025-001', 'IT Days', 'Angela Reyes', 'Science Teacher', '2025-04-30', 'Dr. Manuel Ortega', 'Principal III', '2025-04-30', 'Liza Montano', 'Supply Officer', '2025-04-30', 'Anthony Black', 'Science Teacher', '2025-04-30', '2025-04-30 15:18:43', '2025-04-30 15:18:43'),
(23, 21, 'Malaybalay', 'HUMSS Department', '101-4567-02', 'RIS-2025-002', 'HM Daysssss', 'Angelie Cole', 'Science Teacher', '2025-04-30', 'Jannacole Macapuno', 'Secondary School Principal II', '2025-04-30', 'STEFANY JANE B. LABADAN', 'Administrative Officer II', '2025-04-30', 'Samantha Boone', 'Science Teacher', '2025-04-30', '2025-04-30 15:22:05', '2025-04-30 15:22:05'),
(24, 21, 'Malaybalay', 'ABM Department', '101-4567-03', 'RIS-2025-003', 'IT Days', 'Samantha Boone', 'Teacher II', '2025-04-30', 'Jannacole Macapuno', 'Secondary School Principal II', '2025-04-30', 'STEFANY JANE B. LABADAN', 'Administrative Officer II', '2025-04-30', 'Samantha Boone', 'Teacher II', '2025-04-30', '2025-04-30 15:26:46', '2025-04-30 15:26:46'),
(25, 31, 'Malaybalay', 'TVL Department', '101-4567-04', 'RIS-2025-005', '3rd quarter science activities, printing, and classroom material', 'dave', 'Science Teacher', '2025-04-30', 'Jannacole Macapuno', 'Principal III', '2025-04-30', 'STEFANY JANE B. LABADAN', 'Administrative Officer II', '2025-04-30', 'Samantha Boone', 'Teacher II', '2025-04-30', '2025-04-30 15:28:53', '2025-04-30 15:28:53');

-- --------------------------------------------------------

--
-- Table structure for table `ris_items`
--

CREATE TABLE `ris_items` (
  `ris_item_id` int(11) NOT NULL,
  `ris_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `requested_qty` int(11) NOT NULL,
  `stock_available` varchar(11) DEFAULT NULL,
  `issued_qty` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ris_items`
--

INSERT INTO `ris_items` (`ris_item_id`, `ris_id`, `item_id`, `requested_qty`, `stock_available`, `issued_qty`, `remarks`, `created_at`) VALUES
(25, 22, 154, 10, 'yes', 10, 'Consumable', '2025-04-30 15:18:43'),
(26, 22, 155, 5, '1', 5, 'Consumable', '2025-04-30 15:18:43'),
(27, 23, 156, 5, 'yes', 5, 'Consumable', '2025-04-30 15:22:05'),
(28, 23, 157, 6, '1', 7, 'Consumable', '2025-04-30 15:22:05'),
(29, 23, 158, 20, '1', 8, 'Consumable', '2025-04-30 15:22:05'),
(30, 24, 159, 6, 'yes', 6, 'Consumable', '2025-04-30 15:26:46'),
(31, 24, 160, 20, '1', 20, 'Consumable', '2025-04-30 15:26:46'),
(32, 24, 161, 50, '0', 50, 'Consumable', '2025-04-30 15:26:46'),
(33, 25, 162, 5, 'yes', 5, 'Consumable', '2025-04-30 15:28:53'),
(34, 25, 163, 4, '1', 4, 'Consumable', '2025-04-30 15:28:53');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_info`, `created_at`, `updated_at`) VALUES
(12, 'EduTech Supplies Inc', NULL, '2025-04-30 14:43:30', '2025-04-30 14:43:30'),
(13, 'Shopinas', NULL, '2025-04-30 14:46:25', '2025-04-30 14:46:25'),
(14, 'ML store', NULL, '2025-04-30 14:49:08', '2025-04-30 14:49:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bnhs_admin`
--
ALTER TABLE `bnhs_admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_email` (`admin_email`);

--
-- Indexes for table `bnhs_staff`
--
ALTER TABLE `bnhs_staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `staff_email` (`staff_email`);

--
-- Indexes for table `entities`
--
ALTER TABLE `entities`
  ADD PRIMARY KEY (`entity_id`);

--
-- Indexes for table `iar_items`
--
ALTER TABLE `iar_items`
  ADD PRIMARY KEY (`iar_item_id`),
  ADD KEY `iar_id` (`iar_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `ics_items`
--
ALTER TABLE `ics_items`
  ADD PRIMARY KEY (`ics_item_id`),
  ADD UNIQUE KEY `inventory_item_no` (`inventory_item_no`),
  ADD KEY `ics_id` (`ics_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `idx_inventory_item_no` (`inventory_item_no`);

--
-- Indexes for table `inspection_acceptance_reports`
--
ALTER TABLE `inspection_acceptance_reports`
  ADD PRIMARY KEY (`iar_id`),
  ADD UNIQUE KEY `iar_no` (`iar_no`),
  ADD KEY `entity_id` (`entity_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `idx_iar_no` (`iar_no`);

--
-- Indexes for table `inventory_custodian_slips`
--
ALTER TABLE `inventory_custodian_slips`
  ADD PRIMARY KEY (`ics_id`),
  ADD UNIQUE KEY `ics_no` (`ics_no`),
  ADD KEY `entity_id` (`entity_id`),
  ADD KEY `idx_ics_no` (`ics_no`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD UNIQUE KEY `stock_no` (`stock_no`),
  ADD KEY `idx_stock_no` (`stock_no`);

--
-- Indexes for table `par_items`
--
ALTER TABLE `par_items`
  ADD PRIMARY KEY (`par_item_id`),
  ADD UNIQUE KEY `property_number` (`property_number`),
  ADD KEY `par_id` (`par_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `idx_property_number` (`property_number`);

--
-- Indexes for table `property_acknowledgment_receipts`
--
ALTER TABLE `property_acknowledgment_receipts`
  ADD PRIMARY KEY (`par_id`),
  ADD UNIQUE KEY `par_no` (`par_no`),
  ADD KEY `entity_id` (`entity_id`),
  ADD KEY `idx_par_no` (`par_no`);

--
-- Indexes for table `requisition_and_issue_slips`
--
ALTER TABLE `requisition_and_issue_slips`
  ADD PRIMARY KEY (`ris_id`),
  ADD UNIQUE KEY `ris_no` (`ris_no`),
  ADD KEY `entity_id` (`entity_id`),
  ADD KEY `idx_ris_no` (`ris_no`);

--
-- Indexes for table `ris_items`
--
ALTER TABLE `ris_items`
  ADD PRIMARY KEY (`ris_item_id`),
  ADD KEY `ris_id` (`ris_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `entities`
--
ALTER TABLE `entities`
  MODIFY `entity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `iar_items`
--
ALTER TABLE `iar_items`
  MODIFY `iar_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `ics_items`
--
ALTER TABLE `ics_items`
  MODIFY `ics_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `inspection_acceptance_reports`
--
ALTER TABLE `inspection_acceptance_reports`
  MODIFY `iar_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `inventory_custodian_slips`
--
ALTER TABLE `inventory_custodian_slips`
  MODIFY `ics_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `par_items`
--
ALTER TABLE `par_items`
  MODIFY `par_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `property_acknowledgment_receipts`
--
ALTER TABLE `property_acknowledgment_receipts`
  MODIFY `par_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `requisition_and_issue_slips`
--
ALTER TABLE `requisition_and_issue_slips`
  MODIFY `ris_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `ris_items`
--
ALTER TABLE `ris_items`
  MODIFY `ris_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `iar_items`
--
ALTER TABLE `iar_items`
  ADD CONSTRAINT `iar_items_ibfk_1` FOREIGN KEY (`iar_id`) REFERENCES `inspection_acceptance_reports` (`iar_id`),
  ADD CONSTRAINT `iar_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `ics_items`
--
ALTER TABLE `ics_items`
  ADD CONSTRAINT `ics_items_ibfk_1` FOREIGN KEY (`ics_id`) REFERENCES `inventory_custodian_slips` (`ics_id`),
  ADD CONSTRAINT `ics_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `inspection_acceptance_reports`
--
ALTER TABLE `inspection_acceptance_reports`
  ADD CONSTRAINT `inspection_acceptance_reports_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`entity_id`),
  ADD CONSTRAINT `inspection_acceptance_reports_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `inventory_custodian_slips`
--
ALTER TABLE `inventory_custodian_slips`
  ADD CONSTRAINT `inventory_custodian_slips_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`entity_id`);

--
-- Constraints for table `par_items`
--
ALTER TABLE `par_items`
  ADD CONSTRAINT `par_items_ibfk_1` FOREIGN KEY (`par_id`) REFERENCES `property_acknowledgment_receipts` (`par_id`),
  ADD CONSTRAINT `par_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `property_acknowledgment_receipts`
--
ALTER TABLE `property_acknowledgment_receipts`
  ADD CONSTRAINT `property_acknowledgment_receipts_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`entity_id`);

--
-- Constraints for table `requisition_and_issue_slips`
--
ALTER TABLE `requisition_and_issue_slips`
  ADD CONSTRAINT `requisition_and_issue_slips_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`entity_id`);

--
-- Constraints for table `ris_items`
--
ALTER TABLE `ris_items`
  ADD CONSTRAINT `ris_items_ibfk_1` FOREIGN KEY (`ris_id`) REFERENCES `requisition_and_issue_slips` (`ris_id`),
  ADD CONSTRAINT `ris_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
