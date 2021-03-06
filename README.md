This is a small ORM that I wrote for fun. Here's how you use it! Requires: PHP 5.3+ 

Create database schema:

    CREATE  TABLE `users` (
            `user_id` INT NOT NULL AUTO_INCREMENT ,
            `username` VARCHAR(45) NULL ,
            `email` VARCHAR(45) NULL ,
            `password` VARCHAR(45) NULL ,
            PRIMARY KEY (`user_id`) 
    );

Now use the class.



    <?php
    require_once 'Record.class.php';
    
    class User extends Record {
        public function __construct($database, $table = null, $primary_key = null) {
            parent::__construct($database);
            $this->init->__invoke(isset($table) ? $table : get_class(), $primary_key);
        }
    }
    ?>
That's it! that's all you need to get started!

Create a database connection using the provided helper

    $db = new MysqliDb('host', 'database_username', 'my_password', 'database_name');

By default this will look for a table that has the same name as the class. In this case it is 'user'.

    new User($db);

You can override this by telling it what your table name is.

    new User($db, 'users');

By default it will also look for a primary key called classname_id (in our case
user_id) There is no need to override in this case but you can specify it here.

    $user = new User($db, 'users', 'user_id');
    
Find a row. You can use any database column here.

    $rows = $user -> find_where(
            array(
                  'username' => 'jonathan.frias',
                  'user_id' => 1
                 )
    );

Print all rows found

    print_r($rows);
    print_r($user -> rows);

By convention the currently active row (default $rows[0]) is set.
Notice that this will print out the same row.

    print_r($user -> row);
    print_r($user -> rows[0]);

There is a coressponding function for every column in the database.

    $user -> password("new password!");

Save your object to the database. 
Don't worry about SQL injection! This ORM uses prepared statements to run all of the inserts.

    $user -> save();

Reset resultset

    $user -> reset();//$user -> row = null; $user -> rows = null;

Delete the current row from the database.

    $user -> delete();

