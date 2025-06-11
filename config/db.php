<?php
function getConnection() {
    static $conn = null;
    if ($conn === null) {
        try {            $conn = new PDO("mysql:host=localhost;dbname=le7rayfi_db", "root", "");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $conn->exec("SET NAMES utf8mb4");
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    return $conn;
}