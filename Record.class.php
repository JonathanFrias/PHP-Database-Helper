<?php

require 'MysqliDb.class.php';
/**
 * You should extend this class. 
 * 
 * By default, this class expects your db table to be the
 * name of the child class, but you can override this in 
 * the constructor. 
 * 
 * This class expects you to be using a primary key. 
 * BY default, the primary key is your {$dbTable}_id,
 * but you can override this in the constructor.
 * 
 * Your extended class should have the following type of constructor
 * 
 *   class User extends Record {
 *       public function __construct($database, $table = null, $primary_key = null) {
 *           parent::__construct($database, null, 'id');
 *           $this->init->__invoke(isset($table) ? $table : get_class());
 *       }
 *   }
 */
class Record {
    /**
     * Holds values for PHP magic properties
     */
    protected $properties;
    /**
     * The currently cached rows
     * @var array[][]
     */
    public $rows;
    /**
     * The currently active row.
     * @var array
     */
    public $row;
    /**
     * The table this object refers to
     */
    protected $table;
    /**
     * Table's primary key defaults to the child's class name
     * @var string
     */
    protected $primary_key;
    /**
     * Database options
     * @var MysqliDb
     */
    protected $db;

    /**
     * Parent constructor for child classes. Injects dynamic functions for child.
     * The options are to 
     * 1. Setup database connection
     * 2. override the default table name.
     * 3. override the default primary_key
     */
    public function __construct(MysqliDb $database, $table = null, $primary_key = null) {
        $this->db = $database;
        $this->primary_key = $primary_key;
        $instance = $this;
                
        /**
         * Defines an init script to inject functions
         */
        $this->init = function($table = null, $primary_key) use (&$instance) {
            
                if(!isset($table)){
                    $table = strtolower(get_called_class());
                }
            
                $instance -> table = $table;
                
                if(!isset($primary_key)){
                    $primary_key = strtolower(get_called_class() . '_id');
                }
                $instance -> primary_key = $primary_key;
                
                
                /**
                 * Find rows by criteria
                 */
                $instance->find_where = function (array $where_filter = null) use (&$instance) {

                        //dont know why I have to do this next line...
                        $where_filter = $where_filter[0];
                        foreach ($where_filter as  $key => $value){
                            $instance->db->where($key,$value);
                        }
                        
                        $instance->rows = $instance->db->get($instance->table);
                        $instance -> row = $instance->rows[0];
                        return $instance -> rows;
                };
                
                
                
                //get the list of columns for this table.
                $sql = "SHOW COLUMNS FROM " . $table;
                $cols = $this->db->rawQuery($sql);
                
                
                foreach ($cols as $col) {
                    
                    /**
                     * Function name is dynamicly generated, but corresponds to 
                     * the columns.
                     * 
                     * Sets a column field for the current row.
                     */
                    $instance->{$col['Field']} = function ($x) use (&$instance, $col){
                        if(isset($this->row)){
                            if(is_array($x)){
                                $x = $x[0];
                            }
                            $instance->row[$col['Field']] = $x;
                        }else{
                            throw new Exception("No row has been loaded yet!");
                        }
                    };
                    
                    /**
                     * Saves the object
                     */
                    $instance -> save = function () use(&$instance) {
                        $result = false;
                        if(isset($instance->row[$instance->primary_key])){
                            //record exists in database.
                            $instance->db
                                ->where($instance->primary_key, $instance->row[$instance->primary_key])
                                ->update($instance->table, $instance->row);
                            $result = true;
                        }else{
                            $instance->db->insert($instance->table, $instance->row);
                            $result = true;
                        }
                        return $result;
                    };
                    
                    /**
                     * Creates and saves the object
                     */
                    $instance -> create = function ($array) use(&$instance){
                        $instance -> row = $array;
                        $instance -> db -> save();
                    };
                    /**
                     * Creates and saves, then clears the object
                     */
                    $instance -> create_and_reset = function ($array) use(&$instance){
                        $instance -> row = $array;
                        $instance -> db -> save();
                        $instance -> reset();
                    };
                    /**
                     * Reset's class properties
                     */
                    $instance -> reset = function() use(&$instance){
                        $instance->row = null;
                        $instance->rows = null;
                    };
                    /**
                     * Delete the current row
                     */
                    $instance -> delete = function() use(&$instance){
                        $instance -> db -> where (
                                $instance -> primary_key, $instance -> row[$instance -> primary_key]
                        ) -> delete($instance->table);
                        return true;
                    };
                }
        };
    }

    /**
     * PHP magic getter
     */
    public function __get($key) {
        return $this->properties[$key];
    }

    /**
     * PHP magic setter
     */
    public function __set($key, $value) {
        $this->properties[$key] = $value;
    }
    
    /**
     * PHP magic caller
     */
    public function __call($name, $arguments) {
        return call_user_func($this->{$name},$arguments);
    }
}

?>
