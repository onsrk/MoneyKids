<?php
// This file wraps ALL pages with the same header/footer

function renderHeader($title = 'MoneyKids') {
    echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $title . ' — MoneyKids</title>
    <link rel="stylesheet" href="/moneykids/assets/css/style.css">
    <link rel="stylesheet" href="/moneykids/assets/css/components.css">
</head>
<body>';
}

function renderNavbar() {
    $nom = $_SESSION['nom'] ?? '';
    $role = $_SESSION['role'] ?? '';
    echo '
    <nav class="navbar">
        <div class="nav-logo">
            <span></span> MoneyKids
        </div>
        <div class="nav-right">
            <span class="nav-user"> ' . htmlspecialchars($nom) . '</span>
            <a href="/moneykids/auth/logout.php" class="btn-logout">Déconnexion</a>
        </div>
    </nav>';
}

function renderFooter() {
    echo '
    <footer class="footer">
        <p>© 2025 MoneyKids — Gérer son argent en s\'amusant </p>
    </footer>
</body>
</html>';
}
?>