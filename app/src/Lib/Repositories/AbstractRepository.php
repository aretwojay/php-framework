<?php

namespace App\Lib\Repositories;

use App\Lib\Database\DatabaseConnexion;
use App\Lib\Database\Dsn;
use App\Lib\Entities\AbstractEntity;
use App\Lib\Annotations\ORM\Column;
use App\Lib\Annotations\ORM\References;

abstract class AbstractRepository
{
    protected DatabaseConnexion $db;
    protected string $queryString;
    protected string $tableAlias;
    protected array $params = [];
    protected \PDOStatement $query;

    const CONDITIONS = [
        'eq' => '=',
        'neq' => '!=',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>=',
        'like' => 'LIKE',
        'in' => 'IN'
    ];

    public function __construct()
    {
        $dsn = new Dsn();
        $dsn->addHostToDsn();
        $dsn->addPortToDsn();
        $dsn->addDbnameToDsn();
        
        $db = new DatabaseConnexion();
        $db->setConnexion($dsn);
        
        $this->db = $db;
    }

    public function getTable(): string {
        return strtolower(str_replace('Repository','',(new \ReflectionClass($this))->getShortName()));
    }

    private function getFields(AbstractEntity $entity): string {
        return implode(', ', array_keys($entity->toArray()));
    }

    private function getValues(AbstractEntity $entity): string {
        return implode(', ', array_map(fn($k) => ":$k", array_keys($entity->toArray())));
    }

    public function queryBuilder(): self {
        $this->queryString = "";
        return $this;
    }

    public function select(...$fields): self {
        $this->queryString .= "SELECT";
        $this->queryString .= count($fields) === 0 ? ' *' : ' ' . implode(', ', $fields);
        return $this;
    }

    public function insert(AbstractEntity $entity): self {
        $this->queryString .= "INSERT INTO {$this->getTable()} ({$this->getFields($entity)})";
        return $this;
    }

    public function delete(): self {
        $this->queryString .= "DELETE";
        return $this;
    }

    public function updateTable(): self {
        $this->queryString .= "UPDATE {$this->getTable()}";
        return $this;
    }

    public function values(AbstractEntity $entity): self {
        $this->queryString .= " VALUES ({$this->getValues($entity)})";
        return $this;
    }

    public function from(string $tableAlias): self {
        $table = $this->getTable();
        $this->queryString .= " FROM $table";
        return $this->as($tableAlias);
    }

    public function as(string $tableAlias): self {
        $this->queryString .= " AS $tableAlias";
        $this->tableAlias = $tableAlias;
        return $this;
    }

    public function innerJoin(string $table, string $alias, string $condition): self {
        $this->queryString .= " INNER JOIN $table AS $alias ON $alias.id = $condition";
        return $this;
    }

    public function leftJoin(string $table, string $alias, string $condition): self {
        $this->queryString .= " LEFT JOIN $table AS $alias ON $alias.id = $condition";
        return $this;
    }

    public function andWhere(string $field, string $condition, ?string $table = null): self {
        $this->queryString .= " AND " . ($table ?? $this->tableAlias) . ".$field $condition :$field";
        return $this;
    }

    public function orWhere(string $field, string $condition, ?string $table = null): self {
        $this->queryString .= " OR " . ($table ?? $this->tableAlias) . ".$field $condition :$field";
        return $this;
    }

    public function where(string $field, string $condition, ?string $table = null): self {
        $this->queryString .= strpos($this->queryString, 'WHERE') === false ? " WHERE " : " AND ";
        $this->queryString .= ($table ?? $this->tableAlias) . ".$field $condition :$field";
        return $this;
    }

    public function addParam(string $key, $value): self {
        $this->params[$key] = $value;
        return $this;
    }

    public function setParams(array $params): self {
        $this->params = $params;
        return $this;
    }

    public function executeQuery(): self {
        $this->query = $this->db->getConnexion()->prepare($this->queryString);
        $this->query->execute($this->params);
        return $this;
    }

    // -------------------- FETCH --------------------
    public function getOneResult() {
        $class = 'App\Entities\\' . ucfirst($this->getTable());
        $this->query->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $class);
        return $this->query->fetch();
    }

    public function getAllResults(): array {
        $class = 'App\Entities\\' . ucfirst($this->getTable());
        $this->query->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $class);
        return $this->query->fetchAll();
    }

    public function find(string|int $id, array $relations = []): ?AbstractEntity {
        return $this->findOneBy(['id' => $id], $relations);
    }

    public function findAll(array $relations = []): array {
        return $this->findBy([], $relations);
    }

    public function findBy(array $criteria, array $relations = []): array {
        $alias = substr($this->getTable(), 0, 1);
        $fields = [$alias . '.*'];
        
        foreach ($relations as $relationAlias => $config) {
            if (isset($config['fields'])) {
                foreach ($config['fields'] as $field) {
                    $fields[] = $relationAlias . '.' . $field . ' AS ' . $relationAlias . '_' . $field;
                }
            }
        }

        $this->queryBuilder()->select(...$fields)->from($alias);
        $this->addInnerJoinAccordingToRelations($relations);
        $this->addWhereAccordingToCriterias($criteria);
        return $this->executeQuery()->getAllResults();
    }

    public function findOneBy(array $criteria, array $relations = []) {
        $alias = substr($this->getTable(), 0, 1);
        $fields = [$alias . '.*'];
        
        foreach ($relations as $relationAlias => $config) {
            if (isset($config['fields'])) {
                foreach ($config['fields'] as $field) {
                    $fields[] = $relationAlias . '.' . $field . ' AS ' . $relationAlias . '_' . $field;
                }
            }
        }

        $this->queryBuilder()->select(...$fields)->from($alias);
        $this->addInnerJoinAccordingToRelations($relations);
        $this->addWhereAccordingToCriterias($criteria);
        $data = $this->executeQuery()->getOneResult();
        return $data === false ? null : $data;
    }

    private function addWhereAccordingToCriterias(array $criterias) {
        foreach($criterias as $key => $value) {
            if(strpos($this->queryString, 'WHERE') === false) {
                $this->where($key, self::CONDITIONS['eq']);
            } else {
                $this->andWhere($key, self::CONDITIONS['eq']);
            }
            $this->addParam($key, $value);
        }
    }

    private function addInnerJoinAccordingToRelations(array $relations) {
        foreach($relations as $alias => $config) {
            $this->innerJoin($config['table'], $alias, $config['condition']);
        }
    }

    public function set(AbstractEntity $entity): self {
        $this->queryString .= " SET";
        foreach ($entity->toArray() as $key => $value) {
            $this->queryString .= " $key = :$key,";
        }
        $this->queryString = rtrim($this->queryString, ',');
        return $this;
    }

    public function save(AbstractEntity $entity): string {
        $this->queryBuilder()->insert($entity)->values($entity)->setParams($entity->toArray());
        $this->executeQuery();
        return $this->db->getConnexion()->lastInsertId();
    }

    public function update(AbstractEntity $entity) {
        $this->queryBuilder()
            ->updateTable()
            ->as(substr($this->getTable(), 0, 1))
            ->set($entity)
            ->where('id', self::CONDITIONS['eq'])
            ->setParams($entity->toArray())
            ->executeQuery();
    }

    public function remove(AbstractEntity $entity) {
        $this->queryBuilder()
            ->delete()
            ->from($this->getTable())
            ->where('id', self::CONDITIONS['eq'])
            ->addParam('id', $entity->getId())
            ->executeQuery();
    }

    public function debug(): self
    {
        return $this;
    }
}
