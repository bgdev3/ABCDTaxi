<?php
namespace App\Controllers;

use App\Core\Form;
use App\Core\Language;
use App\Entities\Price;
use App\Models\PriceModel;

session_start();

class AdminPriceController extends Controller
{

    /**
     * Récupère et affiche les données relatives 
     * 
     * @param string [$token] clé de sécurité
     */
    public function index($token): void 
    {
        // Récupère la langue sélectionnée
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Attrubuts gflobales
        global $priceList;
        // Si les tokens correspondent
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {
            // Instance de priceModel afin de récupérer une lecture de table des tarifs
            $priceModel = new PriceModel();
            $priceList = $priceModel->findAll();

        } else {
            header('location:index.php?controller=panelAdmin&action=index');
        }
        // Renvoi vers la vue
        $this->render('admin/prices', ['prices' => $priceList]);
    }


    /**
     * Afiiche le formulaire de mise à jour des tarifs et traites les données envoyés en POST
     * 
     * @param int [$id] Id de l'enregistrement 
     * @param string [Token] de sécurité
     */
    public function updateAdminPrice($id, $token): void 
    {
        global $error;
         // Récupère la langue sélectionnée
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si les les données POST sont valides
        if (Form::validatePost($_POST, ['oneWayDay', 'returnDay', 'oneWayNight', 'returnNight', 'waitingRate'])) {
            // Si le token de sécurité correspondent
            if (isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {
                // Instance de Price puis on l'hydrate
                $price = new Price();
                // Applique htmlspecialchars pour les faille XSS et trim afin de supprimer les espaces
                $price->setOneWayDay(htmlspecialchars(trim($_POST['oneWayDay']), ENT_QUOTES));
                $price->setReturnJourneyDay(htmlspecialchars(trim($_POST['returnDay']), ENT_QUOTES));
                $price->setOneWayNight(htmlspecialchars(trim($_POST['oneWayNight']), ENT_QUOTES));
                $price->setReturnJourneyNight(htmlspecialchars(trim($_POST['returnNight']), ENT_QUOTES));
                $price->setWaitingRate(htmlspecialchars(trim($_POST['waitingRate']), ENT_QUOTES));
                // Effectue la mise à jour
                $priceModel = new PriceModel();
                $priceModel->update($price, $id);
                // redirige vers les tarifications en cours
                header('location:index.php?controller=adminPrice&action=index&token=' . trim($_SESSION['token']));
            // Si le stokens ne correpondent pas
            } else {
                $error = $language->get('unknownUser');
            }
        // Si les champs ne sont pas vides
        } else {
            $error = !empty($_POST) ? $language->get('errorForm') : "";
        }
        // Si le admin est connecté et si les token GET et SESSION correspondent
        if (isset($_SESSION['username_admin']) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {

            $priceModel = new PriceModel();
            $priceModel = $priceModel->findAll();
            // Affiche le formulaire et avec les données récupérées des enregistrements
            $form = new Form();

            $form->startForm('#', 'POST', ['id' => 'myFormFile', 'class' => ' col-12 col-md-8 col-lg-10 mx-auto pb-3 ', 'novalidate' =>'']);
            $form->startDiv('d-flex flex-lg-row flex-column gap-md-5 w-75 mx-auto');
            $form->startFieldSet('', 'form-group  p-2 w-100');
            $form->legend('Jour', 'mb-2fs-5 fst-italic text-danger col-4 col-md-4 col-lg-4');
            $form->startDiv('form-group mb-3');
            $form->addLabel('oneWayDay', 'Simple:');
            $form->addInput('number', 'oneWayDay', ['id' => 'oneWayDay', 'class' => 'form-control  bg-transparent text-secondary border border-secondary', 'value' => $priceModel->oneWayDay, 'required' => '']);
            $form->endDiv();
            $form->startDiv('form-group mb-3');
            $form->addLabel('returnDay', 'Aller-retour');
            $form->addInput('number', 'returnDay', ['id' => 'returnDay', 'class' => 'form-control  bg-transparent text-secondary border border-secondary ', 'value' => $priceModel->returnJourneyDay, 'required' => '']);
            $form->endDiv();
            $form->endFieldset();
            $form->startFieldSet('', 'form-group  p-2 w-100');
            $form->legend('Nuit', 'mb-2 fs-5 fst-italic text-danger col-4 col-md-4 col-lg-4');
            $form->startDiv('form-group mb-3');
            $form->addLabel('oneWayNight', 'Simple :');
            $form->addInput('number', 'oneWayNight', ['id' => 'oneWayNight', 'class' => 'form-control  bg-transparent text-secondary border border-secondary ','value' => $priceModel->oneWayNight, 'required' => '']);
            $form->endDiv();
            $form->startDiv('form-group mb-3');
            $form->addLabel('returnNight', 'Aller-retour');
            $form->addInput('number', 'returnNight', ['id' => 'returnNight', 'class' => 'form-control  bg-transparent text-secondary border border-secondary ', 'value' => $priceModel->returnJourneyNight, 'required' => '']);
            $form->endDiv();
            $form->endFieldSet();
            $form->endDiv();
            $form->startFieldSet('', 'form-group  p-2 w-75 mx-auto'); 
            $form->startDiv('form-group mb-3 col-12 col-md-4 mx-auto p-2');
            $form->legend('Attente', 'mb-2  fs-5 fst-italic text-danger col-4 col-md-4 col-lg-4');
            $form->addInput('number', 'waitingRate', ['id' => 'waitingRate', 'class' => ' form-control  bg-transparent text-secondary border border-secondary ', 'value' => $priceModel->waitingRate, 'required' => '']);
            $form->endDiv();
            $form->endFieldSet(); 
            $form->startDiv('form-group  text-center w-75 mx-auto');
            $form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']) : null]);
            $form->addInput('submit', 'btnPrice',['id' => 'btnPrice', 'class'=>'btn btn-dark text-danger col-10 col-md-3', 'value' => 'Modifier les tarifs']);
            $form->endDiv();
            $form->endForm();
            // Envoi vers la vue 
            $this->render('admin/updatePrices', ['form' => $form->getFormElements(), 'error' => $error]);
        // Sinon on redirige vers la page d'accueil
        } else {
            header('location:index.php');
        }
    }
}