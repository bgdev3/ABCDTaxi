<?php
namespace Tests\Controllers;

use App\Services\TarifService;
use PHPUnit\Framework\TestCase;

class TarifServiceTest extends TestCase
{
    private TarifService $tarifService;

    protected function setUp(): void
    {
        $this->tarifService = new TarifService();

    }

    public function testTarifNuitSansMin(): void
    {
        $dataTrip = [
            'priceDay'   => 1.8,
            'priceNight' => 2.5,
            'distance'   => 20,
            'price'      => 0
        ];

        $result = $this->tarifService->calculate(5.0, $dataTrip);
        $this->assertEquals('55,00', $result);
    }

    public function testTarifMixteAvecMin(): void
    {
        $dataTrip = [
            'priceDay'   => 1.8,
            'priceNight' => 2.5,
            'distance'   => 30,
            'price'      => 0
        ];

        // $min = 29 → 29km en jour, 1km en nuit
        $result = $this->tarifService->calculate(5.0, $dataTrip, 29);

        // Calcul attendu :
        // Nuit  : 2.5 * (30 - 29) + 5.0 = 7.50
        // Jour  : 1.8 * 29        = 52.20
        // Total : 7.50 + 52.20    = 59.70
        $this->assertEquals('59,70', $result);
    }

    public function testTarifAvecAttente(): void
    {
        $dataTrip = [
            'priceDay'   => 1.8,
            'priceNight' => 2.5,
            'distance'   => 20,
            'price'      => 12.0  // temps d'attente déjà calculé en amont
        ];

        $result = $this->tarifService->calculate(5.0, $dataTrip);
        // Calcul attendu :
        // Nuit  : 2.5 * 20 + 5.0 = 55.00
        // Attente : 12.00
        // Total : 55.00 + 12.00 = 67.00
        $this->assertEquals('67,00', $result);
    }

    public function testTarifMixteAvecMinEtAttente(): void
    {
        $dataTrip = [
            'priceDay'   => 1.8,
            'priceNight' => 2.5,
            'distance'   => 30,
            'price'      => 15.0  // temps d'attente déjà calculé en amont
        ];

        // $min = 29 → 29km en jour, 1km en nuit
        $result = $this->tarifService->calculate(5.0, $dataTrip, 29);

        // Calcul attendu :
        // Nuit  : 2.5 * (30 - 29) + 5.0 = 7.50
        // Jour  : 1.8 * 29        = 52.20
        // Attente : 15.00
        // Total : 7.50 + 52.20 + 15.00 = 74.70
        $this->assertEquals('74,70', $result);
    }
}