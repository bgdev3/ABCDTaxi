<?php 
namespace App\Controllers;

use App\Core\Form;
use App\Core\Language;
use App\Models\ClientModel;
use App\Core\Captcha;

session_start();

class UserController extends Controller
{

    /**
     * Affiche le formulaire de connexion et gère les donnés en POST
     * Vérifie les données de connexion et permet la connexion uniquement si le sdonnées sont correctes
     * 
     * @var string [$error] Récupère le message
     * @var string [$email] Récupère l'eamil utilisateur
     * @var string [$nbUser] Récupère le numéro client 
     * @var object [$model] Insatnce de UserModel
     * @var array [$user] Récupère l'enregistrement de l'utilisateur connecté
     */
    public function index(): void
    {
        global $error;
        // Récupère la langiue sélectionnée par défaut
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si les champs sont valides
        if (Form::validatePost($_POST, ['email', 'idUser'])) {

            // On récupère l'email et et le numéro client afin d'éviter la faille XSS
            // en utilisant htmlspecialchars
            $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email']), ENT_QUOTES) : null;
            $nbUser = isset($_POST['idUser']) ? htmlspecialchars(trim($_POST['idUser']), ENT_QUOTES) : null;

            // Si le token est déclaré et s'il correspond à celui passé en post
            if (isset($_POST['token']) && isset($_SESSION['token']) && $_POST['token'] == $_SESSION['token']) {
                
                // Instance de Re-captcha pour la vérification de spams
                $captcha = new Captcha();
                // si la clé en post de vérifiaction du captcha est déclaré
                if (isset($_POST['recaptcha_response']))
                $captcha = $captcha->verify($_POST['recaptcha_response']);
                // Si le re-captcha renvoi true
                if ($captcha == true) {

                    // On instancie UserModel
                    $model = new ClientModel();
                    // Récupère l'enregistrement en testant l'email utilisateur
                    $user = $model->find($email); 
                    // Appelle la méthode privé validateAuth afin de vérifier le numClient hashé en base
                    // Si un erreur est retourné, on l'affiche
                    $error = $this->validateAuth($email, $nbUser, $user);
                } else {
                    $error = "La vérification re-captcha a échoué !";
                }
            } else {
                // Sinon on affiche l'erreur
                $error = $language->get('unknonwUser');
            }

        // Si true est déclarés et que les tokens correspondent OU 
        // si false est déclaré et que les tokens de sécurités correspondent afin de s'assurer du bon utilisateur
        } elseif (isset($_POST['true']) || isset($_POST['false']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {
            // Appelle de la méthode logout avec les arguments correspondant
            // si true on revient à la page d'accueil, sinon on revient sur la liste des réservations
            $_POST['true'] ? $this->logout($_POST['token'], $_POST['true']) : $this->logout($_POST['token'], $_POST['false']);

        // Si certains champs ne sont pas vide ou ne correspondent pas, on affiche l'erreur
        } else {
            $error = !empty($_POST) ? $language->get('unknownUser') : "";
        }

        // Instancie Form et crée le formulaire
        $form = new Form();
        // Si aucun utilisateur est déclaré on créer le formulaire de connexion
        if (!isset($_SESSION['username'])) {
            $form->startForm('#', 'POST', ['id'=>'myForm', 'novalidate' => '']);
            $form->addLabel('email',  $language->get('email') . ': ');
            $form->addInput('email', 'email', ['id' => 'email', 'placeholder' =>  $language->get('email'), 'class'=> 'formInput','required' => '']);
            $form->addLabel('idUser',  $language->get('password') . ': ');
            $form->addInput('text', 'idUser', ['id' => 'idUser','placeholder' => 'xxxxxxxxxx', 'class'=> 'formInput','required' => '']);
            $form->addInput('hidden', 'token', ['id' => 'hidden', 'class'=> 'formInput', 'value' => isset($_SESSION['token']) ? trim($_SESSION['token']) : null]);
            $form->addInput("submit", "btnConnect", ['id'=>'btnConnect', 'class'=>'btnForm', "value"=> $language->get('btnLog')]);
            $form->addInput('hidden', 'recaptcha_response', ['id' => 'recaptchaResponse']);
            $form->endForm();
        // Sinon on crée le formulaire de confirmation 
        } else {
            $form->startForm('#', 'POST', ['id'=>'myForm', 'class' => 'confirmLogout', 'novalidate' => '']);
            $form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']): null]);
            $form->addInput("submit", "true", ['class'=>'btnConfirmLogOut', "value"=> $language->get('leave')]);
            $form->addInput("submit", "false", ['class'=>'btnConfirmLogOut', "value"=> $language->get('stay')]);
            $form->endForm();
        }
       // Envoit vers la vue correspondante
        $this->render("user/index", ['addAuth' => $form->getFormElements(), 'error'=> $error]);
    }


    /**
    * Valide l'authentification de l'utilisateur à la connexion
    * 
    * @param string [email] récupère l'email de l'utilisateur
    * @param string [nbUser] récupère le mdp hashé afin de le tester
    * @param object [user] récupère l'objet l'instance creation UserModel;
    * 
    * @return string $error Le message d'erreur
    */
    private function validateAuth($email, $nbUser, $user): string
    {
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si la session n'existe pas
        if (!isset($_SESSION['username'])) {
            // Si les champs ne sont pas vides et si token est bien déclaré
            if ($email != null && $nbUser != null && isset($_SESSION['token']) && isset($_SESSION['token_time'])) {
                // Si la SESSION token correspond au POST Token
                if ($_SESSION['token'] == $_POST['token']) {
                    $timestamp = time() - 10 * 60;
                    // Si le jeton n'est pas expiré
                    if ($_SESSION['token_time'] >= $timestamp) {
                        // Si l'utilisateur existe et si le nbUser correspond
                        if($user){
                            if (password_verify($nbUser, $user->num_client)) {
                                // Génère un nouvel PHPSESSID afin d'eviter un détournement de session
                                // et on stocke le nom d'utilisateur et l-ID puis on redirige vers la liste des réservations
                                session_regenerate_id();
                                $_SESSION['username'] = $user->surname;
                                $_SESSION['id_user'] = $user->idClient;
                    
                                header("location:index.php?controller=reservations&action=index");
                            } else {
                                $error =  $language->get('errorAuth1');
                            }
                        } else {
                        $error =  $language->get('errorAuth2');
                        }
                    } else {
                    $error= $language->get('errorAuth6');
                    }
                } else {
                $error = $language->get('errorAuth3');
                }
            } else {
            $error = $language->get('errorAuth4');
            }
        } else {
        $error =  $language->get('errorAuth5') . $_SESSION['username'];
        }
        return $error;
    }


    /**
     * Permet la déconnexion de l'utilisateur en vidant les sessions utilisateur
     * @param int [token] Variable stockant le token de sécurité
     * @param array [post] contient la valeur de la confirmation
     * 
     * Selon les paramètres, vide le ssession et redirige vers la page adequate
     */
    public function logout($token, $post = null): void
    {   
        // Si le token est bien déclaré et s'il correspond à celui en GET
        if (isset($_POST['true']) || isset($_GET['token'])  && $_GET['token'] == $_SESSION['token']) {
            // On détruit les sessions utilisateur et on redirige vers la page d'accueil
            session_unset();
            session_destroy();
            header('location:index.php?controller=home&action=index');
        // Sinon redirige vers la liste des réservations
        } else {
           header('location:index.php?controller=reservations&action=index');
        }
    }  
}