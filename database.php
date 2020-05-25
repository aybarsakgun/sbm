<?php
if(!defined('AJAX') && !defined('VAL2')) {
    die('Security');
}
define('VAL3', TRUE);
require_once("settings.php");

$DB_host = $databaseSettings['host'];
$DB_user = $databaseSettings['user'];
$DB_pass = $databaseSettings['password'];
$DB_name = $databaseSettings['databaseName'];

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

define('GOOGLE_CLIENT_ID', $googleSettings['GOOGLE_CLIENT_ID']);
define('GOOGLE_CLIENT_SECRET', $googleSettings['GOOGLE_CLIENT_SECRET']);
if(isset($base_request))
{
	if($base_request == "signup")
	{
		define('GOOGLE_REDIRECT_URL', $companyInformations['companyURL'].'signup-r');
	}
	else if($base_request == "signin")
	{
		define('GOOGLE_REDIRECT_URL', $companyInformations['companyURL'].'signin');
	}
    else if($base_request == "gc")
    {
        define('GOOGLE_REDIRECT_URL', $companyInformations['companyURL'].'sync-gc');
    }
}
else
{
	define('GOOGLE_REDIRECT_URL', $companyInformations['companyURL'].'signin');
}

require_once __DIR__ . '/google-api-php-client/vendor/autoload.php';

$gClient = new Google_Client();
$gClient->setApplicationName($googleSettings['APP_NAME']);
$gClient->setClientId(GOOGLE_CLIENT_ID);
$gClient->setClientSecret(GOOGLE_CLIENT_SECRET);
$gClient->setRedirectUri(GOOGLE_REDIRECT_URL);
$gClient->setAccessType("offline");
if (!empty($_COOKIE['sbmAutoLogin'])) {
    $gClient->setPrompt("none");
} else {
    $gClient->setPrompt("select_account");
    $gClient->setApprovalPrompt("auto");
}
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

date_default_timezone_set($companyInformations['timeZone']);
?>