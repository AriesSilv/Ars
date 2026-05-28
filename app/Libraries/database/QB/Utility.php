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

        $tables =
            $params["tables"]
            ?? $this->listTables();

        $output = "";

        foreach ($tables as $table) {

            /*
            |--------------------------------------------------------------------------
            | CREATE TABLE
            |--------------------------------------------------------------------------
            */

            $query =
                $this->query(
                    "SHOW CREATE TABLE `$table`"
                );

            $row =
                $query->rowArray();

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
                    "SELECT * FROM `$table`"
                );

            foreach (
                $query->resultArray()
                as $data
            ) {

                $columns =
                    array_map(
                        fn($v) =>
                        "`$v`",
                        array_keys($data)
                    );

                $values =
                    array_map(
                        fn($v) =>
                        "'" .
                        addslashes($v)
                        . "'",
                        array_values($data)
                    );

                $output .=
                    "INSERT INTO `$table` ("
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
    | LIST DATABASES
    |--------------------------------------------------------------------------
    */

    public function list_databases(){

        $query =
            $this->query(
                "SHOW DATABASES"
            );

        $result = [];

        foreach (
            $query->resultArray()
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
            $this->listTables()
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

        return $this->query(
            "OPTIMIZE TABLE `$table`"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REPAIR TABLE
    |--------------------------------------------------------------------------
    */

    public function repair_table(
        $table
    ){

        return $this->query(
            "REPAIR TABLE `$table`"
        );
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
            $query->resultArray();

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
            new SimpleXMLElement(
                "<?xml version=\"1.0\"?><$root></$root>"
            );

        foreach (
            $query->resultArray()
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
                    htmlspecialchars($value)
                );
            }
        }

        return $xml->asXML();
    }

}