<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/users/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/users/update.php';

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

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $age    = trim($_POST['age'] ?? '');

    if (empty($nom) || empty($prenom)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!is_numeric($age) || $age < 1 || $age > 17) {
        $error = 'L age doit etre entre 1 et 17 ans.';
    } else {
        updateEnfant($pdo, $enfant_id, $nom, $prenom, $age);
        $success = 'Profil mis a jour avec succes !';
        // Refresh enfant data
        $enfant = getUserById($pdo, $enfant_id);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Modifier enfant</title>
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

    <div class="page-title">Modifier le profil</div>
    <div class="page-sub">
        Modifier les informations de 
        <?= htmlspecialchars($enfant['prenom'] . ' ' . $enfant['nom']) ?>
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
            </div>
        <?php else: ?>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nom *</label>
                    <input class="form-input" type="text"
                           name="nom"
                           value="<?= htmlspecialchars($enfant['nom']) ?>"
                           required>
                </div>
                <div class="form-group">
                    <label class="form-label">Prenom *</label>
                    <input class="form-input" type="text"
                           name="prenom"
                           value="<?= htmlspecialchars($enfant['prenom']) ?>"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Age</label>
                <input class="form-input" type="number"
                       name="age"
                       min="1" max="17"
                       value="<?= htmlspecialchars($enfant['age']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input class="form-input" type="email"
                       value="<?= htmlspecialchars($enfant['email']) ?>"
                       disabled
                       style="background:#f8fafc; color:#94A3B8;">
                <small style="color:#94A3B8; font-size:11px;">
                    L email ne peut pas etre modifie
                </small>
            </div>

            <div class="form-actions">
                <a href="dashboard.php" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">
                    Enregistrer les modifications
                </button>
            </div>
        </form>

        <?php endif; ?>
    </div>
</div>

</body>
</html>