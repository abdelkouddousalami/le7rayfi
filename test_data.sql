USE le7rayfi;

INSERT INTO products (name, description, price, stock, category_id, image_url, ram, storage, processor) VALUES
('MacBook Pro M2', 'Ordinateur portable haute performance avec puce M2', 25999.00, 10, 1, 'img/macbook-pro.jpg', '16GB', '512GB SSD', 'Apple M2'),
('Dell XPS 13', 'Ultrabook compact et performant', 18999.00, 15, 1, 'img/dell-xps13.jpg', '8GB', '256GB SSD', 'Intel Core i7'),
('HP Pavilion Gaming', 'Laptop gaming abordable', 12999.00, 8, 1, 'img/hp-pavilion.jpg', '16GB', '1TB HDD + 256GB SSD', 'AMD Ryzen 5'),
('Lenovo ThinkPad', 'Ordinateur professionnel robuste', 22999.00, 5, 1, 'img/thinkpad.jpg', '32GB', '1TB SSD', 'Intel Core i7'),
('ASUS ROG Strix', 'Laptop gaming haut de gamme', 35999.00, 3, 1, 'img/asus-rog.jpg', '32GB', '2TB SSD', 'Intel Core i9');

INSERT INTO products (name, description, price, stock, category_id, image_url, storage, camera, battery) VALUES
('iPhone 15 Pro', 'Smartphone Apple dernière génération', 13999.00, 20, 3, 'img/iphone15-pro.jpg', '512GB', '48MP', '4000mAh'),
('Samsung Galaxy S24', 'Flagship Android de Samsung', 11999.00, 25, 3, 'img/galaxy-s24.jpg', '256GB', '50MP', '4500mAh'),
('Google Pixel 8', 'Smartphone Google avec IA avancée', 9999.00, 15, 3, 'img/pixel8.jpg', '128GB', '50MP', '4000mAh'),
('OnePlus 12', 'Smartphone performant et abordable', 8999.00, 12, 3, 'img/oneplus12.jpg', '256GB', '64MP', '5000mAh'),
('Xiaomi Mi 13', 'Smartphone chinois haut de gamme', 7999.00, 18, 3, 'img/mi13.jpg', '128GB', '50MP', '4500mAh');

INSERT INTO products (name, description, price, stock, category_id, image_url, storage, camera, battery) VALUES
('iPad Pro 12.9', 'Tablette professionnelle Apple', 15999.00, 8, 4, 'img/ipad-pro.jpg', '256GB', '12MP', '8000mAh'),
('Samsung Galaxy Tab S9', 'Tablette Android premium', 12999.00, 10, 4, 'img/galaxy-tab.jpg', '128GB', '13MP', '7500mAh');

SELECT COUNT(*) as total_products FROM products;
SELECT c.name as category, COUNT(p.id) as nb_products 
FROM categories c 
LEFT JOIN products p ON c.id = p.category_id 
GROUP BY c.id, c.name;