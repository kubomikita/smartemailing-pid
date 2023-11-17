<?php
declare(strict_types=1);
namespace App\Models;

use Nette\Database\Connection;
use Nette\Database\Explorer;
use Nette\Database\ResultSet;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\InvalidArgumentException;
use Nette\Utils\Arrays;


abstract class BaseModel {

	protected static ?string $tableName = null;

	public function __construct(private readonly Explorer $explorer)
	{
		if(static::$tableName === null){
			throw new InvalidArgumentException(sprintf('Please specify static property "tableName" for model "%s"', static::class));
		}
	}

	public function getDatabaseExplorer() : Selection
	{
		return $this->explorer->table(static::$tableName);
	}

	public function getDatabaseConnection() : Connection
	{
		return $this->explorer->getConnection();
	}

	public function findAll(): Selection
	{
		return $this->getDatabaseExplorer();
	}
	public function find(int|string $id): Selection
	{
		return $this->findAll()->wherePrimary((int) $id);
	}

	public function findBy(array $where) : Selection
	{
		return $this->findAll()->where($where);
	}

	public function insert(array $data) : ActiveRow|int|bool
	{
		return $this->getDatabaseExplorer()->insert($data);
	}

	public function insertUpdate(array $data): bool|ResultSet
	{
		if(Arrays::isList($data)) {
			foreach ($data as $values) {
				$this->getDatabaseConnection()->query("INSERT INTO " . static::$tableName, $values, "ON DUPLICATE KEY UPDATE",$values);
			}
			return true;
		}
		return $this->getDatabaseConnection()->query("INSERT INTO " . static::$tableName, $data, "ON DUPLICATE KEY UPDATE",$data);
	}

	public function update(array $where, array $update): int
	{
		return $this->findBy($where)->update($update);
	}

	public function delete(array $where) : int
	{
		return $this->findBy($where)->delete();
	}
}