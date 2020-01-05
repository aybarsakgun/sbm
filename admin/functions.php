<?php
	if(!defined('VAL4')) {
	   die('Security');
	}
	
	define('VAL5', TRUE);
	
	function SessionStartAdmin() 
	{
		$session_name = 'sbmadmin';
		$secure = SECURE;
		$httponly = true;
		$cookieParams = session_get_cookie_params();
		session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
		session_name($session_name);
		session_start();
		session_regenerate_id();
	}
	function CheckBrute($admin_id, $DB_con)
	{
		$date_time = date("Y-m-d H:i:s", strtotime(" -5 minutes"));
		if($stmt = $DB_con->prepare("SELECT date_time FROM login_attempts WHERE member_id = :memberid AND verify = :verify AND date_time > :datetime")) 
		{
			$stmt->execute(array(":memberid"=>$admin_id,":verify"=>0,":datetime"=>$date_time));
			if($stmt->rowCount() > 3) 
			{
				return true;
			} 
			else 
			{
				return false;
			}
		}
	}
	function LoginCheckAdmin($DB_con) 
	{
		if(isset($_SESSION['admin_id'], $_SESSION['admin_mail'], $_SESSION['admin_login_string'])) 
		{
			$admin_id = $_SESSION['admin_id'];
			$admin_login_string = $_SESSION['admin_login_string'];
			$admin_mail = $_SESSION['admin_mail'];
			$admin_browser = $_SERVER['HTTP_USER_AGENT'];
			$admin_ip = $_SERVER['REMOTE_ADDR'];
			if($stmt = $DB_con->prepare("SELECT password FROM admin WHERE id = :adminid LIMIT 1")) 
			{
				$stmt->bindparam(":adminid",$admin_id);
				$stmt->execute();
				$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
				if($stmt->rowCount() == 1) 
				{
					$login_check = hash('sha512', $userRow['password'] . $admin_browser.$admin_ip);
					if(hash_equals($login_check, $admin_login_string))
					{
						return $admin_id;
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
?>