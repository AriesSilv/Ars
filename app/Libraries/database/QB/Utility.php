<?php
namespace Ars\Libraries\Database\QB;

trait Utility {

    /*
    |--------------------------------------------------------------------------
    | BACKUP DATABASE
    |--------------------------------------------------------------------------
    */

    public function backup(
        $params = []
    ){

        if ($this->driver !== 'mysql') {

            throw new \Exception(
                "Backup no supported"
            );
        }

        $tables =
            $params["tables"]
            ?? $this->list_tables();

        $output = "";

        foreach ($tables as $table) {

            /*
            |--------------------------------------------------------------------------
            | CREATE TABLE
            |--------------------------------------------------------------------------
            */

            $query =
                $this->query(
                    "SHOW CREATE TABLE `{$table}`"
                );

            $this->execute();

            $row =
                $query->row_array();

            $create =
                array_values($row)[1];

            $output .=
                $create . ";\n\n";

            /*
            |--------------------------------------------------------------------------
            | TABLE DATA
            |--------------------------------------------------------------------------
            */

            $query =
                $this->query(
                    "SELECT * FROM `{$table}`"
                );

            $this->execute();

            foreach (
                $query->result_array()
                as $data
            ) {

                $columns =
                    array_map(
                        fn($v) => "`{$v}`",
                        array_keys($data)
                    );

                $values =
                    array_map(
                        fn($v) =>
                            is_null($v)
                            ? "NULL"
                            : "'" . addslashes($v) . "'",
                        array_values($data)
                    );

                $output .=
                    "INSERT INTO `{$table}` ("
                    . implode(",", $columns)
                    . ") VALUES ("
                    . implode(",", $values)
                    . ");\n";
            }

            $output .= "\n\n";
        }

        return $output;
    }

    /*
    |--------------------------------------------------------------------------
    | DATABASE EXISTS
    |--------------------------------------------------------------------------
    */

    public function database_exists(
        $database
    ){

        return in_array(
            $database,
            $this->list_databases()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | LIST TABLES
    |--------------------------------------------------------------------------
    */

    public function list_tables(){

        switch ($this->driver) {

            case "mysql":

                $sql =
                    "SHOW TABLES";

            break;

            case "pgsql":

                $sql =
                    "SELECT tablename
                     FROM pg_tables
                     WHERE schemaname = 'public'";

            break;

            case "sqlite":

                $sql =
                    "SELECT name
                     FROM sqlite_master
                     WHERE type = 'table'";

            break;

            default:

                throw new \Exception(
                    "Driver '{$this->driver}' no supported"
                );
        }

        $this->query($sql);

        $this->execute();

        $result =
            $this->result_array();

        $tables = [];

        foreach ($result as $row) {

            $tables[] =
                array_values($row)[0];
        }

        return $tables;
    }

    /*
    |--------------------------------------------------------------------------
    | LIST DATABASES
    |--------------------------------------------------------------------------
    */

    public function list_databases(){

        switch ($this->driver) {

            case "mysql":

                $sql =
                    "SHOW DATABASES";

            break;

            case "pgsql":

                $sql =
                    "SELECT datname
                     FROM pg_database
                     WHERE datistemplate = false";

            break;

            case "sqlite":

                return [
                    $this->config["database"]
                ];

            default:

                throw new \Exception(
                    "Driver '{$this->driver}' not supported"
                );
        }

        $query =
            $this->query($sql);

        $this->execute();

        $result = [];

        foreach (
            $query->result_array()
            as $row
        ) {

            $result[] =
                array_values($row)[0];
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | OPTIMIZE DATABASE
    |--------------------------------------------------------------------------
    */

    public function optimize_database(){

        foreach (
            $this->list_tables()
            as $table
        ) {

            $this->optimize_table(
                $table
            );
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | OPTIMIZE TABLE
    |--------------------------------------------------------------------------
    */

    public function optimize_table(
        $table
    ){

        switch ($this->driver) {

            case "mysql":

                $sql =
                    "OPTIMIZE TABLE `{$table}`";

            break;

            case "pgsql":

                $sql =
                    "VACUUM ANALYZE {$table}";

            break;

            case "sqlite":

                $sql =
                    "VACUUM";

            break;

            default:

                throw new \Exception(
                    "Driver '{$this->driver}' not supported"
                );
        }

        $this->query($sql);

        return $this->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | REPAIR TABLE
    |--------------------------------------------------------------------------
    */

    public function repair_table(
        $table
    ){

        switch ($this->driver) {

            case "mysql":

                $sql =
                    "REPAIR TABLE `{$table}`";

            break;

            case "pgsql":

                throw new \Exception(
                    "PostgreSQL no support REPAIR TABLE"
                );

            case "sqlite":

                throw new \Exception(
                    "SQLite no support REPAIR TABLE"
                );

            default:

                throw new \Exception(
                    "Driver '{$this->driver}' not supported"
                );
        }

        $this->query($sql);

        return $this->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | CSV FROM RESULT
    |--------------------------------------------------------------------------
    */

    public function csv_from_result(
        $query,
        $delim = ",",
        $newline = "\n",
        $enclosure = '"'
    ){

        $output = "";

        $result =
            $query->result_array();

        if (empty($result)) {

            return $output;
        }

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $output .=
            implode(
                $delim,
                array_keys($result[0])
            ) . $newline;

        /*
        |--------------------------------------------------------------------------
        | ROWS
        |--------------------------------------------------------------------------
        */

        foreach (
            $result
            as $row
        ) {

            $line = [];

            foreach ($row as $value) {

                $line[] =
                    $enclosure
                    . str_replace(
                        $enclosure,
                        $enclosure . $enclosure,
                        $value
                    )
                    . $enclosure;
            }

            $output .=
                implode(
                    $delim,
                    $line
                ) . $newline;
        }

        return $output;
    }

    /*
    |--------------------------------------------------------------------------
    | XML FROM RESULT
    |--------------------------------------------------------------------------
    */

    public function xml_from_result(
        $query,
        $params = []
    ){

        $root =
            $params["root"]
            ?? "root";

        $element =
            $params["element"]
            ?? "element";

        $xml =
            new \SimpleXMLElement(
                "<?xml version=\"1.0\"?><$root></$root>"
            );

        foreach (
            $query->result_array()
            as $row
        ) {

            $item =
                $xml->addChild(
                    $element
                );

            foreach (
                $row
                as $key => $value
            ) {

                $item->addChild(
                    $key,
                    htmlspecialchars(
                        (string) $value
                    )
                );
            }
        }

        return $xml->asXML();
    }

}