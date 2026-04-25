<?php

namespace App\Core;

/**
 * Gère la liste blanche des contrôleurs autorisés à être appelés
 * pour éviter les attaques de type LFI ou l'appel de classes non prévues
 */
class Whitelist
{
    private array $allowedControllers;

    public function __construct()
    {
        $this->allowedControllers = $this->load();
    }

    private function load(): array
    {
        $envPath = dirname(__DIR__) . '/.env';

        if (!file_exists($envPath)) {
            throw new \RuntimeException(".env introuvable");
        }

        // Lit le .env et cherche la ligne ALLOWED_CONTROLLERS
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Ignore les commentaires et cherche la ligne ALLOWED_CONTROLLERS
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            
            if (str_starts_with($line, 'ALLOWED_CONTROLLERS=')) {
                $value = explode('=', $line, 2)[1];

                return array_map(
                    'trim',
                    explode(',', $value)
                );
            }
        }

        throw new \RuntimeException(
            "ALLOWED_CONTROLLERS absent du .env"
        );
    }

    // Vérifie si un contrôleur est dans la liste blanche
    public function isAllowed(string $controller): bool
    {
        return in_array(
            $controller,
            $this->allowedControllers,
            true
        );
    }
}