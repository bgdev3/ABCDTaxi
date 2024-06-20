<?php
use App\Core\Language;

$lang ='fr';
 if (isset($_GET['lang'])) {

    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
    
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
}

$language = new Language($lang);