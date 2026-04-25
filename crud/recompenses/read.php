<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function getRecompensesByUser($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM recompense
        WHERE user_id = ?
        ORDER BY date_obtention DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}
?>