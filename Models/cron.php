<?php

use PDO;
use Exception;
/**
 * Script cron afin qui se connecte toutes les 24H à la base
 * afin de supprimer toutes les transport ou date_transport vaut aujourd'hui.
 * 
 * Implementer vos donner de connexuion ici et l alogique de connexion à la BDD
 */
 

   

// Formate la date heure francaise
date_default_timezone_set('Europe/Paris');
$date = date('Y-m-d');

// Requête préparée
$request = $connexion->prepare('DELETE FROM transport WHERE date_transport = :date_transport');
$request->bindParam(':date_transport', $date);
$result = $request->execute();

    