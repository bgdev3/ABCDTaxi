<?php
namespace App\Controllers;

use App\Services\Form;
use App\Services\Language;
use App\Entities\Price;
use App\Models\PriceModel;

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

class AdminPriceController extends Controller
{

    public function __construct ( 
        private Form $form, 
        private PriceModel $priceModel,
        private Price $price
        ){}
    /**
     * Récupère et affiche les données relatives 
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
            $priceList = $this->priceModel->findAll();

        } else {
            header('location:/public/panelAdmin');
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
        $error = '';
         // Récupère la langue sélectionnée
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si les les données POST sont valides
        if ($this->form->validatePost($_POST, ['oneWayDay', 'returnDay', 'oneWayNight', 'returnNight', 'waitingRate', 'minDistanceDay', 'minDistanceNight', 'minDistanceDayReturn', 'minDistanceNightReturn', 'minPerception', 'pickupPrice'])) {
            // Si le token de sécurité correspondent
            if (isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {
              
                // Applique htmlspecialchars pour les faille XSS et trim afin de supprimer les espaces
                $this->price->setOneWayDay(htmlspecialchars(trim($_POST['oneWayDay']), ENT_QUOTES));
                $this->price->setReturnJourneyDay(htmlspecialchars(trim($_POST['returnDay']), ENT_QUOTES));
                $this->price->setOneWayNight(htmlspecialchars(trim($_POST['oneWayNight']), ENT_QUOTES));
                $this->price->setReturnJourneyNight(htmlspecialchars(trim($_POST['returnNight']), ENT_QUOTES));
                $this->price->setWaitingRate(htmlspecialchars(trim($_POST['waitingRate']), ENT_QUOTES));
                $this->price->setMinDistanceDay(htmlspecialchars(trim($_POST['minDistanceDay']), ENT_QUOTES));
                $this->price->setMinDistanceNight(htmlspecialchars(trim($_POST['minDistanceNight']), ENT_QUOTES));
                $this->price->setMinDistanceDayReturn(htmlspecialchars(trim($_POST['minDistanceDayReturn']), ENT_QUOTES));
                $this->price->setMinDistanceNightReturn(htmlspecialchars(trim($_POST['minDistanceNightReturn']), ENT_QUOTES));
                $this->price->setMinPerception(htmlspecialchars(trim($_POST['minPerception']), ENT_QUOTES));
                $this->price->setPickupPrice(htmlspecialchars(trim($_POST['pickupPrice']), ENT_QUOTES));

                // Effectue la mise à jour
                $this->priceModel->update($this->price, $id);
                // redirige vers les tarifications en cours
                header('location:/public/adminPrice/index/' . trim($_SESSION['token']));
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

            $priceModel = $this->priceModel->findAll();
            // Affiche le formulaire et avec les données récupérées des enregistrements

            $this->form->startForm('#', 'POST', ['id' => 'myForm', 'class' => ' col-12 col-md-8 col-lg-10 mx-auto pb-3 ']);
            $this->form->startDiv('d-flex flex-lg-row flex-column gap-md-5 w-75 mx-auto');
            $this->form->startFieldSet('', 'form-group  p-2 w-100');
            $this->form->legend('Jour', 'mb-2fs-5 fst-italic text-danger col-4 col-md-4 col-lg-4');
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('oneWayDay', 'Tarif simple:');
            $this->form->addInput('number', 'oneWayDay', ['id' => 'oneWayDay', 'class' => 'form-control  bg-transparent text-secondary border border-secondary', 'value' => $priceModel->oneWayDay, 'step' => 0.01, 'min' => 0, 'required' => '']);
            $this->form->endDiv();
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('returnDay', 'Tarif aller-retour');
            $this->form->addInput('number', 'returnDay', ['id' => 'returnDay', 'class' => 'form-control  bg-transparent text-secondary border border-secondary ', 'value' => $priceModel->returnJourneyDay, 'step' => 0.01, 'min' => 0, 'required' => '']);
            $this->form->endDiv();

            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('minDistanceDay', 'Min kilomètrique jour');
            $this->form->addInput('number', 'minDistanceDay', ['id' => 'minDistanceDay', 'class' => 'form-control  bg-transparent text-secondary border border-secondary ', 'value' => $priceModel->minDistanceDay, 'step' => 0.01, 'min' => 0, 'required' => '']);
            $this->form->endDiv();
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('minDistanceDayReturn', 'Min kilomètrique aller-retour');
            $this->form->addInput('number', 'minDistanceDayReturn', ['id' => 'minDistanceDayReturn', 'class' => 'form-control  bg-transparent text-secondary border border-secondary ', 'value' => $priceModel->minDistanceDayReturn, 'step' => 0.01, 'min' => 0, 'required' => '']);
            $this->form->endDiv();

            $this->form->endFieldset();
            $this->form->startFieldSet('', 'form-group  p-2 w-100');
            $this->form->legend('Nuit', 'mb-2 fs-5 fst-italic text-danger col-4 col-md-4 col-lg-4');
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('oneWayNight', 'Tarif simple :');
            $this->form->addInput('number', 'oneWayNight', ['id' => 'oneWayNight', 'class' => 'form-control  bg-transparent text-secondary border border-secondary ','value' => $priceModel->oneWayNight, 'step' => 0.01, 'min' => 0, 'required' => '']);
            $this->form->endDiv();
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('returnNight', 'Tarif aller-retour');
            $this->form->addInput('number', 'returnNight', ['id' => 'returnNight', 'class' => 'form-control  bg-transparent text-secondary border border-secondary ', 'value' => $priceModel->returnJourneyNight, 'step' => 0.01, 'min' => 0, 'required' => '']);
            $this->form->endDiv();

            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('minDistanceNight', 'Min kilomètrique nuit');
            $this->form->addInput('number', 'minDistanceNight', ['id' => 'minDistanceNight', 'class' => 'form-control  bg-transparent text-secondary border border-secondary ','value' => $priceModel->minDistanceNight, 'step' => 0.01, 'min' => 0, 'required' => '']);
            $this->form->endDiv();
            $this->form->startDiv('form-group mb-3');
            $this->form->addLabel('minDistanceNightReturn', 'Min kilomètrique aller-retour');
            $this->form->addInput('number', 'minDistanceNightReturn', ['id' => 'minDistanceNightReturn', 'class' => 'form-control  bg-transparent text-secondary border border-secondary ','value' => $priceModel->minDistanceNightReturn, 'step' => 0.01, 'min' => 0, 'required' => '']);
            $this->form->endDiv();
            $this->form->endFieldSet();

            $this->form->endDiv();
            $this->form->startFieldSet('', 'd-flex flex-lg-row flex-column w-75 mx-auto'); 
            $this->form->startDiv('form-group mb-3 col-12 col-md-4 mx-auto p-2');
            $this->form->legend('Attente', 'mb-2  fs-5 fst-italic text-danger col-4 col-md-4 col-lg-4 w-100');
            $this->form->addInput('number', 'waitingRate', ['id' => 'waitingRate', 'class' => ' form-control  bg-transparent text-secondary border border-secondary ', 'value' => $priceModel->waitingRate, 'step' => 0.01, 'min' => 0, 'required' => '']);
            $this->form->endDiv();

            $this->form->startDiv('form-group mb-3 col-12 col-md-4 mx-auto p-2');
            $this->form->legend('Min de perception', 'mb-2  fs-5 fst-italic text-danger col-4 col-md-4 col-lg-4 w-100');
            $this->form->addInput('number', 'minPerception', ['id' => 'minPerception', 'class' => ' form-control  bg-transparent text-secondary border border-secondary ', 'value' => $priceModel->minPerception, 'step' => 0.01, 'min' => 0, 'required' => '']);
            $this->form->endDiv();

            $this->form->startDiv('form-group mb-3 col-12 col-md-4 mx-auto p-2');
            $this->form->legend('Prise en charge', 'mb-2  fs-5 fst-italic text-danger col-4 col-md-4 col-lg-4 w-100');
            $this->form->addInput('number', 'pickupPrice', ['id' => 'pickupPrice', 'class' => ' form-control  bg-transparent text-secondary border border-secondary ', 'value' => $priceModel->pickupPrice, 'step' => 0.01, 'min' => 0, 'required' => '']);
            $this->form->endDiv();
            $this->form->endFieldSet(); 

            $this->form->startDiv('form-group  text-center w-75 mx-auto');
            $this->form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']) : null]);
            $this->form->addInput('submit', 'btnPrice',['id' => 'btnPrice', 'class'=>'btnAdmin btn btn-dark text-danger col-10 col-md-3', 'value' => 'Modifier les tarifs']);
            $this->form->endDiv();
            $this->form->endForm();
            // Envoi vers la vue 
            $this->render('admin/updatePrices', ['form' => $this->form->getFormElements(), 'error' => $error]);
        // Sinon on redirige vers la page d'accueil
        } else {
            header('location:index.php');
        }
    }
}