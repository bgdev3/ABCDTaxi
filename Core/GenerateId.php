<?php 
namespace App\Core;


/**
 * Permet de générer un identifiant unique pour chaque nouvel enregistrement non connu
 * 
 * @return string [$id] Retourne une chaine aléatoire
 */
class generateId 
{

    public function generate() 
    {
        
        // Chaine de support pour le calcul de l'identifiant
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $id = '';
        
        // Génère l'identifant sur  caractère
        for($i = 0; $i < 10; $i++){
            // Effectue un modulo de mt_rand sur la longeur de chaine x10
            $id .= $str[mt_rand()%strlen($str)];
        }
        return $id;
    }         
}
