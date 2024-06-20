<?php 
namespace App\Controllers;


abstract class Controller
{
    /**
     * Redirige vers les vues demandées
     * 
     * @param string Récupère le chemin dans les controllers
     * @param array Récupère un tableau de données
     */

    public function render(string $path, array $data = []): void
    {
        // Permet d'extraire les données sous forme de variable
        extract($data);

        // On crée le buffer de sortie
        ob_start();

        // Créer le chemin et inclue le fichier de la vue souhaité
        include dirname(__DIR__) . '/Views/' . $path . '.php'; 

        // On vide le buffer dans les variables $title et $content
        $content = ob_get_clean();

        //On fabrique le template
        include dirname(__DIR__) . '/Views/base.php';
    }
}