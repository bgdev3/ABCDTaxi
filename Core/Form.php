<?php
namespace App\Core;

class Form 
{

    // Attribut contenant le code du formulaire
    private $formElements;

    public function getFormElements()
    {
        return $this->formElements;
    }

    private function addAttributes(array $attributes): string 
    {
        $att = "";
        // Chaque attribut est parcouru
        foreach($attributes as $attribute => $value){
            // On stocke chaque attribut et sa valeur dans la valriable $att
            $att .= "$attribute=\"$value\""; 
        }
        return $att;
    }

    //Méthode permettant de génerer la balise ouvrante HTML du formulaire
    public function startForm(string $action = "#", string $method = "POST", array $attributes = []): self 
    {
        // On commence le formulaire par l'ouverture de la balise <form> et ses attributs action et method
        $this->formElements = "<form action='$action' method = '$method'";
        // et ses attribts éevntuels
        $this->formElements .= isset($attributes) ? $this->addAttributes($attributes) . ">" : ">";
        return $this;
    }

    // Methode permettant d'ajouter un label
    public function addLabel(string $for, string $text, array $attributes =[]): self 
    {
        // On ajoute la balise label et l'attribut for
        $this->formElements .= "<label for='$for'";    
        $this->formElements .= isset($attributes) ? $this->addAttributes($attributes) . ">" : ">";
        $this->formElements .= "$text</label>";
        return $this;
    }

    // Methode permettant d'ajouter un champs
    public function addInput(string $type, string $name, array $attributes = []): self 
    {
        // On ajoute la balise input et les attribut input et name
        $this->formElements .= "<input type='$type' name='$name'";
        $this->formElements .= isset($attributes) ? $this->addAttributes($attributes) . ">" : ">";
        return $this;
    }

    // Methode permettant d'ajouter un textarea
    public function addTextaera(string $name, string $text = '', array $attributes = []): self 
    {
        //On ajoute la balise textearea et l 'attribut name
        $this->formElements .= "<textarea name='$name'";
        $this->formElements .= isset($attributes) ? $this->addAttributes($attributes) . ">" : ">";
        $this->formElements .= "$text</textarea>";
        return $this;
    }

    // Methode permettant d'ajouter un select
    public function addSelect(string $name, array $options, array $attributes = []): self 
    {
        // On ajoute la balise select et l'attribut name
        $this->formElements .= "<select name = '$name'";
        $this->formElements .= isset($attributes) ? $this->addAttributes($attributes) . ">" : ">";
        // On ajoute la ou les balises options avec leur valeur

        $this->formElements .= "<option selected disabled>Sélectionner</option>";
        foreach ($options as $key=>$value) {
            $this->formElements .= "<option class='bg-dark text-light border border-secondary ' value ='$key'>$value</option>";
        }
        $this->formElements .= "</select>";
        return $this;
    }
    // Permet d'ajouter un small
    public function addSmall(string $text, array $attributes = []): self
    {
        $this->formElements .= "<small ";
        $this->formElements .= isset($attributes) ? $this->addAttributes($attributes) . ">" : ">";
        $this->formElements .= "$text</small>";
        return $this;
    }

    // Permet d'ajouter une div utile à bootstrap
    public function startDiv(string $class): self{

        $this->formElements .= "<div class = '$class'>";
        return $this;
    }

    public function endDiv(): self
    {
        $this->formElements .= "</div>";
        return $this;
    }

    public function startFieldset(string $text, string $class): self
    {
        $this->formElements .= "<fieldset class='$class'>";
        // $this->formElements .= isset($attributes) ? $this->addAttributes($attributes) . ">" : ">";
        $this->formElements .= $text;
        return $this;
    }

    public function endFieldset(): self{
        $this->formElements .= "</fieldset>";
        return $this;
    }

    public function legend(string $text, string $class): self 
    {
        $this->formElements .= "<legend class='$class'>";
        $this->formElements .= $text;
        $this->formElements .= "</legend>";

        return $this;
    }
    
    // Methode permettant de fermer le formulaire
    public function endForm(): self 
    {
        $this->formElements .= "</form>";
        return $this;
    }

    // Methode permettant de tester les champs, les paramètres repésentant les valeurs en POST et le nom des champs
    public static function validatePost(array $post, array $fields): bool 
    {
        // Chaque champs est parcouru
        foreach( $fields as $field){
            // On teste si les champs sont vides ou déclarés
            if (empty($post[$field]) || !isset($post[$field])) {
                return false;
            }
        }
        return true;
    }

    // Methode qui permet de tester si le fichier est bien présent et s'il n'ya pas d'erreur d'envoi
    public static function validateFiles(array $files, array $fields): bool  
    {
        foreach ( $fields as $field) {
           if (isset($files[$field]) && $files[$field]['error'] == 0) {
                return true;
            }
        }
        return false;
    }
        
  
    // Methode qui permet de tester le fichier en cas d'envoi validé
    // et retourne un message d'erreur si le fichier ne correspond pas
    public static function errorUpload(array $files, array $fields, array $type): string 
    {
        $erreur ='';
        // Parcours chaque champs
        foreach ($fields as $field) {
            // Récupère l'extension du fichier
            $ext =  pathinfo($files[$field]['name'], PATHINFO_EXTENSION);

            if (isset($files[$field]) && $files[$field]['error'] == 0) {    
                // Si l'extension correspond aux extensions autorisées
                if (in_array($_FILES[$field]['type'], $type)) {
                    // On delimite une taille max
                    $maxSize = 3 * 1024 * 1024;
                    // On teste si le format correspond, la taille du fichier
                    if (!array_key_exists($ext, $type)) {
                        $erreur = "Le format du fichier est incorrect !";
                        // Si le fichier est trop lourd
                    } elseif ($files[$field]['size'] > $maxSize) {
                        $erreur = "Le fichier est trop volumineux !";
                    }
                } else {
                    $erreur = "Le type et/ou le format du document n'est pas valide !";
                }
            } else {
                // Si $_Files est > 0, on affiche l'erreur correspondante
                $erreur = $files[$field]['error'];
            }
        }
        return $erreur;
    }

    // Méthode qui formate le fichier avant stockage
    public static function formateFile(array $files, array $fields): string 
    {
        // Parcours chaque champs
        foreach ($fields as $field) {
            // Formate le fichier
            $uniqueName = uniqid('', true);
            $file = $uniqueName . "." . pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
        }
        return $file;
    }  
} 
    
