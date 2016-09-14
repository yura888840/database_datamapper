<?php

use ProgressBar\Manager;
use Migration\Container;
use \Migration\Models\UserModel;

require __DIR__ . "/vendor/autoload.php";

$config = require __DIR__ . "/config/config.php";

$container = new Container();
$container->set('progressbar', function ($maxCounter) {return new Manager(0, $maxCounter);});

$selectConnection = null;

foreach ($config['db']['connections'] as $connectionName => $connectionParams) {

    $dsn = sprintf("mysql:dbname=%s;host=%s", $connectionParams['db'], $connectionParams['host']);

    try {
        $conn = new PDO($dsn, $connectionParams['username'], $connectionParams['password']);
    } catch (PDOException $e) {
        die('ERROR connecting to Database :: ' . $e->getMessage() . PHP_EOL);
    }

    if (!$selectConnection) {
        $selectConnection = $conn;
    }

    $container->set('db.'. $connectionName, $conn);
}

$container->set('logger', function ($loggerName) {return new \Monolog\Logger($loggerName);});

$container->set('config', $config);

$container->set('usermodel', new UserModel($selectConnection));

$container->set('inputdata.adapter', function () {return new \Migration\InputUserDataAdapter();});
