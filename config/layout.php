<?php
function renderHeader($title = 'MoneyKids') {
    echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $title . '</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/MoneyKids/assets/css/style.css">
</head>
<body>';
}

function renderNavbar() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $nom  = $_SESSION['prenom'] ?? null;
    $role = $_SESSION['role']   ?? null;
    $currentPage = basename($_SERVER['PHP_SELF']);
    $isHome = ($currentPage === 'index.php');

    // Dashboard link based on role
    $dashboard = '/MoneyKids/index.php';
    if ($role === 'parent') {
        $dashboard = '/MoneyKids/parent/dashboard.php';
    } elseif ($role === 'enfant') {
        $dashboard = '/MoneyKids/enfant/dashboard.php';
    }

    echo '
    <nav class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-xl border-b border-gray-200/50 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

            <!-- LOGO -->
            <a href="/MoneyKids/index.php"
               class="text-3xl font-black bg-gradient-to-br from-blue-800 to-orange-500 bg-clip-text text-transparent tracking-tight">
                MoneyKids
            </a>

            <!-- MENU — only on home -->
            ' . ($isHome ? '
            <div class="hidden md:flex items-center space-x-8 font-semibold text-[#0A2A6B]">
                <a href="#home"     class="hover:text-orange-500 transition">Accueil</a>
                <a href="#features" class="hover:text-orange-500 transition">Fonctionnalités</a>
                <a href="#works"    class="hover:text-orange-500 transition">Comment ca marche</a>
            </div>
            ' : '<div></div>') . '

            <!-- RIGHT SIDE -->';

    // ✅ Only check if logged in — not isHome
    if ($nom && $role) {
        echo '
            <div class="flex items-center space-x-3">
                <a href="' . $dashboard . '"
                   class="px-5 py-2 text-[#0A2A6B] font-bold border-2 border-[#0A2A6B] rounded-xl hover:bg-[#0A2A6B] hover:text-white transition hidden md:block">
                    ' . htmlspecialchars($nom) . '
                </a>
                <a href="/MoneyKids/authentification/logout.php"
                   class="px-5 py-2 bg-red-500 text-white rounded-xl font-bold hover:bg-red-600 transition">
                    Déconnexion
                </a>
            </div>';
    } else {
        echo '
            <div class="flex items-center space-x-3">
                <a href="/MoneyKids/authentification/login.php"
                   class="px-5 py-2 text-[#0A2A6B] font-semibold border-2 border-[#0A2A6B] rounded-xl hover:bg-[#0A2A6B] hover:text-white transition">
                    Connexion
                </a>
                <a href="/MoneyKids/authentification/registre.php"
                   class="px-5 py-2 bg-[#0A2A6B] text-white rounded-xl font-bold hover:bg-blue-800 transition shadow-lg">
                    S\'inscrire
                </a>
            </div>';
    }

    echo '
        </div>
    </nav>';
}

function renderFooter() {
    echo '
    <footer class="bg-white border-t border-gray-200 py-6 mt-20">
        <p class="text-center text-gray-500 text-sm font-semibold">
            © 2025 MoneyKids — Gérer son argent en s\'amusant
        </p>
    </footer>
</body>
</html>';
}
?>