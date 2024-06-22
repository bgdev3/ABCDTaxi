<?php
namespace App\Controllers;

use App\Core\Form;
use App\Core\Captcha;
use App\Core\Language;
use App\Entities\ClientHistory;
use App\Entities\Client;
use App\Models\ClientHistoryModel;
use App\Models\ClientModel;
use App\Models\TransportModel;

session_start();

class HistoryClientsController extends Controller
{

    /**
     * Affiche l'historique clients
     * 
     * @param string [$token] Clé de sécurité
     */
    function index($token): void
    {
        // Si l'admin est connecté et que les tokens GET et SESSION correspondent
        if (isset($_SESSION['username_admin']) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {

            $model = new ClientHistoryModel();
            // Effectue une lecture de la table
            $listClient = $model->findAll();
            $this->render('history/clients', ['list' => $listClient]);

        } else {
            header('location:index.php');
        }
    }


    /**
     * Mets à jour un enregistrement client
     * 
     * @param int [$id] d el'enregistrement sélectionné à modifier
     * @param string [$token] Token de sécurité
     */
    function updateClient($id, $token): void
    {
        global $error;
        // Rédcupère la valeur de la langue sélectionné
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si les données POST sont valides
        if (Form::validatePost($_POST, ['name', 'surname', 'email', 'tel'])) {
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
                $clientHistory = new ClientHistory();
                // Hydrate l'entité
                $clientHistory->setName(htmlspecialchars(trim($_POST['name']), ENT_QUOTES));
                $clientHistory->setSurname(htmlspecialchars(trim($_POST['surname']), ENT_QUOTES));
                $clientHistory->setEmail(htmlspecialchars(trim($_POST['email']), ENT_QUOTES));
                $clientHistory->setTel(htmlspecialchars(trim($_POST['tel']), ENT_QUOTES));

                // Hydrate l'entité client
                $client = new Client();
                $client->setName(htmlspecialchars(trim($_POST['name']), ENT_QUOTES));
                $client->setSurname(htmlspecialchars(trim($_POST['surname']), ENT_QUOTES));
                $client->setEmail(htmlspecialchars(trim($_POST['email']), ENT_QUOTES));
                $client->setPhone(htmlspecialchars(trim($_POST['tel']), ENT_QUOTES));

                // Instance de Re-captcha pour la vérification de spams
                $captcha = new Captcha();
                // si la clé en post de vérifiaction du captcha est déclarée
                // récupère le retour booléen de la méthode verify
                if (isset($_POST['recaptcha_response']))
                    $captcha = $captcha->verify($_POST['recaptcha_response']);
                // Si le re-captcha renvoi true
                if ( $captcha == true ) {
                    // Instance du modèle et mise à jour des données
                    $clientModel = new ClientHistoryModel();
                    $clientModel->update($id, $clientHistory);

                    $modelClient = new ClientModel();
                    // Récupère l'enregistrement client à mettre à jour  correspondant à l'email
                    $clientReservation = $modelClient->find(htmlspecialchars($_POST['email'], ENT_QUOTES));
                    // S'il existe, mise à jour.
                    if($clientReservation)
                        $modelClient->update($clientReservation->email, $client);

                    // Redirige vers la liste de l'historique
                    header('location:index.php?controller=historyClients&action=index&token=' . trim($_SESSION['token']));
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
            $model = new ClientHistoryModel();
            $clientHistory = $model->find($id);
            
             // Si l'email est présent et dans la table et donc des réservations,
            // On interdit la modification de l'email, 
            // sinon l'email est autorisée
            $modelClient = new ClientModel();
            $client = $modelClient->find($clientHistory->email);

            $att = ""; $colorDisabled = "text-secondary";
            if ($client == true) {
                $att = "readonly"; $colorDisabled = "text-danger";
            }
            // Création du formulaire de mise à jour
            $form = new Form();

            $form->startForm('#', 'POST', ['id'=> 'myForm', 'class' => ' col-12 col-md-4 col-lg-4 mx-auto  pt-3 pb-3 mt-5', 'novalidate' =>'']);
            $form->startDiv('form-group mb-3');
            $form->addLabel('name', 'Nom: * ');
            $form->addInput('text', 'name', ['id' => 'nom', 'class'=> 'form-control  bg-transparent text-secondary border border-secondary', 'value' => $clientHistory->name, 'required' => '']);
            $form->endDiv();
            $form->startDiv('form-group mb-3');
            $form->addLabel('surname', 'Prénom: * ');
            $form->addInput('text', 'surname', ['id' => 'prenom', 'class'=> 'form-control bg-transparent text-secondary border border-secondary', 'value' => $clientHistory->surname, 'required' => '']);
            $form->endDiv();
            $form->startDiv('form-group mb-3');
            $form->addLabel('email', 'Email: *');
            $form->addInput('email', 'email', ['id' => 'mail', 'class'=> 'form-control bg-transparent '. $colorDisabled . ' border border-secondary', 'value' => $clientHistory->email, $att =>'' , 'required' => '']);
            $form->endDiv();
            $form->startDiv('form-group mb-3');
            $form->addLabel('tel', 'Tel: * ');
            $form->addInput('tel', 'tel', ['id' => 'phone', 'class'=> 'form-control  bg-transparent text-secondary border border-secondary', 'value' => $clientHistory->tel, 'minlength' => '10', 'maxlength' => '10', 'required' => '']);
            $form->endDiv();
            $form->startDiv('form-group text-center mt-3');
            $form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']) : null]);
            $form->addInput('hidden', 'recaptcha_response', ['id' => 'recaptchaResponse']);       
            $form->addInput('submit', 'update',['class'=>'btnAdd btn btn-dark text-danger', 'value' => 'Mettre à jour']);
            $form->endDiv();
            $form->endForm();

            // renvoi vers la vue correspodante
            $this->render('history/updateClient', ['updateForm' => $form->getFormElements(), 'error' => $error]); 
         // Sinon redirige vers l'historique
        } else {
            header('location:index.php');
        }  
    }


    /**
     * Supprime les données toutes les données utilisateurs de l'enregistrements sélectionné dans l'historique
     * Assure la suppression dans toutes les tables de la présence de l'utilisateur
     * 
     * @param int [$id] Id de l'enregistrement sélectionné
     * @param string [token] Token de sécurité
     */
    function deleteClient($id, $token): void
    {
        global $error;
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si 'Oui' est déclaré en post et les token GET et SESSION correspondent
        if (isset($_POST['Oui']) && isset($_GET['token']) && isset($_GET['id']) && $_GET['token'] == $_SESSION['token']) {
            
            // Récupère l'enregstrement correspondnat à l'ID
            $modelClientHisto = new ClientHistoryModel();
            $clientHistory = $modelClientHisto->find($id);

            // Récupère l'enregistrement de la table client par email
            $modelClient = new ClientModel();
            $clientModel = $modelClient->find($clientHistory->email);
            
            // Effectue une jointure des tables de réservation
            // puis boucle sur chaque itération afin de les supprimer en passant l'id correspondant
            if($clientModel != null) {
                $transportModel = new TransportModel();
                $reservation = $transportModel->join($clientModel->idClient);
                foreach($reservation as $transport) {
    
                    $transportModel->delete($transport->idTransport);
                }
                // Supprime l'enregistrement utilisateur concerné
                $modelClient->delete($clientModel->idClient);
            }
          
            // Supprimme l'enregistrement correspondant à la clé passé en paramètre
            $modelClientHisto->delete($id);
            // Redirige vers la liste des clients
            header('location:index.php?controller=historyClients&action=index&token=' . trim($_SESSION['token']));
            // Si 'Non est déclaré en POST, redirige vers la laiste des clients
        } else if (isset($_POST['Non'])  && isset($_GET['token']) && isset($_GET['id']) && $_GET['token'] == $_SESSION['token']) {

            header('location:index.php?controller=historyClients&action=index&token=' . trim($_SESSION['token']));
        } else {
            $error = $language->get('unknownUser');
        }

        $form = new Form();
        $form->startForm('#', 'POST', ['id'=> 'myForm', 'class' => ' col-12 col-md-4 col-lg-4 mx-auto mt-5', ]);
        $form->startDiv('d-flex flex-lg-row flex-column justify-content-center gap-2 gap-lg-5');
        $form->addInput('submit', 'Oui', ['class' => 'btn btn-dark text-danger', 'value' => 'Oui']);
        $form->addInput('submit', 'Non', ['class' => 'btn btn-dark text-danger', 'value' => 'Non']);
        $form->endDiv();
        $form->endForm();

        $this->render('history/deleteClient', ['form' => $form->getFormElements(), 'error' => $error]);
    }
}