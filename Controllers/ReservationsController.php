<?php
namespace App\Controllers;

use DateTime;
use App\Core\Form;
use App\Core\Mailer;
use App\Core\Language;
use App\Core\CheckDays;
use App\Models\ClientModel;
use App\Models\TransportModel;
use App\Entities\Transport;
use IntlDateFormatter;

session_start();

class ReservationsController extends Controller
{

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
            $transport = new TransportModel;
            $id = $_SESSION['id_user'];
            $list = $transport->join($id);
            // Si l'enregistrement de la table transport est nulle
            if (empty($list)) {
                // On stocke un message
                $info = $language->get('NoReservations');
                // Instance de UserModel
                $model = new ClientModel();
                // On supprime l'utilisateur de la table client et on vide et détruit les sessions
                $model->delete($id);
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
        global $error, $message;
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si les champs du formulaire de mise à jour sont valide
        if (Form::validatePost($_POST, ['date', 'time'])) {

            // On teste ici le token afin d'éviter une faille CSRF
            // ici on teste le token à l'arrivee sur la page en GET et lors de la soumission du formulaire
            if (isset($_SESSION['token']) && $_GET['token'] == $_SESSION['token'] && isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {

                // Récupère le booléen à savoir s'il s'agit d'un jour férié ou un dimanche
                $validDay = $this->checkUpdateDays($_POST['date']);

                // Si ce n'est pas le cas, la mise à jour est effectué
                if ($validDay != true) {

                    // Récupère l'heure au quart d'heure supérieur
                    $time = $this->roundTime(htmlspecialchars($_POST['time'], ENT_QUOTES));
                    // On récupère la date du jour dans le format de la bdd
                    $now = date('Y/m/d');
                    // Insatnce de Transport
                    $transport = new Transport();
                    // Hydrate l'entité avec htmlspecialchars afin d'éviter la faille XSS
                    $transport->setDateReservation($now);
                    $transport->setDateTransport(htmlspecialchars($_POST['date'], ENT_QUOTES));
                    $transport->setDeparture_time($time);
        
                    $sendMail = new Mailer;
                    // Créer une variable de test pour l'action utilisateur
                    // afin de connaitre quel content envoyer
                    $action = 'update';
                    $message = $sendMail->sendUserMail($_SESSION['email'], $action, $transport);
                
                    if (empty($message)) {
                        // On instancie le modèle et appelle la méthode update
                        // afin de mettre à jour l'enregistrement correspodant avec en argument l'id de transport et l'entité transport
                        $model = new TransportModel();
                        $model->update($id, $transport);
                        // Si tout se passe bien, on redirige vers la liste des réservations
                    header('location:index.php?controller=reservations&action=index');
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
        $model = new TransportModel;
        // On récupère le retour de l'enregistrement correspodant
        // grâce à sa méthode find
        $transport = $model->find($id);

        // On instancie Form afin de crée le formulaire de mise à jour
        $formUpdate = new Form();
        // Ici on va remplir les champs avec les données utilsateur de l'enregistrement récupéré
        $formUpdate->startForm('#', 'POST', ['id'=> 'myForm']);
        $formUpdate->addLabel('date', $language->get('dateTransport') . ': ');
        $formUpdate->addInput('date', 'date', ['id' => 'date', 'class'=> 'formInput', 'value' => $transport->date_transport, 'required' => '']);
        $formUpdate->addLabel('time',  $language->get('hourUpdate') . ': ');
        $formUpdate->addInput('time', 'time', ['id' => 'heure', 'class'=> 'formInput', 'value' => $transport->departureTime, 'min'=>'08:00', 'max'=>'20:00', 'required' => '']);
        $formUpdate->addLabel('depart',  $language->get('departurePlaceUpdate') . ': ');
        $formUpdate->addInput('text', 'destination', ['id' => 'depart', 'class'=> 'formInput', 'value' => $transport->departurePlace, 'readonly' => 'readonly']);
        $formUpdate->addLabel('destination',  $language->get('destinationUpdate') . ':');
        $formUpdate->addInput('text', 'destination', ['id' => 'destination', 'class'=> 'formInput', 'value' =>$transport->destination, 'readonly' => 'readonly']);
        $formUpdate->addLabel('roundTrip',  $language->get('roundTripUpdate') . ':');
        $formUpdate->addInput('text', 'roundTrip', ['id' => 'aller_retour', 'class'=> 'formInput', 'value' =>$transport->roundTrip, 'readonly' => 'readonly']);
        $formUpdate->addLabel('price',  $language->get('priceUpdate') . ':');
        $formUpdate->addInput('text', 'price', ['id' => 'aller_retour', 'class'=> 'formInput', 'value' =>$transport->price . '&euro;', 'readonly' => 'readonly']);
        $formUpdate->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']): null]);
        $formUpdate->addInput('submit', 'update',['class'=>'btnForm',' value' =>  $language->get('btnUpdate1')]);
        $formUpdate->endForm();

        // Instance d'un deuxième formulaire afin de passer un post pour valider la nouvelle réservation
        $formNewReservation = new Form();
        $formNewReservation->startForm('#', 'POST', ['id' => 'myForm']);
        $formNewReservation->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']): null]);
        $formNewReservation->addInput('submit', 'new',['class'=>'btnFormNew',' value' =>  $language->get('btnUpdate2')]);
        $formNewReservation->endForm();
        // On redirige vers la vue correspondante
        $this->render('user/updateTransport', ['transport' => $transport, 'updateForm' =>  $formUpdate->getFormElements(),
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
        global $error;
        
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si true est déclaré ainsi que l'id et que les tokens de sécurité correspondante afin de s'assurer du bon utilisateur
        if (isset($_POST['true']) && isset($_GET['id']) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {
            
            // Instance de TransportModel et de Mailer
            $transportModel = new TransportModel;
            $sendMail = new Mailer;
            // Créer une variable de test pour l'action utilisateur
            // afin de connaitre quel content envoyer
            $action = 'delete';
            $message = $sendMail->sendUserMail($_SESSION['email'], $action);
            // Suppression par sa méthode delete prenant l'id de transport
            // correspondant en argument
            $transportModel->delete($id);
            // redirige vers la liste des réservations
            header('location:index.php?controller=reservations&action=index');

        // Sinon si c'est False qui est déclaré en POST et que les tokens de sécurité correspondent
        } elseif (isset($_POST['false']) && isset($_GET['id']) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {

            // On revient vers la liste des réservations sans suppression
            header('location:index.php?controller=reservations&action=index');

        // Sinon si new est déclaré on supprime le transport correspondant 
        // on vide et détruit les sessions utilisateur 
        // et on redirige vers le parcours de réservation
        }  elseif (isset($_POST['new'])) {
            // Instance de TransportModel
            $transportModel = new TransportModel;
            // Appel de la méthode delete
            $transportModel->delete($id);
            // On vide et detruits les sessions utilisateurs
            unset($_SESSION['username']);
            unset($_SESSION['idClient']);
            // session_destroy();
            // Redirige vers les réservations
            header('location:index.php?controller=date&action=index&token=' . $_SESSION['token']);
        // Sinon on affiche un message d'erreur
        } else {
            $error = !empty($_POST) ? $language->get('unknownUser') : "";
        }
       
        //   Instance de Form afin de créer le formulaire
        $form = new Form();
        $form->startForm('', 'POST', ['id'=>'', 'class'=>'confirmDelete']);
        $form->addInput('submit','true',['class'=>'btnFormDelete', 'value' =>  $language->get('confirmYes')]);
        $form->addInput('submit','false',['class'=>'btnFormDelete', 'value' =>  $language->get('confirmNo')]);
        $form->endForm();
        // Redirige vers la vue correspodante
        $this->render('user/deleteTransport',['form'=>$form->getFormElements(),'error' => $error]);
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
    private function checkUpdateDays($day): bool
    {
        // Scinde la date en argument puis la formate en timestamp avec mktime
        $date = explode('-', $day);
        $timestamp = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
    
        // Formate la date complète en chaine de caractère
        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
        $formatter->setPattern('EEEE dd MMMM YYYY');

        $now = new DateTime($day);
        $day = ucfirst($formatter->format($now));

        // Instance
        $checkDays = new CheckDays();
        
        return  $checkDays->easterDays($timestamp, $day);
    }
}