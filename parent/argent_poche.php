<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/users/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/update.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/create.php';

// Check if logged in and is parent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    header('Location: ../authentification/login.php');
    exit();
}

// Get enfant id from URL
$enfant_id = $_GET['id'] ?? null;
if (!$enfant_id) {
    header('Location: dashboard.php');
    exit();
}

// Get enfant info
$enfant = getUserById($pdo, $enfant_id);
$compte = getCompteByUser($pdo, $enfant_id);

if (!$enfant || $enfant['parent_id'] != $_SESSION['user_id']) {
    header('Location: dashboard.php');
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant   = trim($_POST['montant'] ?? '');
    $frequence = trim($_POST['frequence'] ?? '');

    if (empty($montant) || !is_numeric($montant) || $montant <= 0) {
        $error = 'Veuillez entrer un montant valide.';
    } elseif (empty($frequence)) {
        $error = 'Veuillez choisir une fréquence.';
    } else {
        // UPDATE compte
        updateArgentPoche($pdo, $enfant_id, $montant, $frequence);

        // UPDATE solde
        updateSolde($pdo, $enfant_id, $montant, 'credit');

        // CREATE transaction
        createTransaction(
            $pdo,
            $compte['id'],
            $montant,
            'credit',
            'Argent de poche — ' . $frequence
        );

        $success = 'Argent de poche attribué avec succès !';

        // Refresh compte data
        $compte = getCompteByUser($pdo, $enfant_id);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Argent de poche</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/parent.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-logo">MoneyKids</div>
    <div class="nav-right">
        <span class="nav-user">
             <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?>
        </span>
        <a href="../authentification/logout.php" class="btn-logout">Déconnexion</a>
    </div>
</nav>

<!-- CONTENT -->
<div class="page-wrapper">
    <a href="dashboard.php" class="back-link">← Retour au dashboard</a>

    <div class="page-title">
         Argent de poche — <?= htmlspecialchars($enfant['prenom']) ?>
    </div>
    <div class="page-sub">
        Gérez l'argent de poche de <?= htmlspecialchars($enfant['prenom']) ?>
    </div>

    <!-- CURRENT BALANCE CARD -->
    <div class="balance-card">
        <div class="balance-left">
            <div class="balance-avatar">
                <?= strtoupper(substr($enfant['prenom'], 0, 1)) ?>
            </div>
            <div>
                <div class="balance-name">
                    <?= htmlspecialchars($enfant['prenom'] . ' ' . $enfant['nom']) ?>
                </div>
                <div class="balance-age">
                    <?= $enfant['age'] ? $enfant['age'] . ' ans' : '' ?>
                </div>
            </div>
        </div>
        <div class="balance-right">
            <div class="balance-label">Solde actuel</div>
            <div class="balance-amount">
                <?= number_format($compte['solde'], 2) ?> TND
            </div>
            <div class="balance-pocket">
                Argent de poche : 
                <?= number_format($compte['montant_argent_poche'], 2) ?> TND
                / <?= $compte['frequence'] ?>
            </div>
        </div>
    </div>

    <!-- FORM -->
    <div class="form-card" style="margin-top: 24px;">
        <h3 style="font-size:18px; font-weight:800; 
                   color:#1e293b; margin-bottom:20px;">
            Attribuer de l'argent de poche
        </h3>

        <?php if ($error): ?>
            <div class="alert-error"> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success"> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Montant (TND) *</label>
                <input class="form-input" type="number"
                       name="montant" placeholder="20.00"
                       min="0.01" step="0.01"
                       value="<?= htmlspecialchars($_POST['montant'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">Fréquence *</label>
                <select class="form-input" name="frequence" required>
                    <option value="">-- Choisir --</option>
                    <option value="hebdo" 
                        <?= ($_POST['frequence'] ?? '') === 'hebdo' ? 'selected' : '' ?>>
                        Hebdomadaire
                    </option>
                    <option value="mensuel"
                        <?= ($_POST['frequence'] ?? '') === 'mensuel' ? 'selected' : '' ?>>
                        Mensuel
                    </option>
                </select>
            </div>

            <div class="form-actions">
                <a href="dashboard.php" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">
                    Attribuer →
                </button>
            </div>
        </form>
    </div>
</div>

<div style="margin-top: 16px; text-align: right;">
    <a href="delete_argent_poche.php?id=<?= $enfant_id ?>"
       class="btn-danger"
       onclick="return confirm('Reinitialiser l argent de poche de <?= htmlspecialchars($enfant['prenom']) ?> a 0 TND ?')">
        Reinitialiser argent de poche
    </a>
</div>

</body>
</html>