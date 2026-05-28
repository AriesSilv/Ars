<?php
namespace Ars\Libraries\Database\QB;
trait Batch {

    /*
    |--------------------------------------------------------------------------
    | SET INSERT BATCH
    |--------------------------------------------------------------------------
    */

    public function set_insert_batch(
        $key,
        $value = '',
        $escape = null
    ){

        if (empty($key)) {
            return $this;
        }

        // multi row
        if (isset($key[0]) && is_array($key[0])) {

            foreach ($key as $row) {

                $this->insertBatch[] = $row;

            }

        } else {

            // single row
            $this->insertBatch[] = $key;

        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT BATCH
    |--------------------------------------------------------------------------
    */

    public function insert_batch(
        $table = '',
        $set = null,
        $escape = null,
        $batch_size = 100
    ){

        $table = $table ?: $this->table;

        if ($set) {
            $this->set_insert_batch($set);
        }

        if (empty($this->insertBatch)) {
            return false;
        }

        $affected = 0;

        $chunks = array_chunk(
            $this->insertBatch,
            $batch_size
        );

        foreach ($chunks as $chunk) {

            $fields = array_keys($chunk[0]);

            $fieldList =
                "`" . implode("`,`", $fields) . "`";

            $values = [];
            $bindings = [];

            foreach ($chunk as $i => $row) {

                $rowParams = [];

                foreach ($row as $key => $val) {

                    $param = "{$key}_{$i}";

                    $rowParams[] = ":{$param}";

                    $bindings[$param] = $val;
                }

                $values[] =
                    "(" . implode(",", $rowParams) . ")";
            }

            $sql =
                "INSERT INTO `{$table}` ({$fieldList}) VALUES "
                . implode(",", $values);

            $this->query($sql);

            foreach ($bindings as $k => $v) {

                $this->bind($k, $v);

            }

            $this->execute();

            $affected += $this->rowCount();
        }

        $this->insertBatch = [];

        $this->resetQB();

        return $affected;
    }

    /*
    |--------------------------------------------------------------------------
    | SET UPDATE BATCH
    |--------------------------------------------------------------------------
    */

    public function set_update_batch(
        $key,
        $value = '',
        $escape = null
    ){

        if (empty($key)) {
            return $this;
        }

        // multi row
        if (
            isset($key[0]) &&
            is_array($key[0])
        ) {

            foreach ($key as $row) {

                $this->updateBatch[] = $row;

            }

            return $this;
        }

        // single row
        if (!empty($key)) {

            $this->updateBatch[] = [
                $key => $value
            ];

        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE BATCH
    |--------------------------------------------------------------------------
    */

    public function update_batch(
        $table = '',
        $set = null,
        $index = null,
        $batch_size = 100
    ){

        $table = $table ?: $this->table;

        if ($set) {
            $this->set_update_batch($set);
        }

        if (
            empty($this->updateBatch) ||
            !$index
        ) {
            return false;
        }

        $affected = 0;

        $chunks = array_chunk(
            $this->updateBatch,
            $batch_size
        );

        foreach ($chunks as $chunk) {

            $ids = [];

            $cases = [];

            $bindings = [];

            foreach ($chunk as $i => $row) {

                if (!isset($row[$index])) {

                    throw new Exception(
                        "Index '{$index}' tidak ditemukan di data batch"
                    );

                }

                $ids[] = $row[$index];

                foreach ($row as $col => $val) {

                    if ($col == $index) {
                        continue;
                    }

                    $paramVal = "{$col}_{$i}";
                    $paramIdx = "{$index}_{$i}";

                    $cases[$col][] =
                        "WHEN `{$index}` = :{$paramIdx} THEN :{$paramVal}";

                    $bindings[$paramIdx] =
                        $row[$index];

                    $bindings[$paramVal] =
                        $val;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | BUILD CASE SET
            |--------------------------------------------------------------------------
            */

            $setSql = [];

            foreach ($cases as $col => $case) {

                $setSql[] =
                    "`{$col}` = CASE "
                    . implode(" ", $case)
                    . " END";
            }

            /*
            |--------------------------------------------------------------------------
            | WHERE IN
            |--------------------------------------------------------------------------
            */

            $inParams = [];

            foreach ($ids as $i => $id) {

                $param = "{$index}_in_{$i}";

                $inParams[] = ":{$param}";

                $bindings[$param] = $id;
            }

            $sql =
                "UPDATE `{$table}` SET "
                . implode(",", $setSql)
                . " WHERE `{$index}` IN ("
                . implode(",", $inParams)
                . ")";

            $this->query($sql);

            foreach ($bindings as $k => $v) {

                $this->bind($k, $v);

            }

            $this->execute();

            $affected += $this->rowCount();
        }

        $this->updateBatch = [];

        $this->resetQB();

        return $affected;
    }

}