<?php

// four variables to connect to the database
$host = "localhost";
$username = "your_db_username";
$user_pass = "your_db_password";
$database_in_use = "library";

// create a database connection instance
$mysqli = new mysqli($host, $username, $user_pass, $database_in_use);

if ($mysqli->connect_errno) {
    echo "FATAL ERROR: CONTACT ADMIN - Failed to connect to MySQL Database: (" . $mysqli->connect_errno . ") " . $mysqli->connect_errno;
}

?>