<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/users/read.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$enfant_id = $_GET['id'] ?? null;
if (!$enfant_id) {
    header('Location: dashboard.php');
    exit();
}

$enfant = getUserById($pdo, $enfant_id);

if (!$enfant || $enfant['parent_id'] != $_SESSION['user_id']) {
    header('Location: dashboard.php');
    exit();
}

// Get filter
$categorie_filter = $_GET['categorie'] ?? '';

// Get transactions
$query = "
    SELECT t.*
    FROM transaction t
    JOIN compte c ON c.id = t.compte_id
    WHERE c.user_id = ?
";

if (!empty($categorie_filter)) {
    $query .= " AND t.description LIKE ?";
    $query .= " ORDER BY t.date_transaction DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$enfant_id, '%' . $categorie_filter . '%']);
} else {
    $query .= " ORDER BY t.date_transaction DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$enfant_id]);
}

$transactions = $stmt->fetchAll();

// Get total depenses and credits
$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN type = 'debit' THEN montant ELSE 0 END) as total_depenses,
        SUM(CASE WHEN type = 'credit' THEN montant ELSE 0 END) as total_credits
    FROM transaction t
    JOIN compte c ON c.id = t.compte_id
    WHERE c.user_id = ?
");
$stmt->execute([$enfant_id]);
$totaux = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Historique</title>
    <link rel="stylesheet" href="/MoneyKids/assets/css/style.css">
    <link rel="stylesheet" href="/MoneyKids/assets/css/parent.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-logo">MoneyKids</div>
    <div class="nav-right">
        <span class="nav-user">
            <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?>
        </span>
        <a href="/MoneyKids/authentification/logout.php" class="btn-logout">
            Deconnexion
        </a>
    </div>
</nav>

<div class="page-wrapper">
    <a href="dashboard.php" class="back-link">← Retour au dashboard</a>

    <div class="page-title">
        Historique — <?= htmlspecialchars($enfant['prenom'] . ' ' . $enfant['nom']) ?>
    </div>
    <div class="page-sub">
        Toutes les transactions de <?= htmlspecialchars($enfant['prenom']) ?>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card stat-red">
            <div class="stat-label">Total Depenses</div>
            <div class="stat-value">
                <?= number_format($totaux['total_depenses'] ?? 0, 2) ?> TND
            </div>
        </div>
        <div class="stat-card stat-green">
            <div class="stat-label">Total Credits</div>
            <div class="stat-value">
                <?= number_format($totaux['total_credits'] ?? 0, 2) ?> TND
            </div>
        </div>
        <div class="stat-card stat-blue">
            <div class="stat-label">Nb Transactions</div>
            <div class="stat-value"><?= count($transactions) ?></div>
        </div>
    </div>

    <!-- FILTER -->
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="id" value="<?= $enfant_id ?>">
        <select class="form-input" name="categorie" 
                style="max-width:200px;" onchange="this.form.submit()">
            <option value="">Toutes categories</option>
            <option value="Nourriture" <?= $categorie_filter === 'Nourriture' ? 'selected' : '' ?>>Nourriture</option>
            <option value="Loisirs" <?= $categorie_filter === 'Loisirs' ? 'selected' : '' ?>>Loisirs</option>
            <option value="Education" <?= $categorie_filter === 'Education' ? 'selected' : '' ?>>Education</option>
            <option value="Vetements" <?= $categorie_filter === 'Vetements' ? 'selected' : '' ?>>Vetements</option>
            <option value="Epargne" <?= $categorie_filter === 'Epargne' ? 'selected' : '' ?>>Epargne</option>
            <option value="Argent de poche" <?= $categorie_filter === 'Argent de poche' ? 'selected' : '' ?>>Argent de poche</option>
        </select>
    </form>

    <!-- TRANSACTIONS TABLE -->
    <?php if (empty($transactions)): ?>
        <div class="card" style="text-align:center; padding:40px;">
            <div style="font-size:18px; font-weight:800; color:#1e293b;">
                Aucune transaction trouvee
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
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</body>
</html>