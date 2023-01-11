<?php
declare(strict_types=1);

namespace Src\Repository;

use Src\Repository\Interfaces\InvoiceRepositoryInterface;
use Src\Model\Conversion;

class InvoiceRepository implements InvoiceRepositoryInterface
{

	private $db = null;

	public function __construct($db)
	{
		$this->db = $db;
	}

	public function index()
	{
		$query = "
			SELECT 
				*  
			FROM 
				conversions;";

		try {
			$query = $this->db->query($query);
			$result = $query->fetchAll(\PDO::FETCH_ASSOC);
			return $result;
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}
	}

	public function store(array $data)
	{
		$statement = "
            INSERT INTO conversions 
                (name, status)
            VALUES
                (:name, :status);
        ";

		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array(
				'name' => $data['name'],
				'status' => Conversion::CONVERSION_STATUSES['AWAIT'],
			));
			return $statement->rowCount();
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}
	}


	public function update($id, $status)
	{
		$statement = "
            UPDATE 
            	conversions
            SET 
                status = :status
            WHERE 
            	id = :id
        ";

		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array(
				'id' => (int)$id,
				'status' => (int)$status
			));
			return $statement->rowCount();
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}
	}

	public function find($id)
	{
		$query = "
			SELECT 
  				* 
  			FROM 
  				conversions 
  			WHERE 
  				id = :id";

		try {
			$query = $this->db->prepare($query);
			$query->execute(array(
				'id' => (int)$id
			));
			$result = $query->fetch(\PDO::FETCH_ASSOC);
			return $result;
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}
	}

	public function delete($id)
	{
		$statement = "
			DELETE FROM conversions 
			WHERE id = :id
			LIMIT 1;";

		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array('id' => (int)$id));
			return $statement->rowCount();
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}
	}
}
