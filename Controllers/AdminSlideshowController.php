<?php
namespace App\Controllers;

use App\Core\Form;
use App\Core\Language;
use App\Entities\SlideshowAdmin;
use App\Models\SlideshowAdminModel;

session_start();

class AdminSlideshowController extends Controller
{

    /**
     * Affiche les diapos et le formulaired d'upload
     * Sauvegarde les différentes diapos sélectionné par l'admin
     * Récupère en post l'image, la redimensionne et la fomrate en .webp avant de l'uploadé
     * 
     * @param string [$token] Clé de sécurité
     */
    public function index($token): void
    {
        global $error;
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        
        $typeFile = array('jpg'=>'image/jpg', 'jpeg'=>'image/jpeg', 'webp'=>'image/webp');
            // Si le fichier est déclaré et non vide
            if (Form::validateFiles($_FILES, ['picture'])) {
                //  Si l'erreur est vide on récupère l'erreur éventuelle retourné
                // par errorUpload qui teste le fichier
                $error = empty($erreur) ? Form::errorUpload($_FILES, ['picture'], $typeFile) : "" ;
                // Renomme le fichier
                $file = Form::formateFile($_FILES, ['picture']);

                // Array associatif de données permettant d'afficher les bonnes photos RWD
                $dataTest = array('normal'=> array('w' => 1350,'h' => 495, 'normal/'),
                                  'medium' => array('w' => 800,'h' => 300, 'medium/'),
                                  'small' => array('w' => 500,'h' => 183, 'small/'));

                // Si l'upload du fichier s'est bien déroulé
                if (empty($error)) {
                    // Boucle sur le premier index
                    foreach ($dataTest as $key => $val) {
                        // Permet de stocker les données du sous-tableau
                        $size = [];
                        // Si l'index est bien présent
                        if (is_array($val)) {
                            // On assigne dans $size les dimensions et le nom du sous dossier
                            foreach ($val as $k => $value) {
                               array_push($size, $value);
                            }
                        }
                        // Assigne le format de photo au fichier redimensionne l'image en récuprant 
                        // le chemin de la nouvelle image
                        $path =  $size[2] . $file;
                        $picture = $this->imageSize( $path, $size[0],  $size[1]);

                        // Hydrate l'entité
                        $slide = new SlideshowAdmin();
                        $slide->setPicture_path($picture);
                        $slide->setSize_slide($key);
                        // Si le token en post correspond afin d'eviter une faille CSRF
                        // On crée un nouvel enregistrement 
                        if (isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {

                            $model = new SlideshowAdminModel();
                            $model->create($slide);
                            // Redirige vers la page afin d'éviter un envoi automatique 
                            // du formulaire en cas de rechargement de page.
                            header('location:index.php?controller=adminSlideshow&action=index&token='. trim($_SESSION['token']));
                        // Sinon on affiche une erreur
                        } else {
                            $error = $language->get('unknownUser');
                        }
                    }
                }
            // Sinon une erreur est retourné
            } else {
                if (isset($_FILES['picture'])) {
                    $error .= $language->get('errorUploadFile') . Form::errorUpload($_FILES, ['picture'], $typeFile);
                 }
            }
        
        // Si les tokens GET et SESSION correspondent
        if (isset($_GET['token']) && $_GET['token'] && $_SESSION['token']) {

            $form = new Form();
            // Création du formulaire
            $form->startForm('', 'POST', ['id' => 'myForm', 'class' => 'bg-transparent border border-secondary text-center mx-auto mb-3 p-2 ','enctype' => 'multipart/form-data']);
            $form->addLabel('picture', 'Ajouter une photo');
            $form->addInput('file', 'picture', ['id' => 'picture', 'class' => 'form-control mt-3 mb-3']);
            $form->addInput('hidden', 'token',['id'=>'hidden',' value' => isset($_SESSION['token']) ? trim($_SESSION['token']) : null]);
            $form->addInput('submit', 'btnFile',['id' => 'btnFile', 'class'=>'btnAdmin btn btn-dark text-danger', 'value' => 'Envoyer']);
            $form->endForm();
    
            // Si l'admin n'est pas connecté on ridirige vers l'accueil
            if (!isset($_SESSION['username_admin'])) {
                header('location:index.php');
            } else {
                // Récupère les slides correspondant au format d'image testé
                // puis affiche les données récupérées
                $model = new SlideshowAdminModel();
                $slides = $model->findAll($_SESSION['size']);
                $this->render('admin/slideshow', ['fileForm' => $form->getFormElements(), 'slides' => $slides, 'error' => $error]);
            }
        } else {
            header('location:index.php?controller=panelAdmin&action=index');
        }    
    }


    /**
     * 
     * Permet le redimensionnement des images dans 3 formats
     * afin de l'adapter pour le RWD
     * 
     * @param string [$path] Chemin du fichier
     * @param int [$w] Largeur de redimensionnement de l'image voulu
     * @param int [$h] Hauteur de redimensionnement de l'image voulu
     * 
     * @return string [$destination] Retourrne le chemin de l'image redimensionnée
     */
    private function imageSize($path, $w, $h): string
    {
        // Récupère le dirname, l'extension, et le noim du fichier
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $ext =  pathinfo($path, PATHINFO_EXTENSION);
        $name = pathinfo($path, PATHINFO_FILENAME);
        // Crée le chemin du fichier
        $destination =  'image/' . $dir . '/' . $name . '.webp';

        // Déplace le fichier dans le dossier correspondant s'il n'est pas présent
        if (file_exists( $destination)) {
            $error = $name . '.webp' . " déja existant !";
        } else {
            move_uploaded_file($_FILES['picture']["tmp_name"],  'image/normal/'. $name . '.webp');
        }
      
        // Copie le fichier dans des dossier correspondant le RWD
        $src = 'image/normal/' . $name. '.webp';
        copy($src, $destination);
        
        // Récupère les dimension du fichier source
        $size = getimagesize($destination);
        $width = $size[0];
        $height = $size[1];
       
        // Array permettant d'appeler la bonne méthode selon l'extension
        // afin de stocker le format d'image
        $handler = array(
        'jpg' => 'imagecreatefromjpeg', 'png' => 'imagecreatefrompng', 'webp' => 'imagecreatefromwebp', 
                'new' => array('jpg' => 'imagejpeg', 'png' => 'imagepng', 'webp' => 'imagewebp') );

        //Appel la bonne méthode imagecreatefrom**
        $image = $handler[$ext]($destination);

        // Créer une image de fond par default
        $new_image = imagecreatetruecolor($w, $h);
        // Créer la copie de l'image
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $w, $h, $width, $height);
        // Créer l'image dans le format voulu
        // en appelant la bonne méthode image**
        $handler['new'][$ext]($new_image, $destination, 100);
       
        // Détruit l'image source de la mémoire
        imagedestroy($new_image);

        return  $destination;
    }


    /**
     * Récupère la largeur d'écran en pixel 
     * afin de récupèrer les bonnes images redimensionnées sur le serveur
     * Puis renvoi le tableau de données au JS afin d'afficher les images récupérées
     */
    public function addSlide(): void
    {
        // Récupère la taille d'écran 
        $content = trim(file_get_contents("php://input"));
        $data = json_decode($content, true);
        global $size;
        // Assigne la valeur selon les pixels retournés
        // dans une session afin de stocker le fomrat de photos à intégrer
           if ($data  >= 1024) {

                $_SESSION['size'] = 'normal';
                $size = array ('w' => 1350,'h' => 495);

           } elseif ($data > 576 && $data  <= 1024) {

                $_SESSION['size'] = 'medium';
                $size = array ('w' => 800,'h' => 300);

           } elseif ($data <= 576) {

                $_SESSION['size'] = 'small';
                $size = array ('w' => 500,'h' => 183);
            }
        // Instancie le modèle et effectrue une lecture de la table
        // des enregistrements correspondants à la valeur donnée
        $model = new SlideshowAdminModel();
        $slides = $model->findAll($_SESSION['size']);
        
        $slide = array('slides'=> $slides, 'size' => $size);

        // retourne les chemins des images afin de les affiché coté client
        echo json_encode($slide);
    }


    /**
     * Permet la suppression d'un slide et de ses différents formats stocké en BDD et dans le dossier cible
     * 
     * @param int [$id] Identifiant du slide sélectionné
     * @param int [$token] Token de sécurité passé en get
     */
    public function deleteSlide($id, $token): void
    {
        // Si l'id et le token sont déclarés et si les tokens matches
        if (isset($_GET['id']) && isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {
            // Récupère l'enregistrement correspondant
            $model = new SlideshowAdminModel();
            $path_slide = $model->findPath($id);
            // Extrait le nom du fichier
            $path = explode('/', $path_slide->picture_path);
            $nameSlide= $path[2];            

            // Supprime des dossier le slide stocké en bouclant sur les 3
            $size = ['small', 'medium', 'normal'];
           foreach($size as $el) {
            unlink('image/' . $el . '/' . $path[2]);
           };
        //    Supprime les diiférents format du slide stcokés en BDD
            $model->delete($id, $nameSlide);
            // Redirige sur la page des diapos
            header('location:index.php?controller=adminSlideshow&action=index&token=' . trim($_SESSION['token']));
        }
    }
}