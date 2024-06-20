<?php
namespace App\Controllers;

session_start();

/**
 * Renvoit vers la vue apropos
 */
class AboutController extends Controller
{

    public function index(): void
    {
        $this->render('a_propos/index');
    }
}