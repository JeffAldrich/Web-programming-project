-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 13, 2026 at 01:01 PM
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
-- Database: `jac_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `first_name`, `last_name`, `email`, `password`, `created_at`) VALUES
(1, 'JAC', 'Administrator', 'admin@jac.com', '$2y$10$EYwW/7Vb/w6afaeOhAvcjObUUEQkCM/eiqGX4DObiRSrm/Xqlo4jy', '2026-09-08 07:02:23');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `created_at`) VALUES
(2, 1, '2026-09-07 09:05:32'),
(3, 3, '2026-09-08 05:48:55');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(6, 'Suits', 'JAC suits designed for timeless elegance and sophistication.'),
(7, 'Shirts', 'JAC shirts designed for refined and versatile styling.'),
(8, 'Vests', 'JAC vests designed to complement formal outfits.'),
(9, 'Trousers', 'JAC trousers designed for a polished and sophisticated look.'),
(10, 'Accessories', 'JAC accessories designed to complete your formal wardrobe.');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`) VALUES
(1, 'test123@gmail.com', '2026-09-11 14:26:53');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `shipping_name` varchar(200) NOT NULL,
  `shipping_email` varchar(255) NOT NULL,
  `shipping_phone` varchar(30) DEFAULT NULL,
  `shipping_address` varchar(255) NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_province` varchar(100) DEFAULT NULL,
  `shipping_postal_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `shipping_name`, `shipping_email`, `shipping_phone`, `shipping_address`, `shipping_city`, `shipping_province`, `shipping_postal_code`, `created_at`) VALUES
(1, 3, 4999.00, 'Cancelled', 'jeff test', 'test123@gmail.com', '09171234567', '123 Test Street', 'Dumangas', 'Iloilo', '5006', '2026-09-09 03:37:11'),
(2, 3, 4999.00, 'Processing', 'jeff test', 'test123@gmail.com', '1234567', 'Cervantes Street', 'Dumaguete', 'Negros Oriental', '6200', '2026-09-09 03:50:42'),
(3, 3, 9998.00, 'Shipped', 'jeff test', 'test123@gmail.com', '09123456789', '123 Test Street', 'Dumans', 'underground', '5006', '2026-09-09 11:39:29'),
(4, 3, 4999.00, 'Pending', 'jeff test', 'test123@gmail.com', '09123456789', '123 Test Street', 'Dumangasty', 'DEEZ', '5006', '2026-09-09 11:47:19'),
(5, 3, 47992.00, 'Pending', 'jeff test', 'test123@gmail.com', '09123456789', '123 Test Street', 'Dumangas', 'Iloilo', '5006', '2026-09-10 05:51:14');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `quantity`, `price`) VALUES
(1, 1, 1, 4, 1, 4999.00),
(2, 2, 1, 2, 1, 4999.00),
(3, 3, 1, 4, 2, 4999.00),
(4, 4, 1, 4, 1, 4999.00),
(5, 5, 2, 7, 8, 5999.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stock` int(11) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `image`, `created_at`, `stock`) VALUES
(1, 6, 'Classic Single-Breasted Suit', 'A timeless single-breasted suit designed for a clean and sophisticated appearance.', 4999.00, 'single-breasted.jpg', '2026-09-06 04:27:02', 10),
(2, 6, 'Classic Double-Breasted Suit', 'A refined double-breasted suit with a distinctive formal silhouette.', 5999.00, 'double breasted.jpg', '2026-09-06 04:27:02', 10),
(3, 6, 'Classic Three-Piece Suit', 'A complete three-piece suit designed for elegant formal occasions.', 6999.00, 'Three-Piece Suit.jpg', '2026-09-06 04:27:02', 10),
(4, 6, 'Classic Peak Lapel Suit', 'A sharp and elegant suit featuring a sophisticated peak lapel.', 5499.00, 'peak lapel suit.jpg', '2026-09-06 04:27:02', 10),
(5, 6, 'Classic Shawl Lapel Suit', 'A refined formal suit featuring a smooth shawl lapel design.', 5499.00, 'shawl lepel suit.jpg', '2026-09-06 04:27:02', 10),
(6, 7, 'Long Sleeve', 'A classic long sleeve shirt designed for a clean and timeless appearance.', 5999.00, 'white long sleeve.webp', '2026-09-06 04:54:51', 10),
(7, 7, 'Long Sleeve Turtleneck Sweater', 'A refined turtleneck sweater suitable for sophisticated and elegant outfits.', 5999.00, 'turtle neck.webp', '2026-09-06 04:54:51', 10),
(8, 8, 'Slim Fit Waistcoat', 'A slim fit waistcoat designed to complement formal and sophisticated outfits.', 5999.00, 'waistcoat.webp', '2026-09-06 04:54:51', 10);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(20) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `size`, `color`, `stock`) VALUES
(1, 1, 'S', 'Black', 10),
(2, 1, 'M', 'Black', 10),
(3, 1, 'L', 'Black', 10),
(4, 1, 'XL', 'Black', 10),
(6, 2, 'M', 'Black', 10),
(7, 2, 'L', 'Black', 10),
(8, 2, 'XL', 'Black', 10),
(9, 3, 'S', 'Black', 10),
(10, 3, 'M', 'Black', 10),
(11, 3, 'L', 'Black', 10),
(12, 3, 'XL', 'Black', 10),
(13, 4, 'S', 'Black', 10),
(14, 4, 'M', 'Black', 10),
(15, 4, 'L', 'Black', 10),
(16, 4, 'XL', 'Black', 10),
(17, 5, 'S', 'Black', 10),
(18, 5, 'M', 'Black', 10),
(19, 5, 'L', 'Black', 10),
(20, 5, 'XL', 'Black', 10),
(21, 1, 'M', 'White', 10);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `address`, `city`, `province`, `postal_code`, `created_at`, `last_login`) VALUES
(1, 'Test', 'User', 'test@jac.com', 'test123', NULL, NULL, NULL, NULL, NULL, '2026-09-07 09:04:45', NULL),
(2, 'jeff', 'test', 'test1@gmail.com', '$2y$10$R3xFnqR0jGgV7I23xJ5zzunHtp3ZKr/32HsHfLvsbM1PVgwycRRRG', NULL, NULL, NULL, NULL, NULL, '2026-09-07 10:42:04', NULL),
(3, 'jeff', 'test', 'test123@gmail.com', '$2y$10$pTrO1fZLmo/gKS58D4cQW.5l0BfwoGbGzF2DBpBJz6Qts6TF.t5vu', '09123456789', '123 Test Street', 'Dumangas', 'Iloilo', '5006', '2026-09-07 11:28:19', '2026-09-13 18:54:12'),
(4, 'jeff', 'test', 'test12@gmail.com', '$2y$10$u0ymx1SmKIYcUjZJISA3SOx3rPqg0FPSHVk9M.2cSvmEf7zhl/M9a', NULL, NULL, NULL, NULL, NULL, '2026-09-07 11:30:12', NULL),
(5, 'jeff', 't', 'test@gmail.com', '$2y$10$ZF3dV0ZSW9O.I8GO6RoqoeGQXZWTdpbyMOdq8s7LSe5k5RM8IFGfi', NULL, NULL, NULL, NULL, NULL, '2026-09-07 11:59:31', NULL),
(6, 'jeff', 'test', 'test120@gmail.com', '$2y$10$5oBGVURF6AROSc5VALt8I.gVFMLnl1rkTiNEYpWhxLEzyZX3TZL5y', NULL, NULL, NULL, NULL, NULL, '2026-09-07 12:31:28', NULL),
(7, 'jeff', 'test', 'test0@gmail.com', '$2y$10$KA5K6/8kJ6jjvWLStDIsOO1vfiCD7a8miPy4ZzJYGc4650aAZ0ltq', NULL, NULL, NULL, NULL, NULL, '2026-09-07 12:35:02', NULL),
(8, 'blackhole', 'tests', 'test69@gmail.com', '$2y$10$15j5y2twFssFfU786OQOYOGAlsxG0vnnyQppn3clSv4nTyhNsar1m', NULL, NULL, NULL, NULL, NULL, '2026-09-10 05:54:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_id` (`product_id`,`size`,`color`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
