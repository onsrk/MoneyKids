<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/update.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/layout.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    header('Location: ../authentification/login.php');
    exit();
}

$parent_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle approval or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'] ?? null;
    $action = $_POST['action'] ?? '';
    $motif_refus = trim($_POST['motif_refus'] ?? '');

    if ($transaction_id && $action) {
        if ($action === 'approve') {
            $result = approveTransaction($pdo, $transaction_id, $parent_id);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        } 
        elseif ($action === 'decline') {
            $result = declineTransaction($pdo, $transaction_id, $parent_id, $motif_refus);
            $success = $result['message'];
        }
    }
}

$pending_requests = getPendingTransactionsByParent($pdo, $parent_id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyKids - Approbation des depenses</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/parent.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-orange-100 via-white to-blue-100 min-h-screen">

<?php renderNavbar(); ?>

<div class="max-w-5xl mx-auto px-6 pt-32 pb-12 space-y-8">
    <a href="dashboard.php" class="text-[#0A2A6B] font-semibold hover:underline">
        ← Retour au dashboard
    </a>
    <div class="text-3xl font-bold text-[#0A2A6B]">Approbation des depenses</div>
    <div class="page-sub">Approuvez ou refusez les demandes de vos enfants</div>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (empty($pending_requests)): ?>
        <div class="empty-state">
            <div style="font-size: 64px; margin-bottom: 16px;"></div>
            <div style="font-size: 20px; font-weight: 800; color: #1e293b;">Aucune demande en attente</div>
            <div style="color: #64748B; margin-top: 8px;">Les demandes de vos enfants apparaitront ici</div>
        </div>
    <?php else: ?>

<!-- HEADER -->
<div class="bg-white/60 backdrop-blur-sm border border-white/50 rounded-2xl shadow p-4 mb-6">
    <p class="text-[#0A2A6B] font-bold">
        <?= count($pending_requests) ?> demande(s) en attente d'approbation
    </p>
</div>

<!-- REQUESTS -->
<div class="space-y-6">

<?php foreach ($pending_requests as $request): ?>

    <div class="bg-white/70 backdrop-blur-sm border border-white/50 rounded-2xl shadow p-6 space-y-4">

        <!-- TOP -->
        <div class="flex justify-between items-center">
            <div class="font-bold text-[#0A2A6B]">
                <?= htmlspecialchars($request['prenom'] . ' ' . $request['nom']) ?>
            </div>

            <div class="text-orange-500 font-bold">
                <?= number_format($request['montant'], 2) ?> TND
            </div>
        </div>

        <!-- DETAILS -->
        <div class="text-sm text-gray-600 space-y-1">
            <p>Description: <?= htmlspecialchars($request['description']) ?></p>
            <p>Soumis le: <?= date('d/m/Y H:i', strtotime($request['date_soumission'])) ?></p>
            <p>Solde actuel: <?= number_format($request['solde'], 2) ?> TND</p>
        </div>

        <!-- FORM ACTIONS -->
        <form method="POST" class="flex flex-col sm:flex-row gap-3 sm:items-center">

            <input type="hidden" name="transaction_id" value="<?= $request['id'] ?>">

            <input type="text"
                   name="motif_refus"
                   placeholder="Motif du refus (optionnel)"
                   class="flex-1 px-4 py-2 border-b-2 border-gray-300 focus:border-blue-800 outline-none bg-transparent">

            <button type="submit"
                    name="action"
                    value="approve"
                    class="px-5 py-2 rounded-xl bg-[#0A2A6B] text-white font-semibold hover:bg-blue-800 transition">
                Approuver
            </button>

            <button type="submit"
                    name="action"
                    value="decline"
                    onclick="return confirm('Confirmer le refus ?')"
                    class="px-5 py-2 rounded-xl bg-red-500 text-white font-semibold hover:bg-red-600 transition">
                Refuser
            </button>

        </form>

    </div>

<?php endforeach; ?>

</div>

<?php endif; ?>

    
</div>

</body>
</html>