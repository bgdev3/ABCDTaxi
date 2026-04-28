<?php
namespace App\Controllers;

use App\Services\Form;
use App\Services\Mailer;
use App\Services\Captcha;
use App\Services\Language;

session_start();

class ContactController extends Controller
{
    public function __construct(
        private Form $form,
        private Mailer $mailer,
        private Captcha $captcha
    ) {}
    /**
     * Teste les entrées en post, la vérification re-captcha et créer le formulaire par défault
     * 
     * @var $erreur Stocke le message d'erreur
     * @var $captcha Instance de Captcha
     * @var $sendMail Instance de Mailer
     * @var $form Stocke le formulaire par défault
     */

    public function index(): void
    {
        global $sendingMail, $message;
        $error ='';
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);

        $typeFile = array('jpg'=>'image/jpg', 'jpeg'=>'image/jpeg', 'pdf'=>'application/pdf');

        // Si l'envoi en post se déroule bien avec la méthode statique de Form
        if ($this->form->validatePost($_POST, ['name', 'surname', 'mail', 'tel', 'object', 'message'])) {

            // Si les token de sécurité en POST matche afin d'éviter la faille CSRF et d'assurer du bon utilisateur
            if (isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {

                // Instance de Re-captcha pour la vérification de spams
                // $captcha = new Captcha();
                // si la clé en post de vérifiaction du captcha est déclaré
                // if (isset($_POST['recaptcha_response']))
                //     $isCaptchaValid = $this->captcha->verify($_POST['recaptcha_response']);
                // // Si le re-captcha renvoi true
                // if ($isCaptchaValid == true) {

                 // Si le numero de tel n'est pas un nombre, ou n'a pas la bonne longeur ni le bon format
                    if (!preg_match("#^(\+33|0)[67][0-9]{8}$#", $_POST['tel'])) {
                        $error = $language->get('errorPhone');
                        // OU si l'email n'est pas valide
                    } elseif (!(filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL))) {
                        $error = $language->get('errorEmail');
                    } 
                    
                    // Applique htmlspecialchars sur chaque $_POST afin d'éviter une faille XSS
                    foreach($_POST as $el => $val) {
                        $_POST[$el] = isset($_POST[$el]) ? htmlspecialchars($val, ENT_QUOTES) : null;
                    }
                    // Instance de Mailer
                    // $sendMail = new Mailer();

                    // Si l'envoi du fichier ne retourne pas d'erreur
                    // Ici le fichier n'est pas stocké en base et est transmis directement à phpMailer
                    // pour l'envoi du mail
                    if ($this->form->validateFiles($_FILES, ['file'])) {
                        // On teste le fichier et on récupère l'erreur s'il y en a une
                        $error = empty($error) ? $this->form->errorUpload($_FILES, ['file'], $typeFile) : "";

                        // S'il n'y a pas d'erreur de fichier ni de post
                        // Envoi du mail avec un paramètre facultatif : File
                        // en récupérant le message d'erreur d 'envoi de mail s'il y en a un
                        if (empty($error)) {
                            $message =  $this->mailer -> sendContact($_POST, $_FILES);
                            $sendingMail = empty($message) ? true : false;
                        }
                        
                    } else {
                        // Si $_FILES n'est pas déclaré, on envoi sans le paramètre facultatif
                        // et on teste sendingMail pour l'affichage
                        $message = $this->mailer -> sendContact($_POST);
                        $sendingMail = empty($message) ? true : false;
                    }
                    // Si l'envoi de mail retourne une erreur, on la stocke dans la variable $erreur
                    // afin de la passé à la vue pour l'afficher
                    if (!empty($message)) {
                        $error = $message;
                    }
                // } else {
                //     $error = $language->get('errorCaptcha');
                // }
               
            } else {
                $error = !empty($error) ? $error : $language->get('unknownUser');
            }
        // Sinon si certains champs ne sont pas remplis
        // On affiche une erreur
        } else {
            $error = !(empty($_POST)) ?  $language->get('errorForm') : "";
        }

        // Instance du formulaire
        // $form = new Form();
        // Créer le formulaire
        $this->form->startForm('#', 'POST', ['id'=>'myForm', 'novalidate' =>'', "enctype" => "multipart/form-data"]);
        $this->form->addLabel('name', $language->get('name') .': * ');
        $this->form->addInput('text', 'name', ['id' => 'nom', 'class'=> 'formInput', 'placeholder' => $language->get('name') , 'required' => '']);
        $this->form->addLabel('surname', $language->get('surname') .': * ');
        $this->form->addInput('text', 'surname', ['id' => 'prenom', 'class'=> 'formInput', 'placeholder' => $language->get('surname'), 'required' => '']);
        $this->form->addLabel('mail',  $language->get('email') .': *');
        $this->form->addInput('email', 'mail', ['id' => 'mail', 'class'=> 'formInput', 'placeholder' => $language->get('email'), 'required' => '']);
        $this->form->addLabel('tel', $language->get('phone').': * ');
        $this->form->addInput('tel', 'tel', ['id' => 'phone', 'class'=> 'formInput', 'placeholder' => $language->get('phone'), 'minlength' => '10', 'maxlength' => '10', 'required' => '']);
        $this->form->addLabel('text', $language->get('file').':  ');
        $this->form->addSmall('  ( .jpg, .jpeg, .pdf  - ' . $language->get('sizeLimit') .' : 3Mo )');
        $this->form->addInput('file', 'file', ['id' => 'file', 'class'=> 'formInput', 'capture'=>'environment']);
        $this->form->addLabel('object', $language->get('object').': * ',  ['class'=> 'small_contact']);
        $this->form->addInput('text', 'object', ['id' => 'objet', 'class'=> 'formInput', 'placeholder' => $language->get('object'), 'required' => '']);
        $this->form->addLabel('message', $language->get('message').': * ');
        $this->form->addTextaera('message', '',['id' => 'message', 'class'=> 'formInput','placeholder' => $language->get('message'), 'rows' => '10', 'cols'=>'33', 'required' => '']);
        $this->form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']): null]);
        $this->form->addInput('checkbox', 'agree',['id' => 'agree', 'required' => '']);
        $this->form->addLabel('agree', $language->get('agree'));
        $this->form->addInput('submit', 'btnContact',['id' => 'btnContact', 'class'=>'btnForm', 'value' => $language->get('btnSend')]);
        $this->form->addInput('hidden', 'recaptcha_response', ['id' => 'recaptchaResponse']);
        $this->form->endForm();

        // renvoit à la vue correspondante
        $this->render('contact/index', ['addForm'=> $this->form->getFormElements(), 'error'=>$error, 'sendingMail' => $sendingMail]);
    }


    /**
     * Effectue une vérification de la langue à transmettre en JSON
     */
    public function langState(): void
    {
        // Véirifie la déclaration de la langue session, sinon assigne la valeur par défault
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';

        echo json_encode($lang);
    }

}

