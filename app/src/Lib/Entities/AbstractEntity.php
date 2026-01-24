<?php

namespace App\Lib\Entities;

abstract class AbstractEntity {

    abstract public function getId(): int | string;
    
    public function toArray(): array {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties();
        $array = [];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            if ($property->isInitialized($this)) {
                $array[$property->getName()] = $property->getValue($this);
            }
        }
        return $array;
    }
}

?>
