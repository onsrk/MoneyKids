<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/update.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/create.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$compte  = getCompteByUser($pdo, $user_id);
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $montant     = trim($_POST['montant'] ?? '');
    $categorie   = trim($_POST['categorie'] ?? '');

    if (empty($description) || empty($montant) || empty($categorie)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!is_numeric($montant) || $montant <= 0) {
        $error = 'Montant invalide.';
    } elseif ($montant > $compte['solde']) {
        $error = 'Solde insuffisant. Votre solde est de ' . 
                  number_format($compte['solde'], 2) . ' TND.';
    } else {
        // UPDATE solde
        updateSolde($pdo, $user_id, $montant, 'debit');

        // CREATE transaction
        createTransaction(
            $pdo,
            $compte['id'],
            $montant,
            'debit',
            $categorie . ' — ' . $description
        );

        $success = 'Depense enregistree avec succes !';

        // Refresh compte
        $compte = getCompteByUser($pdo, $user_id);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Nouvelle Depense</title>
    <link rel="stylesheet" href="/MoneyKids/assets/css/style.css">
    <link rel="stylesheet" href="/MoneyKids/assets/css/enfant.css">
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

    <div class="page-title">Nouvelle Depense</div>
    <div class="page-sub">Enregistrez une de vos depenses</div>

    <!-- CURRENT BALANCE -->
    <div class="solde-mini">
        Solde actuel :
        <span><?= number_format($compte['solde'], 2) ?> TND</span>
    </div>

    <div class="form-card">

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success">
                <?= htmlspecialchars($success) ?>
                <br><br>
                <a href="dashboard.php" class="btn-primary">
                    Retour au dashboard
                </a>
                <a href="depense.php" class="btn-secondary">
                    Nouvelle depense
                </a>
            </div>
        <?php else: ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Description *</label>
                <input class="form-input" type="text"
                       name="description"
                       placeholder="Ex: Sandwich, Stylos..."
                       value="<?= htmlspecialchars($_POST['description'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">Categorie *</label>
                <select class="form-input" name="categorie" required>
                    <option value="">-- Choisir une categorie --</option>
                    <option value="Nourriture" 
                        <?= ($_POST['categorie'] ?? '') === 'Nourriture' ? 'selected' : '' ?>>
                        Nourriture
                    </option>
                    <option value="Loisirs"
                        <?= ($_POST['categorie'] ?? '') === 'Loisirs' ? 'selected' : '' ?>>
                        Loisirs
                    </option>
                    <option value="Education"
                        <?= ($_POST['categorie'] ?? '') === 'Education' ? 'selected' : '' ?>>
                        Education
                    </option>
                    <option value="Vetements"
                        <?= ($_POST['categorie'] ?? '') === 'Vetements' ? 'selected' : '' ?>>
                        Vetements
                    </option>
                    <option value="Autre"
                        <?= ($_POST['categorie'] ?? '') === 'Autre' ? 'selected' : '' ?>>
                        Autre
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Montant (TND) *</label>
                <input class="form-input" type="number"
                       name="montant"
                       placeholder="5.00"
                       min="0.01" step="0.01"
                       value="<?= htmlspecialchars($_POST['montant'] ?? '') ?>"
                       required>
            </div>

            <div class="form-actions">
                <a href="dashboard.php" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">
                    Enregistrer
                </button>
            </div>
        </form>

        <?php endif; ?>
    </div>
</div>

</body>
</html>