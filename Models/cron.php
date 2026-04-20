<?php

use PDO;
use Exception;
/**
 * Script cron afin qui se connecte toutes les 24H à la base
 * afin de supprimer toutes les transport ou date_transport vaut aujourd'hui.
 */
define("SERVER", "127.0.0.1:3306");
define("USER", "u374194450_abcdtaxi");
define("PASSWORD", 'H*4ihW$T5e');
define("BASE", "u374194450_abcdtaxi");

    //Connexion à la bdd
    try 
    {
        $connexion = new PDO("mysql:host=".SERVER.";dbname=".BASE.";charset=UTF8", USER, PASSWORD);
    
       
    } catch (Exception $e) {
        echo 'Erreur :'. $e->getMessage();
    }

// Formate la date heure francaise
date_default_timezone_set('Europe/Paris');
$date = date('Y-m-d');

// Requête préparée
$request = $connexion->prepare('DELETE FROM transport WHERE date_transport = :date_transport');
$request->bindParam(':date_transport', $date);
$result = $request->execute();

    