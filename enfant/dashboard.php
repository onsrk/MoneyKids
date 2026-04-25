<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/read.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$user_id      = $_SESSION['user_id'];
$prenom       = $_SESSION['prenom'];
$nom          = $_SESSION['nom'];
$compte       = getCompteByUser($pdo, $user_id);
$transactions = getTransactionsByUser($pdo, $user_id);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Mon Espace</title>
    <link rel="stylesheet" href="/MoneyKids/assets/css/style.css">
    <link rel="stylesheet" href="/MoneyKids/assets/css/enfant.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-logo">MoneyKids</div>
    <div class="nav-right">
        <span class="nav-user">
            <?= htmlspecialchars($prenom . ' ' . $nom) ?>
        </span>
        <a href="/MoneyKids/authentification/logout.php" class="btn-logout">
            Deconnexion
        </a>
    </div>
</nav>

<div class="page-wrapper">

    <div class="welcome-banner">
        <div>
            <div class="welcome-title">
                Bonjour <?= htmlspecialchars($prenom) ?> !
            </div>
            <div class="welcome-sub">
                Voici un apercu complet de tes finances
            </div>
        </div>
    </div>

    <div class="solde-card">
        <div class="solde-label">Mon Solde</div>
        <div class="solde-amount">
            <?= number_format($compte['solde'], 2) ?>
            <span class="solde-currency">TND</span>
        </div>
        <div class="solde-pocket">
            Argent de poche :
            <?= number_format($compte['montant_argent_poche'], 2) ?> TND
            / <?= $compte['frequence'] ?>
        </div>
    </div>

    <div class="section-title">Mon Historique</div>

    <?php if (empty($transactions)): ?>
        <div class="card" style="text-align:center; padding:40px;">
            <div style="font-size:18px; font-weight:800; color:#1e293b;">
                Aucune transaction pour le moment
            </div>
            <div style="color:#64748B; margin-top:8px;">
                Ton historique apparaitra ici
            </div>
        </div>

    <?php else: ?>
        <div class="card" style="padding:0; overflow:hidden;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
    <?php foreach ($transactions as $tx): ?>
        <tr>
            <td><?= htmlspecialchars($tx['description']) ?></td>
            <td>
                <span class="badge <?= $tx['type'] === 'credit' ? 'badge-green' : 'badge-red' ?>">
                    <?= $tx['type'] === 'credit' ? 'Credit' : 'Debit' ?>
                </span>
            </td>
            <td class="<?= $tx['type'] === 'credit' ? 'amount-credit' : 'amount-debit' ?>">
                <?= $tx['type'] === 'credit' ? '+' : '-' ?>
                <?= number_format($tx['montant'], 2) ?> TND
            </td>
            <td>
                <?= date('d/m/Y H:i', strtotime($tx['date_transaction'])) ?>
            </td>
            <td>
                <?php if ($tx['type'] === 'debit'): ?>
                <a href="delete_depense.php?id=<?= $tx['id'] ?>"
                   class="btn-small-danger"
                   onclick="return confirm('Supprimer cette depense ? Le montant sera restitue.')">
                    Supprimer
                </a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
            </table>
        </div>
    <?php endif; ?>

<div style="margin-bottom: 20px;">
    <a href="depense.php" class="btn-primary">
        Nouvelle depense
    </a>
    <a href="objectif.php" class="btn-secondary" style="margin-left: 12px;">
        Mes objectifs
    </a>
</div>
</div>

</body>
</html>