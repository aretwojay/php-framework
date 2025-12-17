<?php

namespace App\Lib\Repositories;

use App\Lib\Database\DatabaseConnexion;
use App\Lib\Database\Dsn;
use App\Lib\Entities\AbstractEntity;

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
        $db = new DatabaseConnexion();
        $db->setConnexion($dsn);
        $this->db = $db;
    }

    public function getTable(): string {
        return str_replace('repository','',strtolower((new \ReflectionClass($this))->getShortName()));
    }

    private function getFields(AbstractEntity $entity): string {
        $fields = [];
        foreach ($entity->toArray() as $key => $value) {
            $fields[] = $key;
        }

        return implode(', ', $fields);
    }

    private function getValues(AbstractEntity $entity): string {
        $values = [];
        foreach ($entity->toArray() as $key => $value) {
            $values[] = ':' . $key;
        }

        return implode(', ', $values);
    }

    public function queryBuilder(): self {
        $this->queryString = "SELECT";
        return $this;
    }

    public function select(...$fields): self {
        if(count($fields) === 0) {
            $this->queryString .= ' *';
            return $this;
        }

        $this->queryString .= ' ' . implode(', ', $fields);
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

    public function andWhere(string $field, string $condition, ?string $table = null): self {
        $this->queryString .= " AND  ";
        return $this->where($field, $condition, $table);
    }

    public function orWhere(string $field, string $condition, ?string $table = null): self {
        $this->queryString .= " OR  ";
        return $this->where($field, $condition, $table);
    }

    public function where(string $field, string $condition, ?string $table = null): self {
        $this->queryString .= " WHERE ";
        if($table !== null) {
            $this->queryString .= "$table.";
        }else {
            $this->queryString .= "$this->tableAlias.";
        }

        $this->queryString .= "$field $condition :$field";
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

    public function getOneResult() {
        $this->query->setFetchMode(\PDO::FETCH_CLASS, 'App\Entities\\' . ucfirst($this->getTable()));
        return $this->query->fetch();
    }

    public function getAllResults(): array {
        $this->query->setFetchMode(\PDO::FETCH_CLASS, 'App\Entities\\' . ucfirst($this->getTable()));
        return $this->query->fetchAll();
    }

    public function find(string | int $id) {
        return $this->findOneBy(['id' => $id]);
    }

    public function findAll() {
        $this->queryBuilder()
            ->select()
            ->from(substr($this->getTable(), 0, 1))
            ->executeQuery()
            ->getAllResults();
    }

    public function findBy(array $criteria) {
        $this->queryBuilder()
            ->select()
            ->from(substr($this->getTable(), 0, 1))
            ;

        foreach($criteria as $key => $value) {
            if(strpos($this->queryString, 'WHERE') === false) {
                $this->where($key, self::CONDITIONS['eq']);
            } else {
                $this->andWhere($key, self::CONDITIONS['eq']);
            }
            $this->addParam($key, $value);
        }

        return $this->executeQuery()
            ->getAllResults();
    }

    public function findOneBy(array $criteria) {
        $this->queryBuilder()
            ->select()
            ->from(substr($this->getTable(), 0, 1))
            ;

        foreach($criteria as $key => $value) {
            if(strpos($this->queryString, 'WHERE') === false) {
                $this->where($key, self::CONDITIONS['eq']);
            } else {
                $this->andWhere($key, self::CONDITIONS['eq']);
            }
            $this->addParam($key, $value);
        }

        $data = $this->executeQuery()
            ->getAllResults();

        if($data === false) {
            return null;
        }

        return $data;
    }

    public function save(AbstractEntity $entity): string {
        $this->executeQuery("INSERT INTO {$this->getTable()} ({$this->getFields($entity)}) VALUES ({$this->getValues($entity)})", $entity->toArray());

        return $this->db->getConnexion()->lastInsertId();
    }

    public function update(AbstractEntity $entity) {
        $this->executeQuery("UPDATE {$this->getTable()} SET {$this->getUpdateFields($entity)} WHERE id = :id", array_merge($entity->toArray(), ['id' => $entity->getId()]));
    }

    public function delete(AbstractEntity $entity) {
        $this->executeQuery("DELETE FROM {$this->getTable()} WHERE id = :id", ['id' => $entity->getId()]);;
    }
}
