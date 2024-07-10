<?php 
namespace App\Models;

use PDO;
use Exception;
use App\Core\DbConnect;
use App\Entities\Client;


class ClientModel extends DbConnect
{

    /**
     * Permet de créer un enregistrement dans la table
     * 
     * @param object [User] permet de bénéficier de l'injonction de dépendance
     */
    public function create(Client $user): void
    {
        $this->request = $this->connexion->prepare("INSERT INTO client VALUES (NULL, :nb_user, :name, :surname, :email, :phone)");
        $this->request -> bindValue(":nb_user", $user-> getNb_Client());
        $this->request -> bindValue(":name", $user->getName());
        $this->request -> bindValue(":surname", $user->getSurname());
        $this->request -> bindValue(":email", $user->getEmail());
        $this->request -> bindValue(":phone", $user -> getPhone());
        $this->executeTryCatch();
    }


    /**
     * Lecture de tous les enregiàstrements de la table
     * 
     * @return array [$user] Retourne tous les enregistrements de la table
     */
    public function findAll(): array
    {
        $this->request = $this->connexion->prepare("SELECT * FROM client ORDER BY idClient DESC");
        $this->request->execute();
        $user = $this->request->fetchAll();
        return $user;
    }


    /**
     * Permet de récupèrer l'enregistrement correspondant à l'email
     * 
     * @param string [email] Email de l'enregistrement à récupérer
     * @return object [user] Retourne l'enregistrement correspondant
     */
    public function find(string $email): object | false
    {
        $this->request = $this->connexion->prepare("SELECT * FROM client WHERE email = :email");
        $this->request ->bindParam(':email', $email);
        $this->request -> execute();
        $user = $this->request->fetch();
        return $user;
    }


     /**
     * Permet de récupèrer l'enregistrement correspondant à l'email
     * 
     * @param int [id] Email de l'enregistrement à récupérer
     * @return object [user] Retourne l'enregistrement correspondant
     */
    public function findAdmin(int $id): object
    {
        $this->request = $this->connexion->prepare("SELECT * FROM client WHERE idClient = :id");
        $this->request ->bindParam(':id', $id);
        $this->request -> execute();
        $user = $this->request->fetch();
        return $user;
    }


    /**
     * Mets à jour un enregistrement client
     * 
     * @param string [$email] Email utilisateur à metre à jour
     * @param object [$$client] Object permettant la mise à jour par injection de dépendance
     */
    public function update(string $email, Client $client): void
    {
        $this->request = $this->connexion->prepare('UPDATE client SET name = :name, surname = :surname, email = :email, tel = :tel WHERE email = :email');
        $this->request ->bindValue(':email', $email);
        $this->request ->bindValue(':name', $client->getName());
        $this->request ->bindValue(':surname', $client->getSurname());
        $this->request ->bindValue(':email', $client->getEmail());
        $this->request ->bindValue(':tel', $client->getPhone());
        $this->executeTryCatch();
    }


    /**
     * Permet la suppression de l'enregistrement correspondant à l'id passé en argument
     * @param int [id] Id de l'utilisateur
     */
    public function delete(int $id): void
    {
        $this->request = $this->connexion->prepare("DELETE FROM client WHERE idClient = :id");
        $this->request->bindParam(":id", $id);
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