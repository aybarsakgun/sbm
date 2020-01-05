<?php
$base_request = "signup";
$user_role = $_GET["ur"];
require_once 'database.php'; // SIGN UP ERROR NULL ISE ŞART KOS Aşağıdakilere 
require_once 'functions.php';
SessionStartUser();
if(LoginCheckUser($DB_con) == true) 
{
	header('Location: home');
	exit();
}
if(!isset($_GET["r"]))
{
	$email = filter_input(INPUT_GET, 'email', FILTER_SANITIZE_STRING);
	$invite_token = filter_input(INPUT_GET, 'invite_token', FILTER_SANITIZE_STRING);
	if(isset($email) && !empty($email) && isset($invite_token) && !empty($invite_token))
	{
		if(strlen($invite_token) != 64)
		{
			echo "Yanlış token"; //Yönlendirme yanlış token
			header('Location: signin');
			exit();
		}
		$email_decode = base64_decode(strtr($email, '-_', '+/'));
		$sorgu = $DB_con->prepare("SELECT name,email,invite_token,register_date,google_id,role FROM users WHERE email = :email AND invite_token = :invite_token");
		$sorgu->execute(array(":email"=>$email_decode,":invite_token"=>$invite_token));
		if($sorgu->rowCount() != 1)
		{
			echo "Böyle bir üye yok"; //Yönlendirme yanlış token
			header('Location: signin');
			exit();
		}
		else if($sorgu->rowCount() == 1)
		{
			$yaz = $sorgu->fetch(PDO::FETCH_ASSOC);
			//if($yaz["google_id"] == "" && $yaz["register_date"] == NULL)
			if($yaz["register_date"] == NULL)
			{
				$ad = $yaz["name"];
				if($yaz["role"] == "admin") {
                    $_SESSION["hatalikayiturl"] = 'signup-'.rtrim(strtr(base64_encode($yaz["email"]), '+/', '-_'), '=').'-gc-'.$yaz["invite_token"].'?error=1';
                }
                else if($yaz["role"] == "teacher") {
                    $_SESSION["hatalikayiturl"] = 'signup-'.rtrim(strtr(base64_encode($yaz["email"]), '+/', '-_'), '=').'-gc-'.$yaz["invite_token"].'?error=1';
                }
                else if($yaz["role"] == "student") {
                    $_SESSION["hatalikayiturl"] = 'signup-'.rtrim(strtr(base64_encode($yaz["email"]), '+/', '-_'), '=').'-st-'.$yaz["invite_token"].'?error=1';
                }
			}
			//else if($yaz["google_id"] != "" && $yaz["register_date"] != NULL)
			else if($yaz["register_date"] != NULL)
			{
				header('Location: signin');
				exit();
			}
		}
	}
	else
	{
		header('Location: signin');
		exit();
	}
}
if(isset($_GET["r"]) && $_GET["r"] == "return" && !isset($_GET['code']))
{
	echo "yönlendirxd";
	header('Location: signin');
	exit();
}
else if(isset($_GET["r"]) && $_GET["r"] == "return" && isset($_GET['code']))
{
    $gClient->authenticate($_GET['code']);
    $_SESSION['token'] = $gClient->getAccessToken();
    //header('Location: ' . filter_var(GOOGLE_REDIRECT_URL, FILTER_SANITIZE_URL));
}
if(isset($_SESSION['token'])){
    $gClient->setAccessToken($_SESSION['token']);
}
if($gClient->getAccessToken())
{
	$gpUserProfile = $google_oauthV2->userinfo->get();
	$gpUserData = array();
	$gpUserData['google_id'] = !empty($gpUserProfile['id'])?$gpUserProfile['id']:'';
	$gpUserData['google_email'] = !empty($gpUserProfile['email'])?$gpUserProfile['email']:'';
	$gpUserData['google_picture'] = !empty($gpUserProfile['picture'])?$gpUserProfile['picture']:'';
	//$sorguUye = $DB_con->prepare("SELECT COUNT(id) AS say,name,email,invite_token FROM users WHERE google_id = :gid AND email = :email AND register_date IS NULL");
	$sorguUye = $DB_con->prepare("SELECT COUNT(id) AS say,id,name,email,invite_token,role FROM users WHERE email = :email AND register_date IS NULL");
	//$sorguUye->execute(array(":gid"=>"",":email"=>$gpUserData["google_email"]));
	$sorguUye->execute(array(":email"=>$gpUserData["google_email"]));
	$yazUye = $sorguUye->fetch(PDO::FETCH_ASSOC);
	if($yazUye["say"] == 1)
	{
		$atm = date('Y-m-d H:i:s');
		if($uyerol != "student") {
		    if($gpUserData['google_picture'] != '') {
                $emailparcalax = explode("@", $yazUye["email"]);
                $destinx = 'img/avatars/'.$emailparcalax[0].'-'.rand().'.jpg';
                copy($gpUserData['google_picture'],  $destinx);
            }
        }
		$uyeKayit = $DB_con->prepare("UPDATE users SET google_id = :googleid1 , register_date = :regdatenow , invite_token = :invt , avatar = IF(avatar = '', :avatarr, avatar) WHERE email = :email AND register_date IS NULL");
		$uyeKayit->execute(array(":googleid1"=>$gpUserData["google_id"],":regdatenow"=>$atm,":invt"=>"USED",":avatarr"=>$destinx,":email"=>$gpUserData["google_email"]));
		$ip = $_SERVER['REMOTE_ADDR'];
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$_SESSION['user_id'] = $gpUserData["google_id"];
		$getUserMail = filter_var($gpUserData["google_email"], FILTER_SANITIZE_EMAIL);
		$_SESSION['user_mail'] = $getUserMail;
		$_SESSION['user_login_string'] = hash('sha512', $gpUserData["google_id"].$gpUserData["google_email"].$browser.$ip);
		
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
		
		echo "Yönlendir başarılı üyelik kayıdı.";
		header('Location: home');
		exit();
	}
	else if($yazUye["say"] != 1)
	{
		echo "Veritabanı bilgileriyle google bilgileri eşleşmiyor yani hatalı google hesabı bağlama girişimi";
		unset($_SESSION['token']);
		$hatalikayiturl = $_SESSION["hatalikayiturl"];
		unset($_SESSION["hatalikayiturl"]);
		$gClient->revokeToken();
		header('Location: '.$hatalikayiturl);
		exit();
	}
}
else
{
    $authUrl = $gClient->createAuthUrl();
    $signuplink = filter_var($authUrl, FILTER_SANITIZE_URL);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="Sign up panel of Student Behavior Management">
        <meta name="author" content="Student Behavior Management">
        <title>Sign Up - Student Behavior Management</title>
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
                        <div class="alert bg-red">Kaydolmaya çalıştığınız Google hesabınız sistemimize tanımlanmadığından kaydınızı gerçekleştiremedik. Lütfen sisteme tanımlı google hesabınızla kayıt olunuz.</div>
                        <?php
                    }
                    ?>
                    <div class="alert bg-orange">Student Behavior Management sistemine kayıtlı Google hesabınızla giriş yapabilirsiniz.</div>
                    <div class="btn-wrapper text-center">
                        <a href="<?=$signuplink.'&login_hint='.$email_decode?>" class="btn btn-block g-sign-in-button">
                            <img src="img/google.svg" width="22"><strong class="p-l-5">Sign up with Google</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <script src="plugins/jquery/jquery.min.js"></script>
        <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
        <script src="plugins/node-waves/waves.min.js"></script>
        <script src="js/sign-in.js"></script>
	</body>
</html>