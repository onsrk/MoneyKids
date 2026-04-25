<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/transactions/delete.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enfant') {
    header('Location: /MoneyKids/authentification/login.php');
    exit();
}

$transaction_id = $_GET['id'] ?? null;
if (!$transaction_id) {
    header('Location: dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$result  = deleteTransaction($pdo, $transaction_id, $user_id);

if ($result) {
    header('Location: dashboard.php?deleted_tx=1');
} else {
    header('Location: dashboard.php?error=1');
}
exit();
?>