<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

// Get user by ID
function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Get all children of a parent
function getEnfantsByParent($pdo, $parent_id) {
    $stmt = $pdo->prepare("
        SELECT u.*, c.solde, c.montant_argent_poche, c.frequence
        FROM utilisateur u
        JOIN compte c ON c.user_id = u.id
        WHERE u.parent_id = ?
        ORDER BY u.id DESC
    ");
    $stmt->execute([$parent_id]);
    return $stmt->fetchAll();
}
?>