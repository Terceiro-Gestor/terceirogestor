<?php

require_once __DIR__ . '/../../config/Database.php';

class User
{
    public static function findByEmail($email)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}