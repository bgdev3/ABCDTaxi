<?php
namespace App\Controllers;

use DateTime;
use App\Services\Form;
use App\Services\Mailer;
use App\Services\Language;
use App\Services\CheckDays;
use App\Models\ClientModel;
use App\Models\TransportModel;
use App\Entities\Transport;
use IntlDateFormatter;

session_start();

class ReservationsController extends Controller
{

    public function __construct (
        private Form $form,
        private ClientModel $clientModel,
        private TransportModel $transportModel,
        private Mailer $mailer,
        private CheckDays $checkDays, 
        private Transport $transport
    ){}    

    /**
     * Permet l'affichage de la liste des réservations utilisateur
     * 
     * @var array [$list] Récupère tous les enregistrements correspondant à l'utilisateur
     * @var object [$transport] modèle de l'entité transport
     * @var int [$id] id de l'utilsateur
     */
    public function index(): void
    {   
        $list = [];
        global $info;
        // Récupère la langue par défault
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si username est déclaré
        if (isset($_SESSION['username'])) {
            // Instance de TransportModel, on assigne l'id utilisateur
            // $transport = new TransportModel;
            $id = $_SESSION['id_user'];
            $list = $this->transportModel->join($id);
            // Si l'enregistrement de la table transport est nulle
            if (empty($list)) {
                // On stocke un message
                $info = $language->get('NoReservations');
                // Instance de UserModel
                // $model = new ClientModel();
                // On supprime l'utilisateur de la table client et on vide et détruit les sessions
                $this->clientModel->delete($id);
                session_unset();
                session_destroy();
            } 
            // Récupération de l'email pour l'envoi du mail 
            foreach($list as $item){
                $_SESSION['email'] = $item->email;
            }
        }
        // Renvoit vers la vue correspondante
        $this->render('user/reservationList', ['list' => $list, 'info' => $info]);
    }


    /**
     * Permet la mise à jour d'un enregistrement de la table transport
     * 
     * Récupère les données en post valide, hydrate la table correspondante
     * et appelle le modèle corrspondant afin d'effectuer la mise à jour
     * Sinon, affiche simplement le formulaire de mise à jour
     * 
     * @param int [$id] récupère l'id du transport correspodant
     * @var string [$error] Récupère le message d'erreur
     * @var string [$now] récupère la date du jour
     * @var object [$formUpdate] Stoke le formulaire de mise à jour
     * @var object [$formNewReservation] Stocke le formulaire d'une nouvelle réservation
     * @var object [$transport] Récupère l'insatnce de l'entité
     * @var object [$model] Modèle de l'entité
     */
    public function updateTransport($token, $id): void
    {
        // Variables globales
        global $message;
        $error = '';
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si les champs du formulaire de mise à jour sont valide
        if ($this->form->validatePost($_POST, ['date', 'time', 'nbPerson'])) {

            // On teste ici le token afin d'éviter une faille CSRF
            // ici on teste le token à l'arrivee sur la page en GET et lors de la soumission du formulaire
            if (isset($_SESSION['token']) && $_GET['token'] == $_SESSION['token'] && isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {

                // Si le nombre de passagers est supèrieur à 4
                if (intval($_POST['nbPerson']) > 4) {
                    // Affiche un message correspodant
                    $error = $language->get('errorPassengers');
                }
                // Récupère le booléen à savoir s'il s'agit d'un jour férié ou un dimanche
                $validDay = $this->checkUpdateDays($_POST['date']);

                // Si ce n'est pas le cas, la mise à jour est effectué
                if ($validDay != true) {

                    // Récupère l'heure au quart d'heure supérieur
                    $time = $this->roundTime(htmlspecialchars($_POST['time'], ENT_QUOTES));
                    // On récupère la date du jour dans le format de la bdd
                    $now = date('Y/m/d');
                    // Insatnce de Transport
                    // $transport = new Transport();
                    // Hydrate l'entité avec htmlspecialchars afin d'éviter la faille XSS
                    $this->transport->setDateReservation($now);
                    $this->transport->setDateTransport(htmlspecialchars($_POST['date'], ENT_QUOTES));
                    $this->transport->setDeparture_time($time);
                    $this->transport->setNbPassengers(htmlspecialchars($_POST['nbPerson']), ENT_QUOTES);
        
                    // $sendMail = new Mailer;
                    // Créer une variable de test pour l'action utilisateur
                    // afin de connaitre quel content envoyer
                    $action = 'update';
                    $message = $this->mailer->sendUserMail($_SESSION['email'], $action, $this->transport);
                
                    if (empty($message)) {
                        // On instancie le modèle et appelle la méthode update
                        // afin de mettre à jour l'enregistrement correspodant avec en argument l'id de transport et l'entité transport
                        // $model = new TransportModel();
                        $this->transportModel->update($id, $this->transport);
                        // Si tout se passe bien, on redirige vers la liste des réservations
                    header('location:/public/reservations');
                    exit();
                    } else {
                        // Sinon assigne le retour d'errreur d'envoi afin d'afficher l'erreur
                        $error = $message;                
                    }
                // Sinon l'erreur correspondante est asignée
                } else {
                    $error = $language->get('errorEasterDay');
                }
            // Sinon on affiche l'erreur de session
            } else {
                $error =  $language->get('unknownUser');
            }
        // Ou si l'action d'envoi d'une nouvelle réservation est effective
        } elseif (isset($_POST['new'])) {

            // Si le token en POST correspond avec celui de la session afin de s'assurer du bon utilisateur
            if (isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {
                 // Appelle de deleteTransport afin de supprimer la réservation concernée
                // afin de la remplacer par une nouvelle avec un nouveau devis
                $this->deleteTransport(null, $id, $_POST['new']);
            } else {
                $error = $language->get('unknownUser');
            }
        // Sinon si certain champs ne sont pas vides, on affiche l'erreur 
        } else {
            $error = !empty($_POST) ?  $language->get('errorForm') : "";
        }

        // Instance de TransportModel
        // $model = new TransportModel;
       
        // Si l'Id est bien déclaré
        if(isset($id))
            // Récupère le retour de l'enregistrement correspodant grâce à sa méthode find
            $transport = $this->transportModel->find($id);
        else
        // SInon on redirige vers laliste des réservations
            header('location:/public/reservations');

        // On instancie Form afin de crée le formulaire de mise à jour
        // $formUpdate = new Form();
        // Ici on va remplir les champs avec les données utilsateur de l'enregistrement récupéré
        $this->form->startForm('#', 'POST', ['id'=> 'myForm']);
        $this->form->addLabel('date', $language->get('dateTransport') . ': ');
        $this->form->addInput('date', 'date', ['id' => 'date', 'class'=> 'formInput', 'value' => $transport->date_transport, 'required' => '']);
        $this->form->addLabel('time',  $language->get('hourUpdate') . ': ');
        $this->form->addInput('time', 'time', ['id' => 'heure', 'class'=> 'formInput', 'value' => $transport->departureTime, 'min'=>'08:00', 'max'=>'20:00', 'required' => '']);
        $this->form->addLabel('nbPerson',  $language->get('numberPerson') .' : *');
        $this->form->addInput('number', 'nbPerson', ['id' => 'nbPerson', 'class'=> 'formInput','min' => '1', 'max' => '4', 'value' => $transport->nbPassengers, 'required' => '']);
        $this->form->addLabel('depart',  $language->get('departurePlaceUpdate') . ': ');
        $this->form->addInput('text', 'destination', ['id' => 'depart', 'class'=> 'formInput', 'value' => $transport->departurePlace, 'readonly' => 'readonly']);
        $this->form->addLabel('destination',  $language->get('destinationUpdate') . ':');
        $this->form->addInput('text', 'destination', ['id' => 'destination', 'class'=> 'formInput', 'value' =>$transport->destination, 'readonly' => 'readonly']);
        $this->form->addLabel('roundTrip',  $language->get('roundTripUpdate') . ':');
        $this->form->addInput('text', 'roundTrip', ['id' => 'aller_retour', 'class'=> 'formInput', 'value' =>$transport->roundTrip, 'readonly' => 'readonly']);   
        $this->form->addLabel('price',  $language->get('priceUpdate') . ':');
        $this->form->addInput('text', 'price', ['id' => 'aller_retour', 'class'=> 'formInput', 'value' =>$transport->price . '&euro;', 'readonly' => 'readonly']);
        $this->form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']): null]);
        $this->form->addInput('submit', 'update',['class'=>'btnForm',' value' =>  $language->get('btnUpdate1')]);
        $this->form->endForm();

        // Instance d'un deuxième formulaire afin de passer un post pour valider la nouvelle réservation
        $formNewReservation = new Form();
        $formNewReservation->startForm('#', 'POST', ['id' => 'myForm']);
        $formNewReservation->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']): null]);
        $formNewReservation->addInput('submit', 'new',['class'=>'btnFormNew',' value' =>  $language->get('btnUpdate2')]);
        $formNewReservation->endForm();
        // On redirige vers la vue correspondante
        $this->render('user/updateTransport', ['transport' => $transport, 'updateForm' =>  $this->form->getFormElements(),
                        'newReservation' => $formNewReservation -> getformElements(), 'error' => $error]);
    }


    /**
     *Permet la suppression d'un transport
     *selon les différentes valeur des données recu en post
     
     *@param int [$id] Récupère l'id de transport correspondant
     *@param array [$post] Param facultatif 
     *@var string [$error] Récupère le message d'erreur
     *@var object [$transport] Récupère l'insatnce de l'entité
     */
    public function deleteTransport($token, $id, $post = null): void
    {
        $error = '';
        
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si true est déclaré ainsi que l'id et que les tokens de sécurité correspondante afin de s'assurer du bon utilisateur
        if (isset($_POST['true']) && isset($_GET['id']) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {
            
            // Instance de TransportModel et de Mailer
            // $transportModel = new TransportModel;
            // $sendMail = new Mailer;
            // Créer une variable de test pour l'action utilisateur
            // afin de connaitre quel content envoyer
            $action = 'delete';
            $message = $this->mailer->sendUserMail($_SESSION['email'], $action);
            // Suppression par sa méthode delete prenant l'id de transport
            // correspondant en argument
            $this->transportModel->delete($id);
            // redirige vers la liste des réservations
            header('location:/public/reservations');
            exit();

        // Sinon si c'est False qui est déclaré en POST et que les tokens de sécurité correspondent
        } elseif (isset($_POST['false']) && isset($_GET['id']) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {

            // On revient vers la liste des réservations sans suppression
            header('location:/public/reservations');
            exit();

        // Sinon si new est déclaré on supprime le transport correspondant 
        // on vide et détruit les sessions utilisateur 
        // et on redirige vers le parcours de réservation
        }  elseif (isset($_POST['new'])) {
            // Instance de TransportModel
            // $transportModel = new TransportModel;
            // Appel de la méthode delete
            $this->transportModel->delete($id);
            // On vide et detruits les sessions utilisateurs
            unset($_SESSION['username']);
            unset($_SESSION['idClient']);
            // session_destroy();
            // Redirige vers les réservations
            header('location:/public/date/index/' . $_SESSION['token']);
            exit();
        // Sinon on affiche un message d'erreur
        } else {
            $error = !empty($_POST) ? $language->get('unknownUser') : "";
        }
       
        //   Instance de Form afin de créer le formulaire
        // $form = new Form();
        $this->form->startForm('', 'POST', ['id'=>'', 'class'=>'confirmDelete']);
        $this->form->addInput('submit','true',['class'=>' btnConfirm btnFormDelete', 'value' =>  $language->get('confirmYes')]);
        $this->form->addInput('submit','false',['class'=>'btnConfirm btnFormDelete', 'value' =>  $language->get('confirmNo')]);
        $this->form->endForm();
        // Redirige vers la vue correspodante
        $this->render('user/deleteTransport',['form'=>$this->form->getFormElements(),'error' => $error]);
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


    /**
     * Récupère la date modifié par l'utilisateur
     * la formatte en timestamp et chaine de caractère afin de la passé
     * à la méthode de l'objet CheckDays pour vérifer si c'est un jours férié ou un dimanche
     * 
     * @param string [$day] Date à formater 
     */
    private function checkUpdateDays($day)
    {
        // Scinde la date en argument puis la formate en timestamp avec mktime
        $date = explode('-', $day);
        $timestamp = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
    
        // Formate la date complète en chaine de caractère
        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
        $formatter->setPattern('EEEE dd MMMM YYYY');

        $now = new DateTime($day);
        $day = ucfirst($formatter->format($now));

        // Instance de CheckDays afin d'appeler la méthode easterDays pour vérifier si la date correspond à un jour férié ou un dimanche
        return  $this->checkDays->easterDays($timestamp, $day);
    }
}