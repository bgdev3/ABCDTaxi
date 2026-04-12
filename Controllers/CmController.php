<?php

namespace App\Controllers;

session_start();

class CmController extends Controller
{
    public function index(): void
    {
        $this->render('cm/index');
    }
}