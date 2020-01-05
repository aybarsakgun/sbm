<?php
define('VAL3', TRUE);

$request_uri = $_SERVER['REQUEST_URI'];
$query_string = $_SERVER['QUERY_STRING'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

if (
	stripos($request_uri, 'eval(') || 
	stripos($request_uri, 'CONCAT') || 
	stripos($request_uri, 'UNION+SELECT') || 
	stripos($request_uri, '(null)') || 
	stripos($request_uri, 'base64_') || 
	stripos($request_uri, '/localhost') || 
	stripos($request_uri, '/pingserver') || 
	stripos($request_uri, '/config.') || 
	stripos($request_uri, '/wwwroot') || 
	stripos($request_uri, '/makefile') || 
	stripos($request_uri, 'crossdomain.') || 
	stripos($request_uri, 'proc/self/environ') || 
	stripos($request_uri, 'etc/passwd') || 
	stripos($request_uri, '/https/') || 
	stripos($request_uri, '/http/') || 
	stripos($request_uri, '/ftp/') || 
	stripos($request_uri, '/cgi/') || 
	stripos($request_uri, '.cgi') || 
	stripos($request_uri, '.exe') || 
	stripos($request_uri, '.sql') || 
	stripos($request_uri, '.ini') || 
	stripos($request_uri, '.dll') || 
	stripos($request_uri, '.asp') || 
	stripos($request_uri, '.jsp') || 
	stripos($request_uri, '/.bash') || 
	stripos($request_uri, '/.git') || 
	stripos($request_uri, '/.svn') || 
	stripos($request_uri, '/.tar') || 
	stripos($request_uri, ' ') || 
	stripos($request_uri, '<') || 
	stripos($request_uri, '>') || 
	stripos($request_uri, '/=') || 
	stripos($request_uri, '...') || 
	stripos($request_uri, '+++') || 
	stripos($request_uri, '://') || 
	stripos($request_uri, '/&&') || 
	stripos($query_string, '?') || 
	stripos($query_string, ':') || 
	stripos($query_string, '[') || 
	stripos($query_string, ']') || 
	stripos($query_string, '../') || 
	stripos($query_string, '127.0.0.1') || 
	stripos($query_string, 'loopback') || 
	stripos($query_string, '%0A') || 
	stripos($query_string, '%0D') || 
	stripos($query_string, '%22') || 
	stripos($query_string, '%27') || 
	stripos($query_string, '%3C') || 
	stripos($query_string, '%3E') || 
	stripos($query_string, '%00') || 
	stripos($query_string, '%2e%2e') || 
	stripos($query_string, 'union') || 
	stripos($query_string, 'input_file') || 
	stripos($query_string, 'execute') || 
	stripos($query_string, 'mosconfig') || 
	stripos($query_string, 'environ') || 
	stripos($query_string, 'path=.') || 
	stripos($query_string, 'mod=.') || 
	stripos($user_agent, 'binlar') || 
	stripos($user_agent, 'casper') || 
	stripos($user_agent, 'cmswor') || 
	stripos($user_agent, 'diavol') || 
	stripos($user_agent, 'dotbot') || 
	stripos($user_agent, 'finder') || 
	stripos($user_agent, 'flicky') || 
	stripos($user_agent, 'libwww') || 
	stripos($user_agent, 'nutch') || 
	stripos($user_agent, 'planet') || 
	stripos($user_agent, 'purebot') || 
	stripos($user_agent, 'pycurl') || 
	stripos($user_agent, 'skygrid') || 
	stripos($user_agent, 'sucker') || 
	stripos($user_agent, 'turnit') || 
	stripos($user_agent, 'vikspi') || 
	stripos($user_agent, 'zmeu')
) {
	@header('HTTP/1.1 403 Forbidden');
	@header('Status: 403 Forbidden');
	@header('Connection: Close');
	@exit;
}

require_once 'database.php';
require_once 'functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

class ADMIN
{	
	private $db;

	function __construct($DB_con)
	{
		$this->db = $DB_con;
	}

	public function LoginToAdmin($adminMailLogin,$adminPassLogin)
	{
		try
		{
			$stmt = $this->db->prepare("SELECT id,mail,password FROM admin WHERE mail = :Mail");
			$stmt->execute(array(":Mail"=>$adminMailLogin));
			$userRow=$stmt->fetch(PDO::FETCH_ASSOC);
			if($stmt->rowCount() == 1)
			{
				$getAdminID = $userRow['id'];
				if(CheckBrute($getAdminID, $this->db) == true) 
				{
					echo 3;
					exit();
				}
				else
				{
					$getAdminPass = $userRow['password'];
					if(password_verify($adminPassLogin, $getAdminPass)) 
					{
						$ip = $_SERVER['REMOTE_ADDR'];
						$browser = $_SERVER['HTTP_USER_AGENT'];
						$_SESSION['admin_id'] = $getAdminID;
						$getAdminMail = filter_var($userRow['mail'], FILTER_SANITIZE_EMAIL);
						$_SESSION['admin_mail'] = $getAdminMail;
						$_SESSION['admin_login_string'] = hash('sha512', $userRow['password'] . $browser.$ip);
						
						$status = 1;
								
						$date = date("d F Y H:i:s");
						
						$date_time = date('Y-m-d H:i:s');

						$stmt = $this->db->prepare("INSERT INTO login_attempts(member_id, date, ip, browser, date_time, status, verify) VALUES (:memberid, :date, :ip, :browser, :datetime, :status, :verify)");
						$stmt->bindparam(":memberid",$getAdminID);
						$stmt->bindparam(":date",$date);
						$stmt->bindparam(":ip",$ip);
						$stmt->bindparam(":browser",$browser);
						$stmt->bindparam(":datetime",$date_time);
						$stmt->bindparam(":status",$status);
						$stmt->bindparam(":verify",$status);
						$stmt->execute();
						
						$date_time2 = date("Y-m-d H:i:s", strtotime(" -5 minutes"));
						
						$fails = 0;
						
						$stmt = $this->db->prepare("UPDATE login_attempts SET verify = :verify WHERE member_id = :memberid AND status = :status AND date_time > :datetime");
						$stmt->bindparam(":memberid",$getAdminID);
						$stmt->bindparam(":verify",$status);
						$stmt->bindparam(":status",$fails);
						$stmt->bindparam(":datetime",$date_time2);
						$stmt->execute();
						
						echo 1;
						exit();
					}
					else
					{
						$status = 0;
						
						$ip = $_SERVER['REMOTE_ADDR'];
						$browser = $_SERVER['HTTP_USER_AGENT'];
						$date = date("d F Y H:i:s");

						$date_time = date('Y-m-d H:i:s');
						
						$stmt = $this->db->prepare("INSERT INTO login_attempts(member_id, date, ip, browser, date_time, status, verify) VALUES (:memberid, :date, :ip, :browser, :datetime, :status, :verify)");
						$stmt->bindparam(":memberid",$getAdminID);
						$stmt->bindparam(":date",$date);
						$stmt->bindparam(":ip",$ip);
						$stmt->bindparam(":browser",$browser);
						$stmt->bindparam(":datetime",$date_time);
						$stmt->bindparam(":status",$status);
						$stmt->bindparam(":verify",$status);
						$stmt->execute();
						echo 2;
						exit;
					}	
				}
			}
			else
			{
				echo 2;
				exit;
			}		
		}
		catch(PDOException $ex)
		{
			echo $ex->getMessage();
		}
	}
	public function LogOutAdmin()
	{
		$_SESSION = array();
		$params = session_get_cookie_params();
		setcookie(session_name(),
				'', time() - 42000, 
				$params["path"], 
				$params["domain"], 
				$params["secure"], 
				$params["httponly"]);
		session_destroy();
		echo 1;
		return true;
	}
	public function SendMail($uMail,$uName,$Message,$Subject)
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
		$mail->AddEmbeddedImage('../img/sbmlogo.png', 'logo');
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
}