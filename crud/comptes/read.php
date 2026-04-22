<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function getCompteByUser($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM compte WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}
?>