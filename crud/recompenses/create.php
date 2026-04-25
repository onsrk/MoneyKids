<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function createRecompense($pdo, $user_id, $badge, $description) {
    // Check if badge already exists for this user
    $stmt = $pdo->prepare("
        SELECT id FROM recompense 
        WHERE user_id = ? AND badge = ?
    ");
    $stmt->execute([$user_id, $badge]);
    if ($stmt->fetch()) return false; // already has this badge

    $stmt = $pdo->prepare("
        INSERT INTO recompense (user_id, badge, description)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user_id, $badge, $description]);
    return true;
}
?>