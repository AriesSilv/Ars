<?php

namespace Ars\Libraries\Database;

use Ars\Core\Ars;
use Ars\Libraries\Database\QB\Query_builder;

use PDO;
use PDOException;
use Exception;

class Database extends Ars {
    use Query_builder;

    protected $dbh;

    protected $stmt;
    protected $driver = "mysql";
    protected $config;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCT
    |--------------------------------------------------------------------------
    */

    public function __construct(
        $connection = "default"
    ){

        parent::__construct();
        $this->connect(
            $connection
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CONNECT
    |--------------------------------------------------------------------------
    */

    private function connect(
        $connection
    ){

        /*
        |--------------------------------------------------------------------------
        | config
        |--------------------------------------------------------------------------
        */

        $config =
            $this->load
                ->config("Database");


        /*
        |--------------------------------------------------------------------------
        | CONNECTION EXISTS
        |--------------------------------------------------------------------------
        */

        if (
            !isset(
                $config->connections[$connection]
            )
        ) {

            throw new Exception(
                "Database connection '$connection' tidak ditemukan"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CONNECTION
        |--------------------------------------------------------------------------
        */

        $this->config =
            $config->connections[$connection];

        /*
        |--------------------------------------------------------------------------
        | DRIVER
        |--------------------------------------------------------------------------
        */

        $this->driver = strtolower($this->config["driver"]);
        if (!in_array($this->driver, PDO::getAvailableDrivers())) {
            throw new Exception("PDO driver '{$this->driver}' belum terinstall");
        }

        /*
        |--------------------------------------------------------------------------
        | DSN
        |--------------------------------------------------------------------------
        */

        switch ($this->driver) {

            case "mysql":

                $dsn =
                    "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['charset']}";

            break;

            case "pgsql":

                $dsn =
                    "pgsql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']}";

            break;

            case "sqlite":

                $dsn =
                    "sqlite:{$this->config['database']}";

            break;

            default:

                throw new Exception(
                    "Driver '{$this->driver}' tidak didukung"
                );
        }

        /*
        |--------------------------------------------------------------------------
        | PDO OPTIONS
        |--------------------------------------------------------------------------
        */

        $options = [

            PDO::ATTR_PERSISTENT =>
                true,

            PDO::ATTR_ERRMODE =>
                PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE =>
                PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES =>
                false,
        ];

        /*
        |--------------------------------------------------------------------------
        | configT
        |--------------------------------------------------------------------------
        */

        try {

            $this->dbh =
                new PDO(
                    $dsn,
                    $this->config["username"]
                        ?? null,
                    $this->config["password"]
                        ?? null,
                    $options
                );

        } catch (PDOException $e) {

            throw new Exception(
                $e->getMessage()
            );
        }
    }

}