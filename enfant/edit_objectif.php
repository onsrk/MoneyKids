<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/update.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/layout.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$objectif_id = $_GET['id'] ?? null;
if (!$objectif_id) {
    header('Location: objectif.php');
    exit();
}

$objectif = getObjectifById($pdo, $objectif_id);
if (!$objectif) {
    header('Location: objectif.php');
    exit();
}

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
    } elseif ($montant_cible < $objectif['montant_actuel']) {
        $error = 'Le montant cible ne peut pas etre inferieur au montant deja epargne (' . 
                  number_format($objectif['montant_actuel'], 2) . ' TND).';
    } else {
        // UPDATE objectif
        $stmt = $pdo->prepare("
            UPDATE objectif_epargne
            SET nom = ?, montant_cible = ?, date_limite = ?
            WHERE id = ?
        ");
        $stmt->execute([$nom, $montant_cible, $date_limite, $objectif_id]);
        $success = 'Objectif mis a jour avec succes !';
        $objectif = getObjectifById($pdo, $objectif_id);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Modifier objectif</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">

    <!-- YOUR CSS FILE -->
    <link rel="stylesheet" href="/MoneyKids/assets/css/enfant.css">
    <link rel="stylesheet" href="/MoneyKids/assets/css/style.css">
</head>

<body class="bg-gradient-to-br from-indigo-50 via-white to-cyan-50 min-h-screen overflow-x-hidden font-[Nunito]">

<?php renderNavbar(); ?>

<!-- BACKGROUND BUBBLES (from enfant.css) -->
<div class="bubbles-layer"></div>

<div class="relative z-10 max-w-3xl mx-auto px-6 pt-28 pb-10 space-y-6">

    <!-- BACK -->
    <a href="objectif.php"
       class="text-gray-500 font-bold hover:text-purple-500 transition">
        ← Retour aux objectifs
    </a>

    <!-- TITLE -->
    <div>
        <h1 class="text-4xl font-black text-gray-800 font-['Fredoka_One']">
            Modifier l'objectif
        </h1>
        <p class="text-gray-500 mt-1">
            Modifier les détails de votre objectif
        </p>
    </div>

    <!-- PROGRESS INFO -->
    <div class="bg-white/80 backdrop-blur-lg border border-gray-200 rounded-2xl p-4 shadow-sm">
        Deja epargne :
        <span class="font-black text-purple-600">
            <?= number_format($objectif['montant_actuel'], 2) ?> TND
        </span>
        sur
        <span class="font-black text-cyan-600">
            <?= number_format($objectif['montant_cible'], 2) ?> TND
        </span>
    </div>

    <!-- FORM CARD -->
    <div class="relative bg-white/70 backdrop-blur-xl border border-gray-200 rounded-3xl p-6 shadow-xl overflow-hidden">

        <div class="absolute -top-10 -right-10 w-40 h-40 bg-purple-400 blur-3xl opacity-20 rounded-full"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-cyan-400 blur-3xl opacity-20 rounded-full"></div>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 font-bold p-3 rounded-xl mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 font-bold p-4 rounded-xl">
                <?= htmlspecialchars($success) ?>

                <div class="mt-4">
                    <a href="objectif.php"
                       class="px-5 py-2 bg-purple-500 text-white rounded-xl font-bold">
                        Retour aux objectifs
                    </a>
                </div>
            </div>
        <?php else: ?>

        <form method="POST" class="space-y-4">

            <div>
                <label class="font-bold text-gray-700">Nom de l'objectif</label>
                <input type="text" name="nom"
                       value="<?= htmlspecialchars($objectif['nom']) ?>"
                       class="w-full mt-1 p-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-purple-200 outline-none"
                       required>
            </div>

            <div>
                <label class="font-bold text-gray-700">Montant cible (TND)</label>
                <input type="number" name="montant_cible"
                       min="<?= $objectif['montant_actuel'] ?>"
                       step="0.01"
                       value="<?= htmlspecialchars($objectif['montant_cible']) ?>"
                       class="w-full mt-1 p-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-cyan-200"
                       required>
            </div>

            <div>
                <label class="font-bold text-gray-700">Date limite</label>
                <input type="date" name="date_limite"
                       min="<?= date('Y-m-d') ?>"
                       value="<?= htmlspecialchars($objectif['date_limite'] ?? '') ?>"
                       class="w-full mt-1 p-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-pink-200">
            </div>

            <div class="flex gap-3 pt-2">
                <a href="objectif.php"
                   class="px-5 py-3 rounded-xl bg-gray-100 font-bold hover:bg-gray-200 transition">
                    Annuler
                </a>

                <button type="submit"
                        class="px-5 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold shadow-lg hover:scale-105 transition">
                    Enregistrer
                </button>
            </div>

        </form>

        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('wheel', function(e) {
    if (document.activeElement.type === 'number') {
        document.activeElement.blur();
    }
});
</script>

</body>
</html>