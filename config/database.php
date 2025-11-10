<?php

//desativer affichage erreur
ini_set('display_error', 1);

error_reporting(E_ALL);

// config de la base de donne

define('DB_HOST', 'localhost');
define('DB_NAME', 'bibliotheque');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function get_db_connection()
{
    //sta source name
    $dns = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    //ption de co
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // active exexption en cas erreur
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //mode recup du tableau
        PDO::ATTR_EMULATE_PREPARES => false, //desctive l'emulation des requete preparer
    ];
    try {
        $pdo = new PDO($dns, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOExeption $e) {
        die("Erreur de connexion à la base de donné : " . $e->getMessage());
    }
}

//get_db_connection();