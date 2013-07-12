<?php

//both of these guys will return the same thing.
print_r($rows);
print_r($user->rows);

//get the 'current' row 
//default is first row. (rows[0])
print_r($user->row);

//update current row
$index = 4;
$user -> row = $user->rows[$index];

//context sensitive. Object has already loaded rows, 
//so the column functions will now modify the current row.
$user->username('MyUsername');

//save your changes (insert or update)
$user -> save();
?>
