<?php
namespace App\Controllers;

use App\Services\Form;
use App\Services\Captcha;
use App\Services\Language;
use App\Entities\ClientHistory;
use App\Entities\Client;
use App\Models\ClientHistoryModel;
use App\Models\ClientModel;
use App\Models\TransportModel;

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

class HistoryClientsController extends Controller
{

    public function __construct (
        private Form $form,
        private Captcha $captcha,
        private ClientHistory $clientHistory,
        private Client $client,
        private ClientHistoryModel $clientHistoryModel,
        private ClientModel $clientModel,
        private TransportModel $transportModel
    ){}

    /**
     * Affiche l'historique clients
     * @param string [$token] Clé de sécurité
     */
    public function index($token): void
    {
        // Si l'admin est connecté et que les tokens GET et SESSION correspondent
        if (isset($_SESSION['username_admin']) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {

            // $model = new ClientHistoryModel();
            // Effectue une lecture de la table
            $listClient = $this->clientHistoryModel->findAll();
            $this->render('history/clients', ['list' => $listClient]);

        } else {
            header('location:/public/');
            exit();
        }
    }


    /**
     * Mets à jour un enregistrement client
     * 
     * @param int [$id] d el'enregistrement sélectionné à modifier
     * @param string [$token] Token de sécurité
     */
    public function updateClient($id, $token): void
    {
        $error  ='';
        // Rédcupère la valeur de la langue sélectionné
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si les données POST sont valides
        if ($this->form->validatePost($_POST, ['name', 'surname', 'email', 'tel'])) {
            // Si les token POST et SESSION correspondent
            if (isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {

                // Si le numero de tel n'est pas un nombre, ou n'a pas la bonne longeur ni le bon format
                if (!preg_match("#^(\+33|0)[67][0-9]{8}$#", $_POST['tel'])) {
                    $error = $language->get('errorPhone');
                    // OU si l'email n'est pas valide
                } elseif (!(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))) {
                    $error = $language->get('errorEmail');
                } 
                // Instance d'objet
                // $clientHistory = new ClientHistory();
                // Hydrate l'entité
                $this->clientHistory->setName(htmlspecialchars(trim($_POST['name']), ENT_QUOTES));
                $this->clientHistory->setSurname(htmlspecialchars(trim($_POST['surname']), ENT_QUOTES));
                $this->clientHistory->setEmail(htmlspecialchars(trim($_POST['email']), ENT_QUOTES));
                $this->clientHistory->setTel(htmlspecialchars(trim($_POST['tel']), ENT_QUOTES));

                // Hydrate l'entité client
                // $client = new Client();
                $this->client->setName(htmlspecialchars(trim($_POST['name']), ENT_QUOTES));
                $this->client->setSurname(htmlspecialchars(trim($_POST['surname']), ENT_QUOTES));
                $this->client->setEmail(htmlspecialchars(trim($_POST['email']), ENT_QUOTES));
                $this->client->setPhone(htmlspecialchars(trim($_POST['tel']), ENT_QUOTES));

                // Instance de Re-captcha pour la vérification de spams
                // $captcha = new Captcha();
                // si la clé en post de vérifiaction du captcha est déclarée
                // récupère le retour booléen de la méthode verify
                if (isset($_POST['recaptcha_response']))
                    $isCaptchaValid = $this->captcha->verify($_POST['recaptcha_response']);
                // Si le re-captcha renvoi true
                if ( $isCaptchaValid == true ) {
                    // Instance du modèle et mise à jour des données
                    // $clientModel = new ClientHistoryModel();
                    $this->clientHistoryModel->update($id, $this->clientHistory);

                    // $modelClient = new ClientModel();
                    // Récupère l'enregistrement client à mettre à jour  correspondant à l'email
                    $clientReservation = $this->clientModel->find(htmlspecialchars($_POST['email'], ENT_QUOTES));
                    // S'il existe, mise à jour.
                    if($clientReservation)
                        $this->clientModel->update($clientReservation->email, $this->client);

                    // Redirige vers la liste de l'historique
                    header('location:/public/historyClients/index/' . trim($_SESSION['token']));
                    exit();
                } else {
                    $error = $language->get('errorCaptcha');
                }
            } else {
                $error = !empty($error) ? $error : $language->get('unknownUser');
            }
        } else {
            $error = !empty($_POST) ? $language->get('errorForm') : "";
        }
        $client =[];
        // Si les token GET et SESSION correspondent
        if (isset($_SESSION['username_admin']) && isset($_GET['token']) && isset($_GET['id']) && $_GET['token'] == $_SESSION['token']) {
            // Récupère l'enregistrement correspondant à l'id passé en GET
            // $model = new ClientHistoryModel();
            $clientHistory = $this->clientHistoryModel->find($id);
            
             // Si l'email est présent et dans la table et donc des réservations,
            // On interdit la modification de l'email, 
            // sinon l'email est autorisée
            $this->clientModel = new ClientModel();
            $client = $this->clientModel->find($clientHistory->email);

            $att = ""; $colorDisabled = "text-secondary";
            if ($client == true) {
                $att = "readonly"; $colorDisabled = "text-danger";
            }
            // Création du formulaire de mise à jour
            // $form = new Form();

            $this->form->startForm('#', 'POST', ['id'=> 'myForm', 'class' => ' col-12 col-md-4 col-lg-4 mx-auto  pt-3 pb-3 mt-5', 'novalidate' =>'']);
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('name', 'Nom: * ');
            $this->form->addInput('text', 'name', ['id' => 'nom', 'class'=> 'form-control  bg-transparent text-secondary border border-secondary', 'value' => $clientHistory->name, 'required' => '']);
            $this->form->endDiv();
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('surname', 'Prénom: * ');
            $this->form->addInput('text', 'surname', ['id' => 'prenom', 'class'=> 'form-control bg-transparent text-secondary border border-secondary', 'value' => $clientHistory->surname, 'required' => '']);
            $this->form->endDiv();
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('email', 'Email: *');
            $this->form->addInput('email', 'email', ['id' => 'mail', 'class'=> 'form-control bg-transparent '. $colorDisabled . ' border border-secondary', 'value' => $clientHistory->email, $att =>'' , 'required' => '']);
            $this->form->endDiv();
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('tel', 'Tel: * ');
            $this->form->addInput('tel', 'tel', ['id' => 'phone', 'class'=> 'form-control  bg-transparent text-secondary border border-secondary', 'value' => $clientHistory->tel, 'minlength' => '10', 'maxlength' => '10', 'required' => '']);
            $this->form->endDiv();
            $this->form->startDiv('form-group text-center mt-3');
            $this->form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']) : null]);
            $this->form->addInput('hidden', 'recaptcha_response', ['id' => 'recaptchaResponse']);       
            $this->form->addInput('submit', 'update',['class'=>'btnAdmin btn btn-dark text-danger', 'value' => 'Mettre à jour']);
            $this->form->endDiv();
            $this->form->endForm();

            // renvoi vers la vue correspodante
            $this->render('history/updateClient', ['updateForm' => $this->form->getFormElements(), 'error' => $error]); 
         // Sinon redirige vers l'historique
        } else {
            header('location:/public/');
            exit();
        }  
    }


    /**
     * Supprime les données toutes les données utilisateurs de l'enregistrements sélectionné dans l'historique
     * Assure la suppression dans toutes les tables de la présence de l'utilisateur
     * 
     * @param int [$id] Id de l'enregistrement sélectionné
     * @param string [token] Token de sécurité
     */
    public function deleteClient($id, $token): void
    {
        $error = '';
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si 'Oui' est déclaré en post et les token GET et SESSION correspondent
        if (isset($_POST['Oui']) && isset($_GET['token']) && isset($_GET['id']) && $_GET['token'] == $_SESSION['token']) {
            
            // Récupère l'enregstrement correspondnat à l'ID
            // $modelClientHisto = new ClientHistoryModel();
            $clientHistory = $this->clientHistoryModel->find($id);

            // Récupère l'enregistrement de la table client par email
            // $modelClient = new ClientModel();
            $clientModel = $this->clientModel->find($clientHistory->email);
            
            // Effectue une jointure des tables de réservation
            // puis boucle sur chaque itération afin de les supprimer en passant l'id correspondant
            if($clientModel != null) {
                // $transportModel = new TransportModel();
                $reservation = $this->transportModel->join($clientModel->idClient);
                foreach($reservation as $transport) {
    
                    $this->transportModel->delete($transport->idTransport);
                }
                // Supprime l'enregistrement utilisateur concerné
                $this->clientModel->delete($clientModel->idClient);
            }
          
            // Supprimme l'enregistrement correspondant à la clé passé en paramètre
            $this->clientHistoryModel->delete($id);
            // Redirige vers la liste des clients
            header('location:/public/historyClients/index/' . trim($_SESSION['token']));
            exit();
            // Si 'Non est déclaré en POST, redirige vers la laiste des clients
        } else if (isset($_POST['Non'])  && isset($_GET['token']) && isset($_GET['id']) && $_GET['token'] == $_SESSION['token']) {

            header('location:/public/historyClients/index/' . trim($_SESSION['token']));
            exit();
        } else {
            $error = $language->get('unknownUser');
        }

        // $form = new Form();
        $this->form->startForm('#', 'POST', ['id'=> 'myForm', 'class' => ' col-12 col-md-4 col-lg-4 mx-auto mt-5', ]);
        $this->form->startDiv('d-flex flex-lg-row flex-column justify-content-center gap-2 gap-lg-5');
        $this->form->addInput('submit', 'Oui', ['class' => 'btnConfirm btn btn-dark text-danger', 'value' => 'Oui']);
        $this->form->addInput('submit', 'Non', ['class' => 'btnConfirm btn btn-dark text-danger', 'value' => 'Non']);
        $this->form->endDiv();
        $this->form->endForm();

        $this->render('history/deleteClient', ['form' => $this->form->getFormElements(), 'error' => $error]);
    }
}