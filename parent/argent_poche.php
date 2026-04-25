<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/users/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/update.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/create.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/layout.php';
// Check if logged in and is parent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    header('Location: ../authentification/login.php');
    exit();
}

// Get enfant id from URL
$enfant_id = $_GET['id'] ?? null;
if (!$enfant_id) {
    header('Location: dashboard.php');
    exit();
}

// Get enfant info
$enfant = getUserById($pdo, $enfant_id);
$compte = getCompteByUser($pdo, $enfant_id);

if (!$enfant || $enfant['parent_id'] != $_SESSION['user_id']) {
    header('Location: dashboard.php');
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant   = trim($_POST['montant'] ?? '');
    $frequence = trim($_POST['frequence'] ?? '');

    if (empty($montant) || !is_numeric($montant) || $montant <= 0) {
        $error = 'Veuillez entrer un montant valide.';
    } elseif (empty($frequence)) {
        $error = 'Veuillez choisir une fréquence.';
    } else {
        // UPDATE compte
        updateArgentPoche($pdo, $enfant_id, $montant, $frequence);

        // UPDATE solde
        updateSolde($pdo, $enfant_id, $montant, 'credit');

        // CREATE transaction
        createTransaction(
            $pdo,
            $compte['id'],
            $montant,
            'credit',
            'Argent de poche — ' . $frequence
        );

        $success = 'Argent de poche attribué avec succès !';

        // Refresh compte data
        $compte = getCompteByUser($pdo, $enfant_id);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Argent de poche</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="min-h-screen bg-gradient-to-b from-orange-100 via-white to-blue-100">

<?php renderNavbar(); ?>

<div class="max-w-5xl mx-auto px-6 pt-32 pb-12 space-y-8">

    <!-- BACK -->
    <a href="dashboard.php" class="text-[#0A2A6B] font-semibold hover:underline">
        ← Retour au dashboard
    </a>

    <!-- HEADER -->
    

    <!-- BALANCE CARD -->
    <div class="bg-white/70 backdrop-blur-sm border border-white/50 rounded-2xl shadow-lg p-8 flex justify-between items-center">

        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-[#0A2A6B] text-white flex items-center justify-center font-bold">
                <?= strtoupper(substr($enfant['prenom'], 0, 1)) ?>
            </div>

            <div>
                <div class="font-bold text-gray-800">
                    <?= htmlspecialchars($enfant['prenom'] . ' ' . $enfant['nom']) ?>
                </div>
                <div class="text-gray-500 text-sm">
                    <?= $enfant['age'] ? $enfant['age'] . ' ans' : '' ?>
                </div>
            </div>
        </div>

        <div class="text-right">
            <div class="text-sm text-gray-500">Solde actuel</div>
            <div class="text-3xl font-bold text-[#0A2A6B]">
                <?= number_format($compte['solde'], 2) ?> <span class="text-orange-500 text-lg">TND</span>
            </div>
            <div class="text-sm text-gray-600 mt-1">
                Poche : <?= number_format($compte['montant_argent_poche'], 2) ?> TND / <?= $compte['frequence'] ?>
            </div>
        </div>

    </div>

    <!-- FORM -->
    <div class="bg-white/70 backdrop-blur-sm border border-white/50 rounded-2xl shadow-lg p-8">

        <h2 class="text-xl font-bold text-[#0A2A6B] mb-6">
            Attribuer de l'argent de poche
        </h2>

        <?php if ($error): ?>
            <div class="text-red-600 font-semibold mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="text-green-600 font-semibold mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-10">

            <!-- MONTANT -->
            <div class="relative">
                <input
                    type="number"
                    name="montant"
                    min="0.01"
                    step="0.01"
                    value="<?= htmlspecialchars($_POST['montant'] ?? '') ?>"
                    class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
                    placeholder=" "
                    required
                >
                <label class="absolute left-0 -top-3 text-sm font-bold text-gray-500 transition-all
                    peer-placeholder-shown:top-2
                    peer-placeholder-shown:text-base
                    peer-placeholder-shown:font-normal
                    peer-focus:-top-3
                    peer-focus:text-sm
                    peer-focus:font-bold
                    peer-focus:text-blue-900">
                    Montant (TND)
                </label>
            </div>

            <!-- FREQUENCE -->
            <div class="relative pt-2">
    
    <select
        name="frequence"
        class="peer w-full py-3 bg-transparent border-b-2 border-gray-400 focus:border-blue-800 outline-none"
        required
    >
        <option value=""></option>
        <option value="hebdo" <?= ($_POST['frequence'] ?? '') === 'hebdo' ? 'selected' : '' ?>>
            Hebdomadaire
        </option>
        <option value="mensuel" <?= ($_POST['frequence'] ?? '') === 'mensuel' ? 'selected' : '' ?>>
            Mensuel
        </option>
    </select>

    <label class="absolute left-0 -top-4 text-sm font-bold text-gray-500 transition-all
        peer-focus:-top-4
        peer-focus:text-blue-900">
        Fréquence
    </label>

</div>

            <!-- BUTTONS -->
            <div class="flex gap-4">

                <a href="dashboard.php"
                   class="px-6 py-3 rounded-xl border border-[#0A2A6B] text-[#0A2A6B] font-semibold hover:bg-[#0A2A6B] hover:text-white transition">
                    Annuler
                </a>

                <button
                    type="submit"
                    class="px-6 py-3 rounded-xl bg-[#0A2A6B] text-white font-semibold hover:bg-blue-800 transition">
                    Attribuer →
                </button>

            </div>

        </form>

    </div>

    <!-- RESET -->
    <div class="text-right">
        <a href="delete_argent_poche.php?id=<?= $enfant_id ?>"
           onclick="return confirm('Réinitialiser l argent de poche ?')"
           class="px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition">
            Réinitialiser argent de poche
        </a>
    </div>

</div>

</body>
</html>
