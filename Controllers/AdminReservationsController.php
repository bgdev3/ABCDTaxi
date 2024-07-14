<?php

namespace App\Controllers;

use App\Core\Form;
use App\Core\Mailer;
use App\Core\Captcha;
use App\Core\Language;
use App\Core\GenerateId;
use App\Entities\Client;
use App\Entities\Transport;
use App\Models\ClientModel;
use App\Models\TransportModel;
use App\Models\TransportHistoryModel;

session_start();

class AdminReservationsController extends Controller
{

    /**
     * Afiiche la liste complète des réservations en cours
     * 
     * Teste la concordance des token, récupère tout les enregistrements
     * Puis Boucle sur chaque occurence afin de récupèrer l'idClient et d'effectuer une jointure
     * qui est assignée dans un array
     * 
     * @param string [$token] Clé de sécurité
     */
    public function index($token): void
    {
        // Attribut pour stocker lesjointures des tables
        $transportClient = [];
        // Si l'amdin ets déclaré et si les tokens correspondent
        if (isset($_SESSION['username_admin']) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {
            // Instance du modèle client afin d'effcetuer une lecture de table
            $modelClient = new ClientModel();
            $listClient = $modelClient->findAll();

            // Instance du modèle transport afin d'effectuer des jointures
            // sur chaque enregistrement clients
            $modelTransport = new TransportModel();
            // Récupère sur chaque enregistrement l'idClient afin d'effectuer une jointure dans la boucle
            foreach($listClient as $client) {

                $idClient =  $client->idClient;
                array_push($transportClient,  $modelTransport->join($idClient));
            }

            $this->render('admin/reservations', ['reservations' => $transportClient]);
        // Sinon si l'admin n'est pas connecté, on redirige vers l'accueil
        } else {
            header('location:index.php');
        }
    }


    /**
     * Permet l'ajout d'une réservation complète administrateur
     * 
     * Teste les entrées en POST, vérifie la bonne validité du captcha,
     * puis hydrate les entitées correspondante et alimente les tables.
     * 
     * @param string [$token] Jeton de sécurité en GET
     */
    public function addReservationsAdmin($token, $id = null): void 
    {

        global $error;
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);

        // Si les données d'entrées sont valides
        if (Form::validatePost($_POST, ['name', 'surname', 'email', 'tel', 'nbPerson', 'date_transport', 'time', 'startPlace', 'destination', 'roundTrip'])) {
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
                    $captcha = new Captcha();
                    // si la clé en post de vérifiaction du captcha est déclarée
                    // récupère le retour booléen de la méthode verify
                    if (isset($_POST['recaptcha_response']))
                        $captcha = $captcha->verify($_POST['recaptcha_response']);
                    // Si le re-captcha renvoi true
                    if ( $captcha == true ) {
                        // On instancie userModel afin de vérifier que l'utilisateur n'est pas déja dans la base
                        $modelUser = new ClientModel();
                        $testingUser = $modelUser->find(htmlspecialchars($_POST['email'], ENT_QUOTES));
                        
                        // Si l'utilisateur n'existe pas dans la base
                        if ($testingUser == null) {
                            // Instance de GenerateId afin de créer un numéroClient unique
                            $passUser = new generateId();
                            $passUser = $passUser->generate();         
                            // Le hash
                            $passUser_hash = password_hash($passUser, PASSWORD_DEFAULT);
                            // Instanciation l'entité user
                            $user = new Client();
                            // On hydrate l'entité user en appliquant htmlspecilacahrs
                            // afin d'éviter la faille XSS
                            $user->setNb_client($passUser_hash);
                            $user->setName(htmlspecialchars(trim($_POST['name']), ENT_QUOTES));
                            $user->setSurname(htmlspecialchars(trim($_POST['surname']), ENT_QUOTES));
                            $user->setEmail(htmlspecialchars(trim($_POST['email']), ENT_QUOTES));
                            $user->setPhone(htmlspecialchars(trim($_POST['tel']), ENT_QUOTES));
                            // On alimente la table
                            $modelUser->create($user);
                        } 

                        // Récupère l'id de l'enregistrement nouvellement créé afin d'alimenter la clé étrangère
                        $userData = $modelUser->find(htmlspecialchars($_POST['email'], ENT_QUOTES));
                        $idUser = $userData->idClient;
                       
                        // Récupère l'heure au quart d'heure supérieur
                        $time = $this->roundTime(htmlspecialchars($_POST['time'], ENT_QUOTES));

                        // Instance de l'entité Transport
                        // et l'hydrate avec les donnbées de réservations stockées en sessions
                        $transport = new Transport();
                        $transport->setDateTransport(htmlspecialchars(trim($_POST['date_transport']), ENT_QUOTES));
                        $transport->setNbPassengers(htmlspecialchars(trim($_POST['nbPerson']), ENT_QUOTES));
                        $transport->setDeparture_time($time);
                        $transport->setDeparture_place(htmlspecialchars(trim($_POST['startPlace']), ENT_QUOTES));
                        $transport->setDestination(htmlspecialchars(trim($_POST['destination']), ENT_QUOTES));
                        $transport->setRoundTrip(htmlspecialchars(trim($_POST['roundTrip']), ENT_QUOTES));
                        $transport->setEstimated_wait(0);
                        $transport->setPrice(0); 
                        $transport->setId_user($idUser);

                        // Instanciation de Mailer pour l'envoi des mail de confirmation
                        $mailer = new Mailer();
                        // récupère le mail utilisateur
                        $emailUser = htmlspecialchars(trim($_POST['email']), ENT_QUOTES);
                        // Créer une variable de test pour l'action utilisateur
                        // afin de connaitre quel content envoyer
                        $action = 'confirm';
                        // S'il l'utilisateur n'existe pas, passe en argument le numéro client
                        // Sinon on applique la méthode sans $passUser en argument
                        $message = $testingUser == null ? $mailer->sendUserMail( $emailUser, $action, $transport, $passUser) : $mailer->sendUserMail($emailUser, $action, $transport);
                        // Instance de TransportModel
                        $transportModel = new TransportModel();
                        // Si le message est vide et donc que l'envoi de mail s'est bien déroulé
                        if (empty($message)) {
                            $error = $message;
                            // On alimente la table transport
                            $transportModel->create($transport);
                            // Redirige vers la liste des réservations en cours
                            header('location:index.php?controller=adminReservations&action=index&token=' . trim($_SESSION['token']));
                        // Sinon si un problème d'envoi de mail survient 
                        } else {
                            // Verifie si des transports relatif à l'utilisateur enregistré existent
                            $list = $transportModel->join($_SESSION['idUser']);
                            // Si la jointure retourne un resultat nul
                            if (empty($list)) {
                                // On supprime l'utilisateur de la table User
                                $modelUser->delete($_SESSION['idUser']);
                            }
                        }
                    } else {
                        $error = $language->get('errorCaptcha');
                    }
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
            $modelTransport = new TransportHistoryModel();
            // Jointure par l'idTransport_histo
            $reservation = $modelTransport->joinByOne($id);

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

        $form = new Form();
        // FieldSet client
        $form->startForm('#', 'POST', ['id'=> 'myForm', 'class' => ' col-12 col-md-8 col-lg-10 mx-auto  mt-2 pb-3 ', 'novalidate' =>'']);
        $form->startDiv('d-flex flex-lg-row flex-column gap-5');
        $form->startFieldSet('', 'form-group  p-2 w-100');
        $form->legend('Client', 'text-center mb-3  border border-light fs-5 fst-italic text-danger rounded col-4 col-md-4 col-lg-4');
        $form->startDiv('form-group mb-3');
        $form->addLabel('name', 'Nom: * ');
        $form->addInput('text', 'name', ['id' => 'name', 'class'=> 'form-control  bg-transparent text-secondary border border-secondary','value' =>  $name, 'required' => '']);
        $form->endDiv();
        $form->startDiv('form-group mb-3');
        $form->addLabel('surname', 'Prénom: * ');
        $form->addInput('text', 'surname', ['id' => 'surname', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' =>  $surname, 'required' => '']);
        $form->endDiv();
        $form->startDiv('form-group mb-3');
        $form->addLabel('email', 'Email: *');
        $form->addInput('email', 'email', ['id' => 'email', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' =>  $email, 'required' => '']);
        $form->endDiv();
        $form->startDiv('form-group mb-3');
        $form->addLabel('tel', 'Tel: * ');
        $form->addInput('tel', 'tel', ['id' => 'tel', 'class'=> 'form-control  bg-transparent text-light border border-secondary', 'minlength' => '10', 'maxlength' => '10', 'value' =>  $tel, 'required' => '']);
        $form->endDiv();
        $form->startDiv('form-group mb-3');
        $form->addLabel('nbPerson', 'Nb passagers: *');
        $form->addInput('number', 'nbPerson', ['id' => 'nbPerson', 'class'=> 'form-control  bg-transparent text-light border border-secondary','min' => '1', 'value' => $passengers, 'required' => '']);
        $form->endDiv();
        $form->endFieldset();
        // FieldSetTransport
        $form->startFieldSet('', 'form-group p-2 w-100' );
        $form->legend('Réservation', 'text-center mb-3 border border-light  fs-5 fst-italic text-danger rounded col-4 col-md-4 col-lg-4');
        $form->startDiv('form-group mb-3');
        $form->addLabel('date_transport', 'Date de transport: ');
        $form->addInput('date', 'date_transport', ['id' => 'date_transport', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' =>  $dateTransport, 'required' => '']);
        $form->endDiv();
        $form->startDiv('form-group mb-3');
        $form->addLabel('time', 'Heure de départ: ');
        $form->addInput('time', 'time', ['id' => 'time', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' =>  $departureTime, 'required' => '']);
        $form->endDiv();
        $form->startDiv('form-group mb-3');
        $form->addLabel('startPlace', 'Lieu de départ: ');
        $form->addInput('text', 'startPlace', ['id' => 'startPlace', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' =>  $departurePlace, 'required' => '']);
        $form->endDiv();
        $form->startDiv('form-group mb-3');
        $form->addLabel('destination', 'Lieu de destination:');
        $form->addInput('text', 'destination', ['id' => 'destination', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' =>  $destination, 'required' => '']);
        $form->endDiv();
        $form->startDiv('form-check form-check-inline mb-3');
        $form->addInput('radio', 'roundTrip',['class' =>'form-check-input', 'value' => 'Oui', $roundTrip => '']);
        $form->addLabel('roundTrip', 'Aller-retour', ['class' => 'form-check-label']);
        $form->endDiv();
        $form->startDiv('form-check form-check-inline mb-3');
        $form->addInput('radio', 'roundTrip',['class' =>'form-check-input', 'value' => 'Non', $oneWay=> '']);
        $form->addLabel('roundTrip', 'Aller-simple', ['class' => 'form-check-label']);           
        $form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']): null]);
        $form->endFieldSet();
        $form->endDiv();
        $form->startDiv('form-group text-center mt-3');
        $form->addInput('submit', 'update',['class'=>'btnAdmin btn btn-dark text-danger',' value' => 'Ajouter']);
        $form->endDiv();
        $form->addInput('hidden', 'recaptcha_response', ['id' => 'recaptchaResponse']);
        $form->endForm();
    
        // Si les tokens corresponndent, envoi des données relatives utilisateur
        if (isset($_SESSION['username_admin']) && isset($_GET['token']) && $_GET['token'] ==  $_SESSION['token']){
            $this->render('admin/addReservation', ['addForm' => $form->getFormElements(), 'error' => $error]);
            // Sinon envoi d'un message de non authentification
        } else {
            $errorAuth = $language->get('unknownUser');
            $this->render('admin/addReservation', ['errorAuth' => $errorAuth]);
        }    
    }


    /**
     * Permet la mise à jour d'un enregistrement administrateur
     * 
     * @param string [$token] Jeton de sécurité
     * @param int [$d] Identifiant de l'enregistrement
     */
    public function updateReservationAdmin($token, $id): void
    {
        global $error, $email;
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
                $reservation = new Transport();
                // Hydrate l'entité
                $reservation->setDateTransport(htmlspecialchars(trim($_POST['date_transport']), ENT_QUOTES));
               
                $reservation->setDeparture_time(trim($time));
                $reservation->setDeparture_place(htmlspecialchars(trim($_POST['departurePlace']), ENT_QUOTES));
                $reservation->setDestination(htmlspecialchars(trim($_POST['destination']), ENT_QUOTES));
                $reservation->setNbPassengers(htmlspecialchars(trim($_POST['nbPerson']), ENT_QUOTES));
                $reservation->setRoundTrip(htmlspecialchars(trim($_POST['roundTrip']), ENT_QUOTES));
                $reservation->setPrice(0);

                //Effectue une jointure relative au transport à modifier
                // afin de récupérer l'email tuilisateur associé et envoyé 
                // la notification de modification de réservation.
                $sendMail = new Mailer;
                $modelTransport = new TransportModel();
                $transport = $modelTransport->find($id);
                $list = $modelTransport->join($transport->idClient);

                // Boucle sur la jointure afin de récupérer l'email
                foreach($list as $val){
                    $email = $val->email;  
                }

                // Envoi de l'email
                $message = $sendMail->sendUserMail($email, 'update', $reservation);

                // S'il n'y a pas d'erreur d'envoi, mise à jour sinon on récupère le message d'erreur.
                if(empty($message)) {
                    $modelTransport->updateAdmin($id, $reservation);
                    // on redirige vers la liste des reservations en cours
                    header('location:index.php?controller=adminReservations&action=index&token=' . trim($_SESSION['token']));
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
            $modelTransport = new TransportModel();
            $transport = $modelTransport->find($id);
    
            // var_dump($transport);
            $form = new Form();

            if($transport->roundTrip == 'Oui') {
                $allerSimpe =  '';
                $allerRetour =  'checked';
            } else {
                $allerSimpe =  'checked';
                $allerRetour =  '';
            }
   
            $form->startForm('#', 'POST', ['id'=> 'myForm', 'class' => ' col-12 col-md-6 col-lg-6 mx-auto  pt-3 pb-3 mt-5', 'novalidate' =>'']);
            $form->startDiv('form-group mb-3');
            $form->addLabel('date_transport', 'Date de transport: ');
            $form->addInput('date', 'date_transport', ['id' => 'date_transport', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' => $transport->date_transport, 'required' => '']);
            $form->endDiv();
            $form->startDiv('form-group mb-3');
            $form->addLabel('time', 'Heure de départ: ');
            $form->addInput('time', 'time', ['id' => 'heure', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' => $transport->departureTime, 'required' => '']);
            $form->endDiv();
            $form->startDiv('form-group mb-3');
            $form->addLabel('depart', 'Lieu de départ: ');
            $form->addInput('text', 'departurePlace', ['id' => 'depart', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' => $transport->departurePlace, 'required' => '']);
            $form->endDiv();
            $form->startDiv('form-group mb-3');
            $form->addLabel('destination', 'Lieu de destination:');
            $form->addInput('text', 'destination', ['id' => 'destination', 'class'=> 'form-control bg-transparent text-light border border-secondary', 'value' => $transport->destination, 'required' => '']);
            $form->endDiv();
            $form->startDiv('form-group mb-3');
            $form->addLabel('nbPerson', 'Nb passagers: *');
            $form->addInput('number', 'nbPerson', ['id' => 'nbPerson', 'class'=> 'form-control  bg-transparent text-light border border-secondary','min' => '1', 'value' => $transport->nbPassengers, 'required' => '']);
            $form->endDiv();
            $form->startDiv('form-check form-check-inline mb-3');
            $form->addInput('radio', 'roundTrip',['class' =>'form-check-input', 'value' => 'Oui', $allerRetour => '']);
            $form->addLabel('roundTrip', 'Aller-retour', ['class' => 'form-check-label']);
            $form->endDiv();
            $form->startDiv('form-check form-check-inline mb-3');
            $form->addInput('radio', 'roundTrip',['class' =>'form-check-input', 'value' => 'Non', $allerSimpe=> '']);
            $form->addLabel('roundTrip', 'Aller-simple', ['class' => 'form-check-label']);
            $form->endDiv();
            $form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']): null]);
            $form->addInput('hidden', 'recaptcha_response', ['id' => 'recaptchaResponse']);       
            $form->startDiv('form-group text-center mt-3');
            $form->addInput('submit', 'update',['class'=>'btnAdmin btn btn-dark text-danger', 'value' => 'Mettre à jour']);
            $form->endDiv();
            $form->endForm();
           
            $this->render('admin/updateReservation', ['form' => $form->getFormElements(), 'error' => $error]);
           
        } else {
            header('location:index.php');
        }
    }


    /**
     * Permet la suppression d'une réservation administrateur
     * 
     * @param string [$token] Jeton de sécurité
     * @param int [$d] Identifiant de l'enregistrement
     */
    public function deleteReservationAdmin($token, $id): void
    {
        global $list, $email;
        // Si les tokens en GET et SESSION correspondent
        if (isset($_POST['Oui']) &&  isset($_GET['token']) && isset($_GET['id']) && $_GET['token'] == $_SESSION['token']) {

            // Récupère l'enregistrement du transport de l'id
            // puis récupère l'idClient correspondant
            $modelTransport = new TransportModel();
            $transport = $modelTransport->find($id);
            $idClient = $transport->idClient;
           
            // Vérifie s'il reste des réservations relative à l'utilisatuer
            $sendMail = new Mailer();
            $list = $modelTransport->join($idClient);

            // Boucle afin de récupérer l'email utilisateur
            foreach($list as $val){
                $email = $val->email;
            }

            // Envoi de l'email
            $sendMail->sendUserMail($email, 'delete');
            // Suppression de l'enregistrement transport
            $modelTransport->delete($id);

            // Si aucune réservation n'est présente, l'utilisateur est supprimé
            if(empty($modelTransport->join($idClient))) {
                $model = new ClientModel();
                // On supprime l'utilisateur de la table client et on vide et détruit les sessions
                $model->delete($idClient);
            } 
            
            // Redirige vers l aliste des réservations en cours
            header('location:index.php?controller=adminReservations&action=index&token=' . trim($_SESSION['token']));

        // Si 'Non' est déclaré en POST et si les tokens correspondent
        } else if (isset($_POST['Non']) &&  isset($_GET['token']) && isset($_GET['id']) && $_GET['token'] == $_SESSION['token']) {
            header('location:index.php?controller=adminReservations&action=index&token=' . trim($_SESSION['token']));
        }

        $form = new Form();
        $form->startForm('#', 'POST', ['id'=> 'myForm', 'class' => ' col-12 col-md-4 col-lg-4 mx-auto mt-5' ]);
        $form->startDiv('d-flex flex-lg-row flex-column justify-content-center gap-2 gap-lg-5');
        $form->addInput('submit', 'Oui', ['class' => 'btnConfirm btn btn-dark text-danger', 'value' => 'Oui']);
        $form->addInput('submit', 'Non', ['class' => 'btnConfirm btn btn-dark text-danger', 'value' => 'Non']);
        $form->endDiv();
        $form->endForm();

        $this->render('admin/deleteReservation', ['form' => $form->getFormElements()]);
    }


    /**
     * Récupère l'heure de rendez-vous modifié par l'utilisateur 
     * et l'arrondis au quart d'heure supérieur en cas d'horaire non valide
     * Les horaires stockées sont par tranches de 15 min
     * 
     * @param string [$time] Heure de rendez-vous modifié
     * @return string [$time] Retourne l'heure fomraté
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
