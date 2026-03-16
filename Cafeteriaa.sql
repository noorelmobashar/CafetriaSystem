-- Updated schema (MySQL) 

CREATE TABLE `users` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255),
  `email` varchar(255),
  `password_hash` varchar(255),
  `role` ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  `profile_pic` varchar(255),
  `created_at` datetime,
  `updated_at` datetime
);

CREATE TABLE `categories` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255),
  `description` text,
  `created_at` datetime
);

CREATE TABLE `products` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255),
  `price` decimal(10,2),
  `category_id` int,
  `available` boolean,
  `image_path` varchar(255),
  `created_at` datetime,
  `updated_at` datetime
);

CREATE TABLE `orders` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `user_id` int,
  `order_date` datetime,
  `status` ENUM('incoming','processing','out for delivery','done') NOT NULL DEFAULT 'incoming',
  `total_amount` decimal(10,2),
  `room_snapshot` ENUM('100','200','300','400','500','600','700','800','900','1000'),
  `notes` text,
  `created_at` datetime,
  `updated_at` datetime
);

CREATE TABLE `order_items` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `order_id` int,
  `product_id` int,
  `quantity` int,
  `unit_price` decimal(10,2)
);

-- Foreign keys
ALTER TABLE `products`
  ADD FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

ALTER TABLE `orders`
  ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `order_items`
  ADD FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

ALTER TABLE `order_items`
  ADD FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);