<?php
if(!defined('VAL3')) {
	die('Security');
}
define('VAL4', TRUE);

$DB_host = "localhost";
$DB_user = "root";
$DB_pass = "";
$DB_name = "sbm";

try
{
    $DB_con = new PDO("mysql:host={$DB_host};dbname={$DB_name};charset=utf8",$DB_user,$DB_pass);
	$DB_con->exec("SET NAMES utf8mb4");
	$DB_con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e)
{
    echo $e->getMessage();
}

//date_default_timezone_set("Europe/Istanbul");

define("SECURE", FALSE);
if(isset($base_request) && $base_request == "admin")
{
	$admin = new ADMIN($DB_con);
}
?>