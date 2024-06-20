<?php
namespace App\Controllers;

use IntlDateFormatter;
use App\Core\Form;
use App\Core\Language;
use App\Core\CheckDays;
use App\Models\PriceModel;

session_start();

class EstimateController extends Controller 
{

    /**
     * Méthode qui crée le formulaire par défault
     * 
     * @var [$form] Stocke le formulaire de destination
     */
    public function index(): void 
    {
        //Insatnce de Language afin de récupérer la langue séléctionné
        $_SESSION['lang'] = !isset($_SESSION['lang']) ? 'fr' : $_SESSION['lang'];
        $language = new Language($_SESSION['lang']);
        // Instance de la class Form;
        $form = new Form();
        // Creation du formulaire
        $form->startForm();
        $form->addLabel('check', $language->get('roundTrip'), ['id' => 'checkLabel']);
        $form->addInput('checkbox', 'check', ['id' => 'check', 'value' => 'false']);
        $form->addInput('text', 'destination', ['id' => 'start', 'placeholder'=> $language->get('departurePlace')]);
        $form->addInput('text', 'destination', ['id' => 'end', 'placeholder'=> $language->get('destination')]);
        $form->addLabel('wait', $language->get('wait'), ['class'=>'noneWait', 'name' => 'wait']);
        $form->addInput('number', 'wait', ['class'=>'noneWait', 'id' => 'wait', 'value' => '0', 'step' => '15', 'min' => '0']);
        $form->endForm();
        // Envoie à la vue le formulaire 
        $this->render('reservation/estimate', ['addLabel' => $form -> getFormElements()]);
    }


    /**
     * Recupère les données afin de stocké l'heure sélectionné et la traitée
     * puis la formate en secondes afin d'effectuer des calculs 
     * 
     * @var string [$time] Récupère le retour de l améthode getData
     */
    public function getTime(): void
    {
        // récupère le contenu Fetch POST par la méthode privée getData
        $time = $this->getData();
        // Si time à une longeur inférieure == 4 c'est à dire un format inférieur à 10 sans 0, on l'assigne.
        $_SESSION['time'] = strlen($time) == 4 ? '0' . $time : $time;
        // Explode dans une autre session les heures et les minutes 
        // afin de les convertir en secondes pour les calculs de temps 
        $_SESSION['processTime'] = explode(":", $_SESSION['time']);
        $_SESSION['processTime'] = intval($_SESSION['processTime'][0]) * 3600 + intval($_SESSION['processTime'][1]) * 60 ;
    }


    /**
     * Calcul les tarifs de devis
     * 
     * Une fois les données recupérer via l'API fetch, teste l'heure de rdv stockée en session
     * au préalable lors du click dans la methode getTime
     * afin d'appliqué un tarif kilométrique selon l'heure de rdv
     * Dans le même temps, on formate le temps de trajet en heure  afin de l'afficher dans les informations.
     * 
     * @var int [$price] stocke le tarif
     * @var array [$data] stocke le retour de la méthode getData
     * @var string [$tps] stocke la durée de trajet converti en integer
     * @var int [distance] Stocke la distance du trajet
     * @var string [$choice] Stocke le booléen de la checkbox
     * @var string [$wait] Stocke le temps d'attente indiqué par l'utilisateur
     * @var int [$priceDay] Tarif de jour
     * @var int [$priceNight] Tarif de nuit
     * @var array [dataTrip] Stocke les tarifs dans un tableau associatif
     * 
     * @return [$data] retour json du temps de trajet formaté et du prix
     */

    public function quoteCalculation(): void
    {
        // Initilaisation
        $price = 0;
        // récupére le contenu Fetch POST contenant la durée et la distance du trajet par l'API Matrix de google,
        // la validation du transport aller-retour et le temps d'attente sur place
        $data = $this->getData();
        // Durée du trajet
        $tps = intval(  $data[0]);
        // Distance du trajet converti en kms et arrondis à plus ou moins .5
        $distance = round(intval($data[1]) / 1000);
        // true ou false : transport aller-retour
        $choice = $data[2];
        // Temps d'attente
        $wait = $data[3];
        // On récupère en session le tps d'attente afin d'alimenter la table plus tard
        $_SESSION['wait'] = $wait;

        // Effectue une lecture de table afin de récupérer les tarifs en vigeurs
        $modelPrice = new PriceModel();
        $price = $modelPrice->findAll();

        // Si true, on stocke les données relatif à un transport aller-retour
        // Sinon on stocke les données de transport simple
        $_SESSION['roundTrip'] = "Non";
        if ($choice === true) {
            $priceDay = $price->returnJourneyDay;      // tarif jour
            $priceNight =  $price->returnJourneyNight;      // tarif nuit
            $distance = $distance*2; // Distance récupèrer * 2
            $price = intval($wait) * $price->waitingRate;    // Tps d'attente multiplié par le tarif appliqué à la minute
            $_SESSION['roundTrip'] = "Oui";
        } else {
            $priceDay = $price->oneWayDay;
            $priceNight = $price->oneWayNight;
            $distance = $distance;
            $price = 0;
        }


        // On stocke ces données dans un tableau associatif
        $dataTrip = array('priceDay' =>$priceDay,
                        'priceNight' =>  $priceNight,
                        'distance' => $distance,
                        'price' => $price
                        );
        
        // Si la Session retourne true, c'est à dire, si l'on est sur un jour férié OU un dimanche OU les deux
        // Alors le  forfait adequat est appliqué.
        // Sinon on teste la Session processTime afin d'appliquer le bon forfait kilométrique
        if (isset($_SESSION['restlessDay']) && !$_SESSION['restlessDay']) {
            
            // Calcule le delay entre 19h et l'heure de rdv selectionné
            $delay = 68500 - intval($_SESSION['processTime']); 
            // Si l'heure de rdv est supérieure ou égale à 19h
            if ($_SESSION['processTime'] >= 68500) {
                // Tarif de nuit appliqué
                $price = $this->tarif( $dataTrip);
            // Si rdv inférieur à 19h ET que le temps de trajet est supérieur à $delay ( Donc passage en tarif nuit)
            } elseif ($_SESSION['processTime'] < 68500 && $tps > $delay) {
                // on recupère le delai en minute et on l'envoi au calcul
                $min = floor($delay / 60) - 1;
                $price = $this->tarif($dataTrip, $min);
                // Sinon on applique le tarif de journée
            } else {
              $price = $dataTrip['priceDay'] * $dataTrip['distance'] + 2.30;
              $price += $dataTrip['price'];
              $price = number_format($price, 2, ",", " ");
            }

        } else {
            // Sinon le tarif forfaitaire jour fériés/nuit est appliqué (Corrspondant au tarifs nuit h24)
            $price = $this->tarif($dataTrip);
        }
        // Recupère le temps de trajet (en sec) défini par directionService
        // et l'ajoute à l'heure de rendez-vous convertis en secondes 
        // afin de déterminer l'heure d'arrivée estimée
        $time = $tps + intval($_SESSION['processTime']);
        $h = floor($time / 3600);           //Heures
        $i = (floor($time / 60) % 60) + 1;  //Minutes
        $s = $time % 60;                    //Secondes

        // Stocke le tarif en session afin de le manipuler plus tard 
        $_SESSION['price'] = $price;
        $price = $price . " &euro; *";

        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
         // Convertis en chaine et l'envoi au js
        $time = sprintf("%02.2d h %02d min", $h, $i ) ;
        $data = array(  "price" => $price,
                        "time" => $time,
                        "choice" => $choice, 
                        "lang" =>  $lang);
        
        echo json_encode($data);
    
    }


    /**
     * Calcul du tarif selon les horaires séléctionné 
     * 
     * @param int [$min] (facultatif) delai entre l'heure de de depart et l'heure du traif nuit
     * @param array [$dataTrip] tableau de données de transports selon que ce soit un trajet simple ou transport aller-retour
     * 
     * @return int [$tarif] retourne le tarif à appliquer
     */
    private function tarif($dataTrip = [], $min = null)
    {
        // Si $min n'est pas null, 
        if ($min != null) {
            // 1min = 1km
            // Enleve le delai au total kilométrique
            // Calcule les kilomètre traif nuit
            $distance = $dataTrip['distance'] - $min;
            $price1 = $dataTrip['priceNight'] * $distance + 2.30;
    
            // Kilomètre restant en tarif jour
            $distance = $min;
            $price2 = $dataTrip['priceDay'] * $distance;
            $price = $price1 + $price2;
            // Applique le temps d'attente 
            $price += $dataTrip['price'];
       } else {
            // Sinon le traif nuit est appliqué
            $price = $dataTrip['priceNight'] * $dataTrip['distance'] + 2.30;
            $price += $dataTrip['price'];
        }      
        // Formatte le tarif à 2 chiffres après la virgule
        $price = number_format($price, 2, ",", " ");

        return $price;
    }


    /**
     * Recupère la date séléctionné en front, la formate en timestamp et la comparela methode privé checkDays
     * si la sélection est un jour férié ou un dimanche.
     * 
     * @param string [$day] argument récupérer lors du click du jour séléctionné
     * @var [formatter] Instance de IntlDateFormatter paramétré en timestamp 
     * @var [$day] Récupère la date formater en timestamp
     * @var [S_SESSION['restlessDay] Stocke le booléen retourner par la méthode de restLessDay
     */
    public function getDayOfWeek(): void
    {
        // Récupère le jour sélectionné en session afin de pouvoir alimenter la base par la suite.
        $_SESSION['day'] = $this->getData();
        // Formate le jour de type string en timestamp (param: date locale, dateformat complet, timeformat nulle, timezone, calendar)
        $locale = 'fr_FR'; $timezone = 'Europe/Paris';
        if(isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') {
            $locale = 'en_US';
            $timezone = 'Europe/London';
        } 
        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE, $timezone, IntlDateFormatter::GREGORIAN);
        $day = datefmt_parse($formatter, $_SESSION['day']);

        // teste le timestamp du jours séléctionné afin de déterminé si c'est un jour férié.
        // Le stocke dans une session afin de la tester dans le calcul du devis
        $checkDays = new CheckDays();
        $_SESSION['restlessDay'] = $checkDays->easterDays($day, $_SESSION['day']);
    }  


    /**
     * Recupère le contenu Fetch POST
     * @return mixed tableau des données sélectionné coté client 
     */
    private function getData(): mixed
    {
        $content = trim(file_get_contents("php://input"));
        return  json_decode($content, true);
    }
    

    //  /**
    //  * Recoit en argument le timestamp du RDV 
    //  * la string du jour du RDV afin de les tester.
    //  * S'ils l'un des deux sont présent, la methode renvoit true
    //  * 
    //  * @param int [$date] Timestamp de la date à tester
    //  * @param string [$day] Jour de RDV cliqué
    //  * @return array qui retourne true ou false si $date est présent 
    //  */
    // private function checkDays($date, $day): bool 
    // {
    //     // Récupère l'année en cours
    //     $year = date('Y');
    
    //     // Retourne le timestamp de Pâques afin de déterminer les jours
    //     // fériés variables associés.
    //     $easterDate  = easter_date($year);
    //     $easterDay   = date('j', $easterDate);
    //     $easterMonth = date('n', $easterDate);
    //     $easterYear   = date('Y', $easterDate);
        
    //     $holidays = array(
    //     // Jours fériés fixes
    //     mktime(0, 0, 0, 1,  1,  $year),  // 1er janvier
    //     mktime(0, 0, 0, 5,  1,  $year),  // Fête du travail
    //     mktime(0, 0, 0, 5,  8,  $year),  // Victoire des alliés
    //     mktime(0, 0, 0, 7,  14, $year),  // Fête nationale
    //     mktime(0, 0, 0, 8,  15, $year),  // Assomption
    //     mktime(0, 0, 0, 11, 1,  $year),  // Toussaint
    //     mktime(0, 0, 0, 11, 11, $year),  // Armistice
    //     mktime(0, 0, 0, 12, 25, $year),  // Noel
        
    //     // Jous fériées variables
    //     // Lundi de Pâques
    //     mktime(0, 0, 0, $easterMonth, $easterDay + 1,  $easterYear), 
    //     // Ascencion
    //     mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear),
    //     // Pentecôte
    //     mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear),
    //     );
        
    //     // Scinde la chaine en array et récupère le premier index correspondant
    //     // au jour, puis le compare
    //     $day = explode(' ', $day);

    //     if (in_array($date, $holidays) || ($day[0] == "Dimanche" || $day[0] == "Sunday,")) {
    //         return true;
    //     } else {
    //         return false;
    //     }       
    // }

    /**
     * Transmet en json la langue sélectionné
     */
    public function langState(): void
    {
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        echo json_encode($lang);
    }
}