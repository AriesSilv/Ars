<?php
namespace Ars\Libraries\Database\QB;
trait Compile {

    /*
    |--------------------------------------------------------------------------
    | COMPILED SELECT
    |--------------------------------------------------------------------------
    */

    public function get_compiled_select(
        $table = '',
        $reset = true
    ){

        $table = $table ?: $this->from ?: $this->table;

        /*
        |--------------------------------------------------------------------------
        | AUTO CLOSE GROUP
        |--------------------------------------------------------------------------
        */

        while (!empty($this->groupStack)) {

            $this->where[] = ")";

            array_pop($this->groupStack);
        }

        /*
        |--------------------------------------------------------------------------
        | SELECT
        |--------------------------------------------------------------------------
        */

        $sql = "SELECT ";

        if (!empty($this->distinct)) {

            $sql .= "DISTINCT ";
        }

        $sql .= ($this->select ?? '*');

        $sql .= " FROM `{$table}`";

        /*
        |--------------------------------------------------------------------------
        | JOIN
        |--------------------------------------------------------------------------
        */

        if (!empty($this->join)) {

            $sql .= " "
                . implode(" ", $this->join);
        }

        /*
        |--------------------------------------------------------------------------
        | WHERE
        |--------------------------------------------------------------------------
        */

        if (!empty($this->where)) {

            $sql .= " WHERE "
                . implode(" ", $this->where);
        }

        /*
        |--------------------------------------------------------------------------
        | GROUP BY
        |--------------------------------------------------------------------------
        */

        if (!empty($this->groupBy)) {

            $sql .= " GROUP BY "
                . implode(",", $this->groupBy);
        }

        /*
        |--------------------------------------------------------------------------
        | HAVING
        |--------------------------------------------------------------------------
        */

        if (!empty($this->having)) {

            $sql .= " HAVING "
                . implode(" ", $this->having);
        }

        /*
        |--------------------------------------------------------------------------
        | ORDER
        |--------------------------------------------------------------------------
        */

        if (!empty($this->order)) {

            $sql .= " "
                . $this->order;
        }

        /*
        |--------------------------------------------------------------------------
        | LIMIT
        |--------------------------------------------------------------------------
        */

        if (!empty($this->limit)) {

            $sql .= " "
                . $this->limit;
        }

        if ($reset) {

            $this->resetQB();
        }

        return $sql;
    }

    /*
    |--------------------------------------------------------------------------
    | COMPILED INSERT
    |--------------------------------------------------------------------------
    */

    public function get_compiled_insert(
        $table = '',
        $reset = true
    ){

        $table = $table ?: $this->table;

        $fields = [];

        $params = [];

        foreach ($this->set as $set) {

            preg_match(
                '/`(.+?)`/',
                $set,
                $fieldMatch
            );

            preg_match(
                '/:(\w+)/',
                $set,
                $paramMatch
            );

            $field =
                $fieldMatch[1] ?? null;

            $param =
                $paramMatch[1] ?? null;

            if (!$field || !$param) {
                continue;
            }

            $fields[] = "`{$field}`";

            $params[] = ":{$param}";
        }

        $sql =
            "INSERT INTO `{$table}` ("
            . implode(",", $fields)
            . ") VALUES ("
            . implode(",", $params)
            . ")";

        if ($reset) {

            $this->resetQB();
        }

        return $sql;
    }

    /*
    |--------------------------------------------------------------------------
    | COMPILED UPDATE
    |--------------------------------------------------------------------------
    */

    public function get_compiled_update(
        $table = '',
        $reset = true
    ){

        $table = $table ?: $this->table;

        if (empty($this->set)) {

            throw new Exception(
                "UPDATE tanpa SET"
            );
        }

        if (empty($this->where)) {

            throw new Exception(
                "UPDATE tanpa WHERE berbahaya"
            );
        }

        $sql =
            "UPDATE `{$table}` SET "
            . implode(",", $this->set);

        $sql .= " WHERE "
            . implode(" ", $this->where);

        if ($reset) {

            $this->resetQB();
        }

        return $sql;
    }

    /*
    |--------------------------------------------------------------------------
    | COMPILED DELETE
    |--------------------------------------------------------------------------
    */

    public function get_compiled_delete(
        $table = '',
        $reset = true
    ){

        $table = $table ?: $this->table;

        if (empty($this->where)) {

            throw new Exception(
                "DELETE tanpa WHERE berbahaya"
            );
        }

        $sql =
            "DELETE FROM `{$table}`";

        $sql .= " WHERE "
            . implode(" ", $this->where);

        if ($reset) {

            $this->resetQB();
        }

        return $sql;
    }

}