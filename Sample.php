<?php

require_once 'Record.class.php';

class User extends Record {
    public function __construct($database, $table = null, $primary_key = null) {
        parent::__construct($database, null, 'id');
        $this->init->__invoke(isset($table) ? $table : get_class());
    }
}
//That's it! that's all you need to get started!

//database connection
$db = new MysqliDb('localhost', 'root', '123', 'development');

//By default this will look for a table called 'user' in the database.
new User($db);

//You can override this by telling it what your table name is
new User($db, 'users');

//By default it will also look for a primary key called tablename_id or in our case
//user_id. There is no need to override in this case but you can specify it here.
$user = new User($db, 'users', 'user_id');

//Find a row. You can use any database column here.
$rows = $user -> find_where(
        array(
              'username' => 'jonathan.frias',
              'user_id' => 1
             )
);

//print all rows found
print_r($rows);

//By convention the currently active row(default $rows[0]) is set as follows:
print_r($row);
//also notice that this will print out the same as above.
print_r($user -> row);

//There is a coressponding function for every column in the database.
$user -> password("new password!");

//Save your object to the database. 
$user -> save();

//reset rows
$user -> reset();//$user -> row = null; $user -> rows = null;

//Delete the current row from the database.
$user -> delete();

?>
