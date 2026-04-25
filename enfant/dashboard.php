<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/layout.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/comptes/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/read.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/update.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$user_id      = $_SESSION['user_id'];
$prenom       = $_SESSION['prenom'];
$nom          = $_SESSION['nom'];
$compte       = getCompteByUser($pdo, $user_id);
$transactions = getTransactionsByUser($pdo, $user_id);
$objectifs    = getObjectifsByCompte($pdo, $compte['id']);
$pending_count = getPendingRequestsCountByChild($pdo, $compte['id']);
$deleted_tx = $_GET['deleted_tx'] ?? '';


// Total depenses ce mois
$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN type = 'debit' AND description NOT LIKE 'Epargne%' 
            THEN montant ELSE 0 END) as total_depenses,
        SUM(CASE WHEN type = 'debit' AND description LIKE 'Epargne%' 
            THEN montant ELSE 0 END) as total_epargne
    FROM transaction t
    JOIN compte c ON c.id = t.compte_id
    WHERE c.user_id = ?
    AND MONTH(t.date_transaction) = MONTH(NOW())
    AND YEAR(t.date_transaction) = YEAR(NOW())
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids — Mon Espace</title>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/enfant.css">
</head>

<body >

<!-- NAVBAR -->
<?php renderNavbar(); ?>


<!-- IMPORTANT FIX: navbar spacing -->
<div class="max-w-5xl mx-auto px-6 pt-24 md:pt-28 lg:pt-32 pb-12 space-y-8">
    

<?php if (!empty($deleted_tx)): ?>
    <div id="deleted-tx" class="mb-5 bg-green-50 border border-green-200 text-green-700 font-bold px-4 py-3 rounded-xl">
        Depense supprimee avec succes
    </div>
<?php endif; ?>
<div class="fixed inset-0 pointer-events-none overflow-hidden">
    <div class="bubble w-72 h-72 bg-purple-400 top-[-120px] right-[-120px]"></div>
    <div class="bubble w-96 h-96 bg-cyan-300 bottom-[-150px] left-[-150px]"></div>
    <div class="bubble w-64 h-64 bg-pink-300 top-1/2 left-1/3"></div>
</div>
<!-- HERO -->
<div class="relative overflow-hidden rounded-3xl p-10 mb-8 text-white shadow-2xl
            bg-gradient-to-r from-purple-500 via-pink-500 to-orange-400
            before:content-[''] before:absolute before:top-[-60px] before:right-[-60px]
            before:w-[250px] before:h-[250px] before:rounded-full
            before:bg-white/10
            after:content-[''] after:absolute after:bottom-[-80px] after:left-[30%]
            after:w-[200px] after:h-[200px] after:rounded-full
            after:bg-white/5">

    <div class="text-4xl font-[Fredoka_One] mb-2">
        Bonjour <?= htmlspecialchars($prenom) ?>
    </div>

    <div class="text-white/80 font-semibold">
        Voici un aperçu complet de tes finances du jour
    </div>

    <div class="absolute right-10 top-1/2 -translate-y-1/2
                bg-white/20 backdrop-blur-md border border-white/30
                rounded-2xl px-6 py-4 text-center">
        <div class="text-xs uppercase tracking-wider text-white/80 font-bold">
            Mon Solde
        </div>
        <div class="text-2xl font-[Fredoka_One]">
            <?= number_format($compte['solde'], 2) ?> TND
        </div>
    </div>
</div>

<!-- STATS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">

    <div class="bg-white rounded-3xl p-6 shadow-md border-t-4 border-blue-400 hover:-translate-y-1 transition">
        <div class="text-xs font-bold text-gray-400 uppercase">Argent de poche</div>
        <div class="text-2xl font-bold text-blue-600 mt-2">
            <?= number_format($compte['montant_argent_poche'], 2) ?> TND
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-md border-t-4 border-orange-400 hover:-translate-y-1 transition">
        <div class="text-xs font-bold text-gray-400 uppercase">Dépenses</div>
        <div class="text-2xl font-bold text-orange-600 mt-2">
            <?= number_format($stats['total_depenses'] ?? 0, 2) ?> TND
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-md border-t-4 border-green-400 hover:-translate-y-1 transition">
        <div class="text-xs font-bold text-gray-400 uppercase">Epargne</div>
        <div class="text-2xl font-bold text-green-600 mt-2">
            <?= number_format($stats['total_epargne'] ?? 0, 2) ?> TND
        </div>
    </div>

</div>

<!-- ACTIONS -->
<div class="flex flex-wrap gap-3 mb-8">

    <a href="depense.php"
       class="px-5 py-3 rounded-2xl font-bold text-white bg-gradient-to-r from-blue-500 to-cyan-400 hover:scale-105 transition">
        Nouvelle dépense
    </a>

    <a href="objectif.php"
       class="px-5 py-3 rounded-2xl font-bold text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:scale-105 transition">
        Mes objectifs
    </a>

    <a href="recompenses.php"
       class="px-5 py-3 rounded-2xl font-bold text-white bg-gradient-to-r from-orange-500 to-yellow-400 hover:scale-105 transition">
        Mes récompenses
    </a>
<a href="demandes.php"
       class="px-5 py-3 rounded-2xl font-bold text-white bg-gradient-to-r from-red-500 to-pink-500 hover:scale-105 transition">
        Mes demandes
    </a>
</div>

<!-- OBJECTIFS -->
<?php if (!empty($objectifs)): ?>
<div class="mb-6 text-xl font-[Fredoka_One] border-l-4 border-purple-500 pl-3">
    Mes Objectifs
</div>

<div class="space-y-4 mb-10">

<?php foreach ($objectifs as $obj): ?>
<?php
$progression = min($obj['progression'], 100);
?>

<div class="bg-white rounded-2xl p-5 shadow">

    <div class="flex justify-between mb-2">
        <div class="font-bold">
            <?= htmlspecialchars($obj['nom']) ?>
        </div>
        <div class="text-sm text-gray-500 font-bold">
            <?= $obj['montant_actuel'] ?> / <?= $obj['montant_cible'] ?> TND
        </div>
    </div>

    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
        <div class="h-full rounded-full shimmer"
             style="width:<?= $progression ?>%;
             background: linear-gradient(90deg, #8B5CF6, #EC4899, #F97316);">
        </div>
    </div>

    <div class="text-right text-sm font-bold mt-2 text-purple-600">
        <?= $progression ?>%
    </div>

</div>

<?php endforeach; ?>

</div>
<?php endif; ?>

<!-- HISTORIQUE -->
<div class="text-xl font-[Fredoka_One] border-l-4 border-purple-500 pl-3 mb-4">
    Mon Historique
</div>

<?php if (empty($transactions)): ?>
<div class="bg-white p-10 rounded-2xl text-center text-gray-500 font-bold border border-dashed">
    Aucune transaction pour le moment
</div>

<?php else: ?>

<div class="space-y-3">

<?php foreach ($transactions as $tx): ?>

<div class="flex justify-between items-center bg-white p-4 rounded-xl shadow hover:scale-[1.01] transition">

    <div>
        <div class="font-bold"><?= htmlspecialchars($tx['description']) ?></div>
        <div class="text-xs text-gray-400">
            <?= date('d/m/Y H:i', strtotime($tx['date_transaction'])) ?>
        </div>
    </div>

    <div class="flex items-center gap-3">

        <div class="font-[Fredoka_One] <?= $tx['type'] === 'credit' ? 'text-green-500' : 'text-red-500' ?>">
            <?= $tx['type'] === 'credit' ? '+' : '-' ?>
            <?= number_format($tx['montant'], 2) ?> TND
        </div>

        <?php if ($tx['type'] === 'debit'): ?>
        <a href="delete_depense.php?id=<?= $tx['id'] ?>"
           class="px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition">
            Supprimer
        </a>
        <?php endif; ?>

    </div>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>

<script>
   
const deletedTx = document.getElementById('deleted-tx');
if (deletedTx) {
    setTimeout(function() {
        deletedTx.style.transition = 'opacity 0.5s ease';
        deletedTx.style.opacity = '0';
        setTimeout(function() {
            deletedTx.style.display = 'none';
        }, 500);
    }, 3000);
}
document.addEventListener('wheel', function(e) {
    if (document.activeElement.type === 'number') {
        document.activeElement.blur();
    }
});
</script>
</body>

</html>