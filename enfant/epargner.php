<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/update.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/update.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/create.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$user_id       = $_SESSION['user_id'];
$objectif_id   = $_POST['objectif_id'] ?? null;
$montant       = $_POST['montant_epargne'] ?? 0;
$compte        = getCompteByUser($pdo, $user_id);
$objectif      = getObjectifById($pdo, $objectif_id);

if (!$objectif || !is_numeric($montant) || $montant <= 0) {
    header('Location: objectif.php');
    exit();
}

if ($montant > $compte['solde']) {
    header('Location: objectif.php?error=solde_insuffisant');
    exit();
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
// Update objectif montant_actuel
updateMontantActuel($pdo, $objectif_id, $montant);
// Check if objectif is now reached → give badge
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/recompenses/create.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/read.php';

$objectif_updated = getObjectifById($pdo, $objectif_id);
if ($objectif_updated['montant_actuel'] >= $objectif_updated['montant_cible']) {
    createRecompense(
        $pdo,
        $user_id,
        'Super Epargnant',
        'Objectif « ' . $objectif_updated['nom'] . ' » atteint !'
    );
    header('Location: recompenses.php?badge=1');
    exit();
}

header('Location: objectif.php?success=epargne_ok');
exit();
?>