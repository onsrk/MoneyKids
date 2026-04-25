<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/update.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$user_id      = $_SESSION['user_id'];
$prenom       = $_SESSION['prenom'];
$nom          = $_SESSION['nom'];
$compte       = getCompteByUser($pdo, $user_id);
$transactions = getTransactionsByUser($pdo, $user_id);
$objectifs    = getObjectifsByCompte($pdo, $compte['id']);
$pending_count = getPendingRequestsCountByChild($pdo, $compte['id']);
$deleted_tx = $_GET['deleted_tx'] ?? '';


// Total depenses ce mois
$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN type = 'debit' AND description NOT LIKE 'Epargne%' 
            THEN montant ELSE 0 END) as total_depenses,
        SUM(CASE WHEN type = 'debit' AND description LIKE 'Epargne%' 
            THEN montant ELSE 0 END) as total_epargne
    FROM transaction t
    JOIN compte c ON c.id = t.compte_id
    WHERE c.user_id = ?
    AND MONTH(t.date_transaction) = MONTH(NOW())
    AND YEAR(t.date_transaction) = YEAR(NOW())
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();
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

    <!-- WELCOME -->
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

<?php if ($deleted_tx): ?>
    <div class="alert-success" id="deleted-tx-msg">
        Depense supprimee avec succes !
    </div>
<?php endif; ?>
    <!-- SOLDE CARD -->
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

    <!-- STATS CE MOIS -->
    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="stat-card stat-red">
            <div class="stat-label">Depenses ce mois</div>
            <div class="stat-value">
                <?= number_format($stats['total_depenses'] ?? 0, 2) ?> TND
            </div>
        </div>
        <div class="stat-card stat-green">
            <div class="stat-label">Epargne ce mois</div>
            <div class="stat-value">
                <?= number_format($stats['total_epargne'] ?? 0, 2) ?> TND
            </div>
        </div>
        <div class="stat-card stat-blue">
            <div class="stat-label">Nb Transactions</div>
            <div class="stat-value"><?= count($transactions) ?></div>
        </div>
    </div>
<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 12px 16px; border-radius: 12px; margin-bottom: 20px;">
        Vous avez <strong><?= $pending_count ?></strong> demande(s) en attente d'approbation.
        <a href="demandes.php" style="color: #F59E0B; font-weight: 700;">Voir mes demandes</a>
    </div>
    <!-- ACTION BUTTONS -->
    <div style="display:flex; gap:12px; margin-bottom:24px;">
        <a href="depense.php" class="btn-primary">
            Nouvelle depense
        </a>
        <a href="objectif.php" class="btn-secondary">
            Mes objectifs
        </a>
        <a href="recompenses.php" class="btn-secondary">
        Mes recompenses
    </a>
    </div>

    <!-- OBJECTIFS PROGRESSION -->
    <?php if (!empty($objectifs)): ?>
        <div class="section-title">Mes Objectifs</div>
        <div style="margin-bottom:24px;">
            <?php foreach ($objectifs as $obj): ?>
                <?php
                    $progression = min($obj['progression'], 100);
                    $color = $progression >= 100 ? '#10B981' :
                            ($progression >= 50 ? '#F59E0B' : '#2563EB');
                ?>
                <div class="card" style="margin-bottom:12px; padding:16px;">
                    <div style="display:flex; justify-content:space-between;
                                align-items:center; margin-bottom:10px;">
                        <div style="font-weight:800; color:#1e293b;">
                            <?= htmlspecialchars($obj['nom']) ?>
                        </div>
                        <div style="font-size:13px; color:#64748B;">
                            <?= number_format($obj['montant_actuel'], 2) ?> /
                            <?= number_format($obj['montant_cible'], 2) ?> TND
                        </div>
                    </div>
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill"
                                 style="width:<?= $progression ?>%;
                                        background:<?= $color ?>;">
                            </div>
                        </div>
                        <div class="progress-percent" style="color:<?= $color ?>">
                            <?= $progression ?>%
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- HISTORIQUE -->
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
                        <th>Actions</th>
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

</div>

<script>
   
const deletedTx = document.getElementById('deleted-tx-msg');
if (deletedTx) {
    setTimeout(function() {
        deletedTx.style.transition = 'opacity 0.5s ease';
        deletedTx.style.opacity = '0';
        setTimeout(function() {
            deletedTx.style.display = 'none';
        }, 500);
    }, 3000);
}
document.addEventListener('wheel', function(e) {
    if (document.activeElement.type === 'number') {
        document.activeElement.blur();
    }
});
</script>

</body>
</html>