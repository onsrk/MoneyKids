<?php
// This file wraps ALL pages with the same header/footer

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

    $nom = $_SESSION['prenom'] ?? null;
    $currentPage = basename($_SERVER['PHP_SELF']);
    $isHome = ($currentPage === "index.php");

    echo '
    <nav class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-xl border-b border-gray-200/50 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

            <!-- LOGO -->
            <div class="flex items-center justify-center text-3xl font-black bg-gradient-to-br from-blue-800 to-orange-500 bg-clip-text text-transparent tracking-tight">
                MoneyKids
            </div>

            <!-- MENU -->
            <div class="hidden md:flex items-center space-x-8 font-semibold text-[#0A2A6B]">
                <a href="/MoneyKids/index.php" class="nav-link">Home</a>
                <a href="#features" class="nav-link">Features</a>
                <a href="#works" class="nav-link">How it Works</a>
            </div>

';

    if (!$isHome && $nom) {
        
        echo '
        <div class="flex items-center space-x-4">
        <span class="px-6 py-2.5 hidden md:block text-sm text-gray-600">
            ' . htmlspecialchars($nom) . '
        </span>

        <a href="/MoneyKids/authentification/logout.php"
           class="px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition">
            Log out
        </a>
        </div>
    ';
    } else {
        
        echo '
        <div class="flex items-center space-x-4">
                    <a href="/MoneyKids/authentification/login.php" class="px-6 py-2.5 text-[#0A2A6B] font-semibold border border-[#0A2A6B] rounded-xl hover:bg-[#0A2A6B] hover:text-white transition-all duration-300 hover:scale-105">
                        Login
                    </a>
                    <a  href ="/MoneyKids/authentification/registre.php" class="bg-[#0A2A6B] text-white px-6 py-2.5 rounded-xl font-bold text-lg hover:bg-blue-800 transition duration-300 shadow-lg">
                        Sign Up
                    </a>
                </div>
    ';
    }

    echo '
            </div>

        </div>
    </nav>
    ';
}

function renderFooter() {
    echo '
    <footer class="footer">
        <p>© 2025 MoneyKids — Gérer son argent en s\'amusant</p>
    </footer>
</body>
</html>';
}
?>