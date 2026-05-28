<?php
namespace Ars\Config;
class Database {
    public $default_connection = "default";
    public array $connections = [

        "default" => [

            "driver"   => "mysql",
            "host"     => "127.0.0.1",
            "port"     => 3306,
            "username" => "root",
            "password" => "root",
            "database" => "arspay",
            "charset"  => "utf8mb4",
        ],

        "analytics" => [

            "driver"   => "pgsql",
            "host"     => "localhost",
            "port"     => 5432,
            "username" => "postgres",
            "password" => "secret",
            "database" => "analytics",
            "charset"  => "utf8",
        ],

        "sqlite" => [

            "driver"   => "sqlite",
            "database" => "storage/database.sqlite",
        ],
    ];
}