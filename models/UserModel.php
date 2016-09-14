<?php

namespace Migration\Models;

class UserModel
{
    /**
     * @var \PDO
     */
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function getSizeOfUsersTable()
    {
        $sql = <<<SQL
SELECT count(*) as row_number FROM user
SQL;

        $stmt = $this->db->prepare($sql);

        try {
            $result = $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }

        if ($result) {
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['row_number'];
        }

        return false;
    }

    public function getUsers($page = 0, $perPage)
    {
        $startPos = $page * $perPage;

        $sql =<<<SQL
SELECT * FROM user limit $startPos, $perPage;
SQL;

        $stmt = $this->db->prepare($sql);

        try {
            $result = $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }

        if($result) {
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $sizeOfData = sizeof($data);

            $firstId    = $data[0]['id_user'];
            $lastId     = $data[$sizeOfData - 1]['id_user'];

            return [
               ['users' => $data],
               $firstId,
               $lastId
           ];
        }

        return [];
    }

    public function getUserContract($idUserFrom, $idUserTo)
    {
        $sql =<<<SQL
SELECT * FROM user u
	LEFT JOIN user_contract
		USING(id_user)
	LEFT JOIN user_time_spent uts
		USING (id_contract)
	WHERE id_user between $idUserFrom and $idUserTo;
SQL;
        $stmt = $this->db->prepare($sql);

        try {
            $result = $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }

        if($result) {
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'user_contracts' => $data
            ];
        }

        return [];
    }

    public function getUserApiContracts($idUserFrom, $idUserTo)
    {
        $sql =<<<SQL
SELECT *  FROM user u
	LEFT JOIN user_contract_api uca
		ON u.id_user=uca.id_user
	LEFT JOIN payment_information pi
		ON uca.id_payment_information= pi.id_payment_information
	LEFT JOIN user_time_spent uts
		ON uca.id_contract = uts.id_contract
	WHERE u.id_user between $idUserFrom and $idUserTo;
SQL;
        $stmt = $this->db->prepare($sql);

        try {
            $result = $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }

        if($result) {
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'api_user_contracts' => $data
            ];
        }

        return [];
    }
}
