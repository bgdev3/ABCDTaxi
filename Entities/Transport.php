<?php
namespace App\Entities;

class transport
{
    private $idTransport;
    private $nbPassengers;
    private $dateReservation;
    private $dateTransport;
    private $departure_time;
    private $departure_place;
    private $destination;
    private $roundTrip;
    private $estimated_wait;
    private $price;
    private $id_user;

    /**
     * Get the value of idTransport
     */ 
    public function getIdTransport()
    {
        return $this->idTransport;
    }

    /**
     * Set the value of idTransport
     *
     * @return  self
     */ 
    public function setIdTransport($idTransport)
    {
        $this->idTransport = $idTransport;

        return $this;
    }

     /**
     * Get the value of nbPassengers
     */ 
    public function getNbPassengers()
    {
        return $this->nbPassengers;
    }

    /**
     * Set the value of nbPassengers
     *
     * @return  self
     */ 
    public function setNbPassengers($nbPassengers)
    {
        $this->nbPassengers = $nbPassengers;

        return $this;
    }
    
    /**
     * Get the value of dateReservation
     */ 
    public function getDateReservation()
    {
        return $this->dateReservation;
    }

    /**
     * Set the value of dateReservation
     *
     * @return  self
     */ 
    public function setDateReservation($dateReservation)
    {
        $this->dateReservation = $dateReservation;

        return $this;
    }

    /**
     * Get the value of dateTransport
     */ 
    public function getDateTransport()
    {
        return $this->dateTransport;
    }

    /**
     * Set the value of dateTransport
     *
     * @return  self
     */ 
    public function setDateTransport($dateTransport)
    {
        $this->dateTransport = $dateTransport;

        return $this;
    }

    /**
     * Get the value of departure_time
     */ 
    public function getDeparture_time()
    {
        return $this->departure_time;
    }

    /**
     * Set the value of departure_time
     *
     * @return  self
     */ 
    public function setDeparture_time($departure_time)
    {
        $this->departure_time = $departure_time;

        return $this;
    }

    /**
     * Get the value of departure_place
     */ 
    public function getDeparture_place()
    {
        return $this->departure_place;
    }

    /**
     * Set the value of departure_place
     *
     * @return  self
     */ 
    public function setDeparture_place($departure_place)
    {
        $this->departure_place = $departure_place;

        return $this;
    }

    /**
     * Get the value of destination
     */ 
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set the value of destination
     *
     * @return  self
     */ 
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get the value of roundTrip
     */ 
    public function getRoundTrip()
    {
        return $this->roundTrip;
    }

    /**
     * Set the value of roundTrip
     *
     * @return  self
     */ 
    public function setRoundTrip($roundTrip)
    {
        $this->roundTrip = $roundTrip;

        return $this;
    }

    /**
     * Get the value of estimated_wait
     */ 
    public function getEstimated_wait()
    {
        return $this->estimated_wait;
    }

    /**
     * Set the value of estimated_wait
     *
     * @return  self
     */ 
    public function setEstimated_wait($estimated_wait)
    {
        $this->estimated_wait = $estimated_wait;

        return $this;
    }

    /**
     * Get the value of price
     */ 
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set the value of price
     *
     * @return  self
     */ 
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get the value of id_user
     */ 
    public function getId_user()
    {
        return $this->id_user;
    }

    /**
     * Set the value of id_user
     *
     * @return  self
     */ 
    public function setId_user($id_user)
    {
        $this->id_user = $id_user;

        return $this;
    }
}