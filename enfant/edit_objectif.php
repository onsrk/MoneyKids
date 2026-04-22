<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/update.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$objectif_id = $_GET['id'] ?? null;
if (!$objectif_id) {
    header('Location: objectif.php');
    exit();
}

$objectif = getObjectifById($pdo, $objectif_id);
if (!$objectif) {
    header('Location: objectif.php');
    exit();
}

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
    } elseif ($montant_cible < $objectif['montant_actuel']) {
        $error = 'Le montant cible ne peut pas etre inferieur au montant deja epargne (' . 
                  number_format($objectif['montant_actuel'], 2) . ' TND).';
    } else {
        // UPDATE objectif
        $stmt = $pdo->prepare("
            UPDATE objectif_epargne
            SET nom = ?, montant_cible = ?, date_limite = ?
            WHERE id = ?
        ");
        $stmt->execute([$nom, $montant_cible, $date_limite, $objectif_id]);
        $success = 'Objectif mis a jour avec succes !';
        $objectif = getObjectifById($pdo, $objectif_id);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Modifier objectif</title>
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
    <a href="objectif.php" class="back-link">← Retour aux objectifs</a>

    <div class="page-title">Modifier l objectif</div>
    <div class="page-sub">
        Modifier les details de votre objectif
    </div>

    <!-- CURRENT PROGRESS -->
    <div class="solde-mini" style="margin-bottom:20px;">
        Deja epargne :
        <span><?= number_format($objectif['montant_actuel'], 2) ?> TND</span>
        sur
        <span><?= number_format($objectif['montant_cible'], 2) ?> TND</span>
    </div>

    <div class="form-card">

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success">
                <?= htmlspecialchars($success) ?>
                <br><br>
                <a href="objectif.php" class="btn-primary">
                    Retour aux objectifs
                </a>
            </div>
        <?php else: ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Nom de l objectif *</label>
                <input class="form-input" type="text"
                       name="nom"
                       value="<?= htmlspecialchars($objectif['nom']) ?>"
                       required>
            </div>
            <div class="form-group">
                <label class="form-label">Montant cible (TND) *</label>
                <input class="form-input" type="number"
                       name="montant_cible"
                       min="<?= $objectif['montant_actuel'] ?>"
                       step="0.01"
                       value="<?= htmlspecialchars($objectif['montant_cible']) ?>"
                       required>
                <small style="color:#64748B; font-size:11px;">
                    Minimum : <?= number_format($objectif['montant_actuel'], 2) ?> TND
                    (montant deja epargne)
                </small>
            </div>
            <div class="form-group">
                <label class="form-label">Date limite (optionnel)</label>
                <input class="form-input" type="date"
                       name="date_limite"
                       min="<?= date('Y-m-d') ?>"
                       value="<?= htmlspecialchars($objectif['date_limite'] ?? '') ?>">
            </div>

            <div class="form-actions">
                <a href="objectif.php" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">
                    Enregistrer
                </button>
            </div>
        </form>

        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('wheel', function(e) {
    if (document.activeElement.type === 'number') {
        document.activeElement.blur();
    }
});
</script>

</body>
</html>