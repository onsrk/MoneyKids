<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/recompenses/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/layout.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$user_id      = $_SESSION['user_id'];
$recompenses  = getRecompensesByUser($pdo, $user_id);
$new_badge    = $_GET['badge'] ?? '';

// All possible badges
$all_badges = [
    ['badge' => 'Super Epargnant',    'icon' => '🥇', 'desc' => 'Atteindre un objectif d epargne'],
    ['badge' => 'Planificateur',       'icon' => '📋', 'desc' => 'Creer 3 objectifs d epargne'],
    ['badge' => 'Economiste',          'icon' => '💡', 'desc' => 'Epargner plus de 50 TND au total'],
];

$earned_badges = array_column($recompenses, 'badge');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Mes Récompenses</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/MoneyKids/assets/css/enfant.css">
    <link rel="stylesheet" href="/MoneyKids/assets/css/style.css">
</head>

<body class="bg-gradient-to-br from-indigo-50 via-white to-cyan-50 min-h-screen font-[Nunito] overflow-x-hidden">

<?php renderNavbar(); ?>

<!-- BUBBLES -->
<div class="fixed inset-0 pointer-events-none overflow-hidden">
    <div class="bubble w-72 h-72 bg-purple-400 top-[-120px] right-[-120px]"></div>
    <div class="bubble w-96 h-96 bg-cyan-300 bottom-[-150px] left-[-150px]"></div>
    <div class="bubble w-64 h-64 bg-pink-300 top-1/2 left-1/3"></div>
</div>

<body class="bg-gradient-to-br from-indigo-50 via-white to-cyan-50 min-h-screen font-[Nunito] overflow-x-hidden">

<?php renderNavbar(); ?>

<!-- MAIN CONTAINER -->
<div class="relative z-10 max-w-5xl mx-auto px-6 pt-24 md:pt-28 lg:pt-32 pb-12 space-y-8">

    <!-- BACK -->
    <a href="dashboard.php"
       class="text-gray-500 font-bold hover:text-purple-500 transition">
        ← Retour au dashboard
    </a>

    <!-- HERO -->
    <div class="relative overflow-hidden rounded-3xl p-10 text-white shadow-2xl
                bg-gradient-to-r from-orange-400 via-pink-500 to-purple-500
                before:content-[''] before:absolute before:top-[-60px] before:right-[-60px]
                before:w-[250px] before:h-[250px] before:rounded-full
                before:bg-white/10
                after:content-[''] after:absolute after:bottom-[-80px] after:left-[30%]
                after:w-[200px] after:h-[200px] after:rounded-full
                after:bg-white/5">

        <div class="text-4xl font-[Fredoka_One] mb-2">
            Mes Récompenses
        </div>

        <div class="text-white/80 font-semibold">
            Tes badges gagnés en gérant bien ton argent
        </div>

        <div class="absolute right-10 top-1/2 -translate-y-1/2
                    bg-white/20 backdrop-blur-md border border-white/30
                    rounded-2xl px-6 py-4 text-center">

            <div class="text-xs uppercase tracking-wider text-white/80 font-bold">
                Badges gagnés
            </div>

            <div class="text-2xl font-[Fredoka_One]">
                <?= count($recompenses) ?> / <?= count($all_badges) ?>
            </div>
        </div>
    </div>

    <!-- POPUP -->
    <?php if ($new_badge): ?>
         <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <script>
    window.onload = function () {

        const duration = 3 * 1000;
        const animationEnd = Date.now() + duration;

        const defaults = {
            startVelocity: 30,
            spread: 360,
            ticks: 60,
            zIndex: 999
        };

        function randomInRange(min, max) {
            return Math.random() * (max - min) + min;
        }

        const interval = setInterval(function () {
            const timeLeft = animationEnd - Date.now();

            if (timeLeft <= 0) {
                clearInterval(interval);
                return;
            }

            const particleCount = 50 * (timeLeft / duration);

            // Left side
            confetti({
                ...defaults,
                particleCount,
                origin: {
                    x: randomInRange(0.1, 0.3),
                    y: Math.random() - 0.2
                }
            });

            // Right side
            confetti({
                ...defaults,
                particleCount,
                origin: {
                    x: randomInRange(0.7, 0.9),
                    y: Math.random() - 0.2
                }
            });

        }, 250);
    };
    </script>

        <div id="badge-popup"
             class="bg-yellow-50 border border-yellow-200 text-yellow-700 font-bold px-5 py-4 rounded-2xl shadow-md">

            <div class="font-black">
                Nouveau badge débloqué
            </div>

            <div class="text-sm mt-1 font-semibold">
                Tu as atteint un objectif et gagné un nouveau badge.
            </div>

        </div>
    <?php endif; ?>

    <!-- SECTION TITLE -->
    <div class="text-xl font-[Fredoka_One] border-l-4 border-purple-500 pl-3">
        Tous les Badges
    </div>

    <!-- BADGES GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        <?php foreach ($all_badges as $b): ?>
            <?php $earned = in_array($b['badge'], $earned_badges); ?>

            <div class="bg-white rounded-3xl p-6 shadow-md border-t-4
                <?= $earned ? 'border-green-400 hover:-translate-y-1' : 'border-gray-200' ?>
                transition">

                <div class="text-lg font-bold text-gray-800">
                    <?= htmlspecialchars($b['badge']) ?>
                </div>

                <div class="text-sm text-gray-500 mt-2 leading-relaxed">
                    <?= htmlspecialchars($b['desc']) ?>
                </div>

                <?php if ($earned): ?>
                    <?php foreach ($recompenses as $r): ?>
                        <?php if ($r['badge'] === $b['badge']): ?>

                            <div class="mt-4 inline-block px-4 py-2 rounded-xl
                                        bg-green-50 text-green-600 text-sm font-bold">
                                Obtenu le <?= date('d/m/Y', strtotime($r['date_obtention'])) ?>
                            </div>

                        <?php break; endif; ?>
                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="mt-4 inline-block px-4 py-2 rounded-xl
                                bg-gray-100 text-gray-400 text-sm font-bold">
                        À débloquer
                    </div>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<script>
const popup = document.getElementById('badge-popup');

if (popup) {
    setTimeout(() => {
        popup.style.transition = 'opacity 0.8s ease';
        popup.style.opacity = '0';

        setTimeout(() => {
            popup.remove();
        }, 800);
    }, 5000);
}
</script>