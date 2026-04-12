<?php
namespace App\Controllers;

/**
 * Renvoit vers la vue apropos
 */
class AproposController extends Controller{

    public function index()
    {
        $this->render('a_propos/index');
    }
}