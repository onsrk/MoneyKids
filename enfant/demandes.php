<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/update.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/layout.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$compte = getCompteByUser($pdo, $user_id);
$demandes = getChildRequests($pdo, $compte['id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Mes Demandes</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/MoneyKids/assets/css/style.css">
    <link rel="stylesheet" href="/MoneyKids/assets/css/enfant.css">
</head>

<body class="bg-gradient-to-br from-indigo-50 via-white to-cyan-50 min-h-screen font-[Nunito] overflow-x-hidden">

<?php renderNavbar(); ?>

<!-- BUBBLES -->
<div class="fixed inset-0 pointer-events-none overflow-hidden">
    <div class="bubble w-72 h-72 bg-purple-400 top-[-120px] right-[-120px]"></div>
    <div class="bubble w-96 h-96 bg-cyan-300 bottom-[-150px] left-[-150px]"></div>
    <div class="bubble w-64 h-64 bg-pink-300 top-1/2 left-1/3"></div>
</div>

<!-- MAIN -->
<div class="relative z-10 max-w-5xl mx-auto px-6 pt-24 md:pt-28 lg:pt-32 pb-12 space-y-8">

    <!-- BACK -->
    <a href="dashboard.php"
       class="text-gray-500 font-bold hover:text-purple-500 transition">
        ← Retour au dashboard
    </a>

    <!-- HERO -->
    <div class="relative overflow-hidden rounded-3xl p-10 text-white shadow-2xl
                bg-gradient-to-r from-blue-500 via-cyan-400 to-purple-500
                before:content-[''] before:absolute before:top-[-60px] before:right-[-60px]
                before:w-[250px] before:h-[250px] before:rounded-full
                before:bg-white/10
                after:content-[''] after:absolute after:bottom-[-80px] after:left-[30%]
                after:w-[200px] after:h-[200px] after:rounded-full
                after:bg-white/5">

        <div class="text-4xl font-[Fredoka_One] mb-2">
            Mes Demandes
        </div>

        <div class="text-white/80 font-semibold">
            Suivez l'état de vos demandes de dépense
        </div>

        <div class="absolute right-10 top-1/2 -translate-y-1/2
                    bg-white/20 backdrop-blur-md border border-white/30
                    rounded-2xl px-6 py-4 text-center">

            <div class="text-xs uppercase tracking-wider text-white/80 font-bold">
                Total demandes
            </div>

            <div class="text-2xl font-[Fredoka_One]">
                <?= count($demandes) ?>
            </div>
        </div>
    </div>

    <?php if (empty($demandes)): ?>

        <!-- EMPTY STATE -->
        <div class="bg-white rounded-3xl p-10 shadow-md text-center">

            <div class="text-2xl font-[Fredoka_One] text-gray-800 mb-2">
                Aucune demande
            </div>

            <div class="text-gray-500 font-semibold mb-6">
                Vous n'avez pas encore fait de demande
            </div>

            <a href="depense.php"
               class="inline-block px-6 py-3 rounded-2xl font-bold text-white
                      bg-gradient-to-r from-blue-500 to-cyan-400 hover:scale-105 transition">
                Nouvelle demande
            </a>

        </div>

    <?php else: ?>

        <!-- SECTION TITLE -->
        <div class="text-xl font-[Fredoka_One] border-l-4 border-purple-500 pl-3">
            Historique des demandes
        </div>

        <!-- TABLE -->
        <div class="bg-white rounded-3xl shadow-md overflow-hidden">

            <table class="w-full">

                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-6 py-4 text-sm font-bold text-gray-500 uppercase">Date</th>
                        <th class="text-left px-6 py-4 text-sm font-bold text-gray-500 uppercase">Description</th>
                        <th class="text-left px-6 py-4 text-sm font-bold text-gray-500 uppercase">Montant</th>
                        <th class="text-left px-6 py-4 text-sm font-bold text-gray-500 uppercase">Statut</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($demandes as $d): ?>

                        <tr class="border-b last:border-b-0 hover:bg-gray-50 transition">

                            <td class="px-6 py-5 font-semibold text-gray-600">
                                <?= date('d/m/Y', strtotime($d['date_soumission'])) ?>
                            </td>

                            <td class="px-6 py-5 font-bold text-gray-800">
                                <?= htmlspecialchars($d['description']) ?>
                            </td>

                            <td class="px-6 py-5 font-[Fredoka_One] text-red-500">
                                - <?= number_format($d['montant'], 2) ?> TND
                            </td>

                            <td class="px-6 py-5">

                                <?php if ($d['status'] == 'pending'): ?>
                                    <span class="px-4 py-2 rounded-xl bg-yellow-100 text-yellow-700 text-sm font-bold">
                                        En attente
                                    </span>

                                <?php elseif ($d['status'] == 'approved'): ?>
                                    <span class="px-4 py-2 rounded-xl bg-green-100 text-green-600 text-sm font-bold">
                                        Approuvée
                                    </span>

                                <?php else: ?>
                                    <span class="px-4 py-2 rounded-xl bg-red-100 text-red-600 text-sm font-bold">
                                        Refusée
                                    </span>

                                    <?php if ($d['motif_refus']): ?>
                                        <div class="text-sm text-red-500 font-semibold mt-2">
                                            Motif : <?= htmlspecialchars($d['motif_refus']) ?>
                                        </div>
                                    <?php endif; ?>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>

</body>
</html>