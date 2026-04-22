<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/users/create.php';

// Check if logged in and is parent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    header('Location: ../authentification/login.php');
    exit();
}

$error   = '';
$success = '';
$parent_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom'] ?? '');
    $prenom   = trim($_POST['prenom'] ?? '');
    $age      = trim($_POST['age'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!is_numeric($age) || $age < 1 || $age > 17) {
        $error = 'L\'âge doit être entre 1 et 17 ans.';
    } else {
        $result = createUser(
            $pdo, $nom, $prenom, $email, 
            $password, 'enfant', $parent_id, $age
        );
        if ($result['success']) {
            $success = 'Compte enfant créé avec succès !';
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Ajouter un enfant</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/parent.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-logo">MoneyKids</div>
    <div class="nav-right">
        <span class="nav-user">
            👤 <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?>
        </span>
        <a href="../authentification/logout.php" class="btn-logout">Déconnexion</a>
    </div>
</nav>

<!-- CONTENT -->
<div class="page-wrapper">
    <div class="page-title">Ajouter un enfant</div>
    <div class="page-sub">Créez un compte pour votre enfant</div>

    <div class="form-card">

        <?php if ($error): ?>
            <div class="alert-error"> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success">
                <?= htmlspecialchars($success) ?>
                <br><br>
                <a href="dashboard.php" class="btn-primary">
                    ← Retour au dashboard
                </a>
            </div>
        <?php else: ?>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nom *</label>
                    <input class="form-input" type="text" name="nom"
                           placeholder="Ben Ali"
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                           required>
                </div>
                <div class="form-group">
                    <label class="form-label">Prénom *</label>
                    <input class="form-input" type="text" name="prenom"
                           placeholder="Lina"
                           value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Âge *</label>
                <input class="form-input" type="number" name="age"
                       placeholder="8" min="1" max="17"
                       value="<?= htmlspecialchars($_POST['age'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">Email (pour connexion) *</label>
                <input class="form-input" type="email" name="email"
                       placeholder="lina@moneykids.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">Mot de passe *</label>
                <input class="form-input" type="password"
                       name="password" placeholder="••••••••" required>
            </div>

            <div class="form-actions">
                <a href="dashboard.php" class="btn-secondary">
                    ← Annuler
                </a>
                <button type="submit" class="btn-primary">
                    Créer le compte →
                </button>
            </div>
        </form>

        <?php endif; ?>
    </div>
</div>

</body>
</html>