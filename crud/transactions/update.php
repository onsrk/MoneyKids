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

// NEW FUNCTIONS FOR US11
function approveTransaction($pdo, $transaction_id, $parent_id) {
    // Get transaction details
    $stmt = $pdo->prepare("
        SELECT t.*, c.user_id as enfant_id, c.solde
        FROM transaction t
        JOIN compte c ON c.id = t.compte_id
        WHERE t.id = ? AND t.parent_id = ? AND t.status = 'pending'
    ");
    $stmt->execute([$transaction_id, $parent_id]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        return ['success' => false, 'message' => 'Transaction non trouvee'];
    }
    
    if ($transaction['solde'] < $transaction['montant']) {
        return ['success' => false, 'message' => 'Solde insuffisant'];
    }
    
    // Update transaction status
    $stmt = $pdo->prepare("
        UPDATE transaction 
        SET status = 'approved', date_reponse = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$transaction_id]);
    
    // Debit child's balance
    $stmt = $pdo->prepare("
        UPDATE compte SET solde = solde - ? WHERE user_id = ?
    ");
    $stmt->execute([$transaction['montant'], $transaction['enfant_id']]);
    
    return ['success' => true, 'message' => 'Depense approuvee'];
}

function declineTransaction($pdo, $transaction_id, $parent_id, $motif) {
    $stmt = $pdo->prepare("
        UPDATE transaction 
        SET status = 'declined', date_reponse = NOW(), motif_refus = ? 
        WHERE id = ? AND parent_id = ? AND status = 'pending'
    ");
    $stmt->execute([$motif ?: 'Non specifie', $transaction_id, $parent_id]);
    
    return ['success' => true, 'message' => 'Depense refusee'];
}

function getPendingTransactionsByParent($pdo, $parent_id) {
    $stmt = $pdo->prepare("
        SELECT t.*, u.prenom, u.nom, u.id as enfant_id, c.solde
        FROM transaction t
        JOIN compte c ON c.id = t.compte_id
        JOIN utilisateur u ON u.id = c.user_id
        WHERE t.parent_id = ? AND t.status = 'pending' AND t.type = 'debit'
        ORDER BY t.date_soumission ASC
    ");
    $stmt->execute([$parent_id]);
    return $stmt->fetchAll();
}

function getPendingRequestsCountByChild($pdo, $compte_id) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as pending_count 
        FROM transaction t
        WHERE t.compte_id = ? AND t.status = 'pending' AND t.type = 'debit'
    ");
    $stmt->execute([$compte_id]);
    $result = $stmt->fetch();
    return $result['pending_count'];
}

function getChildRequests($pdo, $compte_id) {
    $stmt = $pdo->prepare("
        SELECT t.*, 
               CASE 
                   WHEN t.status = 'pending' THEN 'En attente'
                   WHEN t.status = 'approved' THEN 'Approuvee'
                   WHEN t.status = 'declined' THEN 'Refusee'
               END as status_label
        FROM transaction t
        WHERE t.compte_id = ? AND t.type = 'debit'
        ORDER BY t.date_soumission DESC
    ");
    $stmt->execute([$compte_id]);
    return $stmt->fetchAll();
}
?>