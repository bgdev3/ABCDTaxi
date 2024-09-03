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
            $mail->Username   = '**********';                               //Identifiant SMTP
            $mail->Password   = '**********';                                     //password de l'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;                            //Active l'encriptage de l'envoi
            $mail->Port       = 465;                                                    //port SMTP
        
            //Recipients
            // email Kevin
            $mail->setFrom('**********');                      //Adresse d'envoi
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
        $email = "**********";
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
                                    
                                    </div>
                                    <div style="text-align:center">'. $language->get('numberCustomer2') .'</div>';
                }
                $subject = $language->get('subjectConfirm');
                $body = '
                        <!DOCTYPE html>
                        <html>
                        <body style="background-color:#1a1919; color:#e9e9e9; padding-top:1em; padding-bottom:1em;">
                            <header>
                                <h1 style="text-align:center; font-size:1.5em;">' . $language->get('titleMail') . '</h1>
                                ' . $infoPass . '
                            </header>
                            <main>
                                <section style="margin:2em 0; background-color: #131010; text-align:center">
                                
                                    <p style="font-size:1.4em; font-style:italic;">' . $language->get('mail_dateTransport') .'</p>
                                    <span style="font-size:1.4em;">' . $dateTransport->format('d-m-Y') . '<br></span>
                                    <p style="font-size:1.4em; font-style:italic;">' . $language->get('mail_departureTime') .'</p> 
                                    <span style="font-size:1.4em;">' .  $transport->getDeparture_time() . '<br></span>
                                    <p style="font-size:1.4em; font-style:italic;">' . $language->get('mail_departurePlace') .'</p>
                                    <span style="font-size:1.4em;"> '. $transport->getDeparture_place() . '<br></span>
                                    <p style="font-size:1.4em; font-style:italic;">' . $language->get('mail_destination') .'</p>
                                    <span style="font-size:1.4em;">' . $transport->getDestination().'<br></span>
                                    <p style="font-size:1.4em; font-style:italic;">' . $language->get('mail_nbPassengers') .'</p>
                                    <span style="font-size:1.4em;">' . $transport->getNbPassengers().'<br><br></span>
                                </section>
                            </main>
                            <footer style="margin: .5em 0;">
                                <p style="font-size:1rem; text-align:center">' . $language->get('mail_endConfirm1') .'<br>' . $language->get('mail_endConfirm2') .'  
                                    <a href="dev.abcdtaxi.fr" style="text-decoration:none; color:#850202; padding:.5em;">' . $language->get('mail_endConfirm3') .'</a>
                                </p>
                                <p style="font-weight:bold; font-style:italic; font-size:1em; text-align:center">'. $language->get('mail_endConfirm4') .'</p>
                            </footer> 
                       </body>
                       </html>';
                    break;

            case 'update' : 

                $subject = $language->get('subjectUpdate');
                $body = '
                        <!DOCTYPE html>
                        <html>
                        <body style="background-color:#1a1919; color:#e9e9e9; padding-top:5em; padding-bottom:5em">
                            <header>
                                <h1 style="text-align:center; font-size:1.5em;">' . $language->get('mail_titleUpdate') .' </h1>
                            </header>
                            <main>
                                <p style="font-size: 1rem; text-align:center; margin:5em 0; background-color: #131010; color:#850202;">' . $language->get('mail_contentUpdate1') .' </p>
                            </main>
                            <footer style="margin: .5em 0;">
                                <p style="font-size:1rem; text-align:center;">' . $language->get('mail_contentUpdate2') .' <br>' . $language->get('mail_contentUpdate3') .'   
                                    <a href="dev.abcdtaxi.fr" style="text-decoration:none; color:#850202; padding:.5em;">' . $language->get('mail_endConfirm3') .'</a>
                                </p>
                                <p style="font-weight:bold; font-style:italic; font-size:1em; text-align:center">'. $language->get('mail_endConfirm4') .'</p>
                            </footer> 
                        </body>
                       </html>';
                    break;

            case 'delete' :
                
                $subject = $language->get('subjectDelete');
                $body = '
                        <!DOCTYPE html>
                        <html>
                        <body style="background-color:#1a1919; color:#e9e9e9; padding-top:5em; padding-bottom:5em">
                            <header>
                                <h1 style="text-align:center; font-size:1.5em;">' . $language->get('mail_titleUpdate') .' </h1>
                            </header>
                            <main>
                                <p style="font-size: 1rem; text-align:center; background-color: #131010; margin:5em 0; color:#850202;">' . $language->get('mail_contentDelete1') .' </p>
                            </main>
                            <footer style="margin: .5em 0;">
                                <p style="font-size:1rem;  text-align:center;">' . $language->get('mail_contentDelete2') .'  
                                    <a href="dev.abcdtaxi.fr" style="text-decoration:none; color:#850202;font-size:1.4em; padding:.5em;">' . $language->get('mail_contentDelete3') .' </a>
                                </p>
                                <p style="font-weight:bold; font-style:italic; font-size:1em; text-align:center">' . $language->get('mail_endConfirm4') .' </p>
                            </footer>
                        </body>
                       </html>';
                        
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
                  <!DOCTYPE html>
                        <html>
                        <body style="background-color:#1a1919; color:#e9e9e9; padding-top:1em; padding-bottom:1em">
                            <header>
                                <h1 style="text-align:center; color:#850202; font-size:1.5em;">Nouvelle réservation : </h1>
                            </header>
                            <main>
                                <p style="font-size:1.4em; text-align:center; font-style:italic;">Transport aller-retour : ' . $transport->getRoundTrip() . '</p>
                                <p style="font-size:1.4em; text-align:center; font-style:italic;">Temps d\'attente estimée : ' . $transport->getEstimated_wait() . ' minutes</p>
                                <p style="font-size:1.4em; text-align:center; font-style:italic;">Nb de passagers : ' . $transport->getNbPassengers() . '</p>
                                
                                <section style="padding:5px; background-color: #131010; text-align:center; margin:2em 0;">
                                    <p style="font-size:1.4em; font-style:italic;">Nom : ' . $user->name  . '</p>
                                    <p style="font-size:1.4em; font-style:italic;"Prénom : ' . $user->surname  . '</p>
                                    <p style="font-size:1.4em; text-decoration:none; font-style:italic;">Email :'  . $user->email . '</p>
                                    <p style="font-size:1.4em; font-style:italic;">Tel : ' . $user->tel .  ' </p>
                                    <p style="font-size:1.4em; font-style:italic;">Date transport : ' . $dateTransport->format('d-m-Y') . '</p>
                                    <p style="font-size:1.4em; font-style:italic;">Heure de prise en charge : ' .  $transport->getDeparture_time() . '</p> 
                                    <p style="font-size:1.4em; font-style:italic;">Lieu de départ : '. $transport->getDeparture_place() . '</p>
                                    <p style="font-size:1.4em; font-style:italic;">Destination : ' . $transport->getDestination() .'</p>
                                    <p style="font-size:1.4em; font-style:italic;">Estimation devis : ' . $transport->getPrice() .' &euro;</p>
                                </section>
                            </main>
                        </body>
                       </html>';
                break;

            case 'update' : 

                $subject = "Modification de transport";
                $body =  '
                        <!DOCTYPE html>
                        <html>
                        <body style="background-color:#1a1919; color:#e9e9e9;  padding-top:1em; padding-bottom:1em;">
                            <header>
                                <h1 style="text-align:center; color:#850202; font-size:1.5em;">Modification de transport </h1>
                            </header>
                            <main>
                                <section style="padding:5px; background-color: #131010; text-align:center; margin:2em 0;">
                                    <p style="font-size:1.4em; font-style:italic;">Nom : ' . $user->name  .  '</p>
                                    <p style="font-size:1.4em; font-style:italic;">Prénom : ' . $user->surname  .  '</p>
                                    <p style="font-size:1.4em; text-decoration:none; font-style:italic;">Email : '  . $user->email .   '</p>
                                    <p style="font-size:1.4em; font-style:italic;">Tel : ' . $user->tel .  '</p>
                                    <p style="font-size:1.4em; font-style:italic;">Date transport :' . $dateTransport->format('d-m-Y') . '</p>
                                    <p style="font-size:1.4em; font-style:italic;">Heure de prise en charge : ' .  $transport->getDeparture_time() . '</p>
                                </section>
                            </main>
                        </body>
                       </html>';
                break;

            case 'delete' :
                
                $subject = "Suppression de transport";
                $body = '
                        <!DOCTYPE html>
                        <html>
                        <body style="background-color:#1a1919; color:#e9e9e9;  padding-top:1em; padding-bottom:1em">
                            <header>
                                <h1 style="text-align:center; color:#850202; font-size:1.5em;">Annulation de transport </h1>
                            </header>
                            <main>
                                <section style="padding:5px; background-color: #131010; text-align:center; margin:2em 0;">
                                    <p style="font-size:1.4em; font-style:italic;">Nom : ' . $user->name  .  '</p>
                                    <p style="font-size:1.4em; font-style:italic;">Prénom : ' . $user->surname  .  '</p>
                                    <p style="font-size:1.4em; text-decoration:none; font-style:italic;">Email :'  . $user->email .   '</p>
                                    <p style="font-size:1.4em; font-style:italic;">Tel : ' . $user->tel .  '</p>
                                </section>
                            </main>
                        </body>
                       </html>';
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
                        <!DOCTYPE html>
                        <html>
                        <body style="background-color:#1a1919; color:#e9e9e9; padding-top:5em; padding-bottom:5em">
                            <header>
                                <h1 style="text-align:center; color:#850202; font-size:1.5em;">Nouvelle demande </h1>
                            </header>
                            <main>
                                <section style="background-color: #131010; text-align:center">
                                    <h1 style="text-align:center; color:#850202; font-size:2em;">' . $_post['object'] . '</h1>
                                    <p style="font-size:1.4em; font-style:italic;"> Nom : ' . $_post['name'] .  '</p>
                                    <p style="font-size:1.4em; font-style:italic; "> Prénom : ' . $_post['surname'] .  '<br></p>
                                    <p style="font-size:1.4em; text-decoration:none; font-style:italic; "> Email: '  . $_post['mail'] .   ' <br></p>
                                    <p style="font-size:1.4em; font-style:italic; "> Téléphone : ' . $_post['tel'] .  ' <br></p>
                                    <p style="font-size:1.4em;"><em>Message</em> : </p>
                                    <span style="font-size:1em">' . $_post['message']. '<br></span>
                                </section>
                            </main>
                        </body>
                       </html>';
    
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
        $email = '**********';
        $body = '
                    <!DOCTYPE html>
                    <html>
                    <body style="background-color:#1a1919; color:#e9e9e9; padding-top:5em; padding-bottom:5em">
                        <header>
                            <h1 style="text-align:center; color:#850202; background-color: #131010; font-size:1.5em;">Nouvel identifiant</h1>
                        </header>
                        <main>
                            <section style="border:1px solid #850202; text-align:center">
                                <h1 style="text-align:center; color:#850202; font-size:2em;">Renouvellement de vos identifiants</h1>
                                <p style="font-size:1.4em; ">Les accès à votre espace administrateur ont été renouvelés.</p>
                                <p>Si vous n\'en êtes pas à l\'origine, veuillez contacter votre service client dans les plus brefs délais !</p>
                            </section>
                        </main>
                    </body>
                    </html>';

    $content = [];
    array_push($content, $email, $subject, $body);

    // Appel à la méthode d'envoi de mail
      $message = $this->sendMail($content);
    return $message;
    }
}
