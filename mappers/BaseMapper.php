<?php

namespace Migration\Mappers;

use Migration\Helpers\Helper;

/**
 * Class BaseMapper
 * @package Migration\Mappers
 */
class BaseMapper
{
    /**
     * Map from key to value indexes
     *
     * @var array
     */
    public $mapping = [];

    /**
     * @var array Data placeholder when select data
     */
    protected $data = [];

    /**
     * @var Helper contains helper functions for mappingThroughFunctions
     */
    protected $helper;

    /**
     * @var array Row in batch (like mongoDb record)
     */
    private $row;

    /**
     * @var array Mapped result set
     */
    private $mapped;

    /**
     * BaseMapper constructor.
     *
     * @param      $config
     * @param \PDO $updateDb
     */
    public function __construct($config, \PDO $updateDb)
    {
        $this->helper = new Helper($config, $updateDb);
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }


    /**
     * Input :
     *
     *  nested-like mongoDb documents
     *
     *
     *  So, we must have schema & dataset
     */

    /**
     * Output format :
     * [
     *      'table' => [
     *          'field1' => '..',
     *          'field2' => '..',
     *          ....
     *      ],
     *      'table2' => [
     *          'field1' => '..',
     *          'field2' => '..',
     *          ....
     *      ],
     *      ...
     * ]
     */

    /**
     * Apply mapping to input array
     *
     * @return array
     */
    // in each table , where we have auto_increment, or - not null values, must to fill them in data builder
    public function map()
    {
        $this->mapped = [];

        $totalRows = [];
        foreach ($this->data as $rowToProcess) {
            $this->row = $rowToProcess;
            $mappedRow = [];

            foreach($this->mapping as $tableTo => $mapData) {
                $mappedRow = array_merge(
                    $mappedRow,
                    $this->mapOneRow($tableTo, $mapData)
                );
            }
            $totalRows[] = $mappedRow;
        }

        //var_dump($totalRows); die();

        //return $this->mapped;

        return $totalRows;
    }

    private function mapOneRow($tableTo, $mapData)
    {

        if(!array_key_exists($tableTo, $this->mapped)) {
            $this->mapped[$tableTo] = [];
        }

        $arrayForInsertingRecord = [];

        $mapper = [
            'mapping'   => $mapData['mapFields'],
            'constants' => $mapData['constants'],
            'remember_values' => [],
        ];
        $rowMappedByFields      = $this->doMapFields($mapper);
        $arrayForInsertingRecord = array_merge($arrayForInsertingRecord, $rowMappedByFields);

        $mapper = [
            'mapping'   => $mapData['mapThroughFunctions'],
            'constants' => $mapData['constants'],
            'remember_values' => [],
        ];
        $rowMappedByFunctions   = $this->doMapThroughFunctions($mapper);
        $arrayForInsertingRecord = array_merge($arrayForInsertingRecord, $rowMappedByFunctions);

        $this->mapped[$tableTo][] = $arrayForInsertingRecord;

        return [$tableTo => $arrayForInsertingRecord];

        // Need to remember LastInsertId
    }

    /**
     *    Input :
     *
     *  mappingFields
     *
     *  [
     *   'password_old'  => 'password',
     *   'id_user_old'   => 'id_user',
     *   'password'      => 'password',
     *  ]
     *
     *   Output :
     *
     *  [
     *   'password_old'  => $row['password'],
     *   'id_user_old'   => $row['id_user'],
     *   'password'      => $row['password'],
     *  ]
     *
     */
    private function doMapFields($mapper)
    {
        $mappingFields  = $mapper['mapping'];
        $consts         = $mapper['constants'];

        $row = $this->row;

        $output = [];

        foreach ($mappingFields as $k => $v) {
            if (array_key_exists($v, $row)) {
                $output[$k] = $row[$v];
            } elseif (array_key_exists($v, $consts)) {
                $output[$k] = $consts[$v];
            }
        }
        return $output;
    }

    /**
     * Mapper through functions in Helper
     *
     * @param $mapper
     *
     * @return array
     */
    private function doMapThroughFunctions($mapper)
    {

        $mapFunctions = $mapper['mapping'];
        $row = $this->row;
        $output = [];

        foreach ($mapFunctions as $column => $functionDef) {
            $params = $functionDef['params'];

            $preparedParams = [];
            foreach($params as $keyInRow) {
                if(array_key_exists($keyInRow, $row)) {
                    $preparedParams[] = $row[$keyInRow];
                }
            }

            $output[$column] = call_user_func_array([$this->helper, $functionDef['function']], $preparedParams);
        }

        return $output;
    }


    public function getChunkData($page)
    {
        return [];


        $tblName    = $this->mapper->tableFrom;
        $this->data = [];
        $perPage    = self::NUMBER_OF_QUERIES_IN_PACKAGE;

        $sql = <<<SQL
SELECT * FROM $tblName LIMIT $page, $perPage
SQL;

        $stmt = $this->selectDb->prepare($sql);

        try {
            $result = $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }

        if($result) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }


    private $tableFrom = 'user';

    /**
     * Get number of sql rows to be processed
     *
     * @return int number of rows
     */
    public function getNumberOfChunks()
    {

        return 100000;

        $tblName = $this->tableFrom;

        $sql = <<<SQL
SELECT count(*) as row_number FROM $tblName
SQL;

        $stmt = $this->selectDb->prepare($sql);

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
}
