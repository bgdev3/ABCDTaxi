<?php
namespace App\Services;


/**
 * 
 * configurer le composer.json et ne pas oublier de faire un dump-autoload pour que le service soit reconnu dans les controllers
 */
class TarifService
{
    public function calculate(float $pickupPrice, array $dataTrip, ?int $min = null): string
    {
        if ($min !== null) {
            $distance = $dataTrip['distance'] - $min;
            $price1 = $dataTrip['priceNight'] * $distance + $pickupPrice;
            $distance = $min;
            $price2 = $dataTrip['priceDay'] * $distance;
            $price = $price1 + $price2;
            $price += $dataTrip['price'];
        } else {
            $price = $dataTrip['priceNight'] * $dataTrip['distance'] + $pickupPrice;
            $price += $dataTrip['price'];
        }

       $price = number_format($price, 2, ',', ' ');
       return $price;
    }
}