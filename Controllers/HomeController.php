<?php
namespace App\Controllers;
use App\Core\Language;

/**
 * envois vers la vue la page à afficher par défault
 */
class HomeController extends Controller
{
    /**
     * Renvoi vers la page d'accueil
     * 
     * @param string [$lang] Langue sélectionné par default
     */
    public function index($lang = 'fr'): void
    {
        $this->render('home/index');
    }

    // Renvoi vers les mentions légales
    public function mentions(): void 
    {
        $this->render('home/mentions');
    }

}