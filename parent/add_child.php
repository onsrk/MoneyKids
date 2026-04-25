<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/users/create.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/layout.php';
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
</head>
<body class="bg-gradient-to-b from-orange-100 via-white to-blue-100 min-h-screen">

<!-- NAVBAR -->
<?php renderNavbar(); ?>

<!-- CONTENT -->
<div class="max-w-5xl mx-auto px-6 pt-32 pb-12 space-y-8">
    <a href="dashboard.php" class="text-[#0A2A6B] font-semibold hover:underline">
        ← Retour au dashboard
    </a>
    <div class="text-3xl font-bold text-[#0A2A6B]">Ajouter un enfant</div>
    <div class="page-sub">Créez un compte pour votre enfant</div>

    <div class="form-card">

        <?php if ($error): ?>
            <div class="alert-error"> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="max-w-5xl mx-auto px-6 pt-24 md:pt-28 lg:pt-32 pb-12 space-y-8">
                <?= htmlspecialchars($success) ?>
                <br><br>
                
    <a href="dashboard.php" class="text-[#0A2A6B] font-semibold hover:underline">
        ← Retour au dashboard
    </a>

            </div>
        <?php else: ?>

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
        <div class="relative">
            <input type="number" name="age"
                value="<?= htmlspecialchars($_POST['age'] ?? '') ?>"
                class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
                placeholder=" ">
            <label class="absolute -top-3 left-0 text-sm font-bold text-gray-500 transition-all peer-placeholder-shown:text-base peer-placeholder-shown:top-2 peer-placeholder-shown:font-normal peer-placeholder-shown:text-gray-500 peer-focus:-top-3 peer-focus:text-sm peer-focus:font-bold peer-focus:text-blue-900">
                Age
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
        

    

            <div class="form-actions">
                <a href="dashboard.php" class="px-6 py-2.5 text-[#0A2A6B] font-semibold border border-[#0A2A6B] rounded-xl hover:bg-[#0A2A6B] hover:text-white transition-all duration-300 hover:scale-105">
                     Annuler
                </a>
                <button type="submit" class="w-full py-3 rounded-lg text-white bg-[#0A2A6B] hover:bg-blue-800 transition font-semibold">
                    Créer le compte 
                </button>
            </div>
        </form>

        <?php endif; ?>
    </div>
</div>

</body>
<script>
document.addEventListener('wheel', function(e) {
    if (document.activeElement.type === 'number') {
        document.activeElement.blur();
    }
});
</script>
</html>