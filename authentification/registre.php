<?php
session_start();
require_once '../config/db.php';
require_once '../crud/users/create.php';

// If already logged in → redirect
if (isset($_SESSION['user_id'])) {
    header('Location: registre.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom'] ?? '');
    $prenom   = trim($_POST['prenom'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm'] ?? '');

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif ($password !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        $result = createUser($pdo, $nom, $prenom, $email, $password, 'parent');
        if ($result['success']) {
            $success = 'Compte créé avec succès ! Vous pouvez vous connecter.';
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
    <title>MoneyKids — Inscription</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-page">
<div class="auth-card">

    <div class="auth-logo">
        <div class="auth-logo-text">MoneyKids</div>
        <div class="auth-logo-sub">Créez votre compte parent</div>
    </div>

    <?php if ($error): ?>
        <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert-success">
            ✅ <?= htmlspecialchars($success) ?>
            <br><a href="login.php">Se connecter →</a>
        </div>
    <?php else: ?>

    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label">Nom</label>
            <input class="form-input" type="text" name="nom"
                   placeholder="Ben Ali"
                   value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                   required>
        </div>
        <div class="form-group">
            <label class="form-label">Prénom</label>
            <input class="form-input" type="text" name="prenom"
                   placeholder="Mohamed"
                   value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                   required>
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input class="form-input" type="email" name="email"
                   placeholder="votre@email.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   required>
        </div>
        <div class="form-group">
            <label class="form-label">Mot de passe</label>
            <input class="form-input" type="password"
                   name="password" placeholder="••••••••" required>
        </div>
        <div class="form-group">
            <label class="form-label">Confirmer mot de passe</label>
            <input class="form-input" type="password"
                   name="confirm" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-primary" style="width:100%">
            S'inscrire →
        </button>
    </form>

    <?php endif; ?>

    <div class="auth-link">
        Déjà un compte ?
        <a href="login.php">Se connecter</a>
    </div>
</div>
</body>
</html>