<?php

declare(strict_types=1);

namespace Kassko\Sample;

class GarageDataSource
{
    public function getCars(?int $id): array
    {
        // Mock data for testing
        if ($id === 1) {
            return [
                ['id' => 1, 'brand' => 'Ford', 'model' => 'Mustang', 'gasolineKind' => 'premium'],
                ['id' => 2, 'brand' => 'Tesla', 'model' => 'Model 3', 'energyProvider' => 'supercharger'],
            ];
        }
        
        if ($id === 2) {
            return [
                ['id' => 3, 'brand' => 'Chevrolet', 'model' => 'Camaro', 'gasolineKind' => 'regular'],
            ];
        }
        
        return [];
    }
}
