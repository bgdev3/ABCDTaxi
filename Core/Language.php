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
    $basePath = dirname(__DIR__) . '/public/languages/';
    $file = $basePath . "lang_{$lang}.php";

    if (file_exists($file)) {
        $this->translations = include($file);
    } else {
        $this->translations = include($basePath . "lang_fr.php");
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