<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/update.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/update.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/create.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/recompenses/create.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$user_id     = $_SESSION['user_id'];
$objectif_id = $_POST['objectif_id'] ?? null;
$montant     = floatval($_POST['montant_epargne'] ?? 0);
$compte      = getCompteByUser($pdo, $user_id);
$objectif    = getObjectifById($pdo, $objectif_id);

// Basic validations
if (!$objectif || $montant <= 0) {
    header('Location: objectif.php?error=invalid');
    exit();
}

// Check solde suffisant
if ($montant > $compte['solde']) {
    header('Location: objectif.php?error=solde');
    exit();
}

// ✅ Cap montant to not exceed remaining amount needed
$restant = $objectif['montant_cible'] - $objectif['montant_actuel'];
if ($montant > $restant) {
    $montant = $restant; // only epargne what's needed
}

// Debit solde
updateSolde($pdo, $user_id, $montant, 'debit');

// Update objectif montant_actuel
updateMontantActuel($pdo, $objectif_id, $montant);

// Create transaction
createTransaction(
    $pdo,
    $compte['id'],
    $montant,
    'debit',
    'Epargne — ' . $objectif['nom']
);

// Check badges
$badge_earned = false;
$objectif_updated = getObjectifById($pdo, $objectif_id);

// Badge 1 — Super Epargnant
if ($objectif_updated['montant_actuel'] >= $objectif_updated['montant_cible']) {
    createRecompense($pdo, $user_id, 'Super Epargnant', 'Objectif atteint !');
    $badge_earned = true;
}

// Badge 2 — Planificateur
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM objectif_epargne o
    JOIN compte c ON c.id = o.compte_id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$nb_objectifs = $stmt->fetch()['total'];
if ($nb_objectifs >= 3) {
    $created = createRecompense($pdo, $user_id, 'Planificateur', 'Tu as cree 3 objectifs !');
    if ($created) $badge_earned = true;
}

// Badge 3 — Economiste
$stmt = $pdo->prepare("
    SELECT SUM(montant_actuel) as total
    FROM objectif_epargne o
    JOIN compte c ON c.id = o.compte_id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$total_epargne = $stmt->fetch()['total'] ?? 0;
if ($total_epargne >= 50) {
    $created = createRecompense($pdo, $user_id, 'Economiste', 'Tu as epargne plus de 50 TND !');
    if ($created) $badge_earned = true;
}

if ($badge_earned) {
    header('Location: recompenses.php?badge=1');
} else {
    header('Location: objectif.php?success=1');
}
exit();
?>