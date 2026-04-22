<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function getObjectifsByCompte($pdo, $compte_id) {
    $stmt = $pdo->prepare("
        SELECT *,
        ROUND((montant_actuel / montant_cible) * 100, 1) as progression
        FROM objectif_epargne
        WHERE compte_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$compte_id]);
    return $stmt->fetchAll();
}

function getObjectifById($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT *,
        ROUND((montant_actuel / montant_cible) * 100, 1) as progression
        FROM objectif_epargne
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
?>