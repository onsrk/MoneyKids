<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function deleteEnfant($pdo, $enfant_id) {
    // Delete transactions first
    $stmt = $pdo->prepare("
        DELETE t FROM transaction t
        JOIN compte c ON c.id = t.compte_id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$enfant_id]);

    // Delete objectifs
    $stmt = $pdo->prepare("
        DELETE o FROM objectif_epargne o
        JOIN compte c ON c.id = o.compte_id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$enfant_id]);

    // Delete compte
    $stmt = $pdo->prepare("DELETE FROM compte WHERE user_id = ?");
    $stmt->execute([$enfant_id]);

    // Delete utilisateur
    $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id = ?");
    $stmt->execute([$enfant_id]);
}
?>