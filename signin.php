<?php
define('AJAX', TRUE);
$base_request = "signin";
require_once 'database.php';
require_once 'functions.php';
SessionStartUser();
$sbmAutoLogin = false;
$loginhint = "";
if(LoginCheckUser($DB_con) == true)
{
	header("Location: home");
	exit();
} else if (LoginCheckUser($DB_con) == false && !empty($_COOKIE['sbmAutoLogin']) && !isset($_GET['error'])) {
    $sbmAutoLogin = true;
    $loginhint = '&login_hint='.$_COOKIE['sbmAutoLogin'];
} else if (LoginCheckUser($DB_con) == false && !empty($_COOKIE['sbmAutoLogin']) && isset($_GET['error'])) {
    setcookie('sbmAutoLogin', null, -1, '/');
}
if(isset($_GET['code']))
{
    $gClient->authenticate($_GET['code']);
    $_SESSION['token'] = $gClient->getAccessToken();
}
if(isset($_SESSION['token']))
{
    $gClient->setAccessToken($_SESSION['token']);
}
if($gClient->getAccessToken())
{
	$gpUserProfile = $google_oauthV2->userinfo->get();
	$gpUserData = array();
	$gpUserData['google_id'] = !empty($gpUserProfile['id'])?$gpUserProfile['id']:'';
	$gpUserData['google_email'] = !empty($gpUserProfile['email'])?$gpUserProfile['email']:'';
	$sorguUye = $DB_con->prepare("SELECT COUNT(id) AS say,id FROM users WHERE google_id = :gid AND email = :email AND invite_token = :invt AND register_date IS NOT NULL");
	$sorguUye->execute(array(":gid"=>$gpUserData["google_id"],":email"=>$gpUserData["google_email"],":invt"=>"USED"));
	$yazUye = $sorguUye->fetch(PDO::FETCH_ASSOC);
	if($yazUye["say"] == 1)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$_SESSION['user_id'] = $gpUserData["google_id"];
		$getUserMail = filter_var($gpUserData["google_email"], FILTER_SANITIZE_EMAIL);
		$_SESSION['user_mail'] = $getUserMail;
		$_SESSION['user_login_string'] = hash('sha512', $gpUserData["google_id"].$gpUserData["google_email"].$browser.$ip);
        setcookie("sbmAutoLogin", $gpUserData["google_email"], time()+3600*24*30, '/');
		$status = 1;
		$platform = "google";
		$date = date("d F Y H:i:s");
		$date_time = date('Y-m-d H:i:s');

		$stmt = $DB_con->prepare("INSERT INTO login_attempts_user(member_id, date, ip, browser, platform, date_time, status, verify) VALUES (:memberid, :date, :ip, :browser, :platform, :datetime, :status, :verify)");
		$stmt->bindparam(":memberid",$yazUye["id"]);
		$stmt->bindparam(":date",$date);
		$stmt->bindparam(":ip",$ip);
		$stmt->bindparam(":browser",$browser);
		$stmt->bindparam(":platform",$platform);
		$stmt->bindparam(":datetime",$date_time);
		$stmt->bindparam(":status",$status);
		$stmt->bindparam(":verify",$status);
		$stmt->execute();
		
		$date_time2 = date("Y-m-d H:i:s", strtotime(" -5 minutes"));
		
		$fails = 0;
		
		$stmt = $DB_con->prepare("UPDATE login_attempts_user SET verify = :verify WHERE member_id = :memberid AND status = :status AND date_time > :datetime");
		$stmt->bindparam(":memberid",$yazUye["id"]);
		$stmt->bindparam(":verify",$status);
		$stmt->bindparam(":status",$fails);
		$stmt->bindparam(":datetime",$date_time2);
		$stmt->execute();
		
		echo "Yönlendir başarılı üyelik girişi.";
		header('Location: home');
		exit();
	}
	else if($yazUye["say"] != 1)
	{
		echo "Veritabanı bilgileriyle google bilgileri eşleşmiyor yani hatalı google girişi.";
		unset($_SESSION['token']);
		$gClient->revokeToken();
		header('Location: signin?error=1');
		exit();
	}
}
else
{
    $authUrl = $gClient->createAuthUrl();
    $signinlink = filter_var($authUrl, FILTER_SANITIZE_URL);
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="Sign in panel of <?=$companyInformations['companyName']?>">
		<meta name="author" content="<?=$companyInformations['companyName']?>">
		<title>Sign In - <?=$companyInformations['companyName']?></title>
		<link href="img/favicon.png" rel="icon" type="image/png">
		<link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

		<link href="plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="plugins/node-waves/waves.min.css" rel="stylesheet" />
		<link href="plugins/animate-css/animate.min.css" rel="stylesheet" />
		<link href="css/style.css" rel="stylesheet">
	</head>
	<body class="login-page">
		<div class="page-loader-wrapper">
			<div class="loader">
				<div class="preloader">
					<div class="spinner-layer pl-orange">
						<div class="circle-clipper left">
							<div class="circle"></div>
						</div>
						<div class="circle-clipper right">
							<div class="circle"></div>
						</div>
					</div>
				</div>
				<p>Please wait...</p>
			</div>
		</div>
		<div class="overlay"></div>
		<div class="login-box">
			<div class="logo text-center">
				<img src="img/sbmlogo.png" width="200">
			</div>
			<div class="card">
				<div class="body">
					<?php 
					$error = filter_input(INPUT_GET, 'error', FILTER_VALIDATE_INT);
					if($error == 1)
					{
					?>
					<div class="alert bg-red">Giriş yapmaya çalıştığınız Google hesabınız sistemimize tanımlanmadığından girişinizi gerçekleştiremedik. Lütfen sisteme tanımlı google hesabınızla giriş yapınız.</div>
					<?php
					}
					?>
					<div class="alert bg-orange"><?=$companyInformations['companyName']?> sistemine kayıtlı Google hesabınızla giriş yapabilirsiniz.</div>
					<div class="btn-wrapper text-center">
						<a href="<?=$signinlink?><?=$loginhint?>" class="btn btn-block g-sign-in-button" id="sbmLoginButton">
							<img src="img/google.svg" width="22"><strong class="p-l-5">Sign in with Google</strong>
						</a>
					</div>
				</div>
			</div>
		</div>
		<script src="plugins/jquery/jquery.min.js"></script>
		<script src="plugins/bootstrap/js/bootstrap.min.js"></script>
		<script src="plugins/node-waves/waves.min.js"></script>
		<script src="js/sign-in.js"></script>
        <?php if ($sbmAutoLogin == true) { ?>
        <script>
            document.getElementById("sbmLoginButton").click();
        </script>
        <?php } ?>
	</body>

</html>