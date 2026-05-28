<?php
namespace Ars\Libraries\Database\QB;
trait Select {

    /*
    |--------------------------------------------------------------------------
    | SELECT
    |--------------------------------------------------------------------------
    */

    public function select(
        $select = '*',
        $escape = null
    ){

        if (is_array($select)) {

            $this->select =
                implode(",", $select);

        } else {

            $this->select =
                trim($select);
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | SELECT FUNCTION
    |--------------------------------------------------------------------------
    */

    private function select_func(
        $func,
        $field,
        $alias = ''
    ){

        $func =
            strtoupper(trim($func));

        $alias =
            $alias ?: $field;

        $this->select =
            "{$func}({$field}) AS `{$alias}`";

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | SELECT AVG
    |--------------------------------------------------------------------------
    */

    public function select_avg(
        $field,
        $alias = ''
    ){

        return $this->select_func(
            'AVG',
            $field,
            $alias
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SELECT MAX
    |--------------------------------------------------------------------------
    */

    public function select_max(
        $field,
        $alias = ''
    ){

        return $this->select_func(
            'MAX',
            $field,
            $alias
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SELECT MIN
    |--------------------------------------------------------------------------
    */

    public function select_min(
        $field,
        $alias = ''
    ){

        return $this->select_func(
            'MIN',
            $field,
            $alias
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SELECT SUM
    |--------------------------------------------------------------------------
    */

    public function select_sum(
        $field,
        $alias = ''
    ){

        return $this->select_func(
            'SUM',
            $field,
            $alias
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DISTINCT
    |--------------------------------------------------------------------------
    */

    public function distinct(
        $val = true
    ){

        $this->distinct =
            (bool) $val;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | FROM
    |--------------------------------------------------------------------------
    */

    public function from($table){

        $this->from =
            trim($table);

        $this->table =
            trim($table);

        return $this;
    }
    /*
|--------------------------------------------------------------------------
| GROUP BY
|--------------------------------------------------------------------------
*/

public function group_by($group){

    if (is_array($group)) {

        foreach ($group as $g) {

            $this->groupBy[] = trim($g);
        }

    } else {

        $this->groupBy[] = trim($group);
    }

    return $this;
}

}