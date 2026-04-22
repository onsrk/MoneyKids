<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function createObjectif($pdo, $compte_id, $nom, $montant_cible, $date_limite = null) {
    $stmt = $pdo->prepare("
        INSERT INTO objectif_epargne 
        (compte_id, nom, montant_cible, montant_actuel, date_limite)
        VALUES (?, ?, ?, 0, ?)
    ");
    $stmt->execute([$compte_id, $nom, $montant_cible, $date_limite]);
    return $pdo->lastInsertId();
}
?>