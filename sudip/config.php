<?php
$dbhost = "localhost";
$dbname = "cargo_record";
$dbuser = "root";
$dbpassword = "";
$dbport = "3306";

$conn = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname, $dbport);

if (mysqli_connect_error()) {
    echo "Connection failed: " . mysqli_connect_error();
    exit;
} else {
}
?>