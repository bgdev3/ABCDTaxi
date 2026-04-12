<?php 
namespace App\Core;

use PDO;
use Exception;

class DbConnect 
{
    // Variables protégées
    protected $connexion;
    protected $request;

    // Constante
    // const SERVEUR = /*'localhost'*/'127.0.0.1:3306';
    // const USER = /*'root'*/'u374194450_abcdtaxi';
    // const PASSWORD = /*''*/'@9EWg!$H3Ma';
    // const BASE = /*'abcdtaxi'*/'u374194450_abcdtaxi';

    // Constructeur qui initialise la connexion lors de l'instanciation de la classe
    public function __construct() 
    {
        // Si la connexion se déroule bien on se connecte
        // sinon on capture une exception
        // try {
        //     $this->connexion = new PDO('mysql:host=' . self::SERVEUR . ';dbname=' . self::BASE, self::USER, self::PASSWORD);
        //     // Activation des erreurs PDO
        //     $this->connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //     // Retour de requete en tableau objet par default
        //     $this->connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        //     // Encodage par default en utf8
        //     $this->connexion->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAME utf8');

        // } catch(exception $e) {
        //     die('Erreur:' . $e->getMessage());
        // }
        
                $serveur  = $_ENV['DB_SERVEUR'];
                $user     = $_ENV['DB_USER'];
                $password = $_ENV['DB_PASSWORD'];
                $base     = $_ENV['DB_BASE'];    

        try {
            $dsn = 'mysql:host=' . $serveur . ';dbname=' . $base . ';charset=utf8mb4';

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES   => false, // meilleure sécurité
            ];

            $this->connexion = new PDO($dsn, $user, $password, $options);

        } catch (Exception $e) {
            // On log l'erreur sans l'exposer à l'utilisateur
            error_log('Erreur de connexion BDD : ' . $e->getMessage());
            die('Une erreur de connexion est survenue. Veuillez réessayer plus tard.');
        }
    
    }
}