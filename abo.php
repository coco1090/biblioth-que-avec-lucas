<?php
require_once __DIR__ . "/config/database.php";
$pdo = get_db_connection();

$motEnClair = "abonne";
$hash = password_hash($motEnClair, PASSWORD_DEFAULT);

$sql = "UPDATE abonne SET mot_de_passe = :hash";
$stmt = $pdo->prepare($sql);
$stmt->execute(['hash' => $hash]);

echo "Mot de passe hashé et mis à jour pour tous les abonnés.";
?>
