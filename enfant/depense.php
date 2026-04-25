<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/layout.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$compte  = getCompteByUser($pdo, $user_id);
$error   = '';
$success = '';

// Get parent_id from enfant's account
$stmt = $pdo->prepare("SELECT parent_id FROM utilisateur WHERE id = ?");
$stmt->execute([$user_id]);
$parent = $stmt->fetch();
$parent_id = $parent['parent_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $montant     = trim($_POST['montant'] ?? '');
    $categorie   = trim($_POST['categorie'] ?? '');

    if (empty($description) || empty($montant) || empty($categorie)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!is_numeric($montant) || $montant <= 0) {
        $error = 'Montant invalide.';
    } else {
        // Insert transaction with status 'pending'
        $stmt = $pdo->prepare("
            INSERT INTO transaction (compte_id, montant, type, description, status, parent_id, date_soumission)
            VALUES (?, ?, 'debit', ?, 'pending', ?, NOW())
        ");
        $stmt->execute([$compte['id'], $montant, $categorie . ' - ' . $description, $parent_id]);
        
        $success = 'Demande envoyee. En attente de l approbation de vos parents.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids - Nouvelle Dépense</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/MoneyKids/assets/css/style.css">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        .bubble {
            position: absolute;
            border-radius: 9999px;
            filter: blur(1px);
            opacity: 0.25;
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0%,100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-indigo-50 via-white to-cyan-50 min-h-screen overflow-x-hidden">

<?php renderNavbar(); ?>

<!-- BACKGROUND BUBBLES -->
<div class="fixed inset-0 overflow-hidden pointer-events-none">
    <div class="bubble w-72 h-72 bg-purple-400 top-[-100px] right-[-100px]"></div>
    <div class="bubble w-96 h-96 bg-cyan-300 bottom-[-120px] left-[-120px]"></div>
    <div class="bubble w-64 h-64 bg-pink-300 top-1/2 left-1/3"></div>
</div>

<div class="relative z-10 max-w-5xl mx-auto px-6 pt-24 md:pt-28 lg:pt-32 pb-12 space-y-8">

    <!-- BACK -->
    <a href="dashboard.php"
       class="text-gray-500 font-bold hover:text-purple-500 transition">
        ← Retour au dashboard
    </a>
    <!-- TITLE -->
    <h1 class="text-4xl font-black mt-4 text-gray-800 font-['Fredoka_One']">
        Nouvelle Dépense
    </h1>

    <p class="text-gray-500 mt-1 mb-6">
        Soumets une demande à tes parents
    </p>

    <!-- SOLDE -->
    <div class="bg-white/80 backdrop-blur-lg border border-gray-200 rounded-2xl p-4 mb-6 shadow-sm">
        <p class="font-bold text-gray-700">
            Solde actuel :
            <span class="text-purple-600">
                <?= number_format($compte['solde'], 2) ?> TND
            </span>
        </p>
    </div>

    <!-- CARD -->
    <div class="relative bg-white/70 backdrop-blur-xl border border-gray-200 rounded-3xl p-6 shadow-xl overflow-hidden">

        <!-- neon glow -->
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
            </div>
        <?php else: ?>

        <form method="POST" class="space-y-4">

            <div>
                <label class="font-bold text-gray-700">Description</label>
                <input type="text" name="description"
                       class="w-full mt-1 p-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-purple-200 outline-none"
                       placeholder="Ex: Sandwich, Cinema..." required>
            </div>

            <div>
                <label class="font-bold text-gray-700">Catégorie</label>
                <select name="categorie"
                        class="w-full mt-1 p-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-cyan-200">
                    <option>Nourriture</option>
                    <option>Loisirs</option>
                    <option>Education</option>
                    <option>Vêtements</option>
                    <option>Autre</option>
                </select>
            </div>

            <div>
                <label class="font-bold text-gray-700">Montant (TND)</label>
                <input type="number" step="0.01" name="montant"
                       class="w-full mt-1 p-3 rounded-xl border border-gray-200 focus:ring-4 focus:ring-pink-200"
                       required>
            </div>

            <div class=" flex gap-3 pt-2 relative z-20">
                <a href="/MoneyKids/enfant/dashboard.php"
   class="px-6 py-2.5 text-[#0A2A6B] font-semibold border border-[#0A2A6B] rounded-xl hover:bg-[#0A2A6B] hover:text-white transition-all duration-300 hover:scale-105">
    Annuler
</a>

                <button type="submit"
                        class="px-5 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold shadow-lg hover:scale-105 transition">
                    Envoyer la demande
                </button>
            </div>

        </form>

        <?php endif; ?>

    </div>
</div>

</body>
</html>