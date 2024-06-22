<?php
namespace App\Controllers;

use App\Core\Form;
use App\Models\ClientHistoryModel;
use App\Models\TransportHistoryModel;

session_start();

class HistoryTransportController extends Controller
{

    /**
     * Affiche la liste des réservations
     * 
     * @param int [$token] Clé de sécurité
     */
    public function index($token): void
    {
        // Si l'admin est connecté et que les tokens GET et SESSION correspondent
        if (isset($_SESSION['username_admin']) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {

            // Récupère tous les enregistrements ClientHistory
            $transportHistory = [];
            $modelClient = new ClientHistoryModel();
            $listClient = $modelClient->findAll();
    
            // Instance du modèle transportHistory afin d'effectuer des jointures
            // sur chaque enregistrement clientHistory
            $modelTransport = new TransportHistoryModel();
            // Récupère sur chaque enregistrement l'idClient afin d'effectuer une jointure dans la boucle
            foreach($listClient as $client) {
    
                $idClient =  $client->idClient_histo;
                array_push($transportHistory,  $modelTransport->join($idClient));
            }
            $form = new Form();

            $form->startForm('#', 'POST', ['id' => 'mForm', 'class' => 'd-flex flex-column flex-md-row flex-lg-row align-items-md-end gap-2 mb-3']);
            $form->startDiv('col-12 col-md-6');
            $form->addLabel('sort', 'Recherche par :');
            $form->addSelect('sort', ['all'=>'Tous les transports', 'name' => 'Nom', 'date' => 'Date', 'cancel' => 'Transports annulés', 'done'=>'Transport effectués'], ['class' => 'form-select form-select-sm bg-transparent text-danger border border-secondary', 'id'=>'sort']);
            $form->endDiv();
            $form->startDiv('text-center d-flex  align-items-center gap-2');
            $form->addLabel('search', 'Nom: ', ['id' => 'searchLabel', 'class' => 'd-none']);
            $form->addInput('text', 'search', ['id'=>'search', 'class' => 'bg-transparent text-light border border-secondary  d-none']);
            $form->addLabel('searchDate', 'Date: ', ['id' => 'searchDateLabel', 'class' => 'd-none']);
            $form->addInput('date', 'searchDate', ['id' =>'searchDate', 'class' => 'bg-transparent text-light border border-secondary d-none']);
            $form->addInput('button', 'sortTransport', ['id' =>'sortTransport', 'class' => 'btn btn-sm btn-dark text-danger', 'value'=>'Rechercher']);
            $form->endDiv();
            $form->endForm();
        
            // Si l'admin n'est pas connecté, redirige vers l'accueil
            if (!isset($_SESSION['username_admin']))
                header('location:index.php');
            else
            $this->render('history/transport', ['list' =>  $transportHistory, 'searchForm' => $form->getFormElements()]);
            
        } else {
            header('location:index.php');
        }        
    }


    /**
     * Teste la valeur du select sélectionné afin d'effectuer la bonne requete sql
     * et de passer les données via API Fetch
     */
    public function selectHistory(): void
    {
        // Récupère les données de séléction
        $content = trim(file_get_contents("php://input"));
        $data = json_decode($content, true);
  
        $modelTransport = new TransportHistoryModel();
        $modelClient = new ClientHistoryModel();
        global $history;

       
        // Teste la valeur du sélect
        switch($data[0]) {

            // Tous les transports confondus
            case 'all':   
            //   Effectue une lecture de la table client
                $listClient = $modelClient->findAll();
                $history = $this->displayData($listClient, "join");
            break;
            // Transport par dates
            case 'date':
                // Récupère les enregistrements correspondants
                $listClient  = $modelClient->findAll();
                $history = $this->displayData($listClient, "joinByDate", $data[1]);
                
            break;
            // Transports par annulation
            case 'cancel':
               // Récupère les enregistrements correspondants
                $listClient = $modelClient->findAll();
                $history = $this->displayData($listClient, "joinByCancelation", true);
               
            break;
            // Transports effectués
            case 'done':
                $listClient = $modelClient->findAll();
                $history = $this->displayData($listClient, "joinByCancelation", false);
                $_SESSION['date'] = $history;
            break;
            // Sinon récupère les enregistrements par Nom sans boucle necessaire
            default :
               if (!empty($data[1]) ) {
                // Récupère les enregistrements correspondants
                $listClient = $modelClient->findByName($data[1]);
                // Récupère l'id correspondant puis effectue une jointure des tables
                $idClient =  $listClient->idClient_histo;
                $history = $modelTransport->join($idClient);
                }
            break;
        }

        echo json_encode($history);
    }


    /**
     * Applique sur les enregistremets récupérés les requêtes SQL correspondante
     * 
     * @param array [$state] Enregistrements des transports complets, nom, date, annulation ou effectuées
     * @param string [$func] Nom de la methode à executer
     * @param bool [$bool] Booléen qui permet l'utilisation de la bonne jointure SQL
     * @param object [$model] Model instancié avec un model assigné par défault
     * 
     * @return array [$history] Retourne l'historique 
     */
    private function displayData( $state, $func, $bool = null, $model = new TransportHistoryModel()): array
    {
        $history =[];
        // Boucle sur les enregistrements en arguments
        foreach ($state  as $val) {
            // Récupère les ids afin de les passer à la méthode à executer
            $idClient =  $val->idClient_histo;
            // Teste le booléen afin d'appler les bons arguments de la méthode   
            array_push($history, !isset($bool) ? $model->$func($idClient) : $model->$func($idClient, $bool));
        }
        
        return $history;
    }
}