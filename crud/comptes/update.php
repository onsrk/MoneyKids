<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function updateSolde($pdo, $user_id, $montant, $type) {
    if ($type === 'credit') {
        $stmt = $pdo->prepare("
            UPDATE compte SET solde = solde + ? WHERE user_id = ?
        ");
    } else {
        $stmt = $pdo->prepare("
            UPDATE compte SET solde = solde - ? WHERE user_id = ?
        ");
    }
    $stmt->execute([$montant, $user_id]);
}

function updateArgentPoche($pdo, $user_id, $montant, $frequence) {
    $stmt = $pdo->prepare("
        UPDATE compte 
        SET montant_argent_poche = ?, frequence = ?
        WHERE user_id = ?
    ");
    $stmt->execute([$montant, $frequence, $user_id]);
}
?>