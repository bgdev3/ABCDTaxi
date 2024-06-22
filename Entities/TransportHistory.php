<?php
namespace App\Entities;

class TransportHistory
{

     // Prorpiétés relative à la table en bdd
    private $idTransport_histo;
    private $dateReservation;
    private $dateTransport;
    private $departure_time;
    private $departure_place;
    private $destination;
    private $roundTrip;
    private $price;
    private $cancelation;
    private $cancellationDate;
    private $idClient_histo;

    /**
     * Get the value of idTransport_histo
     */ 
    public function getIdTransport_histo()
    {
        return $this->idTransport_histo;
    }

    /**
     * Set the value of idTransport_histo
     *
     * @return  self
     */ 
    public function setIdTransport_histo($idTransport_histo)
    {
        $this->idTransport_histo = $idTransport_histo;

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
     * Get the value of cancelation
     */ 
    public function getCancelation()
    {
        return $this->cancelation;
    }

    /**
     * Set the value of cancelation
     *
     * @return  self
     */ 
    public function setCancelation($cancelation)
    {
        $this->cancelation = $cancelation;

        return $this;
    }

    /**
     * Get the value of cancellationDate
     */ 
    public function getCancellationDate()
    {
        return $this->cancellationDate;
    }

    /**
     * Set the value of cancellationDate
     *
     * @return  self
     */ 
    public function setCancellationDate($cancellationDate)
    {
        $this->cancellationDate = $cancellationDate;

        return $this;
    }

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
}