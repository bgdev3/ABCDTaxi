<?php
namespace App\Entities;


class SlideshowAdmin {
    
     // Prorpiétés relative à la table en bdd
    private $idPicture;
    private $size_slide;
    private $picture_path;
   

    

    /**
     * Get the value of idPicture
     */ 
    public function getIdPicture()
    {
        return $this->idPicture;
    }

    /**
     * Set the value of idPicture
     *
     * @return  self
     */ 
    public function setIdPicture($idPicture)
    {
        $this->idPicture = $idPicture;

        return $this;
    }

     /**
     * Get the value of size_slide
     */ 
    public function getSize_slide()
    {
        return $this->size_slide;
    }

    /**
     * Set the value of size_slide
     *
     * @return  self
     */ 
    public function setSize_slide($size_slide)
    {
        $this->size_slide = $size_slide;

        return $this;
    }
    

    /**
     * Get the value of picture_path
     */ 
    public function getPicture_path()
    {
        return $this->picture_path;
    }

    /**
     * Set the value of picture_path
     *
     * @return  self
     */ 
    public function setPicture_path($picture_path)
    {
        $this->picture_path = $picture_path;

        return $this;
    }

   
}