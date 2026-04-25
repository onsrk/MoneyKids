<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/recompenses/read.php';

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
    ['badge' => 'Premier Pas',         'icon' => '👣', 'desc' => 'Premiere depense enregistree'],
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
    <title>MoneyKids — Mes Recompenses</title>
    <link rel="stylesheet" href="/MoneyKids/assets/css/style.css">
    <link rel="stylesheet" href="/MoneyKids/assets/css/enfant.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-logo">MoneyKids</div>
    <div class="nav-right">
        <span class="nav-user">
            <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?>
        </span>
        <a href="/MoneyKids/authentification/logout.php" class="btn-logout">
            Deconnexion
        </a>
    </div>
</nav>

<div class="page-wrapper">
    <a href="dashboard.php" class="back-link">← Retour au dashboard</a>

    <div class="page-title">Mes Recompenses</div>
    <div class="page-sub">
        Tes badges gagnes en gerant bien ton argent
    </div>

    <!-- NEW BADGE POPUP -->
    <?php if ($new_badge): ?>
        <div class="badge-popup" id="badge-popup">
            <div class="badge-popup-icon">🥇</div>
            <div class="badge-popup-title">Nouveau Badge !</div>
            <div class="badge-popup-desc">
                Tu as atteint ton objectif d epargne !
                Tu as gagne le badge « Super Epargnant » !
            </div>
        </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="solde-mini" style="margin-bottom:24px;">
        Badges gagnes :
        <span><?= count($recompenses) ?></span>
        sur
        <span><?= count($all_badges) ?></span>
    </div>

    <!-- BADGES GRID -->
    <div class="badges-grid">
        <?php foreach ($all_badges as $b): ?>
            <?php $earned = in_array($b['badge'], $earned_badges); ?>
            <div class="badge-card <?= $earned ? 'badge-earned' : 'badge-locked' ?>">
                <div class="badge-icon">
                    <?= $earned ? $b['icon'] : '🔒' ?>
                </div>
                <div class="badge-name">
                    <?= htmlspecialchars($b['badge']) ?>
                </div>
                <div class="badge-desc">
                    <?= htmlspecialchars($b['desc']) ?>
                </div>
                <?php if ($earned): ?>
                    <?php
                        // Get date earned
                        foreach ($recompenses as $r) {
                            if ($r['badge'] === $b['badge']) {
                                echo '<div class="badge-date">Obtenu le ' .
                                     date('d/m/Y', strtotime($r['date_obtention'])) .
                                     '</div>';
                                break;
                            }
                        }
                    ?>
                <?php else: ?>
                    <div class="badge-date">A debloquer</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<script>
// Auto hide badge popup after 5 seconds
const popup = document.getElementById('badge-popup');
if (popup) {
    setTimeout(function() {
        popup.style.transition = 'opacity 0.8s ease';
        popup.style.opacity = '0';
        setTimeout(function() {
            popup.style.display = 'none';
        }, 800);
    }, 5000);
}
</script>

</body>
</html>