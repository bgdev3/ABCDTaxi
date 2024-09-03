<?php 
namespace App\Core;

class Captcha {

    /**
     * Methode de vérification d'un test de turing 
     * 
     * @param string [string] Chaine alphanumerique correspond à la clé re-captcha coté client
     * @return bool [bool]
     */

    public function verify($secret): bool
     {
        // Paramètre la requête POST
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_secret = '**********';
        $recaptcha_response = $secret;

        // Récupère et decode le fetch POST
        $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
        $recaptcha = json_decode($recaptcha);
       
        // Retourne true ou false selon le score re-captcha retourné
        // Inférieur à .5 ne correspond pas à un humain.
        if ( $recaptcha->success == true && $recaptcha->score >= .3) {   
            return true;
        } else {    
            return false;
        }
    }      
}
