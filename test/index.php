<?php

use miladm\Prototype;
use miladm\prototype\Schema;
use miladm\table\Connection;

require __DIR__ . "/../vendor/autoload.php";

class MainConnection extends Connection
{
    public $host = "127.0.0.1";
    public $databaseName = "sample";
    public $user = 'root';
    public $password = 'root';
}

class Security
{
    public static function hash($data)
    {
        return 'aaa' . md5($data);
    }
}

class UserP extends Prototype
{
    public function init(): Schema
    {
        return $this->schema('user')
            ->string('name')
            ->email('email')
            ->hash('password')->hashFunction(fn ($data) => Security::hash($data))
            ->json('something');
    }

    public function connection(): Connection
    {
        return new MainConnection;
    }
}
