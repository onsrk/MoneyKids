<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function deleteObjectif($pdo, $objectif_id, $user_id) {
    $stmt = $pdo->prepare("
        DELETE o
        FROM objectif_epargne o
        JOIN compte c ON c.id = o.compte_id
        WHERE o.id = ? AND c.user_id = ?
    ");

    return $stmt->execute([$objectif_id, $user_id]);
}
?>