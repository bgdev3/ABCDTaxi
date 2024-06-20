<?php 
namespace App\Controllers;

use DateTime;
use IntlDateFormatter;
use App\Core\Hours;
use App\Models\TransportModel;

session_start();

class DateController extends Controller
{

    /**
     * Méthode qui initialise par défault l'affichage à 10 jours
     * Instancie un objet DateTime, appel des méthodes privés 
     * puis renvois à la Vue afin d'afficher
     * 
     * @var [$now] Instance de DateTime
     * @var [$date] Stocke le retour de initDate
     * @var [$time] Stocke le retour de initTime
     */
    
    public function index($token): void
    {  
        // Crée une instance de DateTime
        $now = new DateTime();
        //Initialise l'affichage à 10 jours par défault
        $date = $this->initDate(10, $now);
        // Initilaise l'heure à 8h et une occurence tous les 16 1/4h
        $time = $this->iniTime(8, 16, $now);
        $this->dbCheckHours($token);
        // Transmets à la vue
        $this->render('reservation/index', ['dates' => $date, 'times' => $time]);
    }


    /**
     * Méthode qui initie l'affichage des dates journalières
     * 
     * @var [$date] Stocke un tableau
     * @var [formatter] Instance de IntlDateFormatter paramétré en local et stocke le pattern de la date à afficher
     * @var [$locale] Stoche la localisation par défault
     * @var [$pattern] Stocke le fomrat par défault
     * 
     * @param int [$nb] stocke le nombre recupérer en argument
     * @param object [$now] stocke l'objet DateTime 
     * 
     * @return array [$date] retourne le tableau de date
     */

    private function initDate(int $nb, object $now): array
    {
        $date = [];
        $locale = 'fr_FR';
        $pattern = 'EEEE dd MMMM YYYY';
        // Selon la langue sélectionné, applique les bonnes valeur
        if(isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') {
            $locale = 'en_US';
            $pattern = 'EEEE, MMMM dd YYYY';
        } 
        // Formatte la date en local (param: localité, format date, format heure) et détermine l'affichage
        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE);
        $formatter->setPattern($pattern);
        // Boucle sur le nbr de jours recu en argument et assigne
        // dans un tableau la date formatter à j+1  chaque itération
        for($i=0; $i < $nb; ++$i){
            array_push($date, ucfirst($formatter->format($now->modify('next day'))));
            } 
        // Retourne les données
        return $date;
    }


    /**
     * Méthode qui retourne un array d'heure au 1/4h prés
     * 
     * @var [$timeDate] Stocke les heures
     * 
     * @param object [$now] Instanciation de DateTime 
     * @param int [$hours] Heure par défault à afficher
     * @param int [$nb] Nb d'itération à effectuer
     * 
     * @return array [$timeDate] retourne un array des heures
     */

    private function iniTime($hours, $nb, $now): array
    {
        $timeDate = [];
        $minutes=00;
        // Paramètre l'heure de DateTime recu en argument
        $time = $now->setTime($hours, $minutes);

        // Boucle sur le nombre déterminant le nb d'horaire max à affiché
        // au  1/4 d'heure prés : Ajoute 15 min à chaque itération
        for($i=0; $i < $nb ; ++$i){
            $minutes+=15;
            
            if ($minutes == 0 || $minutes == 60) {
                ++$hours;
                $minutes = 0 . '0';
            }
        // Renvois le format d'heure 
            array_push($timeDate, $time->format($hours.':'.$minutes));
        }
        return $timeDate;
    }

    
    /**
     * Méthode qui initialise l'affichage à +$nb jours
     * et mets à jour le front via fetch
     * 
     * @var [$now] Instance de DateTime
     * @var [$nb] Récupère le nombre de jours jours à afficher jusqu'à 28 défini en js
     * @var [$date] Tableau associatif qui sstockes les données d'heures et de jours renvoyés en json
     */

    public function addDays(): void
    {  
        // Crée une instance de DateTime et formate en local
        $now = new DateTime();
        // Récupère et decode le json envoyé en Fetch
        $content = trim(file_get_contents("php://input"));
        $nb= json_decode($content, true);
        // $nb = json_decode($nbr);
        // Modifie le décalage journalier  avec la valeur
        // envoyé par Fetch
        $now -> modify('+' . $nb . 'days');
        // Ajoute 7 jours  passé en arguments
        // à la méthode initDate afin de stocker et de transmettre à la vue
        $date = array("date" => $this->initDate(7, $now), 
                      "time" => $this->iniTime(8, 16,$now));
        // Transmets les données en Json
        echo json_encode($date);
    }


    /**
     * Permet l'ajout des heures renvoyé en ajax
     * 
     * @var [$hours] Renvoi un encodage json au js afin de mettre à jour le front
     */
    
    public function addHours(): void
    {
        $now = new DateTime();
        // Initilaise iniTime à partir de 12H et à 32 occurence de 15min
        $hours = array("hours" => $this->iniTime(12, 32, $now), 'dbHours' => $_SESSION['dbHours']);
        echo json_encode($hours);
    }


    /**
     * Récupère les différents enregistrements des reservations 
     * afin de tester les disponibilités restantes pour les réservations
     * 
     * @param array [$token] token de sécurité  permettant de s'assurer du bon utilisateur
     */
    public function dbCheckHours($token)
    {
        // Si les tokens correspondent
        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {
            // Instance de transportModel afin de récupérer les enregistrements
            // stocké par la suite dans une session afin de l'utiliser utltérieurement.
            $model = new TransportModel();
            $listHours = $model->findAll();
            $_SESSION['dbHours'] = $listHours;
        } else {
            header('location:index.php');
        }
    }
}