<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/create.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/layout.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$compte  = getCompteByUser($pdo, $user_id);
$objectifs = getObjectifsByCompte($pdo, $compte['id']);
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom           = trim($_POST['nom'] ?? '');
    $montant_cible = trim($_POST['montant_cible'] ?? '');
    $date_limite   = trim($_POST['date_limite'] ?? '') ?: null;

    if (empty($nom) || empty($montant_cible)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!is_numeric($montant_cible) || $montant_cible <= 0) {
        $error = 'Montant cible invalide.';
    } else {
        createObjectif($pdo, $compte['id'], $nom, $montant_cible, $date_limite);
        $success  = 'Objectif cree avec succes !';
        $objectifs = getObjectifsByCompte($pdo, $compte['id']);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Mes Objectifs</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">

   

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/enfant.css">
    
</head>

<body class="bg-gradient-to-br from-indigo-50 via-white to-cyan-50 min-h-screen overflow-x-hidden">

<!-- NAVBAR -->
<?php renderNavbar(); ?>

<!-- BUBBLES -->
<div class="fixed inset-0 pointer-events-none overflow-hidden">
    <div class="bubble w-72 h-72 bg-purple-400 top-[-120px] right-[-120px]"></div>
    <div class="bubble w-96 h-96 bg-cyan-300 bottom-[-150px] left-[-150px]"></div>
    <div class="bubble w-64 h-64 bg-pink-300 top-1/2 left-1/3"></div>
</div>

<!-- MAIN -->
<div class="relative z-10 max-w-5xl mx-auto px-6 pt-32 pb-16 space-y-8">

    <!-- BACK -->
    <a href="dashboard.php"
       class="text-gray-500 font-bold hover:text-purple-600 transition inline-block">
        ← Retour au dashboard
    </a>

    <!-- TITLE -->
    <div>
        <h1 class="text-4xl font-black font-['Fredoka_One'] text-gray-800">
            Mes Objectifs d'Epargne
        </h1>
        <p class="text-gray-500 mt-1">
            Défini tes objectifs et suis ta progression
        </p>
    </div>

    <!-- FORM -->
    <div class="relative bg-white/70 backdrop-blur-xl border rounded-3xl p-6 shadow-xl overflow-hidden">

        <div class="absolute -top-10 -right-10 w-40 h-40 bg-purple-400 blur-3xl opacity-20 rounded-full"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-cyan-400 blur-3xl opacity-20 rounded-full"></div>

        <h3 class="text-lg font-black mb-4">Nouvel Objectif</h3>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 font-bold p-3 rounded-xl mb-3">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 font-bold p-3 rounded-xl mb-3">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">

            <input type="text" name="nom"
                   placeholder="Nom de l'objectif"
                   class="w-full p-3 rounded-xl border focus:ring-4 focus:ring-purple-200 outline-none">

            <input type="number" name="montant_cible"
                   placeholder="Montant cible"
                   class="w-full p-3 rounded-xl border focus:ring-4 focus:ring-cyan-200 outline-none">

            <input type="date" name="date_limite"
                   class="w-full p-3 rounded-xl border focus:ring-4 focus:ring-pink-200 outline-none">

            <button type="submit"
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold hover:scale-105 transition">
                Créer l'objectif
            </button>

        </form>
    </div>

    <!-- LIST -->
    <div class="space-y-4">

        <h2 class="text-xl font-black border-l-4 border-purple-500 pl-3">
            Mes Objectifs
        </h2>

        <?php if (empty($objectifs)): ?>
            <div class="bg-white p-6 rounded-2xl text-center text-gray-500 font-bold border">
                Aucun objectif pour le moment
            </div>
        <?php endif; ?>

        <div class="space-y-4">

            <?php foreach ($objectifs as $obj): ?>
                <?php
                    $progression = min($obj['progression'], 100);
                    $color = $progression >= 100 ? '#10B981'
                            : ($progression >= 50 ? '#F59E0B' : '#8B5CF6');
                ?>

                <div class="bg-white/80 backdrop-blur-xl border rounded-2xl p-5 shadow-md hover:shadow-xl transition">

                    <div class="flex justify-between">
                        <div class="font-black text-gray-800">
                            <?= htmlspecialchars($obj['nom']) ?>
                        </div>

                        <?php if ($progression >= 100): ?>
                            <span class="text-xs bg-green-100 text-green-600 px-3 py-1 rounded-full font-bold">
                                Atteint
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="text-sm text-gray-500 mt-1">
                        <?= number_format($obj['montant_actuel'], 2) ?> /
                        <?= number_format($obj['montant_cible'], 2) ?> TND
                    </div>

                    <div class="w-full bg-gray-200 h-3 rounded-full mt-3 overflow-hidden">
                        <div class="h-full rounded-full transition-all"
                             style="width: <?= $progression ?>%; background: <?= $color ?>;">
                        </div>
                    </div>

                    <div class="flex justify-between mt-2 text-sm font-bold">
                        <span style="color: <?= $color ?>;">
                            <?= $progression ?>%
                        </span>
                        <?php if ($progression < 100): ?>
                            <span class="text-gray-500">
                                Il reste <?= number_format($obj['montant_cible'] - $obj['montant_actuel'], 2) ?> TND
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="flex gap-2 mt-4">
                        <a href="edit_objectif.php?id=<?= $obj['id'] ?>"
                           class="bg-[#0A2A6B] text-white px-6 py-2.5 rounded-xl font-bold text-lg hover:bg-blue-800 transition duration-300 shadow-lg">
                            Modifier
                        </a>

                        <a href="delete_objectif.php?id=<?= $obj['id'] ?>"
                           class="px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition">
                            Supprimer
                        </a>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>
    </div>
</div>

<!-- JS (RESTORED) -->
<script>
const msg = document.getElementById('delete-msg');

if (msg) {
    setTimeout(function () {
        msg.style.transition = 'opacity 0.5s ease';
        msg.style.opacity = '0';
        setTimeout(() => msg.style.display = 'none', 500);
    }, 3000);
}

document.addEventListener('wheel', function (e) {
    if (document.activeElement.type === 'number') {
        document.activeElement.blur();
    }
});
</script>

</body>
</html>