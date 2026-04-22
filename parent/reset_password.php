<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/users/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/users/update.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    header('Location: ../authentification/login.php');
    exit();
}

$enfant_id = $_GET['id'] ?? null;
if (!$enfant_id) {
    header('Location: dashboard.php');
    exit();
}

$enfant  = getUserById($pdo, $enfant_id);

if (!$enfant || $enfant['parent_id'] != $_SESSION['user_id']) {
    header('Location: dashboard.php');
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm      = trim($_POST['confirm'] ?? '');

    if (empty($new_password)) {
        $error = 'Veuillez entrer un nouveau mot de passe.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caracteres.';
    } elseif ($new_password !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        updatePassword($pdo, $enfant_id, $new_password);
        $success = 'Mot de passe reinitialise avec succes !';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Reinitialiser mot de passe</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/parent.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-logo">MoneyKids</div>
    <div class="nav-right">
        <span class="nav-user">
            <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?>
        </span>
        <a href="../authentification/logout.php" class="btn-logout">
            Deconnexion
        </a>
    </div>
</nav>

<div class="page-wrapper">
    <a href="dashboard.php" class="back-link">← Retour au dashboard</a>

    <div class="page-title">
        Reinitialiser mot de passe
    </div>
    <div class="page-sub">
        Compte de <?= htmlspecialchars($enfant['prenom'] . ' ' . $enfant['nom']) ?>
    </div>

    <div class="form-card">

        <?php if ($error): ?>
            <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success">
                <?= htmlspecialchars($success) ?>
                <br><br>
                <a href="dashboard.php" class="btn-primary">
                    Retour au dashboard
                </a>
            </div>
        <?php else: ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Nouveau mot de passe *</label>
                <input class="form-input" type="password"
                       name="new_password"
                       placeholder="••••••••" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirmer mot de passe *</label>
                <input class="form-input" type="password"
                       name="confirm"
                       placeholder="••••••••" required>
            </div>
            <div class="form-actions">
                <a href="dashboard.php" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">
                    Reinitialiser →
                </button>
            </div>
        </form>

        <?php endif; ?>
    </div>
</div>

</body>
</html>