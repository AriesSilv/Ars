<?php
namespace Ars\Libraries\Database\QB;
trait Order_limit {

    /*
    |--------------------------------------------------------------------------
    | ORDER BY
    |--------------------------------------------------------------------------
    */

    public function order_by(
        $orderby,
        $direction = '',
        $escape = null
    ){

        /*
        |--------------------------------------------------------------------------
        | ARRAY ORDER
        |--------------------------------------------------------------------------
        */

        if (is_array($orderby)) {

            $orders = [];

            foreach ($orderby as $key => $val) {

                if (is_numeric($key)) {

                    $orders[] = $val;

                } else {

                    $dir = strtoupper($val);

                    if (
                        !in_array(
                            $dir,
                            ['ASC', 'DESC']
                        )
                    ) {
                        $dir = 'ASC';
                    }

                    $orders[] =
                        "{$key} {$dir}";
                }
            }

            $this->order =
                "ORDER BY "
                . implode(",", $orders);

        } else {

            /*
            |--------------------------------------------------------------------------
            | SINGLE ORDER
            |--------------------------------------------------------------------------
            */

            $direction =
                strtoupper($direction);

            if (
                !in_array(
                    $direction,
                    ['ASC', 'DESC']
                )
            ) {
                $direction = 'ASC';
            }

            $this->order =
                "ORDER BY {$orderby} {$direction}";
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | LIMIT
    |--------------------------------------------------------------------------
    */

    public function limit(
        $value,
        $offset = 0
    ){

        $value =
            max(0, (int) $value);

        $offset =
            max(0, (int) $offset);

        /*
        |--------------------------------------------------------------------------
        | MYSQL LIMIT
        |--------------------------------------------------------------------------
        */

        if ($offset > 0) {

            $this->limit =
                "LIMIT {$offset}, {$value}";

        } else {

            $this->limit =
                "LIMIT {$value}";
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | OFFSET
    |--------------------------------------------------------------------------
    */

    public function offset($offset){

        $offset =
            max(0, (int) $offset);

        /*
        |--------------------------------------------------------------------------
        | REBUILD LIMIT
        |--------------------------------------------------------------------------
        */

        if (!empty($this->limit)) {

            preg_match(
                '/LIMIT\s+(\d+)(?:,\s*(\d+))?/i',
                $this->limit,
                $match
            );

            /*
            |--------------------------------------------------------------------------
            | LIMIT EXISTS
            |--------------------------------------------------------------------------
            */

            if (isset($match[2])) {

                $limitVal = $match[2];

            } else {

                $limitVal =
                    $match[1] ?? 0;
            }

            $this->limit =
                "LIMIT {$offset}, {$limitVal}";

        } else {

            /*
            |--------------------------------------------------------------------------
            | STORE OFFSET
            |--------------------------------------------------------------------------
            */

            $this->offset =
                "OFFSET {$offset}";
        }

        return $this;
    }

}