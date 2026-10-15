-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2026 at 03:53 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `volt_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(4, 'admin', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(2, 'Headphones'),
(1, 'Smartwatches'),
(5, 'Speakers'),
(4, 'Wireless Earbuds'),
(3, 'Wireless Neckbands');

-- --------------------------------------------------------

--
-- Table structure for table `offer_bars`
--

CREATE TABLE `offer_bars` (
  `id` int(11) NOT NULL,
  `text_message` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `offer_bars`
--

INSERT INTO `offer_bars` (`id`, `text_message`, `is_active`) VALUES
(1, '⚡ FLASH SALE: Premium Volt Audio gear up to 60% off! ⚡', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `order_date`) VALUES
(1, 1, '2222.00', '2026-07-31 05:55:17'),
(2, 1, '1649.50', '2026-08-01 04:11:45'),
(3, 2, '2499.00', '2026-08-21 05:23:40'),
(4, 2, '2499.00', '2026-08-21 05:24:17'),
(5, 3, '899.50', '2026-08-21 14:40:38'),
(6, 1, '3299.00', '2026-10-10 02:13:51'),
(7, 1, '1699.00', '2026-10-10 02:32:18'),
(8, 1, '1699.00', '2026-10-10 02:32:29'),
(9, 1, '1699.00', '2026-10-10 02:47:34'),
(10, 1, '1699.00', '2026-10-10 02:51:05'),
(11, 3, '599.50', '2026-10-14 04:38:13'),
(12, 3, '959.20', '2026-10-14 04:39:43'),
(13, 3, '1249.50', '2026-10-14 04:46:38'),
(14, 3, '599.00', '2026-10-14 04:50:06'),
(15, 3, '1149.00', '2026-10-14 04:50:52');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 4, 1, '2222.00'),
(2, 2, 2, 1, '3299.00'),
(3, 3, 1, 1, '2499.00'),
(4, 4, 1, 1, '2499.00'),
(5, 5, 3, 1, '1799.00'),
(6, 6, 2, 1, '3299.00'),
(7, 7, 14, 1, '1699.00'),
(8, 8, 14, 1, '1699.00'),
(9, 9, 14, 1, '1699.00'),
(10, 10, 14, 1, '1699.00'),
(11, 11, 10, 1, '1199.00'),
(12, 12, 10, 1, '1199.00'),
(13, 13, 1, 1, '1249.50'),
(14, 14, 10, 1, '599.00'),
(15, 15, 15, 1, '249.00'),
(16, 15, 3, 1, '899.00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `discount` varchar(50) DEFAULT '50% off',
  `image` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 4.5,
  `reviews` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `original_price`, `discount`, `image`, `category_id`, `rating`, `reviews`, `created_at`) VALUES
(1, 'Volt Rockerz Futuristic Edition', 'Next-gen over-ear headphones featuring carbon fiber composite drivers, adaptive ANC, and up to 40 hours of playback.', '2499.00', '4999.00', '50% off', 'volt-headphone-futuristic.png', 2, '4.9', 142, '2026-07-17 05:21:07'),
(2, 'Volt Sonic Boom 5', 'Flagship premium speaker featuring an illuminated AMOLED micro-casing display and medical-grade heart metrics.', '3299.00', '7999.00', '58% off', 'Volt Sonic Boom 5.png', 5, '4.8', 96, '2026-07-17 05:21:07'),
(3, 'The VOLT BassBuds', 'Highly ergonomic wireless neckband utilizing dynamic graphene audio soundstages and flexible lightweight structural bands.', '1799.00', '3999.00', '55% off', 'The VOLT BassBuds.png', 3, '4.7', 210, '2026-07-17 05:21:07'),
(4, 'smart watche x1', 'dsdfsdf', '2222.00', '4443.99', '50% off', 'The VOLT FitWatch X1.png', 1, '4.5', 0, '2026-07-17 05:59:16'),
(5, 'Volt Pods E1', 'this is a best selling product', '1199.95', '2298.98', '50% off', 'Volt Pods E1.png', 4, '4.5', 0, '2026-07-17 11:26:07'),
(6, 'Volt Sonic Over-Ear Headphones', 'Active Noise Cancellation (ANC), 40-hour battery life, Deep Bass Drivers, Lightweight Comfort Design, Bluetooth 5.2.', '5999.00', '3999.00', '60% off', 'Volt Sonic (V-HP01).png', 2, '4.5', 0, '2026-07-17 13:44:03'),
(7, 'Volt Pulse Smartwatch', '1.4\" HD Retina Display, Heart Rate & SpO2 Monitor, 5ATM Water Resistance, Built-in GPS, Multi-Sport Tracking.', '2499.00', '4999.00', '50% off', 'Volt Pulse (V-SW05).png', 1, '4.5', 0, '2026-07-17 13:46:42'),
(8, 'Volt Flex Neckband Earphones', 'Flex-Form Cable, Magnetic Earbuds, 25-hour Playtime, Environmental Noise Cancellation (ENC), Quick Charge (10 mins = 5 hours).', '2499.00', '4998.98', '50% off', 'Volt Flex (V-NB20).png', 3, '4.5', 0, '2026-07-17 13:49:18'),
(9, 'Volt Blast Portable Speaker', '360-Degree Sound, IPX7 Waterproof, 20-hour Playtime, Wireless Stereo Pairing (TWS), Integrated Lanyard.', '2899.00', '5598.99', '50% off', 'Volt Blast (V-PK30).png', 5, '4.5', 0, '2026-07-17 13:51:25'),
(10, 'Volt Budz Wireless Earbuds', 'True Wireless via Bluetooth 5.3, In-Ear Detection, Touch Controls, Transparency Mode, 28-hour Total escuchando con Case.', '1199.00', '2299.00', '50% off', 'Volt Budz (V-TW40).png', 4, '4.5', 0, '2026-07-17 13:53:30'),
(11, 'VOLT \'QUANTUM\'', 'A premium, high-performance smartwatch featuring a durable, lightweight casing and a sophisticated multi-link metal band that combines brushed stainless steel with matte navy blue ceramic links. The watch has a large, high-resolution sapphire crystal display showing a detailed, customizable analog-digital watch face with multiple complications (such as a chronograph, world timer, and fitness data). The \'VOLT\' branding is clearly visible on the side of the case and debossed on the deployment clasp. It is presented against a clean, minimalist grey studio background, highlighting its modern and luxurious design.', '1599.00', '2498.99', '50% off', 'VOLT \'QUANTUM\'.png', 1, '4.5', 0, '2026-07-18 12:14:26'),
(12, 'VOLT \'ARC\' Over-Ear', 'A premium pair of over-ear headphones featuring a sophisticated and modern design. The earcups and headband are crafted from brushed gunmetal grey metal, complemented by rich cognac-brown leather padding on the earcups and the top of the headband. The headphones are presented on a custom-designed, minimalist brushed metal stand. The \'VOLT\' branding is clearly and legibly debossed onto the outer shell of both earcups and subtly placed on the base of the stand, highlighting its identity as a luxury audio accessory. The overall aesthetic is professional, high-end, and minimalist.', '1799.00', '2199.00', '60% off', 'VOLT \'ARC\' Over-Ear.png', 2, '4.5', 0, '2026-07-18 12:18:10'),
(13, 'VOLT \'FLUX\' Neckband', 'A premium, flexible neckband-style earphone featuring a sleek, modern design. The band itself is crafted from a matte-black silicone material with a contrasting electric blue inner lining. It terminates in substantial brushed metal cylindrical end caps, each featuring a debossed \'V\' logo. The attached in-ear earbuds are matte black with matching blue accents. The device is presented on a textured, industrial concrete surface, highlighting its durable and high-tech aesthetic.', '899.00', '1299.00', '50% off', 'VOLT \'FLUX\' Neckband.png', 3, '4.5', 0, '2026-07-18 12:21:33'),
(14, 'VOLT \'PRISMA\' Portable Speaker', 'A premium, geometrically sculpted portable speaker featuring a unique multi-faceted design. The speaker body is crafted from a combination of textured dark grey fabric on the main grille faces and brushed bronze-gold metallic end caps. The end cap on the visible side features a subtle, debossed \'V\' logo. It is presented on a rich, polished walnut wood surface against a softly blurred, textured concrete wall, emphasizing its modern and elegant aesthetic.', '1699.00', '2499.00', '50% off', 'VOLT \'PRISMA\' Portable Speaker.png', 5, '4.5', 0, '2026-07-18 12:23:53'),
(15, 'VOLT \'ZENITH\' TWS Earbuds', 'A premium pair of true wireless earbuds presented in their open matte-black charging case. The earbuds themselves feature a distinctive, faceted geometric design on their outer shells, giving them a modern and sculpted look. The charging case also has a matching matte finish and features a subtle \'V\' logo on the front, along with a small LED battery indicator. The set is displayed elegantly on a rich, polished walnut wood surface against a softly blurred, minimalist concrete background, emphasizing its high-end design.', '499.00', '1199.00', '50% off', 'VOLT \'ZENITH\' TWS Earbuds.png', 4, '4.5', 0, '2026-07-18 12:26:45');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 1, 2, 5, 'THIS IS A GOOD PRODUCT', '2026-08-01 04:12:34'),
(2, 3, 3, 5, '', '2026-08-21 14:40:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'jay', 'jay1@gmail.com', '$2y$10$fFv17z/KXqP1FSMMBG/9reU4aSBG6SVxPbAEFcOS/8GU9aLjYwc4m', '2026-07-31 05:55:03'),
(2, 'savan', 'savan1@gmail.com', '$2y$10$NvH0U6HNLizFYMvzrTlL.OgxvaBPY7Gvy/iV.LCyfS8KNBS/Y./x6', '2026-08-21 05:23:09'),
(3, 'meet', 'meet1@gmail.com', '$2y$10$RZ5/EHiVLgzWg8NQbAlRYONWKnao9yD8UuOhOAvRtL4lOjkMzMGge', '2026-08-21 14:40:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `offer_bars`
--
ALTER TABLE `offer_bars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `offer_bars`
--
ALTER TABLE `offer_bars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
