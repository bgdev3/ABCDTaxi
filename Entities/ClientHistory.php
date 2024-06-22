<?php
namespace App\Entities;


class ClientHistory
{

     // Propiétés relative à la table en bdd
    private $idClient_histo;
    private $name;
    private $surname;
    private $email;
    private $tel;

    /**
     * Get the value of idClient_histo
     */ 
    public function getIdClient_histo()
    {
        return $this->idClient_histo;
    }

    /**
     * Set the value of idClient_histo
     *
     * @return  self
     */ 
    public function setIdClient_histo($idClient_histo)
    {
        $this->idClient_histo = $idClient_histo;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of surname
     */ 
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set the value of surname
     *
     * @return  self
     */ 
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of tel
     */ 
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Set the value of tel
     *
     * @return  self
     */ 
    public function setTel($tel)
    {
        $this->tel = $tel;

        return $this;
    }
}