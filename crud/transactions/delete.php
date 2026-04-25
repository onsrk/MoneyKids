<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function deleteTransaction($pdo, $transaction_id, $user_id) {
    // Get transaction amount and type first
    $stmt = $pdo->prepare("
        SELECT t.montant, t.type 
        FROM transaction t
        JOIN compte c ON c.id = t.compte_id
        WHERE t.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$transaction_id, $user_id]);
    $transaction = $stmt->fetch();

    if (!$transaction) return false;

    // Reverse the effect on solde
    if ($transaction['type'] === 'debit') {
        // Give money back
        $stmt = $pdo->prepare("
            UPDATE compte SET solde = solde + ?
            WHERE user_id = ?
        ");
    } else {
        // Remove credit
        $stmt = $pdo->prepare("
            UPDATE compte SET solde = solde - ?
            WHERE user_id = ?
        ");
    }
    $stmt->execute([$transaction['montant'], $user_id]);

    // Delete transaction
    $stmt = $pdo->prepare("DELETE FROM transaction WHERE id = ?");
    $stmt->execute([$transaction_id]);

    return true;
}
?>