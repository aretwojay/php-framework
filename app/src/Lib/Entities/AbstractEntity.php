<?php

namespace App\Lib\Entities;

abstract class AbstractEntity
{
    abstract public function getId(): int | string;

    public function __set($name, $value)
    {
        // 1. Direct Setter: set{Property}
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
            return;
        }

        // 2. Relation property hydration (e.g. user_id -> user.id)
        if ($this->hydrateRelation($name, $value)) {
            return;
        }

        // 3. Direct Property Access (Reflection for protected/private)
        if (property_exists($this, $name)) {
            $reflection = new \ReflectionClass($this);
            $property = $reflection->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($this, $value);
            return;
        }

        // 4. Dynamic Property
        $this->$name = $value;
    }

    private function hydrateRelation(string $name, mixed $value): bool
    {
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getProperties() as $property) {
            $propName = $property->getName();

            // Check matching prefix: e.g. user_id -> user property
            if (!str_starts_with($name, $propName . '_')) {
                continue;
            }

            $targetClass = $this->getPropertyClass($property);
            if (!$targetClass || !class_exists($targetClass)) {
                continue;
            }

            // Get/Create relation instance
            $relationObj = $this->getRelationInstance($property, $targetClass);

            // Set value on relation (recursively triggers its set/setter)
            $subProp = substr($name, strlen($propName) + 1);
            $subSetter = 'set' . ucfirst($subProp);
            if (method_exists($relationObj, $subSetter)) {
                $relationObj->$subSetter($value);
            } else {
                $relationObj->$subProp = $value;
            }

            // Update main entity
            $mainSetter = 'set' . ucfirst($propName);
            if (method_exists($this, $mainSetter)) {
                $this->$mainSetter($relationObj);
            } else {
                $property->setAccessible(true);
                $property->setValue($this, $relationObj);
            }

            return true;
        }
        return false;
    }

    private function getPropertyClass(\ReflectionProperty $property): ?string
    {
        $type = $property->getType();
        if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
            return $type->getName();
        }
        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $t) {
                if (!$t->isBuiltin()) {
                    return $t->getName();
                }
            }
        }
        return null;
    }

    private function getRelationInstance(\ReflectionProperty $property, string $targetClass): object
    {
        $property->setAccessible(true);
        $currentVal = $property->isInitialized($this) ? $property->getValue($this) : null;

        if ($currentVal instanceof $targetClass) {
            return $currentVal;
        }

        $relationObj = new $targetClass();

        // Preserve legacy scalar value (ID) if present
        if (!is_null($currentVal) && is_scalar($currentVal)) {
            if (method_exists($relationObj, 'setId')) {
                $relationObj->setId($currentVal);
            } else {
                // Triggers __set on the relation object
                $relationObj->id = $currentVal;
            }
        }

        return $relationObj;
    }

    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties();
        $array = [];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            if ($property->isInitialized($this)) {
                $value = $property->getValue($this);
                if (is_bool($value)) {
                    $value = (int) $value;
                }
                if ($value instanceof AbstractEntity) {
                    $value = $value->getId();
                }
                $array[$property->getName()] = $value;
            }
        }
        return $array;
    }
}
