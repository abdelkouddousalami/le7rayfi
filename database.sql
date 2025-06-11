DROP DATABASE IF EXISTS le7rayfi_db;

CREATE DATABASE le7rayfi_db;

 USE le7rayfi_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'user') DEFAULT 'user',
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_role (role),
    INDEX idx_user_status (status)
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category_id INT,
    image_url VARCHAR(255),
    ram VARCHAR(50),
    storage VARCHAR(50),
    processor VARCHAR(50),
    camera VARCHAR(50),
    battery VARCHAR(50),
    discount INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL
);

-- Wishlist table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_wish (user_id, product_id),
    INDEX idx_wishlist_user (user_id)
);

-- Product ratings table
CREATE TABLE IF NOT EXISTS product_ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_rating (user_id, product_id),
    INDEX idx_rating_product (product_id)
);

-- Shopping cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@hagroup.com', '$2y$10$8FX5hC2xTw3AbCEHaYtG4.YwD3Q3AIKp1QWqEwk8r0qGRFYNtMU2e', 'Admin User', 'admin');

-- Insert categories
INSERT INTO categories (name, slug, icon) VALUES
('Ordinateurs Portables', 'laptops', 'fas fa-laptop'),
('Ordinateurs Fixes', 'desktops', 'fas fa-desktop'),
('Smartphones', 'smartphones', 'fas fa-mobile-alt'),
('Tablettes', 'tablets', 'fas fa-tablet-alt'),
('Audio & Casques', 'audio', 'fas fa-headphones'),
('Claviers & Souris', 'keyboards', 'fas fa-keyboard'),
('Imprimantes', 'printers', 'fas fa-print'),
('Composants PC', 'components', 'fas fa-microchip'),
('Gaming', 'gaming', 'fas fa-gamepad'),
('Sécurité', 'security', 'fas fa-shield-alt');

-- Insert sample products for laptops
INSERT INTO products (name, description, price, stock, category_id, image_url, ram, storage, processor, discount) VALUES
('MacBook Pro M2', 'MacBook Pro 14" avec la puce M2 Pro, parfait pour les professionnels créatifs', 25999.00, 10, 
(SELECT id FROM categories WHERE slug = 'laptops'), 'uploads/products/681c8624a25d1.jpg', '16GB', '512GB', 'Apple M2 Pro', 15),

('ASUS ROG Strix G15', 'PC Portable Gaming avec RTX 4070, parfait pour les jeux AAA', 19999.00, 8, 
(SELECT id FROM categories WHERE slug = 'laptops'), 'uploads/products/681d22bdf1f3d.jpg', '32GB', '1TB SSD', 'AMD Ryzen 9 7945HX', 10),

('Dell XPS 13', 'Ultra-portable professionnel avec écran InfinityEdge', 12999.00, 15, 
(SELECT id FROM categories WHERE slug = 'laptops'), 'uploads/products/681d22deb9b29.jpeg', '16GB', '512GB', 'Intel Core i7', 0);

-- Insert sample smartphones
INSERT INTO products (name, description, price, stock, category_id, image_url, storage, camera, battery) VALUES
('iPhone 15 Pro', 'Latest iPhone with advanced camera system', 13999.00, 20, 
(SELECT id FROM categories WHERE slug = 'smartphones'), 'uploads/products/68326b354d63c.png', '256GB', '48MP', '4000mAh'),

('Samsung Galaxy S24 Ultra', '200MP camera system with advanced AI', 14499.00, 15, 
(SELECT id FROM categories WHERE slug = 'smartphones'), 'uploads/products/68326b53491df.png', '512GB', '200MP', '5000mAh'),

('Google Pixel 8 Pro', 'Pure Android experience with exceptional camera', 11999.00, 10, 
(SELECT id FROM categories WHERE slug = 'smartphones'), 'uploads/products/68326d6e0ef23.png', '256GB', '50MP', '4500mAh');

-- Insert gaming accessories
INSERT INTO products (name, description, price, stock, category_id, image_url) VALUES
('Logitech G Pro X', 'Casque gaming professional avec Blue VO!CE', 999.00, 30,
(SELECT id FROM categories WHERE slug = 'gaming'), 'uploads/products/68326eccbc84f.png'),

('Razer BlackWidow V3', 'Clavier mécanique RGB pour gaming', 799.00, 25,
(SELECT id FROM categories WHERE slug = 'keyboards'), 'uploads/products/683270927d9e2.png');
 
VALUES 
    (
        'MacBook Pro M2',
        'MacBook Pro 14" avec la puce M2 Pro, parfait pour les professionnels créatifs',
        25999.00,
        10,
        'laptop',
        (SELECT id FROM categories WHERE slug = 'laptops'),
        'img/best-laptops-20240516-medium.jpg',
        '16GB',
        '512GB',
        'Apple M2 Pro',
        15
    ),
    (
        'ASUS ROG Strix G15',
        'PC Portable Gaming avec RTX 4070, parfait pour les jeux AAA',
        19999.00,
        8,
        'laptop',
        (SELECT id FROM categories WHERE slug = 'laptops'),
        'img/laptop.jpg',
        '32GB',
        '1TB SSD',
        'AMD Ryzen 9 7945HX',
        10
    ),    (
        'MSI Gaming Laptop',
        'High-performance gaming laptop with RTX 4060',
        15499.00,
        15,
        (SELECT id FROM categories WHERE slug = 'laptops'),
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
        (SELECT id FROM categories WHERE slug = 'laptops'),
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
        (SELECT id FROM categories WHERE slug = 'laptops'),
        'uploads/products/681d22bdf1f3d.jpg',
        '32GB',
        '1TB',
        'Intel Core i9'
    );

INSERT INTO
    products (
        name,
        description,
        price,
        stock,
        category_id,
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
        (SELECT id FROM categories WHERE slug = 'smartphones'),
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
        (SELECT id FROM categories WHERE slug = 'smartphones'),
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
        (SELECT id FROM categories WHERE slug = 'smartphones'),
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
        (SELECT id FROM categories WHERE slug = 'smartphones'),
        'uploads/products/681d22bdf1f3d.jpg',
        '256GB',
        '48MP',
        '5400mAh'
    );

