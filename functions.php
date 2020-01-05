<?php
	/*
	if(!defined('VAL4')) {
	   die('Security');
	}
	
	define('VAL5', TRUE);
	*/
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	require 'PHPMailer/src/Exception.php';
	require 'PHPMailer/src/PHPMailer.php';
	require 'PHPMailer/src/SMTP.php';
	function SendMail($uMail,$uName,$Message,$Subject)
	{						
		$mail = new PHPMailer(true);
		$mail->isSMTP(); 
		$mail->SMTPDebug  = 0;                     
		$mail->SMTPAuth   = true;      	
		$mail->SMTPSecure = "ssl";                 
		$mail->Host       = "bree.guzelhosting.com";      
		$mail->Port       = 465;             
		$mail->addAddress($uMail, $uName);
		$mail->Username = "sbm@aybarsakgun.com";  
		$mail->Password = "student123bm.";            
		$mail->setFrom('sbm@aybarsakgun.com','Student Behavior Management');
		$mail->addReplyTo("sbm@aybarsakgun.com","Student Behavior Management");
		$mail->Subject = $Subject;
		$mail->CharSet = "UTF-8";
		$mail->AddEmbeddedImage('img/sbmlogo.png', 'logo');
		$mail->msgHTML($Message);
		if(!$mail->send())
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	function SessionStartUser() 
	{
		$session_name = 'sbmuser';
		$secure = false;
		$httponly = true;
		$cookieParams = session_get_cookie_params();
		session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
		session_name($session_name);
		session_start();
		session_regenerate_id();
	}
	function LoginCheckUser($DB_con) 
	{
		if(isset($_SESSION['user_id'], $_SESSION['user_mail'], $_SESSION['user_login_string'])) 
		{
			$user_id = $_SESSION['user_id'];
			$user_login_string = $_SESSION['user_login_string'];
			$user_mail = $_SESSION['user_mail'];
			$user_browser = $_SERVER['HTTP_USER_AGENT'];
			$user_ip = $_SERVER['REMOTE_ADDR'];
			if($stmt = $DB_con->prepare("SELECT google_id,email FROM users WHERE google_id = :userid LIMIT 1")) 
			{
				$stmt->bindparam(":userid",$user_id);
				$stmt->execute();
				$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
				if($stmt->rowCount() == 1) 
				{
					$login_check = hash('sha512', $userRow['google_id'].$userRow["email"].$user_browser.$user_ip);
					if(hash_equals($login_check, $user_login_string))
					{
						return $user_id;
					} 
					else 
					{
						return false;
					}
				} 
				else 
				{
					return false;
				}
			} 
			else
			{
				return false;
			}
		} 
		else 
		{
			return false;
		}
	}
	function isXmlHttpRequest()
	{
		$header = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : null;
		return ($header === 'XMLHttpRequest');
	}
	function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	function seo($url)
	{
		$url = trim($url);
		$find = array('<b>', '</b>');
		$url = str_replace ($find, '', $url);
		$url = preg_replace('/<(\/{0,1})img(.*?)(\/{0,1})\>/', 'image', $url);
		$find = array(' ', '&amp;amp;amp;quot;', '&amp;amp;amp;amp;', '&amp;amp;amp;', '\r\n', '\n', '/', '\\', '+', '<', '>');
		$url = str_replace ($find, '-', $url);
		$find = array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ë', 'Ê');
		$url = str_replace ($find, 'e', $url);
		$find = array('í', 'ý', 'ì', 'î', 'ï', 'I', 'Ý', 'Í', 'Ì', 'Î', 'Ï','İ','ı');
		$url = str_replace ($find, 'i', $url);
		$find = array('ó', 'ö', 'Ö', 'ò', 'ô', 'Ó', 'Ò', 'Ô');
		$url = str_replace ($find, 'o', $url);
		$find = array('á', 'ä', 'â', 'à', 'â', 'Ä', 'Â', 'Á', 'À', 'Â');
		$url = str_replace ($find, 'a', $url);
		$find = array('ú', 'ü', 'Ü', 'ù', 'û', 'Ú', 'Ù', 'Û');
		$url = str_replace ($find, 'u', $url);
		$find = array('ç', 'Ç');
		$url = str_replace ($find, 'c', $url);
		$find = array('þ', 'Þ','ş','Ş');
		$url = str_replace ($find, 's', $url);
		$find = array('ð', 'Ð','ğ','Ğ');
		$url = str_replace ($find, 'g', $url);
		$find = array('/[^A-Za-z0-9\-<>]/', '/[\-]+/', '/<&#91;^>]*>/');
		$repl = array('', '-', '');
		$url = preg_replace ($find, $repl, $url);
		$url = str_replace ('--', '-', $url);
		$url = strtolower($url);
		return $url;
	}
    function timeConvert ( $zaman ){
        $zaman =  strtotime($zaman);
        $zaman_farki = time() - $zaman;
        $saniye = $zaman_farki;
        $dakika = round($zaman_farki/60);
        $saat = round($zaman_farki/3600);
        $gun = round($zaman_farki/86400);
        $hafta = round($zaman_farki/604800);
        $ay = round($zaman_farki/2419200);
        $yil = round($zaman_farki/29030400);
        if( $saniye < 60 ){
            if ($saniye == 0){
                return "just now";
            } else {
                return $saniye .' seconds ago';
            }
        } else if ( $dakika < 60 ){
            return $dakika .' minutes ago';
        } else if ( $saat < 24 ){
            return $saat.' hours ago';
        } else if ( $gun < 7 ){
            return $gun .' days ago';
        } else if ( $hafta < 4 ){
            return $hafta.' weeks ago';
        } else if ( $ay < 12 ){
            return $ay .' months ago';
        } else {
            return $yil.' years ago';
        }
    }
?>