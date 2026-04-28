<?php
namespace App\Controllers;

use App\Services\Form;
use App\Models\ClientHistoryModel;
use App\Models\TransportHistoryModel;

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

class HistoryTransportController extends Controller
{

    public function __construct (
         private Form $form,
         private ClientHistoryModel $clientHistoryModel,
         private TransportHistoryModel $transportHistoryModel
         ){}

    public function index($token): void
    {
        // Si l'admin est connecté et que les tokens GET et SESSION correspondent
        if (isset($_SESSION['username_admin']) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {

            // Récupère tous les enregistrements ClientHistory
            $transportHistory = [];
            // $modelClient = new ClientHistoryModel();
            $listClient = $this->clientHistoryModel->findAll();
    
            // Instance du modèle transportHistory afin d'effectuer des jointures
            // sur chaque enregistrement clientHistory
            // Récupère sur chaque enregistrement l'idClient afin d'effectuer une jointure dans la boucle
            foreach($listClient as $client) {
    
                $idClient =  $client->idClient_histo;
                array_push($transportHistory,  $this->transportHistoryModel->join($idClient));
            }
            
            $this->form->startForm('#', 'POST', ['id' => 'mForm', 'class' => 'd-flex flex-column flex-md-row flex-lg-row align-items-md-end gap-2 mb-3']);
            $this->form->startDiv('col-12 col-md-6');
            $this->form->addLabel('sort', 'Recherche par :');
            $this->form->addSelect('sort', ['all'=>'Tous les transports', 'name' => 'Nom', 'date' => 'Date', 'cancel' => 'Transports annulés', 'done'=>'Transport effectués'], ['class' => 'form-select form-select-sm bg-transparent text-danger border border-secondary', 'id'=>'sort']);
            $this->form->endDiv();
            $this->form->startDiv('text-center d-flex  align-items-center gap-2');
            $this->form->addLabel('search', 'Nom: ', ['id' => 'searchLabel', 'class' => 'd-none']);
            $this->form->addInput('text', 'search', ['id'=>'search', 'class' => 'bg-transparent text-light border border-secondary  d-none']);
            $this->form->addLabel('searchDate', 'Date: ', ['id' => 'searchDateLabel', 'class' => 'd-none']);
            $this->form->addInput('date', 'searchDate', ['id' =>'searchDate', 'class' => 'bg-transparent text-light border border-secondary d-none']);
            $this->form->addInput('button', 'sortTransport', ['id' =>'sortTransport', 'class' => 'btn btn-sm btn-dark text-danger', 'value'=>'Rechercher']);
            $this->form->endDiv();
            $this->form->endForm();
        
            // Si l'admin n'est pas connecté, redirige vers l'accueil
            if (!isset($_SESSION['username_admin'])) {
                header('location:/public/');
                exit();
            } else
                $this->render('history/transport', ['list' =>  $transportHistory, 'searchForm' => $this->form->getFormElements()]);
            
        } else {
            header('location:/public/');
            exit();
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

        // $modelTransport = new TransportHistoryModel();
        // $modelClient = new ClientHistoryModel();
        global $history;

        // Teste la valeur du sélect
        switch($data[0]) {

            // Tous les transports confondus
            case 'all':   
            //   Effectue une lecture de la table client
                $listClient = $this->clientHistoryModel->findAll();
                $history = $this->displayData($listClient, "join");
            break;
            // Transport par dates
            case 'date':
                // Récupère les enregistrements correspondants
                $listClient  = $this->clientHistoryModel->findAll();
                $history = $this->displayData($listClient, "joinByDate", $data[1]);
                
            break;
            // Transports par annulation
            case 'cancel':
               // Récupère les enregistrements correspondants
                $listClient = $this->clientHistoryModel->findAll();
                $history = $this->displayData($listClient, "joinByCancelation", true);
               
            break;
            // Transports effectués
            case 'done':
                $listClient = $this->clientHistoryModel->findAll();
                $history = $this->displayData($listClient, "joinByDone", false);
                $_SESSION['date'] = $history;
            break;
            // Sinon récupère les enregistrements par Nom sans boucle necessaire
            default :
               if (!empty($data[1]) ) {
                // Récupère les enregistrements correspondants
                $listClient = $this->clientHistoryModel->findByName($data[1]);
                // Récupère l'id correspondant puis effectue une jointure des tables
                $idClient =  $listClient->idClient_histo;
                $history = $this->transportHistoryModel->join($idClient);
                }
            break;
        }
        
        echo json_encode(array('history' => $history, 'token' => $_SESSION['token']));
    }


    /**
     * Applique sur les enregistremets récupérés les requêtes SQL correspondante
     * 
     * @param array $state Enregistrements des transports complets, nom, date, annulation ou effectuées
     * @param string $func Nom de la methode à executer
     * @param bool $bool Booléen qui permet l'utilisation de la bonne jointure SQL
     * @param object $model Model instancié avec un model assigné par défault
     * 
     * @return array $history Retourne l'historique 
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