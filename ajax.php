<?php
require_once 'database.php';
require_once 'functions.php';
SessionStartUser();

if(!isXmlHttpRequest())
{
	exit("Security");
}

if(empty($_SESSION['sbmt']))
{
	exit("Security");
}

$headers = apache_request_headers();
if(isset($headers['sbmtoken']))
{
	if (!hash_equals($_SESSION['sbmt'], $headers['sbmtoken'])) {
		exit("Security");
	}
} else {
	exit("Security");
}

if(LoginCheckUser($DB_con) == false) 
{
	exit();
}

$page_request = filter_input(INPUT_GET, 'pr', FILTER_SANITIZE_STRING);

if(isset($page_request))
{
	$sorguUyeBilgi = $DB_con->prepare("SELECT name,id,role,schools,email FROM users WHERE google_id = :gid");
	$sorguUyeBilgi->execute(array(":gid"=>LoginCheckUser($DB_con)));
	$yazUyeBilgi = $sorguUyeBilgi->fetch(PDO::FETCH_ASSOC);
	$uyevtid = $yazUyeBilgi["id"];
	$uyemail = $yazUyeBilgi["email"];
	$uyerol = $yazUyeBilgi["role"];
	$uyeokul = $yazUyeBilgi["schools"];
	$uyead = $yazUyeBilgi["name"];
	$sorguokulad = $DB_con->prepare("SELECT name FROM schools WHERE id = :okulid");
	$sorguokulad->execute(array(":okulid"=>$uyeokul));
	$yazokulad = $sorguokulad->fetch(PDO::FETCH_ASSOC);
	if($page_request == "logon-records")
	{
		$sorguSay = $DB_con->prepare("SELECT COUNT(member_id) AS say FROM login_attempts_user WHERE member_id = :uyeid");
		$sorguSay->execute(array(":uyeid"=>$uyevtid));
		$yazSay = $sorguSay->fetch(PDO::FETCH_ASSOC);
		if($sorguSay->rowCount() > 0)
		{
			$prefix = "";
			$siralama_durum = isset($_GET['siralama']) ? (int) $_GET['siralama'] : 0;
			if($siralama_durum == 0)
			{
				$siralama_yazisi = "ORDER BY date_time DESC";
			}
			else if($siralama_durum == 1)
			{
				$siralama_yazisi = "ORDER BY date_time ASC";
			}
			else
			{
				$siralama_yazisi = "";
			}
			
			$sorguyazisi = "SELECT COUNT(member_id) AS say FROM login_attempts_user WHERE member_id = :uyeid";
			
			$sorgu = $DB_con->prepare($sorguyazisi);
			$sorgu->execute(array(":uyeid"=>$uyevtid));
			$sayimsonucu = $sorgu->fetch(PDO::FETCH_ASSOC);
			
			$sayfada = 10;
			$toplam_icerik = $sayimsonucu["say"];
			$toplam_sayfa = ceil($toplam_icerik / $sayfada);
			$sayfa = isset($_GET['sayfa']) ? (int) $_GET['sayfa'] : 1;
			if($sayfa < 1) $sayfa = 1; 
			if($sayfa > $toplam_sayfa) $sayfa = $toplam_sayfa; 
			$limit = ($sayfa - 1) * $sayfada;

			$sorguyazisi = "SELECT ip,date,browser,status,platform FROM login_attempts_user WHERE member_id = :uyeid $siralama_yazisi LIMIT :limit , :sayfada";

			$sorgu = $DB_con->prepare($sorguyazisi);
			$sorgu->execute(array(":uyeid"=>$uyevtid,":limit"=>abs($limit),":sayfada"=>$sayfada));

			?>
			<div class="table-responsive">
				<table class="table align-items-center table-flush">
					<thead class="thead-light">
						<tr>
							<th scope="col">IP Address</th>
							<th scope="col">Browser Info</th>
							<th scope="col">Date</th>
							<th scope="col">Logon Platform</th>
							<th scope="col">Status</th>
						</tr>
					</thead>
					<tbody>
				<?php
				if($sayimsonucu["say"] > 0)
				{
				while($yaz = $sorgu->fetch(PDO::FETCH_ASSOC))
				{	
					?>
						<tr>
							<th scope="row">
								<span class="mb-0 text-sm"><?=$yaz["ip"]?></span>
							</th>
							<td><?=$yaz["browser"]?></td>
							<td><?=printDate($DB_con, $yaz["date"], $uyeokul)?></td>
							<td><?php if($yaz["platform"] == "google") { echo '<a href="javascript:;" class="btn btn-neutral btn-icon"><span class="btn-inner--icon"><img src="img/google.svg"></span><span class="btn-inner--text">Google</span></a>'; } ?></td>
							<td><?php if($yaz["status"] == 0) { echo '<a href="javascript:;" class="btn btn-icon btn-2 btn-danger btn-sm"><span class="btn-inner--icon"><i class="fa fa-times"></i></span></a>'; } else if($yaz["status"] == 1) { echo '<a href="javascript:;" class="btn btn-icon btn-2 btn-success btn-sm"><span class="btn-inner--icon"><i class="fa fa-check"></i></span></a>'; } ?></td>
						</tr>
					<?php
				}
				?>
					</tbody>
					<thead class="thead-light">
						<tr>
							<th scope="col" colspan="5">Toplam <?=$sayimsonucu["say"]?> tane bulunan sonuçtan <?=$sorgu->rowCount()?> tanesini görüntülüyorsunuz.</th>
						</tr>
					</thead>
				<?php
				}
				else
				{
				?>
					</tbody>
					<thead class="thead-light">
						<tr>
							<th scope="col" colspan="5">No results were found for your filtering.</th>
						</tr>
					</thead>
				<?php
				}
				?>
				</table>
			</div>
			<?php
			if($sayimsonucu["say"] > 0)
			{
			?>
			<div class="card-footer py-4">
				<nav aria-label="...">
					<ul class="pagination pagination-sm mb-0">
						<?php
						$sayfa_goster = 5;
						 
						$en_az_orta = ceil($sayfa_goster/2);
						$en_fazla_orta = ($toplam_sayfa+1) - $en_az_orta;
						 
						$sayfa_orta = $sayfa;
						if($sayfa_orta < $en_az_orta) $sayfa_orta = $en_az_orta;
						if($sayfa_orta > $en_fazla_orta) $sayfa_orta = $en_fazla_orta;
						 
						$sol_sayfalar = round($sayfa_orta - (($sayfa_goster-1) / 2));
						$sag_sayfalar = round((($sayfa_goster-1) / 2) + $sayfa_orta); 
						 
						if($sol_sayfalar < 1) $sol_sayfalar = 1;
						if($sag_sayfalar > $toplam_sayfa) $sag_sayfalar = $toplam_sayfa;

						if($sayfa != 1) echo '<li class="page-item"><a class="page-link sayfala-buton2" href="javascript:void(0);" id="1"><i class="fa fa-angle-double-left"></i></a></li>';
						else if($sayfa == 1) echo '<li class="page-item disabled"><a class="page-link" href="javascript:void(0);"><i class="fa fa-angle-double-left"></i></a></li>';
						if($sayfa != 1) echo '<li class="page-item"><a class="page-link sayfala-buton2" href="javascript:void(0);" id="'.($sayfa-1).'"><i class="fa fa-angle-left"></i></a></li>';
						else if($sayfa == 1) echo '<li class="page-item disabled"><a class="page-link" href="javascript:void(0);"><i class="fa fa-angle-left"></i></a></li>';
						
						for($s = $sol_sayfalar; $s <= $sag_sayfalar; $s++) {
							if($sayfa == $s) {
								echo '<li class="page-item active"><a class="page-link" href="javascript:void(0);">'.$s.'</a></li>';
							} else {
								echo '<li class="page-item"><a class="page-link sayfala-buton2" href="javascript:void(0);" id="'.$s.'">'.$s.'</a></li>';
							}
						}
						 
						if($sayfa != $toplam_sayfa) echo '<li class="page-item"><a class="page-link sayfala-buton2" href="javascript:void(0);" id="'.($sayfa+1).'"><i class="fa fa-angle-right"></i></a></li>';
						else if($sayfa == $toplam_sayfa) echo '<li class="page-item disabled"><a class="page-link" href="javascript:void(0);"><i class="fa fa-angle-right"></i></a></li>';
						if($sayfa != $toplam_sayfa) echo '<li class="page-item"><a class="page-link sayfala-buton2" href="javascript:void(0);" id="'.$toplam_sayfa.'"><i class="fa fa-angle-double-right"></i></a></li>';
						else if($sayfa == $toplam_sayfa) echo '<li class="page-item disabled"><a class="page-link" href="javascript:void(0);"><i class="fa fa-angle-double-right"></i></a></li>';
						?>
					</ul>
				</nav>
			</div>
			<?php
			}
		}
		else
		{
			?>
			<div class='alert alert-danger mb-0'>No records found yet.</div>
			<?php
		}
	}
	else if($page_request == "create-class")
	{
        if($uyerol == "student")
        {
            echo 0;
            exit();
        }
        if($uyerol == "teacher") {
            $sinifogrt = $uyevtid;
        }
        else if($uyerol == "admin")
        {
            if(isset($_POST["teachers"]))
            {
                $gelenogretmenler = $_POST["teachers"];
            }
            else
            {
                echo 4;
                exit();
            }
            $ogretmenler = "";
            $prefix = "";
            foreach($gelenogretmenler as $val)
            {
                $ogretmenler.= $prefix.$val;
                $prefix = ",";
            }
            $sinifogrt = $ogretmenler;
        }
        else
        {
            echo 0;
            exit();
        }
		$class_name = filter_input(INPUT_POST, 'class_name', FILTER_SANITIZE_STRING);
		if(empty($class_name))
		{
			echo 2;
			exit();
		}
		if(strlen($class_name) < 3 || strlen($class_name) > 64)
		{
			echo 3;
			exit();
		}
        $class_color = filter_input(INPUT_POST, 'class_color', FILTER_SANITIZE_STRING);
        if(empty($class_color) || $class_color === 0)
        {
            echo 2;
            exit();
        }
        $renkler = array("red","pink","purple","deep-purple","indigo","blue","light-blue","cyan","teal","green","light-green","lime","yellow","amber","orange","deep-orange","brown","grey","blue-grey","black");
        if(!in_array($class_color, $renkler)) {
            echo 0;
            exit();
        }
		$create_code = generateRandomString();
		$sorgu = $DB_con->prepare("INSERT INTO classes(name,school,teachers,owner,code,color) VALUES (:class_name, :school, :teacher, :owner, :code, :color)");
		if($sorgu->execute(array(":class_name"=>$class_name,":school"=>$uyeokul,":teacher"=>$sinifogrt,":owner"=>$uyevtid,":code"=>$create_code,":color"=>$class_color)))
		{
		    echo 1;
		    exit();
		}
		else
		{
			echo 0;
			exit();
		}
	}
    else if($page_request == "add-student")
    {
        if($uyerol == "student")
        {
            echo 0;
            exit();
        }
        $student_name = filter_input(INPUT_POST, 'student_name', FILTER_SANITIZE_STRING);
        if(empty($student_name))
        {
            echo 2;
            exit();
        }
        if(strlen($student_name) < 3 || strlen($student_name) > 64)
        {
            echo 3;
            exit();
        }
        $student_email = filter_input(INPUT_POST, 'student_email', FILTER_SANITIZE_EMAIL);
        if(empty($student_email))
        {
            echo 2;
            exit();
        }
        if(!filter_var($student_email, FILTER_VALIDATE_EMAIL))
        {
            echo 4;
            exit();
        }
        $parentname = filter_input(INPUT_POST, 'parentname', FILTER_SANITIZE_STRING);
        if(!empty($parentname)) {
            if (strlen($parentname) < 3 || strlen($parentname) > 64) {
                echo 8;
                exit();
            }
        }
        $parentemail = filter_input(INPUT_POST, 'parentemail', FILTER_SANITIZE_EMAIL);
        if(!empty($parentemail)) {
            if(!filter_var($parentemail, FILTER_VALIDATE_EMAIL))  {
                echo 9;
                exit();
            }
        }
        $parentemail2 = filter_input(INPUT_POST, 'parentemail2', FILTER_SANITIZE_EMAIL);
        if(!empty($parentemail2)) {
            if(!filter_var($parentemail2, FILTER_VALIDATE_EMAIL))  {
                echo 10;
                exit();
            }
        }
        $parentphone = filter_input(INPUT_POST, 'parentphone', FILTER_SANITIZE_STRING);
        $parentphone2 = filter_input(INPUT_POST, 'parentphone2', FILTER_SANITIZE_STRING);
        $homeroom = filter_input(INPUT_POST, 'homeroom', FILTER_SANITIZE_STRING);
        if (!empty($homeroom)) {
            if (strlen($homeroom) < 3 || strlen($homeroom) > 64) {
                echo 11;
                exit();
            }
        }
        $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
        if (!empty($gender)) {
            if (strlen($gender) > 32) {
                echo 12;
                exit();
            }
        }
        $stateID = filter_input(INPUT_POST, 'stateID', FILTER_VALIDATE_INT);
        if(!empty($stateID)) {
            if ($stateID === false) {
                echo 0;
                exit();
            }
        }
        $grade = filter_input(INPUT_POST, 'grade', FILTER_VALIDATE_INT);
        if(!empty($grade)) {
            if ($grade === false) {
                echo 0;
                exit();
            }
        }
        if(isset($_POST["classes"])) {
            $gelensiniflar = $_POST["classes"];
        }
        else
        {
            echo 5;
            exit();
        }
        $siniflar = "";
        $prefix = "";
        foreach($gelensiniflar as $val)
        {
            $siniflar.= $prefix.$val;
            $prefix = ",";
        }
        $sorguogrenci = $DB_con->prepare("SELECT id,role FROM users WHERE email = :email");
        $sorguogrenci->execute(array(":email"=>$student_email));
        if($sorguogrenci->rowCount() == 0)
        {
            $renkler = array("F44336","E91E63","9C27B0","673AB7","3F51B5","2196F3","03A9F4","00BCD4","009688","4CAF50","8BC34A","CDDC39","ffe821","FFC107","FF9800","FF5722","795548");
            shuffle($renkler);
            $adparcala = explode(" ", $student_name);
            $emailparcala = explode("@", $student_email);
            $destin = 'img/avatars/'.$emailparcala[0].'-'.$renkler[0].'.jpg';
            copy('https://ui-avatars.com/api/?name='.reset($adparcala).'+'.end($adparcala).'&background='.$renkler[0].'&color=fff&bold=true&size=100',  $destin);
            $now = date('Y-m-d H:i:s');
            $invite_tokenxd = bin2hex(random_bytes(32));
            $ekleogrenci = $DB_con->prepare("INSERT INTO users(name,email,classes,schools,role,invite_token,invite_date,avatar,register_type,parent_name,parent_email,parent_email2,parent_phone,parent_phone2,homeroom,gender,stateID,grade) VALUES (:name,:email,:classes,:schools,:role,:invitetoken,:invitedate,:avatar,:registertype,:parentname,:parentemail,:parentemail2,:parentphone,:parentphone2,:homeroom,:gender,:stateID,:grade)");
            if($ekleogrenci->execute(array(":name"=>$student_name,":email"=>$student_email,":classes"=>$siniflar,":schools"=>$uyeokul,":role"=>"student",":invitetoken"=>$invite_tokenxd,":invitedate"=>$now,":avatar"=>$destin,":registertype"=>2,":parentname"=>$parentname,":parentemail"=>$parentemail,":parentemail2"=>$parentemail2,":parentphone"=>$parentphone,":parentphone2"=>$parentphone2,":homeroom"=>$homeroom,":gender"=>$gender,":stateID"=>$stateID,":grade"=>$grade)))
            {
                $mail_encoded = rtrim(strtr(base64_encode($student_email), '+/', '-_'), '=');
                $message = '
                <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
                <html>
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
                <title>You have been invited as a student - Student Behavior Management</title>
                <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
                <style type="text/css">
                html { -webkit-text-size-adjust: none; -ms-text-size-adjust: none;}

                    @media only screen and (min-device-width: 750px) {
                        .table750 {width: 750px !important;}
                    }
                    @media only screen and (max-device-width: 750px), only screen and (max-width: 750px){
                      table[class="table750"] {width: 100% !important;}
                      .mob_b {width: 93% !important; max-width: 93% !important; min-width: 93% !important;}
                      .mob_b1 {width: 100% !important; max-width: 100% !important; min-width: 100% !important;}
                      .mob_left {text-align: left !important;}
                      .mob_soc {width: 50% !important; max-width: 50% !important; min-width: 50% !important;}
                      .mob_menu {width: 50% !important; max-width: 50% !important; min-width: 50% !important; box-shadow: inset -1px -1px 0 0 rgba(255, 255, 255, 0.2); }
                      .mob_center {text-align: center !important;}
                      .top_pad {height: 15px !important; max-height: 15px !important; min-height: 15px !important;}
                      .mob_pad {width: 15px !important; max-width: 15px !important; min-width: 15px !important;}
                      .mob_div {display: block !important;}
                    }
                   @media only screen and (max-device-width: 550px), only screen and (max-width: 550px){
                      .mod_div {display: block !important;}
                   }
                    .table750 {width: 750px;}
                </style>
                </head>
                <body style="margin: 0; padding: 0;">

                <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f3f3f3; min-width: 350px; font-size: 1px; line-height: normal;">
                    <tr>
                    <td align="center" valign="top">   			
                        <!--[if (gte mso 9)|(IE)]>
                         <table border="0" cellspacing="0" cellpadding="0">
                         <tr><td align="center" valign="top" width="750"><![endif]-->
                        <table cellpadding="0" cellspacing="0" border="0" width="750" class="table750" style="width: 100%; max-width: 750px; min-width: 350px; background: #f3f3f3;">
                            <tr>
                               <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
                                <td align="center" valign="top" style="background: #ffffff;">

                                  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
                                     <tr>
                                        <td align="right" valign="top">
                                           <div class="top_pad" style="height: 25px; line-height: 25px; font-size: 23px;">&nbsp;</div>
                                        </td>
                                     </tr>
                                  </table>

                                  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
                                     <tr>
                                        <td align="left" valign="top">
                                           <div style="height: 39px; line-height: 39px; font-size: 37px;">&nbsp;</div>
                                           <a href="#" target="_blank" style="display: block; max-width: 128px;">
                                              <img src="cid:logo" alt="img" width="160" border="0" style="display: block; width: 160px;" />
                                           </a>
                                           <div style="height: 73px; line-height: 73px; font-size: 71px;">&nbsp;</div>
                                        </td>
                                     </tr>
                                  </table>

                                  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
                                     <tr>
                                        <td align="left" valign="top">
                                           <font face="Source Sans Pro, sans-serif" color="#1a1a1a" style="font-size: 52px; line-height: 60px; font-weight: 300; letter-spacing: -1.5px;">
                                              <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 52px; line-height: 60px; font-weight: 300; letter-spacing: -1.5px;">Merhaba, '.$student_name.'</span>
                                           </font>
                                           <div style="height: 33px; line-height: 33px; font-size: 31px;">&nbsp;</div>
                                           <font face="Source Sans Pro, sans-serif" color="#585858" style="font-size: 24px; line-height: 32px;">
                                              <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #585858; font-size: 24px; line-height: 32px;">'.$yazokulad["name"].' adlı okula öğrenci olarak davet edildiniz. Aşağıdaki linke tıklayarak sisteme kayıt olabilirsiniz!</span>
                                           </font>
                                           <div style="height: 33px; line-height: 33px; font-size: 31px;">&nbsp;</div>
                                           <table class="mob_btn" cellpadding="0" cellspacing="0" border="0" style="background: #27cbcc; border-radius: 4px;">
                                              <tr>
                                                 <td align="center" valign="top"> 
                                                    <a href="http://localhost/sbm/signup-'.$mail_encoded.'-st-'.$invite_tokenxd.'" target="_blank" style="display: block; border: 1px solid #27cbcc; border-radius: 4px; padding: 12px 23px; font-family: Source Sans Pro, Arial, Verdana, Tahoma, Geneva, sans-serif; color: #ffffff; font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">
                                                       <font face="Source Sans Pro, sans-serif" color="#ffffff" style="font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">
                                                          <span style="font-family: Source Sans Pro, Arial, Verdana, Tahoma, Geneva, sans-serif; color: #ffffff; font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">Tıkla!</span>
                                                       </font>
                                                    </a>
                                                 </td>
                                              </tr>
                                           </table>
                                           <div style="height: 75px; line-height: 75px; font-size: 73px;">&nbsp;</div>
                                        </td>
                                     </tr>
                                  </table>

                                  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
                                     <tr>
                                        <td align="center" valign="top">
                                           <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
                                              <tr>
                                                 <td align="center" valign="top">
                                                    <div style="height: 34px; line-height: 34px; font-size: 32px;">&nbsp;</div>
                                                    <font face="Source Sans Pro, sans-serif" color="#868686" style="font-size: 17px; line-height: 20px;">
                                                       <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #868686; font-size: 17px; line-height: 20px;">Copyright &copy; 2019 Student Behavior Management.</span>
                                                    </font>
                                                    <div style="height: 3px; line-height: 3px; font-size: 1px;">&nbsp;</div>
                                                    <font face="Source Sans Pro, sans-serif" color="#1a1a1a" style="font-size: 17px; line-height: 20px;">
                                                       <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px;"><a href="mailto:sbm@aybarsakgun.com" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">sbm@aybarsakgun.com</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="tel:5555555555" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">+90 555 555 55 55</a></span>
                                                    </font>
                                                    <div style="height: 35px; line-height: 35px; font-size: 33px;">&nbsp;</div>
                                                 </td>
                                              </tr>
                                           </table>
                                        </td>
                                     </tr>
                                  </table>  

                               </td>
                               <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
                            </tr>
                         </table>
                         <!--[if (gte mso 9)|(IE)]>
                         </td></tr>
                         </table><![endif]-->
                      </td>
                   </tr>
                </table>
                </body>
                </html>
                ';
                $subject = "You've been invited as a student - Student Behavior Management";
                SendMail($student_email,$student_name,$message,$subject);
                echo 1;
                exit();
            }
            else
            {
                echo 0;
                exit();
            }
        }
        else if($sorguogrenci->rowCount() > 0)
        {
            $yazogrenci = $sorguogrenci->fetch(PDO::FETCH_ASSOC);
            if($yazogrenci["role"] == "student") {
                $simdi = date('Y-m-d H:i:s');
                $parcalasiniflar = explode(",", $siniflar);
                foreach ($parcalasiniflar as $parcalasinif) {
                    $ogrenciduzenle = $DB_con->prepare("UPDATE users SET classes = if(find_in_set(:siniflarxd,classes),classes, CONCAT(classes, ',', :siniflarxd2)) , update_date = :updatedate WHERE email = :email AND role = :role");
                    $ogrenciduzenle->execute(array(":siniflarxd" => $parcalasinif, ":siniflarxd2" => $parcalasinif, ":updatedate" => $simdi, ":email" => $student_email, ":role" => "student"));
                }
                echo 7;
                exit();
            }
            else
            {
                echo 6;
                exit();
            }
        }
    }
    else if($page_request == "import-student")
    {
        if($uyerol == "student")
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        if(!isset($_FILES['import_file']))
        {
            echo json_encode(array("sonuc"=>2));
            exit();
        }
        if($_FILES['import_file']['error'] != 0)
        {
            echo json_encode(array("sonuc" => 3));
            exit();
        }
        $name = $_FILES['import_file']['name'];
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $type = $_FILES['import_file']['type'];
        $tmpName = $_FILES['import_file']['tmp_name'];
        if($ext != 'csv')
        {
            echo json_encode(array("sonuc" => 4));
            exit();
        }
        if(isset($_POST["classes"])) {
            $gelensiniflar = $_POST["classes"];
        }
        else
        {
            echo json_encode(array("sonuc"=>5));
            exit();
        }
        if (!isset($_POST['name'])) {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        if (count($_POST['name']) > 2) {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        if (!isset($_POST['email'])) {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        if ($_POST['email'] == '') {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $siniflar = "";
        $prefix = "";
        foreach($gelensiniflar as $val)
        {
            $siniflar.= $prefix.$val;
            $prefix = ",";
        }
        $csv = array_map('str_getcsv', file($tmpName));
        $row = 0;
        $addedStudent = 0;
        $editedStudent = 0;
        foreach($csv as $rcsv)
        {
            if($row == 0){ $row++; continue; }
            $vals = explode(";", $rcsv[0]);
            if($vals[$_POST['email']] == '') { continue; }
            $fullName = count($_POST['name']) == 1 ? onlyAlphaNum($vals[$_POST['name'][0]]) : onlyAlphaNum($vals[$_POST['name'][0]])." ".onlyAlphaNum($vals[$_POST['name'][1]]);
            $eMail = $vals[$_POST['email']];
            $parentEmail = isset($_POST['parent_email']) && $_POST['parent_email'] == '' ? '' : $vals[$_POST['parent_email']];
            $parentEmail2 =  isset($_POST['parent_email2']) && $_POST['parent_email2'] == '' ? '' : $vals[$_POST['parent_email2']];
            $parentName = isset($_POST['parent_name']) ? (count($_POST['parent_name']) == 1 ? onlyAlphaNum($vals[$_POST['parent_name'][0]]) : onlyAlphaNum($vals[$_POST['parent_name'][0]])." ".onlyAlphaNum($vals[$_POST['parent_name'][1]])) : '';
            $parentPhone = isset($_POST['parent_phone']) && $_POST['parent_phone'] == '' ? '' : $vals[$_POST['parent_phone']];
            $parentPhone2 = isset($_POST['parent_phone2']) && $_POST['parent_phone2'] == '' ? '' : $vals[$_POST['parent_phone2']];
            $homeroom = isset($_POST['homeroom']) && $_POST['homeroom'] == '' ? '' : onlyAlphaNum($vals[$_POST['homeroom']]);
            $gender = isset($_POST['gender']) && $_POST['gender'] == '' ? '' : onlyAlphaNum($vals[$_POST['gender']]);
            $stateID = isset($_POST['stateID']) && $_POST['stateID'] == '' ? '' : onlyAlphaNum($vals[$_POST['stateID']]);
            $grade = isset($_POST['grade']) && $_POST['grade'] == '' ? '' : onlyAlphaNum($vals[$_POST['grade']]);
            $checkMail = $DB_con->prepare("SELECT id,role FROM users WHERE email = :email");
            $checkMail->execute(array(":email"=>$eMail));
            if($checkMail->rowCount() == 0)
            {
                $renkler = array("F44336","E91E63","9C27B0","673AB7","3F51B5","2196F3","03A9F4","00BCD4","009688","4CAF50","8BC34A","CDDC39","ffe821","FFC107","FF9800","FF5722","795548");
                shuffle($renkler);
                $emailparcala = explode("@", $eMail);
                $destin = 'img/avatars/'.$emailparcala[0].'-'.$renkler[0].'.jpg';
                copy('https://ui-avatars.com/api/?name='.onlyAlphaNum($vals[$_POST['name'][0]]).'+'.onlyAlphaNum($vals[$_POST['name'][1]]).'&background='.$renkler[0].'&color=fff&bold=true&size=100',  $destin);
                $now = date('Y-m-d H:i:s');
                $invite_tokenxd = bin2hex(random_bytes(32));
                $ekleogrenci = $DB_con->prepare("INSERT INTO users(name,email,classes,schools,role,invite_token,invite_date,avatar,register_type,parent_name,parent_email,parent_email2,parent_phone,parent_phone2,homeroom,gender,stateID,grade) VALUES (:name,:email,:classes,:schools,:role,:invitetoken,:invitedate,:avatar,:registertype,:parentname,:parentemail,:parentemail2,:parentphone,:parentphone2,:homeroom,:gender,:stateID,:grade)");
                $ekleogrenci->execute(array(":name"=>$fullName,":email"=>$eMail,":classes"=>$siniflar,":schools"=>$uyeokul,":role"=>"student",":invitetoken"=>$invite_tokenxd,":invitedate"=>$now,":avatar"=>$destin,":registertype"=>2,":parentname"=>$parentName,":parentemail"=>$parentEmail,":parentemail2"=>$parentEmail2,":parentphone"=>$parentPhone,":parentphone2"=>$parentPhone2,":homeroom"=>$homeroom,":gender"=>$gender,":stateID"=>$stateID,":grade"=>$grade));

                $mail_encoded = rtrim(strtr(base64_encode($eMail), '+/', '-_'), '=');
                $message = '
                <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
                <html>
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
                <title>You have been invited as a student - Student Behavior Management</title>
                <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
                <style type="text/css">
                html { -webkit-text-size-adjust: none; -ms-text-size-adjust: none;}

                    @media only screen and (min-device-width: 750px) {
                        .table750 {width: 750px !important;}
                    }
                    @media only screen and (max-device-width: 750px), only screen and (max-width: 750px){
                      table[class="table750"] {width: 100% !important;}
                      .mob_b {width: 93% !important; max-width: 93% !important; min-width: 93% !important;}
                      .mob_b1 {width: 100% !important; max-width: 100% !important; min-width: 100% !important;}
                      .mob_left {text-align: left !important;}
                      .mob_soc {width: 50% !important; max-width: 50% !important; min-width: 50% !important;}
                      .mob_menu {width: 50% !important; max-width: 50% !important; min-width: 50% !important; box-shadow: inset -1px -1px 0 0 rgba(255, 255, 255, 0.2); }
                      .mob_center {text-align: center !important;}
                      .top_pad {height: 15px !important; max-height: 15px !important; min-height: 15px !important;}
                      .mob_pad {width: 15px !important; max-width: 15px !important; min-width: 15px !important;}
                      .mob_div {display: block !important;}
                    }
                   @media only screen and (max-device-width: 550px), only screen and (max-width: 550px){
                      .mod_div {display: block !important;}
                   }
                    .table750 {width: 750px;}
                </style>
                </head>
                <body style="margin: 0; padding: 0;">

                <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f3f3f3; min-width: 350px; font-size: 1px; line-height: normal;">
                    <tr>
                    <td align="center" valign="top">   			
                        <!--[if (gte mso 9)|(IE)]>
                         <table border="0" cellspacing="0" cellpadding="0">
                         <tr><td align="center" valign="top" width="750"><![endif]-->
                        <table cellpadding="0" cellspacing="0" border="0" width="750" class="table750" style="width: 100%; max-width: 750px; min-width: 350px; background: #f3f3f3;">
                            <tr>
                               <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
                                <td align="center" valign="top" style="background: #ffffff;">

                                  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
                                     <tr>
                                        <td align="right" valign="top">
                                           <div class="top_pad" style="height: 25px; line-height: 25px; font-size: 23px;">&nbsp;</div>
                                        </td>
                                     </tr>
                                  </table>

                                  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
                                     <tr>
                                        <td align="left" valign="top">
                                           <div style="height: 39px; line-height: 39px; font-size: 37px;">&nbsp;</div>
                                           <a href="#" target="_blank" style="display: block; max-width: 128px;">
                                              <img src="cid:logo" alt="img" width="160" border="0" style="display: block; width: 160px;" />
                                           </a>
                                           <div style="height: 73px; line-height: 73px; font-size: 71px;">&nbsp;</div>
                                        </td>
                                     </tr>
                                  </table>

                                  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
                                     <tr>
                                        <td align="left" valign="top">
                                           <font face="Source Sans Pro, sans-serif" color="#1a1a1a" style="font-size: 52px; line-height: 60px; font-weight: 300; letter-spacing: -1.5px;">
                                              <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 52px; line-height: 60px; font-weight: 300; letter-spacing: -1.5px;">Merhaba, '.$fullName.'</span>
                                           </font>
                                           <div style="height: 33px; line-height: 33px; font-size: 31px;">&nbsp;</div>
                                           <font face="Source Sans Pro, sans-serif" color="#585858" style="font-size: 24px; line-height: 32px;">
                                              <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #585858; font-size: 24px; line-height: 32px;">'.$yazokulad["name"].' adlı okula öğrenci olarak davet edildiniz. Aşağıdaki linke tıklayarak sisteme kayıt olabilirsiniz!</span>
                                           </font>
                                           <div style="height: 33px; line-height: 33px; font-size: 31px;">&nbsp;</div>
                                           <table class="mob_btn" cellpadding="0" cellspacing="0" border="0" style="background: #27cbcc; border-radius: 4px;">
                                              <tr>
                                                 <td align="center" valign="top"> 
                                                    <a href="http://localhost/sbm/signup-'.$mail_encoded.'-st-'.$invite_tokenxd.'" target="_blank" style="display: block; border: 1px solid #27cbcc; border-radius: 4px; padding: 12px 23px; font-family: Source Sans Pro, Arial, Verdana, Tahoma, Geneva, sans-serif; color: #ffffff; font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">
                                                       <font face="Source Sans Pro, sans-serif" color="#ffffff" style="font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">
                                                          <span style="font-family: Source Sans Pro, Arial, Verdana, Tahoma, Geneva, sans-serif; color: #ffffff; font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">Tıkla!</span>
                                                       </font>
                                                    </a>
                                                 </td>
                                              </tr>
                                           </table>
                                           <div style="height: 75px; line-height: 75px; font-size: 73px;">&nbsp;</div>
                                        </td>
                                     </tr>
                                  </table>

                                  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
                                     <tr>
                                        <td align="center" valign="top">
                                           <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
                                              <tr>
                                                 <td align="center" valign="top">
                                                    <div style="height: 34px; line-height: 34px; font-size: 32px;">&nbsp;</div>
                                                    <font face="Source Sans Pro, sans-serif" color="#868686" style="font-size: 17px; line-height: 20px;">
                                                       <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #868686; font-size: 17px; line-height: 20px;">Copyright &copy; 2019 Student Behavior Management.</span>
                                                    </font>
                                                    <div style="height: 3px; line-height: 3px; font-size: 1px;">&nbsp;</div>
                                                    <font face="Source Sans Pro, sans-serif" color="#1a1a1a" style="font-size: 17px; line-height: 20px;">
                                                       <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px;"><a href="mailto:sbm@aybarsakgun.com" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">sbm@aybarsakgun.com</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="tel:5555555555" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">+90 555 555 55 55</a></span>
                                                    </font>
                                                    <div style="height: 35px; line-height: 35px; font-size: 33px;">&nbsp;</div>
                                                 </td>
                                              </tr>
                                           </table>
                                        </td>
                                     </tr>
                                  </table>  

                               </td>
                               <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
                            </tr>
                         </table>
                         <!--[if (gte mso 9)|(IE)]>
                         </td></tr>
                         </table><![endif]-->
                      </td>
                   </tr>
                </table>
                </body>
                </html>
                ';
                $subject = "You've been invited as a student - Student Behavior Management";
                SendMail($eMail,$fullName,$message,$subject);
                $addedStudent++;
            }
            else if($checkMail->rowCount() == 1)
            {
                $getIt = $checkMail->fetch(PDO::FETCH_ASSOC);
                if($getIt["role"] == "student")
                {
                    $simdi = date('Y-m-d H:i:s');
                    $parcalasiniflar = explode(",", $siniflar);
                    foreach ($parcalasiniflar as $parcalasinif) {
                        $ogrenciduzenle = $DB_con->prepare("UPDATE users SET classes = if(find_in_set(:siniflarxd,classes),classes, CONCAT(classes, ',', :siniflarxd2)) , update_date = :updatedate WHERE email = :email AND role = :role");
                        $ogrenciduzenle->execute(array(":siniflarxd" => $parcalasinif, ":siniflarxd2" => $parcalasinif, ":updatedate" => $simdi, ":email" => $eMail, ":role" => "student"));
                    }
                    $editedStudent++;
                }
            }
        }
        echo json_encode(array("eklenen"=>$addedStudent,"duzenlenen"=>$editedStudent,"sonuc"=>1));
    }
    else if($page_request == "edit-class")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $class_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($class_id === false)
        {
            echo 0;
            exit();
        }
        if(!isset($class_id) || empty($class_id))
        {
            echo 0;
            exit();
        }
        $sorgusinif = $DB_con->prepare("SELECT id FROM classes WHERE id = :id");
        $sorgusinif->execute(array(":id"=>$class_id));
        if($sorgusinif->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorgu = $DB_con->prepare("SELECT id FROM classes WHERE FIND_IN_SET(:uyeid,teachers) AND school = :school AND id = :id");
        $sorgu->execute(array(":uyeid"=>$uyevtid,":school"=>$uyeokul,":id"=>$class_id));
        if($sorgu->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $class_name = filter_input(INPUT_POST, 'class_name', FILTER_SANITIZE_STRING);
        if(empty($class_name))
        {
            echo 2;
            exit();
        }
        if(strlen($class_name) < 3 || strlen($class_name) > 64)
        {
            echo 3;
            exit();
        }
        $class_status = filter_input(INPUT_POST, 'class_status', FILTER_VALIDATE_INT);
        if(empty($class_status) || $class_status === 0)
        {
            echo 2;
            exit();
        }
        if($class_status < 1 || $class_status > 2)
        {
            echo 0;
            exit();
        }
        $class_color = filter_input(INPUT_POST, 'class_color', FILTER_SANITIZE_STRING);
        if(empty($class_color) || $class_color === 0)
        {
            echo 2;
            exit();
        }
        $renkler = array("red","pink","purple","deep-purple","indigo","blue","light-blue","cyan","teal","green","light-green","lime","yellow","amber","orange","deep-orange","brown","grey","blue-grey","black");
        if(!in_array($class_color, $renkler)) {
            echo 0;
            exit();
        }
        $show_lastname = filter_input(INPUT_POST, 'show_lastname', FILTER_VALIDATE_INT);
        if(empty($show_lastname) || $show_lastname === 0)
        {
            echo 2;
            exit();
        }
        if($show_lastname < 1 || $show_lastname > 2)
        {
            echo 0;
            exit();
        }
        $show_point = filter_input(INPUT_POST, 'show_point', FILTER_VALIDATE_INT);
        if(empty($show_point) || $show_point === 0)
        {
            echo 2;
            exit();
        }
        if($show_point < 1 || $show_point > 3)
        {
            echo 0;
            exit();
        }
        $points_by_time = filter_input(INPUT_POST, 'points_by_time', FILTER_VALIDATE_INT);
        if(empty($points_by_time) || $points_by_time === 0)
        {
            echo 2;
            exit();
        }
        if($points_by_time < 1 || $points_by_time > 4)
        {
            echo 0;
            exit();
        }
        $sorguxd = $DB_con->prepare("UPDATE classes SET name = :name , status = :status , color = :color , student_show = :studentshow , point_show = :pointshow , points_by_time = :pointsbytime WHERE id = :classid");
        if($sorguxd->execute(array(":name"=>$class_name,":status"=>$class_status,":color"=>$class_color,":studentshow"=>$show_lastname,":pointshow"=>$show_point,":pointsbytime"=>$points_by_time,":classid"=>$class_id)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "invite-teacher-t-c")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $class_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($class_id === false)
        {
            echo 0;
            exit();
        }
        if(!isset($class_id) || empty($class_id))
        {
            echo 0;
            exit();
        }
        $sorgusinif = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND school = :school");
        $sorgusinif->execute(array(":id"=>$class_id,":school"=>$uyeokul));
        if($sorgusinif->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorgu = $DB_con->prepare("SELECT id FROM classes WHERE FIND_IN_SET(:uyeid,teachers) AND school = :school AND id = :id");
        $sorgu->execute(array(":uyeid"=>$uyevtid,":school"=>$uyeokul,":id"=>$class_id));
        if($sorgu->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $teacher = filter_input(INPUT_POST, 'invite_teacher', FILTER_VALIDATE_INT);
        if(empty($teacher) || $teacher === 0)
        {
            echo 2;
            exit();
        }
        $sorguteach = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND schools = :school AND role = :role");
        $sorguteach->execute(array(":id"=>$teacher,":school"=>$uyeokul,":role"=>"teacher"));
        if($sorguteach->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorguogretmen = $DB_con->prepare("SELECT users.id,users.name FROM users WHERE role = :role AND schools = :schools AND users.id = :teacherid AND (SELECT classes.id FROM classes WHERE classes.id = :classid AND FIND_IN_SET(users.id, classes.teachers))");
        $sorguogretmen->execute(array(":role"=>"teacher",":schools"=>$uyeokul,":teacherid"=>$teacher,":classid"=>$class_id));
        if($sorguogretmen->rowCount() > 0)
        {
            echo 0;
            exit();
        }
        $sorgudavet = $DB_con->prepare("SELECT id FROM invited_teachers WHERE class = :class AND invited = :invited");
        $sorgudavet->execute(array(":class"=>$class_id,":invited"=>$teacher));
        if($sorgudavet->rowCount() > 0)
        {
            echo 3;
            exit();
        }
        $datee = date('Y-m-d H:i:s');
        $sorguxd = $DB_con->prepare("INSERT INTO invited_teachers (class,inviting_by,invited,date) VALUES (:class,:invitingby,:invited,:date)");
        if($sorguxd->execute(array(":class"=>$class_id,":invitingby"=>$uyevtid,":invited"=>$teacher,":date"=>$datee)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "invite-answer")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $invite = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if($invite === false)
        {
            echo 0;
            exit();
        }
        if(!isset($invite) || empty($invite))
        {
            echo 0;
            exit();
        }
        $sorguinvite = $DB_con->prepare("SELECT id,class FROM invited_teachers WHERE invited = :id AND id = :invite");
        $sorguinvite->execute(array(":id"=>$uyevtid,":invite"=>$invite));
        if($sorguinvite->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $type = filter_input(INPUT_POST, 'type', FILTER_VALIDATE_INT);
        if($type === false)
        {
            echo 0;
            exit();
        }
        if(!isset($type) || empty($type))
        {
            echo 0;
            exit();
        }
        if($type < 1 || $type > 2)
        {
            echo 0;
            exit();
        }
        if($type == 1) {
            $yazinvite = $sorguinvite->fetch(PDO::FETCH_ASSOC);
            $sorgu = $DB_con->prepare("UPDATE classes SET teachers = if(find_in_set(:ogretmenxd,teachers),teachers, CONCAT(teachers, ',', :ogretmenxd2)) WHERE id = :classid AND school = :schoolid");
            $sorgu->execute(array(":ogretmenxd"=>$uyevtid,":ogretmenxd2"=>$uyevtid,":classid"=>$yazinvite["class"],":schoolid"=>$uyeokul));
            $sorgudavetsil = $DB_con->prepare("DELETE FROM invited_teachers WHERE invited = :id AND id = :invite");
            $sorgudavetsil->execute(array(":id"=>$uyevtid,":invite"=>$invite));
            echo 1;
            exit();
        } else if($type == 2) {
            $sorgudavetsil = $DB_con->prepare("DELETE FROM invited_teachers WHERE invited = :id AND id = :invite");
            $sorgudavetsil->execute(array(":id"=>$uyevtid,":invite"=>$invite));
            echo 1;
            exit();
        }

    }
    else if($page_request == "gclassroom-classes")
    {
        if($uyerol == "student")
        {
            echo 0;
            exit();
        }
        if($gClient->getAccessToken($gClient->setAccessToken($_SESSION['token'])))
        {
            $optParams = array(
                'teacherId' => $uyemail,
                'courseStates' => "ACTIVE",
                'pageSize' => 0
            );
            $results = $classroom->courses->listCourses($optParams);
            $countGcClasses = count($results->getCourses());
            if ($countGcClasses == 0)
            {
                echo "<div class='alert alert-warning'>Google Classroom hesabınıza ait oluşturulmuş sınıfınız bulunamadı.</div>";
                exit();
            }
            else
            {
                echo "<div class='alert alert-info'>Google Classroom hesabınıza ait oluşturulmuş, öğretmeni olduğunuz, <strong>".$countGcClasses."</strong> adet sınıf bulundu:</div><form id='Add-Class-Form'>";
                if($uyerol == "admin")
                {
                    echo "<div class='alert alert-warning'>".$yazokulad["name"]." okulunun yöneticisi olduğunuz için Google Classroom içerisinde öğretmeni olarak göründüğünüz sınıfları ve öğrencileri sisteme aktarabilmeniz için aşağıdan en az bir öğretmen seçmelisiniz.</div>";
                    $sorguxd = $DB_con->prepare("SELECT id,name FROM users WHERE schools = :school AND role = :role");
                    $sorguxd->execute(array(":school"=>$uyeokul,":role"=>"teacher"));
                    if($sorguxd->rowCount() > 0) {
                        echo '<label>Teacher(s):</label>';
                        while ($yazogretmenlerx = $sorguxd->fetch(PDO::FETCH_ASSOC)) {
                            echo '<div class="form-group"><input type="checkbox" id="teacher_'.$yazogretmenlerx["id"].'" name="teachers[]" class="filled-in chk-col-orange" value="'.$yazogretmenlerx["id"].'"><label for="teacher_'.$yazogretmenlerx["id"].'">'.$yazogretmenlerx["name"].'</label></div>';
                        }
                    }
                }
                echo "<table class='table table-condensed table-striped liste sync-gc-table'><thead><tr><th></th><th>Class Name</th><tbody>";
                foreach ($results->getCourses() as $course) {
                    echo '<tr><td><div class="switch" style="min-width:unset!important;"><label><input type="checkbox" id="add_check" data-input="' . $course->getId() . '" checked=""><span class="lever switch-col-orange"></span></label></div></td><td><div class="form-group" style="margin-bottom:0!important;"><div class="form-line">
                          <input type="text" class="form-control" data-input="' . $course->getId() . '" name="class[class_name_' . $course->getId() . ']" id="class[class_name_' . $course->getId() . ']" value="' . $course->getName() . '"></div></div></td></tr>';
                }
                echo '</tbody></table><div class="form-group"><button type="submit" class="btn btn-success btn-block btn-lg waves-effect Add-Class-Button">Add GC Classes</button></div><input type="hidden" name="toplamsinif" value="'.$countGcClasses.'"></form><div id="Add-Class-Result"></div>';
            }
        }
    }
	else if($page_request == "add-class")
	{
		if($uyerol == "student")
		{
            echo json_encode(array("sonuc"=>0));
			exit();
		}
		if($uyerol == "teacher") {
		    $sinifogrt = $uyevtid;
        }
		else if($uyerol == "admin")
		{
            if(isset($_POST["teachers"]))
            {
                $gelenogretmenler = $_POST["teachers"];
            }
            else
            {
                echo json_encode(array("sonuc"=>2));
                exit();
            }
            $ogretmenler = "";
            $prefix = "";
            foreach($gelenogretmenler as $val)
            {
                $ogretmenler.= $prefix.$val;
                $prefix = ",";
            }
            $sinifogrt = $ogretmenler;
        }
		else
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $eklenensinif = 0;
        $duzenlenensinif = 0;
        $eklenenogrenci = 0;
        $duzenlenenogrenci = 0;
        if($gClient->getAccessToken($gClient->setAccessToken($_SESSION['token']))) {
            foreach ($_POST["class"] as $gelenler => $val) {
                $sinifbilgi = explode("class_name_", $gelenler);
                if (count($sinifbilgi) == 2) {
                    $sinifsorgu = $DB_con->prepare("SELECT id FROM classes WHERE gc_id = :gccid");
                    $sinifsorgu->execute(array(":gccid" => $sinifbilgi[1]));
                    if ($sinifsorgu->rowCount() == 0) {
                        $renkler = array("red","pink","purple","deep-purple","indigo","blue","light-blue","cyan","teal","green","light-green","lime","yellow","amber","orange","deep-orange","brown","grey","blue-grey","black");
                        shuffle($renkler);
                        $sinifekle = $DB_con->prepare("INSERT INTO classes (name,school,teachers,owner,gc_id,color) VALUES (:name,:school,:teachers,:owner,:gcid,:color)");
                        if ($sinifekle->execute(array(":name" => $val, ":school" => $uyeokul, ":teachers" => $sinifogrt, ":owner" => $uyevtid, ":gcid" => $sinifbilgi[1], ":color" => $renkler[0]))) {
                            $eklenensinif++;
                        }
                    } else if ($sinifsorgu->rowCount() > 0) {
                        if($uyerol == "teacher") {
                            $sinifduzenle = $DB_con->prepare("UPDATE classes SET teachers = if(find_in_set(:ogretmenxd,teachers),teachers, CONCAT(teachers, ',', :ogretmenxd2)) WHERE gc_id = :gcid");
                            if ($sinifduzenle->execute(array(":ogretmenxd" => $uyevtid, ":ogretmenxd2" => $uyevtid, ":gcid" => $sinifbilgi[1]))) {
                                $duzenlenensinif++;
                            }
                        }
                        else if($uyerol == "admin")
                        {
                            $parcalaogretmenler = explode(",", $sinifogrt);
                            foreach ($parcalaogretmenler as $parcalaogretmen) {
                                $sinifduzenle = $DB_con->prepare("UPDATE classes SET teachers = if(find_in_set(:ogretmenxd,teachers),teachers, CONCAT(teachers, ',', :ogretmenxd2)) WHERE gc_id = :gcid");
                                $sinifduzenle->execute(array(":ogretmenxd" => $parcalaogretmen, ":ogretmenxd2" => $parcalaogretmen, ":gcid" => $sinifbilgi[1]));
                            }
                        }
                    }
                    $results = $classroom->courses_students->listCoursesStudents($sinifbilgi[1]);
                    foreach ($results->getStudents() as $student) {
                        $sorgusinif = $DB_con->prepare("SELECT id,name FROM classes WHERE gc_id = :gcid");
                        $sorgusinif->execute(array(":gcid" => $sinifbilgi[1]));
                        $yazsinifid = $sorgusinif->fetch(PDO::FETCH_ASSOC);
                        $sorguogrenci = $DB_con->prepare("SELECT id,role FROM users WHERE email = :email");
                        $sorguogrenci->execute(array(":email" => $student->profile->emailAddress));
                        if ($sorguogrenci->rowCount() == 0) {
                            $renkler = array("F44336", "E91E63", "9C27B0", "673AB7", "3F51B5", "2196F3", "03A9F4", "00BCD4", "009688", "4CAF50", "8BC34A", "CDDC39", "ffe821", "FFC107", "FF9800", "FF5722", "795548");
                            shuffle($renkler);
                            $adparcala = explode(" ", $student->profile->name->getFullName());
                            $emailparcala = explode("@", $student->profile->emailAddress);
                            $destin = 'img/avatars/' . $emailparcala[0] . '-' . $renkler[0] . '.jpg';
                            copy('https://ui-avatars.com/api/?name=' . reset($adparcala) . '+' . end($adparcala) . '&background=' . $renkler[0] . '&color=fff&bold=true&size=100', $destin);
                            $simdi = date('Y-m-d H:i:s');
                            $invite_token = bin2hex(random_bytes(32));
                            $ogrenciekle = $DB_con->prepare("INSERT INTO users (google_id,name,email,classes,schools,role,invite_token,invite_date,avatar,register_type) VALUES (:gid,:name,:email,:classes,:schools,:role,:invitetoken,:invitedate,:avatar,:registertype)");
                            if ($ogrenciekle->execute(array(":gid" => $student->getUserId(), ":name" => $student->profile->name->getFullName(), ":email" => $student->profile->emailAddress, ":classes" => $yazsinifid["id"], ":schools" => $uyeokul, ":role" => "student", ":invitetoken" => $invite_token, ":invitedate" => $simdi, ":avatar" => $destin, ":registertype"=>1))) {
                                $mail_encoded = rtrim(strtr(base64_encode($student->profile->emailAddress), '+/', '-_'), '=');
                                $message = '
                                <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
                                <html>
                                <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
                                <title>You have been invited as a student - Student Behavior Management</title>
                                <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
                                <style type="text/css">
                                html { -webkit-text-size-adjust: none; -ms-text-size-adjust: none;}

                                    @media only screen and (min-device-width: 750px) {
                                        .table750 {width: 750px !important;}
                                    }
                                    @media only screen and (max-device-width: 750px), only screen and (max-width: 750px){
                                      table[class="table750"] {width: 100% !important;}
                                      .mob_b {width: 93% !important; max-width: 93% !important; min-width: 93% !important;}
                                      .mob_b1 {width: 100% !important; max-width: 100% !important; min-width: 100% !important;}
                                      .mob_left {text-align: left !important;}
                                      .mob_soc {width: 50% !important; max-width: 50% !important; min-width: 50% !important;}
                                      .mob_menu {width: 50% !important; max-width: 50% !important; min-width: 50% !important; box-shadow: inset -1px -1px 0 0 rgba(255, 255, 255, 0.2); }
                                      .mob_center {text-align: center !important;}
                                      .top_pad {height: 15px !important; max-height: 15px !important; min-height: 15px !important;}
                                      .mob_pad {width: 15px !important; max-width: 15px !important; min-width: 15px !important;}
                                      .mob_div {display: block !important;}
                                    }
                                   @media only screen and (max-device-width: 550px), only screen and (max-width: 550px){
                                      .mod_div {display: block !important;}
                                   }
                                    .table750 {width: 750px;}
                                </style>
                                </head>
                                <body style="margin: 0; padding: 0;">

                                <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f3f3f3; min-width: 350px; font-size: 1px; line-height: normal;">
                                    <tr>
                                    <td align="center" valign="top">   			
                                        <!--[if (gte mso 9)|(IE)]>
                                         <table border="0" cellspacing="0" cellpadding="0">
                                         <tr><td align="center" valign="top" width="750"><![endif]-->
                                        <table cellpadding="0" cellspacing="0" border="0" width="750" class="table750" style="width: 100%; max-width: 750px; min-width: 350px; background: #f3f3f3;">
                                            <tr>
                                               <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
                                                <td align="center" valign="top" style="background: #ffffff;">

                                                  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
                                                     <tr>
                                                        <td align="right" valign="top">
                                                           <div class="top_pad" style="height: 25px; line-height: 25px; font-size: 23px;">&nbsp;</div>
                                                        </td>
                                                     </tr>
                                                  </table>

                                                  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
                                                     <tr>
                                                        <td align="left" valign="top">
                                                           <div style="height: 39px; line-height: 39px; font-size: 37px;">&nbsp;</div>
                                                           <a href="#" target="_blank" style="display: block; max-width: 128px;">
                                                              <img src="cid:logo" alt="img" width="160" border="0" style="display: block; width: 160px;" />
                                                           </a>
                                                           <div style="height: 73px; line-height: 73px; font-size: 71px;">&nbsp;</div>
                                                        </td>
                                                     </tr>
                                                  </table>

                                                  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
                                                     <tr>
                                                        <td align="left" valign="top">
                                                           <font face="Source Sans Pro, sans-serif" color="#1a1a1a" style="font-size: 52px; line-height: 60px; font-weight: 300; letter-spacing: -1.5px;">
                                                              <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 52px; line-height: 60px; font-weight: 300; letter-spacing: -1.5px;">Merhaba, ' . $student->profile->name->getFullName() . '</span>
                                                           </font>
                                                           <div style="height: 33px; line-height: 33px; font-size: 31px;">&nbsp;</div>
                                                           <font face="Source Sans Pro, sans-serif" color="#585858" style="font-size: 24px; line-height: 32px;">
                                                              <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #585858; font-size: 24px; line-height: 32px;">' . $yazokulad["name"] . ' adlı okulun ' . $yazsinifid["name"] . ' sınıfına öğrenci olarak davet edildiniz. Aşağıdaki linke tıklayarak sisteme kayıt olabilirsiniz!</span>
                                                           </font>
                                                           <div style="height: 33px; line-height: 33px; font-size: 31px;">&nbsp;</div>
                                                           <table class="mob_btn" cellpadding="0" cellspacing="0" border="0" style="background: #27cbcc; border-radius: 4px;">
                                                              <tr>
                                                                 <td align="center" valign="top"> 
                                                                    <a href="http://localhost/sbm/signup-' . $mail_encoded . '-st-' . $invite_token . '" target="_blank" style="display: block; border: 1px solid #27cbcc; border-radius: 4px; padding: 12px 23px; font-family: Source Sans Pro, Arial, Verdana, Tahoma, Geneva, sans-serif; color: #ffffff; font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">
                                                                       <font face="Source Sans Pro, sans-serif" color="#ffffff" style="font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">
                                                                          <span style="font-family: Source Sans Pro, Arial, Verdana, Tahoma, Geneva, sans-serif; color: #ffffff; font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">Tıkla!</span>
                                                                       </font>
                                                                    </a>
                                                                 </td>
                                                              </tr>
                                                           </table>
                                                           <div style="height: 75px; line-height: 75px; font-size: 73px;">&nbsp;</div>
                                                        </td>
                                                     </tr>
                                                  </table>

                                                  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
                                                     <tr>
                                                        <td align="center" valign="top">
                                                           <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
                                                              <tr>
                                                                 <td align="center" valign="top">
                                                                    <div style="height: 34px; line-height: 34px; font-size: 32px;">&nbsp;</div>
                                                                    <font face="Source Sans Pro, sans-serif" color="#868686" style="font-size: 17px; line-height: 20px;">
                                                                       <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #868686; font-size: 17px; line-height: 20px;">Copyright &copy; 2019 Student Behavior Management.</span>
                                                                    </font>
                                                                    <div style="height: 3px; line-height: 3px; font-size: 1px;">&nbsp;</div>
                                                                    <font face="Source Sans Pro, sans-serif" color="#1a1a1a" style="font-size: 17px; line-height: 20px;">
                                                                       <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px;"><a href="mailto:sbm@aybarsakgun.com" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">sbm@aybarsakgun.com</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="tel:5555555555" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">+90 555 555 55 55</a></span>
                                                                    </font>
                                                                    <div style="height: 35px; line-height: 35px; font-size: 33px;">&nbsp;</div>
                                                                 </td>
                                                              </tr>
                                                           </table>
                                                        </td>
                                                     </tr>
                                                  </table>  

                                               </td>
                                               <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
                                            </tr>
                                         </table>
                                         <!--[if (gte mso 9)|(IE)]>
                                         </td></tr>
                                         </table><![endif]-->
                                      </td>
                                   </tr>
                                </table>
                                </body>
                                </html>
                                ';
                                $subject = "You've been invited as a student - Student Behavior Management";
                                SendMail($student->profile->emailAddress,$val2,$message,$subject);
                                $eklenenogrenci++;
                            }
                        } else if ($sorguogrenci->rowCount() > 0) {
                            $yazogrenci = $sorguogrenci->fetch(PDO::FETCH_ASSOC);
                            if ($yazogrenci["role"] == "student") {
                                $simdi = date('Y-m-d H:i:s');
                                $ogrenciduzenle = $DB_con->prepare("UPDATE users SET classes = if(find_in_set(:siniflarxd,classes),classes, CONCAT(classes, ',', :siniflarxd2)) , update_date = :updatedate WHERE google_id = :gid AND role = :role");
                                if ($ogrenciduzenle->execute(array(":siniflarxd" => $yazsinifid["id"], ":siniflarxd2" => $yazsinifid["id"], ":updatedate" => $simdi, ":gid" => $student->getUserId(), ":role" => "student"))) {
                                    $duzenlenenogrenci++;
                                }
                            }
                        }
                    }
                } else {
                    echo json_encode(array("sonuc"=>0));
                    exit();
                }
            }
        }
        else
        {
            echo json_encode(array("sonuc"=>3));
            exit();
        }
		echo json_encode(array("toplam_sinif" => $_POST["toplamsinif"], "eklenen_sinif" => $eklenensinif, "duzenlenen_sinif" => $duzenlenensinif, "eklenen_ogrenci" => $eklenenogrenci, "duzenlenen_ogrenci" => $duzenlenenogrenci, "sonuc" => "1"));
	}
	else if($page_request == "get-students")
	{
		$sinifid = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
		if($sinifid === false)
		{
			echo 0;
			exit();
		}
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $sorgusinifid = $DB_con->prepare("SELECT id,student_show,point_show,points_by_time FROM classes WHERE FIND_IN_SET(:uyeid, teachers) AND id = :id AND school = :school");
        $sorgusinifid->execute(array(":uyeid"=>$uyevtid,":id"=>$sinifid,":school"=>$uyeokul));
        if($sorgusinifid->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $siralama_durum = isset($_GET['siralama']) ? (int) $_GET['siralama'] : 0;
        if($siralama_durum == 0)
        {
            $siralama_yazisi = "ORDER BY SUBSTRING_INDEX(users.name, ' ', 1) ASC";
        }
        else if($siralama_durum == 1)
        {
            $siralama_yazisi = "ORDER BY SUBSTRING_INDEX(users.name, ' ', 1) DESC";
        }
        else if($siralama_durum == 2)
        {
            $siralama_yazisi = "ORDER BY SUBSTRING_INDEX(users.name, ' ', -1) ASC";
        }
        else if($siralama_durum == 3)
        {
            $siralama_yazisi = "ORDER BY SUBSTRING_INDEX(users.name, ' ', -1) DESC";
        }
        else if($siralama_durum == 4)
        {
            $siralama_yazisi = "ORDER BY davranis_toplam DESC";
        }
        else if($siralama_durum == 5)
        {
            $siralama_yazisi = "ORDER BY davranis_toplam ASC";
        }
        else if($siralama_durum == 6)
        {
            $siralama_yazisi = "ORDER BY users.id DESC";
        }
        else if($siralama_durum == 7)
        {
            $siralama_yazisi = "ORDER BY users.id ASC";
        }
        else
        {
            $siralama_yazisi = "";
        }
        $yazsinifid = $sorgusinifid->fetch(PDO::FETCH_ASSOC);
        $pointsByTimeQuery = '';
        if ($yazsinifid['points_by_time'] == 2) {
            $pointsByTimeQuery = 'AND date(date) = CURDATE()';
        } else if ($yazsinifid['points_by_time'] == 3) {
            $pointsByTimeQuery = 'AND YEARWEEK(`date`, 1) = YEARWEEK(CURDATE(), 1)';
        } else if ($yazsinifid['points_by_time'] == 4) {
            $pointsByTimeQuery = 'AND MONTH(date) = MONTH(CURRENT_DATE())';
        }
		$sorguogrenciler = $DB_con->prepare("SELECT id,name,invite_date,register_date,update_date,avatar,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classidx $pointsByTimeQuery) as davranis_toplam,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classidx2 AND type = 1 $pointsByTimeQuery) as davranis_pozitif,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classidx3 AND type = 2 $pointsByTimeQuery) as davranis_negatif, (SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classidx4 AND type = 3 $pointsByTimeQuery) as redeem_point FROM users WHERE FIND_IN_SET(:sinifid, classes) AND role = :role AND schools = :school $siralama_yazisi");
		$sorguogrenciler->execute(array(":classidx"=>$sinifid,":classidx2"=>$sinifid,":classidx3"=>$sinifid,":classidx4"=>$sinifid,":sinifid"=>$sinifid,":role"=>"student",":school"=>$uyeokul));
		if($sorguogrenciler->rowCount() > 0)
		{
		    echo '<div class="row">';
            $sorgupuansx = $DB_con->prepare("SELECT (SELECT SUM(point) FROM feedbacks_students WHERE type = 1 AND class_id = :classid) as pozitifpuans,(SELECT SUM(point) FROM feedbacks_students WHERE type = 2 AND class_id = :classid2) as negatifpuans,(SELECT SUM(point) FROM feedbacks_students WHERE class_id = :classid3) as toplampuans");
            $sorgupuansx->execute(array(":classid"=>$sinifid,":classid2"=>$sinifid,":classid3"=>$sinifid));
            $yazpuansx = $sorgupuansx->fetch(PDO::FETCH_ASSOC);
            $a = $yazpuansx["negatifpuans"];
            $b = $yazpuansx["pozitifpuans"];
            $c = $yazpuansx["toplampuans"];
		    ?>
            <div class="col-xl-2 col-lg-3 col-md-3 col-sm-4 col-xs-12">
                <div class="info-box" style="margin-bottom:13px!important;">
                    <div class="icon">
                        <div class="chart chart-pie" id="whole-class" data-chartcolor="orange"><?= empty($b) ? "0" : abs($b) ?>,<?= empty($a) ? "0" : abs($a) ?></div>
                    </div>
                    <div class="content">
                        <div class="text nowrapwithellipsis">WHOLE CLASS</div>
                        <span class="label bg-green"><?= empty($b) ? "0" : $b ?></span>
                        <span class="label bg-red"><?= empty($a) ? "0" : $a ?></span>
                        <span class="label bg-blue"><?= empty($c) ? "0" : $c ?></span>
                    </div>
                </div>
            </div>
            <?php
		    $json_ogrenciler = "";
			while($yazogrenciler = $sorguogrenciler->fetch(PDO::FETCH_ASSOC)) {
			    if($yazsinifid["student_show"] == 2) {
                    $explodestudentname = explode(" ", $yazogrenciler["name"]);
                    array_pop($explodestudentname);
                    $namexdxd = implode(" ", $explodestudentname);
                } else {
                    $namexdxd = $yazogrenciler["name"];
                }
                ?>
                <div class="col-xl-2 col-lg-3 col-md-3 col-sm-4 col-xs-12">
                    <a href="javascript:;" data-toggle="modal" data-target="#modal-student" class="ogrenci-puanla"
                       id="<?= $yazogrenciler["id"] ?>" class_id="<?= $sinifid ?>">
                        <div class="info-box hover-expand-effect">
                            <div class="icon bg-light-green">
                                <img src="<?= $yazogrenciler["avatar"] ?>" width="100%" height="100%"
                                     class="student-img">
                            </div>
                            <div class="content">
                                <div class="text nowrapwithellipsis"><?= $namexdxd ?></div>
                                <?php if($yazsinifid["point_show"] == 1) { ?><span class="label bg-green"><?= empty($yazogrenciler["davranis_pozitif"]) ? "0" : $yazogrenciler["davranis_pozitif"] ?></span><?php } ?>
                                <?php if($yazsinifid["point_show"] == 1) { ?><span class="label bg-red"><?= empty($yazogrenciler["davranis_negatif"]) ? "0" : $yazogrenciler["davranis_negatif"] ?></span><?php } ?>
                                <?php if($yazsinifid["point_show"] == 1) { ?><span class="label bg-orange"><?= empty($yazogrenciler["redeem_point"]) ? "0" : $yazogrenciler["redeem_point"] ?></span><?php } ?>
                                <?php if($yazsinifid["point_show"] != 3) { ?><span class="label bg-blue <?php if($siralama_durum == 4 || $siralama_durum == 5) { echo "selected-behavior-label"; } ?>"><?= empty($yazogrenciler["davranis_toplam"]) ? "0" : $yazogrenciler["davranis_toplam"] ?></span><?php } ?>
                            </div>
                        </div>
                    </a>
                </div>
                <?php

                $json_ogrenciler .= "{'id':" . $yazogrenciler["id"] . ",'name':'" . $namexdxd . "','avatar':'" . $yazogrenciler["avatar"] . "','class':'" . $sinifid . "'},";
                ?>
                <script>
                    var ogrenciler = [<?=$json_ogrenciler?>];
                </script>
                <?php
            }
            echo '</div>';
		}
		else
		{
			echo 2;
			exit();
		}
	}
	else if($page_request == "get-redeem-items")
    {
        if($uyerol == "student")
        {
            $sorguyazisi = "";
            $prefix = "";
            $sorguteachers = $DB_con->prepare("SELECT (SELECT (SELECT GROUP_CONCAT(id) FROM users WHERE FIND_IN_SET(id,GROUP_CONCAT(classes.teachers))) FROM classes WHERE FIND_IN_SET(id, users.classes)) AS teachers FROM users WHERE role = :role AND schools = :school AND id = :id");
            $sorguteachers->execute(array(":role"=>"student",":school"=>$uyeokul,":id"=>$uyevtid));
            $yazteachers = $sorguteachers->fetch(PDO::FETCH_ASSOC);
            $explodeteachers = explode(",", $yazteachers["teachers"]);
            foreach($explodeteachers as $explodedteachers) {
                if($explodedteachers != "") {
                    $sorguyazisi .= $prefix . "user = " . $explodedteachers . " ";
                    $prefix = "OR ";
                }
            }
            $sorgu = $DB_con->prepare("SELECT * FROM redeem_items WHERE FIND_IN_SET(user, (SELECT GROUP_CONCAT(id) FROM users WHERE role = :roleadmin AND schools = :schools)) ".($sorguyazisi != "" ? "OR" : "")." $sorguyazisi");
            $sorgu->execute(array(":roleadmin"=>"admin",":schools"=>$uyeokul));
            if($sorgu->rowCount() > 0) {
                echo '<div class="row">';
                while ($yaz = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                    $sorguteachername = $DB_con->prepare("SELECT name FROM users WHERE (role = :role OR role = :role2) AND id = :id");
                    $sorguteachername->execute(array(":role" => "teacher", ":role2" => "admin", ":id" => $yaz["user"]));
                    $yazteachername = $sorguteachername->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
                        <div class="thumbnail">
                            <img src="<?= $yaz["image"] ?>" alt="<?= $yaz["name"] ?>">
                            <div class="caption">
                                <h3 class="nowrapwithellipsis"><?= $yaz["name"] ?></h3>
                                <p class="nowrapwithellipsis"><?= $yazteachername["name"] ?></p>
                                <button type="button" class="btn btn-success waves-effect"><?= $yaz["point"] ?> Points
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                echo '</div>';
            }
            else
            {
                echo 2;
                exit();
            }
        }
        else
        {
            $sorgu = $DB_con->prepare("SELECT * FROM redeem_items WHERE (user = :teacher OR FIND_IN_SET(user, (SELECT GROUP_CONCAT(id) FROM users WHERE role = :roleadmin AND schools = :schools))) ORDER BY id DESC");
            $sorgu->execute(array(":teacher"=>$uyevtid,":roleadmin"=>"admin",":schools"=>$uyeokul));
            if($sorgu->rowCount() > 0) {
                echo '<div class="row">';
                while ($yaz = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                    $sorguteachername = $DB_con->prepare("SELECT name FROM users WHERE (role = :role OR role = :role2) AND id = :id");
                    $sorguteachername->execute(array(":role" => "teacher", ":role2" => "admin", ":id" => $yaz["user"]));
                    $yazteachername = $sorguteachername->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
                        <div class="thumbnail">
                            <img src="<?= $yaz["image"] ?>" alt="<?= $yaz["name"] ?>">
                            <div class="caption">
                                <h3 class="nowrapwithellipsis"><?= $yaz["name"] ?></h3>
                                <p class="nowrapwithellipsis"><?= $yazteachername["name"] ?></p>
                                <p>
                                    <?php if ($yaz["user"] == $uyevtid) { ?>
                                        <button type="button"
                                                class="btn btn-info waves-effect editRedeemItemModalButton"
                                                data-redeem-item-id="<?= $yaz["id"] ?>">Edit</button><?php } ?>
                                    <button type="button"
                                            class="btn btn-success waves-effect <?= $yaz["user"] == $uyevtid ? pull-right : "" ?>"><?= $yaz["point"] ?> Points
                                    </button>
                                </p>
                                <small class="text-muted">Created
                                    at: <?= printDate($DB_con, $yaz["date"], $uyeokul) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                echo '</div>';
            }
            else
            {
                echo 2;
                exit();
            }
        }
    }
	else if($page_request == "student-feedback")
	{
		if($uyerol != "teacher")
		{
			echo 0;
			exit();
		}
		$ogrenci = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
		if($ogrenci === false)
		{
			echo 0;
			exit();
		}
		$sinif = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);
		if($sinif === false)
		{
			echo 0;
			exit();
		}
		$sorgu = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND schools = :school");
		$sorgu->execute(array(":id"=>$ogrenci,":sid"=>$sinif,":school"=>$uyeokul));
		if($sorgu->rowCount() != 1)
		{
			echo 0;
			exit();
		}
		$sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
		$sorgu2->execute(array(":id"=>$sinif,":oid"=>$uyevtid,":school"=>$uyeokul));
		if($sorgu2->rowCount() != 1)
		{
			echo 0;
			exit();
		}
		$sorguogrenci = $DB_con->prepare("SELECT name FROM users WHERE id = :id");
		$sorguogrenci->execute(array(":id"=>$ogrenci));
		$yazogrenci = $sorguogrenci->fetch(PDO::FETCH_ASSOC);
		?>
		<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="material-icons">close</i></button>
			<h4 class="modal-title"><?=$yazogrenci["name"]?></h4>
            <div class="btn-group m-t-5">
                <button type="button" class="btn btn-info waves-effect dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="caret"></span>
                    <span class="sr-only"><?=$yazogrenci["name"]?></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="report-<?=$sinif?>-<?=$ogrenci?>" class="waves-effect waves-block">Raporunu Görüntüle</a></li>
                    <?php
                    $sorguKonusma = $DB_con->prepare("SELECT class_name,class_id,first,second,id FROM conversations WHERE (first = :ben AND second = :o AND class_id = :class) OR (first = :o2 AND second = :ben2 AND class_id = :class2)");
                    $sorguKonusma->bindValue(':ben', $uyevtid, PDO::PARAM_INT);
                    $sorguKonusma->bindValue(':o', $ogrenci, PDO::PARAM_INT);
                    $sorguKonusma->bindValue(':o2', $ogrenci, PDO::PARAM_INT);
                    $sorguKonusma->bindValue(':ben2', $uyevtid, PDO::PARAM_INT);
                    $sorguKonusma->bindValue(':class', $sinif, PDO::PARAM_INT);
                    $sorguKonusma->bindValue(':class2', $sinif, PDO::PARAM_INT);
                    $sorguKonusma->execute();
                    $yazKonusma = $sorguKonusma->fetch(PDO::FETCH_ASSOC);
                    if($sorguKonusma->rowCount() > 0)
                    {
                        if($yazKonusma["first"] != $uyevtid) $gelenuyexd = $yazKonusma["first"];
                        else if($yazKonusma["second"] != $uyevtid) $gelenuyexd = $yazKonusma["second"];
                        ?>
                        <li><a href="messages-<?=seo($yazKonusma["class_name"])?>-<?=$yazKonusma["class_id"]?>-<?=$gelenuyexd?>-<?=$yazKonusma["id"]?>" class="waves-effect waves-block">Mesaj Gönder</a></li>
                        <?php
                    }
                    else
                    {
                        ?>
                        <li><a href="javascript:;"class="waves-effect waves-block send-message" data-student="<?=$ogrenci?>" data-class="<?=$sinif?>">Mesaj Gönder</a></li>
                        <?php
                    }
                    ?>
                    <li><a href="javascript:;" class="waves-effect waves-block send-mail-to-parent" data-student="<?=$ogrenci?>" data-class="<?=$sinif?>">Velisine Mail Gönder</a></li>
                    <li><a href="javascript:;" class="waves-effect waves-block edit-student" data-student="<?=$ogrenci?>" data-class="<?=$sinif?>">Düzenle</a></li>
                </ul>
            </div>
		</div>
		<div class="modal-body">
			<ul class="nav nav-tabs tab-col-orange" role="tablist">
				<li role="presentation" class="active">
					<a href="#home_with_icon_title" data-toggle="tab" aria-expanded="true" class="font-bold col-green">
                        <i class="material-icons">thumb_up</i> <span class="hidden-sm hidden-xs">Positive</span>
					</a>
				</li>
				<li role="presentation" class="">
					<a href="#profile_with_icon_title" data-toggle="tab" aria-expanded="false" class="font-bold col-red">
                        <i class="material-icons">thumb_down</i> <span class="hidden-sm hidden-xs">Negative</span>
					</a>
				</li>
                <li role="presentation" class="">
                    <a href="#history" data-toggle="tab" aria-expanded="false" class="font-bold col-orange">
                        <i class="material-icons">history</i> <span class="hidden-sm hidden-xs">History</span>
                    </a>
                </li>
                <li role="presentation" class="">
                    <a href="#redeem" data-toggle="tab" aria-expanded="false" class="font-bold col-light-blue">
                        <i class="material-icons">shopping_cart</i> <span class="hidden-sm hidden-xs">Redeem</span>
                    </a>
                </li>
			</ul>
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane fade active in" id="home_with_icon_title">
				<div class="row row-no-gutters">
					<?php
					$sorgufeed = $DB_con->prepare("SELECT id,name,point FROM feedbacks WHERE type = :type AND (user = :teacher OR FIND_IN_SET(user, (SELECT GROUP_CONCAT(id) FROM users WHERE role = :roleadmin AND schools = :schools)))");
					$sorgufeed->execute(array(":type"=>"1",":teacher"=>$uyevtid,":roleadmin"=>"admin",":schools"=>$uyeokul));
					if($sorgufeed->rowCount() > 0)
					{
					while($yazfeed = $sorgufeed->fetch(PDO::FETCH_ASSOC))
					{
					?>
					<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 m-b-5">
						<button class="btn btn-success btn-sm waves-effect waves-light btn-text-ellipsis give-behavior" type="button" data-student="<?=$ogrenci?>" data-class="<?=$sinif?>" data-behavior="<?=$yazfeed["id"]?>">
							<i class="material-icons behavior-icon">thumb_up</i>
							<span><?=$yazfeed["name"]?></span>
							<span class="badge">+<?=$yazfeed["point"]?></span>
						</button>
					</div>
					<?php
					}
					}
					else
					{
					?>
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"><div class="alert alert-warning">Henüz sisteme eklenen olumlu davranış notu bulunmamakta. Dilerseniz aşağıda bulunan butona tıklayarak yeni bir davranış notu ekleyebilirsiniz.</div></div>
					<?php
					}
					?>
				</div>
                    <div class="form-group m-t-15">
                        <label for="pointLocation">Point Location:</label>
                        <select class="form-control show-tick" id="point_location_1">
                            <?php
                            $getPointLocationsQuery = $DB_con->prepare("SELECT * FROM point_locations WHERE school = :school");
                            $getPointLocationsQuery->execute(array(':school'=>$uyeokul));
                            while($fetchPointLocations = $getPointLocationsQuery->fetch(PDO::FETCH_ASSOC)) {
                                ?>
                                <option value="<?=$fetchPointLocations['id']?>" <?php if($fetchPointLocations['id'] == 1) { echo 'selected'; } ?>><?=$fetchPointLocations['name']?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                <div class="form-group m-t-15">
                    <label for="feedback_description">Behavior Description:</label>
                    <div class="form-line">
                        <textarea class="form-control no-resize" id="feedback_description_1" name="feedback_description_1" rows="4"></textarea>
                    </div>
                </div>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="profile_with_icon_title">
				<div class="row row-no-gutters">
					<?php
					$sorgufeed = $DB_con->prepare("SELECT id,name,point FROM feedbacks WHERE type = :type AND (user = :teacher OR FIND_IN_SET(user, (SELECT GROUP_CONCAT(id) FROM users WHERE role = :roleadmin AND schools = :schools)))");
					$sorgufeed->execute(array(":type"=>"2",":teacher"=>$uyevtid,":roleadmin"=>"admin",":schools"=>$uyeokul));
					if($sorgufeed->rowCount() > 0)
					{
					while($yazfeed = $sorgufeed->fetch(PDO::FETCH_ASSOC))
					{
					?>
					<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 m-b-5">
						<button class="btn btn-danger btn-sm waves-effect waves-light btn-text-ellipsis give-behavior" type="button" data-student="<?=$ogrenci?>" data-class="<?=$sinif?>" data-behavior="<?=$yazfeed["id"]?>">
							<i class="material-icons behavior-icon">thumb_down</i>
							<span><?=$yazfeed["name"]?></span>
							<span class="badge"><?=$yazfeed["point"]?></span>
						</button>
					</div>
					<?php
					}
					}
					else
					{
					?>
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"><div class="alert alert-warning">Henüz sisteme eklenen olumsuz davranış notu bulunmamakta. Dilerseniz aşağıda bulunan butona tıklayarak yeni bir davranış notu ekleyebilirsiniz.</div></div>
					<?php
					}
					?>
				</div>
                    <div class="form-group m-t-15">
                        <label for="pointLocation">Point Location:</label>
                        <select class="form-control show-tick" id="point_location_2">
                            <?php
                            $getPointLocationsQuery = $DB_con->prepare("SELECT * FROM point_locations WHERE school = :school");
                            $getPointLocationsQuery->execute(array(':school'=>$uyeokul));
                            while($fetchPointLocations = $getPointLocationsQuery->fetch(PDO::FETCH_ASSOC)) {
                                ?>
                                <option value="<?=$fetchPointLocations['id']?>" <?php if($fetchPointLocations['id'] == 1) { echo 'selected'; } ?>><?=$fetchPointLocations['name']?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                <div class="form-group m-t-15">
                    <label for="feedback_description">Behavior Description:</label>
                    <div class="form-line">
                        <textarea class="form-control no-resize" id="feedback_description_2" name="feedback_description_2" rows="4"></textarea>
                    </div>
                </div>
				</div>
                <div role="tabpanel" class="tab-pane fade" id="history">
                    <?php
                    $sorguHistory = $DB_con->prepare("SELECT id,name,point,type,description,teacher,date FROM feedbacks_students WHERE class_id = :class AND student_id = :student AND type <> :type ORDER BY id DESC");
                    $sorguHistory->execute(array(":class"=>$sinif,":student"=>$ogrenci,":type"=>3));
                    if($sorguHistory->rowCount() > 0)
                    {
                        ?>
                        <table class="table table-bordered table-striped table-hover report-behavior-list dataTable nowrap" style="width:100%!important;">
                            <thead>
                            <tr>
                                <th>Behavior Name</th>
                                <th>Type</th>
                                <th>Point</th>
                                <th>Teacher</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            while($yazHistory = $sorguHistory->fetch(PDO::FETCH_ASSOC))
                            {
                                $sorguTeacher = $DB_con->prepare("SELECT name FROM users WHERE id = :id AND role = :role");
                                $sorguTeacher->execute(array(":id"=>$yazHistory["teacher"],":role"=>"teacher"));
                                $yazTeacher = $sorguTeacher->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <tr>
                                    <td><?=$yazHistory["name"]?></td>
                                    <td><?php if($yazHistory["type"] == 1) { echo "<b class='col-green'>Positive</b>"; } else if($yazHistory["type"] == 2) { echo "<b class='col-red'>Negative</b>"; } ?></td>
                                    <td><?=$yazHistory["point"]?></td>
                                    <td><?=$yazTeacher["name"]?></td>
                                    <td><?=printDate($DB_con, $yazHistory["date"], $uyeokul)?></td>
                                    <td><?=$yazHistory["description"]?></td>
                                    <td><button type="button" class="btn btn-danger btn-xs btn-block Revoke-Point-Button" data-student="<?=$ogrenci?>" data-class="<?=$sinif?>" data-point="<?=$yazHistory["id"]?>">Revoke</button></td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                        <?php
                    }
                    else
                    {
                        ?>
                        <div class="col-12">
                            <div class="alert alert-warning">Henüz öğrenciye verilen davranış notu bulunmamakta.</div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="redeem">
                    <?php
                    $sorgu = $DB_con->prepare("SELECT id,name,image,user,point FROM redeem_items WHERE (user = :teacher OR FIND_IN_SET(user, (SELECT GROUP_CONCAT(id) FROM users WHERE role = :roleadmin AND schools = :schools))) ORDER BY id DESC");
                    $sorgu->execute(array(":teacher"=>$uyevtid,":roleadmin"=>"admin",":schools"=>$uyeokul));
                    if($sorgu->rowCount() > 0) {
                        $sorguogrencipuan = $DB_con->prepare("SELECT SUM(point) as toplampuans FROM feedbacks_students WHERE student_id = :studentid AND class_id = :class");
                        $sorguogrencipuan->execute(array(":studentid"=>$ogrenci,":class"=>$sinif));
                        $yazogrencipuan = $sorguogrencipuan->fetch(PDO::FETCH_ASSOC);
                        echo '<div class="row">';
                        while ($yaz = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                            $sorguteachername = $DB_con->prepare("SELECT name FROM users WHERE id = :id");
                            $sorguteachername->execute(array(":id" => $yaz["user"]));
                            $yazteachername = $sorguteachername->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
                                <div class="thumbnail">
                                    <img src="<?= $yaz["image"] ?>" alt="<?= $yaz["name"] ?>" <?=($yazogrencipuan["toplampuans"] < abs($yaz["point"])) ? 'class="disable-img"' : ''?>>
                                    <div class="caption">
                                        <h3 class="nowrapwithellipsis"><?= $yaz["name"] ?></h3>
                                        <p class="nowrapwithellipsis"><?= $yazteachername["name"] ?></p>
                                        <p>
                                            <button type="button"
                                                    class="btn btn-success waves-effect giveRedeem" data-student="<?=$ogrenci?>" data-class="<?=$sinif?>" data-redeem="<?=$yaz["id"]?>" <?=($yazogrencipuan["toplampuans"] < abs($yaz["point"])) ? 'disabled="disabled"' : ''?>>Give
                                            </button>
                                            <button type="button"
                                                    class="btn <?=($yazogrencipuan["toplampuans"] < abs($yaz["point"])) ? 'btn-danger' : 'btn-success'?> waves-effect pull-right" <?=($yazogrencipuan["toplampuans"] < abs($yaz["point"])) ? 'disabled="disabled"' : ''?>><?= $yaz["point"] ?> Points
                                            </button>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        echo '</div>';
                    }
                    else
                    {
                        ?>
                        <div class="col-12">
                            <div class="alert alert-danger">No items available in the system.</div>
                        </div>
                        <?php
                    }
                    $sorguHistory = $DB_con->prepare("SELECT id,description,teacher,date,name,point FROM feedbacks_students WHERE class_id = :class AND student_id = :student AND type = :type ORDER BY id DESC");
                    $sorguHistory->execute(array(":class"=>$sinif,":student"=>$ogrenci,":type"=>3));
                    if($sorguHistory->rowCount() > 0)
                    {
                        ?>
                        <table class="table table-bordered table-striped table-hover report-redeem-list dataTable nowrap" style="width:100%!important;">
                            <thead>
                            <tr>
                                <th>Redeem Name</th>
                                <th>Point</th>
                                <th>Teacher</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            while($yazHistory = $sorguHistory->fetch(PDO::FETCH_ASSOC))
                            {
                                $sorguTeacher = $DB_con->prepare("SELECT name FROM users WHERE id = :id AND role = :role");
                                $sorguTeacher->execute(array(":id"=>$yazHistory["teacher"],":role"=>"teacher"));
                                $yazTeacher = $sorguTeacher->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <tr>
                                    <td><?=$yazHistory["name"]?></td>
                                    <td><?=abs($yazHistory["point"])?></td>
                                    <td><?=$yazTeacher["name"]?></td>
                                    <td><?=printDate($DB_con, $yazHistory["date"], $uyeokul)?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                        <?php
                    }
                    else
                    {
                        ?>
                        <div class="col-12">
                            <div class="alert alert-warning">Henüz öğrenciye verilen ödül bulunmamakta.</div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
			</div>
		</div>
		<div class="modal-footer">
            <div class="switch pull-left editBehaviorsButton" style="margin-top: 6px;">
                <label>Edit Behavior(s)<input type="checkbox" class="toggle-edit-behaviors"><span class="lever switch-col-green"></span></label>
            </div>
			<button type="button" class="btn btn-warning waves-effect addNewBehaviorButton" data-toggle="modal" data-target="#modal-add-behavior" data-student="<?=$ogrenci?>" data-class="<?=$sinif?>">Add New Behavior</button>
			<button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
		</div>
		<?php
	}
	else if($page_request == "start-conversation-modal")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $ogrenci = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($ogrenci === false)
        {
            echo 0;
            exit();
        }
        $sinif = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);
        if($sinif === false)
        {
            echo 0;
            exit();
        }
        $sorgu = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND schools = :school");
        $sorgu->execute(array(":id"=>$ogrenci,":sid"=>$sinif,":school"=>$uyeokul));
        if($sorgu->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
        $sorgu2->execute(array(":id"=>$sinif,":oid"=>$uyevtid,":school"=>$uyeokul));
        if($sorgu->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorguogrenci = $DB_con->prepare("SELECT name FROM users WHERE id = :id");
        $sorguogrenci->execute(array(":id"=>$ogrenci));
        $yazogrenci = $sorguogrenci->fetch(PDO::FETCH_ASSOC);
        $sorgusinif = $DB_con->prepare("SELECT name FROM classes WHERE id = :id");
        $sorgusinif->execute(array(":id"=>$sinif));
        $yazsinif = $sorgusinif->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="modal-header">
            <h4 class="modal-title"><a href="javascript:;" class="col-black goStudentGeneralModal" data-student="<?=$ogrenci?>" data-class="<?=$sinif?>" data-current-modal="sendMessageModal"><i class="material-icons">arrow_back</i></a> <div class="titleWithButton">Send Message to <?=$yazogrenci["name"]?></div></h4>
        </div>
        <div class="modal-body">
            <ul class="list-group">
                <li class="list-group-item">
                    <h6 class="list-group-item-heading">Class Name</h6>
                    <p class="list-group-item-text"><?=$yazsinif["name"]?></p>
                </li>
                <li class="list-group-item">
                    <h6 class="list-group-item-heading">Student Name</h6>
                    <p class="list-group-item-text"><?=$yazogrenci["name"]?></p>
                </li>
            </ul>
            <form id="Send-Message-Form">
                <div class="form-group">
                    <label for="name">Message:</label>
                    <div class="form-line">
                        <textarea class="form-control no-resize" id="message" name="message" rows="4"></textarea>
                    </div>
                </div>
                <input type="hidden" name="hidden_class_id" id="hidden_class_id" value="<?=$sinif?>">
                <input type="hidden" name="hidden_student_id" id="hidden_student_id" value="<?=$ogrenci?>">
                <input type="hidden" name="hidden_teacher_id" id="hidden_teacher_id" value="<?=$uyevtid?>">
                <div class="form-group">
                    <button type="submit" class="btn btn-success btn-block btn-lg waves-effect Send-Message-Button">Send Message</button>
                </div>
            </form>
            <div id="Send-Message-Result"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
    else if($page_request == "send-mail-to-parent-modal")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $ogrenci = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($ogrenci === false)
        {
            echo 0;
            exit();
        }
        $sinif = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);
        if($sinif === false)
        {
            echo 0;
            exit();
        }
        $sorgu = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND schools = :school");
        $sorgu->execute(array(":id"=>$ogrenci,":sid"=>$sinif,":school"=>$uyeokul));
        if($sorgu->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
        $sorgu2->execute(array(":id"=>$sinif,":oid"=>$uyevtid,":school"=>$uyeokul));
        if($sorgu->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorguogrenci = $DB_con->prepare("SELECT name,parent_email,parent_email2 FROM users WHERE id = :id");
        $sorguogrenci->execute(array(":id"=>$ogrenci));
        $yazogrenci = $sorguogrenci->fetch(PDO::FETCH_ASSOC);
        $sorgusinif = $DB_con->prepare("SELECT name FROM classes WHERE id = :id");
        $sorgusinif->execute(array(":id"=>$sinif));
        $yazsinifad = $sorgusinif->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="modal-header">
            <h4 class="modal-title"><a href="javascript:;" class="col-black goStudentGeneralModal" data-student="<?=$ogrenci?>" data-class="<?=$sinif?>" data-current-modal="sendMailToParentModal"><i class="material-icons">arrow_back</i></a> <div class="titleWithButton">Send Mail to <?=$yazogrenci["name"]?>'s Parent</div></h4>
        </div>
        <div class="modal-body">
            <form id="Send-Mail-To-Parent-Form">
                <div class="form-group p-b-10">
                    <label>Class: <u><?= $yazsinifad["name"] ?></u></label>
                </div>
                <div class="form-group p-b-10">
                    <label>Message Template:</label>
                    <select class="form-control" id="message_template" name="message_template">
                        <option value="0">Choose</option>
                        <?php
                        $sorgusablon = $DB_con->prepare("SELECT id,name,text FROM message_templates WHERE user_id = :uye");
                        $sorgusablon->execute(array(":uye" => $uyevtid));
                        while ($yazsablon = $sorgusablon->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                            <option value="<?= $yazsablon["id"] ?>"><?= $yazsablon["name"] ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group p-b-10">
                    <label>Message Template:</label>
                    <br>
                    <small>Available variables: <strong>{{studentName}}</strong></small>
                    <textarea name="message" id="message"></textarea>
                </div>
                <input type="hidden" name="studentid" id="studentid" value="<?=$ogrenci?>">
                <input type="hidden" name="classid" id="classid" value="<?=$sinif?>">
                <?php
                if($yazogrenci["parent_email"] != "" || $yazogrenci["parent_email2"] != "") {
                    ?>
                    <div class="form-group">
                        <button type="submit"
                                class="btn btn-success btn-block btn-lg waves-effect Send-Mail-To-Parent-Button">
                            Send
                        </button>
                    </div>
                    <?php
                } else {
                    echo "<div class='alert alert-danger'><strong>Error:</strong> No e-mail address of the student's parents could be found. Please update the student's parent email address first.";
                }
                ?>
            </form>
            <div id="Send-Mail-To-Parent-Result"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
    else if($page_request == "edit-student-modal")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $ogrenci = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($ogrenci === false)
        {
            echo 0;
            exit();
        }
        $sinif = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);
        if($sinif === false)
        {
            echo 0;
            exit();
        }
        $sorgu = $DB_con->prepare("SELECT id,name,classes,schools,parent_name,parent_email,parent_email2,parent_phone,parent_phone2,homeroom,gender,stateID,grade FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND schools = :school");
        $sorgu->execute(array(":id"=>$ogrenci,":sid"=>$sinif,":school"=>$uyeokul));
        if($sorgu->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
        $sorgu2->execute(array(":id"=>$sinif,":oid"=>$uyevtid,":school"=>$uyeokul));
        if($sorgu2->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $yazogrenci = $sorgu->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="modal-header">
            <h4 class="modal-title"><a href="javascript:;" class="col-black goStudentGeneralModal" data-student="<?=$yazogrenci["id"]?>" data-class="<?=$sinif?>" data-current-modal="editStudentModal"><i class="material-icons">arrow_back</i></a> <div class="titleWithButton">Editing Student: <?=$yazogrenci["name"]?></div></h4>
        </div>
        <div class="modal-body">
            <form id="Edit-Student-Form">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <div class="form-line">
                        <input class="form-control" name="name" id="name" type="text" value="<?=$yazogrenci["name"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="name">Parent Name:</label>
                    <div class="form-line">
                        <input class="form-control" name="parentname" id="parentname" type="text" value="<?=$yazogrenci["parent_name"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="name">Parent Primary E-Mail Address:</label>
                    <div class="form-line">
                        <input class="form-control" name="parentemail" id="parentemail" type="text" value="<?=$yazogrenci["parent_email"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="name">Parent Secondary E-Mail Address:</label>
                    <div class="form-line">
                        <input class="form-control" name="parentemail2" id="parentemail2" type="text" value="<?=$yazogrenci["parent_email2"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="name">Parent Primary Phone Number:</label>
                    <div class="form-line">
                        <input class="form-control" name="parentphone" id="parentphone" type="text" value="<?=$yazogrenci["parent_phone"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="name">Parent Secondary Phone Number:</label>
                    <div class="form-line">
                        <input class="form-control" name="parentphone2" id="parentphone2" type="text" value="<?=$yazogrenci["parent_phone2"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="homeroom">Homeroom:</label>
                    <div class="form-line">
                        <input class="form-control" name="homeroom" id="homeroom" type="text" value="<?=$yazogrenci["homeroom"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <div class="form-line">
                        <input class="form-control" name="gender" id="gender" type="text" value="<?=$yazogrenci["gender"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="stateID">StateID:</label>
                    <div class="form-line">
                        <input class="form-control" name="stateID" id="stateID" type="text" value="<?=$yazogrenci["stateID"]?>" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                    </div>
                </div>
                <div class="form-group">
                    <label for="grade">Grade:</label>
                    <div class="form-line">
                        <input class="form-control" name="grade" id="grade" type="text" value="<?=$yazogrenci["grade"]?>" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                    </div>
                </div>
                <label>Classes:</label>
                <div class="row">
                <?php
                $explodeclasses = explode(",", $yazogrenci["classes"]);
                foreach($explodeclasses as $explodedclasses)
                {
                    $sClasses[] = $explodedclasses;
                }
                implode(",", $sClasses);
                $classesQuery = $DB_con->prepare("SELECT id,name,color FROM classes WHERE FIND_IN_SET(:uyeid,teachers) AND school = :school AND status = :status ORDER BY id ASC");
                $classesQuery->execute(array(":uyeid"=>$uyevtid,":school"=>$uyeokul,":status"=>1));
                while($getClasses = $classesQuery->fetch(PDO::FETCH_ASSOC))
                {
                    ?>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <input type="checkbox" name="class[]" value="<?=$getClasses["id"]?>" id="classIdfor<?=$getClasses["id"]?>" <?php if(in_array($getClasses["id"],$sClasses)) { echo 'checked'; } ?> class="filled-in chk-col-orange">
                            <label for="classIdfor<?=$getClasses["id"]?>"><?=$getClasses["name"]?></label>
                        </div>
                    </div>
                    <?php
                }
                ?>
                </div>
                <input type="hidden" name="hidden_student_id" id="hidden_student_id" value="<?=$yazogrenci["id"]?>">
                <input type="hidden" name="hidden_class_id" id="hidden_class_id" value="<?=$sinif?>">
                <div id="Edit-Student-Result"></div>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block btn-lg waves-effect Edit-Student-Button">Edit Student</button>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <button type="button" class="btn btn-danger btn-block btn-lg waves-effect Delete-Student-Button" data-student="<?=$yazogrenci["id"]?>" data-class="<?=$sinif?>">Delete This Student</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
	else if($page_request == "add-behavior")
	{
		if($uyerol != "teacher")
		{
			echo json_encode(array("sonuc"=>0));
			exit();
		}
		$ogrenci = filter_input(INPUT_POST, 'hidden_student_id', FILTER_VALIDATE_INT);
		if($ogrenci === false)
		{
			echo json_encode(array("sonuc"=>0));
			exit();
		}
		$sinif = filter_input(INPUT_POST, 'hidden_class_id', FILTER_VALIDATE_INT);
		if($sinif === false)
		{
			echo json_encode(array("sonuc"=>0));
			exit();
		}
		$sorgu = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND schools = :school");
		$sorgu->execute(array(":id"=>$ogrenci,":sid"=>$sinif,":school"=>$uyeokul));
		if($sorgu->rowCount() != 1)
		{
			echo json_encode(array("sonuc"=>0));
			exit();
		}
		$sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
		$sorgu2->execute(array(":id"=>$sinif,":oid"=>$uyevtid,":school"=>$uyeokul));
		if($sorgu2->rowCount() != 1)
		{
			echo json_encode(array("sonuc"=>0));
			exit();
		}
		$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
		$type = filter_input(INPUT_POST, 'type', FILTER_VALIDATE_INT);
		if(($type == 0 || $type == 1 || $type == 2) && $type === false)
		{
			echo json_encode(array("sonuc"=>0));
			exit();
		}
		$point = filter_input(INPUT_POST, 'point', FILTER_VALIDATE_INT);
		if($point != null && $point === false)
		{
			echo json_encode(array("sonuc"=>0));
			exit();
		}
		if($type < 0 || $type > 2)
		{
			echo json_encode(array("sonuc"=>0));
			exit();
		}
		if(empty($name) || $type == 0 || empty($point))
		{
			echo json_encode(array("sonuc"=>2));
			exit();
		}
		if(strlen($name) < 3 || strlen($name) > 64)
		{
			echo json_encode(array("sonuc"=>3));
			exit();
		}
		if($point < 1 || $point > 100)
		{
			echo json_encode(array("sonuc"=>4));
			exit();
		}
		$puanxd = "";
		if($type == 1)
		{
			$puanxd = $point;
		}
		if($type == 2)
		{
			$puanxd = "-".$point;
		}
		$sorguekle = $DB_con->prepare("INSERT INTO feedbacks(name,point,type,user) VALUES (:name,:point,:type,:user)");
		if($sorguekle->execute(array(":name"=>$name,":point"=>$puanxd,":type"=>$type,":user"=>$uyevtid)))
		{
			echo json_encode(array("student"=>$ogrenci,"class"=>$sinif,"sonuc"=>1));
			exit();
		}
		else
		{
			echo json_encode(array("sonuc"=>0));
			exit();
		}
	}
	else if($page_request == "give-behavior")
    {
        if($uyerol != "teacher")
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $ogrenci = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if($ogrenci === false)
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $sinif = filter_input(INPUT_POST, 'class', FILTER_VALIDATE_INT);
        if($sinif === false)
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $behavior = filter_input(INPUT_POST, 'behavior', FILTER_VALIDATE_INT);
        if($behavior === false)
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $sorgu = $DB_con->prepare("SELECT id,name FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND schools = :school");
        $sorgu->execute(array(":id"=>$ogrenci,":sid"=>$sinif,":school"=>$uyeokul));
        if($sorgu->rowCount() != 1)
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
        $sorgu2->execute(array(":id"=>$sinif,":oid"=>$uyevtid,":school"=>$uyeokul));
        if($sorgu2->rowCount() != 1)
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $sorgu3 = $DB_con->prepare("SELECT id,name,point,type FROM feedbacks WHERE id = :id AND (user = :teacher OR FIND_IN_SET(user, (SELECT GROUP_CONCAT(id) FROM users WHERE role = :roleadmin AND schools = :schools)))");
        $sorgu3->execute(array(":id"=>$behavior,":teacher"=>$uyevtid,":roleadmin"=>"admin",":schools"=>$uyeokul));
        if($sorgu3->rowCount() != 1)
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $yazStudentName = $sorgu->fetch(PDO::FETCH_ASSOC);
        $yazFeedbackType = $sorgu3->fetch(PDO::FETCH_ASSOC);
        $simdi = date('Y-m-d H:i:s');
        if($yazFeedbackType["type"] == 1) {
            $description = filter_input(INPUT_POST, 'feedback_description_1', FILTER_SANITIZE_STRING);
            $pointLocation = filter_input(INPUT_POST, 'point_location_1', FILTER_SANITIZE_STRING);
        }
        else if($yazFeedbackType["type"] == 2) {
            $description = filter_input(INPUT_POST, 'feedback_description_2', FILTER_SANITIZE_STRING);
            $pointLocation = filter_input(INPUT_POST, 'point_location_2', FILTER_SANITIZE_STRING);
        }
        $sorguekle = $DB_con->prepare("INSERT INTO feedbacks_students(class_id,student_id,name,point,type,description,teacher,date,point_location) VALUES (:class,:student,:bname,:bpoint,:btype,:description,:teacher,:date,:pointlocation)");
        if($sorguekle->execute(array(":class"=>$sinif,":student"=>$ogrenci,":bname"=>$yazFeedbackType["name"],":bpoint"=>$yazFeedbackType["point"],":btype"=>$yazFeedbackType["type"],":description"=>$description,":teacher"=>$uyevtid,":date"=>$simdi,":pointlocation"=>$pointLocation)))
        {
            echo json_encode(array("feedback_type"=>$yazFeedbackType["type"],"sonuc"=>1,"feedback_name"=>$yazFeedbackType["name"],"student_name"=>$yazStudentName["name"]));
            exit();
        }
        else
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
    }
    else if($page_request == "give-redeem")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $ogrenci = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if($ogrenci === false)
        {
            echo 0;
            exit();
        }
        $sinif = filter_input(INPUT_POST, 'class', FILTER_VALIDATE_INT);
        if($sinif === false)
        {
            echo 0;
            exit();
        }
        $redeem = filter_input(INPUT_POST, 'redeem', FILTER_VALIDATE_INT);
        if($redeem === false)
        {
            echo 0;
            exit();
        }
        $sorgu = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND schools = :school");
        $sorgu->execute(array(":id"=>$ogrenci,":sid"=>$sinif,":school"=>$uyeokul));
        if($sorgu->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
        $sorgu2->execute(array(":id"=>$sinif,":oid"=>$uyevtid,":school"=>$uyeokul));
        if($sorgu2->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorgu4 = $DB_con->prepare("SELECT id,name,point,image FROM redeem_items WHERE id = :id AND (user = :user OR FIND_IN_SET(user, (SELECT GROUP_CONCAT(id) FROM users WHERE role = :roleadmin AND schools = :schools)))");
        $sorgu4->execute(array(":id"=>$redeem,":user"=>$uyevtid,":roleadmin"=>"admin",":schools"=>$uyeokul));
        if($sorgu4->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $yazRedeem = $sorgu4->fetch(PDO::FETCH_ASSOC);
        $sorguogrencipuan = $DB_con->prepare("SELECT SUM(point) as toplampuans FROM feedbacks_students WHERE student_id = :studentid AND class_id = :class");
        $sorguogrencipuan->execute(array(":studentid"=>$ogrenci,":class"=>$sinif));
        $yazogrencipuan = $sorguogrencipuan->fetch(PDO::FETCH_ASSOC);
        if($yazogrencipuan["toplampuans"] < abs($yazRedeem["point"]))
        {
            echo 2;
            exit();
        }
        $simdi = date('Y-m-d H:i:s');
        $yazredeempuan = -$yazRedeem["point"];
        $sorguekle = $DB_con->prepare("INSERT INTO feedbacks_students(class_id,student_id,name,point,type,description,teacher,date) VALUES (:class,:student,:rname,:rpoint,:rtype,:description,:teacher,:date)");
        if($sorguekle->execute(array(":class"=>$sinif,":student"=>$ogrenci,":rname"=>$yazRedeem["name"],":rpoint"=>$yazredeempuan,":rtype"=>3,":description"=>"",":teacher"=>$uyevtid,":date"=>$simdi)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "give-behavior-multiple")
    {
        if($uyerol != "teacher")
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $ogrenciler = filter_input(INPUT_POST, 'ids', FILTER_SANITIZE_STRING);
        $sinif = filter_input(INPUT_POST, 'class', FILTER_VALIDATE_INT);
        if($sinif === false)
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $behavior = filter_input(INPUT_POST, 'behavior', FILTER_VALIDATE_INT);
        if($behavior === false)
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers)");
        $sorgu2->execute(array(":id"=>$sinif,":oid"=>$uyevtid));
        if($sorgu2->rowCount() != 1)
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $sorgu3 = $DB_con->prepare("SELECT id,name,point,type FROM feedbacks WHERE id = :id AND (user = :user OR FIND_IN_SET(user, (SELECT GROUP_CONCAT(id) FROM users WHERE role = :roleadmin AND schools = :schools)))");
        $sorgu3->execute(array(":id"=>$behavior,":user"=>$uyevtid,":roleadmin"=>"admin",":schools"=>$uyeokul));
        if($sorgu3->rowCount() != 1)
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $yazFeedbackType = $sorgu3->fetch(PDO::FETCH_ASSOC);
        $simdi = date('Y-m-d H:i:s');
        if($yazFeedbackType["type"] == 1) {
            $description = filter_input(INPUT_POST, 'feedback_description_1', FILTER_SANITIZE_STRING);
            $pointLocation = filter_input(INPUT_POST, 'point_location_1', FILTER_SANITIZE_STRING);
        }
        else if($yazFeedbackType["type"] == 2) {
            $description = filter_input(INPUT_POST, 'feedback_description_2', FILTER_SANITIZE_STRING);
            $pointLocation = filter_input(INPUT_POST, 'point_location_2', FILTER_SANITIZE_STRING);
        }
        $explodeogrenciler = explode(",", $ogrenciler);
        foreach(array_unique($explodeogrenciler) as $expogrenciler) {
            $sorguekle = $DB_con->prepare("INSERT INTO feedbacks_students(class_id,student_id,name,point,type,description,teacher,date,point_location) VALUES (:class,:student,:bname,:bpoint,:btype,:description,:teacher,:date,:pointlocation)");
            $sorguekle->execute(array(":class" => $sinif, ":student" => $expogrenciler, ":bname"=>$yazFeedbackType["name"], ":bpoint"=>$yazFeedbackType["point"], ":btype"=>$yazFeedbackType["type"],":description" => $description, ":teacher" => $uyevtid, ":date" => $simdi, ":pointlocation" => $pointLocation));
        }
        echo json_encode(array("feedback_type" => $yazFeedbackType["type"], "sonuc" => 1));
        exit();
    }
	else if($page_request == "logout")
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
		$gClient->revokeToken();
		echo 1;		
	}
    else if($page_request == "admin-logon-records")
    {
        if($uyerol == "admin")
        {
            $sorguSay = $DB_con->prepare("SELECT COUNT(member_id) AS say FROM login_attempts_user WHERE member_id = :uyeid");
            $sorguSay->execute(array(":uyeid"=>$uyevtid));
            $yazSay = $sorguSay->fetch(PDO::FETCH_ASSOC);
            if($sorguSay->rowCount() > 0)
            {
                $filtre_yazisi = "";
                $prefix = "";
                $siralama_durum = isset($_GET['siralama']) ? (int) $_GET['siralama'] : 0;
                if($siralama_durum == 0)
                {
                    $siralama_yazisi = "ORDER BY date_time DESC";
                }
                else if($siralama_durum == 1)
                {
                    $siralama_yazisi = "ORDER BY date_time ASC";
                }
                else
                {
                    $siralama_yazisi = "";
                }

                if(isset($_GET["filtre_durum"]))
                {
                    $gelen_filtremax = filter_input(INPUT_GET, 'filtre_durum', FILTER_SANITIZE_STRING);
                    $filtre_durum = preg_replace('/[^a-z0-9]/', '', $gelen_filtremax);
                    if($filtre_durum != "")
                    {
                        if($filtre_durum == "filtredurumbasarili")
                        {
                            $filtre_yazisi .= $prefix . "status = 1 ";
                            $prefix = 'AND ';
                        }
                        else if($filtre_durum == "filtredurumbasarisiz")
                        {
                            $filtre_yazisi .= $prefix . "status = 0 ";
                            $prefix = 'AND ';
                        }
                    }
                }

                if($filtre_yazisi != "")
                {
                    $sorguyazisi = "SELECT COUNT(member_id) AS say FROM login_attempts_user WHERE $filtre_yazisi AND member_id = :uyeid";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT COUNT(member_id) AS say FROM login_attempts_user WHERE member_id = :uyeid";
                }

                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute(array(":uyeid"=>$uyevtid));
                $sayimsonucu = $sorgu->fetch(PDO::FETCH_ASSOC);

                $sayfada = 10;
                $toplam_icerik = $sayimsonucu["say"];
                $toplam_sayfa = ceil($toplam_icerik / $sayfada);
                $sayfa = isset($_GET['sayfa']) ? (int) $_GET['sayfa'] : 1;
                if($sayfa < 1) $sayfa = 1;
                if($sayfa > $toplam_sayfa) $sayfa = $toplam_sayfa;
                $limit = ($sayfa - 1) * $sayfada;

                if($filtre_yazisi != "")
                {
                    $sorguyazisi = "SELECT ip,date,browser,status FROM login_attempts_user WHERE $filtre_yazisi AND member_id = :uyeid $siralama_yazisi LIMIT :limit , :sayfada";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT ip,date,browser,status FROM login_attempts_user WHERE member_id = :uyeid $siralama_yazisi LIMIT :limit , :sayfada";
                }

                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute(array(":uyeid"=>$uyevtid,":limit"=>abs($limit),":sayfada"=>$sayfada));

                if($sayimsonucu["say"] > 0)
                {
                    if($filtre_yazisi == "")
                    {
                        ?>
                        <small>Toplam <?=$sayimsonucu["say"]?> tane bulunan sonuçtan <?=$sorgu->rowCount()?> tanesini görüntülüyorsunuz.</small>
                        <?php
                    }
                    else
                    {
                        ?>
                        <small>Toplam <?=$yazSay["say"]?> tane bulunan sonuçtan, filtrelemenize uygun <?=$sayimsonucu["say"]?> tanesinin <?=$sorgu->rowCount()?> tanesini görüntülüyorsunuz.</small>
                        <?php
                    }
                }
                ?>
                <table class="table table-condensed table-striped liste">
                    <thead>
                    <tr>
                        <th>IP Address</th>
                        <th class="baslik_th">Browser</th>
                        <th class="visible-sm visible-md visible-lg">Date</th>
                        <th class="visible-sm visible-md visible-lg">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if($sayimsonucu["say"] > 0)
                    {
                        while($yaz = $sorgu->fetch(PDO::FETCH_ASSOC))
                        {
                            ?>
                            <tr>
                                <td><?=$yaz["ip"]?></td>
                                <td class="baslik_td">
                                    <span class="baslik"><?=$yaz["browser"]?></span>
                                    <div class="visible-xs">
                                        <strong>Date:</strong> <?=printDate($DB_con, $yaz["date"], $uyeokul)?><br>
                                        <strong>Status:</strong><br>
                                        <?php if($yaz["status"] == "0") { ?><span class="label label-danger">Unsuccessful</span><?php } else if($yaz["status"] == "1") { ?><span class="label label-success">Successful</span><?php } ?>
                                    </div>
                                </td>
                                <td class="visible-sm visible-md visible-lg"><?=printDate($DB_con, $yaz["date"], $uyeokul)?></td>
                                <td class="visible-sm visible-md visible-lg"><?php if($yaz["status"] == "0") { ?><span class="label label-danger">Unsuccessful</span><?php } else if($yaz["status"] == "1") { ?><span class="label label-success">Successful</span><?php } ?></td>
                            </tr>
                            <?php
                        }
                    }
                    else
                    {
                        ?>
                        <tr>
                            <td colspan="5">No results were found for your filtering.</td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
                <?php
                if($sayimsonucu["say"] > 0)
                {
                    ?>
                    <ul class="pagination pagination-sm">
                        <?php
                        $sayfa_goster = 5;

                        $en_az_orta = ceil($sayfa_goster/2);
                        $en_fazla_orta = ($toplam_sayfa+1) - $en_az_orta;

                        $sayfa_orta = $sayfa;
                        if($sayfa_orta < $en_az_orta) $sayfa_orta = $en_az_orta;
                        if($sayfa_orta > $en_fazla_orta) $sayfa_orta = $en_fazla_orta;

                        $sol_sayfalar = round($sayfa_orta - (($sayfa_goster-1) / 2));
                        $sag_sayfalar = round((($sayfa_goster-1) / 2) + $sayfa_orta);

                        if($sol_sayfalar < 1) $sol_sayfalar = 1;
                        if($sag_sayfalar > $toplam_sayfa) $sag_sayfalar = $toplam_sayfa;

                        if($sayfa != 1) echo '<li><a class="waves-effect sayfala-buton2" href="javascript:void(0);" id="1"><i class="material-icons">first_page</i></a></li>';
                        else if($sayfa == 1) echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">first_page</i></a></li>';
                        if($sayfa != 1) echo '<li><a class="waves-effect sayfala-buton2" href="javascript:void(0);" id="'.($sayfa-1).'"><i class="material-icons">chevron_left</i></a></li>';
                        else if($sayfa == 1) echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">chevron_left</i></a></li>';

                        for($s = $sol_sayfalar; $s <= $sag_sayfalar; $s++) {
                            if($sayfa == $s) {
                                echo '<li class="active"><a href="javascript:void(0);">'.$s.'</a></li>';
                            } else {
                                echo '<li><a class="waves-effect sayfala-buton2" href="javascript:void(0);" id="'.$s.'">'.$s.'</a></li>';
                            }
                        }

                        if($sayfa != $toplam_sayfa) echo '<li><a class="waves-effect sayfala-buton2" href="javascript:void(0);" id="'.($sayfa+1).'"><i class="material-icons">chevron_right</i></a></li>';
                        else if($sayfa == $toplam_sayfa) echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">chevron_right</i></a></li>';
                        if($sayfa != $toplam_sayfa) echo '<li><a class="waves-effect sayfala-buton2" href="javascript:void(0);" id="'.$toplam_sayfa.'"><i class="material-icons">last_page</i></a></li>';
                        else if($sayfa == $toplam_sayfa)  echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">last_page</i></a></li>';
                        ?>
                    </ul>
                    <?php
                }
            }
            else
            {
                ?>
                <div class='alert alert-danger mb-0'>No records found yet.</div>
                <?php
            }
        }
        else
        {
            ?>
            <div class="alert alert-danger mb-0">There was a technical problem. Please try again.</div>
            <?php
        }
    }
    else if($page_request == "invite-teacher")
    {
        if($uyerol != "admin")
        {
            echo 0;
            exit();
        }
        $teacher_name = filter_input(INPUT_POST, 'teacher_name', FILTER_SANITIZE_STRING);
        $teacher_email = filter_input(INPUT_POST, 'teacher_email', FILTER_SANITIZE_EMAIL);
        if(empty($teacher_name) || empty($teacher_email))
        {
            echo 2;
            exit();
        }
        if(strlen($teacher_name) < 3 || strlen($teacher_name) > 64)
        {
            echo 3;
            exit();
        }
        if(!filter_var($teacher_email, FILTER_VALIDATE_EMAIL))
        {
            echo 4;
            exit();
        }
        $sorguUye = $DB_con->prepare("SELECT id,name,invite_token,role FROM users WHERE email = :email");
        $sorguUye->execute(array(":email"=>$teacher_email));
        if($sorguUye->rowCount() == 0)
        {
//            $renkler = array("F44336","E91E63","9C27B0","673AB7","3F51B5","2196F3","03A9F4","00BCD4","009688","4CAF50","8BC34A","CDDC39","ffe821","FFC107","FF9800","FF5722","795548");
//            shuffle($renkler);
//            $adparcala = explode(" ", $teacher_name);
//            $emailparcala = explode("@", $teacher_email);
//            $destin = 'img/avatars/'.$emailparcala[0].'-'.$renkler[0].'.jpg';
//            copy('https://ui-avatars.com/api/?name='.reset($adparcala).'+'.end($adparcala).'&background='.$renkler[0].'&color=fff&bold=true&size=100',  $destin);
            $now = date('Y-m-d H:i:s');
            $invite_token = bin2hex(random_bytes(32));
            $sorgu = $DB_con->prepare("INSERT INTO users(name,email,schools,role,invite_token,invite_date,avatar) VALUES (:name,:email,:schools,:role,:invitetoken,:invitedate,:avatar)");
            if($sorgu->execute(array(":name"=>$teacher_name,":email"=>$teacher_email,":schools"=>$uyeokul,":role"=>"teacher",":invitetoken"=>$invite_token,":invitedate"=>$now,":avatar"=>""))) //$destin
            {
                $mail_encoded = rtrim(strtr(base64_encode($teacher_email), '+/', '-_'), '=');
                $message = '
				<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
				<html>
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
				<title>You have been invited as a teacher - Student Behavior Management</title>
				<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
				<style type="text/css">
				html { -webkit-text-size-adjust: none; -ms-text-size-adjust: none;}

					@media only screen and (min-device-width: 750px) {
						.table750 {width: 750px !important;}
					}
					@media only screen and (max-device-width: 750px), only screen and (max-width: 750px){
					  table[class="table750"] {width: 100% !important;}
					  .mob_b {width: 93% !important; max-width: 93% !important; min-width: 93% !important;}
					  .mob_b1 {width: 100% !important; max-width: 100% !important; min-width: 100% !important;}
					  .mob_left {text-align: left !important;}
					  .mob_soc {width: 50% !important; max-width: 50% !important; min-width: 50% !important;}
					  .mob_menu {width: 50% !important; max-width: 50% !important; min-width: 50% !important; box-shadow: inset -1px -1px 0 0 rgba(255, 255, 255, 0.2); }
					  .mob_center {text-align: center !important;}
					  .top_pad {height: 15px !important; max-height: 15px !important; min-height: 15px !important;}
					  .mob_pad {width: 15px !important; max-width: 15px !important; min-width: 15px !important;}
					  .mob_div {display: block !important;}
					}
				   @media only screen and (max-device-width: 550px), only screen and (max-width: 550px){
					  .mod_div {display: block !important;}
				   }
					.table750 {width: 750px;}
				</style>
				</head>
				<body style="margin: 0; padding: 0;">

				<table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f3f3f3; min-width: 350px; font-size: 1px; line-height: normal;">
					<tr>
					<td align="center" valign="top">   			
						<!--[if (gte mso 9)|(IE)]>
						 <table border="0" cellspacing="0" cellpadding="0">
						 <tr><td align="center" valign="top" width="750"><![endif]-->
						<table cellpadding="0" cellspacing="0" border="0" width="750" class="table750" style="width: 100%; max-width: 750px; min-width: 350px; background: #f3f3f3;">
							<tr>
							   <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
								<td align="center" valign="top" style="background: #ffffff;">

								  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
									 <tr>
										<td align="right" valign="top">
										   <div class="top_pad" style="height: 25px; line-height: 25px; font-size: 23px;">&nbsp;</div>
										</td>
									 </tr>
								  </table>

								  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
									 <tr>
										<td align="left" valign="top">
										   <div style="height: 39px; line-height: 39px; font-size: 37px;">&nbsp;</div>
										   <a href="#" target="_blank" style="display: block; max-width: 128px;">
											  <img src="cid:logo" alt="img" width="160" border="0" style="display: block; width: 160px;" />
										   </a>
										   <div style="height: 73px; line-height: 73px; font-size: 71px;">&nbsp;</div>
										</td>
									 </tr>
								  </table>

								  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
									 <tr>
										<td align="left" valign="top">
										   <font face="Source Sans Pro, sans-serif" color="#1a1a1a" style="font-size: 52px; line-height: 60px; font-weight: 300; letter-spacing: -1.5px;">
											  <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 52px; line-height: 60px; font-weight: 300; letter-spacing: -1.5px;">Merhaba, '.$teacher_name.'</span>
										   </font>
										   <div style="height: 33px; line-height: 33px; font-size: 31px;">&nbsp;</div>
										   <font face="Source Sans Pro, sans-serif" color="#585858" style="font-size: 24px; line-height: 32px;">
											  <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #585858; font-size: 24px; line-height: 32px;">'.$yazokulad["name"].' adlı okula öğretmen olarak davet edildiniz. Aşağıdaki linke tıklayarak sisteme kayıt olabilirsiniz!</span>
										   </font>
										   <div style="height: 33px; line-height: 33px; font-size: 31px;">&nbsp;</div>
										   <table class="mob_btn" cellpadding="0" cellspacing="0" border="0" style="background: #27cbcc; border-radius: 4px;">
											  <tr>
												 <td align="center" valign="top"> 
													<a href="http://localhost/sbm/signup-'.$mail_encoded.'-gc-'.$invite_token.'" target="_blank" style="display: block; border: 1px solid #27cbcc; border-radius: 4px; padding: 12px 23px; font-family: Source Sans Pro, Arial, Verdana, Tahoma, Geneva, sans-serif; color: #ffffff; font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">
													   <font face="Source Sans Pro, sans-serif" color="#ffffff" style="font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">
														  <span style="font-family: Source Sans Pro, Arial, Verdana, Tahoma, Geneva, sans-serif; color: #ffffff; font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">Tıkla!</span>
													   </font>
													</a>
												 </td>
											  </tr>
										   </table>
										   <div style="height: 75px; line-height: 75px; font-size: 73px;">&nbsp;</div>
										</td>
									 </tr>
								  </table>

								  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
									 <tr>
										<td align="center" valign="top">
										   <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
											  <tr>
												 <td align="center" valign="top">
													<div style="height: 34px; line-height: 34px; font-size: 32px;">&nbsp;</div>
													<font face="Source Sans Pro, sans-serif" color="#868686" style="font-size: 17px; line-height: 20px;">
													   <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #868686; font-size: 17px; line-height: 20px;">Copyright &copy; 2019 Student Behavior Management.</span>
													</font>
													<div style="height: 3px; line-height: 3px; font-size: 1px;">&nbsp;</div>
													<font face="Source Sans Pro, sans-serif" color="#1a1a1a" style="font-size: 17px; line-height: 20px;">
													   <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px;"><a href="mailto:sbm@aybarsakgun.com" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">sbm@aybarsakgun.com</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="tel:5555555555" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">+90 555 555 55 55</a></span>
													</font>
													<div style="height: 35px; line-height: 35px; font-size: 33px;">&nbsp;</div>
												 </td>
											  </tr>
										   </table>
										</td>
									 </tr>
								  </table>  

							   </td>
							   <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
							</tr>
						 </table>
						 <!--[if (gte mso 9)|(IE)]>
						 </td></tr>
						 </table><![endif]-->
					  </td>
				   </tr>
				</table>
				</body>
				</html>
				';
                $subject = "You've been invited as a teacher - Student Behavior Management";
                if(SendMail($teacher_email,$teacher_name,$message,$subject))
                {
                    echo 1;
                    exit();
                }
                else
                {
                    echo 0;
                    exit();
                }
            }
            else
            {
                echo 0;
                exit();
            }
        }
        else if($sorguUye->rowCount() == 1)
        {
            $yazUye = $sorguUye->fetch(PDO::FETCH_ASSOC);
            if($yazUye["role"] != "teacher")
            {
                echo 6;
                exit();
            }
            $mail_encoded = rtrim(strtr(base64_encode($teacher_email), '+/', '-_'), '=');
            $message = '
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
			<title>You have been invited as a teacher - Student Behavior Management</title>
			<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
			<style type="text/css">
			html { -webkit-text-size-adjust: none; -ms-text-size-adjust: none;}

				@media only screen and (min-device-width: 750px) {
					.table750 {width: 750px !important;}
				}
				@media only screen and (max-device-width: 750px), only screen and (max-width: 750px){
				  table[class="table750"] {width: 100% !important;}
				  .mob_b {width: 93% !important; max-width: 93% !important; min-width: 93% !important;}
				  .mob_b1 {width: 100% !important; max-width: 100% !important; min-width: 100% !important;}
				  .mob_left {text-align: left !important;}
				  .mob_soc {width: 50% !important; max-width: 50% !important; min-width: 50% !important;}
				  .mob_menu {width: 50% !important; max-width: 50% !important; min-width: 50% !important; box-shadow: inset -1px -1px 0 0 rgba(255, 255, 255, 0.2); }
				  .mob_center {text-align: center !important;}
				  .top_pad {height: 15px !important; max-height: 15px !important; min-height: 15px !important;}
				  .mob_pad {width: 15px !important; max-width: 15px !important; min-width: 15px !important;}
				  .mob_div {display: block !important;}
				}
			   @media only screen and (max-device-width: 550px), only screen and (max-width: 550px){
				  .mod_div {display: block !important;}
			   }
				.table750 {width: 750px;}
			</style>
			</head>
			<body style="margin: 0; padding: 0;">

			<table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f3f3f3; min-width: 350px; font-size: 1px; line-height: normal;">
				<tr>
				<td align="center" valign="top">   			
					<!--[if (gte mso 9)|(IE)]>
					 <table border="0" cellspacing="0" cellpadding="0">
					 <tr><td align="center" valign="top" width="750"><![endif]-->
					<table cellpadding="0" cellspacing="0" border="0" width="750" class="table750" style="width: 100%; max-width: 750px; min-width: 350px; background: #f3f3f3;">
						<tr>
						   <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
							<td align="center" valign="top" style="background: #ffffff;">

							  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
								 <tr>
									<td align="right" valign="top">
									   <div class="top_pad" style="height: 25px; line-height: 25px; font-size: 23px;">&nbsp;</div>
									</td>
								 </tr>
							  </table>

							  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
								 <tr>
									<td align="left" valign="top">
									   <div style="height: 39px; line-height: 39px; font-size: 37px;">&nbsp;</div>
									   <a href="#" target="_blank" style="display: block; max-width: 128px;">
										  <img src="cid:logo" alt="img" width="160" border="0" style="display: block; width: 160px;" />
									   </a>
									   <div style="height: 73px; line-height: 73px; font-size: 71px;">&nbsp;</div>
									</td>
								 </tr>
							  </table>

							  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
								 <tr>
									<td align="left" valign="top">
									   <font face="Source Sans Pro, sans-serif" color="#1a1a1a" style="font-size: 52px; line-height: 60px; font-weight: 300; letter-spacing: -1.5px;">
										  <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 52px; line-height: 60px; font-weight: 300; letter-spacing: -1.5px;">Merhaba, '.$yazUye["name"].'</span>
									   </font>
									   <div style="height: 33px; line-height: 33px; font-size: 31px;">&nbsp;</div>
									   <font face="Source Sans Pro, sans-serif" color="#585858" style="font-size: 24px; line-height: 32px;">
										  <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #585858; font-size: 24px; line-height: 32px;">'.$yazokulad["name"].' adlı okula öğretmen olarak davet edildiniz. Aşağıdaki linke tıklayarak sisteme kayıt olabilirsiniz!</span>
									   </font>
									   <div style="height: 33px; line-height: 33px; font-size: 31px;">&nbsp;</div>
									   <table class="mob_btn" cellpadding="0" cellspacing="0" border="0" style="background: #27cbcc; border-radius: 4px;">
										  <tr>
											 <td align="center" valign="top"> 
												<a href="http://localhost/sbm/signup-'.$mail_encoded.'-gc-'.$yazUye["invite_token"].'" target="_blank" style="display: block; border: 1px solid #27cbcc; border-radius: 4px; padding: 12px 23px; font-family: Source Sans Pro, Arial, Verdana, Tahoma, Geneva, sans-serif; color: #ffffff; font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">
												   <font face="Source Sans Pro, sans-serif" color="#ffffff" style="font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">
													  <span style="font-family: Source Sans Pro, Arial, Verdana, Tahoma, Geneva, sans-serif; color: #ffffff; font-size: 20px; line-height: 30px; text-decoration: none; white-space: nowrap; font-weight: 600;">Tıkla!</span>
												   </font>
												</a>
											 </td>
										  </tr>
									   </table>
									   <div style="height: 75px; line-height: 75px; font-size: 73px;">&nbsp;</div>
									</td>
								 </tr>
							  </table>

							  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
								 <tr>
									<td align="center" valign="top">
									   <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
										  <tr>
											 <td align="center" valign="top">
												<div style="height: 34px; line-height: 34px; font-size: 32px;">&nbsp;</div>
												<font face="Source Sans Pro, sans-serif" color="#868686" style="font-size: 17px; line-height: 20px;">
												   <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #868686; font-size: 17px; line-height: 20px;">Copyright &copy; 2019 Student Behavior Management.</span>
												</font>
												<div style="height: 3px; line-height: 3px; font-size: 1px;">&nbsp;</div>
												<font face="Source Sans Pro, sans-serif" color="#1a1a1a" style="font-size: 17px; line-height: 20px;">
												   <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px;"><a href="mailto:sbm@aybarsakgun.com" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">sbm@aybarsakgun.com</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="tel:5555555555" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">+90 555 555 55 55</a></span>
												</font>
												<div style="height: 35px; line-height: 35px; font-size: 33px;">&nbsp;</div>
											 </td>
										  </tr>
									   </table>
									</td>
								 </tr>
							  </table>  

						   </td>
						   <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
						</tr>
					 </table>
					 <!--[if (gte mso 9)|(IE)]>
					 </td></tr>
					 </table><![endif]-->
				  </td>
			   </tr>
			</table>
			</body>
			</html>
			';
            $subject = "You've been invited as a teacher - Student Behavior Management";
            if(SendMail($teacher_email,$teacher_name,$message,$subject))
            {
                echo 5;
                exit();
            }
            else
            {
                echo 0;
                exit();
            }
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "editstudent") {
        if ($uyerol != "admin") {
            echo 0;
            exit();
        }
        $student = filter_input(INPUT_POST, 'hidden_student_id', FILTER_VALIDATE_INT);
        if ($student === false) {
            echo 0;
            exit();
        }
        $sorguStudent = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND role = :role AND schools = :school");
        $sorguStudent->execute(array(":id" => $student, ":role" => "student", ":school" => $uyeokul));
        if ($sorguStudent->rowCount() != 1) {
            echo 0;
            exit();
        }
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        if (empty($name)) {
            echo 2;
            exit();
        }
        if (strlen($name) < 3 || strlen($name) > 64) {
            echo 3;
            exit();
        }
        $parentname = filter_input(INPUT_POST, 'parentname', FILTER_SANITIZE_STRING);
        if (!empty($parentname)) {
            if (strlen($parentname) < 3 || strlen($parentname) > 64) {
                echo 5;
                exit();
            }
        }
        $parentemail = filter_input(INPUT_POST, 'parentemail', FILTER_SANITIZE_EMAIL);
        if (!empty($parentemail)) {
            if (!filter_var($parentemail, FILTER_VALIDATE_EMAIL)) {
                echo 6;
                exit();
            }
        }
        $parentemail2 = filter_input(INPUT_POST, 'parentemail2', FILTER_SANITIZE_EMAIL);
        if (!empty($parentemail2)) {
            if (!filter_var($parentemail2, FILTER_VALIDATE_EMAIL)) {
                echo 7;
                exit();
            }
        }
        $parentphone = filter_input(INPUT_POST, 'parentphone', FILTER_SANITIZE_STRING);
        $parentphone2 = filter_input(INPUT_POST, 'parentphone2', FILTER_SANITIZE_STRING);
        $homeroom = filter_input(INPUT_POST, 'homeroom', FILTER_SANITIZE_STRING);
        if (!empty($homeroom)) {
            if (strlen($homeroom) < 3 || strlen($homeroom) > 64) {
                echo 8;
                exit();
            }
        }
        $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
        if (!empty($gender)) {
            if (strlen($gender) > 32) {
                echo 9;
                exit();
            }
        }
        $stateID = filter_input(INPUT_POST, 'stateID', FILTER_VALIDATE_INT);
        if(!empty($stateID)) {
            if ($stateID === false) {
                echo 0;
                exit();
            }
        }
        $grade = filter_input(INPUT_POST, 'grade', FILTER_VALIDATE_INT);
        if(!empty($grade)) {
            if ($grade === false) {
                echo 0;
                exit();
            }
        }
        if(!isset($_POST["class"]))
        {
            echo 4;
            exit();
        }
        if(count($_POST["class"]) == 0)
        {
            echo 4;
            exit();
        }
        $implodeclasses = implode(",", $_POST["class"]);
        $simdi = date('Y-m-d H:i:s');
        $sorgu = $DB_con->prepare("UPDATE users SET name = :name , classes = :classes , update_date = :simdi , parent_name = :parentname , parent_email = :parentemail , parent_email2 = :parentemail2 , parent_phone = :parentphone , parent_phone2 = :parentphone2 , homeroom = :homeroom , gender = :gender , stateID = :stateID , grade = :grade WHERE id = :id AND role = :role AND schools = :school");
        if($sorgu->execute(array(":name"=>$name,":classes"=>$implodeclasses,":simdi"=>$simdi,":parentname"=>$parentname,":parentemail"=>$parentemail,":parentemail2"=>$parentemail2,":parentphone"=>$parentphone,":parentphone2"=>$parentphone2,":homeroom"=>$homeroom,":gender"=>$gender,":stateID"=>$stateID,":grade"=>$grade,":id"=>$student,":role"=>"student",":school"=>$uyeokul)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "editstudent2")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $student = filter_input(INPUT_POST, 'hidden_student_id', FILTER_VALIDATE_INT);
        if($student === false)
        {
            echo 0;
            exit();
        }
        $classxx = filter_input(INPUT_POST, 'hidden_class_id', FILTER_VALIDATE_INT);
        if($classxx === false)
        {
            echo 0;
            exit();
        }
        $sorguStudent = $DB_con->prepare("SELECT id,classes FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND schools = :school AND role = :role");
        $sorguStudent->execute(array(":id"=>$student,":sid"=>$classxx,":school"=>$uyeokul,":role"=>"student"));
        if($sorguStudent->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        if(empty($name))
        {
            echo 2;
            exit();
        }
        if(strlen($name) < 3 || strlen($name) > 64)
        {
            echo 3;
            exit();
        }
        $parentname = filter_input(INPUT_POST, 'parentname', FILTER_SANITIZE_STRING);
        if(!empty($parentname)) {
            if (strlen($parentname) < 3 || strlen($parentname) > 64) {
                echo 5;
                exit();
            }
        }
        $parentemail = filter_input(INPUT_POST, 'parentemail', FILTER_SANITIZE_EMAIL);
        if(!empty($parentemail)) {
            if(!filter_var($parentemail, FILTER_VALIDATE_EMAIL))  {
                echo 6;
                exit();
            }
        }
        $parentemail2 = filter_input(INPUT_POST, 'parentemail2', FILTER_SANITIZE_EMAIL);
        if(!empty($parentemail2)) {
            if(!filter_var($parentemail2, FILTER_VALIDATE_EMAIL))  {
                echo 7;
                exit();
            }
        }
        $parentphone = filter_input(INPUT_POST, 'parentphone', FILTER_SANITIZE_STRING);
        $parentphone2 = filter_input(INPUT_POST, 'parentphone2', FILTER_SANITIZE_STRING);
        $homeroom = filter_input(INPUT_POST, 'homeroom', FILTER_SANITIZE_STRING);
        if (!empty($homeroom)) {
            if (strlen($homeroom) < 3 || strlen($homeroom) > 64) {
                echo 8;
                exit();
            }
        }
        $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
        if (!empty($gender)) {
            if (strlen($gender) > 32) {
                echo 9;
                exit();
            }
        }
        $stateID = filter_input(INPUT_POST, 'stateID', FILTER_VALIDATE_INT);
        if(!empty($stateID)) {
            if ($stateID === false) {
                echo 0;
                exit();
            }
        }
        $grade = filter_input(INPUT_POST, 'grade', FILTER_VALIDATE_INT);
        if(!empty($grade)) {
            if ($grade === false) {
                echo 0;
                exit();
            }
        }
        if(!isset($_POST["class"]))
        {
            echo 4;
            exit();
        }
        if(count($_POST["class"]) == 0)
        {
            echo 4;
            exit();
        }
        foreach($_POST["class"] as $classidsxd)
        {
            $sorguclass = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
            $sorguclass->execute(array(":id"=>$classidsxd,":oid"=>$uyevtid,":school"=>$uyeokul));
            if($sorguclass->rowCount() != 1)
            {
                echo 0;
                exit();
            }
            $inputgelensiniflar[] = $classidsxd;
        }
        $yazStudent = $sorguStudent->fetch(PDO::FETCH_ASSOC);
        $sorguogretmensiniflar = $DB_con->prepare("SELECT id FROM classes WHERE FIND_IN_SET(:uyeid,teachers) AND school = :school ORDER BY id ASC");
        $sorguogretmensiniflar->execute(array(":uyeid"=>$uyevtid,":school"=>$uyeokul));
        while($yazogretmensiniflar = $sorguogretmensiniflar->fetch(PDO::FETCH_ASSOC)){
            $ogretmeninsiniflari[] = $yazogretmensiniflar["id"];
        }
        $ogrencininsiniflari = explode(",", $yazStudent["classes"]);

        $toplamsiniflar = array_unique(array_merge($ogretmeninsiniflari,$ogrencininsiniflari));

        foreach($toplamsiniflar as $sinifss)
        {
            if(!in_array($sinifss, $ogretmeninsiniflari))
            {
                $ogrencinindigersiniflari[] = $sinifss;
            }
        }

        $implodeclasses = implode(",", $inputgelensiniflar);
        if(isset($ogrencinindigersiniflari))
        {
            $implodediger = implode(",", $ogrencinindigersiniflari);
            $sonucsinifxde = $implodediger.",".$implodeclasses;
        }
        else
        {
            $sonucsinifxde = $implodeclasses;
        }

        $simdi = date('Y-m-d H:i:s');
        $sorgu = $DB_con->prepare("UPDATE users SET name = :name , classes = :classes , update_date = :simdi , parent_name = :parentname , parent_email = :parentemail , parent_email2 = :parentemail2 , parent_phone = :parentphone , parent_phone2 = :parentphone2 , homeroom = :homeroom , gender = :gender , stateID = :stateID , grade = :grade WHERE id = :id AND role = :role AND schools = :school");
        if($sorgu->execute(array(":name"=>$name,":classes"=>$sonucsinifxde,":simdi"=>$simdi,":parentname"=>$parentname,":parentemail"=>$parentemail,":parentemail2"=>$parentemail2,":parentphone"=>$parentphone,":parentphone2"=>$parentphone2,":homeroom"=>$homeroom,":gender"=>$gender,":stateID"=>$stateID,":grade"=>$grade,":id"=>$student,":role"=>"student",":school"=>$uyeokul)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "delete-student")
    {
        if($uyerol == "student")
        {
            echo 0;
            exit();
        }
        $student = filter_input(INPUT_POST, 'student', FILTER_VALIDATE_INT);
        if($student === false)
        {
            echo 0;
            exit();
        }
        if($uyerol == "teacher")
        {
            $classxx = filter_input(INPUT_POST, 'class', FILTER_VALIDATE_INT);
            if($classxx === false)
            {
                echo 0;
                exit();
            }
            $sorguStudent = $DB_con->prepare("SELECT id,avatar FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND schools = :school AND role = :role");
            $sorguStudent->execute(array(":id"=>$student,":sid"=>$classxx,":school"=>$uyeokul,":role"=>"student"));
            if($sorguStudent->rowCount() != 1)
            {
                echo 0;
                exit();
            }
            $sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
            $sorgu2->execute(array(":id"=>$classxx,":oid"=>$uyevtid,":school"=>$uyeokul));
            if($sorgu2->rowCount() != 1)
            {
                echo 0;
                exit();
            }
            $yazogrencixde = $sorguStudent->fetch(PDO::FETCH_ASSOC);
        }
        else if($uyerol == "admin")
        {
            $sorgu = $DB_con->prepare("SELECT id,avatar FROM users WHERE id = :id AND schools = :school");
            $sorgu->execute(array(":id"=>$student,":school"=>$uyeokul));
            if($sorgu->rowCount() != 1)
            {
                echo 0;
                exit();
            }
            $yazogrencixde = $sorgu->fetch(PDO::FETCH_ASSOC);
        }
        $sorgukonusmalar = $DB_con->prepare("SELECT id FROM conversations WHERE first = :uyeid OR second = :uyeid2");
        $sorgukonusmalar->execute(array(":uyeid"=>$student,":uyeid2"=>$student));
        while($yazkonusmalar = $sorgukonusmalar->fetch(PDO::FETCH_ASSOC))
        {
            $silmesajlar = $DB_con->prepare("DELETE FROM messages WHERE conversation = :conid");
            $silmesajlar->execute(array(":conid"=>$yazkonusmalar["id"]));
        }
        $silkonusmalar = $DB_con->prepare("DELETE FROM conversations WHERE first = :uyeid OR second = :uyeid2");
        $silkonusmalar->execute(array(":uyeid"=>$student,":uyeid2"=>$student));
        $silpuanlar = $DB_con->prepare("DELETE FROM feedbacks_students WHERE student_id = :studentid");
        $silpuanlar->execute(array(":studentid"=>$student));
        $silkayitlar = $DB_con->prepare("DELETE FROM login_attempts_user WHERE member_id = :studentid");
        $silkayitlar->execute(array(":studentid"=>$student));
        $findInGroups = $DB_con->prepare("SELECT id,students FROM groups WHERE FIND_IN_SET(:student, students)");
        $findInGroups->execute(array(':student'=>$student));
        if ($findInGroups->rowCount() > 0) {
            while ($founds = $findInGroups->fetch(PDO::FETCH_ASSOC)) {
                $explodedStudents = explode(',', $founds['students']);
                if (in_array($student, $explodedStudents)) {
                    unset($explodedStudents[array_search($student, $explodedStudents)]);
                }
                $fixStudentsOfGroup = $DB_con->prepare("UPDATE groups SET students = :students WHERE id = :id");
                $fixStudentsOfGroup->execute(array(':students' => implode(',', $explodedStudents), ':id' => $founds['id']));
            }
        }
        $sorgusil = $DB_con->prepare("DELETE FROM users WHERE id = :id AND role = :role AND schools = :school");
        if($sorgusil->execute(array(":id"=>$student,":role"=>"student",":school"=>$uyeokul)))
        {
            @unlink($yazogrencixde["avatar"]);
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "revoke-point")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $point = filter_input(INPUT_POST, 'point', FILTER_VALIDATE_INT);
        if($point === false)
        {
            echo 0;
            exit();
        }
        $sorgupoint = $DB_con->prepare("SELECT id FROM feedbacks_students WHERE id = :point AND teacher = :id");
        $sorgupoint->execute(array(":point"=>$point,":id"=>$uyevtid));
        if($sorgupoint->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorgusil = $DB_con->prepare("DELETE FROM feedbacks_students WHERE id = :point AND teacher = :id");
        if($sorgusil->execute(array(":point"=>$point,":id"=>$uyevtid)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "editteacher")
    {
        if($uyerol != "admin")
        {
            echo 0;
            exit();
        }
        $student = filter_input(INPUT_POST, 'hidden_student_id', FILTER_VALIDATE_INT);
        if($student === false)
        {
            echo 0;
            exit();
        }
        $sorguStudent = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND role = :role AND schools = :school");
        $sorguStudent->execute(array(":id"=>$student,":role"=>"teacher",":school"=>$uyeokul));
        if($sorguStudent->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        if(empty($name))
        {
            echo 2;
            exit();
        }
        if(strlen($name) < 3 || strlen($name) > 64)
        {
            echo 3;
            exit();
        }
        $simdi = date('Y-m-d H:i:s');
        $sorgu = $DB_con->prepare("UPDATE users SET name = :name , update_date = :simdi WHERE id = :id AND role = :role AND schools = :school");
        if($sorgu->execute(array(":name"=>$name,":simdi"=>$simdi,":id"=>$student,":role"=>"teacher",":school"=>$uyeokul)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "editclass")
    {
        if($uyerol != "admin")
        {
            echo 0;
            exit();
        }
        $student = filter_input(INPUT_POST, 'hidden_student_id', FILTER_VALIDATE_INT);
        if($student === false)
        {
            echo 0;
            exit();
        }
        $sorguStudent = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND school = :school");
        $sorguStudent->execute(array(":id"=>$student,":school"=>$uyeokul));
        if($sorguStudent->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        if(empty($name))
        {
            echo 2;
            exit();
        }
        if(strlen($name) < 3 || strlen($name) > 64)
        {
            echo 3;
            exit();
        }
        if(!isset($_POST["teacher"]))
        {
            echo 4;
            exit();
        }
        if(count($_POST["teacher"]) == 0)
        {
            echo 4;
            exit();
        }
        $implodeclasses = implode(",", $_POST["teacher"]);
        $sorgu = $DB_con->prepare("UPDATE classes SET name = :name , teachers = :teachers WHERE id = :id AND school = :school");
        if($sorgu->execute(array(":name"=>$name,":teachers"=>$implodeclasses,":id"=>$student,":school"=>$uyeokul)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "classes")
    {
        if($uyerol == "admin")
        {
            $sorguSay = $DB_con->prepare("SELECT COUNT(id) AS say FROM classes WHERE school = :school");
            $sorguSay->execute(array(":school"=>$uyeokul));
            $yazSay = $sorguSay->fetch(PDO::FETCH_ASSOC);

            if($yazSay["say"] > 0)
            {
                $filtre_yazisi = "";
                $prefix = "";

                if(isset($_GET["arama"]))
                {
                    $gelen_aramax = filter_input(INPUT_GET, 'arama', FILTER_SANITIZE_STRING);
                    $arama_durum = preg_replace('/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı\s]/', '', $gelen_aramax);
                    if($arama_durum != "")
                    {
                        $filtre_yazisi .= $prefix . "(classes.name LIKE '%".$arama_durum."%') ";
                        $prefix = 'AND ';
                    }
                }

                if(isset($_GET["kayit"]))
                {
                    $gelen_filtre_kayit = (int)$_GET["kayit"];
                    if($gelen_filtre_kayit != "" || $gelen_filtre_kayit != "0")
                    {
                        if($gelen_filtre_kayit == 1)
                        {
                            $filtre_yazisi .= $prefix . "classes.gc_id != '' ";
                            $prefix = 'AND ';
                        }
                        else if($gelen_filtre_kayit == 2)
                        {
                            $filtre_yazisi .= $prefix . "classes.gc_id = '' ";
                            $prefix = 'AND ';
                        }
                    }
                }

                if($filtre_yazisi != "")
                {
                    $sorguyazisi = "SELECT count(classes.id) as say FROM classes WHERE $filtre_yazisi AND school = :school";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT COUNT(id) AS say FROM classes WHERE school = :school";
                }

                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute(array(":school"=>$uyeokul));
                $sayimsonucu = $sorgu->fetch(PDO::FETCH_ASSOC);

                $sayfada = 25;
                $toplam_icerik = $sayimsonucu["say"];
                $toplam_sayfa = ceil($toplam_icerik / $sayfada);
                $sayfa = isset($_GET['sayfa']) ? (int) $_GET['sayfa'] : 1;
                if($sayfa < 1) $sayfa = 1;
                if($sayfa > $toplam_sayfa) $sayfa = $toplam_sayfa;
                $limit = ($sayfa - 1) * $sayfada;

                if($filtre_yazisi != "")
                {
                    $sorguyazisi = "SELECT classes.gc_id,classes.id,classes.name,(SELECT group_concat(users.name) FROM users WHERE FIND_IN_SET(users.id, classes.teachers) AND users.role = 'teacher' AND schools = $uyeokul ) AS teachersx FROM classes WHERE $filtre_yazisi AND school = :school LIMIT :limit , :sayfada";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT classes.gc_id,classes.id,classes.name,(SELECT group_concat(users.name) FROM users WHERE FIND_IN_SET(users.id, classes.teachers) AND users.role = 'teacher' AND schools = $uyeokul ) AS teachersx FROM classes WHERE school = :school LIMIT :limit , :sayfada";
                }

                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute(array(":school"=>$uyeokul,":limit"=>abs($limit),":sayfada"=>$sayfada));

                if($sayimsonucu["say"] > 0)
                {
                    if($filtre_yazisi == "")
                    {
                        ?>
                        <small>Toplam <?=$sayimsonucu["say"]?> tane bulunan sonuçtan <?=$sorgu->rowCount()?> tanesini görüntülüyorsunuz.</small>
                        <?php
                    }
                    else
                    {
                        ?>
                        <small>Toplam <?=$yazSay["say"]?> tane bulunan sonuçtan, filtrelemenize uygun <?=$sayimsonucu["say"]?> tanesinin <?=$sorgu->rowCount()?> tanesini görüntülüyorsunuz.</small>
                        <?php
                    }
                }
                ?>
                <div class="table-responsive">
                    <table class="table table-condensed table-striped liste">
                        <thead>
                        <tr>
                            <th class="baslik_th">Name</th>
                            <th class="visible-sm visible-md visible-lg">Teachers</th>
                            <th class="visible-sm visible-md visible-lg">Register Type</th>
                            <th class="visible-sm visible-md visible-lg">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if($sayimsonucu["say"] > 0)
                        {
                            while($yaz = $sorgu->fetch(PDO::FETCH_ASSOC))
                            {
                                ?>
                                <tr>
                                    <td class="baslik_td">
                                        <span class="baslik"><?=$yaz["name"]?></span>
                                        <div class="visible-xs">
                                            <strong>Teachers:</strong> <?=$yaz["teachersx"]?><br>
                                            <strong>Register Type:</strong> <?php if($yaz["gc_id"] != NULL) { echo "Google Classroom"; } else { echo 'Manual'; } ?><br>
                                            <strong>Action: </strong> <a href="javascript:;" data-toggle="modal" data-target="#modal-class" class="label label-info sinif-duzenle" id="<?=$yaz["id"]?>">Edit</a>
                                        </div>
                                    </td>
                                    <td class="visible-sm visible-md visible-lg"><?=$yaz["teachersx"]?></td>
                                    <td class="visible-sm visible-md visible-lg"><?php if($yaz["gc_id"] != NULL) { echo "Google Classroom"; } else { echo 'Manual'; } ?></td>
                                    <td class="visible-md visible-lg"><a href="javascript:;" data-toggle="modal" data-target="#modal-class" class="label label-info sinif-duzenle" id="<?=$yaz["id"]?>">Edit</a></td>
                                </tr>
                                <?php
                            }
                        }
                        else
                        {
                            ?>
                            <tr>
                                <td colspan="5">Filtrelemenize uygun sonuç bulunamadı.</td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <?php
                if($sayimsonucu["say"] > 0)
                {
                    ?>
                    <ul class="pagination pagination-sm">
                        <?php
                        $sayfa_goster = 5;

                        $en_az_orta = ceil($sayfa_goster/2);
                        $en_fazla_orta = ($toplam_sayfa+1) - $en_az_orta;

                        $sayfa_orta = $sayfa;
                        if($sayfa_orta < $en_az_orta) $sayfa_orta = $en_az_orta;
                        if($sayfa_orta > $en_fazla_orta) $sayfa_orta = $en_fazla_orta;

                        $sol_sayfalar = round($sayfa_orta - (($sayfa_goster-1) / 2));
                        $sag_sayfalar = round((($sayfa_goster-1) / 2) + $sayfa_orta);

                        if($sol_sayfalar < 1) $sol_sayfalar = 1;
                        if($sag_sayfalar > $toplam_sayfa) $sag_sayfalar = $toplam_sayfa;

                        if($sayfa != 1) echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="1"><i class="material-icons">first_page</i></a></li>';
                        else if($sayfa == 1) echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">first_page</i></a></li>';
                        if($sayfa != 1) echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="'.($sayfa-1).'"><i class="material-icons">chevron_left</i></a></li>';
                        else if($sayfa == 1) echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">chevron_left</i></a></li>';

                        for($s = $sol_sayfalar; $s <= $sag_sayfalar; $s++) {
                            if($sayfa == $s) {
                                echo '<li class="active"><a href="javascript:void(0);">'.$s.'</a></li>';
                            } else {
                                echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="'.$s.'">'.$s.'</a></li>';
                            }
                        }

                        if($sayfa != $toplam_sayfa) echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="'.($sayfa+1).'"><i class="material-icons">chevron_right</i></a></li>';
                        else if($sayfa == $toplam_sayfa) echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">chevron_right</i></a></li>';
                        if($sayfa != $toplam_sayfa) echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="'.$toplam_sayfa.'"><i class="material-icons">last_page</i></a></li>';
                        else if($sayfa == $toplam_sayfa)  echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">last_page</i></a></li>';
                        ?>
                    </ul>
                    <?php
                }
            }
            else
            {
                ?>
                <div class='notice notice-danger'><strong>Bilgi: </strong>Henüz okula kayıtlı sınıf bulunamadı.</div>
                <?php
            }
        }
        else
        {
            ?>
            <div class="alert alert-danger mb-0">There was a technical problem. Please try again.</div>
            <?php
        }
    }
    else if($page_request == "announcements")
    {
        if($uyerol == "admin")
        {
            $sorguSay = $DB_con->prepare("SELECT COUNT(id) AS say FROM announcements WHERE school = :school");
            $sorguSay->execute(array(":school"=>$uyeokul));
            $yazSay = $sorguSay->fetch(PDO::FETCH_ASSOC);

            if($yazSay["say"] > 0)
            {
                $sorgu = $DB_con->prepare("SELECT * FROM announcements WHERE school = :school");
                $sorgu->execute(array(":school"=>$uyeokul));
                ?>
                <div class="table-responsive">
                    <table class="table table-condensed table-striped liste">
                        <thead>
                        <tr>
                            <th class="baslik_th">Date</th>
                            <th class="visible-sm visible-md visible-lg">Created by</th>
                            <th class="visible-sm visible-md visible-lg">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        while($yaz = $sorgu->fetch(PDO::FETCH_ASSOC))
                        {
                            $adminName = $DB_con->prepare("SELECT name FROM users WHERE id = :id");
                            $adminName->execute(array(":id"=>$yaz["admin"]));
                            $getAdminName = $adminName->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <tr>
                                <td class="baslik_td">
                                    <span class="baslik"><?=printDate($DB_con, $yaz["date"], $uyeokul)?></span>
                                </td>
                                <td class="visible-sm visible-md visible-lg"><?=$getAdminName["name"]?></td>
                                <td class="visible-md visible-lg"><a href="javascript:;" data-toggle="modal" data-target="#editAnnouncementModal" class="label label-info editAnnouncement" id="<?=$yaz["id"]?>">Edit</a><a href="javascript:;" class="label label-danger deleteAnnouncement" id="<?=$yaz["id"]?>">Delete</a></td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
            else
            {
                ?>
                <div class='notice notice-info'><strong>Information: </strong>The school has no announcements yet.</div>
                <?php
            }
        }
        else
        {
            ?>
            <div class="alert alert-danger mb-0">There was a technical problem. Please try again.</div>
            <?php
        }
    }
    else if($page_request == "classinfos")
    {
        if($uyerol != "admin")
        {
            echo 0;
            exit();
        }
        $ogrenci = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($ogrenci === false)
        {
            echo 0;
            exit();
        }
        $sorguogrenci = $DB_con->prepare("SELECT id,name,teachers FROM classes WHERE id = :id AND school = :school");
        $sorguogrenci->execute(array(":id"=>$ogrenci,":school"=>$uyeokul));
        if($sorguogrenci->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $yazogrenci = $sorguogrenci->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="modal-header">
            <h4 class="modal-title">Editing Class: <?=$yazogrenci["name"]?></h4>
        </div>
        <div class="modal-body">
            <form id="Edit-Class-Form">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <div class="form-line">
                        <input class="form-control" name="name" id="name" type="text" value="<?=$yazogrenci["name"]?>">
                    </div>
                </div>
                <label>Teacher(s):</label>
                <?php
                $explodeclasses = explode(",", $yazogrenci["teachers"]);
                foreach($explodeclasses as $explodedclasses)
                {
                    $sClasses[] = $explodedclasses;
                }
                implode(",", $sClasses);
                $classesQuery = $DB_con->prepare("SELECT id,name FROM users WHERE role = :role AND schools = :school");
                $classesQuery->execute(array(":role"=>"teacher",":school"=>$uyeokul));
                while($getClasses = $classesQuery->fetch(PDO::FETCH_ASSOC))
                {
                    ?>
                    <div class="form-group">
                        <input type="checkbox" name="teacher[]" value="<?=$getClasses["id"]?>" id="classIdfor<?=$getClasses["id"]?>" <?php if(in_array($getClasses["id"],$sClasses)) { echo 'checked'; } ?> class="filled-in chk-col-orange">
                        <label for="classIdfor<?=$getClasses["id"]?>"><?=$getClasses["name"]?></label>
                    </div>
                    <?php
                }
                ?>
                <input type="hidden" name="hidden_student_id" id="hidden_student_id" value="<?=$yazogrenci["id"]?>">
                <div id="Edit-Class-Result"></div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block btn-lg waves-effect Edit-Class-Button">Edit Class</button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
    else if($page_request == "teachers")
    {
        if($uyerol == "admin")
        {
            $sorguSay = $DB_con->prepare("SELECT COUNT(id) AS say FROM users WHERE role = :role AND schools = :school");
            $sorguSay->execute(array(":role"=>"teacher",":school"=>$uyeokul));
            $yazSay = $sorguSay->fetch(PDO::FETCH_ASSOC);

            if($yazSay["say"] > 0)
            {
                $filtre_yazisi = "";
                $prefix = "";
                $siralama_durum = isset($_GET['siralama']) ? (int) $_GET['siralama'] : 0;
                if($siralama_durum == 0)
                {
                    $siralama_yazisi = "ORDER BY users.id DESC";
                }
                else if($siralama_durum == 1)
                {
                    $siralama_yazisi = "ORDER BY users.id ASC";
                }
                else
                {
                    $siralama_yazisi = "";
                }

                if(isset($_GET["sinif"]))
                {
                    $gelen_filtre_sinif = (int)$_GET["sinif"];
                    if($gelen_filtre_sinif != "" || $gelen_filtre_sinif != "0")
                    {
                        $filtre_yazisi .= $prefix . "FIND_IN_SET(".$gelen_filtre_sinif.", (SELECT group_concat(classes.id) FROM classes WHERE FIND_IN_SET(users.id, classes.teachers) AND school = '".$uyeokul."')) ";
                        $prefix = 'AND ';
                    }
                }

                if(isset($_GET["arama"]))
                {
                    $gelen_aramax = filter_input(INPUT_GET, 'arama', FILTER_SANITIZE_STRING);
                    $arama_durum = preg_replace('/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı\s]/', '', $gelen_aramax);
                    if($arama_durum != "")
                    {
                        $filtre_yazisi .= $prefix . "(users.id LIKE '%".$arama_durum."%' OR users.name LIKE '%".$arama_durum."%' OR users.email LIKE '%".$arama_durum."%') ";
                        $prefix = 'AND ';
                    }
                }

                if(isset($_GET["kayit"]))
                {
                    $gelen_filtre_kayit = (int)$_GET["kayit"];
                    if($gelen_filtre_kayit != "" || $gelen_filtre_kayit != "0")
                    {
                        if($gelen_filtre_kayit == 1)
                        {
                            $filtre_yazisi .= $prefix . "users.register_date IS NOT NULL ";
                            $prefix = 'AND ';
                        }
                        else if($gelen_filtre_kayit == 2)
                        {
                            $filtre_yazisi .= $prefix . "users.register_date IS NULL ";
                            $prefix = 'AND ';
                        }
                    }
                }

                if($filtre_yazisi != "")
                {
                    $sorguyazisi = "SELECT count(users.id) as say FROM users WHERE $filtre_yazisi AND role = :role AND schools = :school";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT COUNT(id) AS say FROM users WHERE role = :role AND schools = :school";
                }

                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute(array(":role"=>"teacher",":school"=>$uyeokul));
                $sayimsonucu = $sorgu->fetch(PDO::FETCH_ASSOC);

                $sayfada = 25;
                $toplam_icerik = $sayimsonucu["say"];
                $toplam_sayfa = ceil($toplam_icerik / $sayfada);
                $sayfa = isset($_GET['sayfa']) ? (int) $_GET['sayfa'] : 1;
                if($sayfa < 1) $sayfa = 1;
                if($sayfa > $toplam_sayfa) $sayfa = $toplam_sayfa;
                $limit = ($sayfa - 1) * $sayfada;

                if($filtre_yazisi != "")
                {
                    $sorguyazisi = "SELECT users.name,users.email,users.invite_date,users.register_date,users.update_date,users.id AS teacherid,(SELECT group_concat(classes.name) FROM classes WHERE FIND_IN_SET(users.id, classes.teachers) AND school = :school) as sinifad FROM users WHERE $filtre_yazisi AND role = :role AND schools = :school2 $siralama_yazisi LIMIT :limit , :sayfada";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT users.name,users.email,users.invite_date,users.register_date,users.update_date,users.id AS teacherid,(SELECT group_concat(classes.name) FROM classes WHERE FIND_IN_SET(users.id, classes.teachers) AND school = :school) as sinifad FROM users WHERE role = :role AND schools = :school2 $siralama_yazisi LIMIT :limit , :sayfada";
                }

                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute(array(":school"=>$uyeokul,":role"=>"teacher",":school2"=>$uyeokul,":limit"=>abs($limit),":sayfada"=>$sayfada));

                if($sayimsonucu["say"] > 0)
                {
                    if($filtre_yazisi == "")
                    {
                        ?>
                        <small>Toplam <?=$sayimsonucu["say"]?> tane bulunan sonuçtan <?=$sorgu->rowCount()?> tanesini görüntülüyorsunuz.</small>
                        <?php
                    }
                    else
                    {
                        ?>
                        <small>Toplam <?=$yazSay["say"]?> tane bulunan sonuçtan, filtrelemenize uygun <?=$sayimsonucu["say"]?> tanesinin <?=$sorgu->rowCount()?> tanesini görüntülüyorsunuz.</small>
                        <?php
                    }
                }
                ?>
                <div class="table-responsive">
                    <table class="table table-condensed table-striped liste">
                        <thead>
                        <tr>
                            <th class="baslik_th">Name</th>
                            <th class="visible-sm visible-md visible-lg">E-Mail</th>
                            <th class="visible-sm visible-md visible-lg">Classes</th>
                            <th class="visible-md visible-lg">Invite Date</th>
                            <th class="visible-md visible-lg">Register Date</th>
                            <th class="visible-md visible-lg">Update Date</th>
                            <th class="visible-md visible-lg">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if($sayimsonucu["say"] > 0)
                        {
                            while($yaz = $sorgu->fetch(PDO::FETCH_ASSOC))
                            {
                                ?>
                                <tr>
                                    <td class="baslik_td">
                                        <span class="baslik"><?=$yaz["name"]?></span>
                                        <div class="visible-xs">
                                            <strong>E-mail:</strong> <?=$yaz["email"]?><br>
                                            <strong>Classes:</strong> <?=$yaz["sinifad"]?><br>
                                            <strong>Invite Date:</strong> <?=$yaz["invite_date"]?><br>
                                            <strong>Register Date:</strong> <?php if($yaz["register_date"] != "") { echo $yaz["register_date"]; } else { echo "Kayıt olmadı."; }?><br>
                                            <strong>Update Date:</strong> <?=$yaz["update_date"]?><br>
                                            <strong>Action: </strong> <a href="javascript:;" data-toggle="modal" data-target="#modal-teacher" class="label label-info ogretmen-duzenle" id="<?=$yaz["teacherid"]?>">Edit</a>
                                        </div>
                                        <div class="visible-sm">
                                            <strong>Invite Date:</strong> <?=$yaz["invite_date"]?><br>
                                            <strong>Register Date:</strong> <?php if($yaz["register_date"] != "") { echo $yaz["register_date"]; } else { echo "Kayıt olmadı."; }?><br>
                                            <strong>Update Date:</strong> <?=$yaz["update_date"]?><br>
                                            <strong>Action: </strong> <a href="javascript:;" data-toggle="modal" data-target="#modal-teacher" class="label label-info ogretmen-duzenle" id="<?=$yaz["teacherid"]?>">Edit</a>
                                        </div>
                                    </td>
                                    <td class="visible-sm visible-md visible-lg"><?=$yaz["email"]?></td>
                                    <td class="visible-sm visible-md visible-lg"><?=$yaz["sinifad"]?></td>
                                    <td class="visible-md visible-lg"><?=$yaz["invite_date"]?></td>
                                    <td class="visible-md visible-lg"><?php if($yaz["register_date"] != "") { echo $yaz["register_date"]; } else { echo "Kayıt olmadı."; }?></td>
                                    <td class="visible-md visible-lg"><?=$yaz["update_date"]?></td>
                                    <td class="visible-md visible-lg"><a href="javascript:;" data-toggle="modal" data-target="#modal-teacher" class="label label-info ogretmen-duzenle" id="<?=$yaz["teacherid"]?>">Edit</a></td>
                                </tr>
                                <?php
                            }
                        }
                        else
                        {
                            ?>
                            <tr>
                                <td colspan="8">Filtrelemenize uygun sonuç bulunamadı.</td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <?php
                if($sayimsonucu["say"] > 0)
                {
                    ?>
                    <ul class="pagination pagination-sm">
                        <?php
                        $sayfa_goster = 5;

                        $en_az_orta = ceil($sayfa_goster/2);
                        $en_fazla_orta = ($toplam_sayfa+1) - $en_az_orta;

                        $sayfa_orta = $sayfa;
                        if($sayfa_orta < $en_az_orta) $sayfa_orta = $en_az_orta;
                        if($sayfa_orta > $en_fazla_orta) $sayfa_orta = $en_fazla_orta;

                        $sol_sayfalar = round($sayfa_orta - (($sayfa_goster-1) / 2));
                        $sag_sayfalar = round((($sayfa_goster-1) / 2) + $sayfa_orta);

                        if($sol_sayfalar < 1) $sol_sayfalar = 1;
                        if($sag_sayfalar > $toplam_sayfa) $sag_sayfalar = $toplam_sayfa;

                        if($sayfa != 1) echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="1"><i class="material-icons">first_page</i></a></li>';
                        else if($sayfa == 1) echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">first_page</i></a></li>';
                        if($sayfa != 1) echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="'.($sayfa-1).'"><i class="material-icons">chevron_left</i></a></li>';
                        else if($sayfa == 1) echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">chevron_left</i></a></li>';

                        for($s = $sol_sayfalar; $s <= $sag_sayfalar; $s++) {
                            if($sayfa == $s) {
                                echo '<li class="active"><a href="javascript:void(0);">'.$s.'</a></li>';
                            } else {
                                echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="'.$s.'">'.$s.'</a></li>';
                            }
                        }

                        if($sayfa != $toplam_sayfa) echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="'.($sayfa+1).'"><i class="material-icons">chevron_right</i></a></li>';
                        else if($sayfa == $toplam_sayfa) echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">chevron_right</i></a></li>';
                        if($sayfa != $toplam_sayfa) echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="'.$toplam_sayfa.'"><i class="material-icons">last_page</i></a></li>';
                        else if($sayfa == $toplam_sayfa)  echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">last_page</i></a></li>';
                        ?>
                    </ul>
                    <?php
                }
            }
            else
            {
                ?>
                <div class='notice notice-danger'><strong>Bilgi: </strong>Henüz okula kayıtlı öğretmen bulunamadı.</div>
                <?php
            }
        }
        else
        {
            ?>
            <div class="alert alert-danger mb-0">There was a technical problem. Please try again.</div>
            <?php
        }
    }
    else if($page_request == "teacherinfos")
    {
        if($uyerol != "admin")
        {
            echo 0;
            exit();
        }
        $ogrenci = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($ogrenci === false)
        {
            echo 0;
            exit();
        }
        $sorguogrenci = $DB_con->prepare("SELECT id,name FROM users WHERE id = :id AND role = :role AND schools = :school");
        $sorguogrenci->execute(array(":id"=>$ogrenci,":role"=>"teacher",":school"=>$uyeokul));
        if($sorguogrenci->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $yazogrenci = $sorguogrenci->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="modal-header">
            <h4 class="modal-title">Editing Teacher: <?=$yazogrenci["name"]?></h4>
        </div>
        <div class="modal-body">
            <form id="Edit-Teacher-Form">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <div class="form-line">
                        <input class="form-control" name="name" id="name" type="text" value="<?=$yazogrenci["name"]?>">
                    </div>
                </div>
                <input type="hidden" name="hidden_student_id" id="hidden_student_id" value="<?=$yazogrenci["id"]?>">
                <div id="Edit-Teacher-Result"></div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block btn-lg waves-effect Edit-Teacher-Button">Edit Teacher</button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
    else if($page_request == "students")
    {
        if($uyerol == "admin")
        {
            $sorguSay = $DB_con->prepare("SELECT COUNT(id) AS say FROM users WHERE role = :role AND schools = :school");
            $sorguSay->execute(array(":role"=>"student",":school"=>$uyeokul));
            $yazSay = $sorguSay->fetch(PDO::FETCH_ASSOC);

            if($yazSay["say"] > 0)
            {
                $duzenlemesorguxd = "";
                $filtre_yazisi = "";
                $prefix = "";
                $puansorguxd = "SELECT SUM(point) FROM feedbacks_students WHERE student_id = users.id AND users.schools = :school";
                $siralama_durum = isset($_GET['siralama']) ? (int) $_GET['siralama'] : 0;
                if($siralama_durum == 0)
                {
                    $siralama_yazisi = "ORDER BY users.id DESC";
                }
                else if($siralama_durum == 1)
                {
                    $siralama_yazisi = "ORDER BY users.id ASC";
                }
                else if($siralama_durum == 2)
                {
                    $siralama_yazisi = "ORDER BY davranis_toplam DESC";
                }
                else if($siralama_durum == 3)
                {
                    $siralama_yazisi = "ORDER BY davranis_toplam ASC";
                }
                else if($siralama_durum == 4)
                {
                    $siralama_yazisi = "ORDER BY SUBSTRING_INDEX(users.name, ' ', 1) ASC";
                }
                else if($siralama_durum == 5)
                {
                    $siralama_yazisi = "ORDER BY SUBSTRING_INDEX(users.name, ' ', 1) DESC";
                }
                else if($siralama_durum == 6)
                {
                    $siralama_yazisi = "ORDER BY SUBSTRING_INDEX(users.name, ' ', -1) ASC";
                }
                else if($siralama_durum == 7)
                {
                    $siralama_yazisi = "ORDER BY SUBSTRING_INDEX(users.name, ' ', -1) DESC";
                }
                else
                {
                    $siralama_yazisi = "";
                }

                if(isset($_GET["sinif"]))
                {
                    $gelen_filtre_sinif = (int)$_GET["sinif"];
                    if($gelen_filtre_sinif != "" || $gelen_filtre_sinif != "0")
                    {
                        $duzenlemesorguxd = "AND class_id = ".$gelen_filtre_sinif;
                        $filtre_yazisi .= $prefix . "FIND_IN_SET(".$gelen_filtre_sinif.", users.classes) ";
                        $prefix = 'AND ';
                    }
                }

                if(isset($_GET["arama"]))
                {
                    $gelen_aramax = filter_input(INPUT_GET, 'arama', FILTER_SANITIZE_STRING);
                    $arama_durum = preg_replace('/[^a-zA-Z0-9ÇŞĞÜÖİçşğüöı\s]/', '', $gelen_aramax);
                    if($arama_durum != "")
                    {
                        $filtre_yazisi .= $prefix . "(users.id LIKE '%".$arama_durum."%' OR users.name LIKE '%".$arama_durum."%' OR users.email LIKE '%".$arama_durum."%') ";
                        $prefix = 'AND ';
                    }
                }

                if(isset($_GET["kayit"]))
                {
                    $gelen_filtre_kayit = (int)$_GET["kayit"];
                    if($gelen_filtre_kayit != "" || $gelen_filtre_kayit != "0")
                    {
                        if($gelen_filtre_kayit == 1)
                        {
                            $filtre_yazisi .= $prefix . "users.register_type = 1 ";
                            $prefix = 'AND ';
                        }
                        else if($gelen_filtre_kayit == 2)
                        {
                            $filtre_yazisi .= $prefix . "users.register_type = 2 ";
                            $prefix = 'AND ';
                        }
                    }
                }

                if(isset($_GET["puan"]))
                {
                    $gelen_filtre_puan = (int)$_GET["puan"];
                    if($gelen_filtre_puan != "" || $gelen_filtre_puan != "0")
                    {
                        if($gelen_filtre_puan == 1)
                        {
                            $puansorguxd = "SELECT SUM(point) FROM feedbacks_students WHERE student_id = users.id AND users.schools = :school AND type = 1";
                        }
                        else if($gelen_filtre_puan == 2)
                        {
                            $puansorguxd = "SELECT SUM(point) FROM feedbacks_students WHERE student_id = users.id AND users.schools = :school AND type = 2";
                        }
                        else if($gelen_filtre_puan == 3)
                        {
                            $puansorguxd = "SELECT SUM(point) FROM feedbacks_students WHERE student_id = users.id AND users.schools = :school AND type = 3";
                        }
                        else if($gelen_filtre_puan == 4)
                        {
                            $puansorguxd = "SELECT SUM(point) FROM feedbacks_students WHERE student_id = users.id AND users.schools = :school AND type <> 3";
                        }
                    }
                }

                if(isset($_GET["kayit2"]))
                {
                    $gelen_filtre_kayit2 = (int)$_GET["kayit2"];
                    if($gelen_filtre_kayit2 != "" || $gelen_filtre_kayit2 != "0")
                    {
                        if($gelen_filtre_kayit2 == 1)
                        {
                            $filtre_yazisi .= $prefix . "users.register_date IS NOT NULL ";
                            $prefix = 'AND ';
                        }
                        else if($gelen_filtre_kayit2 == 2)
                        {
                            $filtre_yazisi .= $prefix . "users.register_date IS NULL ";
                            $prefix = 'AND ';
                        }
                    }
                }

                if($filtre_yazisi != "")
                {
                    $sorguyazisi = "SELECT COUNT(say) as say FROM (SELECT users.id AS say FROM users INNER JOIN classes on FIND_IN_SET(classes.id, users.classes) > 0 WHERE $filtre_yazisi AND role = :role AND schools = :school GROUP BY users.id) users";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT COUNT(id) AS say FROM users WHERE role = :role AND schools = :school";
                }

                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute(array(":role"=>"student",":school"=>$uyeokul));
                $sayimsonucu = $sorgu->fetch(PDO::FETCH_ASSOC);

                $sayfada = 20;
                $toplam_icerik = $sayimsonucu["say"];
                $toplam_sayfa = ceil($toplam_icerik / $sayfada);
                $sayfa = isset($_GET['sayfa']) ? (int) $_GET['sayfa'] : 1;
                if($sayfa < 1) $sayfa = 1;
                if($sayfa > $toplam_sayfa) $sayfa = $toplam_sayfa;
                $limit = ($sayfa - 1) * $sayfada;

                if($filtre_yazisi != "")
                {
                    $sorguyazisi = "SELECT users.register_type,users.name,users.email,users.invite_date,users.register_date,users.update_date,users.id AS studentid,GROUP_CONCAT(concat(classes.name,'+_+',classes.id) ORDER BY classes.id SEPARATOR '_-_') AS sinifad,($puansorguxd $duzenlemesorguxd) as davranis_toplam FROM users INNER JOIN classes on FIND_IN_SET(classes.id, users.classes) > 0 WHERE $filtre_yazisi AND role = :role AND schools = :school2 GROUP BY users.id $siralama_yazisi LIMIT :limit , :sayfada";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT users.register_type,users.name,users.email,users.invite_date,users.register_date,users.update_date,users.id AS studentid,GROUP_CONCAT(concat(classes.name,'+_+',classes.id) ORDER BY classes.id SEPARATOR '_-_') AS sinifad,($puansorguxd) as davranis_toplam FROM users INNER JOIN classes on FIND_IN_SET(classes.id, users.classes) > 0 WHERE role = :role AND schools = :school2 GROUP BY users.id $siralama_yazisi  LIMIT :limit , :sayfada";
                }
                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute(array(":school"=>$uyeokul,":role"=>"student",":school2"=>$uyeokul,":limit"=>abs($limit),":sayfada"=>$sayfada));

                if($sayimsonucu["say"] > 0)
                {
                    if($filtre_yazisi == "")
                    {
                        ?>
                        <small>Toplam <?=$sayimsonucu["say"]?> tane bulunan sonuçtan <?=$sorgu->rowCount()?> tanesini görüntülüyorsunuz.</small>
                        <?php
                    }
                    else
                    {
                        ?>
                        <small>Toplam <?=$yazSay["say"]?> tane bulunan sonuçtan, filtrelemenize uygun <?=$sayimsonucu["say"]?> tanesinin <?=$sorgu->rowCount()?> tanesini görüntülüyorsunuz.</small>
                        <?php
                    }
                }
                ?>
                <div class="table-responsive">
                    <table class="table table-condensed table-striped liste">
                        <thead>
                        <tr>
                            <th class="baslik_th">Name</th>
                            <th class="visible-sm visible-md visible-lg">E-Mail</th>
                            <th class="visible-sm visible-md visible-lg">Classes</th>
                            <th class="visible-sm visible-md visible-lg"><?=isset($_GET["puan"]) ? ($gelen_filtre_puan != "" || $gelen_filtre_puan != "0" ? ($gelen_filtre_puan == 1 ? "Positive" : ($gelen_filtre_puan == 2 ? "Negative" : "Total" ) ) : "Total" ) : "Total"?> <?=isset($_GET["puan"]) ? ($gelen_filtre_puan != "" || $gelen_filtre_puan != "0" ? ($gelen_filtre_puan == 3 ? "Redeem" : "Behavior") : "Behavior") : "Behavior"?> Points <?=isset($_GET["puan"]) ? ($gelen_filtre_puan != "" || $gelen_filtre_puan != "0" ? ($gelen_filtre_puan == 4 ? "W/O Redeem" : "") : "") : ""?></th>
                            <th class="visible-md visible-lg">Invite Date</th>
                            <th class="visible-md visible-lg">Register Date</th>
                            <th class="visible-md visible-lg">Update Date</th>
                            <th class="visible-md visible-lg">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if($sayimsonucu["say"] > 0)
                        {
                            while($yaz = $sorgu->fetch(PDO::FETCH_ASSOC))
                            {
                                $explodeit = explode("_-_", $yaz["sinifad"]);
                                $prefixxd = "";
                                $sinifads = "";
                                foreach($explodeit as $explodedit)
                                {
                                    $explodeit2 = explode("+_+", $explodedit);
                                    $sinifads .= $prefixxd."<a href='report-".$explodeit2[1]."-".$yaz["studentid"]."'>".$explodeit2[0]."</a>";
                                    $prefixxd = ",";
                                }
                                ?>
                                <tr>
                                    <td class="baslik_td">
                                        <span class="baslik"><?=$yaz["name"]?></span>
                                        <div class="visible-xs">
                                            <strong>E-mail:</strong> <?=$yaz["email"]?><br>
                                            <strong>Classes:</strong> <?=$sinifads?><br>
                                            <strong><?=isset($_GET["puan"]) ? ($gelen_filtre_puan != "" || $gelen_filtre_puan != "0" ? ($gelen_filtre_puan == 1 ? "Positive" : ($gelen_filtre_puan == 2 ? "Negative" : "Total" ) ) : "Total" ) : "Total"?> <?=isset($_GET["puan"]) ? ($gelen_filtre_puan != "" || $gelen_filtre_puan != "0" ? ($gelen_filtre_puan == 3 ? "Redeem" : "Behavior") : "Behavior") : "Behavior"?> Points <?=isset($_GET["puan"]) ? ($gelen_filtre_puan != "" || $gelen_filtre_puan != "0" ? ($gelen_filtre_puan == 4 ? "W/O Redeem" : "") : "") : ""?>:</strong> <?=isset($_GET["puan"]) ? ($gelen_filtre_puan != "" || $gelen_filtre_puan != "0" ? ($gelen_filtre_puan == 3 ? abs($yaz["davranis_toplam"]) : $yaz["davranis_toplam"]) : $yaz["davranis_toplam"]) : $yaz["davranis_toplam"]?><br>
                                            <strong>Invite Date:</strong> <?=$yaz["invite_date"]?><br>
                                            <strong>Register Date:</strong> <?php if($yaz["register_date"] != "") { echo $yaz["register_date"]; } else { echo "Kayıt olmadı."; }?><br>
                                            <strong>Update Date:</strong> <?=$yaz["update_date"]?><br>
                                            <strong>Action: </strong> <a href="javascript:;" data-toggle="modal" data-target="#modal-student" class="label label-info ogrenci-duzenle" id="<?=$yaz["studentid"]?>">Edit</a><a href="javascript:;" class="label label-danger Delete-Student-Button" id="<?=$yaz["studentid"]?>">Delete</a>
                                        </div>
                                        <div class="visible-sm">
                                            <strong>Invite Date:</strong> <?=$yaz["invite_date"]?><br>
                                            <strong>Register Date:</strong> <?php if($yaz["register_date"] != "") { echo $yaz["register_date"]; } else { echo "Kayıt olmadı."; }?><br>
                                            <strong>Update Date:</strong> <?=$yaz["update_date"]?><br>
                                            <strong>Action: </strong> <a href="javascript:;" data-toggle="modal" data-target="#modal-student" class="label label-info ogrenci-duzenle" id="<?=$yaz["studentid"]?>">Edit</a><a href="javascript:;" class="label label-danger Delete-Student-Button" id="<?=$yaz["studentid"]?>">Delete</a>
                                        </div>
                                    </td>
                                    <td class="visible-sm visible-md visible-lg"><?=$yaz["email"]?></td>
                                    <td class="visible-sm visible-md visible-lg"><?=$sinifads?></td>
                                    <td class="visible-sm visible-md visible-lg"><?=isset($_GET["puan"]) ? ($gelen_filtre_puan != "" || $gelen_filtre_puan != "0" ? ($gelen_filtre_puan == 3 ? abs($yaz["davranis_toplam"]) : $yaz["davranis_toplam"]) : $yaz["davranis_toplam"]) : $yaz["davranis_toplam"]?></td>
                                    <td class="visible-md visible-lg"><?=$yaz["invite_date"]?></td>
                                    <td class="visible-md visible-lg"><?php if($yaz["register_date"] != "") { echo $yaz["register_date"]; } else { echo "Kayıt olmadı."; }?></td>
                                    <td class="visible-md visible-lg"><?=$yaz["update_date"]?></td>
                                    <td class="visible-md visible-lg"><a href="javascript:;" data-toggle="modal" data-target="#modal-student" class="label label-info ogrenci-duzenle" id="<?=$yaz["studentid"]?>">Edit</a><a href="javascript:;" class="label label-danger Delete-Student-Button" id="<?=$yaz["studentid"]?>">Delete</a></td>
                                </tr>
                                <?php
                            }
                        }
                        else
                        {
                            ?>
                            <tr>
                                <td colspan="9">Filtrelemenize uygun sonuç bulunamadı.</td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <?php
                if($sayimsonucu["say"] > 0)
                {
                    ?>
                    <ul class="pagination pagination-sm">
                        <?php
                        $sayfa_goster = 5;

                        $en_az_orta = ceil($sayfa_goster/2);
                        $en_fazla_orta = ($toplam_sayfa+1) - $en_az_orta;

                        $sayfa_orta = $sayfa;
                        if($sayfa_orta < $en_az_orta) $sayfa_orta = $en_az_orta;
                        if($sayfa_orta > $en_fazla_orta) $sayfa_orta = $en_fazla_orta;

                        $sol_sayfalar = round($sayfa_orta - (($sayfa_goster-1) / 2));
                        $sag_sayfalar = round((($sayfa_goster-1) / 2) + $sayfa_orta);

                        if($sol_sayfalar < 1) $sol_sayfalar = 1;
                        if($sag_sayfalar > $toplam_sayfa) $sag_sayfalar = $toplam_sayfa;

                        if($sayfa != 1) echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="1"><i class="material-icons">first_page</i></a></li>';
                        else if($sayfa == 1) echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">first_page</i></a></li>';
                        if($sayfa != 1) echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="'.($sayfa-1).'"><i class="material-icons">chevron_left</i></a></li>';
                        else if($sayfa == 1) echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">chevron_left</i></a></li>';

                        for($s = $sol_sayfalar; $s <= $sag_sayfalar; $s++) {
                            if($sayfa == $s) {
                                echo '<li class="active"><a href="javascript:void(0);">'.$s.'</a></li>';
                            } else {
                                echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="'.$s.'">'.$s.'</a></li>';
                            }
                        }

                        if($sayfa != $toplam_sayfa) echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="'.($sayfa+1).'"><i class="material-icons">chevron_right</i></a></li>';
                        else if($sayfa == $toplam_sayfa) echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">chevron_right</i></a></li>';
                        if($sayfa != $toplam_sayfa) echo '<li><a class="waves-effect sayfala-buton" href="javascript:void(0);" id="'.$toplam_sayfa.'"><i class="material-icons">last_page</i></a></li>';
                        else if($sayfa == $toplam_sayfa)  echo '<li class="disabled"><a href="javascript:void(0);"><i class="material-icons">last_page</i></a></li>';
                        ?>
                    </ul>
                    <?php
                }
            }
            else
            {
                ?>
                <div class='notice notice-danger'><strong>Bilgi: </strong>Henüz okula kayıtlı öğrenci bulunamadı.</div>
                <?php
            }
        }
        else
        {
            ?>
            <div class="alert alert-danger mb-0">There was a technical problem. Please try again.</div>
            <?php
        }
    }
    else if($page_request == "studentinfos")
    {
        if($uyerol != "admin")
        {
            echo 0;
            exit();
        }
        $ogrenci = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($ogrenci === false)
        {
            echo 0;
            exit();
        }
        $sorguogrenci = $DB_con->prepare("SELECT id,name,classes,parent_name,parent_email,parent_email2,parent_phone,parent_phone2,homeroom,gender,stateID,grade FROM users WHERE id = :id AND role = :role AND schools = :school");
        $sorguogrenci->execute(array(":id"=>$ogrenci,":role"=>"student",":school"=>$uyeokul));
        if($sorguogrenci->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $yazogrenci = $sorguogrenci->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="modal-header">
            <h4 class="modal-title">Editing Student: <?=$yazogrenci["name"]?></h4>
        </div>
        <div class="modal-body">
            <form id="Edit-Student-Form">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <div class="form-line">
                        <input class="form-control" name="name" id="name" type="text" value="<?=$yazogrenci["name"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="name">Parent Name:</label>
                    <div class="form-line">
                        <input class="form-control" name="parentname" id="parentname" type="text" value="<?=$yazogrenci["parent_name"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="name">Parent Primary E-Mail Address:</label>
                    <div class="form-line">
                        <input class="form-control" name="parentemail" id="parentemail" type="text" value="<?=$yazogrenci["parent_email"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="name">Parent Secondary E-Mail Address:</label>
                    <div class="form-line">
                        <input class="form-control" name="parentemail2" id="parentemail2" type="text" value="<?=$yazogrenci["parent_email2"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="name">Parent Primary Phone Number:</label>
                    <div class="form-line">
                        <input class="form-control" name="parentphone" id="parentphone" type="text" value="<?=$yazogrenci["parent_phone"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="name">Parent Secondary Phone Number:</label>
                    <div class="form-line">
                        <input class="form-control" name="parentphone2" id="parentphone2" type="text" value="<?=$yazogrenci["parent_phone2"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="homeroom">Homeroom:</label>
                    <div class="form-line">
                        <input class="form-control" name="homeroom" id="homeroom" type="text" value="<?=$yazogrenci["homeroom"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <div class="form-line">
                        <input class="form-control" name="gender" id="gender" type="text" value="<?=$yazogrenci["gender"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="stateID">StateID:</label>
                    <div class="form-line">
                        <input class="form-control" name="stateID" id="stateID" type="text" value="<?=$yazogrenci["stateID"]?>" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                    </div>
                </div>
                <div class="form-group">
                    <label for="grade">Grade:</label>
                    <div class="form-line">
                        <input class="form-control" name="grade" id="grade" type="text" value="<?=$yazogrenci["grade"]?>" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                    </div>
                </div>
                <label>Classes:</label>
                <?php
                $explodeclasses = explode(",", $yazogrenci["classes"]);
                foreach($explodeclasses as $explodedclasses)
                {
                    $sClasses[] = $explodedclasses;
                }
                implode(",", $sClasses);
                $classesQuery = $DB_con->prepare("SELECT classes.id,classes.name,group_concat(users.name) AS teachersname FROM classes INNER JOIN users ON FIND_IN_SET(users.id,teachers) > 0 WHERE school = :school AND role = :role GROUP BY classes.id");
                $classesQuery->execute(array(":school"=>$uyeokul,":role"=>"teacher"));
                while($getClasses = $classesQuery->fetch(PDO::FETCH_ASSOC))
                {
                    ?>
                    <div class="form-group">
                        <input type="checkbox" name="class[]" value="<?=$getClasses["id"]?>" id="classIdfor<?=$getClasses["id"]?>" <?php if(in_array($getClasses["id"],$sClasses)) { echo 'checked'; } ?> class="filled-in chk-col-orange">
                        <label for="classIdfor<?=$getClasses["id"]?>"><?=$getClasses["name"]?> <small>(Teachers: <?=$getClasses["teachersname"]?>)</small></label>
                    </div>
                    <?php
                }
                ?>
                <input type="hidden" name="hidden_student_id" id="hidden_student_id" value="<?=$yazogrenci["id"]?>">
                <div id="Edit-Student-Result"></div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block btn-lg waves-effect Edit-Student-Button">Edit Student</button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
    else if($page_request == "manage-message-templates")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        ?>
        <div class="modal-header">
            <h4 class="modal-title">Manage Message Templates</h4>
        </div>
        <div class="modal-body">
            <?php
            $queryTemplates = $DB_con->prepare("SELECT id,name,text FROM message_templates WHERE user_id = :id");
            $queryTemplates->execute(array(":id"=>$uyevtid));
            if($queryTemplates->rowCount() > 0)
            {
                while($getTemplate = $queryTemplates->fetch(PDO::FETCH_ASSOC))
                {
                    ?>
                    <form id="editMessageTemplate_<?=$getTemplate["id"]?>">
                        <div class="panel-group" id="accordionoftemplate_<?=$getTemplate["id"]?>" role="tablist" aria-multiselectable="true">
                            <div class="panel panel-col-orange">
                                <div class="panel-heading" role="tab" id="nameoftemplate_<?=$getTemplate["id"]?>">
                                    <h4 class="panel-title">
                                        <a role="button" data-toggle="collapse" data-parent="#accordionoftemplate_<?=$getTemplate["id"]?>" href="#collapseoftemplate_<?=$getTemplate["id"]?>" aria-expanded="true" aria-controls="collapseoftemplate_<?=$getTemplate["id"]?>">
                                            <i class="material-icons">textsms</i> <span id="templateName_<?=$getTemplate["id"]?>"><?=$getTemplate["name"]?></span>
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseoftemplate_<?=$getTemplate["id"]?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="nameoftemplate_<?=$getTemplate["id"]?>" aria-expanded="false">
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label for="name">Template Name:</label>
                                            <div class="form-line">
                                                <input class="form-control" name="name_<?=$getTemplate["id"]?>" id="name_<?=$getTemplate["id"]?>" type="text" value="<?=$getTemplate["name"]?>">
                                            </div>
                                        </div>
                                        <textarea id="templateContentof_<?=$getTemplate["id"]?>" name="templateContent_<?=$getTemplate["id"]?>"><?=$getTemplate["text"]?></textarea>
                                        <input type="hidden" name="template_id" id="template_id" value="<?=$getTemplate["id"]?>">
                                        <button type="button" class="btn btn-success btn-block btn-lg waves-effect m-t-5 editMessageTemplateButton" id="<?=$getTemplate["id"]?>">Edit</button>
                                        <button type="button" class="btn btn-danger btn-block btn-lg waves-effect m-t-5 deleteMessageTemplateButton" id="<?=$getTemplate["id"]?>">Delete</button>
                                        <div id="manageMessageTemplatesResult_<?=$getTemplate["id"]?>"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <script>CKEDITOR.replace('templateContentof_<?=$getTemplate["id"]?>');</script>
                    <?php
                }
            }
            else
            {
                echo '<div class="alert alert-danger"><strong>Error:</strong> No message templates found yet.</div>';
            }
            ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
    else if($page_request == "start-conversation")
    {
        if($uyerol != "student") {
            echo 0;
            exit();
        }
        $classid = filter_input(INPUT_POST, 'hidden_class_id', FILTER_VALIDATE_INT);
        if($classid === false) {
            echo 0;
            exit();
        }
        $sorgusinifid = $DB_con->prepare("SELECT id FROM users WHERE FIND_IN_SET(:sinifid, classes) AND schools = :school AND id = :id");
        $sorgusinifid->execute(array(":sinifid"=>$classid,":school"=>$uyeokul,":id"=>$uyevtid));
        if($sorgusinifid->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $teacherid = filter_input(INPUT_POST, 'hidden_teacher_id', FILTER_VALIDATE_INT);
        if($teacherid === false) {
            echo 0;
            exit();
        }
        $sorguogretmenid = $DB_con->prepare("SELECT id,name FROM classes WHERE FIND_IN_SET(:uyeid, teachers) AND school = :school AND id = :id");
        $sorguogretmenid->execute(array(":uyeid" => $teacherid, ":school" => $uyeokul, ":id" => $classid));
        if ($sorguogretmenid->rowCount() != 1) {
            echo 0;
            exit();
        }
        $studentid = filter_input(INPUT_POST, 'hidden_student_id', FILTER_VALIDATE_INT);
        if($studentid === false) {
            echo 0;
            exit();
        }
        if($studentid != $uyevtid)
        {
            echo 0;
            exit();
        }
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        $yazogretmenid = $sorguogretmenid->fetch(PDO::FETCH_ASSOC);
        if(isset($message) && !empty($message))
        {
            $result = $DB_con->query("SHOW TABLE STATUS LIKE 'conversations'");
            $data = $result->fetch(PDO::FETCH_ASSOC);
            $LastAutoIncrement = $data['Auto_increment'];

            $konusmabaslat = $DB_con->prepare("INSERT INTO conversations (first,second,class_name,class_id) VALUES (:first,:second,:classname,:classid)");
            $konusmabaslat->bindValue(':first', $studentid, PDO::PARAM_INT);
            $konusmabaslat->bindValue(':second', $teacherid, PDO::PARAM_INT);
            $konusmabaslat->bindValue(':classname', $yazogretmenid["name"]);
            $konusmabaslat->bindValue(':classid', $classid, PDO::PARAM_INT);
            $konusmabaslat->execute();

            $Mesaj = strip_tags($message);

            $tarih = date('Y-m-d H:i:s');

            $q = $DB_con->prepare("INSERT INTO messages (conversation,user_from,user_to,sent,message) VALUES (:conver,:from,:to,:tarih,:mesaj)");
            $q->bindValue(':conver', $LastAutoIncrement, PDO::PARAM_INT);
            $q->bindValue(':from', $studentid, PDO::PARAM_INT);
            $q->bindValue(':to', $teacherid, PDO::PARAM_INT);
            $q->bindValue(':tarih', $tarih);
            $q->bindValue(':mesaj', $Mesaj);
            $q->execute();

            echo json_encode(array("class_id" => "$classid" , "class_name" => seo($yazogretmenid["name"]) , "user_id" => "$teacherid" , "conversation_id" => "$LastAutoIncrement" , "sonuc" => "1"));
        }
        else
        {
            echo 2;
            exit();
        }
    }
    else if($page_request == "start-conversation2")
    {
        if($uyerol != "teacher") {
            echo 0;
            exit();
        }
        $classid = filter_input(INPUT_POST, 'hidden_class_id', FILTER_VALIDATE_INT);
        if($classid === false) {
            echo 0;
            exit();
        }
        $teacherid = filter_input(INPUT_POST, 'hidden_teacher_id', FILTER_VALIDATE_INT);
        if($teacherid === false) {
            echo 0;
            exit();
        }
        if($teacherid != $uyevtid)
        {
            echo 0;
            exit();
        }
        $sorguogretmenid = $DB_con->prepare("SELECT id,name FROM classes WHERE FIND_IN_SET(:uyeid, teachers) AND school = :school AND id = :id");
        $sorguogretmenid->execute(array(":uyeid" => $teacherid, ":school" => $uyeokul, ":id" => $classid));
        if ($sorguogretmenid->rowCount() != 1) {
            echo 0;
            exit();
        }
        $studentid = filter_input(INPUT_POST, 'hidden_student_id', FILTER_VALIDATE_INT);
        if($studentid === false) {
            echo 0;
            exit();
        }
        $sorgusinifid = $DB_con->prepare("SELECT id FROM users WHERE FIND_IN_SET(:sinifid, classes) AND schools = :school AND id = :id");
        $sorgusinifid->execute(array(":sinifid"=>$classid,":school"=>$uyeokul,":id"=>$studentid));
        if($sorgusinifid->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        $yazogretmenid = $sorguogretmenid->fetch(PDO::FETCH_ASSOC);
        if(isset($message) && !empty($message))
        {
            $result = $DB_con->query("SHOW TABLE STATUS LIKE 'conversations'");
            $data = $result->fetch(PDO::FETCH_ASSOC);
            $LastAutoIncrement = $data['Auto_increment'];

            $konusmabaslat = $DB_con->prepare("INSERT INTO conversations (first,second,class_name,class_id) VALUES (:first,:second,:classname,:classid)");
            $konusmabaslat->bindValue(':first', $teacherid, PDO::PARAM_INT);
            $konusmabaslat->bindValue(':second', $studentid, PDO::PARAM_INT);
            $konusmabaslat->bindValue(':classname', $yazogretmenid["name"]);
            $konusmabaslat->bindValue(':classid', $classid, PDO::PARAM_INT);
            $konusmabaslat->execute();

            $Mesaj = strip_tags($message);

            $tarih = date('Y-m-d H:i:s');

            $q = $DB_con->prepare("INSERT INTO messages (conversation,user_from,user_to,sent,message) VALUES (:conver,:from,:to,:tarih,:mesaj)");
            $q->bindValue(':conver', $LastAutoIncrement, PDO::PARAM_INT);
            $q->bindValue(':from', $teacherid, PDO::PARAM_INT);
            $q->bindValue(':to', $studentid, PDO::PARAM_INT);
            $q->bindValue(':tarih', $tarih);
            $q->bindValue(':mesaj', $Mesaj);
            $q->execute();

            echo json_encode(array("class_id" => "$classid" , "class_name" => seo($yazogretmenid["name"]) , "user_id" => "$studentid" , "conversation_id" => "$LastAutoIncrement" , "sonuc" => "1"));
        }
        else
        {
            echo 2;
            exit();
        }
    }
    else if($page_request == "send-message")
    {
        if(isset($_POST["message"]) && !empty($_POST["message"]) && isset($_POST["conversation_id"]) && !empty($_POST["conversation_id"]) && isset($_POST["user_form"]) && !empty($_POST["user_form"]) && isset($_POST["user_to"]) && !empty($_POST["user_to"]))
        {
            $conversation_id = base64_decode($_POST["conversation_id"]);
            $user_form = base64_decode($_POST["user_form"]);
            $user_to = base64_decode($_POST["user_to"]);
            $conversation_id = filter_var($conversation_id, FILTER_VALIDATE_INT);
            $user_form = filter_var($user_form, FILTER_VALIDATE_INT);
            $user_to = filter_var($user_to, FILTER_VALIDATE_INT);
            $Mesajx = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
            if($conversation_id === false || $user_form === false || $user_to === false)
            {
                echo "Hata!";
                exit();
            }
            $sorguKonusma = $DB_con->prepare("SELECT id FROM conversations WHERE id = :konusmaid AND (first = :benone OR second = :bentwo) AND ((first = :benx AND first_deleted = 0) OR (second = :benxx AND second_deleted = 0))");
            $sorguKonusma->execute(array(":konusmaid"=>$conversation_id,":benone"=>$uyevtid,":bentwo"=>$uyevtid,":benx"=>$uyevtid,":benxx"=>$uyevtid));
            if($sorguKonusma->rowCount() != 1)
            {
                echo "Hata!";
                exit();
            }
            $Mesaj = strip_tags($Mesajx);

            $tarih = date('Y-m-d H:i:s');

            $q = $DB_con->prepare("INSERT INTO messages (conversation,user_from,user_to,sent,message) VALUES (:conver,:from,:to,:tarih,:mesaj)");
            $q->bindValue(':conver', $conversation_id, PDO::PARAM_INT);
            $q->bindValue(':from', $user_form, PDO::PARAM_INT);
            $q->bindValue(':to', $user_to, PDO::PARAM_INT);
            $q->bindValue(':tarih', $tarih);
            $q->bindValue(':mesaj', $Mesaj);
            $q->execute();

            $sorguKonusmaDurum = $DB_con->prepare("SELECT id,first,second,first_deleted,second_deleted FROM conversations WHERE id = :konid");
            $sorguKonusmaDurum->execute(array(":konid"=>$conversation_id));
            $yazKonusmaDurum = $sorguKonusmaDurum->fetch(PDO::FETCH_ASSOC);
            if($yazKonusmaDurum["first"] == $uyevtid && $yazKonusmaDurum["second_deleted"] == 1)
            {
                $sorgum = $DB_con->prepare("UPDATE conversations SET second_deleted = 0 WHERE first = :beno AND id = :konsid");
                $sorgum->execute(array(":beno"=>$uyevtid,":konsid"=>$conversation_id));
            }
            else if($yazKonusmaDurum["second"] == $uyevtid && $yazKonusmaDurum["first_deleted"] == 1)
            {
                $sorgum = $DB_con->prepare("UPDATE conversations SET first_deleted = 0 WHERE second = :beno AND id = :konsid");
                $sorgum->execute(array(":beno"=>$uyevtid,":konsid"=>$conversation_id));
            }
        }
        else
        {
            echo "Hata!";
            exit();
        }
    }
    else if($page_request == "get-messages")
    {
        if(isset($_GET['c_id']) && !empty($_GET['c_id']))
        {
            $conversation_id = base64_decode($_GET['c_id']);
            $conversation_id = filter_var($conversation_id, FILTER_VALIDATE_INT);
            if($conversation_id === false)
            {
                $gelenData["hata"] = "Hatalı token kullandın kardeş :(";
                exit(json_encode($gelenData));
            }
            $q = $DB_con->prepare("SELECT * FROM messages WHERE conversation = :konusmaxd AND ((user_from = :ben1 AND from_deleted = 0) OR (user_to = :ben2 AND to_deleted = 0))");
            $q->bindValue(":konusmaxd", $conversation_id, PDO::PARAM_INT);
            $q->bindValue(":ben1", $uyevtid, PDO::PARAM_INT);
            $q->bindValue(":ben2", $uyevtid, PDO::PARAM_INT);
            $q->execute();
            if($q->rowCount() > 0)
            {
                while($m = $q->fetch(PDO::FETCH_ASSOC))
                {
                    $user_form = $m['user_from'];
                    $user_to = $m['user_to'];
                    $message = strip_tags($m['message']);

                    $user = $DB_con->prepare("SELECT avatar FROM users WHERE id = :userform");
                    $user->execute(array(":userform"=>$user_form));

                    $user_fetch = $user->fetch(PDO::FETCH_ASSOC);

                    $tarih = date('Y-m-d H:i:s');

                    $guncelle = $DB_con->prepare("UPDATE messages SET seen = :tarih WHERE user_to = :uyeto AND conversation = :konusmaid AND seen IS NULL");
                    $guncelle->bindValue(":tarih", $tarih);
                    $guncelle->bindValue(":uyeto", $uyevtid, PDO::PARAM_INT);
                    $guncelle->bindValue(":konusmaid", $conversation_id, PDO::PARAM_INT);
                    $guncelle->execute();

                    if($user_form == $uyevtid)
                    {
                        if(!empty($m["seen"]))
                        {
                            $gelenData["mesaj"][] = "<li class='message-right'><img src='".$user_fetch["avatar"]."'><div class='message'><p>".$message."</p><small class='col-light-green'>Seen at: ".$m["seen"]."</small></div><span>".timeConvert($m["sent"])."</span></li>";
                        }
                        else if(empty($m["seen"]))
                        {
                            $gelenData["mesaj"][] = "<li class='message-right'><img src='".$user_fetch["avatar"]."'><div class='message'><p>".$message."</p></div><span>".timeConvert($m["sent"])."</span></li>";
                        }
                    }
                    else
                    {
                        $gelenData["mesaj"][] = "<li class='message-left'><img src='".$user_fetch["avatar"]."'><div class='message'><p>".$message."</p></div><span>".timeConvert($m["sent"])."</span></li>";
                    }
                }
            }
            echo json_encode($gelenData);
        }
    }
    else if($page_request == "notifications")
    {
        if($uyerol != "admin")
        {
            $sorguMesaj = $DB_con->prepare("SELECT COUNT(messages.id) as toplamYeniMesaj,conversation,sent,first,second,class_name,class_id FROM messages INNER JOIN conversations ON conversations.id = messages.conversation WHERE user_to = :uyeto AND seen IS NULL GROUP BY conversation ORDER BY messages.id DESC");
            $sorguMesaj->bindValue(":uyeto", $uyevtid, PDO::PARAM_INT);
            $sorguMesaj->execute();
            if($sorguMesaj->rowCount() > 0)
            {
                $toplamgelenmesajsay = 0;
                while($yazMesaj = $sorguMesaj->fetch(PDO::FETCH_ASSOC))
                {
                    if($yazMesaj["first"] != $uyevtid)
                    {
                        $sorguUye = $DB_con->prepare("SELECT id,name,avatar FROM users WHERE id = :birincix");
                        $sorguUye->execute(array(":birincix"=>$yazMesaj["first"]));
                        $yazUye = $sorguUye->fetch(PDO::FETCH_ASSOC);
                        $gelenData["konusmalar"][] = '<li><a href="messages-'.seo($yazMesaj["class_name"]).'-'.$yazMesaj["class_id"].'-'.$yazMesaj["first"].'-'.$yazMesaj["conversation"].'" class=" waves-effect waves-block"><img src="'.$yazUye["avatar"].'" width="36" height="36" class="img-circle" style="vertical-align:top;"><div class="menu-info"><h4>'.$yazMesaj["toplamYeniMesaj"].' new message!</h4><p><i class="material-icons">access_time</i> '.timeConvert($yazMesaj["sent"]).'</p></div></a></li>';
                    }
                    else if($yazMesaj["second"] != $uyevtid)
                    {
                        $sorguUye = $DB_con->prepare("SELECT id,name,avatar FROM users WHERE id = :ikincix");
                        $sorguUye->execute(array(":ikincix"=>$yazMesaj["second"]));
                        $yazUye = $sorguUye->fetch(PDO::FETCH_ASSOC);
                        $gelenData["konusmalar"][] = '<li><a href="messages-'.seo($yazMesaj["class_name"]).'-'.$yazMesaj["class_id"].'-'.$yazMesaj["second"].'-'.$yazMesaj["conversation"].'" class=" waves-effect waves-block"><img src="'.$yazUye["avatar"].'" width="36" height="36" class="img-circle" style="vertical-align:top;"><div class="menu-info"><h4>'.$yazMesaj["toplamYeniMesaj"].' new message!</h4><p><i class="material-icons">access_time</i> '.timeConvert($yazMesaj["sent"]).'</p></div></a></li>';
                    }
                    $toplamgelenmesajsay += $yazMesaj["toplamYeniMesaj"];
                }
                $gelenData["hamburger"] = "1";
                $gelenData["menu"] = $toplamgelenmesajsay;
                echo json_encode($gelenData);
            }
            else
            {
                $gelenData["hamburger"] = "0";
                $gelenData["menu"] = "0";
                echo json_encode($gelenData);
            }
        }
    }
    else if($page_request == "get-message-templates")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $type = filter_input(INPUT_POST, 'type', FILTER_VALIDATE_INT);
        if($type === false) {
            echo 0;
            exit();
        }
        if($type == 0) {
            $sorgu = $DB_con->prepare("SELECT id,name FROM message_templates WHERE user_id = :userid");
            $sorgu->execute(array(":userid" => $uyevtid));
            while ($yaz = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                $gelenler[] = $yaz;
            }
            echo json_encode($gelenler);
            exit();
        }
        else if($type == 1)
        {
            $template = filter_input(INPUT_POST, 'template', FILTER_VALIDATE_INT);
            if($template === false) {
                echo 0;
                exit();
            }
            $sorgu = $DB_con->prepare("SELECT text FROM message_templates WHERE user_id = :userid AND id = :template");
            $sorgu->execute(array(":userid" => $uyevtid, ":template" => $template));
            while ($yaz = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                $gelenler[] = $yaz;
            }
            echo json_encode($gelenler);
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "create-template")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        if(empty($name))
        {
            echo 2;
            exit();
        }
        $template = filter_input(INPUT_POST, 'template', FILTER_SANITIZE_STRING);
        if(empty($template))
        {
            echo 2;
            exit();
        }
        $sorguekle = $DB_con->prepare("INSERT INTO message_templates(name,text,user_id) VALUES (:name,:text,:userid)");
        if($sorguekle->execute(array(":name"=>$name,":text"=>$template,":userid"=>$uyevtid)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "edit-template")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $template_id = filter_input(INPUT_POST, 'template_id', FILTER_VALIDATE_INT);
        if($template_id === false) {
            echo 0;
            exit();
        }
        $checkTemplate = $DB_con->prepare("SELECT id FROM message_templates WHERE id = :id AND user_id = :userid");
        $checkTemplate->execute(array(":id"=>$template_id,":userid"=>$uyevtid));
        if($checkTemplate->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $name = filter_input(INPUT_POST, 'name_'.$template_id, FILTER_SANITIZE_STRING);
        if(empty($name))
        {
            echo 2;
            exit();
        }
        $template = filter_input(INPUT_POST, 'templateContent_'.$template_id, FILTER_SANITIZE_STRING);
        if(empty($template))
        {
            echo 2;
            exit();
        }
        $editQuery = $DB_con->prepare("UPDATE message_templates SET name = :name, text = :text WHERE id = :id AND user_id = :userid");
        if($editQuery->execute(array(":name"=>$name,":text"=>$template,":id"=>$template_id,":userid"=>$uyevtid)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "delete-template")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $template_id = filter_input(INPUT_POST, 'template_id', FILTER_VALIDATE_INT);
        if($template_id === false) {
            echo 0;
            exit();
        }
        $checkTemplate = $DB_con->prepare("SELECT id FROM message_templates WHERE id = :id AND user_id = :userid");
        $checkTemplate->execute(array(":id"=>$template_id,":userid"=>$uyevtid));
        if($checkTemplate->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $deleteQuery = $DB_con->prepare("DELETE FROM message_templates WHERE id = :id AND user_id = :userid");
        if($deleteQuery->execute(array(":id"=>$template_id,":userid"=>$uyevtid)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "get-class-students")
    {
        if(!isset($_GET["classes"]))
        {
            echo 0;
            exit();
        }
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $sinifsorguyazisi = "";
        $sinifsorguyazisipre = "";
        $gelensinifads = array();
        $gelensiniflarexp = !empty($_GET["classes"]) ? explode(",", $_GET["classes"]) : array();
        if(count($gelensiniflarexp) > 0)
        {
            foreach($gelensiniflarexp as $gelensinif)
            {
                if(filter_var($gelensinif, FILTER_VALIDATE_INT))
                {
                    $sorgusinifid = $DB_con->prepare("SELECT id,name FROM classes WHERE FIND_IN_SET(:uyeid, teachers) AND id = :id AND school = :school");
                    $sorgusinifid->execute(array(":uyeid"=>$uyevtid,":id"=>$gelensinif,":school"=>$uyeokul));
                    if($sorgusinifid->rowCount() != 1)
                    {
                        echo 0;
                        exit();
                    }
                    $yazsinifad = $sorgusinifid->fetch(PDO::FETCH_ASSOC);
                    $gelensinifads[] = $yazsinifad["name"];
                    $sinifsorguyazisi .= $sinifsorguyazisipre."FIND_IN_SET(".$gelensinif.", classes) ";
                    $sinifsorguyazisipre = "OR ";
                }
                else
                {
                    echo 0;
                    exit();
                }
            }
        }
        else
        {
            ?>
            <div class="alert alert-info">Henüz herhangi bir sınıf seçmediniz.</div>
            <script>$('#sendButtonPlace').html('');</script>
            <?php
            exit();
        }
        $gelensinifsxd = array_unique($gelensinifads);
        $sorguogrenciler = $DB_con->prepare("SELECT id,name FROM users WHERE ($sinifsorguyazisi) AND (parent_email <> :bos OR parent_email2 <> :bos2) AND role = :role AND schools = :school");
        $sorguogrenciler->execute(array(":bos"=>"",":bos2"=>"",":role"=>"student",":school"=>$uyeokul));
        if($sorguogrenciler->rowCount() > 0)
        {
            echo '<div class="row">';
            while ($yazogrenciler = $sorguogrenciler->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                    <div class="form-group">
                        <input type="checkbox" id="student_<?= $yazogrenciler["id"] ?>" data-student-id="<?= $yazogrenciler["id"] ?>"
                               name="students[]" class="filled-in chk-col-orange studentCheckBox"
                               value="<?= $yazogrenciler["id"] ?>">
                        <label for="student_<?= $yazogrenciler["id"] ?>"><?= $yazogrenciler["name"] ?></label>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
            ?>
            <script>$('#sendButtonPlace').html('<div class="form-group"><button type="submit" class="btn btn-success btn-block btn-lg waves-effect Send-Messages-Button">Send</button></div>');</script>
            <?php
        }
        else
        {
            ?>
            <div class="alert alert-danger"><strong><?=implode(",", $gelensinifsxd)?></strong> <?=count($gelensinifsxd) > 1 ? 'adlı sınıflara' : 'adlı sınıfa'?> ait öğrenci bulunamadı veya hiç bir öğrencinin veli e-posta bilgisi mevcut değil.</div>
            <?php
        }
    }
    else if($page_request == "get-report")
    {
        $ogrenciid = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        if($ogrenciid === false)
        {
            echo 0;
            exit();
        }
        if(!isset($_GET["classes"]))
        {
            echo 0;
            exit();
        }
        if($uyerol == "student")
        {
            echo 0;
            exit();
        }
        $sinifsorguyazisi = "";
        $sinifsorguyazisipre = "";
        $sinifsorguyazisi2 = "";
        $sinifsorguyazisipre2 = "";
        $siralama_yazisi = "";
        $gelensiniflarexp = !empty($_GET["classes"]) ? explode(",", $_GET["classes"]) : array();
        if(count($gelensiniflarexp) > 0)
        {
            foreach($gelensiniflarexp as $gelensinif)
            {
                if(filter_var($gelensinif, FILTER_VALIDATE_INT))
                {
                    $sorgusinifid = $DB_con->prepare("SELECT id,name FROM classes WHERE id = :id AND school = :school");
                    $sorgusinifid->execute(array(":id"=>$gelensinif,":school"=>$uyeokul));
                    if($sorgusinifid->rowCount() != 1)
                    {
                        echo 0;
                        exit();
                    }
                    $sinifsorguyazisi .= $sinifsorguyazisipre."class_id = ".$gelensinif." ";
                    $sinifsorguyazisipre = "OR ";
                    $sinifsorguyazisi2 .= $sinifsorguyazisipre2."classes.id = ".$gelensinif." ";
                    $sinifsorguyazisipre2 = "OR ";
                }
                else
                {
                    echo 0;
                    exit();
                }
            }
        }
        else
        {

            echo json_encode(array('general'=>'','table'=>'<div class="alert alert-info">Henüz öğrenciye ait herhangi bir sınıf seçmediniz.</div>'));
            exit();
        }
        $timefilterstatus = isset($_GET['timefilter']) ? (int) $_GET['timefilter'] : 0;
        $suanzamanxd = date("Y-m-d");
        $buaybasixd = date("Y-m-01");
        $buaysonuxd = date("Y-m-t");
        $dunzamanxd = date('Y-m-d',strtotime("-1 days"));
        $gecenhaftaxd = date('Y-m-d',strtotime("-7 days"));
        $gecenhaftaxd2 = date('Y-m-d',strtotime("-14 days"));
        $gecenaybasixd = date('Y-m-01',strtotime("-1 month"));
        $gecenaysonuxd = date('Y-m-t',strtotime("-1 month"));
        if($timefilterstatus == 0)
        {
            $siralama_yazisi = "";
        }
        else if($timefilterstatus == 1)
        {
            $siralama_yazisi = "AND (feedbacks_students.date BETWEEN '".$suanzamanxd." 00:00:00' AND '".$suanzamanxd." 23:59:59')";
        }
        else if($timefilterstatus == 2)
        {
            $siralama_yazisi = "AND (feedbacks_students.date BETWEEN '".$dunzamanxd." 00:00:00' AND '".$dunzamanxd." 23:59:59')";
        }
        else if($timefilterstatus == 3)
        {
            $siralama_yazisi = "AND (feedbacks_students.date BETWEEN '".$gecenhaftaxd." 00:00:00' AND '".$suanzamanxd."  23:59:59')";
        }
        else if($timefilterstatus == 4)
        {
            $siralama_yazisi = "AND (feedbacks_students.date BETWEEN '".$gecenhaftaxd2." 00:00:00' AND '".$gecenhaftaxd." 23:59:59')";
        }
        else if($timefilterstatus == 5)
        {
            $siralama_yazisi = "AND (feedbacks_students.date BETWEEN '".$buaybasixd." 00:00:00' AND '".$buaysonuxd." 23:59:59')";
        }
        else if($timefilterstatus == 6)
        {
            $siralama_yazisi = "AND (feedbacks_students.date BETWEEN '".$gecenaybasixd." 00:00:00' AND '".$gecenaysonuxd." 23:59:59')";
        }
        else if($timefilterstatus == 7)
        {
            if(isset($_GET["date1"]) && isset($_GET["date2"]))
            {
                if(!empty($_GET["date1"]) && !empty($_GET["date2"]))
                {
                    $siralama_yazisi = "AND (feedbacks_students.date BETWEEN '".$_GET["date1"]." 00:00:00' AND '".$_GET["date2"]." 23:59:59')";
                }
            }
        }
        else
        {
            $siralama_yazisi = "";
        }
        $sorgupuans = $DB_con->prepare("SELECT (SELECT SUM(point) FROM feedbacks_students WHERE student_id = :studentid AND type = 1 AND ($sinifsorguyazisi) $siralama_yazisi) as pozitifpuans,(SELECT SUM(point) FROM feedbacks_students WHERE student_id = :studentid2 AND type = 2 AND ($sinifsorguyazisi) $siralama_yazisi) as negatifpuans,(SELECT SUM(point) FROM feedbacks_students WHERE student_id = :studentid3 AND ($sinifsorguyazisi) $siralama_yazisi) as toplampuans, (SELECT SUM(point) FROM feedbacks_students WHERE student_id = :studentid4 AND type <> :type AND ($sinifsorguyazisi) $siralama_yazisi) as toplampuans2");
        $sorgupuans->execute(array(":studentid"=>$ogrenciid,":studentid2"=>$ogrenciid,":studentid3"=>$ogrenciid,":studentid4"=>$ogrenciid,":type"=>3));
        $yazpuans = $sorgupuans->fetch(PDO::FETCH_ASSOC);
        $a = $yazpuans["negatifpuans"] != NULL ? abs($yazpuans["negatifpuans"]) : 0;
        $b = $yazpuans["pozitifpuans"] != NULL ? $yazpuans["pozitifpuans"] : 0;
        $sorguteachers = $DB_con->prepare("SELECT users.name,users.avatar FROM users INNER JOIN classes ON FIND_IN_SET(users.id, classes.teachers) > 0 WHERE role = :role AND school = :school AND ($sinifsorguyazisi2) GROUP BY users.id");
        $sorguteachers->execute(array(":role"=>"teacher",":school"=>$uyeokul));

        $teachersecho = "";
        while($yazteachers = $sorguteachers->fetch(PDO::FETCH_ASSOC)) {
            $teachersecho .= '
            <div class="panel-heading">
                <div class="media">
                    <div class="media-left">
                        <a href="javascript:;"><img src="'.$yazteachers["avatar"].'"></a>
                    </div>
                    <div class="media-body">
                        <h4 class="media-heading">
                            <a href="javascript:;">'.$yazteachers["name"].'</a>
                        </h4>
                        Teacher
                    </div>
                </div>
            </div>
            ';
        }

        $tableecho = "";

        $sorguHistory = $DB_con->prepare("SELECT id,name,point,type,description,teacher,date,class_id FROM feedbacks_students WHERE ($sinifsorguyazisi) AND student_id = :student $siralama_yazisi ORDER BY id DESC");
        $sorguHistory->execute(array(":student"=>$ogrenciid));
        if($sorguHistory->rowCount() > 0)
        {
            $tableecho .= '
                <table class="table table-bordered table-striped table-hover report-behavior-list dataTable nowrap" style="width:100%">
                    <thead>
                    <tr>
                        <th>Behavior Name</th>
                        <th>Type</th>
                        <th>Point</th>
                        <th>Class</th>
                        <th>Teacher</th>
                        <th>Date</th>
                        <th>Description</th>
                    </tr>
                    </thead>
                    <tbody>
                    ';
                    while($yazHistory = $sorguHistory->fetch(PDO::FETCH_ASSOC))
                    {
                        $sorguTeacher = $DB_con->prepare("SELECT name FROM users WHERE id = :id AND role = :role");
                        $sorguTeacher->execute(array(":id"=>$yazHistory["teacher"],":role"=>"teacher"));
                        $yazTeacher = $sorguTeacher->fetch(PDO::FETCH_ASSOC);
                        $sorguSinifad = $DB_con->prepare("SELECT name FROM classes WHERE id = :id");
                        $sorguSinifad->execute(array(":id"=>$yazHistory["class_id"]));
                        $yazSinifad = $sorguSinifad->fetch(PDO::FETCH_ASSOC);
                        $tableecho .= '
                        <tr>
                            <td>'.$yazHistory["name"].'</td>
                            <td>'.($yazHistory["type"] == 1 ? "<b class='col-green'>Positive</b>" : ($yazHistory["type"] == 2 ? "<b class='col-red'>Negative</b>" : ($yazHistory['type'] == 3 ? "<b class='col-blue'>Redeem</b>" : ""))).'</td>
                            <td>'.($yazHistory["type"] == 3 ? abs($yazHistory["point"]) : $yazHistory["point"]) .'</td>
                            <td>'.$yazSinifad["name"].'</td>
                            <td>'.$yazTeacher["name"].'</td>
                            <td>'.printDate($DB_con, $yazHistory["date"], $uyeokul).'</td>
                            <td>'.$yazHistory["description"].'</td>
                        </tr>
                        ';
                    }
            $tableecho .= '
                    </tbody>
                </table>
                ';
        }
        else
        {
            if($siralama_yazisi == "") {
                $tableecho = '<div class="alert alert-warning">Henüz öğrenciye verilen davranış notu bulunmamakta.</div>';
            }
            else
            {
                $tableecho = '<div class="alert alert-warning">Seçtiğiniz zaman aralığına göre öğrenciye verilen davranış notu bulunamadı.</div>';
            }
        }
        echo json_encode(array('general'=>'
            <div class="info-box hover-zoom-effect">
                <div class="icon bg-light-blue">
                    <i class="material-icons">school</i>
                </div>
                <div class="content">
                    <div class="text nowrapwithellipsis">TOTAL BEHAVIOR POINT</div>
                    <div class="number"><b class="col-light-blue">'.($yazpuans["toplampuans"] != NULL ? $yazpuans["toplampuans"] : 0).'<small class="font-15"> (W/O Redeem: '.($yazpuans["toplampuans2"] != NULL ? $yazpuans["toplampuans2"] : 0).')</small></b></div>
                </div>
            </div>
            <div class="info-box">
                <div class="icon">
                    <div class="chart chart-pie" data-chartcolor="orange">'.$b.','.$a.'</div>
                </div>
                <div class="content">
                    <div class="text nowrapwithellipsis">POSITIVE / NEGATIVE BEHAVIOR POINTS</div>
                    <div class="number"><b class="col-green">'.$b.'</b> / <b class="col-red">-'.$a.'</b> Points</div>
                </div>
            </div>
            <div class="panel panel-default panel-post">
                <div class="panel-heading">
                    <h4>Teacher(s):</h4>
                </div>
                '.$teachersecho.'
            </div>
        ','table'=>$tableecho));
    }
    else if($page_request == "send-messages")
    {
        if($uyerol != "teacher")
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        if(!isset($_POST["checkedStudents"]))
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $message_template = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        if(empty($message_template))
        {
            echo json_encode(array("sonuc"=>2));
            exit();
        }
        $ogretmensiniflari = array();
        $sorguogretmensiniflari = $DB_con->prepare("SELECT id FROM classes WHERE FIND_IN_SET(:uyeid,teachers) AND school = :school");
        $sorguogretmensiniflari->execute(array(":uyeid"=>$uyevtid,":school"=>$uyeokul));
        if($sorguogretmensiniflari->rowCount() > 0)
        {
            while($yazogretmensiniflari = $sorguogretmensiniflari->fetch(PDO::FETCH_ASSOC))
            {
               $ogretmensiniflari[] = $yazogretmensiniflari["id"];
            }
        }
        else
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $sentMessageCount = 0;
        $gelenogrencilerexp = !empty($_POST["checkedStudents"]) ? explode(",", $_POST["checkedStudents"]) : array();
        if(count($gelenogrencilerexp) > 0)
        {
            foreach($gelenogrencilerexp as $gelenogrenci)
            {
                if(filter_var($gelenogrenci, FILTER_VALIDATE_INT))
                {
                    $sorguStudent = $DB_con->prepare("SELECT id,classes,name,parent_name,parent_email,parent_email2 FROM users WHERE id = :id AND schools = :school AND role = :role");
                    $sorguStudent->execute(array(":id"=>$gelenogrenci,":school"=>$uyeokul,":role"=>"student"));
                    if($sorguStudent->rowCount() != 1)
                    {
                        continue;
                    }
                    else if($sorguStudent->rowCount() == 1)
                    {
                        $yazstudentclass = $sorguStudent->fetch(PDO::FETCH_ASSOC);
                        $explodestudentclass = explode(",", $yazstudentclass["classes"]);
                        foreach($explodestudentclass as $explodedstudentclass)
                        {
                            if(!in_array($explodedstudentclass, $ogretmensiniflari))
                            {
                                continue;
                            }
                        }
                        $studentParentMail = "";
                        if($yazstudentclass["parent_email"] != "")
                        {
                            $studentParentMail = $yazstudentclass["parent_email"];
                        }
                        else if($yazstudentclass["parent_email"] == "")
                        {
                            if($yazstudentclass["parent_email2"] != "")
                            {
                                $studentParentMail = $yazstudentclass["parent_email2"];
                            }
                            else if($yazstudentclass["parent_email2"] == "")
                            {
                                continue;
                            }
                        }
                        if($studentParentMail != "") {
                            $ogrenciadxd = $yazstudentclass['name'];
                            $generatedMessage = str_replace('{{studentName}}', $ogrenciadxd, $message_template);
                            $message = '
				<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
				<html>
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
				<title>New message - Student Behavior Management</title>
				<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
				<style type="text/css">
				html { -webkit-text-size-adjust: none; -ms-text-size-adjust: none;}

					@media only screen and (min-device-width: 750px) {
						.table750 {width: 750px !important;}
					}
					@media only screen and (max-device-width: 750px), only screen and (max-width: 750px){
					  table[class="table750"] {width: 100% !important;}
					  .mob_b {width: 93% !important; max-width: 93% !important; min-width: 93% !important;}
					  .mob_b1 {width: 100% !important; max-width: 100% !important; min-width: 100% !important;}
					  .mob_left {text-align: left !important;}
					  .mob_soc {width: 50% !important; max-width: 50% !important; min-width: 50% !important;}
					  .mob_menu {width: 50% !important; max-width: 50% !important; min-width: 50% !important; box-shadow: inset -1px -1px 0 0 rgba(255, 255, 255, 0.2); }
					  .mob_center {text-align: center !important;}
					  .top_pad {height: 15px !important; max-height: 15px !important; min-height: 15px !important;}
					  .mob_pad {width: 15px !important; max-width: 15px !important; min-width: 15px !important;}
					  .mob_div {display: block !important;}
					}
				   @media only screen and (max-device-width: 550px), only screen and (max-width: 550px){
					  .mod_div {display: block !important;}
				   }
					.table750 {width: 750px;}
				</style>
				</head>
				<body style="margin: 0; padding: 0;">

				<table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f3f3f3; min-width: 350px; font-size: 1px; line-height: normal;">
					<tr>
					<td align="center" valign="top">   			
						<!--[if (gte mso 9)|(IE)]>
						 <table border="0" cellspacing="0" cellpadding="0">
						 <tr><td align="center" valign="top" width="750"><![endif]-->
						<table cellpadding="0" cellspacing="0" border="0" width="750" class="table750" style="width: 100%; max-width: 750px; min-width: 350px; background: #f3f3f3;">
							<tr>
							   <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
								<td align="center" valign="top" style="background: #ffffff;">

								  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
									 <tr>
										<td align="right" valign="top">
										   <div class="top_pad" style="height: 25px; line-height: 25px; font-size: 23px;">&nbsp;</div>
										</td>
									 </tr>
								  </table>

								  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
									 <tr>
										<td align="left" valign="top">
										   <div style="height: 39px; line-height: 39px; font-size: 37px;">&nbsp;</div>
										   <a href="#" target="_blank" style="display: block; max-width: 128px;">
											  <img src="cid:logo" alt="img" width="160" border="0" style="display: block; width: 160px;" />
										   </a>
										   <div style="height: 73px; line-height: 73px; font-size: 71px;">&nbsp;</div>
										</td>
									 </tr>
								  </table>

								  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
									 <tr>
										<td align="left" valign="top">
										   <font face="Source Sans Pro, sans-serif" color="#585858" style="font-size: 24px; line-height: 32px;">
											  <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #585858; font-size: 24px; line-height: 32px;">'.$generatedMessage.'</span>
										   </font>
										   <div style="height: 75px; line-height: 75px; font-size: 73px;">&nbsp;</div>
										</td>
									 </tr>
								  </table>

								  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
									 <tr>
										<td align="center" valign="top">
										   <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
											  <tr>
												 <td align="center" valign="top">
													<div style="height: 34px; line-height: 34px; font-size: 32px;">&nbsp;</div>
													<font face="Source Sans Pro, sans-serif" color="#868686" style="font-size: 17px; line-height: 20px;">
													   <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #868686; font-size: 17px; line-height: 20px;">Copyright &copy; 2019 Student Behavior Management.</span>
													</font>
													<div style="height: 3px; line-height: 3px; font-size: 1px;">&nbsp;</div>
													<font face="Source Sans Pro, sans-serif" color="#1a1a1a" style="font-size: 17px; line-height: 20px;">
													   <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px;"><a href="mailto:sbm@aybarsakgun.com" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">sbm@aybarsakgun.com</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="tel:5555555555" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">+90 555 555 55 55</a></span>
													</font>
													<div style="height: 35px; line-height: 35px; font-size: 33px;">&nbsp;</div>
												 </td>
											  </tr>
										   </table>
										</td>
									 </tr>
								  </table>  

							   </td>
							   <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
							</tr>
						 </table>
						 <!--[if (gte mso 9)|(IE)]>
						 </td></tr>
						 </table><![endif]-->
					  </td>
				   </tr>
				</table>
				</body>
				</html>
				';
                            $subject = "New message - Student Behavior Management";
                            if(SendMail($studentParentMail,$yazstudentclass["parent_name"],$message,$subject))
                            {
                                $sentMessageCount++;
                            }
                        }
                        else
                        {
                            continue;
                        }
                    }
                }
                else
                {
                    continue;
                }
            }
            echo json_encode(array("sentMessageCount"=>$sentMessageCount,"sonuc"=>1));
            exit();
        }
        else
        {
            echo json_encode(array("sonuc"=>3));
            exit();
        }
    }
    else if($page_request == "create-redeem-item")
    {
        if($uyerol == "student")
        {
            echo 0;
            exit();
        }
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $point = filter_input(INPUT_POST, 'point', FILTER_VALIDATE_INT);
        if($point != null && $point === false)
        {
            echo 0;
            exit();
        }
        if(empty($name)|| empty($point) || empty($_FILES["image"]["name"]))
        {
            echo 2;
            exit();
        }
        if(strlen($name) < 3 || strlen($name) > 64)
        {
            echo 3;
            exit();
        }
        if($point < 1 || $point > 1000)
        {
            echo 4;
            exit();
        }
        $uzanti2 = strtolower(substr($_FILES["image"]["name"], strripos($_FILES["image"]["name"], '.')+1));
        $yeni_resim2 = round(microtime(true)).mt_rand().'.'.$uzanti2;
        if(!($uzanti2 == "jpg" || $uzanti2 == "jpeg" || $uzanti2 == "png"))
        {
            echo 5;
            exit();
        }
        $allowed_types = array('image/jpeg', 'image/png');
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected_type = finfo_file($fileInfo, $_FILES['image']['tmp_name']);
        if(!in_array($detected_type, $allowed_types))
        {
            finfo_close($fileInfo);
            echo 5;
            exit();
        }
        finfo_close($fileInfo);

        $targetPath = "img/redeem_items/";
        $tempFile = $_FILES['image']['tmp_name'];

        $resim  = $targetPath.$yeni_resim2 . "-RedeemItem." . $uzanti2;

        thumbOlustur1($tempFile, $resim, 400, 400);

        $simdi = date('Y-m-d H:i:s');

        $sorguekle = $DB_con->prepare("INSERT INTO redeem_items(point,name,image,user,date) VALUES (:point,:name,:image,:user,:date)");
        if($sorguekle->execute(array(":point"=>$point,":name"=>$name,":image"=>$resim,":user"=>$uyevtid,":date"=>$simdi)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "edit-redeem-item")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $item = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($item === false)
        {
            echo 0;
            exit();
        }
        $sorguitem = $DB_con->prepare("SELECT id FROM redeem_items WHERE id = :id AND user = :user");
        $sorguitem->execute(array(":id"=>$item,":user"=>$uyevtid));
        if($sorguitem->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $point = filter_input(INPUT_POST, 'point', FILTER_VALIDATE_INT);
        if($point != null && $point === false)
        {
            echo 0;
            exit();
        }
        if(empty($name)|| empty($point))
        {
            echo 2;
            exit();
        }
        if(strlen($name) < 3 || strlen($name) > 64)
        {
            echo 3;
            exit();
        }
        if($point < 1 || $point > 1000)
        {
            echo 4;
            exit();
        }
        $negativepoint = -$point;
        if(!empty($_FILES["image"]["name"]))
        {
            $uzanti2 = strtolower(substr($_FILES["image"]["name"], strripos($_FILES["image"]["name"], '.') + 1));
            $yeni_resim2 = round(microtime(true)) . mt_rand() . '.' . $uzanti2;
            if (!($uzanti2 == "jpg" || $uzanti2 == "jpeg" || $uzanti2 == "png")) {
                echo 5;
                exit();
            }
            $allowed_types = array('image/jpeg', 'image/png');
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $detected_type = finfo_file($fileInfo, $_FILES['image']['tmp_name']);
            if (!in_array($detected_type, $allowed_types)) {
                finfo_close($fileInfo);
                echo 5;
                exit();
            }
            finfo_close($fileInfo);

            $targetPath = "img/redeem_items/";
            $tempFile = $_FILES['image']['tmp_name'];

            $resim = $targetPath . $yeni_resim2 . "-RedeemItem." . $uzanti2;

            thumbOlustur1($tempFile, $resim, 400, 400);

            $sorguekle = $DB_con->prepare("UPDATE redeem_items SET point = :point , name = :name , image = :image WHERE id = :id AND user = :user");
            if($sorguekle->execute(array(":point"=>$point,":name"=>$name,":image"=>$resim,":id"=>$item,":user"=>$uyevtid)))
            {
                echo 1;
                exit();
            }
            else
            {
                echo 0;
                exit();
            }
        }
        else
        {
            $sorguekle = $DB_con->prepare("UPDATE redeem_items SET point = :point , name = :name WHERE id = :id AND user = :user");
            if($sorguekle->execute(array(":point"=>$point,":name"=>$name,":id"=>$item,":user"=>$uyevtid)))
            {
                echo 1;
                exit();
            }
            else
            {
                echo 0;
                exit();
            }
        }
    }
    else if($page_request == "delete-redeem-item")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $item = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if($item === false)
        {
            echo 0;
            exit();
        }
        $sorguitem = $DB_con->prepare("SELECT id,image FROM redeem_items WHERE id = :id AND user = :user");
        $sorguitem->execute(array(":id"=>$item,":user"=>$uyevtid));
        if($sorguitem->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $yazitem = $sorguitem->fetch(PDO::FETCH_ASSOC);
        unlink($yazitem["image"]);
        $sorguekle = $DB_con->prepare("DELETE FROM redeem_items WHERE id = :id AND user = :user");
        if($sorguekle->execute(array(":id"=>$item,":user"=>$uyevtid)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "edit-redeem-item-modal")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $item = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($item === false)
        {
            echo 0;
            exit();
        }
        $sorguitem = $DB_con->prepare("SELECT id,point,name,image FROM redeem_items WHERE id = :id AND user = :user");
        $sorguitem->execute(array(":id"=>$item,":user"=>$uyevtid));
        if($sorguitem->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $yazitem = $sorguitem->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="modal-header">
            <h4 class="modal-title">Editing Redeem Item: <?=$yazitem["name"]?></h4>
        </div>
        <div class="modal-body">
            <form id="editRedeemItemForm">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <div class="form-line">
                        <input class="form-control" name="name" id="name" type="text" value="<?=$yazitem["name"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="point">Point:</label>
                    <div class="form-line">
                        <input class="form-control" name="point" id="point" type="text" value="<?=$yazitem["point"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="name">Image:</label>
                    <div class="form-line">
                        <input class="form-control" name="image" id="image" type="file">
                    </div>
                </div>
                <div class="alert alert-info">If you want to change the image of the item, select a new image above.</div>
                <p><b>Current image:</b><br><img src="<?=$yazitem["image"]?>" width="150px" height="150px" alt="<?=$yazitem["name"]?>"></p>
                <div id="editRedeemItemResult"></div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block btn-lg waves-effect editRedeemItemButton" data-redeem-item-id="<?=$item?>">Edit This Item</button>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-danger btn-block btn-lg waves-effect deleteRedeemItemButton" data-redeem-item-id="<?=$item?>">Delete This Item</button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
    else if($page_request == "send-mail-to-parent")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $ogrenci = filter_input(INPUT_POST, 'studentid', FILTER_VALIDATE_INT);
        if($ogrenci === false)
        {
            echo 0;
            exit();
        }
        $sinif = filter_input(INPUT_POST, 'classid', FILTER_VALIDATE_INT);
        if($sinif === false)
        {
            echo 0;
            exit();
        }
        $sorgu = $DB_con->prepare("SELECT id,name,parent_name,parent_email,parent_email2 FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND schools = :school AND role = :role");
        $sorgu->execute(array(":id"=>$ogrenci,":sid"=>$sinif,":school"=>$uyeokul,":role"=>"student"));
        if($sorgu->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
        $sorgu2->execute(array(":id"=>$sinif,":oid"=>$uyevtid,":school"=>$uyeokul));
        if($sorgu2->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $message_template = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        if(empty($message_template))
        {
            echo 2;
            exit();
        }
        $yazstudentclass = $sorgu->fetch(PDO::FETCH_ASSOC);
        $studentParentMail = "";
        if($yazstudentclass["parent_email"] != "")
        {
            $studentParentMail = $yazstudentclass["parent_email"];
        }
        else if($yazstudentclass["parent_email"] == "")
        {
            if($yazstudentclass["parent_email2"] != "")
            {
                $studentParentMail = $yazstudentclass["parent_email2"];
            }
        }
        if($studentParentMail != "") {
            $ogrenciadxd = $yazstudentclass['name'];
            $generatedMessage = str_replace('{{studentName}}', $ogrenciadxd, $message_template);
            $message = '
				<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
				<html>
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
				<title>New message - Student Behavior Management</title>
				<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
				<style type="text/css">
				html { -webkit-text-size-adjust: none; -ms-text-size-adjust: none;}

					@media only screen and (min-device-width: 750px) {
						.table750 {width: 750px !important;}
					}
					@media only screen and (max-device-width: 750px), only screen and (max-width: 750px){
					  table[class="table750"] {width: 100% !important;}
					  .mob_b {width: 93% !important; max-width: 93% !important; min-width: 93% !important;}
					  .mob_b1 {width: 100% !important; max-width: 100% !important; min-width: 100% !important;}
					  .mob_left {text-align: left !important;}
					  .mob_soc {width: 50% !important; max-width: 50% !important; min-width: 50% !important;}
					  .mob_menu {width: 50% !important; max-width: 50% !important; min-width: 50% !important; box-shadow: inset -1px -1px 0 0 rgba(255, 255, 255, 0.2); }
					  .mob_center {text-align: center !important;}
					  .top_pad {height: 15px !important; max-height: 15px !important; min-height: 15px !important;}
					  .mob_pad {width: 15px !important; max-width: 15px !important; min-width: 15px !important;}
					  .mob_div {display: block !important;}
					}
				   @media only screen and (max-device-width: 550px), only screen and (max-width: 550px){
					  .mod_div {display: block !important;}
				   }
					.table750 {width: 750px;}
				</style>
				</head>
				<body style="margin: 0; padding: 0;">

				<table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f3f3f3; min-width: 350px; font-size: 1px; line-height: normal;">
					<tr>
					<td align="center" valign="top">   			
						<!--[if (gte mso 9)|(IE)]>
						 <table border="0" cellspacing="0" cellpadding="0">
						 <tr><td align="center" valign="top" width="750"><![endif]-->
						<table cellpadding="0" cellspacing="0" border="0" width="750" class="table750" style="width: 100%; max-width: 750px; min-width: 350px; background: #f3f3f3;">
							<tr>
							   <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
								<td align="center" valign="top" style="background: #ffffff;">

								  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
									 <tr>
										<td align="right" valign="top">
										   <div class="top_pad" style="height: 25px; line-height: 25px; font-size: 23px;">&nbsp;</div>
										</td>
									 </tr>
								  </table>

								  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
									 <tr>
										<td align="left" valign="top">
										   <div style="height: 39px; line-height: 39px; font-size: 37px;">&nbsp;</div>
										   <a href="#" target="_blank" style="display: block; max-width: 128px;">
											  <img src="cid:logo" alt="img" width="160" border="0" style="display: block; width: 160px;" />
										   </a>
										   <div style="height: 73px; line-height: 73px; font-size: 71px;">&nbsp;</div>
										</td>
									 </tr>
								  </table>

								  <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
									 <tr>
										<td align="left" valign="top">
										   <font face="Source Sans Pro, sans-serif" color="#585858" style="font-size: 24px; line-height: 32px;">
											  <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #585858; font-size: 24px; line-height: 32px;">'.$generatedMessage.'</span>
										   </font>
										   <div style="height: 75px; line-height: 75px; font-size: 73px;">&nbsp;</div>
										</td>
									 </tr>
								  </table>

								  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
									 <tr>
										<td align="center" valign="top">
										   <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
											  <tr>
												 <td align="center" valign="top">
													<div style="height: 34px; line-height: 34px; font-size: 32px;">&nbsp;</div>
													<font face="Source Sans Pro, sans-serif" color="#868686" style="font-size: 17px; line-height: 20px;">
													   <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #868686; font-size: 17px; line-height: 20px;">Copyright &copy; 2019 Student Behavior Management.</span>
													</font>
													<div style="height: 3px; line-height: 3px; font-size: 1px;">&nbsp;</div>
													<font face="Source Sans Pro, sans-serif" color="#1a1a1a" style="font-size: 17px; line-height: 20px;">
													   <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px;"><a href="mailto:sbm@aybarsakgun.com" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">sbm@aybarsakgun.com</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="tel:5555555555" style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 17px; line-height: 20px; text-decoration: none;">+90 555 555 55 55</a></span>
													</font>
													<div style="height: 35px; line-height: 35px; font-size: 33px;">&nbsp;</div>
												 </td>
											  </tr>
										   </table>
										</td>
									 </tr>
								  </table>  

							   </td>
							   <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
							</tr>
						 </table>
						 <!--[if (gte mso 9)|(IE)]>
						 </td></tr>
						 </table><![endif]-->
					  </td>
				   </tr>
				</table>
				</body>
				</html>
			';
            $subject = "New message - Student Behavior Management";
            if(SendMail($studentParentMail,$yazstudentclass["parent_name"],$message,$subject))
            {
                echo 1;
                exit();
            } else {
                echo 0;
                exit();
            }
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "edit-behavior")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $item = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($item === false)
        {
            echo 0;
            exit();
        }
        $sorguitem = $DB_con->prepare("SELECT id FROM feedbacks WHERE id = :id AND user = :user");
        $sorguitem->execute(array(":id"=>$item,":user"=>$uyevtid));
        if($sorguitem->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $point = filter_input(INPUT_POST, 'point', FILTER_VALIDATE_INT);
        $type = filter_input(INPUT_POST, 'type', FILTER_VALIDATE_INT);
        if($point != null && $point === false)
        {
            echo 0;
            exit();
        }
        if(empty($name)|| empty($point))
        {
            echo 2;
            exit();
        }
        if(strlen($name) < 3 || strlen($name) > 64)
        {
            echo 3;
            exit();
        }
        if($point < 1 || $point > 100)
        {
            echo 4;
            exit();
        }
        if(!($type == 1 || $type == 2)) {
            echo 2;
            exit();
        }
        $puanxd = "";
        if($type == 1)
        {
            $puanxd = $point;
        }
        if($type == 2)
        {
            $puanxd = "-".$point;
        }
        $sorguekle = $DB_con->prepare("UPDATE feedbacks SET point = :point , name = :name , type = :type WHERE id = :id AND user = :user");
        if($sorguekle->execute(array(":point"=>$puanxd,":name"=>$name,":type"=>$type,":id"=>$item,":user"=>$uyevtid)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "delete-behavior")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $item = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if($item === false)
        {
            echo 0;
            exit();
        }
        $sorguitem = $DB_con->prepare("SELECT id FROM feedbacks WHERE id = :id AND user = :user");
        $sorguitem->execute(array(":id"=>$item,":user"=>$uyevtid));
        if($sorguitem->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorguekle = $DB_con->prepare("DELETE FROM feedbacks WHERE id = :id AND user = :user");
        if($sorguekle->execute(array(":id"=>$item,":user"=>$uyevtid)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "edit-behavior-modal")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $item = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($item === false)
        {
            echo 0;
            exit();
        }
        $ogrenci = filter_input(INPUT_GET, 'sid', FILTER_VALIDATE_INT);
        if($ogrenci === false)
        {
            echo 0;
            exit();
        }
        $sinif = filter_input(INPUT_GET, 'cid', FILTER_VALIDATE_INT);
        if($sinif === false)
        {
            echo 0;
            exit();
        }
        $sorgu = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND schools = :school");
        $sorgu->execute(array(":id"=>$ogrenci,":sid"=>$sinif,":school"=>$uyeokul));
        if($sorgu->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
        $sorgu2->execute(array(":id"=>$sinif,":oid"=>$uyevtid,":school"=>$uyeokul));
        if($sorgu->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorguitem = $DB_con->prepare("SELECT id,point,name,type FROM feedbacks WHERE id = :id AND user = :user");
        $sorguitem->execute(array(":id"=>$item,":user"=>$uyevtid));
        if($sorguitem->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $yazitem = $sorguitem->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="modal-header">
            <h4 class="modal-title"><a href="javascript:;" class="col-black goStudentGeneralModal" data-student="<?=$ogrenci?>" data-class="<?=$sinif?>" data-current-modal="editBehaviorModal"><i class="material-icons">arrow_back</i></a> <div class="titleWithButton">Editing Behavior: <?=$yazitem["name"]?></div></h4>
        </div>
        <div class="modal-body">
            <form id="editBehaviorForm">
                <div class="form-group">
                    <label for="name">Behavior Name:</label>
                    <div class="form-line">
                        <input class="form-control" name="name" id="name" type="text" value="<?=$yazitem["name"]?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="point">Behavior Point(only number):</label>
                    <div class="form-line">
                        <input class="form-control" name="point" id="point" type="text" value="<?=abs($yazitem["point"])?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="type">Behavior Type:</label>
                    <select class="form-control" name="type" id="type">
                        <option value="0">Choose...</option>
                        <option value="1" <?php if($yazitem["type"] === 1) { ?>selected<?php } ?>>Positive</option>
                        <option value="2" <?php if($yazitem["type"] === 2) { ?>selected<?php } ?>>Negative</option>
                    </select>
                </div>
                <div id="editBehaviorResult"></div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block btn-lg waves-effect editBehaviorButton" data-behavior="<?=$item?>">Edit This Behavior</button>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-danger btn-block btn-lg waves-effect deleteBehaviorButton" data-behavior="<?=$item?>">Delete This Behavior</button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
    else if($page_request == "create-announcement")
    {
        if($uyerol != "admin")
        {
            echo 0;
            exit();
        }
        $template = filter_input(INPUT_POST, 'template');
        if(empty($template))
        {
            echo 2;
            exit();
        }
        $date = date('Y-m-d H:i:s');
        $sorguekle = $DB_con->prepare("INSERT INTO announcements(detail,date,admin,school) VALUES (:detail,:date,:admin,:school)");
        if($sorguekle->execute(array(":detail"=>$template,":date"=>$date,":admin"=>$uyevtid,":school"=>$uyeokul)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "edit-announcement")
    {
        if($uyerol != "admin")
        {
            echo 0;
            exit();
        }
        $announcement_id = filter_input(INPUT_POST, 'template_id', FILTER_VALIDATE_INT);
        if($announcement_id === false) {
            echo 0;
            exit();
        }
        $template = filter_input(INPUT_POST, 'templateEdit');
        if(empty($template))
        {
            echo 2;
            exit();
        }
        $editQuery = $DB_con->prepare("UPDATE announcements SET detail = :detail WHERE id = :id AND school = :school");
        if($editQuery->execute(array(":detail"=>$template,":id"=>$announcement_id,":school"=>$uyeokul)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "delete-announcement")
    {
        if($uyerol != "admin")
        {
            echo 0;
            exit();
        }
        $announcement_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if($announcement_id === false) {
            echo 0;
            exit();
        }
        $deleteQuery = $DB_con->prepare("DELETE FROM announcements WHERE id = :id AND school = :school");
        if($deleteQuery->execute(array(":id"=>$announcement_id,":school"=>$uyeokul)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "infos-announcement")
    {
        if($uyerol != "admin")
        {
            echo 0;
            exit();
        }
        $announcement_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($announcement_id === false)
        {
            echo 0;
            exit();
        }
        $queryAnnouncement = $DB_con->prepare("SELECT * FROM announcements WHERE id = :id AND school = :school");
        $queryAnnouncement->execute(array(":id"=>$announcement_id,":school"=>$uyeokul));
        if($queryAnnouncement->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $announcement = $queryAnnouncement->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="modal-header">
            <h4 class="modal-title">Editing Announcement</h4>
        </div>
        <div class="modal-body">
            <form id="editAnnouncementForm">
                <div class="form-group">
                    <textarea name="templateEdit" id="templateEdit"><?=$announcement["detail"]?></textarea>
                </div>
                <input type="hidden" name="template_id" id="template_id" value="<?=$announcement["id"]?>">
                <div id="editAnnouncementResult"></div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block btn-lg waves-effect editAnnouncementButton">Edit Announcement</button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
    else if($page_request == "edit-profile")
    {
        if($uyerol == "student")
        {
            echo json_encode(array("sonuc"=>0));
            exit();
        }
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        if(empty($name))
        {
            echo json_encode(array("sonuc"=>2));
            exit();
        }
        if(strlen($name) < 3 || strlen($name) > 64)
        {
            echo json_encode(array("sonuc"=>3));
            exit();
        }
        $simdi = date('Y-m-d H:i:s');
        if(isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
            $uzanti2 = strtolower(substr($_FILES["image"]["name"], strripos($_FILES["image"]["name"], '.')+1));
            $yeni_resim2 = round(microtime(true)).mt_rand().'.'.$uzanti2;
            if(!($uzanti2 == "jpg" || $uzanti2 == "jpeg" || $uzanti2 == "png"))
            {
                echo json_encode(array("sonuc"=>4));
                exit();
            }
            $allowed_types = array('image/jpeg', 'image/png');
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $detected_type = finfo_file($fileInfo, $_FILES['image']['tmp_name']);
            if(!in_array($detected_type, $allowed_types))
            {
                finfo_close($fileInfo);
                echo json_encode(array("sonuc"=>4));
                exit();
            }
            finfo_close($fileInfo);

            $targetPath = "img/avatars/";
            $tempFile = $_FILES['image']['tmp_name'];

            $resim  = $targetPath.$yeni_resim2 . "-PP." . $uzanti2;

            thumbOlustur1($tempFile, $resim, 100, 100);

            $sorguekle = $DB_con->prepare("UPDATE users SET name = :name , avatar = :avatar , update_date = :updatedate WHERE id = :id");
            if($sorguekle->execute(array(":name"=>$name,":avatar"=>$resim,":updatedate"=>$simdi,":id"=>$uyevtid)))
            {
                echo json_encode(array("sonuc"=>1,"photo"=>$resim,"name"=>$name));
                exit();
            }
            else
            {
                echo json_encode(array("sonuc"=>0));
                exit();
            }
        } else {
            $sorguekle = $DB_con->prepare("UPDATE users SET name = :name , update_date = :updatedate WHERE id = :id");
            if($sorguekle->execute(array(":name"=>$name,":updatedate"=>$simdi,":id"=>$uyevtid)))
            {
                echo json_encode(array("sonuc"=>5,"name"=>$name));
                exit();
            }
            else
            {
                echo json_encode(array("sonuc"=>0));
                exit();
            }
        }
    }
    else if($page_request == "edit-school")
    {
        if($uyerol != "admin")
        {
            echo 0;
            exit();
        }
        $sorguSchool = $DB_con->prepare("SELECT id FROM schools WHERE id = :id");
        $sorguSchool->execute(array(":id"=>$uyeokul));
        if($sorguSchool->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorguSchool2 = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND schools = :school");
        $sorguSchool2->execute(array(":id"=>$uyevtid,":school"=>$uyeokul));
        if($sorguSchool2->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $school_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        if(empty($school_name))
        {
            echo 2;
            exit();
        }
        if(strlen($school_name) < 3 || strlen($school_name) > 64)
        {
            echo 3;
            exit();
        }
        $date_type = filter_input(INPUT_POST, 'date_type', FILTER_VALIDATE_INT);
        if($date_type === false)
        {
            echo 0;
            exit();
        }
        if($date_type < 1 || $date_type > 5)
        {
            echo 0;
            exit();
        }
        $sorgu = $DB_con->prepare("UPDATE schools SET name = :name , date_type = :datetype WHERE id = :id");
        if($sorgu->execute(array(":name"=>$school_name,":datetype"=>$date_type,":id"=>$uyeokul)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "edit-group-modal")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $class = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($class === false)
        {
            echo 0;
            exit();
        }
        $group = filter_input(INPUT_GET, 'group', FILTER_VALIDATE_INT);
        if($group === false)
        {
            echo 0;
            exit();
        }
        $sorgu2 = $DB_con->prepare("SELECT id,student_show,point_show,points_by_time FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
        $sorgu2->execute(array(":id"=>$class,":oid"=>$uyevtid,":school"=>$uyeokul));
        if($sorgu2->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $getGroups = $DB_con->prepare("SELECT id,name,students FROM groups WHERE id = :group AND class = :id AND school = :school");
        $getGroups->execute(array(":group"=>$group,":id"=>$class,":school"=>$uyeokul));
        if($getGroups->rowCount() == 0)
        {
            echo 0;
            exit();
        }
        $fetchGroup = $getGroups->fetch(PDO::FETCH_ASSOC);
        $yazsinifid = $sorgu2->fetch(PDO::FETCH_ASSOC);
        $pointsByTimeQuery = '';
        if ($yazsinifid['points_by_time'] == 2) {
            $pointsByTimeQuery = 'AND date(date) = CURDATE()';
        } else if ($yazsinifid['points_by_time'] == 3) {
            $pointsByTimeQuery = 'AND YEARWEEK(`date`, 1) = YEARWEEK(CURDATE(), 1)';
        } else if ($yazsinifid['points_by_time'] == 4) {
            $pointsByTimeQuery = 'AND MONTH(date) = MONTH(CURRENT_DATE())';
        }
        ?>
        <div class="modal-header">
            <h4 class="modal-title">Edit Group</h4>
        </div>
        <div class="modal-body">
            <form id="editGroupForm" data-class="<?= $class ?>" data-group="<?=$group?>">
                <div class="form-group">*
                    <label for="name">Name:</label>
                    <div class="form-line">
                        <input class="form-control" name="name" id="name" type="text" value="<?=$fetchGroup['name']?>">
                    </div>
                </div>
                <label>Students to be added to the group:</label>
                <div class="row">
                    <?php
                    $availableStudentsQuery = $DB_con->prepare("SELECT id,name,avatar,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx AND type = 1 $pointsByTimeQuery) as positiveBehaviorPoints,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx2 AND type = 2 $pointsByTimeQuery) as negativeBehaviorPoints, (SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx3 AND type = 3 $pointsByTimeQuery) as redeemedPoints,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx4 $pointsByTimeQuery) as totalBehaviorPoints FROM users WHERE FIND_IN_SET(:class2, classes) AND role = :role AND schools = :school AND NOT FIND_IN_SET(id, (SELECT IFNULL(GROUP_CONCAT(students), '') FROM groups WHERE class = :class3 AND school = :school2))");
                    $availableStudentsQuery->execute(array(":classx"=>$class,":classx2"=>$class,":classx3"=>$class,":classx4"=>$class,":class2"=>$class,":role"=>"student",":school"=>$uyeokul,":class3"=>$class,":school2"=>$uyeokul));
                    if ($availableStudentsQuery->rowCount() > 0) {
                        while ($getStudents = $availableStudentsQuery->fetch(PDO::FETCH_ASSOC)) {
                            if($yazsinifid["student_show"] == 2) {
                                $explodestudentname = explode(" ", $getStudents["name"]);
                                array_pop($explodestudentname);
                                $namexdxd = implode(" ", $explodestudentname);
                            } else {
                                $namexdxd = $getStudents["name"];
                            }
                            ?>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <a href="javascript:;" id="selectStudent" data-student="<?= $getStudents["id"] ?>">
                                    <div class="panel panel-default panel-post groupStudents">
                                        <div class="panel-heading">
                                            <div class="media">
                                                <div class="media-left">
                                                    <img src="<?= $getStudents["avatar"] ?>" class="studentAvatar">
                                                </div>
                                                <div class="media-body">
                                                    <h4 class="media-heading"><?= $namexdxd ?></h4>
                                                    <?php if($yazsinifid["point_show"] == 1) { ?><span class="badge bg-green"><?= empty($getStudents["positiveBehaviorPoints"]) ? "0" : $getStudents["positiveBehaviorPoints"] ?></span><?php } ?>
                                                    <?php if($yazsinifid["point_show"] == 1) { ?><span class="badge bg-red"><?= empty($getStudents["negativeBehaviorPoints"]) ? "0" : $getStudents["negativeBehaviorPoints"] ?></span><?php } ?>
                                                    <?php if($yazsinifid["point_show"] == 1) { ?><span class="badge bg-orange"><?= empty($getStudents["redeemedPoints"]) ? "0" : $getStudents["redeemedPoints"] ?></span><?php } ?>
                                                    <?php if($yazsinifid["point_show"] != 3) { ?><span class="badge bg-blue"><?= empty($getStudents["totalBehaviorPoints"]) ? "0" : $getStudents["totalBehaviorPoints"] ?></span><?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'><div class='alert alert-danger'><strong>Error:</strong> All students belonging to this class are members of any group.</div></div>";
                    }
                    ?>
                </div>
                <label>Students to be removed from the group:</label>
                <div class="row">
                    <?php
                    $availableStudentsQuery = $DB_con->prepare("SELECT id,name,avatar,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx AND type = 1 $pointsByTimeQuery) as positiveBehaviorPoints,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx2 AND type = 2 $pointsByTimeQuery) as negativeBehaviorPoints, (SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx3 AND type = 3 $pointsByTimeQuery) as redeemedPoints,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx4 $pointsByTimeQuery) as totalBehaviorPoints FROM users WHERE FIND_IN_SET(:class2, classes) AND role = :role AND schools = :school AND FIND_IN_SET(id, (SELECT IFNULL(GROUP_CONCAT(students), '') FROM groups WHERE id = :group AND class = :class3 AND school = :school2))");
                    $availableStudentsQuery->execute(array(":classx"=>$class,":classx2"=>$class,":classx3"=>$class,":classx4"=>$class,":class2"=>$class,":role"=>"student",":school"=>$uyeokul,":group"=>$group,":class3"=>$class,":school2"=>$uyeokul));
                    if ($availableStudentsQuery->rowCount() > 0) {
                        while ($getStudents = $availableStudentsQuery->fetch(PDO::FETCH_ASSOC)) {
                            if($yazsinifid["student_show"] == 2) {
                                $explodestudentname = explode(" ", $getStudents["name"]);
                                array_pop($explodestudentname);
                                $namexdxd = implode(" ", $explodestudentname);
                            } else {
                                $namexdxd = $getStudents["name"];
                            }
                            ?>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <a href="javascript:;" id="selectStudent2" data-student="<?= $getStudents["id"] ?>">
                                    <div class="panel panel-default panel-post groupStudents">
                                        <div class="panel-heading">
                                            <div class="media">
                                                <div class="media-left">
                                                    <img src="<?= $getStudents["avatar"] ?>" class="studentAvatar">
                                                </div>
                                                <div class="media-body">
                                                    <h4 class="media-heading"><?= $namexdxd ?></h4>
                                                    <?php if($yazsinifid["point_show"] == 1) { ?><span class="badge bg-green"><?= empty($getStudents["positiveBehaviorPoints"]) ? "0" : $getStudents["positiveBehaviorPoints"] ?></span><?php } ?>
                                                    <?php if($yazsinifid["point_show"] == 1) { ?><span class="badge bg-red"><?= empty($getStudents["negativeBehaviorPoints"]) ? "0" : $getStudents["negativeBehaviorPoints"] ?></span><?php } ?>
                                                    <?php if($yazsinifid["point_show"] == 1) { ?><span class="badge bg-orange"><?= empty($getStudents["redeemedPoints"]) ? "0" : $getStudents["redeemedPoints"] ?></span><?php } ?>
                                                    <?php if($yazsinifid["point_show"] != 3) { ?><span class="badge bg-blue"><?= empty($getStudents["totalBehaviorPoints"]) ? "0" : $getStudents["totalBehaviorPoints"] ?></span><?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<div class='col-lg-12 col-md-12 col-sm-12 col-xs-12'><div class='alert alert-danger'><strong>Error:</strong> No students belonging to the group were found.</div></div>";
                    }
                    ?>
                </div>
                <input type="hidden" name="hidden_class_id" id="hidden_class_id" value="<?=$class?>">
                <input type="hidden" name="hidden_group_id" id="hidden_group_id" value="<?=$group?>">
                <input type="hidden" id="selected_students" name="selected_students" data-class="<?= $class ?>">
                <input type="hidden" id="selected_students2" name="selected_students2" data-class="<?= $class ?>">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-block btn-lg waves-effect editGroupButton" data-class="<?=$class?>" data-group="<?=$group?>">Edit This Group</button>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <button type="button" class="btn btn-danger btn-block btn-lg waves-effect" id="deleteGroupButton" data-class="<?=$class?>" data-group="<?=$group?>">Delete This Group</button>
                        </div>
                    </div>
                </div>
                <div id="editGroupResult"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
    else if($page_request == "create-group-modal")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $class = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($class === false)
        {
            echo 0;
            exit();
        }
        $sorgu2 = $DB_con->prepare("SELECT id,student_show,point_show,points_by_time FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
        $sorgu2->execute(array(":id"=>$class,":oid"=>$uyevtid,":school"=>$uyeokul));
        if($sorgu2->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $yazsinifid = $sorgu2->fetch(PDO::FETCH_ASSOC);
        $pointsByTimeQuery = '';
        if ($yazsinifid['points_by_time'] == 2) {
            $pointsByTimeQuery = 'AND date(date) = CURDATE()';
        } else if ($yazsinifid['points_by_time'] == 3) {
            $pointsByTimeQuery = 'AND YEARWEEK(`date`, 1) = YEARWEEK(CURDATE(), 1)';
        } else if ($yazsinifid['points_by_time'] == 4) {
            $pointsByTimeQuery = 'AND MONTH(date) = MONTH(CURRENT_DATE())';
        }
        $noAvailableStudent = false;
        ?>
        <div class="modal-header">
            <h4 class="modal-title">Create Group</h4>
        </div>
        <div class="modal-body">
            <form id="createGroupForm" data-class="<?= $class ?>">
                <div class="form-group">*
                    <label for="name">Name:</label>
                    <div class="form-line">
                        <input class="form-control" name="name" id="name" type="text">
                    </div>
                </div>
                <label>Students:</label>
                <div class="row">
                    <?php
                    $availableStudentsQuery = $DB_con->prepare("SELECT id,name,avatar,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx AND type = 1 $pointsByTimeQuery) as positiveBehaviorPoints,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx2 AND type = 2 $pointsByTimeQuery) as negativeBehaviorPoints, (SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx3 AND type = 3 $pointsByTimeQuery) as redeemedPoints,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx4 $pointsByTimeQuery) as totalBehaviorPoints FROM users WHERE FIND_IN_SET(:class2, classes) AND role = :role AND schools = :school AND NOT FIND_IN_SET(id, (SELECT IFNULL(GROUP_CONCAT(students), '') FROM groups WHERE class = :class3 AND school = :school2))");
                    $availableStudentsQuery->execute(array(":classx"=>$class,":classx2"=>$class,":classx3"=>$class,":classx4"=>$class,":class2"=>$class,":role"=>"student",":school"=>$uyeokul,":class3"=>$class,":school2"=>$uyeokul));
                    if ($availableStudentsQuery->rowCount() > 0) {
                        while ($getStudents = $availableStudentsQuery->fetch(PDO::FETCH_ASSOC)) {
                            if($yazsinifid["student_show"] == 2) {
                                $explodestudentname = explode(" ", $getStudents["name"]);
                                array_pop($explodestudentname);
                                $namexdxd = implode(" ", $explodestudentname);
                            } else {
                                $namexdxd = $getStudents["name"];
                            }
                            ?>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <a href="javascript:;" id="selectStudent" data-student="<?= $getStudents["id"] ?>">
                                    <div class="panel panel-default panel-post groupStudents">
                                        <div class="panel-heading">
                                            <div class="media">
                                                <div class="media-left">
                                                    <img src="<?= $getStudents["avatar"] ?>" class="studentAvatar">
                                                </div>
                                                <div class="media-body">
                                                    <h4 class="media-heading"><?= $namexdxd ?></h4>
                                                    <?php if($yazsinifid["point_show"] == 1) { ?><span class="badge bg-green"><?= empty($getStudents["positiveBehaviorPoints"]) ? "0" : $getStudents["positiveBehaviorPoints"] ?></span><?php } ?>
                                                    <?php if($yazsinifid["point_show"] == 1) { ?><span class="badge bg-red"><?= empty($getStudents["negativeBehaviorPoints"]) ? "0" : $getStudents["negativeBehaviorPoints"] ?></span><?php } ?>
                                                    <?php if($yazsinifid["point_show"] == 1) { ?><span class="badge bg-orange"><?= empty($getStudents["redeemedPoints"]) ? "0" : $getStudents["redeemedPoints"] ?></span><?php } ?>
                                                    <?php if($yazsinifid["point_show"] != 3) { ?><span class="badge bg-blue"><?= empty($getStudents["totalBehaviorPoints"]) ? "0" : $getStudents["totalBehaviorPoints"] ?></span><?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php
                        }
                    } else {
                        $noAvailableStudent = true;
                    }
                    ?>
                </div>
                <input type="hidden" name="hidden_class_id" id="hidden_class_id" value="<?=$class?>">
                <input type="hidden" id="selected_students" name="selected_students" data-class="<?= $class ?>">
                <?php
                if ($noAvailableStudent == true) {
                    ?>
                    <div class='alert alert-danger'><strong>Error:</strong> All students belonging to this class are members of any group.</div>
                    <?php
                } else {
                  ?>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success btn-block btn-lg waves-effect createGroupButton" data-class="<?=$class?>">Create This Group</button>
                    </div>
                    <?php
                }
                ?>
                <div id="createGroupResult"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
    else if($page_request == "create-group")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $class = filter_input(INPUT_POST, 'hidden_class_id', FILTER_VALIDATE_INT);
        if($class === false)
        {
            echo 0;
            exit();
        }
        $sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
        $sorgu2->execute(array(":id"=>$class,":oid"=>$uyevtid,":school"=>$uyeokul));
        if($sorgu2->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $groupName = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        if(empty($groupName))
        {
            echo 2;
            exit();
        }
        if(strlen($groupName) < 3 || strlen($groupName) > 64)
        {
            echo 3;
            exit();
        }
        $students = filter_input(INPUT_POST, 'selected_students', FILTER_SANITIZE_STRING);
        $nowDate = date('Y-m-d H:i:s');
        $sorgu = $DB_con->prepare("INSERT INTO groups(name,class,school,students,created_time,created_by) VALUES (:name, :class, :school, :students, :createdtime, :createdby)");
        if($sorgu->execute(array(":name"=>$groupName,":class"=>$class,":school"=>$uyeokul,":students"=>$students,":createdtime"=>$nowDate,":createdby"=>$uyevtid)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "edit-group")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $class = filter_input(INPUT_POST, 'hidden_class_id', FILTER_VALIDATE_INT);
        if($class === false)
        {
            echo 0;
            exit();
        }
        $group = filter_input(INPUT_POST, 'hidden_group_id', FILTER_VALIDATE_INT);
        if($group === false)
        {
            echo 0;
            exit();
        }
        $sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
        $sorgu2->execute(array(":id"=>$class,":oid"=>$uyevtid,":school"=>$uyeokul));
        if($sorgu2->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorgu3 = $DB_con->prepare("SELECT id,students FROM groups WHERE id = :id AND class = :class AND school = :school");
        $sorgu3->execute(array(":id"=>$group,":class"=>$class,":school"=>$uyeokul));
        if($sorgu3->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $groupName = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        if(empty($groupName))
        {
            echo 2;
            exit();
        }
        if(strlen($groupName) < 3 || strlen($groupName) > 64)
        {
            echo 3;
            exit();
        }
        $fetchGroup = $sorgu3->fetch(PDO::FETCH_ASSOC);
        $explodeCurrentStudents = explode(',', $fetchGroup['students']);
        $willBeRemoveStudents = filter_input(INPUT_POST, 'selected_students2', FILTER_SANITIZE_STRING);
        if (!empty($willBeRemoveStudents)) {
            $explodeWillBeRemoveStudents = explode(',', $willBeRemoveStudents);
            foreach(array_unique($explodeWillBeRemoveStudents) as $student) {
                unset($explodeCurrentStudents[array_search($student, $explodeCurrentStudents)]);
            }
        }
        $willBeAddStudents = filter_input(INPUT_POST, 'selected_students', FILTER_SANITIZE_STRING);
        if (!empty($willBeAddStudents)) {
            $explodeWillBeAddStudents = explode(',', $willBeAddStudents);
            foreach(array_unique($explodeWillBeAddStudents) as $student) {
                $explodeCurrentStudents[] = $student;
            }
        }
        $sorgu = $DB_con->prepare("UPDATE groups SET name = :name , students = :students WHERE class = :class AND school = :school AND id = :id");
        if($sorgu->execute(array(":name"=>$groupName,":students"=>implode(',', array_unique($explodeCurrentStudents)),":class"=>$class,":school"=>$uyeokul,":id"=>$group)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "delete-group")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $class = filter_input(INPUT_POST, 'class', FILTER_VALIDATE_INT);
        if($class === false)
        {
            echo 0;
            exit();
        }
        $group = filter_input(INPUT_POST, 'group', FILTER_VALIDATE_INT);
        if($group === false)
        {
            echo 0;
            exit();
        }
        $sorgu2 = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
        $sorgu2->execute(array(":id"=>$class,":oid"=>$uyevtid,":school"=>$uyeokul));
        if($sorgu2->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorgu3 = $DB_con->prepare("SELECT id FROM groups WHERE id = :id AND class = :class AND school = :school");
        $sorgu3->execute(array(":id"=>$group,":class"=>$class,":school"=>$uyeokul));
        if($sorgu3->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorgu = $DB_con->prepare("DELETE FROM groups WHERE class = :class AND school = :school AND id = :id");
        if($sorgu->execute(array(":class"=>$class,":school"=>$uyeokul,":id"=>$group)))
        {
            echo 1;
            exit();
        }
        else
        {
            echo 0;
            exit();
        }
    }
    else if($page_request == "get-groups")
    {
        $class = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($class === false)
        {
            echo 0;
            exit();
        }
        $sorgusinifid = $DB_con->prepare("SELECT id,student_show,point_show,points_by_time FROM classes WHERE FIND_IN_SET(:uyeid, teachers) AND id = :id AND school = :school");
        $sorgusinifid->execute(array(":uyeid"=>$uyevtid,":id"=>$class,":school"=>$uyeokul));
        if($sorgusinifid->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $getGroups = $DB_con->prepare("SELECT id,name,students FROM groups WHERE class = :id AND school = :school");
        $getGroups->execute(array(":id"=>$class,":school"=>$uyeokul));
        if($getGroups->rowCount() == 0)
        {
            echo 2;
            exit();
        }
        $yazsinifid = $sorgusinifid->fetch(PDO::FETCH_ASSOC);
        $pointsByTimeQuery = '';
        if ($yazsinifid['points_by_time'] == 2) {
            $pointsByTimeQuery = 'AND date(date) = CURDATE()';
        } else if ($yazsinifid['points_by_time'] == 3) {
            $pointsByTimeQuery = 'AND YEARWEEK(`date`, 1) = YEARWEEK(CURDATE(), 1)';
        } else if ($yazsinifid['points_by_time'] == 4) {
            $pointsByTimeQuery = 'AND MONTH(date) = MONTH(CURRENT_DATE())';
        }
        echo '<div class="row"><div class="group-board">';
        while($fetchGroups = $getGroups->fetch(PDO::FETCH_ASSOC)) {
            ?>
            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-3">
                <div class="group-card">
                    <div class="card-header">
                        <span class="card-header-text">
                            <?=$fetchGroups["name"]?>
                            <a href="#" class="pull-right" id="editGroup" data-toggle="modal" data-target="#editGroupModal" data-class="<?=$class?>" data-group="<?=$fetchGroups['id']?>">
                                <i class="material-icons font-17">edit</i>
                            </a>
                            <a href="#" class="pull-right group-give-points" data-group-id="<?=$fetchGroups["id"]?>" data-group-name="<?=$fetchGroups["name"]?>">
                                <i class="material-icons font-17">note_add</i>
                            </a>
                        </span>
                    </div>
                    <ul class="sortable ui-sortable" id="sort<?=$fetchGroups["id"]?>" data-group-id="<?=$fetchGroups["id"]?>">
                        <?php
                        $getStudents = $DB_con->prepare("SELECT id,name,avatar,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx AND type = 1 $pointsByTimeQuery) as positiveBehaviorPoints,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx2 AND type = 2 $pointsByTimeQuery) as negativeBehaviorPoints, (SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx3 AND type = 3 $pointsByTimeQuery) as redeemedPoints,(SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND class_id = :classx4 $pointsByTimeQuery) as totalBehaviorPoints FROM users WHERE FIND_IN_SET(id, (SELECT students FROM groups WHERE id = :id AND class = :class AND school = :school))");
                        $getStudents->execute(array(":classx"=>$class,":classx2"=>$class,":classx3"=>$class,":classx4"=>$class,":id"=>$fetchGroups["id"],":class"=>$class,":school"=>$uyeokul));
                        $studentCount = false;
                        $groupTotalPoint = 0;
                        if ($getStudents->rowCount() > 0) {
                            $studentCount = true;
                            while ($fetchStudents = $getStudents->fetch(PDO::FETCH_ASSOC)) {
                                if ($yazsinifid["student_show"] == 2) {
                                    $explodestudentname = explode(" ", $fetchStudents["name"]);
                                    array_pop($explodestudentname);
                                    $namexdxd = implode(" ", $explodestudentname);
                                } else {
                                    $namexdxd = $fetchStudents["name"];
                                }
                                ?>
                                <li class="text-row ui-sortable-handle"
                                    data-student-id="<?= $fetchStudents["id"] ?>" id="<?= $fetchStudents["id"] ?>">
                                    <a href="javascript:;" data-toggle="modal" data-target="#modal-student"
                                       class="ogrenci-puanla" id="<?= $fetchStudents["id"] ?>" class_id="<?= $class ?>">
                                        <div class="media">
                                            <div class="media-left">
                                                <img src="<?= $fetchStudents["avatar"] ?>" class="studentAvatar">
                                            </div>
                                            <div class="media-body">
                                                <h4 class="media-heading"><?= $namexdxd ?></h4>
                                                <?php if ($yazsinifid["point_show"] == 1) { ?><span
                                                        class="badge bg-green"><?= empty($fetchStudents["positiveBehaviorPoints"]) ? "0" : $fetchStudents["positiveBehaviorPoints"] ?></span><?php } ?>
                                                <?php if ($yazsinifid["point_show"] == 1) { ?><span
                                                        class="badge bg-red"><?= empty($fetchStudents["negativeBehaviorPoints"]) ? "0" : $fetchStudents["negativeBehaviorPoints"] ?></span><?php } ?>
                                                <?php if ($yazsinifid["point_show"] == 1) { ?><span
                                                        class="badge bg-orange"><?= empty($fetchStudents["redeemedPoints"]) ? "0" : $fetchStudents["redeemedPoints"] ?></span><?php } ?>
                                                <?php if ($yazsinifid["point_show"] != 3) { ?><span
                                                        class="badge bg-blue"><?= empty($fetchStudents["totalBehaviorPoints"]) ? "0" : $fetchStudents["totalBehaviorPoints"] ?></span><?php } ?>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <?php
                                $groupTotalPoint += $fetchStudents["totalBehaviorPoints"];
                            }
                        }
                        ?>
                    </ul>
                    <?php
                    if($studentCount) {
                        ?>
                        <div class="card-footer">
                        <span class="card-header-text">
                            <span class="badge <?=$groupTotalPoint > 0 ? 'bg-green' : 'bg-red'?> btn-block"><?=$groupTotalPoint > 0 ? '+ ' : '- '?><?=abs($groupTotalPoint)?> Points</span>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        echo '</div></div>';
    }
    else if($page_request == "change-student-group")
    {
        if($uyerol != "teacher")
        {
            echo 0;
            exit();
        }
        $class = filter_input(INPUT_GET, 'class', FILTER_VALIDATE_INT);
        if($class === false)
        {
            echo 0;
            exit();
        }
        $student = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        if($student === false)
        {
            echo 0;
            exit();
        }
        $group = filter_input(INPUT_GET, 'group', FILTER_VALIDATE_INT);
        if($group === false)
        {
            echo 0;
            exit();
        }
        $checkTeacher = $DB_con->prepare("SELECT id FROM classes WHERE id = :id AND FIND_IN_SET(:oid, teachers) AND school = :school");
        $checkTeacher->execute(array(":id"=>$class,":oid"=>$uyevtid,":school"=>$uyeokul));
        if($checkTeacher->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $checkStudent = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND FIND_IN_SET(:sid, classes) AND schools = :school");
        $checkStudent->execute(array(":id"=>$student,":sid"=>$class,":school"=>$uyeokul));
        if($checkStudent->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $checkGroup = $DB_con->prepare("SELECT id FROM groups WHERE class = :id AND school = :school AND id = :group");
        $checkGroup->execute(array(":id"=>$class,":school"=>$uyeokul,":group"=>$group));
        if($checkGroup->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $checkStudentGroup = $DB_con->prepare("SELECT id FROM groups WHERE FIND_IN_SET(:student, students) AND class = :class AND school = :school");
        $checkStudentGroup->execute(array(":student"=>$student,":class"=>$class,":school"=>$uyeokul));
        if($checkStudentGroup->rowCount() != 1) {
            echo 0;
            exit();
        } else {
            $getStudentGroup = $checkStudentGroup->fetch(PDO::FETCH_ASSOC);
            $updateStudentPreviousGroup = $DB_con->prepare("UPDATE groups SET students = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', students), ',".$student."', '')) WHERE FIND_IN_SET(:student2, students) AND class = :class AND school = :school AND id = :group");
            $updateStudentPreviousGroup->execute(array(":student2"=>$student,":class"=>$class,":school"=>$uyeokul,":group"=>$getStudentGroup["id"]));
            $updateStudentNewGroup = $DB_con->prepare("UPDATE groups SET students = CONCAT(students,',".$student."')  WHERE class = :class AND school = :school AND id = :group");
            if ($updateStudentNewGroup->execute(array(":class"=>$class,":school"=>$uyeokul,":group"=>$group))) {
                echo 1;
                exit();
            }
        }
    }
    else if($page_request == 'point-locations')
    {
        if($uyerol != 'admin') {
            echo 0;
            exit();
        }
        $sorguSchool = $DB_con->prepare("SELECT id FROM schools WHERE id = :id");
        $sorguSchool->execute(array(":id"=>$uyeokul));
        if($sorguSchool->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorguSchool2 = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND schools = :school");
        $sorguSchool2->execute(array(":id"=>$uyevtid,":school"=>$uyeokul));
        if($sorguSchool2->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $query = $DB_con->prepare('SELECT * FROM point_locations WHERE school = :school');
        $query->execute(array(':school'=>$uyeokul));
        ?>
        <table class="table table-condensed table-striped liste">
            <thead>
            <tr>
                <th>Name</th>
                <th>Action</th>
                <th style="padding:0!important;background: rgba(115, 255, 106, 0.1);width:30px;"><a href="#" data-toggle="modal" data-target="#actionPointLocationModal" id="create"><i class="material-icons">add</i></a></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if($query->rowCount() > 0)
            {
                while($fetch = $query->fetch(PDO::FETCH_ASSOC))
                {
                    ?>
                    <tr>
                        <td class="baslik_td"><?=$fetch['name']?></td>
                        <td colspan="2">
                            <a href="#" data-toggle="modal" data-target="#actionPointLocationModal" class="label label-info" id="<?=$fetch['id']?>">Edit</a>
                            <?php if($fetch['id'] != 1) { ?>
                            <a href="#" class="label label-danger deletePointLocation" id="<?=$fetch['id']?>">Delete</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            else
            {
                ?>
                <tr>
                    <td colspan="3">No results were found.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
    }
    else if($page_request == 'action-point-location-info')
    {
        if($uyerol != 'admin') {
            echo 0;
            exit();
        }
        $sorguSchool = $DB_con->prepare("SELECT id FROM schools WHERE id = :id");
        $sorguSchool->execute(array(":id"=>$uyeokul));
        if($sorguSchool->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorguSchool2 = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND schools = :school");
        $sorguSchool2->execute(array(":id"=>$uyevtid,":school"=>$uyeokul));
        if($sorguSchool2->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $actionType = isset($_GET['id']) ? 'edit' : 'create';
        if ($actionType == 'edit') {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($id === false) {
                echo 0;
                exit();
            }
            $query = $DB_con->prepare('SELECT * FROM point_locations WHERE id = :id');
            $query->execute(array(':id'=>$id));
            if ($query->rowCount() != 1) {
                echo 0;
                exit();
            }
            $fetch = $query->fetch(PDO::FETCH_ASSOC);
        }
        ?>
        <div class="modal-header">
            <h4 class="modal-title"><?=$actionType == 'edit' ? 'Edit Point Location' : 'Create Point Location'?></h4>
        </div>
        <div class="modal-body">
            <form id="actionPointLocationForm">
                <?php
                if($actionType == 'edit') {
                    ?>
                    <input type="hidden" name="id" value="<?=$fetch['id']?>">
                    <?php
                }
                ?>
                <div class="form-group">
                    <label for="name">Name:</label>
                    <div class="form-line">
                        <input class="form-control" name="name" id="name" type="text" value="<?=$actionType == 'edit' ? $fetch['name'] : ''?>">
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block btn-lg waves-effect actionPointLocationButton"><?=$actionType == 'edit' ? 'Edit Point Location' : 'Create Point Location'?></button>
                </div>
            </form>
            <div id="actionPointLocationResult"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
    else if($page_request == "action-point-location")
    {
        if ($uyerol != "admin") {
            echo 0;
            exit();
        }
        $sorguSchool = $DB_con->prepare("SELECT id FROM schools WHERE id = :id");
        $sorguSchool->execute(array(":id" => $uyeokul));
        if ($sorguSchool->rowCount() != 1) {
            echo 0;
            exit();
        }
        $sorguSchool2 = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND schools = :school");
        $sorguSchool2->execute(array(":id" => $uyevtid, ":school" => $uyeokul));
        if ($sorguSchool2->rowCount() != 1) {
            echo 0;
            exit();
        }
        $actionType = isset($_POST['id']) ? 'edit' : 'create';
        if ($actionType == 'edit') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id === false) {
                echo 0;
                exit();
            }
            $query = $DB_con->prepare("SELECT id FROM point_locations WHERE id = :id AND school = :school");
            $query->execute(array(':id' => $id, ':school' => $uyeokul));
            if ($query->rowCount() != 1) {
                echo 0;
                exit();
            }
        }
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        if (empty($name)) {
            echo 2;
            exit();
        }
        if (strlen($name) < 3 || strlen($name) > 64) {
            echo 3;
            exit();
        }
        $queryString = $actionType == 'edit' ? 'UPDATE point_locations SET name = :name WHERE id = :id AND school = :school' : 'INSERT INTO point_locations(school,name) VALUES (:school, :name)';
        $sorgu = $DB_con->prepare($queryString);
        if ($sorgu->execute($actionType == 'edit' ? array(":name" => $name, ":id" => $id, ":school" => $uyeokul) : array(":school" => $uyeokul, ":name" => $name))) {
            echo 1;
            exit();
        } else {
            echo 0;
            exit();
        }
    }
    else if($page_request == "delete-point-location")
    {
        if($uyerol != "admin")
        {
            echo 0;
            exit();
        }
        $sorguSchool = $DB_con->prepare("SELECT id FROM schools WHERE id = :id");
        $sorguSchool->execute(array(":id"=>$uyeokul));
        if($sorguSchool->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $sorguSchool2 = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND schools = :school");
        $sorguSchool2->execute(array(":id"=>$uyevtid,":school"=>$uyeokul));
        if($sorguSchool2->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id === false) {
            echo 0;
            exit();
        }
        if ($id == 1) {
            echo 0;
            exit();
        }
        $query = $DB_con->prepare("SELECT id FROM point_locations WHERE id = :id AND school = :school");
        $query->execute(array(':id'=>$id,':school'=>$uyeokul));
        if($query->rowCount() != 1) {
            echo 0;
            exit();
        }
        $setToDefaultQuery = $DB_con->prepare("UPDATE feedbacks_students SET point_location = :defaultPointLocation WHERE point_location = :pointLocation");
        if ($setToDefaultQuery->execute(array(':defaultPointLocation'=>1,':pointLocation'=>$id))) {
            $sorgu = $DB_con->prepare("DELETE FROM point_locations WHERE id = :id AND school = :school");
            if($sorgu->execute(array(":id"=>$id,":school"=>$uyeokul)))
            {
                echo 1;
                exit();
            }
            else
            {
                echo 0;
                exit();
            }
        } else {
            echo 0;
            exit();
        }
    }
    else if($page_request == "stats") {
        if($uyerol != "admin") {
            echo 0;
            exit();
        }
        $timeFilterStatus = isset($_GET['timefilter']) ? (int) $_GET['timefilter'] : 0;
        $now = date("Y-m-d");
        $headOfMonth = date("Y-m-01");
        $endOfMonth = date("Y-m-t");
        $yesterday = date('Y-m-d',strtotime("-1 days"));
        $lastWeek = date('Y-m-d',strtotime("-7 days"));
        $lastWeek2x = date('Y-m-d',strtotime("-14 days"));
        $headOfMonth2x = date('Y-m-01',strtotime("-1 month"));
        $endOfMonth2x = date('Y-m-t',strtotime("-1 month"));
        if($timeFilterStatus == 0)
        {
            $dateFilterQueryString = "";
        }
        else if($timeFilterStatus == 1)
        {
            $dateFilterQueryString = "AND (feedbacks_students.date BETWEEN '".$now." 00:00:00' AND '".$now." 23:59:59')";
        }
        else if($timeFilterStatus == 2)
        {
            $dateFilterQueryString = "AND (feedbacks_students.date BETWEEN '".$yesterday." 00:00:00' AND '".$yesterday." 23:59:59')";
        }
        else if($timeFilterStatus == 3)
        {
            $dateFilterQueryString = "AND (feedbacks_students.date BETWEEN '".$lastWeek." 00:00:00' AND '".$now."  23:59:59')";
        }
        else if($timeFilterStatus == 4)
        {
            $dateFilterQueryString = "AND (feedbacks_students.date BETWEEN '".$lastWeek2x." 00:00:00' AND '".$lastWeek." 23:59:59')";
        }
        else if($timeFilterStatus == 5)
        {
            $dateFilterQueryString = "AND (feedbacks_students.date BETWEEN '".$headOfMonth." 00:00:00' AND '".$endOfMonth." 23:59:59')";
        }
        else if($timeFilterStatus == 6)
        {
            $dateFilterQueryString = "AND (feedbacks_students.date BETWEEN '".$headOfMonth2x." 00:00:00' AND '".$endOfMonth2x." 23:59:59')";
        }
        else if($timeFilterStatus == 7)
        {
            if(isset($_GET["date1"]) && isset($_GET["date2"]))
            {
                if(!empty($_GET["date1"]) && !empty($_GET["date2"]))
                {
                    $dateFilterQueryString = "AND (feedbacks_students.date BETWEEN '".$_GET["date1"]." 00:00:00' AND '".$_GET["date2"]." 23:59:59')";
                } else {
                    $dateFilterQueryString = "";
                }
            } else {
                $dateFilterQueryString = "";
            }
        }
        else
        {
            $dateFilterQueryString = "";
        }
        $allBehaviorPointsQuery = $DB_con->prepare("SELECT (SELECT SUM(point) FROM feedbacks_students INNER JOIN classes ON classes.id = feedbacks_students.class_id WHERE type = 1 AND classes.school = :school $dateFilterQueryString) as positivePoints,(SELECT SUM(point) FROM feedbacks_students INNER JOIN classes ON classes.id = feedbacks_students.class_id WHERE type = 2 AND classes.school = :school2 $dateFilterQueryString) as negativePoints");
        $allBehaviorPointsQuery->execute(array(":school"=>$uyeokul,":school2"=>$uyeokul));
        $fetchAllBehaviorPoints = $allBehaviorPointsQuery->fetch(PDO::FETCH_ASSOC);

        $mostPositiveBehaviorPointsQuery = $DB_con->prepare("SELECT feedbacks_students.name, COUNT(*) as count FROM feedbacks_students INNER JOIN classes ON classes.id = feedbacks_students.class_id WHERE type = :type AND classes.school = :school $dateFilterQueryString GROUP BY name ORDER BY count DESC LIMIT 5");
        $mostPositiveBehaviorPointsQuery->execute(array(":type"=>1,":school"=>$uyeokul));
        $fetchMostPositiveBehaviorPoints = $mostPositiveBehaviorPointsQuery->fetchAll(PDO::FETCH_ASSOC);

        $topPositiveStudents = $DB_con->prepare("SELECT users.name, SUM(point) as totalPoint FROM feedbacks_students INNER JOIN classes ON classes.id = feedbacks_students.class_id INNER JOIN users ON users.id = feedbacks_students.student_id WHERE classes.school = :school AND users.role = :role $dateFilterQueryString GROUP BY users.id ORDER BY totalPoint DESC LIMIT 10");
        $topPositiveStudents->execute(array(":school"=>$uyeokul,":role"=>"student"));
        $fetchTopPositiveStudents = $topPositiveStudents->fetchAll(PDO::FETCH_ASSOC);

        $topNegativeStudents = $DB_con->prepare("SELECT users.name, SUM(point) as totalPoint FROM feedbacks_students INNER JOIN classes ON classes.id = feedbacks_students.class_id INNER JOIN users ON users.id = feedbacks_students.student_id WHERE classes.school = :school AND users.role = :role $dateFilterQueryString GROUP BY users.id ORDER BY totalPoint ASC LIMIT 10");
        $topNegativeStudents->execute(array(":school"=>$uyeokul,":role"=>"student"));
        $fetchTopNegativeStudents = $topNegativeStudents->fetchAll(PDO::FETCH_ASSOC);

        $pointsViaLocationsQuery = $DB_con->prepare("SELECT point_locations.name, SUM(CASE WHEN feedbacks_students.type = :type THEN feedbacks_students.point ELSE 0 END) AS positivePoints, SUM(CASE WHEN feedbacks_students.type = :type2 THEN feedbacks_students.point ELSE 0 END) AS negativePoints FROM feedbacks_students INNER JOIN classes ON classes.id = feedbacks_students.class_id INNER JOIN point_locations ON point_locations.id = feedbacks_students.point_location WHERE classes.school = :school AND point_locations.school = :school2 $dateFilterQueryString GROUP BY feedbacks_students.point_location");
        $pointsViaLocationsQuery->execute(array(":type"=>1,":type2"=>2,":school"=>$uyeokul,":school2"=>$uyeokul));
        $fetchPointsViaLocations = $pointsViaLocationsQuery->fetchAll(PDO::FETCH_ASSOC);

        $mostPointerTeachersQuery = $DB_con->prepare("SELECT users.name, COUNT(feedbacks_students.name) as times, SUM(CASE WHEN feedbacks_students.type = :type THEN feedbacks_students.point ELSE 0 END) AS positivePoints, SUM(CASE WHEN feedbacks_students.type = :type2 THEN feedbacks_students.point ELSE 0 END) AS negativePoints FROM feedbacks_students INNER JOIN users ON users.id = feedbacks_students.teacher WHERE users.role = :role AND users.schools = :school $dateFilterQueryString GROUP BY users.id");
        $mostPointerTeachersQuery->execute(array(":type"=>1,":type2"=>2,":role"=>"teacher",":school"=>$uyeokul));
        $fetchMostPointerTeachers = $mostPointerTeachersQuery->fetchAll(PDO::FETCH_ASSOC);

        $pointsByDayQuery = $DB_con->prepare("SELECT DAYNAME(date) as day, SUM(CASE WHEN type = :type THEN point ELSE 0 END) as positivePoints, SUM(CASE WHEN type = :type2 THEN point ELSE 0 END) as negativePoints FROM feedbacks_students INNER JOIN classes ON classes.id = feedbacks_students.class_id WHERE classes.school = :school $dateFilterQueryString GROUP BY DAY(date)");
        $pointsByDayQuery->execute(array(":type"=>1,":type2"=>2,":school"=>$uyeokul));
        $fetchPointsByDay = $pointsByDayQuery->fetchAll(PDO::FETCH_ASSOC);
        $days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
        $newPointsByDay = [];
        foreach ($days as $day) {
            $newPointsByDay[] = searchArray('day', $day, $fetchPointsByDay) === null ? ["day" => $day, "positivePoints" => 0, "negativePoints" => 0] : $fetchPointsByDay[searchArray('day', $day, $fetchPointsByDay)];
        }

        $pointsByHourQuery = $DB_con->prepare("SELECT CONCAT(HOUR(date), ':00') as hour, SUM(CASE WHEN type = :type THEN point ELSE 0 END) as positivePoints, SUM(CASE WHEN type = :type2 THEN point ELSE 0 END) as negativePoints FROM feedbacks_students INNER JOIN classes ON classes.id = feedbacks_students.class_id WHERE classes.school = :school $dateFilterQueryString GROUP BY hour");
        $pointsByHourQuery->execute(array(":type"=>1,":type2"=>2,":school"=>$uyeokul));
        $fetchPointsByHour = $pointsByHourQuery->fetchAll(PDO::FETCH_ASSOC);
        $hours = array("07:00", "08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00", "21:00", "22:00", "23:00", "00:00", "01:00", "02:00", "03:00", "04:00", "05:00", "06:00");
        $newPointsByHour = [];
        foreach ($hours as $hour) {
            $newPointsByHour[] = searchArray('hour', $hour, $fetchPointsByHour) === null ? ["hour" => $hour, "positivePoints" => 0, "negativePoints" => 0] : $fetchPointsByHour[searchArray('hour', $hour, $fetchPointsByHour)];
        }
        ?>
        <script type="text/javascript">
            function createChart(chartId) {
                if (chartId === 'total_behaviors_chart') {
                    if (window.totalBehaviorsChart) {
                        window.totalBehaviorsChart.destroy();
                    }
                    var ctx = document.getElementById(chartId).getContext("2d");
                    window.totalBehaviorsChart = new Chart(ctx, getChartJs('doughnut'));
                    window.totalBehaviorsChart.options.circumference = Math.PI;
                    window.totalBehaviorsChart.options.rotation = -Math.PI;
                    window.totalBehaviorsChart.update();
                } else if (chartId === 'most_positive_behaviors_chart') {
                    if (window.mostPositiveBehaviorsChart) {
                        window.mostPositiveBehaviorsChart.destroy();
                    }
                    var ctx2 = document.getElementById(chartId).getContext('2d');
                    window.mostPositiveBehaviorsChart = new Chart(ctx2, getChartJs('bar'));
                } else if (chartId === 'top_best_students') {
                    if (window.topBestStudents) {
                        window.topBestStudents.destroy();
                    }
                    var ctx3 = document.getElementById(chartId).getContext('2d');
                    window.topBestStudents = new Chart(ctx3, getChartJs('bar2'));
                } else if (chartId === 'top_worst_students') {
                    if (window.topWorstStudents) {
                        window.topWorstStudents.destroy();
                    }
                    var ctx4 = document.getElementById(chartId).getContext('2d');
                    window.topWorstStudents = new Chart(ctx4, getChartJs('bar3'));
                } else if (chartId === 'point_location_chart') {
                    if (window.pointLocationChart) {
                        window.pointLocationChart.destroy();
                    }
                    var ctx5 = document.getElementById(chartId).getContext('2d');
                    window.pointLocationChart = new Chart(ctx5, getChartJs('bar4'));
                } else if (chartId === 'most_pointer_teachers') {
                    if (window.mostPointerTeachersChart) {
                        window.mostPointerTeachersChart.destroy();
                    }
                    var ctx6 = document.getElementById(chartId).getContext('2d');
                    window.mostPointerTeachersChart = new Chart(ctx6, getChartJs('bar5'));
                } else if (chartId === 'points_by_day_chart') {
                    if (window.pointsByDay) {
                        window.pointsByDay.destroy();
                    }
                    var ctx7 = document.getElementById(chartId).getContext('2d');
                    window.pointsByDay = new Chart(ctx7, getChartJs('bar6'));
                } else if (chartId === 'points_by_hour_chart') {
                    if (window.pointsByHour) {
                        window.pointsByHour.destroy();
                    }
                    var ctx8 = document.getElementById(chartId).getContext('2d');
                    window.pointsByHour = new Chart(ctx8, getChartJs('bar7'));
                }
            }
            function getChartJs(type) {
                var config = null;
                if (type === 'bar') {
                    config = {
                        type: 'bar',
                        data: {

                            labels: [
                                <?php
                                foreach($fetchMostPositiveBehaviorPoints as $behavior) {
                                    echo '"'.$behavior['name'].'",';
                                }
                                ?>
                            ],
                            datasets: [
                                {label: "Total Point", data: [<?php foreach($fetchMostPositiveBehaviorPoints as $behavior) { echo '"'.$behavior['count'].'",'; } ?>], backgroundColor: "rgb(45,185,50)"}
                            ]
                        },
                        options: {
                            responsive: true,
                            legend: false
                        }
                    }
                } else if (type === 'bar2') {
                    config = {
                        type: 'bar',
                        data: {

                            labels: [
                                <?php
                                foreach($fetchTopPositiveStudents as $behavior) {
                                    echo '"'.$behavior['name'].'",';
                                }
                                ?>
                            ],
                            datasets: [
                                {label: "Total Point", data: [<?php foreach($fetchTopPositiveStudents as $behavior) { echo '"'.$behavior['totalPoint'].'",'; } ?>], backgroundColor: "rgb(45,185,50)"}
                            ]
                        },
                        options: {
                            responsive: true,
                            legend: false
                        }
                    }
                } else if (type === 'bar3') {
                    config = {
                        type: 'bar',
                        data: {

                            labels: [
                                <?php
                                foreach($fetchTopNegativeStudents as $behavior) {
                                    echo '"'.$behavior['name'].'",';
                                }
                                ?>
                            ],
                            datasets: [
                                {label: "Total Point", data: [<?php foreach($fetchTopNegativeStudents as $behavior) { echo '"'.$behavior['totalPoint'].'",'; } ?>], backgroundColor: "rgb(244,67,54)"}
                            ]
                        },
                        options: {
                            responsive: true,
                            legend: false
                        }
                    }
                } else if (type === 'bar4') {
                    config = {
                        type: 'bar',
                        data: {

                            labels: [
                                <?php
                                foreach($fetchPointsViaLocations as $location) {
                                    echo '"'.$location['name'].'",';
                                }
                                ?>
                            ],
                            datasets: [
                                {
                                    label: "Positive",
                                    type: "bar",
                                    stack: "Base",
                                    backgroundColor: "rgb(45,185,50)",
                                    data: [<?php foreach($fetchPointsViaLocations as $positive) { echo '"'.$positive['positivePoints'].'",'; } ?>],
                                }, {
                                    label: "Negative",
                                    type: "bar",
                                    stack: "Base",
                                    backgroundColor: "rgb(244,67,54)",
                                    data: [<?php foreach($fetchPointsViaLocations as $negative) { echo '"'.$negative['negativePoints'].'",'; } ?>],
                                }
                            ]
                        },
                        options: {
                            scales: {
                                xAxes: [{
                                    stacked: true,
                                    ticks: {
                                        beginAtZero: true,
                                        maxRotation: 0,
                                        minRotation: 0
                                    }
                                }],
                                yAxes: [{
                                    stacked: true,
                                }]
                            },
                        }
                    }
                } else if (type === 'bar5') {
                    config = {
                        type: 'bar',
                        data: {

                            labels: [
                                <?php
                                foreach($fetchMostPointerTeachers as $location) {
                                    echo '"'.$location['name'].'",';
                                }
                                ?>
                            ],
                            datasets: [
                                {
                                    label: "Points Awarded",
                                    type: "bar",
                                    stack: "Base",
                                    backgroundColor: "#eece01",
                                    data: [<?php foreach($fetchMostPointerTeachers as $point) { echo '"'.$point['times'].'",'; } ?>],
                                }, {
                                    label: "Total Positive",
                                    type: "bar",
                                    stack: "Sensitivity",
                                    backgroundColor: "rgb(45,185,50)",
                                    data: [<?php foreach($fetchMostPointerTeachers as $point) { echo '"'.$point['positivePoints'].'",'; } ?>],
                                }, {
                                    label: "Total Negative",
                                    type: "bar",
                                    stack: "Sensitivity",
                                    backgroundColor: "rgb(244,67,54)",
                                    data: [<?php foreach($fetchMostPointerTeachers as $point) { echo '"'.$point['negativePoints'].'",'; } ?>]
                                }
                            ]
                        },
                        options: {
                            scales: {
                                xAxes: [{
                                    stacked: true,
                                    ticks: {
                                        beginAtZero: true,
                                        maxRotation: 0,
                                        minRotation: 0
                                    }
                                }],
                                yAxes: [{
                                    stacked: true,
                                }]
                            },
                        }
                    }
                } else if (type === 'bar6') {
                    config = {
                        type: 'bar',
                        data: {

                            labels: [
                                <?php
                                foreach($newPointsByDay as $day) {
                                    echo '"'.$day['day'].'",';
                                }
                                ?>
                            ],
                            datasets: [
                                {
                                    label: "Positive",
                                    type: "bar",
                                    stack: "Base",
                                    backgroundColor: "rgb(45,185,50)",
                                    data: [<?php foreach($newPointsByDay as $positive) { echo '"'.$positive['positivePoints'].'",'; } ?>],
                                }, {
                                    label: "Negative",
                                    type: "bar",
                                    stack: "Base",
                                    backgroundColor: "rgb(244,67,54)",
                                    data: [<?php foreach($newPointsByDay as $negative) { echo '"'.$negative['negativePoints'].'",'; } ?>],
                                }
                            ]
                        },
                        options: {
                            scales: {
                                xAxes: [{
                                    stacked: true,
                                    ticks: {
                                        beginAtZero: true,
                                        maxRotation: 0,
                                        minRotation: 0
                                    }
                                }],
                                yAxes: [{
                                    stacked: true,
                                }]
                            },
                        }
                    }
                } else if (type === 'bar7') {
                    config = {
                        type: 'bar',
                        data: {

                            labels: [
                                <?php
                                foreach($newPointsByHour as $hour) {
                                    echo '"'.$hour['hour'].'",';
                                }
                                ?>
                            ],
                            datasets: [
                                {
                                    label: "Positive",
                                    type: "bar",
                                    stack: "Base",
                                    backgroundColor: "rgb(45,185,50)",
                                    data: [<?php foreach($newPointsByHour as $positive) { echo '"'.$positive['positivePoints'].'",'; } ?>],
                                }, {
                                    label: "Negative",
                                    type: "bar",
                                    stack: "Base",
                                    backgroundColor: "rgb(244,67,54)",
                                    data: [<?php foreach($newPointsByHour as $negative) { echo '"'.$negative['negativePoints'].'",'; } ?>],
                                }
                            ]
                        },
                        options: {
                            scales: {
                                xAxes: [{
                                    stacked: true,
                                    ticks: {
                                        beginAtZero: true,
                                        maxRotation: 0,
                                        minRotation: 0
                                    }
                                }],
                                yAxes: [{
                                    stacked: true,
                                }]
                            },
                        }
                    }
                } else if (type === 'doughnut') {
                    config = {
                        type: 'doughnut',
                        data: {
                            datasets: [{
                                data: [<?=$fetchAllBehaviorPoints['positivePoints']?>, <?=$fetchAllBehaviorPoints['negativePoints']?>],
                                backgroundColor: [
                                    'rgb(45,185,50)',
                                    'rgb(244,67,54)',
                                ],
                                label: 'All behavior points of school'
                            }],
                            labels: [
                                'Positive',
                                'Negative'
                            ]
                        },
                        options: {
                            responsive: true,
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'All behavior points of school'
                            },
                            animation: {
                                animateScale: true,
                                animateRotate: true
                            }
                        }
                    }
                }
                return config;
            }
        </script>
        <?php
    }
}
?>