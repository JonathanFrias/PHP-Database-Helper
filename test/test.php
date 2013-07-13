<?php

require_once '../Record.class.php';

class User extends Record {

    public function __construct($database, $table = null, $primary_key = null) {
        parent::__construct($database);
        $this->init->__invoke(isset($table) ? $table : get_class(),$primary_key);
    }

}

class Test extends PHPUnit_Framework_TestCase {
    protected function setUp() {
        $db = new MysqliDb('localhost', 'root', '123', 'test');
        $db->query("DROP TABLE IF EXISTS `test`.`users`");
        $db->rawQuery("
            CREATE  TABLE `test`.`users` (
            `user_id` INT NOT NULL AUTO_INCREMENT ,
            `username` VARCHAR(45) NULL ,
            `email` VARCHAR(45) NULL ,
            `password_digest` VARCHAR(45) NULL ,
            PRIMARY KEY (`user_id`) );
        ",NULL);
        $db->rawQuery('INSERT INTO users (username,email,password_digest)
              VALUES(?,?,?)', array(
                  'username' => 'username',
                  'email' => 'jonathan.frias1@gmail.com',
                  'password_digest' => '154db6120a14b6f6f855e9d3edd41767'
        ));
    }

    public function test() {
        $db = new MysqliDb('localhost', 'root', '123', 'test');
        $instance = new User($db, 'users', null);
        $this->assertNotNull($instance);
    }
    
    public function testwhere(){
        $db = new MysqliDb('localhost', 'root', '123', 'test');
        $user = new User($db, 'users', null);
        $user-> find_where(
                array(
                    'user_id' => 1
                )
        );
        $this->assertNotNull($user -> row);
        $this->assertEquals(sizeof($user->row),4);
        
        $user->reset();
        $user -> find_where(
                array('email' => 'jonathan.frias1@gmail.com')
                );
        $this->assertNotNull($user -> row);
        $this->assertEquals(sizeof($user->row),4);
    }
    
    public function testColumnFunctions(){
        $db = new MysqliDb('localhost', 'root', '123', 'test');
        $user = new User($db, 'users', null);
        $user-> find_where(
                array(
                    'user_id' => 1
                )
        );
        $user -> username("jonathanFrias");
        $this -> assertTrue($user -> save());
    }
    public function testDelete(){
        $db = new MysqliDb('localhost', 'root', '123', 'test');
        $user = new User($db, 'users', null);
        $user-> find_where(
                array(
                    'user_id' => 1
                )
        );
        $this -> assertTrue($user -> delete());
    }
}

?>