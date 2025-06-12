<?php
require_once 'db.php';

try {
    $conn = getConnection();
    
    $createMessagesTable = "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('unread', 'read') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($createMessagesTable);
    echo "Contact messages table created successfully!\n";
    
} catch(PDOException $e) {
    die("Error creating messages table: " . $e->getMessage());
}
?>