<?php
namespace Ars\Libraries\Database\QB;

trait Compile {

    /*
    |--------------------------------------------------------------------------
    | ESCAPE CHAR
    |--------------------------------------------------------------------------
    */

    protected function escape_char()
    {

        return match ($this->driver) {

            'pgsql',
            'sqlite' => '"',

            default => '`'
        };
    }

    /*
    |--------------------------------------------------------------------------
    | PROTECT IDENTIFIER
    |--------------------------------------------------------------------------
    */

    protected function protect_identifier(
        $identifier
    ){

        if ($identifier === '*') {

            return '*';
        }

        $identifier = trim($identifier);

        $escape =
            $this->escape_char();

        /*
        |--------------------------------------------------------------------------
        | MULTIPLE IDENTIFIER
        |--------------------------------------------------------------------------
        */

        if (str_contains($identifier, ',')) {

            $parts =
                array_map(
                    'trim',
                    explode(',', $identifier)
                );

            return implode(
                ', ',
                array_map(
                    fn($part) =>
                        $this->protect_identifier($part),
                    $parts
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SQL FUNCTION
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/^[A-Z_]+\(.+\)$/i',
                $identifier
            )
        ) {

            return $identifier;
        }

        /*
        |--------------------------------------------------------------------------
        | ALREADY ESCAPED
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($identifier, '`')
            || str_contains($identifier, '"')
        ) {

            return $identifier;
        }

        /*
        |--------------------------------------------------------------------------
        | ORDER DIRECTION
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/^(.+?)\s+(ASC|DESC)$/i',
                $identifier,
                $match
            )
        ) {

            return
                $this->protect_identifier(
                    $match[1]
                )
                . ' '
                . strtoupper($match[2]);
        }

        /*
        |--------------------------------------------------------------------------
        | ALIAS WITH AS
        |--------------------------------------------------------------------------
        */

        if (
            stripos(
                $identifier,
                ' AS '
            ) !== false
        ) {

            [$field, $alias] =
                preg_split(
                    '/\s+AS\s+/i',
                    $identifier
                );

            return
                $this->protect_identifier($field)
                . ' AS '
                . $this->protect_identifier($alias);
        }

        /*
        |--------------------------------------------------------------------------
        | ALIAS WITHOUT AS
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/^([a-zA-Z0-9_\.]+)\s+([a-zA-Z_][a-zA-Z0-9_]*)$/',
                $identifier,
                $match
            )
        ) {

            return
                $this->protect_identifier($match[1])
                . ' '
                . $this->protect_identifier($match[2]);
        }

        /*
        |--------------------------------------------------------------------------
        | DOT NOTATION
        |--------------------------------------------------------------------------
        */

        $parts =
            explode('.', $identifier);

        $parts =
            array_map(
                function ($part) use ($escape) {

                    return $part === '*'
                        ? '*'
                        : "{$escape}{$part}{$escape}";
                },
                $parts
            );

        return implode('.', $parts);
    }

    /*
    |--------------------------------------------------------------------------
    | COMPILED SELECT
    |--------------------------------------------------------------------------
    */

    public function get_compiled_select(
        $table = ''
    ){

        $table =
            $table ?: $this->table;

        /*
        |--------------------------------------------------------------------------
        | AUTO CLOSE GROUP
        |--------------------------------------------------------------------------
        */

        while (!empty($this->groupStack)) {

            $this->where[] = ')';

            array_pop($this->groupStack);
        }

        $table =
            $this->protect_identifier($table);

        $sql = 'SELECT ';

        /*
        |--------------------------------------------------------------------------
        | DISTINCT
        |--------------------------------------------------------------------------
        */

        if ($this->distinct) {

            $sql .= 'DISTINCT ';
        }

        /*
        |--------------------------------------------------------------------------
        | SELECT FIELD
        |--------------------------------------------------------------------------
        */

        if (empty($this->select)) {

            $sql .= '*';

        } else {

            $select = [];

            foreach ($this->select as $field) {

                $select[] =
                    $this->protect_identifier(
                        $field
                    );
            }

            $sql .=
                implode(', ', $select);
        }

        /*
        |--------------------------------------------------------------------------
        | FROM
        |--------------------------------------------------------------------------
        */

        $sql .= " FROM {$table}";

        /*
        |--------------------------------------------------------------------------
        | JOIN
        |--------------------------------------------------------------------------
        */

        if (!empty($this->join)) {

            $sql .= ' '
                . implode(' ', $this->join);
        }

        /*
        |--------------------------------------------------------------------------
        | WHERE
        |--------------------------------------------------------------------------
        */

        if (!empty($this->where)) {

            $sql .= ' WHERE '
                . implode(' ', $this->where);
        }

        /*
        |--------------------------------------------------------------------------
        | GROUP BY
        |--------------------------------------------------------------------------
        */

        if (!empty($this->groupBy)) {

            $groups = [];

            foreach ($this->groupBy as $group) {

                $groups[] =
                    $this->protect_identifier(
                        $group
                    );
            }

            $sql .= ' GROUP BY '
                . implode(', ', $groups);
        }

        /*
        |--------------------------------------------------------------------------
        | HAVING
        |--------------------------------------------------------------------------
        */

        if (!empty($this->having)) {

            $sql .= ' HAVING '
                . implode(' ', $this->having);
        }

        /*
        |--------------------------------------------------------------------------
        | ORDER
        |--------------------------------------------------------------------------
        */

        if (!empty($this->order)) {

            $sql .= ' '
                . $this->order;
        }

        /*
        |--------------------------------------------------------------------------
        | LIMIT OFFSET
        |--------------------------------------------------------------------------
        */

        if ($this->limit !== '') {

            switch ($this->driver) {

                case 'pgsql':
                case 'sqlite':

                    $sql .=
                        " LIMIT {$this->limit}";

                    if ($this->offset !== '') {

                        $sql .=
                            " OFFSET {$this->offset}";
                    }

                break;

                default:

                    if ($this->offset !== '') {

                        $sql .=
                            " LIMIT {$this->offset}, {$this->limit}";

                    } else {

                        $sql .=
                            " LIMIT {$this->limit}";
                    }

                break;
            }
        }

        return $sql;
    }

    /*
    |--------------------------------------------------------------------------
    | COMPILED INSERT
    |--------------------------------------------------------------------------
    */

    public function get_compiled_insert(
        $table = ''
    ){

        $table =
            $table ?: $this->table;

        $tableProtected =
            $this->protect_identifier(
                $table
            );

        if (empty($this->set)) {

            throw new \Exception(
                'INSERT membutuhkan data'
            );
        }

        $this->bindings = [];

        $rows =
            isset($this->set[0])
            && is_array($this->set[0])
            ? $this->set
            : [$this->set];

        $fields =
            array_keys($rows[0]);

        $fieldSql =
            array_map(
                fn($field) =>
                    $this->protect_identifier(
                        $field
                    ),
                $fields
            );

        $values = [];

        foreach ($rows as $i => $row) {

            $params = [];

            foreach ($fields as $field) {

                $param =
                    "{$field}_{$i}";

                $params[] =
                    ":{$param}";

                $this->bindings[$param] =
                    $row[$field] ?? null;
            }

            $values[] =
                '('
                . implode(', ', $params)
                . ')';
        }

        return
            "INSERT INTO {$tableProtected} ("
            . implode(', ', $fieldSql)
            . ') VALUES '
            . implode(', ', $values);
    }

    /*
    |--------------------------------------------------------------------------
    | COMPILED UPDATE
    |--------------------------------------------------------------------------
    */

    public function get_compiled_update(
        $table = ''
    ){

        $table =
            $table ?: $this->table;

        $tableProtected =
            $this->protect_identifier(
                $table
            );

        if (empty($this->set)) {

            throw new \Exception(
                'UPDATE tanpa SET'
            );
        }

        if (empty($this->where)) {

            throw new \Exception(
                'UPDATE tanpa WHERE berbahaya'
            );
        }

        $oldBindings =
            $this->bindings;

        $this->bindings = [];

        $setSql = [];

        $i = 0;

        foreach ($this->set as $field => $value) {

            $param =
                "update_{$field}_{$i}";

            $setSql[] =
                $this->protect_identifier($field)
                . " = :{$param}";

            $this->bindings[$param] =
                $value;

            $i++;
        }

        foreach ($oldBindings as $k => $v) {

            if (!isset($this->bindings[$k])) {

                $this->bindings[$k] = $v;
            }
        }

        return
            "UPDATE {$tableProtected} SET "
            . implode(', ', $setSql)
            . ' WHERE '
            . implode(' ', $this->where);
    }
    /*
    |--------------------------------------------------------------------------
    | COMPILED UPDATE BATCH
    |--------------------------------------------------------------------------
    */

    public function get_compiled_update_batch(
        $table = '',
        string $index = ''
    ){
    
        $table =
            $table ?: $this->table;
    
        $tableProtected =
            $this->protect_identifier(
                $table
            );
    
        /*
        |--------------------------------------------------------------------------
        | EMPTY
        |--------------------------------------------------------------------------
        */
    
        if (empty($this->set)) {
    
            throw new \Exception(
                'UPDATE BATCH tanpa data'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | INDEX
        |--------------------------------------------------------------------------
        */
    
        if ($index === '') {
    
            throw new \Exception(
                'UPDATE BATCH membutuhkan index'
            );
        }
    
        /*
        |--------------------------------------------------------------------------
        | RESET BINDINGS
        |--------------------------------------------------------------------------
        */
    
        $this->bindings = [];
    
        /*
        |--------------------------------------------------------------------------
        | FIELDS
        |--------------------------------------------------------------------------
        */
    
        $fields =
            array_keys($this->set[0]);
        
        
    
        /*
        |--------------------------------------------------------------------------
        | REMOVE INDEX FIELD
        |--------------------------------------------------------------------------
        */
    
        $fields =
            array_filter(
                $fields,
                fn($field) =>
                    $field !== $index
            );
        if (empty($fields)) {
            throw new \Exception(
                'Tidak ada kolom untuk diupdate'
            );
        }
        /*
        |--------------------------------------------------------------------------
        | BUILD CASE
        |--------------------------------------------------------------------------
        */
    
        $updates = [];
    
        foreach ($fields as $field) {
    
            $case =
                $this->protect_identifier($field)
                . ' = CASE';
    
            foreach ($this->set as $i => $row) {
    
                if (!isset($row[$index])) {
    
                    throw new \Exception(
                        "Index '{$index}' wajib ada"
                    );
                }
    
                $indexParam =
                    "{$index}_{$i}";
    
                $valueParam =
                    "{$field}_{$i}";
    
                $case .=
                    ' WHEN '
                    . $this->protect_identifier($index)
                    . " = :{$indexParam}"
                    . " THEN :{$valueParam}";
    
                $this->bindings[$indexParam] =
                    $row[$index];
    
                $this->bindings[$valueParam] =
                    $row[$field];
            }
    
            $case .= ' END';
    
            $updates[] = $case;
        }
    
        /*
        |--------------------------------------------------------------------------
        | WHERE IN
        |--------------------------------------------------------------------------
        */
    
        $whereIn = [];
    
        foreach ($this->set as $i => $row) {
    
            $whereIn[] =
                ":where_in_{$i}";
    
            $this->bindings["where_in_{$i}"] =
                $row[$index];
        }
    
        /*
        |--------------------------------------------------------------------------
        | SQL
        |--------------------------------------------------------------------------
        */
    
        return
            "UPDATE {$tableProtected} SET "
            . implode(', ', $updates)
            . ' WHERE '
            . $this->protect_identifier($index)
            . ' IN ('
            . implode(', ', $whereIn)
            . ')';
    }
    /*
    |--------------------------------------------------------------------------
    | COMPILED DELETE
    |--------------------------------------------------------------------------
    */

    public function get_compiled_delete(
        $table = ''
    ){

        $table =
            $table ?: $this->table;

        $table =
            $this->protect_identifier(
                $table
            );

        if (empty($this->where)) {

            throw new \Exception(
                'DELETE tanpa WHERE berbahaya'
            );
        }

        return
            "DELETE FROM {$table}"
            . ' WHERE '
            . implode(' ', $this->where);
    }

    /*
    |--------------------------------------------------------------------------
    | COMPILED TRUNCATE
    |--------------------------------------------------------------------------
    */

    public function get_compiled_truncate(
        $table = ''
    ){

        $table =
            $table ?: $this->table;

        $table =
            $this->protect_identifier(
                $table
            );

        switch ($this->driver) {

            case 'sqlite':

                return
                    "DELETE FROM {$table}";

            default:

                return
                    "TRUNCATE {$table}";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | COMPILED EMPTY TABLE
    |--------------------------------------------------------------------------
    */

    public function get_compiled_empty_table(
        $table = ''
    ){

        $table =
            $table ?: $this->table;

        $table =
            $this->protect_identifier(
                $table
            );

        return
            "DELETE FROM {$table}";
    }

    /*
    |--------------------------------------------------------------------------
    | COMPILED REPLACE
    |--------------------------------------------------------------------------
    */

    public function get_compiled_replace(
        $table = ''
    ){

        $table =
            $table ?: $this->table;

        $tableProtected =
            $this->protect_identifier(
                $table
            );

        if (empty($this->set)) {

            throw new \Exception(
                'REPLACE membutuhkan data'
            );
        }

        $this->bindings = [];

        $row =
            isset($this->set[0])
            && is_array($this->set[0])
            ? $this->set[0]
            : $this->set;

        $fields =
            array_keys($row);

        $fieldSql =
            array_map(
                fn($field) =>
                    $this->protect_identifier(
                        $field
                    ),
                $fields
            );

        $params = [];

        foreach ($fields as $i => $field) {

            $param =
                "replace_{$field}_{$i}";

            $params[] =
                ":{$param}";

            $this->bindings[$param] =
                $row[$field];
        }

        /*
        |--------------------------------------------------------------------------
        | MYSQL
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $this->driver,
                ['mysql', 'mysqli']
            )
        ) {

            return
                "REPLACE INTO {$tableProtected} ("
                . implode(', ', $fieldSql)
                . ') VALUES ('
                . implode(', ', $params)
                . ')';
        }

        /*
        |--------------------------------------------------------------------------
        | SQLITE
        |--------------------------------------------------------------------------
        */

        if ($this->driver === 'sqlite') {

            return
                "INSERT OR REPLACE INTO {$tableProtected} ("
                . implode(', ', $fieldSql)
                . ') VALUES ('
                . implode(', ', $params)
                . ')';
        }

        /*
        |--------------------------------------------------------------------------
        | PGSQL
        |--------------------------------------------------------------------------
        */

        throw new \Exception(
            'REPLACE tidak didukung PostgreSQL'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ORDER RANDOM
    |--------------------------------------------------------------------------
    */

    public function order_random()
    {

        switch ($this->driver) {

            case 'pgsql':
            case 'sqlite':

                $this->order =
                    'ORDER BY RANDOM()';

            break;

            default:

                $this->order =
                    'ORDER BY RAND()';

            break;
        }

        return $this;
    }
}