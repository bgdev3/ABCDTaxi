<?php
namespace App\DependencyInjection;

use ReflectionClass;
use Exception;

/**
 * Container de dépendances simple utilisant la réflexion pour instancier les classes
 * et résoudre leurs dépendances automatiquement
 */
class Container
{
    private array $instances = [];

    public function get(string $class)
    {
        // Singleton simple
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        // Reflection de la classe
        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new Exception("Classe non instanciable : $class");
        }

        $constructor = $reflection->getConstructor();

        // Pas de constructeur → new direct
        if (!$constructor) {
            $object = new $class();
        } else {
            $dependencies = [];

            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();

                if (!$type) {
                    throw new Exception("Impossible de résoudre {$param->getName()}");
                }

                $dependencies[] = $this->get($type->getName());
            }

            $object = $reflection->newInstanceArgs($dependencies);
        }

        return $this->instances[$class] = $object;
    }
}