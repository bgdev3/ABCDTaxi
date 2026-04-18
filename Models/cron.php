<?php

/**
 * Script cron afin qui se connecte toutes les 24H à la base
 * afin de supprimer toutes les transport ou date_transport vaut aujourd'hui.
 */
// define("SERVER", "127.0.0.1:3306");
// define("USER", "u374194450_abcdtaxi");
// define("PASSWORD", 'H*4ihW$T5e');
// define("BASE", "u374194450_abcdtaxi");

define("SERVER", "localhost");
define("USER", "root");
define("PASSWORD", 'test');
define("BASE", "abcdtaxi");

    //Connexion à la bdd
    try 
    {
        $connexion = new PDO("mysql:host=".SERVER.";dbname=".BASE.";charset=UTF8", USER, PASSWORD);
    
       $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   
    } catch (Exception $e) {
        echo 'Erreur :'. $e->getMessage();
    }

date_default_timezone_set('Europe/Paris');

// // require_once('/home/u374194450/domains/abcdtaxi.fr/public_html/dev/Core/DbConnect.php');

$date = date('Y-m-d');

// // file_put_contents('/home/u374194450/domains/abcdtaxi.fr/public_html/dev/cron.log', "Date utilisée : " . $date . PHP_EOL);
try {$request = $connexion->prepare('DELETE FROM transport WHERE date_transport = :date_transport');
$request->bindParam(':date_transport', $date);
$result = $request->execute();

} catch (Exception $e) {
    // Log de l'erreur sans l'exposer à l'utilisateur
   echo 'erreur';

}

// echo "Lignes trouvées : " . count($rows) . "\n";
// Log du résultat
// file_put_contents('/home/u374194450/domains/abcdtaxi.fr/public_html/dev/cron.log', "Résultat execute : " . var_export($result, true) . PHP_EOL, FILE_APPEND);
// file_put_contents('/home/u374194450/domains/abcdtaxi.fr/public_html/dev/cron.log', "Lignes supprimées : " . $request->rowCount() . PHP_EOL, FILE_APPEND);
// file_put_contents('/home/u374194450/domains/abcdtaxi.fr/public_html/dev/cron.log', "Erreur PDO : " . var_export($request->errorInfo(), true) . PHP_EOL, FILE_APPEND);
    