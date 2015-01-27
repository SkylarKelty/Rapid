<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Rapid\Data;

class PDO
{
    /**
     * PDO object.
     */
    private $db;

    /**
     * DSN.
     */
    private $dsn;

    /**
     * Username.
     */
    private $username;

    /**
     * Password.
     */
    private $password;

    /**
     * Table prefix.
     */
    private $prefix;

    /**
     * Constructor
     */
    public function __construct($engine, $hostname, $port, $dbname, $username, $password, $prefix) {
        $this->dsn = "$engine:host=$hostname;port=$port;dbname=$dbname";
        $this->username = $username;
        $this->password = $password;
        $this->prefix = $prefix;

        $this->db = new \PDO($this->dsn, $this->username, $this->password, array(
            \PDO::ATTR_PERSISTENT => true
        ));
    }

    /**
     * Magic!
     */
    public function __destruct() {
        $this->db = null;
    }

    /**
     * Magic!
     */
    public function __sleep() {
        return array();
    }

    /**
     * Magic!
     */
    public function __wakeup() {
        $this->reset();
    }

    /**
     * Magic!
     */
    public function __get($name) {
        return $this->db->$name;
    }

    /**
     * Magic!
     */
    public function __set($name, $value) {
        $this->db->$name = $value;
    }

    /**
     * Magic!
     */
    public function __isset($name) {
        return isset($this->db->$name);
    }

    /**
     * Magic!
     */
    public function __unset($name) {
        unset($this->db->$name);
    }

    /**
     * Magic!
     */
    public function __call($func, $params) {
        return call_user_func_array(array($this->db, $func), $params);
    }

    /**
     * Magic!
     */
    public function reset() {
        $this->db = new \PDO($this->dsn, $this->username, $this->password);
    }

    /**
     * Get table name.
     */
    private function get_table($name) {
        return $this->prefix . $name;
    }

    /**
     * Replace curley brace table names with real table names.
     */
    private function substitute_tables($sql) {
        if (preg_match_all('/\{(.*?)\}/', $sql, $matches) > 0) {
            foreach ($matches[1] as $match) {
                $table = $this->get_table($match);
                $sql = str_replace("{{$match}}", $table, $sql);
            }
        }

        return $sql;
    }

    /**
     * Execute SQL and return statement
     */
    public function execute($sql, $params = array()) {
        $sql = $this->substitute_tables($sql);
        $stmt = $this->prepare($sql);

        foreach ($params as $k => $v) {
            if (!is_int($k)) {
                $k = ":{$k}";
            } else {
                $k++;
            }

            $stmt->bindValue($k, $v);
        }

        if ($stmt->execute() === false) {
            $error = $this->errorInfo();
            if ($error[0] > 0) {
                throw new \Rapid\Exception("Exception during database execute.", $error);
            }
        }

        return $stmt;
    }

    /**
     * Get records from DB.
     */
    public function get_records_sql($sql, $params = array()) {
        $stmt = $this->execute($sql, $params);

        $results = array();
        while (($obj = $stmt->fetchObject()) !== false) {
            if (isset($obj->id)) {
                $results[$obj->id] = $obj;
            } else {
                $results[] = $obj;
            }
        }

        $stmt->closeCursor();

        return $results;
    }

    /**
     * Get WHERE clause.
     */
    protected function get_where_clause($params = array()) {
        $sql = '';
        if (!empty($params)) {
            $sql .= ' WHERE';

            $joins = array();
            foreach ($params as $k => $v) {
                $joins[] = "`{$k}`= :{$k}";
            }

            $sql .= ' ' . implode(' AND ', $joins);
        }

        return $sql;
    }

    /**
     * Get records from DB.
     */
    public function get_records($table, $params = array(), $fields = '*') {
        if (is_array($fields)) {
            $fields = implode(', ', $fields);
        }

        $sql = "SELECT {$fields} FROM {{$table}}";
        $sql .= $this->get_where_clause($params);

        return $this->get_records_sql($sql, $params);
    }

    /**
     * Get a record from DB.
     */
    public function get_record($table, $params = array()) {
        $results = $this->get_records($table, $params);
        $count = count($results);

        if ($count > 1) {
            throw new \Rapid\Exception('get_record() yielded multiple results!');
        }

        if ($count === 0) {
            return null;
        }

        return array_pop($results);
    }
    
    /**
     * Returns a single field.
     */
    public function get_field($table, $field, $params = array()) {
        $record = $this->get_record($table, $params);
        return ($record && isset($record->$field)) ? $record->$field : null;
    }

    /**
     * Returns an array containing values of $field
     */
    public function get_fieldset($table, $field, $params = array()) {
        $records = $this->get_records($table, $params, $field);

        $results = array_map(function($obj) use($field) {
            return $obj->$field;
        }, $records);

        return $results;
    }

    /**
     * Get records from DB and convert them to models.
     */
    public function get_models($model, $params = array()) {
        $obj = new $model();
        $table = $obj->get_table();

        $data = $this->get_records($table, $params);

        $results = array();
        foreach ($data as $datum) {
            $obj = new $model();
            $obj->bulk_set_data($datum, true);
            $results[] = $obj;
        }

        return $results;
    }

    /**
     * Get a record from DB and convert it to a model.
     */
    public function get_model($model, $params = array()) {
        $results = $this->get_models($model, $params);
        $count = count($results);

        if ($count > 1) {
            throw new \Rapid\Exception('get_model() yielded multiple results!');
        }

        if ($count === 0) {
            return null;
        }

        return array_pop($results);
    }

    /**
     * Insert records.
     */
    public function insert_records($table, $params) {
        $params = (array)$params;
        if (empty($params)) {
            throw new \Rapid\Exception("Error in call to insert_record(...): \$params cannot be empty!");
        }

        $sqlkeys = array();
        $linezero = $params[0];
        ksort($linezero);
        $sqlkeys = array_keys($linezero);

        $sqlvalues = array();
        $binds = array();
        foreach ($params as $line) {
            ksort($line);

            $linevals = array();
            foreach ($line as $k => $v) {
                if (!in_array($k, $sqlkeys)) {
                    throw new \Rapid\Exception("Error in call to insert_record(...): Inconsistent keys!");
                }

                $linevals[] = "?";
                $binds[] = $v;
            }
            $sqlvalues[] = '(' . join(', ', $linevals) . ')';
        }
        $sqlvalues = join(', ', $sqlvalues);

        $sqlkeys = join(', ', $sqlkeys);
        $sql = "INSERT INTO {{$table}} ({$sqlkeys}) VALUES {$sqlvalues}";

        $this->execute($sql, $binds);
    }

    /**
     * Insert a record.
     */
    public function insert_record($table, $params) {
        $params = (array)$params;

        if (empty($params)) {
            throw new \Rapid\Exception("Error in call to insert_record(...): \$params cannot be empty!");
        }

        $this->insert_records($table, array($params));

        return $this->lastInsertId();
    }

    /**
     * Update a record.
     */
    public function update_record($table, $params) {
        $params = (array)$params;
        if (!isset($params['id'])) {
            throw new \Exception('update_record() must have ID set in params array.');
        }

        $sql = array();
        foreach ($params as $k => $v) {
            if ($k == 'id') {
                continue;
            }

            $sql[] = "`{$k}` = :{$k}";
        }
        $sql = join(', ', $sql);
        $sql = "UPDATE {{$table}} SET {$sql} WHERE `id`=:id";

        $this->execute($sql, $params);

        return true;
    }
    
    /**
     * Update or Insert helper
     */
    public function update_or_insert($table, $searchparams, $params) {
        $params = (array)$params;

        $records = $this->get_records($table, $searchparams);
        if (count($records) == 1) {
            $record = array_pop($records);
            $params['id'] = $record->id;
            return $this->update_record($table, $params);
        }

        return $this->insert_record($table, $params);
    }

    /**
     * Delete records matching the values in $params.
     */
    public function delete_records($table, $params) {
        if (empty($params)) {
            throw new \Rapid\Exception("Error in call to delete_records(...): \$params cannot be empty!");
        }

        $sql = array();
        foreach ($params as $k => $v) {
            $sql[] = "`{$k}` = :{$k}";
        }
        $sql = join(' AND ', $sql);
        $sql = "DELETE FROM {{$table}} WHERE {$sql}";

        $this->execute($sql, $params);

        return true;
    }

    /**
     * Count records.
     */
    public function count_records($table, $params = array()) {
        $params = (array)$params;

        $sql = "SELECT COUNT(*) AS count FROM {{$table}}";
        $sql .= $this->get_where_clause($params);

        $results = $this->get_records_sql($sql, $params);

        if (empty($results)) {
            throw new \Rapid\Exception("Error in call to count_records(...): No result from database, possibly a bad table name?");
        }

        $count = array_pop($results);
        return $count->count;
    }

    /**
     * Delete all records in a table.
     */
    public function truncate($table) {
        $this->execute("TRUNCATE {{$table}}");

        return true;
    }
}
