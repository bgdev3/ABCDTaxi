<?php
namespace App\Entities;

class price
{

    private $idPrice;
    private $oneWayDay;
    private $returnJourneyDay;
    private $oneWayNight;
    private $returnJourneyNight;
    private $waitingRate;
    private $minDistanceDay;
    private $minDistanceNight;
    private $minDistanceDayReturn;
    private $minDistanceNightReturn;   
    private $minPerception; 



    /**
     * Get the value of idPrice
     */ 
    public function getIdPrice()
    {
        return $this->idPrice;
    }

    /**
     * Set the value of idPrice
     *
     * @return  self
     */ 
    public function setIdPrice($idPrice)
    {
        $this->idPrice = $idPrice;

        return $this;
    }

    /**
     * Get the value of oneWayDay
     */ 
    public function getOneWayDay()
    {
        return $this->oneWayDay;
    }

    /**
     * Set the value of oneWayDay
     *
     * @return  self
     */ 
    public function setOneWayDay($oneWayDay)
    {
        $this->oneWayDay = $oneWayDay;

        return $this;
    }

    /**
     * Get the value of returnJourneyDay
     */ 
    public function getReturnJourneyDay()
    {
        return $this->returnJourneyDay;
    }

    /**
     * Set the value of returnJourneyDay
     *
     * @return  self
     */ 
    public function setReturnJourneyDay($returnJourneyDay)
    {
        $this->returnJourneyDay = $returnJourneyDay;

        return $this;
    }

    /**
     * Get the value of oneWayNight
     */ 
    public function getOneWayNight()
    {
        return $this->oneWayNight;
    }

    /**
     * Set the value of oneWayNight
     *
     * @return  self
     */ 
    public function setOneWayNight($oneWayNight)
    {
        $this->oneWayNight = $oneWayNight;

        return $this;
    }

    /**
     * Get the value of returnJourneyNight
     */ 
    public function getReturnJourneyNight()
    {
        return $this->returnJourneyNight;
    }

    /**
     * Set the value of returnJourneyNight
     *
     * @return  self
     */ 
    public function setReturnJourneyNight($returnJourneyNight)
    {
        $this->returnJourneyNight = $returnJourneyNight;

        return $this;
    }

    /**
     * Get the value of waitingRate
     */ 
    public function getWaitingRate()
    {
        return $this->waitingRate;
    }

    /**
     * Set the value of waitingRate
     *
     * @return  self
     */ 
    public function setWaitingRate($waitingRate)
    {
        $this->waitingRate = $waitingRate;

        return $this;
    }

    /**
     * Get the value of minDistanceNight
     */ 
    public function getMinDistanceNight()
    {
        return $this->minDistanceNight;
    }

    /**
     * Set the value of minDistanceNight
     *
     * @return  self
     */ 
    public function setMinDistanceNight($minDistanceNight)
    {
        $this->minDistanceNight = $minDistanceNight;

        return $this;
    }

    /**
     * Get the value of minDistanceNightReturn
     */ 
    public function getMinDistanceNightReturn()
    {
        return $this->minDistanceNightReturn;
    }

    /**
     * Set the value of minDistanceNightReturn
     *
     * @return  self
     */ 
    public function setMinDistanceNightReturn($minDistanceNightReturn)
    {
        $this->minDistanceNightReturn = $minDistanceNightReturn;

        return $this;
    }

    /**
     * Get the value of minDistanceDay
     */ 
    public function getMinDistanceDay()
    {
        return $this->minDistanceDay;
    }

    /**
     * Set the value of minDistanceDay
     *
     * @return  self
     */ 
    public function setMinDistanceDay($minDistanceDay)
    {
        $this->minDistanceDay = $minDistanceDay;

        return $this;
    }

    /**
     * Get the value of minDistanceDayReturn
     */ 
    public function getMinDistanceDayReturn()
    {
        return $this->minDistanceDayReturn;
    }

    /**
     * Set the value of minDistanceDayReturn
     *
     * @return  self
     */ 
    public function setMinDistanceDayReturn($minDistanceDayReturn)
    {
        $this->minDistanceDayReturn = $minDistanceDayReturn;

        return $this;
    }

   

    /**
     * Get the value of minPerception
     */ 
    public function getMinPerception()
    {
        return $this->minPerception;
    }

    /**
     * Set the value of minPerception
     *
     * @return  self
     */ 
    public function setMinPerception($minPerception)
    {
        $this->minPerception = $minPerception;

        return $this;
    }
}