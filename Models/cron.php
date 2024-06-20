<?php

/**
 * Script cron afin qui se connecte toutes les 24H à la base
 * afin de supprimer toutes les transport ou date_transport vaut aujourd'hui.
 */
define("SERVER", "127.0.0.1:3306"/*"localhost"*/);
define("USER", "u572485290_abcdtaxi"/*"root"*/);
define("PASSWORD", "6vzA6U!^T"/*""*/);
define("BASE", "u572485290_abcdtaxi"/*"abcdtaxi"*/);

    //Connexion à la bdd
    try 
    {
        $connexion = new PDO("mysql:host=".SERVER.";dbname=".BASE.";charset=UTF8", USER, PASSWORD);
    
        $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    } catch (Exception $e) {
        echo 'Erreur :'. $e->getMessage();
    }
    
    $date = date('Y-m-d');

    $request = $connexion->prepare('DELETE FROM transport WHERE date_transport = :date_transport');
    $request -> bindParam(':date_transport', $date);
    $request-> execute();

    