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
    const SERVEUR = ************;
    const USER = ************
    const PASSWORD = ************
    const BASE = ***********€

    // Constructeur qui initialise la connexion lors de l'instanciation de la classe
    public function __construct() 
    {
        // Si la connexion se déroule bien on se connecte
        // sinon on capture une exception
        try {
            $this->connexion = new PDO('mysql:host=' . self::SERVEUR . ';dbname=' . self::BASE, self::USER, self::PASSWORD);
            // Activation des erreurs PDO
            $this->connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Retour de requete en tableau objet par default
            $this->connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            // Encodage par default en utf8
            $this->connexion->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAME utf8');

        } catch(exception $e) {
            die('Erreur:' . $e->getMessage());
        }
    }
}
