<?php

require_once __DIR__.'/../loader.php';

$tableName = 'receipts';

while ($data = fgetcsv(STDIN, 4096)) {
    if ('type' === $data[0]) {
        $createTable = $handler->createTableIfNotExists($tableName, $data);
        if (false === $createTable) {
            echo 'Tables exists, will not create one.'.PHP_EOL;
        } else {
            echo sprintf('Table %s was created.', $tableName).PHP_EOL;
        }
    } else {
        $insertRecord = $handler->insertRecord($tableName, $data);
        if (true === $insertRecord) {
            echo sprintf('Record inserted. Id: %s', $handler->getConn()->lastInsertId()).PHP_EOL;
        }
    }
}
