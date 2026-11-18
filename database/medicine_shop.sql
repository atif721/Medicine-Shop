CREATE DATABASE IF NOT EXISTS `medicine_shop`;

USE `medicine_shop`;

-- ------------------------------------------------------------
-- Admins
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

-- ------------------------------------------------------------
-- Categories
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

-- ------------------------------------------------------------
-- Products
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `generic_name` VARCHAR(150) DEFAULT NULL,
    `category_id` INT NOT NULL,
    `unit` VARCHAR(50) NOT NULL DEFAULT 'Piece',
    `cost_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `selling_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `mrp` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `stock` INT NOT NULL DEFAULT 0,
    `reorder_level` INT NOT NULL DEFAULT 0,
    `expiry_date` DATE NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
);

-- ------------------------------------------------------------
-- Sales
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sales` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `invoice_no` VARCHAR(30) NOT NULL UNIQUE,
    `customer_name` VARCHAR(100) DEFAULT NULL,
    `customer_phone` VARCHAR(20) DEFAULT NULL,
    `subtotal` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `discount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `grand_total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `payment_method` VARCHAR(30) NOT NULL DEFAULT 'Cash',
    `total_cost` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `sold_by` INT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`sold_by`) REFERENCES `admins` (`id`) ON DELETE
    SET
        NULL
);

-- ------------------------------------------------------------
-- Sale Items
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sale_items` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `sale_id` INT NOT NULL,
    `product_id` INT DEFAULT NULL,
    `product_name` VARCHAR(150) NOT NULL,
    `quantity` INT NOT NULL,
    `unit_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `cost_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `line_total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE
    SET
        NULL
);

-- ------------------------------------------------------------
-- Starter Categories
-- ------------------------------------------------------------
INSERT INTO
    `categories` (`name`, `description`)
VALUES
    ('Tablet', 'Oral tablet medicines'),
    ('Capsule', 'Oral capsule medicines'),
    ('Syrup', 'Liquid oral medicines'),
    ('Injection', 'Injectable medicines'),
    ('Ointment/Cream', 'Topical ointments and creams'),
    ('Drops', 'Eye, ear and nasal drops'),
    (
        'First Aid',
        'Bandages, antiseptics and first aid supplies'
    ),
    (
        'Baby Care',
        'Baby food, diapers and baby care items'
    ),
    (
        'Supplements',
        'Vitamins, minerals and food supplements'
    );

-- ------------------------------------------------------------
-- Default Admin Account (admin / admin123)
-- ------------------------------------------------------------
INSERT INTO
    `admins` (`username`, `password`, `full_name`)
VALUES
    ('admin', MD5('admin123'), 'Default Admin');