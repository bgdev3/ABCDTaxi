<?php
namespace App\Controllers;

use App\Entities\AdminUser;
use App\Models\AdminUserModel;
use App\Core\Mailer;
use App\Core\Form;
use App\Core\Captcha;
use App\Core\Language;

session_start();

class AuthAdminController extends Controller
{
    
    /**
     * Affiche le formulaire d'authentification administrateur
     * et effectue les vérification de connexion
     * 
     */
    public function index(): void
    {
        global $error;
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);

        // Si les champs sont valides
        if (Form::validatePost($_POST, ['email', 'password'])) {
            // Stocke les données email et password en évitant la faille XSS
            $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : null;
            $password = isset($_POST['password']) ? htmlspecialchars($_POST['password']) : null;

            // Si le token de sécurité correspond
            if (isset($_SESSION['token']) && $_POST['token'] == $_SESSION['token']) {
                // Instance de la classe Captcha
                $captcha = new Captcha();
                // Si la clé du cpatcha est bien récupéré en POST
                 // Teste la rapidité d'execution afin de s'assurer d'un humain
                if (isset($_POST['recaptcha_response']))  
                   $captcha = $captcha->verify($_POST['recaptcha_response']);
                // Si le captcha est valide
                if ($captcha == true) {
                    // Instance de AdminModel
                    $admin = new AdminUserModel();
                    // Récupère l'enregistrement correspondant
                    $admin = $admin->find($email);
                    $_SESSION['test'] = $admin;
                    // Teste le bon mot de passe renseigné par l'administrateur
                    $error = $this->validateAuth($email, $password, $admin);
                    //  Si okay redirection vers le contenu
                } else {
                    $error =  $language->get('errorCaptcha');
                }
            // Sinon l'erreur du Token est renvoyé
            } else {
                $error = $language->get('unknownUser');
            }
        // L'erreur de la requête POST est envoyé
        } else {
           $error = !empty($_POST) ? $language->get('errorForm') : "";
        }
        // Permet de charger la librairies bootstrap uniquement lors de la connexion au Back-Office
        $_SESSION['admin'] = 'start';
        // Instance du formulaire
        $form = new Form();

        $form->startForm('#', 'POST', ['id'=>'myForm', 'class' => 'bg-dark border border border-dark mb-3 p-2 ', 'novalidate' =>'']);
        $form->startFieldset('LOGIN', '');
        $form->startDiv('form-floating');
        $form->addInput('email', 'email', ['id' => 'email', 'class'=> 'form-control mb-3 mt-3 bg-dark border border-secondary text-light ', 'placeholder' => 'Email', 'required' => '']);
        $form->addLabel('email', 'Email', ['class' => 'bg-dark bg-opacity-10']);
        $form->endDiv();
        $form->startDiv('form-floating');
        $form->addInput('text', 'password', ['id' => 'password', 'class'=> 'form-control mb-3 bg-dark border border-secondary text-light',  'placeholder' => 'Password', 'required' => '']);
        $form->addLabel('password', 'Password', ['class' => 'bg-dark bg-opacity-10']);
        $form->endDiv();
        $form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']) : null]);
        $form->addInput('hidden', 'recaptcha_response', ['id' => 'recaptchaResponse']);
        $form->addInput('submit', 'btnAuth',['id' => 'btnAuth', 'class'=>'btn btn-dark border border-danger', 'value' => 'Se connecter']);
        $form->endFieldset();
        $form->endForm();

        $this->render('admin/auth', ['addSignUpForm' => $form->getFormElements(), 'error' => $error]);
    }


    /**
     * Affiche le formulaire de renouvellement de mot de passe 
     * et effectue les vérifications de connexion
     * 
     * @param string [$token] Clé de sécurité
     */
    public function register($token): void
    {
        global $error;
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si les champs de formulaires sont valides
        if (Form::validatePost($_POST, ['username', 'email', 'password', 'confirmPassword'])) {

            // récupère les données en post en applisaunt htmlspecialchars afin d'eviter une faille XSS
            $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : null;
            $password = isset($_POST['password']) ? htmlspecialchars($_POST['password']) : null;
            $confirmPassword = isset($_POST['confirmPassword']) ? htmlspecialchars($_POST['confirmPassword']) : null;

            // Insatnce de la classe Captcha
            $captcha = new Captcha();
           
            // Si le mot de passe est supérieur ou égal à 8 ET inférieur ou égal à 12
            // ET que les deux pot de passe concordent
            if(strlen($password) >= 8 && strlen($password) <= 12 && $password == $confirmPassword) {
                // Si le mot de passe contient des cartères spéciaux et s'il contient au moins un chiffre et une lettre
                if(!ctype_alnum($password) && preg_match('#([a-z][0-9])#', $password)) {
                    // Si le recpatcha est bien déclaré en POST
                    if (isset($_POST['recaptcha_response']))
                    // Vérifie le recaptcha
                        $captcha->verify($_POST['recaptcha_response']);
                        // Si true et donc si le score est valide
                        if ($captcha == true) {
                            
                            $admin = new AdminUser();
                            // Hash le mot de passe administrateur et hydrate l'entité AdminUser
                            $password = password_hash($password, PASSWORD_DEFAULT);
                            $admin->setUsername(htmlspecialchars(trim($_POST['username']), ENT_QUOTES));
                            $admin->setEmail(trim($email));
                            $admin->setPassword(trim($password));
                            
                            // Si le token post et le token session matchent
                            if (isset($_SESSION['token']) && $_POST['token'] == $_SESSION['token']) {
                                // Instance de Mailer
                                $mailer = new Mailer();
                                // Appel de la méthode correspondant au mail à envoyé
                                $message =  $mailer->confirmAdminRegister();
                                // S'il n'y a pas de problème d'envoi
                                if(empty($message)) {
                                    // Mise à jour des données en BDD
                                    $adminModel = new AdminUserModel();
                                    $adminModel->update($_SESSION['id_admin'], $admin);
                                    // On détruit la séssion username_admin afin de rediriger vers une nouvelle connexion administrateur
                                    // avec les nouveaux identifiant 
                                    unset($_SESSION['username_admin']);
                                    header('location:index.php?controller=authAdmin&action=index');
                                } else {
                                    $error = $language->get('unknonwUser');
                                }
                               
                            } else {
                                $error =  $language->get('unknonwUser');
                            }
                        } else {
                            $error =  $language->get('errorCaptcha');
                        }
                } else {
                    $error =  $language->get('errorPassword1');
                }
            } else {
            $error = $language->get('errorPassword2');
            }
        } else {
            $error = !empty($_POST) ? $language->get('errorForm') : "";
        }

        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {
            $form = new Form();

            $form->startForm('#', 'POST', ['id'=>'myForm', 'class' => 'bg-dark border border border-dark mb-3 p-2', 'novalidate' =>'']);
            $form->startFieldset('Nouvel identifiant ', '');
            $form->startDiv('form-floating');
            $form->addInput('text', 'username', ['id' => 'username', 'class'=> 'form-control mb-3 mt-3 bg-dark border border-secondary text-light', 'placeholder' => 'username', 'required' => '']);
            $form->addLabel('username', 'Username', ['class' => 'bg-dark bg-opacity-10']);
            $form->endDiv();
            $form->startDiv('form-floating');
            $form->addInput('text', 'email', ['id' => 'email', 'class'=> 'form-control mb-3 mt-3 bg-dark border border-secondary text-light', 'placeholder' => 'Email', 'required' => '']);
            $form->addLabel('email', 'Email', ['class' => 'bg-dark bg-opacity-10']);
            $form->endDiv();
            $form->startDiv('form-floating');
            $form->addInput('text', 'password', ['id' => 'password', 'class'=> 'form-control mb-3 mt-3 bg-dark border border-secondary text-light',  'placeholder' => ' Nouveau mot de passe', 'required' => '']);
            $form->addLabel('password', 'Mot de passe', ['class' => 'bg-dark bg-opacity-10']);
            $form->endDiv();
            $form->startDiv('form-floating');
            $form->addInput('text', 'confirmPassword', ['id' => 'confirmPassword', 'class'=> 'form-control mb-3 mt-3 bg-dark border border-secondary text-light',  'placeholder' => 'Confirmation de mot de passe', 'min-length' => '12', 'required' => '']);
            $form->addLabel('confirmPassword', 'Confirmation de mot de passe', ['class' => 'bg-dark bg-opacity-10']);
            $form->endDiv();
            $form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']) : null]);
            $form->addInput('hidden', 'recaptcha_response', ['id' => 'recaptchaResponse']);
            $form->addInput('submit', 'btnAuth',['id' => 'btnAuth', 'class'=>'btn btn-dark border border-danger', 'value' => 'Mettre à jour']);
            $form->endFieldset();
            $form->endForm();
    
            $this->render('admin/register', ['addLogForm' => $form->getFormElements(), 'error' => $error]);
        } else {
            header('location:index.php?controller=panelAdmin&action=index');
        }
       
    }

    /**
    * Valide l'authentification de l'administrateur 
    * 
    * @param string [email] récupère l'email de l'admin
    * @param string [password] récupère le mdp hashé afin de le tester
    * @param object [admin] récupère l'objet creation AdminUserModel;
    * 
    * @return string $error Le message d'erreur
    */
    private function validateAuth($email, $password, $admin): string
    {
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // Si la session n'existe pas
        if (!isset($_SESSION['username_admin'])) {
            // Si les champs ne sont pas vides et si token est bien déclaré
            if ($email != null && $password != null && isset($_SESSION['token']) && isset($_SESSION['token_time'])) {
                // Si la SESSION token correspond au POST Token
                if ($_SESSION['token'] == $_POST['token']) {
                    $timestamp = time() - 10 * 60;
                    // Si le jeton n'est pas expiré
                    if ($_SESSION['token_time'] >= $timestamp) {
                        // Si l'utilisateur existe et si le nbUser correspond
                        if ($admin) {
                            if (password_verify($password, $admin->password)) {
                                // Génère un nouvel PHPSESSID afin d'eviter un détournement de session
                                // et on stocke le nom d'utilisateur et l-ID puis on redirige vers la liste des réservations
                                session_regenerate_id();
                                $_SESSION['username_admin'] = $admin->username;
                                $_SESSION['id_admin'] = $admin->idAdmin;
                    
                                header("location:index.php?controller=panelAdmin&action=index");
                            } else {
                                $error = $language->get('errorAuth1');
                            }
                        } else {
                        $error =  $language->get('errorAuth2');
                        }
                    } else {
                    $error=  $language->get('errorAuth6');
                    }
                } else {
                $error = $language->get('errorAuth3');
                }
            } else {
            $error =  $language->get('errorAuth4');
            }
        } else {
        $error =  $language->get('errorAuth5') . $_SESSION['username_admin'];
        }
        return $error;
    }


    /**
     * Permet la deconnexion de l'administrateur
     */
    public function logout($token): void
    {   
        // Si le token est bien déclaré et s'il correspond à celui en GET
        if (isset($_GET['token'])  && $_GET['token'] == $_SESSION['token']) {
            // On détruit les sessions utilisateur et on redirige vers la page d'accueil
            session_unset();
            session_destroy();
            header('location:index.php?controller=home&action=index');
        // Sinon redirige vers la liste des réservations
        } else {
           header('location:index.php?controller=authAdmin&action=index');
        }
    }  
}

