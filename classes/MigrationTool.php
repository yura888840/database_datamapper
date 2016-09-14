<?php

namespace Migration;

use Migration\Mappers\BaseMapper;
use Migration\Models\UserModel;
use Monolog\Logger;

class MigrationTool
{
    /**
     * Number of queries in batch
     */
    const NUMBER_OF_QUERIES_IN_CHUNK = 2;

    /**
     * @var array Holder for data to insert
     */
    private $data;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var \PDO
     */
    private $selectDb;

    /**
     * @var \PDO
     */
    private $updateDb;

    /**
     * @var BaseMapper
     */
    private $mapper;

    /**
     * If debug setted, there is no actual inserts will be
     *
     * @var bool
     */
    private $debug = true;

    /**
     * @var array Input batch data
     */
    private $inputData;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var UserModel
     */
    private $model;

    /**
     * @var int
     */
    private $numberOfQueriesInChunk;

    /**
     * @var InputUserDataAdapter
     */
    private $adapter;

    public function __construct(Container $container, BaseMapper $mapper)
    {
        $this->container    = $container;
        $this->selectDb     = $container->get('db.onlineconvert');
        $this->updateDb     = $container->get('db.usermanagement');
        $this->mapper       = $mapper;

        $config             = $container->get('config');
        $loggerParams       = $config['logger']['name'];
        $this->logger       = $container->get('logger', [$loggerParams]);

        $this->model        = $container->get('usermodel');

        //@todo change this to config option
        $this->numberOfQueriesInChunk = self::NUMBER_OF_QUERIES_IN_CHUNK;

        $this->adapter = $this->container->get('inputdata.adapter');
    }

    /**
     * Main cycle of chunk Inserting
     */
    public function execute()
    {
        $numberOfUsersToProcess     = $this->model->getSizeOfUsersTable();
        $numberOfIterations         = floor($numberOfUsersToProcess / $this->numberOfQueriesInChunk);
        $progressBar                = $this->container->get('progressbar', [$numberOfIterations]);

        for ($chunkNumber = 0; $chunkNumber < $numberOfIterations; $chunkNumber++)
        {
            $this->doMigrationStep($chunkNumber);

            $progressBar->advance();

            time_nanosleep(0, 300);
        }
    }

    /**
     * Process 1 step of migration (with one Chunk)
     *
     * @param $chunkNumber
     */
    private function doMigrationStep($chunkNumber)
    {
        $this->getChunkData($chunkNumber);
        $this->applyMapping();
        $this->insertChunk();
    }

    // @todo - separate this into own class
    // варианты - можно делать выборку. И маппить. В соответствии с ней
    //  построчно
    private function getChunkData($chunkNumber)
    {
        list($users, $firstId, $lastId) = $this->model->getUsers($chunkNumber, $this->numberOfQueriesInChunk);

        $this->adapter->mapPartOfData($users);

        $data = $this->model->getUserContract($firstId, $lastId);

        $this->adapter->mapPartOfData($data);

        $data = $this->model->getUserApiContracts($firstId, $lastId);

        $this->adapter->mapPartOfData($data);

        $this->inputData = $this->adapter->getData();
    }

    /**
     * Apply mapping to input data
     */
    private function applyMapping()
    {
        $this->mapper->setData($this->inputData);
        $this->data = $this->mapper->map();
    }
    //у нас должны быть - old_id, везде

    /**
     * Batch Insert 1 chunk into Db
     *
     * @return bool success of operation
     */
    private function insertChunk()
    {
        $queries    = $this->prepareQueries();

        var_dump($queries);
        return true;

        $conn       = $this->updateDb;

        $conn->beginTransaction();

        foreach ($queries as $sql) {
            $stmt = $conn->prepare($sql);

            if (!$this->debug) {
                try {
                    $result = $stmt->execute();
                } catch (\PDOException $e) {
                    $conn->rollBack();
                    //@todo log event
                    return false;
                }

                if(!$result) {
                    $conn->rollBack();
                    return false;
                    //@todo log event
                }
            } else {
                file_put_contents(__DIR__ . '/../log/queries.log', $sql . PHP_EOL, FILE_APPEND);
            }
        }

        $conn->commit();

        return true;
    }

    /**
     * Return list of SQLs for inserting
     *
     * Input format :
     *
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
     *
     * @return array
     */
    private function prepareQueries()
    {
        $queries = [];

        foreach ($this->data as $userData) {
            foreach ($userData as $tableName => $rowData) {

                $columns    = implode(',', array_keys($rowData));

                $values     = $this->sanitizeValues($rowData);
                $values     = implode(',', $values);

                $sql = <<<SQL
INSERT IGNORE INTO $tableName ($columns) VALUES ($values)
SQL;

                $queries[] = $sql;
            }
        }

        return $queries;
    }

    /**
     * Sanitizing / escaping in values for the insert queries
     * @param $values
     *
     * @return array
     */
    private function sanitizeValues($values)
    {
        $preparedRow = [];//var_dump($values);die();
        array_walk($values, function ($v) use (&$preparedRow) {
            if(!in_array($v, ["last_insert_id()",])) {
                $preparedRow[] = sprintf('"%s"', $v);
            } else {
                $preparedRow[] = $v;
            }
        });

        return $preparedRow;
    }
}
