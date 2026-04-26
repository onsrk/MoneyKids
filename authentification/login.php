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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Connexion</title>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://kit.fontawesome.com/8368f69198.js" crossorigin="anonymous"></script>
</head>

<body class="min-h-screen bg-gradient-to-b from-orange-100 via-white to-blue-100 flex items-center justify-center p-4">

<div class="w-full max-w-2xl bg-white/40 border border-orange-100 p-8 rounded-xl shadow-lg backdrop-blur-sm space-y-6">

    <!-- LOGO -->
    <div class="text-center">
        <span class="text-3xl font-black bg-gradient-to-br from-blue-800 to-orange-500 bg-clip-text text-transparent">
            MoneyKids
        </span>
    </div>

    <!-- TITLE -->
    <div class="text-center space-y-1">
        <h2 class="text-2xl font-bold text-[#0A2A6B]">
            Connectez-vous à votre espace
        </h2>
        <p class="text-sm text-gray-700">
            Accédez à votre compte parent
        </p>
    </div>

    <!-- ERROR -->
    <?php if (!empty($error)): ?>
        <div class="text-red-600 font-semibold text-center">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- FORM -->
    <form method="POST" class="space-y-6">

        <!-- EMAIL -->
        <div class="relative">
            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
                placeholder=" "
                required
            >

            <label class="absolute -top-3 left-0 text-sm font-bold text-gray-500 transition-all
                peer-placeholder-shown:text-base
                peer-placeholder-shown:top-2
                peer-placeholder-shown:font-normal
                peer-placeholder-shown:text-gray-500
                peer-focus:-top-3
                peer-focus:text-sm
                peer-focus:font-bold
                peer-focus:text-blue-900">
                Email
            </label>
        </div>

        <!-- PASSWORD -->
        <div class="relative">
            <input
                type="password"
                name="password"
                class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
                placeholder=" "
                required
            >

            <label class="absolute -top-3 left-0 text-sm font-bold text-gray-500 transition-all
                peer-placeholder-shown:text-base
                peer-placeholder-shown:top-2
                peer-placeholder-shown:font-normal
                peer-placeholder-shown:text-gray-500
                peer-focus:-top-3
                peer-focus:text-sm
                peer-focus:font-bold
                peer-focus:text-blue-900">
                Mot de passe
            </label>
        </div>

        <!-- BUTTON -->
        <button class="w-full py-3 rounded-lg text-white bg-[#0A2A6B] hover:bg-blue-800 transition font-semibold">
            Se connecter
        </button>

    </form>

    <!-- LINK -->
    <div class="text-center text-sm">
        Pas encore de compte ?
        <a href="/MoneyKids/authentification/registre.php" class="text-blue-700 font-semibold">
    S'inscrire
</a>
    </div>

</div>

</body>
</html>