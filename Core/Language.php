<?php
namespace App\Core;

class Language
{

    // Attribut
    private $lang;
    private $translations = [];

   /**
    * Assigne à l'instanciation les valeur par défault et charge le fichier corespondant
    * @param string [$lang] Valeur par défault
    */
    public function __construct($lang = 'fr') 
    {
        $this->lang = $lang;
        $this->loadLanguage($lang);
    }


    /**
     * Charge le fochier coorespondant selon le paramètre
     * 
     * @param string [$lang] valeur de la langue sélectionnée
     */
    private function loadLanguage($lang): void 
    {
        // Chemin de fichier par default
        $file = "languages/lang_{$lang}.php";
        // Si file existe, stocke le chemin de fichier correspondant
        if (file_exists($file)) {
            $this->translations = include($file);
        } else {
            $this->translations = include("languages/lang_fr.php"); // Fallback to English
        }
    }


    /**
     * @param string [$key] Valeur de la clé voulu du tableau associatif
     *  du fichier de la langue choisi
     */
    public function get($key): string
    {
        return $this->translations[$key] ?? $key;
    }
}