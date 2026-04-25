<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/update.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$compte = getCompteByUser($pdo, $user_id);
$demandes = getChildRequests($pdo, $compte['id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids - Mes Demandes</title>
    <link rel="stylesheet" href="/MoneyKids/assets/css/style.css">
    <link rel="stylesheet" href="/MoneyKids/assets/css/enfant.css">
    <style>
        .status-pending { background: #FEF3C7; color: #92400E; }
        .status-approved { background: #D1FAE5; color: #065F46; }
        .status-declined { background: #FEE2E2; color: #991B1B; }
        .motif-refus { font-size: 12px; color: #DC2626; margin-top: 5px; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-logo">MoneyKids</div>
    <div class="nav-right">
        <span class="nav-user"><?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></span>
        <a href="/MoneyKids/authentification/logout.php" class="btn-logout">Deconnexion</a>
    </div>
</nav>

<div class="page-wrapper">
    <a href="dashboard.php" class="back-link">Retour au dashboard</a>

    <div class="page-title">Mes Demandes</div>
    <div class="page-sub">Suivez l etat de vos demandes de depense</div>

    <?php if (empty($demandes)): ?>
        <div class="card" style="text-align:center; padding:40px;">
            <div style="font-size:48px; margin-bottom:16px;">📋</div>
            <div style="font-size:18px; font-weight:800;">Aucune demande</div>
            <div style="color:#64748B;">Vous n avez pas encore fait de demande</div>
            <a href="depense.php" class="btn-primary" style="margin-top:20px;">Nouvelle demande</a>
        </div>
    <?php else: ?>
        <div class="card" style="padding:0; overflow:hidden;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($demandes as $d): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($d['date_soumission'])) ?></td>
                            <td><?= htmlspecialchars($d['description']) ?></td>
                            <td class="amount-debit"><?= number_format($d['montant'], 2) ?> TND</td>
                            <td>
                                <span class="badge status-<?= $d['status'] ?>">
                                    <?php
                                        if ($d['status'] == 'pending') echo 'En attente';
                                        elseif ($d['status'] == 'approved') echo 'Approuvee';
                                        else echo 'Refusee';
                                    ?>
                                </span>
                                <?php if ($d['status'] == 'declined' && $d['motif_refus']): ?>
                                    <div class="motif-refus">Motif: <?= htmlspecialchars($d['motif_refus']) ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</body>
</html>