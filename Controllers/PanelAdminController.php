<?php
namespace App\Controllers;

use App\Core\Form;

class PanelAdminController extends Controller
{

  // Renvoi vers l'accueil administrateur
    public function index(): void
    {
      $this->render('admin/index');   
    }
}