<?php
session_start();
// Add this after session_start()
$deleted = $_GET['deleted'] ?? '';
// Add after $deleted check
$reset = $_GET['reset'] ?? '';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/users/read.php';

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
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Dashboard Parent</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/parent.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-logo">MoneyKids</div>
    <div class="nav-right">
        <span class="nav-user">
            <?= htmlspecialchars($prenom . ' ' . $nom) ?>
        </span>
        <a href="../authentification/logout.php" class="btn-logout">
            Déconnexion
        </a>
    </div>
</nav>

<!-- CONTENT -->
<div class="page-wrapper">
    <div class="page-title">Mes Enfants </div>
    <div class="page-sub">
        Bonjour <?= htmlspecialchars($prenom) ?> ! 
        Gérez les comptes de vos enfants.
    </div>

    <?php if (empty($enfants)): ?>
        <div class="card" style="text-align:center; padding: 40px;">
            <div style="font-size: 48px; margin-bottom: 16px;"></div>
            <div style="font-size: 18px; font-weight: 800; color: #1e293b;">
                Aucun enfant ajouté
            </div>
            <div style="color: #64748B; margin: 8px 0 20px;">
                Commencez par créer un compte pour votre enfant
            </div>
            <a href="add_child.php" class="btn-primary">
                + Ajouter un enfant
            </a>
        </div>

    <?php else: ?>

<table class="children-table">
    <thead>
        <tr>
            <th>Enfant</th>
            <th>Age</th>
            <th>Solde</th>
            <th>Argent de poche</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($enfants as $enfant): ?>
            <tr>
                <!-- Enfant -->
                <td class="child-cell">
                    <div class="child-mini">
                        <div class="child-avatar">
                            <?= strtoupper(substr($enfant['prenom'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="child-name">
                                <?= htmlspecialchars($enfant['prenom'] . ' ' . $enfant['nom']) ?>
                            </div>
                        </div>
                    </div>
                </td>

                <!-- Age -->
                <td>
                    <?= $enfant['age'] ? $enfant['age'] . ' ans' : '-' ?>
                </td>

                <!-- Solde -->
                <td class="balance">
                    <?= number_format($enfant['solde'], 2) ?> TND
                </td>

                <!-- Pocket money -->
                <td>
                    <?= number_format($enfant['montant_argent_poche'], 2) ?> TND / <?= $enfant['frequence'] ?>
                </td>

                <!-- Actions -->
                <td>
                    <div class="actions">
                        <a href="reset_password.php?id=<?= $enfant['id'] ?>" class="btn-small btn-small-primary">MDP</a>
                        <a href="argent_poche.php?id=<?= $enfant['id'] ?>" class="btn-small btn-small-primary">Poche</a>
                        <a href="historique.php?id=<?= $enfant['id'] ?>" class="btn-small btn-small-secondary">Hist</a>
                        <a href="edit_child.php?id=<?= $enfant['id'] ?>" class="btn-small btn-small-secondary">Modif</a>
                        <a href="delete_child.php?id=<?= $enfant['id'] ?>"
   class="btn-small-danger"
   onclick="return confirm('Etes-vous sur de vouloir supprimer le compte de <?= htmlspecialchars($enfant['prenom']) ?> ? Cette action est irreversible.')">
    Supp
</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="add_child.php" class="btn-add-child">
    + Ajouter un enfant
</a>
<a href="approval.php" class="btn-primary" style="background: #F59E0B; margin-left: 10px;">
    Approuver depenses
</a>
<?php if ($deleted): ?>
    <div class="alert-success" id="delete-msg">
        Compte enfant supprime avec succes !
    </div>
<?php endif; ?>
<?php endif; ?>
</div>

<?php if ($reset): ?>
    <div class="alert-success" id="reset-msg">
        Argent de poche reinitialise avec succes !
    </div>
<?php endif; ?>
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
const resetMsg = document.getElementById('reset-msg');
if (resetMsg) {
    setTimeout(function() {
        resetMsg.style.transition = 'opacity 0.5s ease';
        resetMsg.style.opacity = '0';
        setTimeout(function() {
            resetMsg.style.display = 'none';
        }, 500);
    }, 3000);
}
</script>
</html>