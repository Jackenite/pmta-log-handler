<?php

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/Constants.php';
require_once __DIR__.'/Handler.php';

/** @var array $config */
$config = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__.DIRECTORY_SEPARATOR.'config/parameters.yml');

/** @var array $parameters */
$parameters = [];

try {
    if (empty($config['parameters'])) {
        throw new Exception('"parameters" node must be configured under parameters.yml file');
    }

    if (empty($config['parameters']['database_host'])) {
        throw new Exception('"database_host" node must be configured under parameters. in parameters.yml file');
    }

    if (empty($config['parameters']['database_name'])) {
        throw new Exception('"database_name" node must be configured under parameters. in parameters.yml file');
    }

    if (empty($config['parameters']['database_user'])) {
        throw new Exception('"database_user" node must be configured under parameters. parameters.yml file');
    }

    if (empty($config['parameters']['database_password'])) {
        throw new Exception('"database_password" node must be configured under parameters. in parameters.yml file');
    }
    /** @var Handler $handler */
    $handler = new Handler($config['parameters']);
} catch (\Exception $e) {
    exit($e->getMessage());
}
