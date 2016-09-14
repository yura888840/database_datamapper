<?php

use Migration\MigrationTool;

require "services.php";

foreach (glob(__DIR__ . "/mappers/*.php") as $mapperFile) {
    $file = substr($mapperFile, strrpos($mapperFile, "/") + 1);

    if(in_array($file, ["BaseMapper.php"])) {
        continue;
    }

    $className = substr($file, 0, strrpos($file, "."));

    $fullClassname = '\Migration\Mappers\\' . $className;

    try {
        $app = new MigrationTool($container, new $fullClassname($config, $container->get('db.usermanagement')));

        $app->execute();
    } catch (Exception $e) {
        echo 'ERROR : ' . $e->getMessage() . PHP_EOL;
    }
}

