<?php

namespace App\Controllers;

use App\Services\Form;
use App\Services\Mailer;
use App\Services\Captcha;
use App\Services\Language;
use App\Services\GenerateId;
use App\Entities\Client;
use App\Entities\Transport;
use App\Models\ClientModel;
use App\Models\TransportModel;
use App\Models\TransportHistoryModel;

if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AdminReservationsController extends Controller
{
    
    public function __construct(
            private ClientModel $clientModel,
            private TransportModel $transportModel,     
            private TransportHistoryModel $transportHistoryModel,
            private Transport $transport,
            private GenerateId $passUser,
            private Client $user,
            private Mailer $mailer,
            private Captcha $captcha,
            private Form $form,
        ) {}
    /**
     * Afiiche la liste complète des réservations en cours
     * 
     * Teste la concordance des token, récupère tout les enregistrements
     * Puis Boucle sur chaque occurence afin de récupèrer l'idClient et d'effectuer une jointure
     * which is assigned in an array
     * 
     * @param string $token Clé de sécurité
     */
    public function index($token): void
    {
        // Attribut pour stocker lesjointures des tables
        $transportClient = [];
        // Si l'amdin ets déclaré et si les tokens correspondent
        if (isset($_SESSION['username_admin']) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {
            // Instance du modèle client afin d'effcetuer une lecture de table
            // $modelClient = new ClientModel();
            $listClient = $this->clientModel->findAll();

            // Instance du modèle transport afin d'effectuer des jointures
            // sur chaque enregistrement clients
            // $modelTransport = new TransportModel();
            // Récupère sur chaque enregistrement l'idClient afin d'effectuer une jointure dans la boucle
            foreach($listClient as $client) {

                $idClient =  $client->idClient;
                array_push($transportClient,  $this->transportModel->join($idClient));
            }

            $this->render('admin/reservations', ['reservations' => $transportClient]);
        // Sinon si l'admin n'est pas connecté, on redirige vers l'accueil
        } else {
            header('location:/public/');
        }
    }


    /**
     * Permet l'ajout d'une réservation complète administrateur
     * 
     * Teste les entrées en POST, vérifie la bonne validité du captcha,
     * puis hydrate les entitées correspondante et alimente les tables.
     * 
     * @param string $token Jeton de sécurité en GET
     */
    public function addReservationsAdmin($token, ?int $id = null): void 
    {

        $error = '';
        $passUser = '';
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);

        // Si les données d'entrées sont valides
        if ($this->form->validatePost($_POST, ['name', 'surname', 'email', 'tel', 'nbPerson', 'date_transport', 'time', 'startPlace', 'destination', 'roundTrip'])) {
                 // Si le numero de tel n'est pas un nombre, ou n'a pas la bonne longeur ni le bon format
            if (!preg_match("#^(\+33|0)[67][0-9]{8}$#", $_POST['tel'])) {
                $error =$language->get('errorPhone');
                // OU si l'email n'est pas valide
            } elseif (!(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))) {
                $error = $language->get('errorEmail');;
            } 

            // S'il n'y a pas d'erreur et si les tokens correspondent afin d'eviter une faille CSRF
            // lors de la soumission du formulaire
            if (empty($error) && isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {

                // Instance de Re-captcha pour la vérification de spams
                    // $captcha = new Captcha();
                    // si la clé en post de vérifiaction du captcha est déclarée
                    // récupère le retour booléen de la méthode verify
                    // if (isset($_POST['recaptcha_response']))
                    //     $isCaptchaValid = $this->captcha->verify($_POST['recaptcha_response']);
                    // Si le re-captcha renvoi true
                    // if ( $isCaptchaValid == true ) {
                        // On instancie userModel afin de vérifier que l'utilisateur n'est pas déja dans la base
                        // $modelUser = new ClientModel();
                        $testingUser = $this->clientModel->find(htmlspecialchars($_POST['email'], ENT_QUOTES));
                      
                        // Si l'utilisateur n'existe pas dans la base
                        if ($testingUser == null) {
                            // Instance de GenerateId afin de créer un numéroClient unique
                            
                            $passUser = $this->passUser->generate();         
                            // Le hash
                            $passUser_hash = password_hash($passUser, PASSWORD_DEFAULT);
                            // Instanciation l'entité user
                            // $user = new Client();
                            // On hydrate l'entité user en appliquant htmlspecilacahrs
                            // afin d'éviter la faille XSS
                            $this->user->setNb_client($passUser_hash);
                            $this->user->setName(htmlspecialchars(trim($_POST['name']), ENT_QUOTES));
                            $this->user->setSurname(htmlspecialchars(trim($_POST['surname']), ENT_QUOTES));
                            $this->user->setEmail(htmlspecialchars(trim($_POST['email']), ENT_QUOTES));
                            $this->user->setPhone(htmlspecialchars(trim($_POST['tel']), ENT_QUOTES));
                            // On alimente la table
                            $this->clientModel->create($this->user);
                        } 

                        // Récupère l'id de l'enregistrement nouvellement créé afin d'alimenter la clé étrangère
                        $userData = $this->clientModel->find(htmlspecialchars($_POST['email'], ENT_QUOTES));
                        $idUser = $userData->idClient;
                       
                        // Récupère l'heure au quart d'heure supérieur
                        $time = $this->roundTime(htmlspecialchars($_POST['time'], ENT_QUOTES));

                        // Instance de l'entité Transport
                        // et l'hydrate avec les donnbées de réservations stockées en sessions
                        // $transport = new Transport();
                        $this->transport->setDateTransport(htmlspecialchars(trim($_POST['date_transport']), ENT_QUOTES));
                        $this->transport->setNbPassengers(htmlspecialchars(trim($_POST['nbPerson']), ENT_QUOTES));
                        $this->transport->setDeparture_time($time);
                        $this->transport->setDeparture_place(htmlspecialchars(trim($_POST['startPlace']), ENT_QUOTES));
                        $this->transport->setDestination(htmlspecialchars(trim($_POST['destination']), ENT_QUOTES));
                        $this->transport->setRoundTrip(htmlspecialchars(trim($_POST['roundTrip']), ENT_QUOTES));
                        $this->transport->setEstimated_wait(0);
                        $this->transport->setPrice(0); 
                        $this->transport->setId_user($idUser);

                        // Instanciation de Mailer pour l'envoi des mail de confirmation
                        // $mailer = new Mailer();
                        // récupère le mail utilisateur
                        $emailUser = htmlspecialchars(trim($_POST['email']), ENT_QUOTES);
                        // Créer une variable de test pour l'action utilisateur
                        // afin de connaitre quel content envoyer
                        $action = 'confirm';
                        // S'il l'utilisateur n'existe pas, passe en argument le numéro client
                        // Sinon on applique la méthode sans $passUser en argument
                        $message = $testingUser == null ? $this->mailer->sendUserMail( $emailUser, $action, $this->transport, $passUser) : $this->mailer->sendUserMail($emailUser, $action, $this->transport);
                        
                        // Instance de TransportModel
                        // $transportModel = new TransportModel();
                        // Si le message est vide et donc que l'envoi de mail s'est bien déroulé
                        if (empty($message)) {
                            $error = $message;
                            // On alimente la table transport
                            $this->transportModel->create($this->transport);
                            // Redirige vers la liste des réservations en cours
                            header('location:/public/adminReservations/index/' . trim($_SESSION['token']));
                        // Sinon si un problème d'envoi de mail survient 
                        } else {
                            // Verifie si des transports relatif à l'utilisateur enregistré existent
                            $list = $this->transportModel->join($idUser); /* $_SESSION['idUser'] */
                            // Si la jointure retourne un resultat nul
                            if (empty($list)) {
                                // On supprime l'utilisateur de la table User
                                $this->clientModel->delete($idUser);
                            }
                        }
                    // } else {
                    //     $error = $language->get('errorCaptcha');
                    // }
                // Si le token ne correspond pas, affiche l'erreur
            } else {
                $error = !empty($error) ? $error : $language->get('unknownUser');
            }
        } else {
            // S'il y a une erreur lors de l'envoi en post, on l'affiche
            $error = !empty($_POST) ? $language->get('errorForm') : "";
        }

        // Initailise les variables afin de stocker des valeur par default pour la valeur des input du formulaire
        $name =''; $surname =''; $email = ''; $tel =''; $passengers = 1; $dateTransport =''; $departureTime =''; $departurePlace = ''; $destination = ''; $oneWay =''; $roundTrip ='';
       
        // Si l'id est déclaré et donc si cest une nouvelle réservation rempli automatiquement par les données de l'historique de transport
        // et si les token get et session correspondent
        if (isset($id) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {
    
            // instance du modèle 
            // $modelTransport = new TransportHistoryModel();
            // Jointure par l'idTransport_histo
            $reservation = $this->transportHistoryModel->joinByOne($id);

            foreach($reservation as $val) {
    
                $name = $val->name;
                $surname = $val->surname;
                $email = $val->email;
                $tel = $val->tel;
                $passengers = $val->nbPassengers;
                $dateTransport = $val->date_transport;
                $departureTime = $val->departureTime;
                $departurePlace = $val->departurePlace;
                $destination = $val->destination;

                if ($val->roundTrip == 'Oui') {
                    $oneWay =  '';
                    $roundTrip =  'checked';
                } else {
                    $oneWay =  'checked';
                    $roundTrip =  '';
                }
            }
        }

        // $form = new Form();
        // FieldSet client
        $this->form->startForm('#', 'POST', ['id'=> 'myForm', 'class' => ' col-12 col-md-8 col-lg-10 mx-auto  mt-2 pb-3 ', 'novalidate' =>'']);
        $this->form->startDiv('d-flex flex-lg-row flex-column gap-5');
        $this->form->startFieldSet('', 'form-group  p-2 w-100');
        $this->form->legend('Client', 'text-center mb-3  border border-light fs-5 fst-italic text-danger rounded col-4 col-md-4 col-lg-4');
        $this->form->startDiv('form-group mb-3');
        $this->form->addLabel('name', 'Nom: * ');
        $this->form->addInput('text', 'name', ['id' => 'name', 'class'=> 'form-control  bg-transparent text-secondary border border-secondary','value' =>  $name, 'required' => '']);
        $this->form->endDiv();
        $this->form->startDiv('form-group mb-3');
        $this->form->addLabel('surname', 'Prénom: * ');
        $this->form->addInput('text', 'surname', ['id' => 'surname', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' =>  $surname, 'required' => '']);
        $this->form->endDiv();
        $this->form->startDiv('form-group mb-3');
        $this->form->addLabel('email', 'Email: *');
        $this->form->addInput('email', 'email', ['id' => 'email', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' =>  $email, 'required' => '']);
        $this->form->endDiv();
        $this->form->startDiv('form-group mb-3');
        $this->form->addLabel('tel', 'Tel: * ');
        $this->form->addInput('tel', 'tel', ['id' => 'tel', 'class'=> 'form-control  bg-transparent text-light border border-secondary', 'minlength' => '10', 'maxlength' => '10', 'value' =>  $tel, 'required' => '']);
        $this->form->endDiv();
        $this->form->startDiv('form-group mb-3');
        $this->form->addLabel('nbPerson', 'Nb passagers: *');
        $this->form->addInput('number', 'nbPerson', ['id' => 'nbPerson', 'class'=> 'form-control  bg-transparent text-light border border-secondary','min' => '1', 'value' => $passengers, 'required' => '']);
        $this->form->endDiv();
        $this->form->endFieldset();
        // FieldSetTransport
        $this->form->startFieldSet('', 'form-group p-2 w-100' );
        $this->form->legend('Réservation', 'text-center mb-3 border border-light  fs-5 fst-italic text-danger rounded col-4 col-md-4 col-lg-4');
        $this->form->startDiv('form-group mb-3');
        $this->form->addLabel('date_transport', 'Date de transport: ');
        $this->form->addInput('date', 'date_transport', ['id' => 'date_transport', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' =>  $dateTransport, 'required' => '']);
        $this->form->endDiv();
        $this->form->startDiv('form-group mb-3');
        $this->form->addLabel('time', 'Heure de départ: ');
        $this->form->addInput('time', 'time', ['id' => 'time', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' =>  $departureTime, 'required' => '']);
        $this->form->endDiv();
        $this->form->startDiv('form-group mb-3');
        $this->form->addLabel('startPlace', 'Lieu de départ: ');
        $this->form->addInput('text', 'startPlace', ['id' => 'startPlace', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' =>  $departurePlace, 'required' => '']);
        $this->form->endDiv();
        $this->form->startDiv('form-group mb-3');
        $this->form->addLabel('destination', 'Lieu de destination:');
        $this->form->addInput('text', 'destination', ['id' => 'destination', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' =>  $destination, 'required' => '']);
        $this->form->endDiv();
        $this->form->startDiv('form-check form-check-inline mb-3');
        $this->form->addInput('radio', 'roundTrip',['class' =>'form-check-input', 'value' => 'Oui', 'id' => 'roundTripYes']);
        $this->form->addLabel('roundTrip', 'Aller-retour', ['class' => 'form-check-label']);
        $this->form->endDiv();
        $this->form->startDiv('form-check form-check-inline mb-3');
        $this->form->addInput('radio', 'roundTrip',['class' =>'form-check-input', 'value' => 'Non', 'id' => 'roundTripNo']);
        $this->form->addLabel('roundTrip', 'Aller-simple', ['class' => 'form-check-label']);           
        $this->form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']): null]);
        $this->form->endFieldSet();
        $this->form->endDiv();
        $this->form->startDiv('form-group text-center mt-3');
        $this->form->addInput('submit', 'update',['class'=>'btnAdmin btn btn-dark text-danger',' value' => 'Ajouter']);
        $this->form->endDiv();
        $this->form->addInput('hidden', 'recaptcha_response', ['id' => 'recaptchaResponse']);
        $this->form->endForm();
    
        // Si les tokens corresponndent, envoi des données relatives utilisateur
        if (isset($_SESSION['username_admin']) && isset($_GET['token']) && $_GET['token'] ==  $_SESSION['token']){
            $this->render('admin/addReservation', ['addForm' => $this->form->getFormElements(), 'error' => $error]);
            // Sinon envoi d'un message de non authentification
        } else {
            $errorAuth = $language->get('unknownUser');
            $this->render('admin/addReservation', ['errorAuth' => $errorAuth]);
        }    
    }


    /**
     * Permet la mise à jour d'un enregistrement administrateur
     * 
     * @param string $token Jeton de sécurité
     * @param int $id Identifiant de l'enregistrement
     */
    public function updateReservationAdmin($token, $id): void
    {
        global $email;
        $error='';
        // Récupère la valeur de la langue sélectionnées
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);

        // Si les valeur en POST sont bien déclarées
        if (Form::validatePost($_POST, ['date_transport', 'time', 'departurePlace', 'destination', 'nbPerson', 'roundTrip'])) {
            // Si les tokens de sécurité correspondent
            if (isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {

                // Récupère l'heure au quart d'heure supérieur
                $time = $this->roundTime(htmlspecialchars($_POST['time'], ENT_QUOTES));
                // Instance de l'entité Transport
                // $reservation = new Transport();
                // Hydrate l'entité
                $this->transport->setDateTransport(htmlspecialchars(trim($_POST['date_transport']), ENT_QUOTES));
               
                $this->transport->setDeparture_time(trim($time));
                $this->transport->setDeparture_place(htmlspecialchars(trim($_POST['departurePlace']), ENT_QUOTES));
                $this->transport->setDestination(htmlspecialchars(trim($_POST['destination']), ENT_QUOTES));
                $this->transport->setNbPassengers(htmlspecialchars(trim($_POST['nbPerson']), ENT_QUOTES));
                $this->transport->setRoundTrip(htmlspecialchars(trim($_POST['roundTrip']), ENT_QUOTES));
                $this->transport->setPrice(0);

                //Effectue une jointure relative au transport à modifier
                // afin de récupérer l'email tuilisateur associé et envoyé 
                // la notification de modification de réservation.
                // $sendMail = new Mailer;
                // $modelTransport = new TransportModel();
                $transport = $this->transportModel->find($id);
                $list = $this->transportModel->join($transport->idClient);

                // Boucle sur la jointure afin de récupérer l'email
                foreach($list as $val){
                    $email = $val->email;  
                }

                // Envoi de l'email
                $message = $this->mailer->sendUserMail($email, 'update', $this->transport);

                // S'il n'y a pas d'erreur d'envoi, mise à jour sinon on récupère le message d'erreur.
                if(empty($message)) {
                    $this->transportModel->updateAdmin($id, $this->transport);
                    // on redirige vers la liste des reservations en cours
                    header('location:/public/adminReservations/index/' . trim($_SESSION['token']));
                } else {
                    $error = $language->get('sendEMail');
                }    
                
            // Sinon une message d'erreur d'authentification est indiqué
            } else {
                $error = $language->get('unknownUser');
            }
            
        } else {
            // S'il y a une erreur lors de l'envoi en post, on l'affiche
            $error = !empty($_POST) ? $language->get('errorForm') : "";
        }
       
        // Si le admin est connecté et si les token GET et SESSION correspondent
        if (isset($_SESSION['username_admin']) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {
            // Récupère l'enregistrement correspondant à l'Id passé en GET
            // Afin de pré-remplir le formulaire de modification
            // $modelTransport = new TransportModel();
            $transport = $this->transportModel->find($id);
    
            // var_dump($transport);
            // $form = new Form();

            if($transport->roundTrip == 'Oui') {
                $allerSimpe =  '';
                $allerRetour =  'checked';
            } else {
                $allerSimpe =  'checked';
                $allerRetour =  '';
            }
   
            $this->form->startForm('#', 'POST', ['id'=> 'myForm', 'class' => ' col-12 col-md-6 col-lg-6 mx-auto  pt-3 pb-3 mt-5', 'novalidate' =>'']);
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('date_transport', 'Date de transport: ');
            $this->form->addInput('date', 'date_transport', ['id' => 'date_transport', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' => $transport->date_transport, 'required' => '']);
            $this->form->endDiv();
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('time', 'Heure de départ: ');
            $this->form->addInput('time', 'time', ['id' => 'heure', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' => $transport->departureTime, 'required' => '']);
            $this->form->endDiv();
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('depart', 'Lieu de départ: ');
            $this->form->addInput('text', 'departurePlace', ['id' => 'depart', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' => $transport->departurePlace, 'required' => '']);
            $this->form->endDiv();
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('destination', 'Lieu de destination:');
            $this->form->addInput('text', 'destination', ['id' => 'destination', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' => $transport->destination, 'required' => '']);
            $this->form->endDiv();
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('nbPerson', 'Nb passagers: *');
            $this->form->addInput('number', 'nbPerson', ['id' => 'nbPerson', 'class'=> 'form-control  bg-transparent text-light border border-secondary','min' => '1', 'value' => $transport->nbPassengers, 'required' => '']);
            $this->form->endDiv();
            $this->form->startDiv('form-check form-check-inline mb-3');
            $this->form->addInput('radio', 'roundTrip',['class' =>'form-check-input', 'value' => 'Oui', $allerRetour => '']);
            $this->form->addLabel('roundTrip', 'Aller-retour', ['class' => 'form-check-label']);
            $this->form->endDiv();
            $this->form->startDiv('form-check form-check-inline mb-3');
            $this->form->addInput('radio', 'roundTrip',['class' =>'form-check-input', 'value' => 'Non', $allerSimpe=> '']);
            $this->form->addLabel('roundTrip', 'Aller-simple', ['class' => 'form-check-label']);
            $this->form->endDiv();
            $this->form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']): null]);
            $this->form->addInput('hidden', 'recaptcha_response', ['id' => 'recaptchaResponse']);       
            $this->form->startDiv('form-group text-center mt-3');
            $this->form->addInput('submit', 'update',['class'=>'btnAdmin btn btn-dark text-danger', 'value' => 'Mettre à jour']);
            $this->form->endDiv();
            $this->form->endForm();
           
            $this->render('admin/updateReservation', ['form' => $this->form->getFormElements(), 'error' => $error]);
           
        } else {
            header('location:/public/');
        }
    }


    /**
     * Permet la suppression d'une réservation administrateur
     * 
     * @param string $token Jeton de sécurité
     * @param int $id Identifiant de l'enregistrement
     */
    public function deleteReservationAdmin($token, $id): void
    {
        global $list, $email;
        // Si les tokens en GET et SESSION correspondent
        if (isset($_POST['Oui']) &&  isset($_GET['token']) && isset($_GET['id']) && $_GET['token'] == $_SESSION['token']) {

            // Récupère l'enregistrement du transport de l'id
            // puis récupère l'idClient correspondant
            // $modelTransport = new TransportModel();
            $transport = $this->transportModel->find($id);
            $idClient = $transport->idClient;
           
            // Vérifie s'il reste des réservations relative à l'utilisatuer
            // $sendMail = new Mailer();
            $list = $this->transportModel->join($idClient);

            // Boucle afin de récupérer l'email utilisateur
            foreach($list as $val){
                $email = $val->email;
            }

            // Envoi de l'email
            $this->mailer->sendUserMail($email, 'delete');
            // Suppression de l'enregistrement transport
            $this->transportModel->delete($id);

            // Si aucune réservation n'est présente, l'utilisateur est supprimé
            if(empty($this->transportModel->join($idClient))) {
                // $model = new ClientModel();
                // On supprime l'utilisateur de la table client et on vide et détruit les sessions
                $this->clientModel->delete($idClient);
            } 
            
            // Redirige vers l aliste des réservations en cours
            header('location:/public/adminReservations/index/' . trim($_SESSION['token']));

        // Si 'Non' est déclaré en POST et si les tokens correspondent
        } else if (isset($_POST['Non']) &&  isset($_GET['token']) && isset($_GET['id']) && $_GET['token'] == $_SESSION['token']) {
            header('location:/public/adminReservations/index/' . trim($_SESSION['token']));
        }

        
        $this->form->startForm('#', 'POST', ['id'=> 'myForm', 'class' => ' col-12 col-md-4 col-lg-4 mx-auto mt-5' ]);
        $this->form->startDiv('d-flex flex-lg-row flex-column justify-content-center gap-2 gap-lg-5');
        $this->form->addInput('submit', 'Oui', ['class' => 'btnConfirm btn btn-dark text-danger', 'value' => 'Oui']);
        $this->form->addInput('submit', 'Non', ['class' => 'btnConfirm btn btn-dark text-danger', 'value' => 'Non']);
        $this->form->endDiv();
        $this->form->endForm();

        $this->render('admin/deleteReservation', ['form' => $this->form->getFormElements()]);
    }


    /**
     * Récupère l'heure de rendez-vous modifié par l'utilisateur 
     * et l'arrondis au quart d'heure supérieur en cas d'horaire non valide
     * Les horaires stockées sont par tranches de 15 min
     * 
     * @param string $time Heure de rendez-vous modifié
     * @return string $time Retourne l'heure fomraté
     */
    private function roundTime($time): string
    {
        $min = array('00', '15', '30', '45');
        // Sinde l'heure récupérée
        $updateTime = explode(':', $time);
        // Si l'heure sélectionnée est au quart d'heure près, on n'applique pas de formatage
        if(in_array($updateTime[1], $min)) {
            $updateTime = $time;
        } else {
            // Convertit en timestamp
            $updateTime = mktime($updateTime[0], $updateTime[1], 0, 0, 0, 0);
            // Ajoute 15 min en secondes
            $updateTime -= $updateTime % 900;
            // Convertit en heure
            $updateTime = date('H:i', $updateTime + 900);
        }

        return $updateTime;
    }
} 
