<?php

namespace rotmistrz\comments;

/********************************************************************
 *
 *  File's name: DataBase.php
 *  Script's author: Filip Markiewicz (www.filipmarkiewicz.pl)
 *
 *  Created: 05.11.2014r.
 *  Last modificated: 03.01.2015r.
 *
 ********************************************************************/

class DataBase
{
    static private $instance;

    static private $host =     DB_HOST;
    static private $name =     DB_NAME;
    static private $user =     DB_USERNAME;
    static private $password = DB_PASSWORD;

    private function __construct() {}
    private function __clone() {}

     /**
      *
      * @return PDO Connected with database PDO object. If it doesn't exist, it creates it.
      *
      */
    static public function getInstance()
    {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=".self::$host.";dbname=".self::$name;
                self::$instance = new \PDO($dsn, self::$user, self::$password, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                self::$instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch(\PDOException $e) {
                throw new \Exception("Wystąpił problem podczas nawiązywania połączenia z bazą danych.");
            }
            return self::$instance;
        }
        return self::$instance;
    }
}
