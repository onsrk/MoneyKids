<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$compte  = getCompteByUser($pdo, $user_id);
$error   = '';
$success = '';

// Get parent_id from enfant's account
$stmt = $pdo->prepare("SELECT parent_id FROM utilisateur WHERE id = ?");
$stmt->execute([$user_id]);
$parent = $stmt->fetch();
$parent_id = $parent['parent_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $montant     = trim($_POST['montant'] ?? '');
    $categorie   = trim($_POST['categorie'] ?? '');

    if (empty($description) || empty($montant) || empty($categorie)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!is_numeric($montant) || $montant <= 0) {
        $error = 'Montant invalide.';
    } else {
        // Insert transaction with status 'pending'
        $stmt = $pdo->prepare("
            INSERT INTO transaction (compte_id, montant, type, description, status, parent_id, date_soumission)
            VALUES (?, ?, 'debit', ?, 'pending', ?, NOW())
        ");
        $stmt->execute([$compte['id'], $montant, $categorie . ' - ' . $description, $parent_id]);
        
        $success = 'Demande envoyee. En attente de l approbation de vos parents.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids - Nouvelle Depense</title>
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
        <a href="/MoneyKids/authentification/logout.php" class="btn-logout">Deconnexion</a>
    </div>
</nav>

<div class="page-wrapper">
    <a href="dashboard.php" class="back-link">Retour au dashboard</a>

    <div class="page-title">Nouvelle Depense</div>
    <div class="page-sub">Soumettez une demande a vos parents</div>

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
                    Nouvelle demande
                </a>
            </div>
        <?php else: ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Description</label>
                <input class="form-input" type="text"
                       name="description"
                       placeholder="Ex: Sandwich, Stylos, Cinema"
                       value="<?= htmlspecialchars($_POST['description'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">Categorie</label>
                <select class="form-input" name="categorie" required>
                    <option value="">-- Choisir une categorie --</option>
                    <option value="Nourriture">Nourriture</option>
                    <option value="Loisirs">Loisirs</option>
                    <option value="Education">Education</option>
                    <option value="Vetements">Vetements</option>
                    <option value="Autre">Autre</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Montant (TND)</label>
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
                    Envoyer la demande
                </button>
            </div>
        </form>

        <?php endif; ?>
    </div>
</div>

</body>
</html>