-- Drop database if exists and create new one
DROP DATABASE IF EXISTS le7rayfi_db;

CREATE DATABASE le7rayfi_db;

USE le7rayfi_db;

-- Create users table with role
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create products table with all specifications
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category VARCHAR(100) NOT NULL,
    image_url VARCHAR(255),
    ram VARCHAR(50) NULL,
    storage VARCHAR(50) NULL,
    processor VARCHAR(50) NULL,
    camera VARCHAR(50) NULL,
    battery VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL
);

-- Create cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

-- Insert sample PC products
INSERT INTO
    products (
        name,
        description,
        price,
        stock,
        category,
        image_url,
        ram,
        storage,
        processor
    )
VALUES (
        'MacBook Pro M2',
        '14-inch Liquid Retina XDR display, Professional Grade Laptop',
        25999.00,
        10,
        'pc',
        'img/best-laptops-20240516-medium.jpg',
        '16GB',
        '512GB',
        'Apple M2'
    ),
    (
        'MSI Gaming Laptop',
        'High-performance gaming laptop with RTX 4060',
        15499.00,
        15,
        'pc',
        'img/laptop.jpg',
        '32GB',
        '1TB',
        'Intel Core i7'
    ),
    (
        'Dell XPS 13',
        'Ultra-portable laptop with InfinityEdge display',
        12999.00,
        8,
        'pc',
        'uploads/products/681c8624a25d1.jpg',
        '16GB',
        '512GB',
        'Intel Core i5'
    ),
    (
        'Lenovo ThinkPad X1',
        'Business laptop with exceptional build quality',
        18999.00,
        12,
        'pc',
        'uploads/products/681d22bdf1f3d.jpg',
        '32GB',
        '1TB',
        'Intel Core i9'
    );

-- Insert sample mobile products
INSERT INTO
    products (
        name,
        description,
        price,
        stock,
        category,
        image_url,
        storage,
        camera,
        battery
    )
VALUES (
        'iPhone 15 Pro',
        'Latest iPhone with advanced camera system',
        13999.00,
        20,
        'mobile',
        'img/phone.png',
        '256GB',
        '48MP',
        '4000mAh'
    ),
    (
        'Samsung Galaxy S24 Ultra',
        '200MP camera system with advanced AI',
        14499.00,
        15,
        'mobile',
        'uploads/products/681d22deb9b29.jpeg',
        '512GB',
        '200MP',
        '5000mAh'
    ),
    (
        'Google Pixel 8 Pro',
        'Pure Android experience with exceptional camera',
        11999.00,
        10,
        'mobile',
        'uploads/products/681c8624a25d1.jpg',
        '256GB',
        '50MP',
        '4500mAh'
    ),
    (
        'OnePlus 12',
        'Flagship killer with Hasselblad cameras',
        9999.00,
        25,
        'mobile',
        'uploads/products/681d22bdf1f3d.jpg',
        '256GB',
        '48MP',
        '5400mAh'
    );

-- Insert sample accessories
INSERT INTO
    products (
        name,
        description,
        price,
        stock,
        category,
        image_url
    )
VALUES (
        'Logitech MX Master 3',
        'Premium wireless mouse for productivity',
        999.00,
        30,
        'accessory',
        'uploads/products/681d22deb9b29.jpeg'
    ),
    (
        'Sony WH-1000XM4',
        'Premium noise-canceling headphones',
        2499.00,
        20,
        'accessory',
        'uploads/products/681c8624a25d1.jpg'
    ),
    (
        'Samsung 27" 4K Monitor',
        'Professional grade display monitor',
        3499.00,
        15,
        'accessory',
        'uploads/products/681d22bdf1f3d.jpg'
    );

-- Create default admin user (password: admin123)
INSERT INTO
    users (
        username,
        email,
        password,
        full_name,
        role
    )
VALUES (
        'admin',
        'admin@le7rayfi.com',
        '$2y$10$8jf0VpZ9K2YF.q9z5s1PC.N.JUY3E0hvw6nz6h.WGX9jbze0q1Idy',
        'Admin User',
        'admin'
    );