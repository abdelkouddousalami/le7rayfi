<?php

$db_host = "mysql";
$db_name = "le7rayfi";
$db_user = "root";
$db_pass = "root_password";

function getConnection() {
    global $db_host, $db_name, $db_user, $db_pass;
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    return $conn;
}