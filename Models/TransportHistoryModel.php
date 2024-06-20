<?php

namespace App\Models;

use PDO;
use Execption;
use App\Core\DbConnect;


class TransportHistoryModel extends DbConnect
{

   /**
    * Lecture de la table de tous les enregistrements correspondant
    *
    * @param string [$date] Date correspondant aux enregistrements
    * @return array [$list] Contient les différentes enregistrements récupérer 
    */
    public function findDate(string $date): array
    {
        $this->request = $this->connexion->prepare("SELECT * FROM transport_history WHERE date_transport = :date_transport");
        $this->request->bindParam(':date_transport', $date);
        $this->request->execute();
        $list = $this->request->fetchAll();
        return $list;
    }


    /**
     * Lecture de tous les enregistrements par jointure sur les table client_history et transport_hsitory
     * 
     * @param int [$int] Id de l'enregistrement à retourné
     * @return array [$list] Touc les enregistrements récupérer 
     */
    public function join(int $id): array
    {
        $this->request =$this->connexion->prepare("SELECT * FROM client_history RIGHT OUTER JOIN transport_history 
                                                    ON client_history.idClient_histo = transport_history.idClient_histo 
                                                    WHERE client_history.idClient_histo = :id ORDER BY transport_history.date_transport ASC");
        $this->request->bindParam(':id',$id);
        $this->request->execute();
        $list = $this->request->fetchAll();
        return $list;
    }


    /**
     * Lecture de tous les enregistrements par jointure sur les table client_history et transport_hsitory
     * 
     * @param int [$int] Id de l'enregistrement à retourné
     * @param bool [$bool] Booléen permettant de récupérere les transports annulés ou effectués
     * @return array [$list] Retour la liste des enregistrements
     */
    public function joinByCancelation(int $id, bool $cancel): array
    {
        $this->request =$this->connexion->prepare("SELECT * FROM client_history RIGHT OUTER JOIN transport_history 
                                                    ON client_history.idClient_histo = transport_history.idClient_histo 
                                                    WHERE client_history.idClient_histo = :id AND cancelation = :cancelation ORDER BY transport_history.date_transport ASC");
        $this->request->bindParam(':id',$id);
        $this->request->bindValue(':cancelation', $cancel);
        $this->request->execute();
        $list = $this->request->fetchAll();
        return $list;
    }


    /**
     * Lecture de tous les enregistrements par jointure sur les table client_history et transport_history
     * 
     * @param int [$int]  Id de l'enregistrement à retourné
     * @param string [$date] Date correspondant aux enregistrements
     * @return array [$list] Retour la liste des enregistrements
     */
    
    public function joinByDate(int $id, string $dateTransport): array
    {
        $this->request =$this->connexion->prepare("SELECT * FROM client_history RIGHT OUTER JOIN transport_history 
                                                    ON client_history.idClient_histo = transport_history.idClient_histo 
                                                    WHERE client_history.idClient_histo = :id AND date_transport = :dateTransport ORDER BY transport_history.date_transport ASC");
        $this->request->bindParam(':id',$id);
        $this->request->bindValue(':dateTransport', $dateTransport);
        $this->request->execute();
        $list = $this->request->fetchAll();
        return $list;
    }
}