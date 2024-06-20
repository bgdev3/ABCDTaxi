<?php 
namespace App\Core;

class checkDays{

     
    /**  
     * Recoit en argument le timestamp du RDV 
    * la string du jour du RDV afin de les tester.
    * S'ils l'un des deux sont présent, la methode renvoit true
    * 
    * @param int [$date] Timestamp de la date à tester
    * @param string [$day] Jour de RDV cliqué
    * @return array qui retourne true ou false si $date est présent 
    */
    
    public function easterDays($date, $day): bool
    {
         // Récupère l'année en cours
         $year = date('Y');
    
         // Retourne le timestamp de Pâques afin de déterminer les jours
         // fériés variables associés.
         $easterDate  = easter_date($year);
         $easterDay   = date('j', $easterDate);
         $easterMonth = date('n', $easterDate);
         $easterYear   = date('Y', $easterDate);
         
         $holidays = array(
         // Jours fériés fixes
         mktime(0, 0, 0, 1,  1,  $year),  // 1er janvier
         mktime(0, 0, 0, 5,  1,  $year),  // Fête du travail
         mktime(0, 0, 0, 5,  8,  $year),  // Victoire des alliés
         mktime(0, 0, 0, 7,  14, $year),  // Fête nationale
         mktime(0, 0, 0, 8,  15, $year),  // Assomption
         mktime(0, 0, 0, 11, 1,  $year),  // Toussaint
         mktime(0, 0, 0, 11, 11, $year),  // Armistice
         mktime(0, 0, 0, 12, 25, $year),  // Noel
         
         // Jous fériées variables
         // Lundi de Pâques
         mktime(0, 0, 0, $easterMonth, $easterDay + 1,  $easterYear), 
         // Ascencion
         mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear),
         // Pentecôte
         mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear),
         );
         
         // Scinde la chaine en array et récupère le premier index correspondant
         // au jour, puis le compare
         $day = explode(' ', $day);
 
        //  Si présent dans l'array ou si le prmier index de date vaut les valeur indiqué
         if (in_array($date, $holidays) || ($day[0] == "Dimanche" || $day[0] == "Sunday,")) {
             return true;
         } else {
             return false;
         }       
    }
}