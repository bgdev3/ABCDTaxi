<?php
namespace App\Controllers;

use IntlDateFormatter;
use App\Core\Form;
use App\Core\Mailer;
use App\Core\Captcha;
use App\Core\GenerateId;
use App\Core\Language;
use App\Entities\Client;
use App\Entities\Transport;
use App\Models\ClientModel;
use App\Models\TransportModel;

session_start();

class RegistrationController extends Controller 
{

    /**
     * Teste et sécurise les données entrées en POST, hydrate les entitées correspondane
     * et crée le formualire de contact par défault afin d'enregistrer la réservation
     * 
     * @var [$error] Récupère les message d'erreur
     * @var [$modelUser] Instance de UserModel
     * @var [$dataUser] Enregistrement de la BDD récupèrer
     * @var [$passUser] numero client générer aléatoirement
     * @var [$passUser_hash] Numéro client hashé
     * @var [$captcha] Instance de la class Captcha
     * @var [$mailer] Instance de Mailer
     * @var [$emailUser] récupère l'email en post de l'utilisateur
     * @var [$message] récupère le message d'erreur du mail
     * @var [$form] stocke le formulaire
     */

    public function index(): void
    {
        // Variables globales
        global $error, $message, $passUser, $idUser;
        // Récupère la la langue sélectionné
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si les validatePost renvoi TRUE, c'est à dire si les champs ne sont pas vide
        if (Form::validatePost ($_POST, ['name', 'surname', 'email', 'tel'])) {

            // Si le numero de tel n'est pas un nombre, ou n'a pas la bonne longeur ni le bon format
            if (!preg_match("#^(\+33|0)[67][0-9]{8}$#", $_POST['tel'])) {
                $error = $language->get('errorPhone');
                // OU si l'email n'est pas valide
            } elseif (!(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))) {
                $error = $language->get('errorEmail');
            } 

            // S'il n'y a pas d'erreur et si les tokens correspondent afin d'eviter une faille CSRF
            // lors de la soumission du formulaire
            if (empty($error) && isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {

                // Si les sessions de données de réservations sont bien déclarées
                if ($this->validateSession()) {

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
                             // Insatnce de GenerateId afin de créer un numéroClient unique
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
                        // Récupère l'id de l'enregistrement nouvellement créé
                        // que l'on stocke dans une session afin de le tester pour l'affichage de confirmation dans la vue correspondante
                        $userData = $modelUser->find(htmlspecialchars($_POST['email'], ENT_QUOTES));
                        $idUser = $userData->idClient;
                        // Génère un nouvel PHPSSID afin de sécuriser les données utilisateur
                        session_regenerate_id();
                        $_SESSION['idUser'] = $idUser;

                        // Selon la langue déclaré, on assigne le timzeone correspondant
                        $locale = isset($_SESSION['lang']) && $_SESSION['lang'] == 'en' ? 'en_US' : 'fr_FR';
                        // Paramètrage du format de la date 
                        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE,
                         'Europe/Paris');
                        // Formatte en timestamp la chaine récupèrer
                        // puis en date numérique afin de la stocker en base en ajoutant 24h afin de réduire l'écart
                        $date = datefmt_parse($formatter, $_SESSION['day']) + 86400;
                        $date = date('Y/m/d', $date);
                       
                        // Instance de l'entité Transport
                        // et l'hydrate avec les donnbées de réservations stockées en sessions
                        $transport = new Transport();
                        $transport->setDateTransport(trim($date));
                        $transport->setDeparture_time(trim($_SESSION['time']));
                        $transport->setDeparture_place(trim($_SESSION['departurePlace']));
                        $transport->setDestination(trim($_SESSION['destination']));
                        $transport->setRoundTrip($_SESSION['roundTrip']);
                        $transport->setEstimated_wait(trim($_SESSION['wait']));
                        $transport->setPrice($_SESSION['price']); 
                        $transport->setId_user($idUser);

                        // Instanciation de Mailer pour l'envoi des mail de confirmation
                        $mailer = new Mailer();
                        // récupère le mail utilisateur
                        $emailUser = htmlspecialchars($_POST['email'], ENT_QUOTES);
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
                            // On alimente la table transport
                            $transportModel->create($transport);

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
                // S'il y a un problème de sessions de réservations non déclarées ou vide, on affiche l'erreur
                } else {
                    $error = $language->get('errorRegistration');
                }
                // Si le token ne correspond pas, affiche l'erreur
            } else {
                $error = !empty($error) ? $error : $language->get('unknownUser');;
            }
        } else {
            // S'il y a une erreur lors de l'envoi en post, on l'affiche
            $error = !empty($_POST) ?  $language->get('errorForm') : "";
        }

        // Instancie la classe Form afin de construire si aucune données sont envoyés
        $form = new Form();

        $form->startForm('#', 'POST', ['id'=>'myForm', 'novalidate' =>'']);
        $form->addLabel('name', $language->get('name') .' : * ');
        $form->addInput('text', 'name', ['id' => 'nom', 'placeholder' =>  $language->get('name'), 'class'=> 'formInput','required' => '']);
        $form->addLabel('surname',  $language->get('surname') .': * ');
        $form->addInput('text', 'surname', ['id' => 'prenom', 'class'=> 'formInput', 'placeholder' =>  $language->get('surname'), 'required' => '']);
        $form->addLabel('email',  $language->get('email') .' : *');
        $form->addInput('email', 'email', ['id' => 'mail', 'class'=> 'formInput','placeholder' =>  $language->get('email'), 'required' => '']);
        $form->addLabel('tel',  $language->get('phone') .': * ');
        $form->addInput('tel', 'tel', ['id' => 'phone', 'class'=> 'formInput', 'placeholder' =>  $language->get('phone'),
         'minlength' => '10', 'maxlength' => '10', 'required' => '']);
        $form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']) : null]);
        $form->addInput('checkbox', 'agree',['id' => 'agree', 'required' => '']);
        $form->addLabel('agree',  $language->get('agree'));
        $form->addInput('submit', 'btnRdv',['id' => 'btnRdv', 'class'=>'btnForm', 'value' => $language->get('btnRegistration')]);
        $form->addInput('hidden', 'recaptcha_response', ['id' => 'recaptchaResponse']);
        $form->endForm();

        // Redirige vers la vue correspodante en afficgant le formulaire si il n 'y a pas d'utilisateur
        if (!isset($_SESSION['idUser'])) {
            $this->render('reservation/registration', ['addForm'=> $form->getFormElements(), 'error' =>  $error]);
        // Sinon, affiche les message de confirmation de réservation
        // On libère et détruit les sessions
        } else {
            $this->render('reservation/registration', ['message'=> $message]);
            session_unset();
            session_destroy();
        }
    }


    /**
     * Fonction qui récupère les champs de destinations 
     * renvoyés par l'api Places de google
     * et les stockes en sessions afin de les manipuler
     */
    public function getPlaces(): void
    {
        $content = trim(file_get_contents("php://input"));
        $data= json_decode($content, true);
        // Applique htmlspecialchars sur les champs de destination avant d'alimenter la table
        $_SESSION['departurePlace'] = htmlspecialchars($data['depart'], ENT_QUOTES);
        $_SESSION['destination'] = htmlspecialchars($data['destination'], ENT_QUOTES);
    }

    /**
     * Ici on teste si les sessions sont bien déclarées
     * A ce stade, seules les sessions de données d'enregistrement sont déclarées
     * puis détruite apres l'hydratation de la table
     * 
     * @return bool
     */
    private function validateSession(): bool 
    {
        // Boucle sur chaque SESSION déclarées et vérifie qu'elles soint bien 
        // déclarées et non vide avant de les alimenter en base.
        foreach($_SESSION as $item){
            if (isset($item) && !empty($item)) {
                return true;
            }
        }
        return false;
    }  
}