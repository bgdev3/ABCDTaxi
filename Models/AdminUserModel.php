<?php
namespace App\Models;

use App\Core\DbConnect;
use App\Entities\AdminUser;

class AdminUserModel extends DbConnect
{

    /**
     * Effectue la lecture d'un enregistretment
     * @param string [$email] Email de l'enregistrement à récupérer
     * @return object [$admin]
     */
    public function find(string $email): object
    {
        $this->request = $this->connexion->prepare("SELECT * FROM adminuser WHERE email = :email");
        $this->request -> bindParam(':email', $email);
        $this-> request -> execute();
        $admin = $this->request->fetch();
  
        return $admin;
    }

    /**
     * Mets à jour l'enregistrement correspondant à l'id
     * 
     * @param int [$idAdmin] id de l'enregistrement à mettre à jour
     * @param object [$admin] Permet de bénéficier de l'injection de dépendance
     */
    public function update(int $idAdmin, AdminUser $admin): void
    {
        $this->request = $this->connexion->prepare("UPDATE adminuser SET username = :username, email = :email, password = :password WHERE idAdmin = :idAdmin");
        $this->request -> bindValue(':idAdmin', $idAdmin);
        $this->request -> bindValue(':username', $admin->getUsername());
        $this->request -> bindValue(':email', $admin->getEmail());
        $this->request -> bindValue(':password', $admin->getPassword());
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