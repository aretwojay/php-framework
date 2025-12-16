<?php

namespace App\Lib\Commands;

use App\Lib\Annotations\ORM\AutoIncrement;
use App\Lib\Annotations\ORM\Column;
use App\Lib\Annotations\ORM\Id;
use App\Lib\Database\DatabaseConnexion;
use App\Lib\Database\Dsn;
use App\Lib\Entities\AbstractEntity;

class CreateSchema extends AbstractCommand {

    const string ENTITIES_NAMESPACE_PREFIX = "App\\Entities\\";
    const string CREATE_TABLE_FORMAT = 'CREATE TABLE IF NOT EXISTS %s (%s);';

    public function execute(): void {
        $entitiesClasses = self::getEntitiesClasses();
        $statement = '';
        foreach($entitiesClasses as $entityClass) {
            $properties = self::getClassProperties($entityClass);
            $properties = self::sanitizeProperties($properties);
            $statement .= self::getSqlCreateTableScript($entityClass, $properties);
        }

        echo $statement;

        $db = new DatabaseConnexion();
        $dsn = new Dsn();
        $dsn->addHostToDsn();
        $dsn->addPortToDsn();
        $dsn->addDbnameToDsn();
        $db->setConnexion($dsn);

        $db->getConnexion()->exec($statement);
    }

    public function undo(): void {
        
    }

    public function redo(): void {
        
    }

    private static function getEntitiesClasses(): array {
        $entitiesClasses = [];

        $files = scandir(__DIR__ . '/../../Entities');

        foreach($files as $file) {
            if($file === '.' || $file === '..') {
                continue;
            }
            
            $className = self::ENTITIES_NAMESPACE_PREFIX . pathinfo($file, PATHINFO_FILENAME);

            if(!class_exists($className)) {
                continue;
            }

            if(!is_subclass_of($className, AbstractEntity::class)) {
                continue;
            }

            $entitiesClasses[] = $className;
        }


        return $entitiesClasses;
    }

    private static function getClassProperties(string $className): array {
        $properties = [];

        $reflectionClass = new \ReflectionClass($className);
        $entity = new $className();
        $reflectionProperties = $reflectionClass->getProperties();

        foreach($reflectionProperties as $property) {
            $attributes = $property->getAttributes();
            $properties[$property->getName()] = [];
            foreach($attributes as $attribute) {
                $instance = $attribute->newInstance();
                $properties[$property->getName()][$attribute->getName()] = $instance;
            }
        }
        
        return $properties;
    }

    private static function getSqlCreateTableScript(string $className, array $properties): string {
        $propertiesStatement = '';
        
        foreach($properties as $key=>$config) {
            $propertiesStatement .= self::getSqlPropertyScript($key, $config);
        }

        return sprintf(self::CREATE_TABLE_FORMAT, (new \ReflectionClass($className))->getShortName(), rtrim($propertiesStatement, ','));
    }

    private static function getSqlPropertyScript(string $phpPropertyName, array $property): string {
        $statement = '';

        $propertyName = $phpPropertyName;
        if($property[Column::class]->name !== null) {
            $propertyName = $property[Column::class]->name;
        }

        $statement .= $propertyName . ' ';

        $statement .= $property[Column::class]->type . '';

        if($property[Column::class]->size !== null) {
            $statement .= '(' . $property[Column::class]->size . ')';
        }
        
        if($property[Column::class]->nullable === true) {
            $statement .= ' NOT NULL';
        }
        
        if(array_key_exists(AutoIncrement::class, $property) === true) {
            $statement .= ' AUTO_INCREMENT';
        }
        
        if(array_key_exists(Id::class, $property) === true) {
            $statement .= ' PRIMARY KEY';
        }

        $statement .= ',';

        return $statement;
    }

    private static function sanitizeProperties(array $properties): array {
        foreach($properties as $property=>$config) {
            if(array_key_exists(Column::class, $config) === false) {
                unset($properties[$property]);
            }
        }

        return $properties;
    }
}
