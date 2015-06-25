<?php

class DB
{
    private $_resource = null;

    public function __construct($config)
    {
        if (empty($config['type'])
            || empty($config['host'])
            || empty($config['base_name'])
            || empty($config['user'])
            || empty($config['password'])
            || empty($config['encode'])) {
            throw new Exception('Incorrect config');
        }

        $this->_resource = new PDO("{$config['type']}:host={$config['host']};dbname={$config['base_name']}", $config['user'], $config['password']);
        if ($this->_resource instanceof PDO) {
            $this->_resource->query("SET NAMES {$config['encode']}");
        }
    }

    /**
     * @return null|PDO
     */
    public function getConnect() {
        return $this->_resource;
    }
}