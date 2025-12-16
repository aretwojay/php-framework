<?php

namespace App\Lib\Commands;

use App\Lib\Annotations\AnnotationReader;
use App\Lib\Annotations\AnnotationsDump\ClassAnnotationsDump;
use App\Lib\Annotations\AnnotationsDump\PropertyAnnotationsDump;
use App\Lib\Annotations\ORM\AutoIncrement;
use App\Lib\Annotations\ORM\Column;
use App\Lib\Annotations\ORM\Id;
use App\Lib\Annotations\ORM\References;
use App\Lib\Database\DatabaseConnexion;
use App\Lib\Database\Dsn;
use App\Lib\Entities\AbstractEntity;

class CreateSchema extends AbstractCommand {

    const string ENTITIES_NAMESPACE_PREFIX = "App\\Entities\\";
    const string CREATE_TABLE_FORMAT = 'CREATE TABLE IF NOT EXISTS %s (%s);';

    public function execute(): void {
        $entitiesClasses = self::getEntitiesClasses();
        $statement = '';

        $classesAnnotationsDump = [];

        foreach($entitiesClasses as $entityClass) {
            $classesAnnotationsDump[] = AnnotationReader::extractFromClass($entityClass);
        }

        $sortedClasses = self::sortClassesByDependencies($classesAnnotationsDump);

        $weightedEntitiesClasses = [];

        while(count($weightedEntitiesClasses) < count($classesAnnotationsDump)) {
        	foreach($classesAnnotationsDump as $class) {
            	if(array_key_exists($class->getName(), $weightedEntitiesClasses) === true) {
            		continue;
            	}

            	if($class->propertiesHaveAnnotation(References::class) === false) {
            		$weightedEntitiesClasses[$class->getName()] = $class;
            	    continue;
            	}
	
            	$referencesCount = count($class->getPropertiesWithAnnotation(References::class));
            	foreach($weightedEntitiesClasses as $name => $weightedClass) {
            	    foreach($class->getPropertiesWithAnnotation(References::class) as $property) {
            	        if($name === $property->getAnnotation(References::class)->class) {
                			$referencesCount--;
            	        }
            	    }
            	}
	
            	if($referencesCount === 0) {
            		$weightedEntitiesClasses[$class->getName()] = $class;
            		continue;
            	}
            }
        }

        foreach($weightedEntitiesClasses as $classAnnotionsDump) {
            $properties = $classAnnotionsDump->getProperties();
            $properties = self::sanitizeProperties($properties);

            $statement .= self::getSqlCreateTableScript($classAnnotionsDump->getName(), $properties);
        }

        echo $statement;

        // $db = new DatabaseConnexion();
        // $dsn = new Dsn();
        // $dsn->addHostToDsn();
        // $dsn->addPortToDsn();
        // $dsn->addDbnameToDsn();
        // $db->setConnexion($dsn);

        // $db->getConnexion()->exec($statement);
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


    private static function sortClassesByDependencies(array $classesAnnotationsDump): array {
        $sorted = [];
        $classesAndReferences = [];

        foreach($classesAnnotationsDump as $classAnnotationsDump) {
            $class = [
                'name' => $classAnnotationsDump->getName(),
                'references' => []
            ];

            if($classAnnotationsDump->propertiesHaveAnnotation(References::class) === true) {
                $properties = $classAnnotationsDump->getPropertiesWithAnnotation(References::class);
                foreach($properties as $property) {
                    $class['references'][] = $property->getAnnotation(References::class)->class;
                }
            }

            $classesAndReferences[] = $class;
        }

        while(count($classesAndReferences) > 0) {
            $dependencies = [];
            for($i = 0; $i < count($classesAndReferences); $i++) {
                
                if(empty($classesAndReferences[$i]['references'])) {
                    $sorted[] = $i;
                    $dependencies[] = $classesAndReferences[$i]['name'];

                    unset($classesAndReferences[$i]);
                }
            }

            echo 'classesAndReferences : ';
            var_dump($classesAndReferences);
            echo 'dependencies : ';
            var_dump($dependencies);

            foreach($classesAndReferences as $key => $classAndReferences) {
                if(!empty($classesAndReferences[$key]['references'])) {
                    $classesAndReferences[$key]['references'] = array_diff($classesAndReferences[$key]['references'], $dependencies);
                }
            }

        }

        var_dump($sorted);die;
        
    }

    private static function getSqlCreateTableScript(string $className, array $properties): string {
        $propertiesStatement = '';
        
        foreach($properties as $propertyAnnotationsDump) {
            $propertiesStatement .= self::getSqlPropertyScript($propertyAnnotationsDump);
        }

        return sprintf(self::CREATE_TABLE_FORMAT, (new \ReflectionClass($className))->getShortName(), rtrim($propertiesStatement, ','));
    }
    
    private static function getSqlPropertyScript(PropertyAnnotationsDump $propertyAnnotationsDump): string {
        $statement = '';

        $propertyName = $propertyAnnotationsDump->getName();
        if($propertyAnnotationsDump->getAnnotation(Column::class)->name !== null) {
            $propertyName = $propertyAnnotationsDump->getAnnotation(Column::class)->name;
        }

        $statement .= $propertyName . ' ';

        $statement .= $propertyAnnotationsDump->getAnnotation(Column::class)->type . '';

        if($propertyAnnotationsDump->getAnnotation(Column::class)->size !== null) {
            $statement .= '(' . $propertyAnnotationsDump->getAnnotation(Column::class)->size . ')';
        }
        
        if($propertyAnnotationsDump->getAnnotation(Column::class)->nullable === false) {
            $statement .= ' NOT NULL';
        }
        
        if($propertyAnnotationsDump->hasAnnotation(AutoIncrement::class) === true) {
            $statement .= ' AUTO_INCREMENT';
        }
        
        if($propertyAnnotationsDump->hasAnnotation(Id::class) === true) {
            $statement .= ' PRIMARY KEY';
        }

        $statement .= ',';

        return $statement;
    }
    
    private static function sanitizeProperties(array $properties): array {
        foreach($properties as $key=>$property) {
            if($property->hasAnnotation(Column::class) === false) {
                unset($properties[$key]);
            }
        }

        return $properties;
    }
}
