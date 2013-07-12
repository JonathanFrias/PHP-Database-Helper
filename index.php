<?php

require 'MysqliDb.class.php';
class Record {

    protected $properties;
    public $rows;
    public $row;
    protected $primary_key;
    protected $db;

    public function __construct(MysqliDb $database, $table = null, $primary_key = null) {
        $this->db = $database;
        $this->primary_key = $primary_key;
        $instance = $this;
        
        //define init function that will be ran later
        $this->init = function($table = null) use (&$instance) {
                //default table name to the name of the child class.                      
                if (!isset($table)) {
                    $table = get_called_class();
                }
                $this->table = $table;
                
                
                //get the list of columns for this table.
                $sql = "SHOW COLUMNS FROM " . $table;
                $cols = $this->db->rawQuery($sql);
                foreach ($cols as $col) {
                    
                    //create callable functions for each column name.
                    $instance->find_where = function (array $where_filter = null) use (&$instance) {

                            //dont know why I have to do this next line...
                            $where_filter = $where_filter[0];
                            foreach ($where_filter as  $key => $value){
                                $instance->db->where($key,$value);
                            }

                            $instance->rows = $instance->db->get($this->table);
                            $instance -> row = $instance->rows[0];
                            return $instance -> rows;
                    };
                    
                    $instance->${$col['Field']} = function ($x) use (&$instance){
                        if(isset($this->row)){
                            $instance->row[$col['Field']] = $x;
                        }else{
                            throw new Exception("No row has been loaded yet!");
                        }
                    };
                    $instance -> save = function () use(&$instance) {        
                        if(isset($instance->row[$instance->primary_key])){
                            //record exists in database.
                            $instance->db
                                ->where($instance->primary_key, $instance->row[$instance->primary_key])
                                ->update($instance->table, $instance->row);
                        }else{
                            $instance->db->insert($instance->table, $instance->row);
                        }
                    };
                    $instance -> reset = function() use(&$instance){
                        $instance->row = null;
                        $instance->rows = null;
                    };
                    $instance -> delete = function() use(&$instance){
                        $instance -> db -> where (array(
                                $instance -> primary_key => $instance -> row[$instance -> primary_key]
                            )
                        ) -> delete();
                    };
                }
        };
    }

    public function __get($key) {
        return $this->properties[$key];
    }

    public function __set($key, $value) {
        $this->properties[$key] = $value;
    }
    
    public function __call($name, $arguments) {
        return call_user_func($this->{$name},$arguments);
    }
}

class User extends Record {
    public function __construct($table = null, $primary_key = null) {
        parent::__construct(new MysqliDb('localhost', 'root', '123', 'asdf_development'), null, 'id');
        $this->init->__invoke(isset($table) ? $table : get_class());
        
    }
}

$user = new User('users', 'id');
//Get data based off 
//Dynamic column functions(according to table schema)
$rows = $user->find_where(
    array('id' => 1)
); 

//This returns the same thing
print_r($rows);
print_r($user->rows);

//First row is currently 'selected' by default
print_r($user->row);


//update and save record
$user->rows['username'] = 'my new username';
$user -> save();
//reset object and find record again.
$user -> reset(); 
$rows = $user->find_where(
    array('id' => 1)
);
print_r($user->row);//object has been changed

$user->username('Also a new my new username2');

?>
