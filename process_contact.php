<?php
session_start();
require_once 'config/db.php';
header('Content-Type: application/json');

$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

if (!$name || !$email || !$subject || !$message) {
    echo json_encode([
        'success' => false,
        'message' => 'Tous les champs sont requis'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email invalide'
    ]);
    exit;
}

try {
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$name, $email, $subject, $message])) {
        echo json_encode([            'success' => true,
            'message' => 'Message envoyé avec succès!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Une erreur est survenue lors de l\'envoi du message'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors de l\'envoi du message'
    ]);
}