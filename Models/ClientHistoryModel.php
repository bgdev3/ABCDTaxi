<?php

namespace App\Models;

use PDO;
use Exception;
use App\Core\DbConnect;
use App\Entities\ClientHistory;


class ClientHistoryModel extends DbConnect
{

    /**
     * Effectue une lecture de tous les enregistrements de la table
     * 
     * @return array [$user] Tableau des différents enregistrements
     */
    public function findAll(): array
    {
        $this->request = $this->connexion->prepare("SELECT * FROM client_history ORDER BY idClient_histo DESC");
        $this->request->execute();
        $user = $this->request->fetchAll();
        return $user;
    }

    /**
     * Lecture d'un enregistrement relatif à l'id passé en argument
     * 
     * @param int [$id] id de l'enregistrement à récupérer
     * @return object [$user] Retourne l'enregistrement 
     */
    public function find(int $id): object 
    {
        $this->request = $this->connexion->prepare("SELECT * FROM client_history WHERE idClient_histo = :id");
        $this->request ->bindParam(':id', $id);
        $this->request->execute();
        $user = $this->request->fetch();
        return $user;
    }
    

    /**
     * Lecture d'un enregistrement par le nom
     * 
     * @param string [$name] Pemet de récupérere un enregistrement par le nom
     * @return object [$user] Retourne l'enregistrement 
     */
    public function findByName(string $name): object
    {
        $this->request = $this->connexion->prepare("SELECT * FROM client_history WHERE name = :name");
        $this->request ->bindParam(':name', $name);
        $this->request -> execute();
        $user = $this->request->fetch();
        return $user;
    }


    /**
     * Permet la mise à jour de l'enregistrement correspondant
     * 
     * @param int [$int] Id de l'enregitrement à mettre à jour
     * @param object [$client] Permet de béénficier de l'injection de dépendance
     */
    public function update(int $id, ClientHistory $client): void
    {
        $this->request = $this->connexion->prepare('UPDATE client_history SET name = :name, surname = :surname, email = :email, tel = :tel WHERE idClient_histo = :id');
        $this->request ->bindValue(':id', $id);
        $this->request ->bindValue(':name', $client->getName());
        $this->request ->bindValue(':surname', $client->getSurname());
        $this->request ->bindValue(':email', $client->getEmail());
        $this->request ->bindValue(':tel', $client->getTel());
        $this->executeTryCatch();

    }
    

    /**
     * Permet la suppression de l'enregistrement correspondant
     * 
     * @param int [$id]  Id de l'enregitrement à mettre à jour
     */
    public function delete(int $id): void
    {
        $this->request = $this->connexion->prepare('DELETE FROM client_history WHERE idClient_histo = :id');
        $this->request ->bindParam(':id', $id);
        $this->executeTryCatch();
    }

    
     /**
     * Méthode privé qui permet de tester l'execution de la méthode éxecute
     */
    private function executeTryCatch(): void
    {
        try {
            $this->request->execute();
        } catch (Exception $e) {
            die("Erreur:" . $e->getMessage());
        }
        // Ferme le curseur permettant à la requette d'être de nouveau executée.
        $this->request->closeCursor();
    }  
}