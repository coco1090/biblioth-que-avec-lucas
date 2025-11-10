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
//$deletePrep->execute([':id_livre' => $id_livre]);

$sql = "SELECT l.id_livre, l.auteur, l.titre, l.couverture, CASE WHEN e.id_emprunt IS NOT NULL THEN 'emprunté' ELSE 'disponible' END AS statut, CONCAT(a.nom, ' ', a.prenom) AS emprunteur FROM livre l LEFT JOIN emprunt e ON l.id_livre = e.id_livre AND e.date_rendu IS NULL LEFT JOIN abonne a ON e.id_abonne = a.id_abonne;";

$reqPrepare = $pdo->prepare($sql);
$reqPrepare->execute();

$livres = $reqPrepare->fetchALL();

foreach ($livres as $livre) {
    if ($livre['id_livre'] == $id_livre) {
        $titre = $livre['titre'];
        break;
    }
}

foreach ($livres as $livre) {
    if ($livre['id_livre'] == $id_livre) {
        $auteur = $livre['auteur'];
        break;
    }
}

$_SESSION['message_suppression'] = "Le livre '{$livre['titre']}' de {$livre['auteur']} a été supprimé avec succès.";

header('Location: livres.php');
exit();