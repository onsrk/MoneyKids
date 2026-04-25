<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/create.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/read.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$compte  = getCompteByUser($pdo, $user_id);
$objectifs = getObjectifsByCompte($pdo, $compte['id']);
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
    } else {
        createObjectif($pdo, $compte['id'], $nom, $montant_cible, $date_limite);
        $success  = 'Objectif cree avec succes !';
        $objectifs = getObjectifsByCompte($pdo, $compte['id']);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Mes Objectifs</title>
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

    <div class="page-title">Mes Objectifs d'Epargne</div>
    <div class="page-sub">Definis tes objectifs et suis ta progression</div>

    <!-- CREATE OBJECTIF FORM -->
    <div class="form-card" style="margin-bottom: 32px;">
        <h3 style="font-size:18px; font-weight:800;
                   color:#1e293b; margin-bottom:20px;">
            Nouvel Objectif
        </h3>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Nom de l'objectif *</label>
                <input class="form-input" type="text"
                       name="nom"
                       placeholder="Ex: Lego, Velo, Jeu video..."
                       value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                       required>
            </div>
            <div class="form-group">
                <label class="form-label">Montant cible (TND) *</label>
                <input class="form-input" type="number"
                       name="montant_cible"
                       placeholder="35.00"
                       min="0.01" step="0.01"
                       value="<?= htmlspecialchars($_POST['montant_cible'] ?? '') ?>"
                       required>
            </div>
            <div class="form-group">
                <label class="form-label">Date limite (optionnel)</label>
                <input class="form-input" type="date"
                       name="date_limite"
                       min="<?= date('Y-m-d') ?>"
                       value="<?= htmlspecialchars($_POST['date_limite'] ?? '') ?>">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Creer l'objectif
                </button>
            </div>
        </form>
    </div>

    <!-- OBJECTIFS LIST — US7 progression here -->
    <div class="section-title">Mes Objectifs</div>

    <?php if (empty($objectifs)): ?>
        <div class="card" style="text-align:center; padding:40px;">
            <div style="font-size:18px; font-weight:800; color:#1e293b;">
                Aucun objectif defini
            </div>
            <div style="color:#64748B; margin-top:8px;">
                Cree ton premier objectif d'epargne !
            </div>
        </div>

    <?php else: ?>
        <div class="objectifs-grid">
            <?php foreach ($objectifs as $obj): ?>
                <?php
                    $progression = min($obj['progression'], 100);
                    $atteint = $progression >= 100;
                    $color = $atteint ? '#10B981' :
                            ($progression >= 50 ? '#F59E0B' : '#2563EB');
                ?>
                <div class="objectif-card">
                    <div class="objectif-header">
                        <div class="objectif-name">
                            <?= htmlspecialchars($obj['nom']) ?>
                        </div>
                        <?php if ($atteint): ?>
                            <span class="badge badge-green">Atteint !</span>
                        <?php endif; ?>
                    </div>

                    <div class="objectif-amounts">
                        <span class="objectif-actuel">
                            <?= number_format($obj['montant_actuel'], 2) ?> TND
                        </span>
                        <span class="objectif-cible">
                            / <?= number_format($obj['montant_cible'], 2) ?> TND
                        </span>
                    </div>
<!-- CRUD BUTTONS -->
<div style="display:flex; gap:8px; margin-top:12px; margin-bottom:8px;">
    <a href="edit_objectif.php?id=<?= $obj['id'] ?>"
       class="btn-secondary"
       style="font-size:12px; padding:6px 12px;">
        Modifier
    </a>
    <a href="delete_objectif.php?id=<?= $obj['id'] ?>"
       class="btn-danger"
       style="font-size:12px; padding:6px 12px;"
       onclick="return confirm('Supprimer cet objectif ?')">
        Supprimer
    </a>
</div>
                    <!-- PROGRESS BAR — US7 -->
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill"
                                 style="width: <?= $progression ?>%;
                                        background: <?= $color ?>;">
                            </div>
                        </div>
                        <div class="progress-percent"
                             style="color: <?= $color ?>">
                            <?= $progression ?>%
                        </div>
                    </div>

                    <?php if (!$atteint): ?>
                        <div class="objectif-restant">
                            Il reste <?= number_format($obj['montant_cible'] - $obj['montant_actuel'], 2) ?> TND
                        </div>
                    <?php endif; ?>

                    <?php if ($obj['date_limite']): ?>
                        <div class="objectif-date">
                            Date limite : <?= date('d/m/Y', strtotime($obj['date_limite'])) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Epargner vers cet objectif -->
                    <?php if (!$atteint): ?>
                    <form method="POST" 
                          action="epargner.php" 
                          style="margin-top: 12px;">
                        <input type="hidden" 
                               name="objectif_id" 
                               value="<?= $obj['id'] ?>">
                        <div style="display:flex; gap:8px;">
                            <input class="form-input" 
                                   type="number"
                                   name="montant_epargne"
                                   placeholder="Montant a epargner"
                                   min="0.01" step="0.01"
                                   style="flex:1;">
                            <button type="submit" class="btn-primary">
                                Epargner
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php if (isset($_GET['success']) && $_GET['success'] == 'deleted'): ?>
    <div class="alert-success" id="delete-msg">
        Objectif supprimé avec succès !
    </div>
<?php endif; ?>

</div>
</div>

</body>
<script>
// Auto hide success message after 3 seconds
const msg = document.getElementById('delete-msg');
if (msg) {
    setTimeout(function() {
        msg.style.transition = 'opacity 0.5s ease';
        msg.style.opacity = '0';
        setTimeout(function() {
            msg.style.display = 'none';
        }, 500);
    }, 3000); 
}

document.addEventListener('wheel', function(e) {
    if (document.activeElement.type === 'number') {
        document.activeElement.blur();
    }
});
</script>
</html>