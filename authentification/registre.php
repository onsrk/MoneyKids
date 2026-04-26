
<?php
session_start();
require_once '../config/db.php';
require_once '../crud/users/create.php';

// If already logged in → redirect
if (isset($_SESSION['user_id'])) {
    
    
header('Location: /MoneyKids/authentification/login.php');
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
        <h2 class="text-2xl font-bold text-[#0A2A6B]">Créer un compte parent</h2>
        <p class="text-sm text-gray-700">Rejoignez MoneyKids et commencez à gérer votre argent</p>
    </div>

    <!-- ERROR -->
    <?php if ($error): ?>
        <div class="text-red-600 font-semibold text-center">
             <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- FORM -->
    <form method="POST" class="space-y-6">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <!-- PRENOM -->
            <div class="relative">
                <input type="text" name="prenom"
                    value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                    class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
                    placeholder=" ">
                <label class="absolute -top-3 left-0 text-sm font-bold text-gray-500 transition-all peer-placeholder-shown:text-base peer-placeholder-shown:top-2 peer-placeholder-shown:font-normal peer-placeholder-shown:text-gray-500 peer-focus:-top-3 peer-focus:text-sm peer-focus:font-bold peer-focus:text-blue-900">
                    Prénom
                </label>
            </div>

            <!-- NOM -->
            <div class="relative">
                <input type="text" name="nom"
                    value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                    class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
                    placeholder=" ">
                <label class="absolute -top-3 left-0 text-sm font-bold text-gray-500 transition-all peer-placeholder-shown:text-base peer-placeholder-shown:top-2 peer-placeholder-shown:font-normal peer-placeholder-shown:text-gray-500 peer-focus:-top-3 peer-focus:text-sm peer-focus:font-bold peer-focus:text-blue-900">
                    Nom
                </label>
            </div>

        </div>

        <!-- EMAIL -->
        <div class="relative">
            <input type="email" name="email"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
                placeholder=" ">
            <label class="absolute -top-3 left-0 text-sm font-bold text-gray-500 transition-all peer-placeholder-shown:text-base peer-placeholder-shown:top-2 peer-placeholder-shown:font-normal peer-placeholder-shown:text-gray-500 peer-focus:-top-3 peer-focus:text-sm peer-focus:font-bold peer-focus:text-blue-900">
                Email
            </label>
        </div>

        <!-- PASSWORD -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <div class="relative">
                <input type="password" name="password"
                    class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
                    placeholder=" ">
                <label class="absolute -top-3 left-0 text-sm font-bold text-gray-500 transition-all peer-placeholder-shown:text-base peer-placeholder-shown:top-2 peer-placeholder-shown:font-normal peer-placeholder-shown:text-gray-500 peer-focus:-top-3 peer-focus:text-sm peer-focus:font-bold peer-focus:text-blue-900">
                    Mot de passe
                </label>
            </div>

            <div class="relative">
                <input type="password" name="confirm"
                    class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
                    placeholder=" ">
                <label class="absolute -top-3 left-0 text-sm font-bold text-gray-500 transition-all peer-placeholder-shown:text-base peer-placeholder-shown:top-2 peer-placeholder-shown:font-normal peer-placeholder-shown:text-gray-500 peer-focus:-top-3 peer-focus:text-sm peer-focus:font-bold peer-focus:text-blue-900">
                    Confirmer
                </label>
            </div>

        </div>

        <!-- BUTTON -->
        <button class="w-full py-3 rounded-lg text-white bg-[#0A2A6B] hover:bg-blue-800 transition font-semibold">
            S'inscrire 
        </button>

    </form>

    <!-- LINK -->
    <div class="text-center text-sm">
        Déjà un compte ?
        <a href="/MoneyKids/authentification/login.php" class="text-blue-700 font-semibold">
    Se connecter
</a>
    </div>

</div>

</body>
</html>