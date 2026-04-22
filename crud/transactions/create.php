<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function createTransaction($pdo, $compte_id, $montant, $type, $description) {
    $stmt = $pdo->prepare("
        INSERT INTO transaction (compte_id, montant, type, description)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$compte_id, $montant, $type, $description]);
    return $pdo->lastInsertId();
}
?>