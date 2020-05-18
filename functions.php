<?php
	if(!defined('AJAX') && !defined('VAL3')) {
	   die('Security');
	}
	
	define('VAL4', TRUE);
	require_once 'settings.php';
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
    function thumbOlustur1($filepath, $thumbpath, $thumbnail_width, $thumbnail_height) {
        list($original_width, $original_height, $original_type) = getimagesize($filepath);
        if ($original_width > $original_height) {
            $new_width = $thumbnail_width;
            $new_height = intval($original_height * $new_width / $original_width);
        } else {
            $new_height = $thumbnail_height;
            $new_width = intval($original_width * $new_height / $original_height);
        }
        $dest_x = intval(($thumbnail_width - $new_width) / 2);
        $dest_y = intval(($thumbnail_height - $new_height) / 2);
        if ($original_type === 2) {
            $exif = @exif_read_data($filepath);
            $imgt = "ImageJPEG";
            $imgcreatefrom = "ImageCreateFromJPEG";
        } else if ($original_type === 3) {
            $imgt = "ImagePNG";
            $imgcreatefrom = "ImageCreateFromPNG";
        } else {
            return false;
        }
        $old_image = $imgcreatefrom($filepath);
        $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
        $color = imagecolorallocate($new_image, 255,255,255);
        imagefill($new_image, 0, 0, $color);
        imagecopyresampled($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 3:
                    $new_image = imagerotate($new_image, 180, 0);
                    break;

                case 6:
                    $new_image = imagerotate($new_image, -90, 0);
                    break;

                case 8:
                    $new_image = imagerotate($new_image, 90, 0);
                    break;
            }
        }
        $imgt($new_image, $thumbpath);
        imagedestroy($new_image);
        imagedestroy($old_image);
        return true;
    }
    function printDate($DB_con, $date, $uyeokul) {
        $dateTypeQuery = $DB_con->prepare("SELECT date_type FROM schools WHERE id = :id");
        $dateTypeQuery->execute(array(":id"=>$uyeokul));
        $dateType = $dateTypeQuery->fetch(PDO::FETCH_ASSOC);
        if ($dateType['date_type'] == 1) {
            return date('d/m/Y h:i A', strtotime($date));
        } else if ($dateType['date_type'] == 2) {
            return date('m/d/Y h:i A', strtotime($date));
        } else if ($dateType['date_type'] == 3) {
            return date('y/m/d h:i A', strtotime($date));
        } else if ($dateType['date_type'] == 4) {
            return date('F d, Y h:i A', strtotime($date));
        } else if ($dateType['date_type'] == 5) {
            return date('d F, Y h:i A', strtotime($date));
        }
    }
    function setDateFormat($DB_con, $uyeokul, $type) {
        $dateTypeQuery = $DB_con->prepare("SELECT date_type FROM schools WHERE id = :id");
        $dateTypeQuery->execute(array(":id"=>$uyeokul));
        $dateType = $dateTypeQuery->fetch(PDO::FETCH_ASSOC);
        if ($dateType['date_type'] == 1) {
            return $type == 'blank' ? '__-__-____' : 'dd-mm-yyyy';
        } else if ($dateType['date_type'] == 2) {
            return $type == 'blank' ? '__-__-____' : 'mm-dd-yyyy';
        } else if ($dateType['date_type'] == 3) {
            return $type == 'blank' ? '____-__-__' : 'yyyy-mm-dd';
        } else if ($dateType['date_type'] == 4) {
            return $type == 'blank' ? '__-__-____' : 'mm-dd-yyyy';
        } else if ($dateType['date_type'] == 5) {
            return $type == 'blank' ? '__-__-____' : 'dd-mm-yyyy';
        }
    }
    function onlyAlphaNum($string) {
	    return preg_replace("/[^a-zA-Z0-9]+/", "", $string);
    }
    function searchArray($field, $id, $array) {
        foreach ($array as $key => $val) {
            if ($val[$field] === $id) {
                return $key;
            }
        }
        return null;
    }
?>