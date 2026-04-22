<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function getTransactionsByUser($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT t.*
        FROM transaction t
        JOIN compte c ON c.id = t.compte_id
        WHERE c.user_id = ?
        ORDER BY t.date_transaction DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}
?>