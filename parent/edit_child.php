<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/users/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/users/update.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/layout.php';

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
    <title>MoneyKids — Modifier enfant</title>
    <link rel="stylesheet" href="/MoneyKids/assets/css/style.css">
    <link rel="stylesheet" href="/MoneyKids/assets/css/parent.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
<body class="min-h-screen bg-gradient-to-b from-orange-100 via-white to-blue-100">

<?php renderNavbar(); ?>

<div class="max-w-5xl mx-auto px-6 pt-32 pb-12 space-y-8">
    <a href="dashboard.php" class="text-[#0A2A6B] font-semibold hover:underline">
        ← Retour au dashboard
    </a>

    <div class="text-3xl font-bold text-[#0A2A6B]">Modifier le profil</div>
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

        <form method="POST" class="space-y-6">

    <!-- NOM + PRENOM -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        <!-- NOM -->
        <div class="relative">
            <input type="text"
                   name="nom"
                   value="<?= htmlspecialchars($enfant['nom']) ?>"
                   class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
                   placeholder=" "
                   required>

            <label class="absolute left-0 -top-4 text-sm font-bold text-gray-500
                peer-placeholder-shown:top-2
                peer-placeholder-shown:text-base
                peer-placeholder-shown:font-normal
                peer-focus:-top-4
                peer-focus:text-sm
                peer-focus:text-blue-900">
                Nom
            </label>
        </div>

        <!-- PRENOM -->
        <div class="relative">
           

            <input type="text"
                   name="prenom"
                   value="<?= htmlspecialchars($enfant['prenom']) ?>"
                   class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
                   placeholder=" "
                   required>

            <label class="absolute left-0 -top-4 text-sm font-bold text-gray-500
                peer-placeholder-shown:top-2
                peer-placeholder-shown:text-base
                peer-placeholder-shown:font-normal
                peer-focus:-top-4
                peer-focus:text-sm
                peer-focus:text-blue-900">
                Prénom
            </label>
        </div>

    </div>

    <!-- AGE -->
    <div class="relative">
        <input type="number"
               name="age"
               min="1" max="17"
               value="<?= htmlspecialchars($enfant['age']) ?>"
               class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
               placeholder=" ">

        <label class="absolute left-0 -top-4 text-sm font-bold text-gray-500
            peer-placeholder-shown:top-2
            peer-placeholder-shown:text-base
            peer-placeholder-shown:font-normal
            peer-focus:-top-4
            peer-focus:text-sm
            peer-focus:text-blue-900">
            Age
        </label>
    </div>

    <!-- EMAIL (disabled) -->
    <div class="relative">
        <input type="email"
               value="<?= htmlspecialchars($enfant['email']) ?>"
               disabled
               class="w-full py-3 bg-gray-100 border-b-2 border-gray-300 text-gray-500"
               placeholder=" ">

        <label class="absolute left-0 -top-4 text-sm font-bold text-gray-400">
            Email (non modifiable)
        </label>
    </div>

    <!-- PASSWORD -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        <div class="relative">
            <input type="password"
                   name="new_password"
                   class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
                   placeholder=" "
                  >

            <label class="absolute left-0 -top-4 text-sm font-bold text-gray-500
                peer-placeholder-shown:top-2
                peer-placeholder-shown:text-base
                peer-placeholder-shown:font-normal
                peer-focus:-top-4
                peer-focus:text-sm
                peer-focus:text-blue-900">
                Nouveau mot de passe
            </label>
        </div>

        <div class="relative">
            <input type="password"
                   name="confirm"
                   class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
                   placeholder=" "
                   >

            <label class="absolute left-0 -top-4 text-sm font-bold text-gray-500
                peer-placeholder-shown:top-2
                peer-placeholder-shown:text-base
                peer-placeholder-shown:font-normal
                peer-focus:-top-4
                peer-focus:text-sm
                peer-focus:text-blue-900">
                Confirmer mot de passe
            </label>
        </div>

    </div>

    <!-- BUTTONS -->
    <div class="flex gap-4 pt-4">

        <a href="dashboard.php"
           class="px-6 py-3 rounded-xl border border-[#0A2A6B] text-[#0A2A6B] font-semibold hover:bg-[#0A2A6B] hover:text-white transition">
            Annuler
        </a>

        <button type="submit"
                class="px-6 py-3 rounded-xl bg-[#0A2A6B] text-white font-semibold hover:bg-blue-800 transition">
            Enregistrer
        </button>

    </div>

</form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>