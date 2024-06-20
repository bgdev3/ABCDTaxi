<?php
namespace App\Models;

use PDO;
use Exception;
use App\Core\DbConnect;
use App\Entities\Transport;

class TransportModel extends DbConnect {


    /**
     * Création d'un enregistrement 
     * 
     * @param object [transport] Permet de bénéficier de l'injection de dépendance
     */
    public function create(Transport $transport): void 
    {
        $this->request = $this->connexion->prepare("INSERT INTO transport VALUES (NULL, NOW(), :dateTransport,
                                                     :departure_time, :departure_place, :destination,
                                                     :roundTrip, :estimated_wait, :price, :idUser)");
        $this->request -> bindValue(":dateTransport", $transport->getDateTransport());
        $this->request -> bindValue(":departure_time",  $transport->getDeparture_time());
        $this->request -> bindValue(":departure_place",  $transport->getDeparture_place());
        $this->request -> bindValue(":destination", $transport -> getDestination());
        $this->request -> bindValue(':roundTrip', $transport -> getRoundTrip());
        $this->request -> bindValue(':estimated_wait', $transport -> getEstimated_wait());
        $this->request -> bindValue(':price', $transport ->getPrice());
        $this->request -> bindValue(":idUser", $transport -> getId_user());

        $this->executeTryCatch();
    }

    /**
     * Lecture de tous les enregistrements de la table
     * 
     * @return array [$date] Retourne tous les enregistrement 
     */
    public function findAll(): array 
    {
        $this->request =$this->connexion->prepare("SELECT * FROM transport");
        $this->request->execute();
        $list = $this->request->fetchAll();

        return $list;
    }

    /**
     * Récupère un enregistrement correspondant à l'id passé en argument
     * 
     * @param int [idTransport] Id du transport à récupérer
     * @return object [transport] Retourne l'enregistrement 
     */
    public function find(int $idTransport): object
    {
        $this->request = $this->connexion->prepare("SELECT * FROM transport WHERE idTransport = :idTransport");
        $this->request->bindParam(":idTransport", $idTransport);
        $this->request->execute();
        $transport = $this->request->fetch();

        return $transport;
    }


    /**
     * Permet la mise à jour d'un enregistrement correspondant 
     * à l'id passé en argument 
     * 
     * @param int [idTransport] Id du transport à récupérer
     * @param object [transport] Permet l'injonction de dépendance
     */
    public function update(int $idTransport, Transport $transport): void
    {
        $this->request = $this->connexion->prepare("UPDATE transport SET date_reservation = :date_reservation,
                                                     date_transport = :date_transport,
                                                     departureTime = :departure_time WHERE idTransport = :idTransport");
        $this->request -> bindValue(":idTransport", $idTransport);
        $this->request -> bindValue(":date_reservation", $transport->getDateReservation());
        $this->request -> bindValue(":date_transport",  $transport->getDateTransport());
        $this->request -> bindValue(":departure_time",  $transport->getDeparture_time());

        $this->executeTryCatch();

    }


     /**
     * Permet la mise à jour d'un enregistrement de transport administrateur correspondant 
     * à l'id passé en argument 
     * 
     * @param int [idTransport] Id du transport à récupérer
     * @param object [transport] Permet l'injonction de dépendance
     */
    public function updateAdmin(int $idTransport, Transport $transport): void
    {
        $this->request = $this->connexion->prepare("UPDATE transport SET 
                                                    date_transport = :date_transport,
                                                    departureTime = :departure_time, departurePlace = :departure_place,
                                                    destination = :destination, roundTrip = :roundTrip, price = :price WHERE idTransport = :idTransport");
        $this->request -> bindValue(":idTransport", $idTransport);
        $this->request -> bindValue(":date_transport",  $transport->getDateTransport());
        $this->request -> bindValue(":departure_time",  $transport->getDeparture_time());
        $this->request -> bindValue(":departure_place",  $transport->getDeparture_place());
        $this->request -> bindValue(":destination",  $transport->getDestination());
        $this->request -> bindValue(":roundTrip",  $transport->getRoundTrip());
        $this->request -> bindValue(":price",  $transport->getPrice());

        $this->executeTryCatch();

    }


    /**
     * Supprime l'enregistrement correspondant à l'id passé en arguments
     * 
     * @param int [idTransport] Id du transport à récupérer
     */
    public function delete(int $idTransport): void
    {   
        $this->request = $this->connexion->prepare("DELETE FROM transport WHERE idTransport = :idTransport");
        $this->request->bindParam(":idTransport", $idTransport);

        $this->executeTryCatch();
    }


    /**
     * Permet d'effectuer une jointure entre les tables client et transport
     * par l'idClient clé primaire de client correspondant à la clé étrangère de transport
     * 
     * @param int [id] Id client correspondant
     * @return array [$list] Retourne les enregistrements
     */
    public function join(int $id): array
    { 
        $this->request =$this->connexion->prepare("SELECT * FROM client RIGHT OUTER JOIN transport 
                                                    ON client.idClient = transport.idClient 
                                                    WHERE client.idClient = :id ORDER BY transport.date_transport ASC");
        $this->request->bindParam(':id',$id);
        $this->request->execute();
        $list = $this->request->fetchAll();
        
        return $list;
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