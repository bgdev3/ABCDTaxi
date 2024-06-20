<?php
namespace App\Models;

use PDO;
use Exception;
use App\Core\DbConnect;
use App\Entities\SlideshowAdmin;

class SlideshowAdminModel extends DbConnect
{

    /**
     * Crée un enregistrement 
     * 
     * @param object [$picture] Permet de bénéficier de l'injonction de dépendance
     */
    public function create(SlideshowAdmin $picture): void
    {
        $this->request = $this->connexion -> prepare("INSERT INTO slideshowadmin VALUES ( NULL, :size_slide,  :picture_path)");
        $this->request -> bindValue(':size_slide', $picture->getSize_slide());
        $this->request -> bindValue(':picture_path', $picture->getPicture_path());
        $this->executeTryCatch();
    }


    /**
     * Lecture de la table en fonction de du paramètre
     * 
     * @param string [$size] Récupère les enregistrements coorespodant aux $sizes
     * @return array [$slide] Récupère les différents enregistrements
     */
    public function findAll(string $size): array
    {
        $this->request = $this->connexion->prepare("SELECT * FROM slideshowadmin WHERE size_slide = :size_slide");
        $this->request -> bindParam(':size_slide', $size);
        $this->request -> execute();
        $slide = $this->request->fetchAll();
        return $slide;
    
    }


    /**
     * Lecture d'un enregistrement correspondant à l'id
     * 
     * @param int [$id] Id de l'enregistrement à récupérer
     * @return object [$slide] Retourne le slide correspondant 
     */
    public function findPath(int $id): object 
    {
        $this->request = $this->connexion->prepare("SELECT * FROM slideshowadmin WHERE IdPicture = :id");
        $this->request -> bindParam(':id', $id);
        $this->request -> execute();
        $slide = $this->request->fetch();
        return $slide;
    }


    /**
     * Effectue une suppression sur l'id sélectionné et sur les nom de fichier correspondant à celui de l'id selectionné
     * @param int [$id] Id de l'enregistrement à supprimer
     * @param string [$nameslide] Nom du slide à supprimer
     */
    public function delete(int $id, string $nameslide): void
    {
        $this->request = $this->connexion->prepare("DELETE FROM slideshowadmin WHERE IdPicture = :id OR picture_path LIKE CONCAT('%', :nameslide, '%')");
        $this->request -> bindParam(':id', $id);
        $this->request -> bindParam(':nameslide', $nameslide);
        $this->executeTryCatch();
    }


     /**
     * Méthode privé qui permet de tester l'execution de la méthode éxecute
     */
    private function executeTryCatch(): void
    {
        try {
            $this->request->execute();
        } catch (Exception $e) {
            die("Erreur:" . $e->getMessage());
        }
        // Ferme le curseur permettant à la requette d'être de nouveau executée.
        $this->request->closeCursor();
    }
}