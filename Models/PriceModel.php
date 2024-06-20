<?php 
namespace App\Models;

use PDO;
use Exception;
use App\Core\DbConnect;
use App\Entities\Price;

class PriceModel extends DbConnect
{

    /**
     * Effectue une lecture de la table
     * 
     *  @return object [$price] Retourne les différenets enregistrements de la table
     */
    public function findAll(): object 
    {
        $this->request = $this->connexion->prepare("SELECT * FROM pricing");
        $this->request->execute();
        $price = $this->request->fetch();

        return $price;
    }

    /**
     * Effectue une mise à jour des données par injection de dépendance
     * 
     * @param object [$price ] instance de Price
     * @param int $id Identifiant de l'enregistrement à mettre à jour
     */
    public function update(Price $price, int $id): void
    {
        $this->request = $this->connexion->prepare("UPDATE pricing SET oneWayDay = :oneWayDay, returnJourneyDay = :returnJourneyDay,
                                                        oneWayNight = :oneWayNight, returnJourneyNight = :returnJourneyNight, waitingRate = :waitingRate WHERE idPrice = :id");                                        
        $this->request->bindValue(':id', $id);
        $this->request->bindValue(':oneWayDay', $price->getOneWayDay());
        $this->request->bindValue(':returnJourneyDay', $price->getReturnJourneyDay());
        $this->request->bindValue(':oneWayNight', $price->getOneWayNight());
        $this->request->bindValue(':returnJourneyNight', $price->getReturnJourneyNight());
        $this->request->bindValue(':waitingRate', $price->getWaitingRate());

        $this->executeTryCatch();
    }
    

    /**
     * Effectue une suppression d'un enregistrement
     * 
     * @param [$id] Identifiant de l'enregistrement à supprimer
     */
    public function delete(int $id): void
    {
        $this->request = $this->connexion->prepare("DELETE FROM pricing WHERE idPrice = :id");
        $this->request->bindParam(":id", $idPrice);
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