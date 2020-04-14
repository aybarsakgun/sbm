<?php
/*
if(!defined('VAL3')) {
	die('Security');
}
define('VAL4', TRUE);
*/
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

define('GOOGLE_CLIENT_ID', '131963723658-22e1k2o18q3595j0lp8qnq10cvkndfr4.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'AI91AZm8xo0Q50pt2GQ2qbA_');
if(isset($base_request))
{
	if($base_request == "signup")
	{
		define('GOOGLE_REDIRECT_URL', 'http://localhost/sbm/signup-r');
	}
	else if($base_request == "signin")
	{
		define('GOOGLE_REDIRECT_URL', 'http://localhost/sbm/signin');
	}
    else if($base_request == "gc")
    {
        define('GOOGLE_REDIRECT_URL', 'http://localhost/sbm/sync-gc');
    }
}
else
{
	define('GOOGLE_REDIRECT_URL', 'http://localhost/sbm/signin');
}

require_once __DIR__ . '/google-api-php-client/vendor/autoload.php';

$gClient = new Google_Client();
$gClient->setApplicationName('Student Behavior Management');
$gClient->setClientId(GOOGLE_CLIENT_ID);
$gClient->setClientSecret(GOOGLE_CLIENT_SECRET);
$gClient->setRedirectUri(GOOGLE_REDIRECT_URL);
$gClient->setAccessType("offline");
$gClient->setApprovalPrompt("auto");
$gClient->setIncludeGrantedScopes(true);
if(isset($base_request))
{
    if($base_request == "gc")
    {
        $gClient->addScope("https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/classroom.rosters https://www.googleapis.com/auth/classroom.courses.readonly https://www.googleapis.com/auth/classroom.rosters.readonly https://www.googleapis.com/auth/classroom.profile.emails https://www.googleapis.com/auth/classroom.profile.photos");
    } else {
        $gClient->addScope("https://www.googleapis.com/auth/userinfo.email");
    }
}
else
{
    $gClient->addScope("https://www.googleapis.com/auth/userinfo.email");
}

$google_oauthV2 = new Google_Service_Oauth2($gClient);
$classroom = new Google_Service_Classroom($gClient);

date_default_timezone_set('America/New_York');

/*
date_default_timezone_set("Europe/Istanbul");

define("SECURE", FALSE);
if(isset($base_request) && $base_request == "admin")
{
	$admin = new ADMIN($DB_con);
}
*/
?>