<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function updateMontantActuel($pdo, $objectif_id, $montant) {
    $stmt = $pdo->prepare("
        UPDATE objectif_epargne
        SET montant_actuel = montant_actuel + ?
        WHERE id = ?
    ");
    $stmt->execute([$montant, $objectif_id]);
}
?>