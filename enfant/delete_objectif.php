<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/crud/objectifs/delete.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $objectif_id = (int) $_GET['id'];
    $user_id = $_SESSION['user_id'];

    deleteObjectif($pdo, $objectif_id, $user_id);

    header("Location: objectif.php?success=deleted");
    exit();
}
?>