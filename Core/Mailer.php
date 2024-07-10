<?php 
namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Models\ClientModel;
use App\Entities\Transport;
use App\Core\Language;
use DateTime;

/**
 * Classe qui gère l'envoi des différents infomrations
 * issu des formulaires par mails.
 */

class Mailer 
{

    /**
     * Permet le paramètrage du mail en SMTP
     * 
     * @param array [$content] Contient les données à afficher
     * @param array [$file] Paramètre facultatif ( si un fichier est transmis)
     * 
     * @return string [message] Contient le message d'erreur
    */
    private function sendMail($content, $file = null): string
    {

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                                   //Enable verbose debug output
            $mail->isSMTP();                                                            //Envoi en SMTP
            $mail->Host       = 'smtp.gmail.com';                                       //Adresse serveur SMTP
            $mail->SMTPAuth   = true;                                                   //Active l'authentification
            $mail->Username   = 'boukehaili.g@gmail.com';                               //Identifiant SMTP
            $mail->Password   = 'hekfmufaowzithcv';                                     //password de l'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;                            //Active l'encriptage de l'envoi
            $mail->Port       = 465;                                                    //port SMTP
        
            //Recipients
            $mail->setFrom('boukehaili.g@gmail.com', 'ABCD Taxi');                      //Adresse d'envoi
            $mail->addAddress($content[0]);                                             //Destinatire
        
            // //Attachments
            if($file)                                                                    //Si le paramètre facultatif est présent        
            $mail->addAttachment($file['file']['tmp_name'], $file['file']['name']);     //Pièce jointe : chemin du fichier temporaire, nom du fichier
        
            //Content
            $mail->isHTML(true);                                                        //Format HTML activé
            $mail->CharSet= 'UTF-8';                                                    //Encodage utf-8
            $mail->Subject = $content[1];                                               //Email 
                            
            $mail->Body    = $content[2];                                               //Corps du mail
                               
            $mail->send();                                                              //Envoi
            $message="";
            // Si l'envoi ne s'effectue pas un eexception est levé et on affiche un message d'erreur
        } catch (Exception $e) {
            // Error à supprimer avant l amise en ligne !!!!
            $message =  "L'email n'a pu être envoyé ! Merci de recommencer.";
            
        }
        // On retourne le message s'il y en a un.
        return $message;
    }


    /**
     * Permet de paramétrer les arguments de la méthode contentMailUser
     * selon les éléments passés dans le controller afin de retourner le bon contenu de mail
     * Gère l'envoi simultanées à l'utilisateur et au client selon qu'il y ai un message d'erreur ou non
     * en passant les données corespodantes à sendMail
     * 
     * @param string [$email] Email récupèrer de l'utlisateur
     * @param string [$action] Permet de tester l'action effectuée
     * @param object [$transport] Afin de profiter des assesseurs pour transmettre les informations de transport - peut être null
     * @param string [$passUser] Contient le num client s'il est transmis
     * 
     * @return string [$message] Message d'erreur
     */
    public function sendUserMail($email, $action, Transport $transport = null,  $passUser = null): string
    { 
        global $data;
        // Si l'email et  l'entité sont passés en argument et sont déclarés
        if (isset($email) || isset($transport)) {
            // Si $transport est déclaré, appel de contentMailUser avec certains argument 
            if (isset($transport)) {
                // Si passUser est déclaré on adapte les argumùent de la methode et on récupère les données de retour
                $data =  isset($passUser) ? $this->contentMailUser($action, $transport, $passUser) : $this->contentMailUser($action, $transport);
                // Sinon si $transport n'est pas déclaré, on nepasse que l'action à teste en argument
            } else {
                $data = $this->contentMailUser($action);
            }
                   
            // Email utilisateur
            $subject = $data[0];
            // Contenu du mail
            $body = $data[1];
            $content = [];
            // Récupère dans un tableau les données
            array_push($content, $email, $subject, $body);
            // appelle la fonction d'envoi. S'il n y a pas de message d'erreur, que l'on est dans le TRY 
            // de la methode d'envoi et donc que l'envoi s'est bien déroulé, on envoi en même temps les infos au client 
            // Sinon aucun mail n'est envoyé
            $message = $this->sendMail($content);
           
            // Si pas d'erreur
            if (empty($message)) {
                // Si transport est déclaré
                if (isset($transport)) {
                    // Envoi l'une ou l'autre methdde
                    $this->sendClientMail($email, $action, $transport);
                } else {
                    $this->sendClientMail($email, $action);
                }
            } 
        // Sino affiche le message d'erreur
        } else {
            $message = "L'email n'a pu être envoyé ! Merci de recommencer.";
        }
        return $message;
    }


    /**
    * Récupère l'email, l'action et l'objet transport si present, 
    * afin de récupèrer et d'afficher le bon contenu de mail client selon l'action utilisé

    * @param string [$email] Email récupèrer de l'utlisateur
    * @param string [$action] Permet de tester l'action effectuée
    * @param object [$transport] Afin de profiter des assesseurs pour transmettre les informations de transport - peut être null
    */
    private function sendClientMail($email, $action, Transport $transport = null ): void
    {
        global $data;
        // Instance de userModel afin de récupèrer les infos utilisateurs correspondantes
        $model = new ClientModel();
        // Récupère l'enregistrement
        $user = $model->find($email);
        // si $user et $action sont déclarés
         if (isset($user) && isset($action)) {
            // On assigne le retour de la méthode selon les arguments passés
                 $data =  !isset($transport) ? $this->contentMailClient($action, $user) : $this->contentMailClient($action, $user, $transport);
        }
        // Assigne les données récupérées et les stockes dans un array
        $subject = $data[0];
        $body = $data[1];
        // Email client
        $email = "boukehaili.g@gmail.com";
        $content = [];
        array_push($content, $email, $subject, $body);
        // Appel à la méthode d'envoi 
        $this->sendMail($content);
    }
 

    /**
    * Récupère les données de la méthode parentes et teste l'action effectué par l'utilisateur
    * afin de retourner le contenu du mail correspondant 
    *
    * @param string [$action] Permet de tester l'action effectuée
    * @param object [$transport] Afin de profiter des assesseurs pour transmettre les informations de transport - peut être null
    * @param string [$passUser] Contient le num client s'il est transmis - peut être null
    * @return array [$content]
    */
    private function contentMailUser($action, $transport = null,  $passUser = null): array
    {

        $content = [];
        global $subject, $body;

        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        $language = new Language($lang);
        // teste avec un switch la valeur de $action
        switch ($action) {

            case 'confirm' :
                 // Formate la date avant de l'insérer
                $dateTransport = new DateTime($transport->getDateTransport());
                // Si $passUser est transmis, affiche le numero client
                $infoPass = $passUser;
                if (!isset($passUser)) {
                    $infoPass ="<p style='text-align:center; font-size: 1rem; color:#850202; '>". $language->get('noneNumberCustomer') ."</p>";
                } else {
                    $infoPass = '   <p style="font-size:1em; text-align:center">'. $language->get('numberCustomer1') .'</p>
                                    <div style="font-size:1.5em; padding:.5em; background-color:#850202; text-align:center; width:50%; margin:0 auto;">'
                                        .  $passUser . ' 
                                    </div><br><span>'. $language->get('numberCustomer2') .'</span>';
                }
                $subject = $language->get('subjectConfirm');
                $body = '
                        <h1 style="text-align:center; font-size:1.5em;">' . $language->get('titleMail') . '</h1>
                         ' . $infoPass . '
                        <section style="margin:2em 0; border:1px solid #131010; text-align:center">
                           
                            <p style="font-size:1.4em;"><strong>' . $language->get('mail_dateTransport') .' </strong></p>
                            <span style="font-size:1.4em;">' . $dateTransport->format('d-m-Y') . '<br></span>
                            <p style="font-size:1.4em;"><strong>' . $language->get('mail_departureTime') .'</strong></p> 
                            <span style="font-size:1.4em;">' .  $transport->getDeparture_time() . '<br></span>
                            <p style="font-size:1.4em;"><strong>' . $language->get('mail_departurePlace') .'</strong></p>
                            <span style="font-size:1.4em;"> '. $transport->getDeparture_place() . '<br></span>
                            <p style="font-size:1.4em;"><strong>' . $language->get('mail_destination') .'</strong></p>
                            <span style="font-size:1.4em;">' . $transport->getDestination().'<br><br></span>
                             <p style="font-size:1.4em;"><strong>' . $language->get('mail_nbPassengers') .'</strong></p>
                            <span style="font-size:1.4em;">' . $transport->getNbPassengers().'<br><br></span>
                        </section>
                        <div style="margin: .5em 0;">
                            <p style="font-size:1rem; text-align:center">' . $language->get('mail_endConfirm1') .'<br>' . $language->get('mail_endConfirm2') .'  
                                <a href="dev.abcdtaxi.fr" style="text-decoration:none; color:#850202; padding:.5em;">' . $language->get('mail_endConfirm3') .'</a>
                            </p>
                        </div> 
                        <p style="font-weight:bold; font-style:italic; font-size:1.5em; text-align:center">'. $language->get('mail_endConfirm4') .'</p>';
                       
                    break;

            case 'update' : 

                $subject = $language->get('subjectUpdate');
                $body = '
                        <h1 style="text-align:center; font-size:1.5em;"' . $language->get('mail_titleUpdate') .' </h1>
                        <p style="font-size: 1rem; text-align:center; margin:5em 0; color:#850202;">' . $language->get('mail_contentUpdate1') .' </p>
                        <div style="margin: .5em 0;">
                            <p style="font-size:1rem; text-align:center;">' . $language->get('mail_contentUpdate2') .' <br>' . $language->get('mail_contentUpdate3') .'   
                                <a href="dev.abcdtaxi.fr" style="text-decoration:none; color:#850202; padding:.5em;">' . $language->get('mail_endConfirm3') .'</a>
                            </p>
                        </div> 
                         <p style="font-weight:bold; font-style:italic; font-size:1em; text-align:center">'. $language->get('mail_endConfirm4') .'</p>'; 
                break;

            case 'delete' :
                
                $subject = $language->get('subjectDelete');
                $body = '
                        <h1 style="text-align:center; font-size:1.5em;">' . $language->get('mail_titleUpdate') .' </h1>
                        <p style="font-size: 1rem; text-align:center; margin:5em 0; color:#850202;">' . $language->get('mail_contentDelete1') .' </p>
                        <div style="margin: .5em 0;">
                            <p style="font-size:1rem;  text-align:center;">' . $language->get('mail_contentDelete2') .'  
                                <a href="dev.abcdtaxi.fr" style="text-decoration:none; color:#850202;font-size:1.4em; padding:.5em;">' . $language->get('mail_contentDelete3') .' </a>
                            </p>
                        </div>
                        <p style="font-weight:bold; font-style:italic; font-size:1.5em; text-align:center">' . $language->get('mail_endConfirm4') .' </p>'; 
                break;
        }
        
        array_push($content, $subject, $body);
        return $content;
    }

    
    /**
    * Récupère les données de la méthode parentes et teste l'action effectué par l'utilisateur
    * afin de retourner le contenu du mail correspondant 

    * @param string [$action] Permet de tester l'action effectuée
    * @param object [$user] Stocke les données utilisateurs
    * @param object [$transport] Afin de profiter des assesseurs pour transmettre les informations de transport - peut être null
    * @return array [$content]
    */
    private function contentMailClient ($action, $user, $transport = null): array
    {

        $content = [];
        global $subject, $body;

        // Formate la date avant de l'insérer
        // uniquement si l'objet transport est présent
        if(isset($transport))
        $dateTransport = new DateTime($transport->getDateTransport());
        // teste avec un switch la valeur de $action
        switch ($action) {

            case 'confirm' :
                
                $subject = 'Nouvelle réservation';
                $body = '
                        <h1 style="text-align:center; color:#850202; font-size:1.5em;">Nouvelle réservation : </h1>
                        <p style="font-size:1.4em; text-align:center; "><strong>Transport aller-retour : </strong>' . $transport->getRoundTrip() . '</p>
                        <p style="font-size:1.4em; text-align:center;"><strong>Temps d\'attente estimée : </strong>' . $transport->getEstimated_wait() . ' minutes</p>
                        <p style="font-size:1.4em; text-align:center;"><strong>Nb de passagers : </strong>' . $transport->getNbPassengers() . '</p>
                        
                        <section style="padding:5px; text-align:center; margin:2em 0; border:1px solid #131010;">
                            <p style="font-size:1.4em; "><strong>Nom : </strong>' . $user->name  . '</p>
                            <p style="font-size:1.4em; "<strong>Prénom : </strong>' . $user->surname  . '</p>
                            <p style="font-size:1.4em; text-decoration:none; "><strong>Email :</strong> <strong>'  . $user->email . '</p>
                            <p style="font-size:1.4em; "><strong>Tel : </strong>' . $user->tel .  ' </p>
                            <p style="font-size:1.4em;"><strong>Date transport : </strong>' . $dateTransport->format('d-m-Y') . '</p>
                            <p style="font-size:1.4em;"><strong>Heure de prise en charge : </strong>' .  $transport->getDeparture_time() . '</p> 
                            <p style="font-size:1.4em;"><strong>Lieu de départ : </strong>'. $transport->getDeparture_place() . '</p>
                            <p style="font-size:1.4em;"><strong>Destination : </strong>' . $transport->getDestination() .'</p>
                            <p style="font-size:1.4em;"><strong>Estimation devis : </strong>' . $transport->getPrice() .' &euro;</p>
                        </section>';
                break;

            case 'update' : 

                $subject = "Modification de transport";
                $body =  '
                        <h1 style="text-align:center; color:#850202; font-size:1.5em;">Modification de transport </h1>
                        <section style="padding:5px; text-align:center; margin:2em 0; border:1px solid #131010;">
                            <p style="font-size:1.4em; "><strong>Nom : </strong>' . $user->name  .  '</p>
                            <p style="font-size:1.4em; "><strong>Prénom : </strong>' . $user->surname  .  '</p>
                            <p style="font-size:1.4em; text-decoration:none; "><strong>Email : </strong>'  . $user->email .   '</p>
                            <p style="font-size:1.4em; "><strong>Tel : </strong>' . $user->tel .  ' </strong></p>
                            <p style="font-size:1.4em;"><strong>Date transport : </strong>' . $dateTransport->format('d-m-Y') . '</p>
                            <p style="font-size:1.4em;"><strong>Heure de prise en charge : </strong>' .  $transport->getDeparture_time() . '</p>
                        </section>';
                break;

            case 'delete' :
                
                $subject = "Suppression de transport";
                $body = '
                        <h1 style="text-align:center; color:#850202; font-size:1.5em;">Annulation de transport </h1>
                        <section style="padding:5px; text-align:center; margin:2em 0; border:1px solid #131010;">
                            <p style="font-size:1.4em; "><strong>Nom : </strong>' . $user->name  .  '</p>
                            <p style="font-size:1.4em; "><strong>Prénom : </strong>' . $user->surname  .  '</p>
                            <p style="font-size:1.4em; text-decoration:none; "><strong>Email : </strong>'  . $user->email .   '</p>
                            <p style="font-size:1.4em; "><strong>Tel : </strong>' . $user->tel .  '</p>
                        </section>';
                break;
        }
        array_push($content, $subject, $body);
        return $content;
    }


    /**
     * Formate le mail Contact
     * @param array [$_post] Contient les informations envoyé en post
     * @param array [$file] Paramètre facultatif contenant les pièces jointes
     * @return string [$message] Message d'erreur retourné
     */
    public function sendContact($_post, $file = null): string  
    {
        // Si les arguments dont bien déclarés on creer le template

        if (isset($_post)) {

            $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
            $language = new Language($lang);
            
            $subject = $_post['object'];
            $email = 'boukehaili.g@gmail.com';
            $body = '
                    <section style="border:1px solid #850202; text-align:center">
                        <h1 style="text-align:center; color:#850202; font-size:2em;">' . $_post['object'] . '</h1>
                        <p style="font-size:1.4em; "> Nom : ' . $_post['name'] .  '</p>
                        <p style="font-size:1.4em; "><strong> Prénom : ' . $_post['surname'] .  '<br></p>
                        <p style="font-size:1.4em; text-decoration:none; "> Email: <strong>'  . $_post['mail'] .   ' </strong><br></p>
                        <p style="font-size:1.4em; "> Téléphone : <strong>' . $_post['tel'] .  ' </strong><br></p>
                        <p style="font-size:1.4em;">Message : </p>
                        <span style="font-size:1.4em;"><strong>' . $_post['message']. '</strong><br></span>
                    </section>';
    
        $content = [];
        array_push($content, $email, $subject, $body);
    
        // Appel à la méthode d'envoi de mail
          $message = $this->sendMail($content, $file);
        // Sinon on affcihe un message d'ereur
        } else {
            $message = "L'email n'a pu être envoyé ! Merci de recommencer.";
        }
        return $message;
    }


    /**
     * Formate le mail de confirmation de changement de mdp
     * @return string [$message] Message d'erreur
     */
    public function confirmAdminRegister(): string
    {
        $subject = "Renouvellement de vos identifiants";
        $email = 'boukehaili.g@gmail.com';
        $body = "
                <section style='border:1px solid #850202; text-align:center'>
                    <h1 style='text-align:center; color:#850202; font-size:2em;'>Renouvellement de vos identifiants</h1>
                    <p style='font-size:1.4em; '>Les accès à votre espace administrateur ont été renouvelés.</p>
                    <p>Si vous n'en êtes pas à l'origine, veuillez contacter votre service client dans les plus brefs délais !</p>
                </section>";

    $content = [];
    array_push($content, $email, $subject, $body);

    // Appel à la méthode d'envoi de mail
      $message = $this->sendMail($content);
    return $message;
    }
}
