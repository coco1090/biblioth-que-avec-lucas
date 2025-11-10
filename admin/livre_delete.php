<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: livres.php');
    exit();
}

$id_livre = intval($_GET['id']);
$pdo = get_Db_Connection();
$sql = "DELETE FROM livre WHERE id_livre = :id_livre";
$deletePrep = $pdo->prepare($sql);
$deletePrep->execute([':id_livre' => $id_livre]);


header('Location: livres.php');
exit();