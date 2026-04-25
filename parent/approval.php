<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/update.php';

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
    <style>
        .request-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-left: 5px solid #F59E0B;
        }
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .enfant-name {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
        }
        .request-montant {
            font-size: 24px;
            font-weight: 900;
            color: #DC2626;
        }
        .request-details {
            color: #64748B;
            margin-bottom: 15px;
            padding: 10px;
            background: #F8FAFC;
            border-radius: 12px;
        }
        .btn-approve {
            background: #10B981;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 800;
            cursor: pointer;
        }
        .btn-decline {
            background: #EF4444;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 800;
            cursor: pointer;
        }
        .motif-input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-family: 'Nunito', sans-serif;
        }
        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 20px;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-logo">MoneyKids</div>
    <div class="nav-right">
        <span class="nav-user"><?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></span>
        <a href="../authentification/logout.php" class="btn-logout">Deconnexion</a>
    </div>
</nav>

<div class="page-wrapper">
    <div class="page-title">Approbation des depenses</div>
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
        <div style="margin-bottom: 20px; background: #EFF6FF; padding: 12px; border-radius: 12px;">
            <strong><?= count($pending_requests) ?></strong> demande(s) en attente d'approbation
        </div>

        <?php foreach ($pending_requests as $request): ?>
            <div class="request-card">
                <div class="request-header">
                    <div class="enfant-name">
                        <?= htmlspecialchars($request['prenom'] . ' ' . $request['nom']) ?>
                    </div>
                    <div class="request-montant">
                        <?= number_format($request['montant'], 2) ?> TND
                    </div>
                </div>

                <div class="request-details">
                    <div>Description: <?= htmlspecialchars($request['description']) ?></div>
                    <div>Soumis le: <?= date('d/m/Y H:i', strtotime($request['date_soumission'])) ?></div>
                    <div>Solde actuel: <?= number_format($request['solde'], 2) ?> TND</div>
                </div>

                <form method="POST" action="" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <input type="hidden" name="transaction_id" value="<?= $request['id'] ?>">
                    
                    <input type="text" 
                           name="motif_refus" 
                           class="motif-input" 
                           placeholder="Motif du refus (optionnel)"
                           style="flex: 1;">

                    <button type="submit" name="action" value="approve" class="btn-approve">
                        Approuver
                    </button>
                    <button type="submit" name="action" value="decline" class="btn-decline" 
                            onclick="return confirm('Confirmer le refus de cette depense ?')">
                        Refuser
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="margin-top: 30px;">
        <a href="dashboard.php" class="btn-secondary">Retour au dashboard</a>
    </div>
</div>

</body>
</html>