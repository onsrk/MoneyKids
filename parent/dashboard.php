<?php
session_start();
// Add this after session_start()
$deleted = $_GET['deleted'] ?? '';
// Add after $deleted check
$reset = $_GET['reset'] ?? '';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/users/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/layout.php';

// Check if logged in and is parent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    header('Location: ../authentification/login.php');
    exit();
}

$parent_id = $_SESSION['user_id'];
$enfants   = getEnfantsByParent($pdo, $parent_id);
$nom       = $_SESSION['nom'];
$prenom    = $_SESSION['prenom'];
?>
<<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Dashboard Parent</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-gradient-to-b from-orange-100 via-white to-blue-100 min-h-screen">

<?php renderNavbar(); ?>

<div class="max-w-7xl mx-auto px-6 pt-32 pb-12 space-y-8">

    <!-- HEADER -->
    <div class="bg-white/60 backdrop-blur-sm border border-white/50 rounded-2xl shadow-lg p-8">
        <h1 class="text-3xl font-bold text-[#0A2A6B]">
            Mes Enfants 
        </h1>
        <p class="text-gray-600 mt-2">
            Bonjour <?= htmlspecialchars($prenom) ?> ! Gérez les comptes de vos enfants.
        </p>
    </div>

    <!-- EMPTY STATE -->
    <?php if (empty($enfants)): ?>

        <div class="bg-white/60 backdrop-blur-sm border border-white/50 rounded-2xl shadow-lg p-10 text-center space-y-4">
            
            <h2 class="text-xl font-bold text-[#0A2A6B]">Aucun enfant ajouté</h2>
            <p class="text-gray-600">Commencez par créer un compte pour votre enfant</p>

            <a href="add_child.php"
               class="inline-block px-6 py-3 rounded-xl bg-[#0A2A6B] text-white font-semibold hover:bg-blue-800 transition">
                + Ajouter un enfant
            </a>
        </div>

    <?php else: ?>

        <!-- TABLE -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-lg overflow-hidden border border-white/50">

            <table class="w-full text-left">

                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="p-4">Enfant</th>
                        <th class="p-4">Age</th>
                        <th class="p-4">Solde</th>
                        <th class="p-4">Argent de poche</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($enfants as $enfant): ?>

                    <tr class="border-t hover:bg-white/60 transition">

                        <!-- ENFANT -->
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#0A2A6B] text-white flex items-center justify-center font-bold">
                                    <?= strtoupper(substr($enfant['prenom'], 0, 1)) ?>
                                </div>
                                <div class="font-semibold text-gray-800">
                                    <?= htmlspecialchars($enfant['prenom'] . ' ' . $enfant['nom']) ?>
                                </div>
                            </div>
                        </td>

                        <!-- AGE -->
                        <td class="p-4 text-gray-600">
                            <?= $enfant['age'] ? $enfant['age'] . ' ans' : '-' ?>
                        </td>

                        <!-- SOLDE -->
                        <td class="p-4 font-bold text-[#0A2A6B]">
                            <?= number_format($enfant['solde'], 2) ?> TND
                        </td>

                        <!-- POCKET MONEY -->
                        <td class="p-4 text-gray-700">
                            <?= number_format($enfant['montant_argent_poche'], 2) ?> TND / <?= $enfant['frequence'] ?>
                        </td>

                        <!-- ACTIONS -->
                        <td class="p-4">
                            <div class="flex flex-wrap gap-2">

                              

                                <a href="argent_poche.php?id=<?= $enfant['id'] ?>"
                                   class="px-3 py-1 text-sm rounded-lg border border-[#0A2A6B] text-[#0A2A6B] hover:bg-[#0A2A6B] hover:text-white transition">
                                    Poche
                                </a>

                                <a href="historique.php?id=<?= $enfant['id'] ?>"
                                   class="px-3 py-1 text-sm rounded-lg border border-[#0A2A6B] text-[#0A2A6B] hover:bg-[#0A2A6B] hover:text-white transition">
                                    Hist
                                </a>

                                <a href="edit_child.php?id=<?= $enfant['id'] ?>"
                                   class="px-3 py-1 text-sm rounded-lg border border-[#0A2A6B] text-[#0A2A6B] hover:bg-[#0A2A6B] hover:text-white transition">
                                    Modif
                                </a>

                                <a href="delete_child.php?id=<?= $enfant['id'] ?>"
                                   onclick="return confirm('Supprimer <?= htmlspecialchars($enfant['prenom']) ?> ?')"
                                   class="px-3 py-1 text-sm rounded-lg bg-red-500 text-white hover:bg-red-600 transition">
                                    Supp
                                </a>

                            </div>
                        </td>

                    </tr>

                <?php endforeach; ?>
                </tbody>

            </table>

        </div>

        <!-- ACTION BUTTONS -->
        <div class="flex flex-wrap gap-4">

            <a href="add_child.php"
               class="px-6 py-3 rounded-xl bg-[#0A2A6B] text-white font-semibold hover:bg-blue-800 transition">
                + Ajouter un enfant
            </a>

            <a href="approval.php"
               class="px-6 py-3 rounded-xl bg-orange-500 text-white font-semibold hover:bg-orange-600 transition">
                Approuver dépenses
            </a>

        </div>

        <!-- ALERTS -->
        <?php if ($deleted): ?>
            <div class="bg-green-100 text-green-700 px-6 py-4 rounded-xl">
                Compte enfant supprimé avec succès
            </div>
        <?php endif; ?>

        <?php if ($reset): ?>
            <div class="bg-green-100 text-green-700 px-6 py-4 rounded-xl">
                Argent de poche réinitialisé avec succès
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>
