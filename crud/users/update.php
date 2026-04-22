<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function updatePassword($pdo, $user_id, $new_password) {
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        UPDATE utilisateur SET password = ? WHERE id = ?
    ");
    $stmt->execute([$hashed, $user_id]);
}

function updateEnfant($pdo, $enfant_id, $nom, $prenom, $age) {
    $stmt = $pdo->prepare("
        UPDATE utilisateur 
        SET nom = ?, prenom = ?, age = ?
        WHERE id = ?
    ");
    $stmt->execute([$nom, $prenom, $age, $enfant_id]);
}

function resetArgentPoche($pdo, $user_id) {
    $stmt = $pdo->prepare("
        UPDATE compte 
        SET montant_argent_poche = 0.00, frequence = 'mensuel'
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
}
?>