<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['prenom']  = $user['prenom'];

            session_write_close();

            if ($user['role'] === 'parent') {
                header('Location: /MoneyKids/parent/dashboard.php');
            } else {
                header('Location: /MoneyKids/enfant/dashboard.php');
            }
            exit();
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<!-- auth/login.html -->
<body class="auth-page">
<div class="auth-card">
    <div class="auth-logo">
        <div class="auth-logo-text">MoneyKids</div>
        <div class="auth-logo-sub">Connectez-vous à votre espace</div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label">Email</label>
            <input class="form-input" type="email" name="email"
                   placeholder="votre@email.com" required>
        </div>
        <div class="form-group">
            <label class="form-label">Mot de passe</label>
            <input class="form-input" type="password"
                   name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-primary" style="width:100%">
            Se connecter →
        </button>
    </form>

    <div class="auth-link">
        Pas encore de compte ?
        <a href="registre.php">S'inscrire</a>
    </div>
</div>
</body>