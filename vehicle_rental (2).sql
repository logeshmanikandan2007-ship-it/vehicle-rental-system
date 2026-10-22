-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2026 at 10:40 AM
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
-- Database: `vehicle_rental`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `vehicle_name` varchar(100) NOT NULL,
  `booking_date` date NOT NULL,
  `start_km` decimal(10,2) NOT NULL DEFAULT 0.00,
  `end_km` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_km` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_fare` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(30) NOT NULL DEFAULT 'Booked'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_name`, `vehicle_id`, `vehicle_name`, `booking_date`, `start_km`, `end_km`, `total_km`, `total_fare`, `created_at`, `status`) VALUES
(6, 'loki', 1, 'Toyota Innova', '2026-08-26', 0.00, 1250.00, 1250.00, 25000.00, '2026-08-25 04:56:58', 'Pending Payment'),
(9, 'logesh', 2, 'Honda City', '2026-09-19', 10000.00, 10100.00, 100.00, 1500.00, '2026-09-19 08:47:56', 'Completed'),
(10, 'logesh', 2, 'Honda City', '2026-09-29', 10000.00, 10100.00, 100.00, 1500.00, '2026-09-27 05:51:23', 'Completed'),
(13, 'karthi', 4, 'Royal Enfield', '2026-10-05', 12000.00, 13000.00, 1000.00, 10000.00, '2026-10-04 12:15:28', 'Completed'),
(14, 'loki', 5, 'Yamaha R15', '2026-10-16', 12000.00, 12300.00, 300.00, 2700.00, '2026-10-15 08:31:08', 'Pending Payment'),
(15, 'Logesh', 5, 'Yamaha R15', '2026-10-15', 12000.00, 12500.00, 500.00, 4500.00, '2026-10-15 08:42:35', 'Completed'),
(16, 'Logesh', 5, 'Yamaha R15', '2026-10-15', 12000.00, 12500.00, 500.00, 4500.00, '2026-10-15 08:57:45', 'Completed'),
(17, 'Logesh', 5, 'Yamaha R15', '2026-10-15', 12000.00, 0.00, 0.00, 0.00, '2026-10-15 09:14:53', 'Booked'),
(18, 'Logesh', 5, 'Yamaha R15', '2026-10-15', 12000.19, 12400.00, 399.81, 3598.29, '2026-10-15 09:38:05', 'Pending Payment'),
(19, 'Logesh', 5, 'Yamaha R15', '2026-10-15', 12000.00, 12500.00, 500.00, 4500.00, '2026-10-15 09:39:00', 'Pending Payment'),
(20, 'Logesh', 5, 'Yamaha R15', '2026-10-15', 12000.00, 12500.00, 500.00, 4500.00, '2026-10-15 09:47:20', 'Completed'),
(21, 'Logesh', 5, 'Yamaha R15', '2026-10-15', 12300.00, 12330.00, 30.00, 270.00, '2026-10-15 10:11:41', 'Pending Payment'),
(22, 'Logesh', 2, 'Honda City', '2026-10-16', 12000.00, 0.00, 0.00, 0.00, '2026-10-15 14:52:32', 'Booked'),
(23, 'Logesh', 4, 'Royal Enfield', '2026-10-15', 1000.00, 0.00, 0.00, 0.00, '2026-10-15 15:01:00', 'Booked'),
(24, 'Logesh', 5, 'Yamaha R15', '2026-10-21', 12000.00, 0.00, 0.00, 0.00, '2026-10-21 09:15:37', 'Booked'),
(25, 'Logesh', 4, 'Royal Enfield', '2026-10-21', 1200.00, 1500.00, 300.00, 3000.00, '2026-10-21 09:18:10', 'Pending Payment'),
(26, 'Logesh', 1, 'Toyota Innova', '2026-10-21', 12000.00, 0.00, 0.00, 0.00, '2026-10-21 14:18:03', 'Booked'),
(27, 'Logesh', 5, 'Yamaha R15', '2026-10-22', 12000.00, 0.00, 0.00, 0.00, '2026-10-22 05:17:51', 'Booked');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`) VALUES
(1, 'Logesh', 'logesh@gmail.com', '12345');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `vehicle_name` varchar(100) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `price_per_km` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rate_per_km` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) DEFAULT 'Available',
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `vehicle_name`, `vehicle_type`, `price_per_km`, `rate_per_km`, `status`, `image`) VALUES
(1, 'Toyota Innova', 'Car', 20.00, 20.00, 'Available', 'Toyota-Innova.jpg'),
(2, 'Honda City', 'Car', 15.00, 15.00, 'Available', 'Honda-City.jpg'),
(3, 'Maruti Swift', 'Car', 12.00, 12.00, 'Available', 'Maruti-Swift.jpg'),
(4, 'Royal Enfield', 'Bike', 10.00, 10.00, 'Available', 'Royal-Enfield.jpg'),
(5, 'Yamaha R15', 'Bike', 9.00, 9.00, 'Available', 'Yamaha-R15-New.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_locations`
--

CREATE TABLE `vehicle_locations` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_locations`
--

INSERT INTO `vehicle_locations` (`id`, `booking_id`, `latitude`, `longitude`, `updated_at`) VALUES
(1, 17, 11.2277020, 78.1691230, '2026-10-15 09:15:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicle_locations`
--
ALTER TABLE `vehicle_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vehicle_locations`
--
ALTER TABLE `vehicle_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `vehicle_locations`
--
ALTER TABLE `vehicle_locations`
  ADD CONSTRAINT `vehicle_locations_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
