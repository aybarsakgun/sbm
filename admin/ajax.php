<?php
$base_request = filter_input(INPUT_GET, 'br', FILTER_SANITIZE_STRING);

include_once 'class.admin.php';

SessionStartAdmin();

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

if(isset($_GET["s"]))
{
	if($_GET["s"] == "sign-in")
	{
		$admin_mail = filter_input(INPUT_POST, 'admin_mail', FILTER_SANITIZE_EMAIL);
		$admin_password = filter_input(INPUT_POST, 'aPass', FILTER_SANITIZE_STRING);
		if(empty($admin_mail) || empty($admin_password))
		{
			echo 4;
			exit();
		}
		if(!filter_var($admin_mail, FILTER_VALIDATE_EMAIL)) 
		{
			echo 0;
			exit();
		}
		$admin->LoginToAdmin($admin_mail,$admin_password);
	}
	else if($_GET["s"] == "sign-out")
	{
		if(LoginCheckAdmin($DB_con) == true)
		{
			$admin->LogOutAdmin();	
		}
	}
	else if($_GET["s"] == "admin-logon-records")
	{
		if(LoginCheckAdmin($DB_con) == true)
		{
			$sorguSay = $DB_con->prepare("SELECT COUNT(member_id) AS say FROM login_attempts WHERE member_id = :uyeid");
			$sorguSay->execute(array(":uyeid"=>1));
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
					$sorguyazisi = "SELECT COUNT(member_id) AS say FROM login_attempts WHERE $filtre_yazisi AND member_id = :uyeid";
				}
				else if($filtre_yazisi == "")
				{
					$sorguyazisi = "SELECT COUNT(member_id) AS say FROM login_attempts WHERE member_id = :uyeid";
				}

				$sorgu = $DB_con->prepare($sorguyazisi);
				$sorgu->execute(array(":uyeid"=>1));
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
					$sorguyazisi = "SELECT ip,date,browser,status FROM login_attempts WHERE $filtre_yazisi AND member_id = :uyeid $siralama_yazisi LIMIT :limit , :sayfada";
				}
				else if($filtre_yazisi == "")
				{
					$sorguyazisi = "SELECT ip,date,browser,status FROM login_attempts WHERE member_id = :uyeid $siralama_yazisi LIMIT :limit , :sayfada";
				}

				$sorgu = $DB_con->prepare($sorguyazisi);
				$sorgu->execute(array(":uyeid"=>1,":limit"=>abs($limit),":sayfada"=>$sayfada));

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
									<strong>Date:</strong> <?=$yaz["date"]?><br>
									<strong>Status:</strong><br>
									<?php if($yaz["status"] == "0") { ?><span class="label label-danger">Unsuccessful</span><?php } else if($yaz["status"] == "1") { ?><span class="label label-success">Successful</span><?php } ?>
								</div>
							</td>
							<td class="visible-sm visible-md visible-lg"><?=$yaz["date"]?></td>
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
    else if($_GET["s"] == "schools")
    {
        if(LoginCheckAdmin($DB_con) == true)
        {
            $sorguSay = $DB_con->prepare("SELECT id FROM schools");
            $sorguSay->execute();
            if($sorguSay->rowCount() > 0)
            {
                $sayimsonucu = $sorguSay->fetch(PDO::FETCH_ASSOC);

                $sayfada = 10;
                $toplam_icerik = $sorguSay->rowCount();
                $toplam_sayfa = ceil($toplam_icerik / $sayfada);
                $sayfa = isset($_GET['sayfa']) ? (int) $_GET['sayfa'] : 1;
                if($sayfa < 1) $sayfa = 1;
                if($sayfa > $toplam_sayfa) $sayfa = $toplam_sayfa;
                $limit = ($sayfa - 1) * $sayfada;

                $sorgu = $DB_con->prepare("SELECT id,name FROM schools LIMIT :limit , :sayfada");
                $sorgu->execute(array(":limit"=>abs($limit),":sayfada"=>$sayfada));

                ?>
                <small>Toplam <?=$sorguSay->rowCount()?> tane bulunan sonuçtan <?=$sorgu->rowCount()?> tanesini görüntülüyorsunuz.</small>
                <table class="table table-condensed table-striped liste">
                    <thead>
                    <tr>
                        <th class="baslik_th">Name</th>
                        <th class="visible-sm visible-md visible-lg">Admin</th>
                        <th class="visible-sm visible-md visible-lg">Students</th>
                        <th class="visible-sm visible-md visible-lg">Teachers</th>
                        <th class="visible-sm visible-md visible-lg">Classes</th>
                        <th class="visible-sm visible-md visible-lg">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    while($yaz = $sorgu->fetch(PDO::FETCH_ASSOC))
                    {
                        $sorguOkulOgrencileri = $DB_con->prepare("SELECT id FROM users WHERE schools = :school AND role = :role");
                        $sorguOkulOgrencileri->execute(array(":school"=>$yaz["id"],":role"=>"student"));
                        $sorguOkulOgretmenleri = $DB_con->prepare("SELECT id FROM users WHERE schools = :school AND role = :role");
                        $sorguOkulOgretmenleri->execute(array(":school"=>$yaz["id"],":role"=>"teacher"));
                        $sorguOkulSiniflari = $DB_con->prepare("SELECT id FROM classes WHERE school = :school");
                        $sorguOkulSiniflari->execute(array(":school"=>$yaz["id"]));
                        $sorguOkulAdmin = $DB_con->prepare("SELECT id,name,email,register_date FROM users WHERE schools = :school AND role = :role");
                        $sorguOkulAdmin->execute(array(":school"=>$yaz["id"],":role"=>"admin"));
                        if($sorguOkulAdmin->rowCount() == 1) {
                            $yazOkulAdmin = $sorguOkulAdmin->fetch(PDO::FETCH_ASSOC);
                            $okuladminad = $yazOkulAdmin["name"];
                            if($yazOkulAdmin["register_date"] == NULL)
                            {
                                $okuladmindurum = 0;
                            }
                            else
                            {
                                $okuladmindurum = 1;
                            }
                        } else {
                          $okuladminad = "-";
                        }
                        ?>
                        <tr>
                            <td class="baslik_td">
                                <span class="baslik"><?=$yaz["name"]?></span>
                                <div class="visible-xs">
                                    <strong>Admin: </strong><?php if($okuladminad != "-") { ?><a href="javascript:;" data-toggle="modal" data-target="#modal-school-admin-edit" data-school-admin-id="<?=$yazOkulAdmin["id"]?>" data-school-admin-name="<?=$yazOkulAdmin["name"]?>" data-school-name="<?=$yaz["name"]?>" data-school-admin-status="<?=$okuladmindurum?>"><?=$okuladminad?></a> <?php } else if($okuladminad == "-") { echo $okuladminad; } ?><br>
                                    <strong>Students: </strong><a href="students?school=<?=$yaz["id"]?>"><?=$sorguOkulOgrencileri->rowCount()?></a><br>
                                    <strong>Teachers: </strong><a href="teachers?school=<?=$yaz["id"]?>"><?=$sorguOkulOgretmenleri->rowCount()?></a><br>
                                    <strong>Classes: </strong><a href="classes?school=<?=$yaz["id"]?>"><?=$sorguOkulSiniflari->rowCount()?></a><br>
                                    <strong>Action:</strong> <a href="javascript:;" class="label label-info" data-toggle="modal" data-target="#modal-school-edit" data-school="<?=$yaz["id"]?>" data-school-name="<?=$yaz["name"]?>">Edit</a>
                                </div>
                            </td>
                            <td class="visible-sm visible-md visible-lg"><?php if($okuladminad != "-") { ?><a href="javascript:;" data-toggle="modal" data-target="#modal-school-admin-edit" data-school-admin-id="<?=$yazOkulAdmin["id"]?>" data-school-admin-name="<?=$yazOkulAdmin["name"]?>" data-school-name="<?=$yaz["name"]?>" data-school-admin-status="<?=$okuladmindurum?>"><?=$okuladminad?></a> <?php } else if($okuladminad == "-") { echo $okuladminad; } ?></td>
                            <td class="visible-sm visible-md visible-lg"><a href="students?school=<?=$yaz["id"]?>"><?=$sorguOkulOgrencileri->rowCount()?></a></td>
                            <td class="visible-sm visible-md visible-lg"><a href="teachers?school=<?=$yaz["id"]?>"><?=$sorguOkulOgretmenleri->rowCount()?></a></td>
                            <td class="visible-sm visible-md visible-lg"><a href="classes?school=<?=$yaz["id"]?>"><?=$sorguOkulSiniflari->rowCount()?></a></td>
                            <td class="visible-sm visible-md visible-lg"><a href="javascript:;" class="label label-info editSchool" data-toggle="modal" data-target="#modal-school-edit" id="<?=$yaz["id"]?>" data-school-name="<?=$yaz["name"]?>">Edit</a></td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
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
    else if($_GET["s"] == "get-classes")
    {
        if(LoginCheckAdmin($DB_con) == false)
        {
            echo 0;
            exit();
        }
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $sorgu = $DB_con->prepare("SELECT classes.id,classes.name,group_concat(users.name) AS teachersname FROM classes INNER JOIN users ON FIND_IN_SET(users.id,teachers) > 0 WHERE school = :school AND role = :role GROUP BY classes.id");
        $sorgu->execute(array(":school"=>$id,":role"=>"teacher"));
        while($yaz = $sorgu->fetch(PDO::FETCH_ASSOC))
        {
            $gelenler[] = $yaz;
        }
        echo json_encode($gelenler);
        exit();
    }
    else if($_GET["s"] == "students")
    {
        if(LoginCheckAdmin($DB_con) == true)
        {
            $sorguSay = $DB_con->prepare("SELECT COUNT(id) AS say FROM users WHERE role = :role");
            $sorguSay->execute(array(":role"=>"student"));
            $yazSay = $sorguSay->fetch(PDO::FETCH_ASSOC);

            if($yazSay["say"] > 0)
            {
                $duzenlemesorguxd = "";
                $filtre_yazisi = "";
                $prefix = "";
                $puansorguxd = "SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id";
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

                if(isset($_GET["okul"]))
                {
                    $gelen_filtre_okul = (int)$_GET["okul"];
                    if($gelen_filtre_okul != "" || $gelen_filtre_okul != "0")
                    {
                        $filtre_yazisi .= $prefix . "schools.id = ".$gelen_filtre_okul." ";
                        $prefix = 'AND ';
                    }
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
                            $puansorguxd = "SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND feedbacks_students.type = 1";
                        }
                        else if($gelen_filtre_puan == 2)
                        {
                            $puansorguxd = "SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND feedbacks_students.type = 2";
                        }
                        else if($gelen_filtre_puan == 3)
                        {
                            $puansorguxd = "SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND feedbacks_students.type = 3";
                        }
                        else if($gelen_filtre_puan == 4)
                        {
                            $puansorguxd = "SELECT SUM(feedbacks_students.point) FROM feedbacks_students WHERE feedbacks_students.student_id = users.id AND feedbacks_students.type <> 3";
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
                    $sorguyazisi = "SELECT COUNT(say) as say FROM (SELECT users.id AS say FROM users INNER JOIN schools ON schools.id = users.schools INNER JOIN classes on FIND_IN_SET(classes.id, users.classes) > 0 WHERE $filtre_yazisi AND role = :role GROUP BY users.id) users";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT COUNT(id) AS say FROM users WHERE role = :role";
                }

                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute(array(":role"=>"student"));
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
                    $sorguyazisi = "SELECT users.register_type,users.name,users.email,users.invite_date,users.register_date,users.update_date,users.id AS studentid,schools.name AS okulad,GROUP_CONCAT(concat(classes.name,'+_+',classes.id) ORDER BY classes.id SEPARATOR '_-_') AS sinifad,($puansorguxd $duzenlemesorguxd) as davranis_toplam FROM users INNER JOIN schools ON schools.id = users.schools INNER JOIN classes on FIND_IN_SET(classes.id, users.classes) > 0 WHERE $filtre_yazisi AND role = :role GROUP BY users.id $siralama_yazisi LIMIT :limit , :sayfada";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT users.register_type,users.name,users.email,users.invite_date,users.register_date,users.update_date,users.id AS studentid,schools.name AS okulad,GROUP_CONCAT(concat(classes.name,'+_+',classes.id) ORDER BY classes.id SEPARATOR '_-_') AS sinifad,($puansorguxd) as davranis_toplam FROM users INNER JOIN schools ON schools.id = users.schools INNER JOIN classes on FIND_IN_SET(classes.id, users.classes) > 0 WHERE role = :role GROUP BY users.id $siralama_yazisi  LIMIT :limit , :sayfada";
                }

                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute(array(":role"=>"student",":limit"=>abs($limit),":sayfada"=>$sayfada));

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
                            <th class="visible-sm visible-md visible-lg">School</th>
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
                                            <strong>School:</strong> <?=$yaz["okulad"]?><br>
                                            <strong>Classes:</strong> <?=$sinifads?><br>
                                            <strong><?=isset($_GET["puan"]) ? ($gelen_filtre_puan != "" || $gelen_filtre_puan != "0" ? ($gelen_filtre_puan == 1 ? "Positive" : ($gelen_filtre_puan == 2 ? "Negative" : "Total" ) ) : "Total" ) : "Total"?> <?=isset($_GET["puan"]) ? ($gelen_filtre_puan != "" || $gelen_filtre_puan != "0" ? ($gelen_filtre_puan == 3 ? "Redeem" : "Behavior") : "Behavior") : "Behavior"?> Points <?=isset($_GET["puan"]) ? ($gelen_filtre_puan != "" || $gelen_filtre_puan != "0" ? ($gelen_filtre_puan == 4 ? "W/O Redeem" : "") : "") : ""?>:</strong> <?=isset($_GET["puan"]) ? ($gelen_filtre_puan != "" || $gelen_filtre_puan != "0" ? ($gelen_filtre_puan == 3 ? abs($yaz["davranis_toplam"]) : $yaz["davranis_toplam"]) : $yaz["davranis_toplam"]) : $yaz["davranis_toplam"]?><br>
                                            <strong>Invite Date:</strong> <?=$yaz["invite_date"]?><br>
                                            <strong>Register Date:</strong> <?php if($yaz["register_date"] != "") { echo $yaz["register_date"]; } else { echo "Kayıt olmadı."; }?><br>
                                            <strong>Update Date:</strong> <?=$yaz["update_date"]?><br>
                                            <strong>Action: </strong> <a href="javascript:;" data-toggle="modal" data-target="#modal-student" class="label label-info ogrenci-duzenle" id="<?=$yaz["studentid"]?>">Edit</a>
                                        </div>
                                        <div class="visible-sm">
                                            <strong>Invite Date:</strong> <?=$yaz["invite_date"]?><br>
                                            <strong>Register Date:</strong> <?php if($yaz["register_date"] != "") { echo $yaz["register_date"]; } else { echo "Kayıt olmadı."; }?><br>
                                            <strong>Update Date:</strong> <?=$yaz["update_date"]?><br>
                                            <strong>Action: </strong> <a href="javascript:;" data-toggle="modal" data-target="#modal-student" class="label label-info ogrenci-duzenle" id="<?=$yaz["studentid"]?>">Edit</a>
                                        </div>
                                    </td>
                                    <td class="visible-sm visible-md visible-lg"><?=$yaz["email"]?></td>
                                    <td class="visible-sm visible-md visible-lg"><?=$yaz["okulad"]?></td>
                                    <td class="visible-sm visible-md visible-lg"><?=$sinifads?></td>
                                    <td class="visible-sm visible-md visible-lg"><?=isset($_GET["puan"]) ? ($gelen_filtre_puan != "" || $gelen_filtre_puan != "0" ? ($gelen_filtre_puan == 3 ? abs($yaz["davranis_toplam"]) : $yaz["davranis_toplam"]) : $yaz["davranis_toplam"]) : $yaz["davranis_toplam"]?></td>
                                    <td class="visible-md visible-lg"><?=$yaz["invite_date"]?></td>
                                    <td class="visible-md visible-lg"><?php if($yaz["register_date"] != "") { echo $yaz["register_date"]; } else { echo "Kayıt olmadı."; }?></td>
                                    <td class="visible-md visible-lg"><?=$yaz["update_date"]?></td>
                                    <td class="visible-md visible-lg"><a href="javascript:;" data-toggle="modal" data-target="#modal-student" class="label label-info ogrenci-duzenle" id="<?=$yaz["studentid"]?>">Edit</a></td>
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
                <div class='notice notice-danger'><strong>Bilgi: </strong>Henüz sisteme kayıtlı öğrenci bulunamadı.</div>
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
    else if($_GET["s"] == "student-infos")
    {
        if(LoginCheckAdmin($DB_con) == false)
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
        $sorguogrenci = $DB_con->prepare("SELECT id,name,classes,schools,parent_name,parent_email,parent_email2,parent_phone,parent_phone2 FROM users WHERE id = :id AND role = :role");
        $sorguogrenci->execute(array(":id"=>$ogrenci,":role"=>"student"));
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
                <label>Classes:</label>
                <?php
                $explodeclasses = explode(",", $yazogrenci["classes"]);
                foreach($explodeclasses as $explodedclasses)
                {
                    $sClasses[] = $explodedclasses;
                }
                implode(",", $sClasses);
                $classesQuery = $DB_con->prepare("SELECT classes.id,classes.name,group_concat(users.name) AS teachersname FROM classes INNER JOIN users ON FIND_IN_SET(users.id,teachers) > 0 WHERE school = :school AND role = :role GROUP BY classes.id");
                $classesQuery->execute(array(":school"=>$yazogrenci["schools"],":role"=>"teacher"));
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
    else if($_GET["s"] == "teachers")
    {
        if(LoginCheckAdmin($DB_con) == true)
        {
            $sorguSay = $DB_con->prepare("SELECT COUNT(id) AS say FROM users WHERE role = :role");
            $sorguSay->execute(array(":role"=>"teacher"));
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

                if(isset($_GET["okul"]))
                {
                    $gelen_filtre_okul = (int)$_GET["okul"];
                    if($gelen_filtre_okul != "" || $gelen_filtre_okul != "0")
                    {
                        $filtre_yazisi .= $prefix . "schools.id = ".$gelen_filtre_okul." ";
                        $prefix = 'AND ';
                    }
                }

                if(isset($_GET["sinif"]))
                {
                    $gelen_filtre_sinif = (int)$_GET["sinif"];
                    if($gelen_filtre_sinif != "" || $gelen_filtre_sinif != "0")
                    {
                        $filtre_yazisi .= $prefix . "FIND_IN_SET(".$gelen_filtre_sinif.", (SELECT group_concat(classes.id) FROM classes WHERE FIND_IN_SET(users.id, classes.teachers))) ";
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
                    $sorguyazisi = "SELECT count(users.id) as say FROM users INNER JOIN schools ON schools.id = users.schools WHERE $filtre_yazisi AND role = :role";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT COUNT(id) AS say FROM users WHERE role = :role";
                }

                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute(array(":role"=>"teacher"));
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
                    $sorguyazisi = "SELECT users.name,users.email,users.invite_date,users.register_date,users.update_date,users.id AS teacherid,schools.name AS okulad,(SELECT group_concat(classes.name) FROM classes WHERE FIND_IN_SET(users.id, classes.teachers)) as sinifad FROM users INNER JOIN schools ON schools.id = users.schools WHERE $filtre_yazisi AND role = :role $siralama_yazisi LIMIT :limit , :sayfada";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT users.name,users.email,users.invite_date,users.register_date,users.update_date,users.id AS teacherid,schools.name AS okulad,(SELECT group_concat(classes.name) FROM classes WHERE FIND_IN_SET(users.id, classes.teachers)) as sinifad FROM users INNER JOIN schools ON schools.id = users.schools WHERE role = :role $siralama_yazisi LIMIT :limit , :sayfada";
                }

                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute(array(":role"=>"teacher",":limit"=>abs($limit),":sayfada"=>$sayfada));

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
                            <th class="visible-sm visible-md visible-lg">School</th>
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
                                            <strong>School:</strong> <?=$yaz["okulad"]?><br>
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
                                    <td class="visible-sm visible-md visible-lg"><?=$yaz["okulad"]?></td>
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
                <div class='notice notice-danger'><strong>Bilgi: </strong>Henüz sisteme kayıtlı öğretmen bulunamadı.</div>
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
    else if($_GET["s"] == "teacher-infos")
    {
        if(LoginCheckAdmin($DB_con) == false)
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
        $sorguogrenci = $DB_con->prepare("SELECT id,name,schools,(SELECT group_concat(classes.id) FROM classes WHERE FIND_IN_SET(users.id, classes.teachers)) as classes FROM users WHERE id = :id AND role = :role");
        $sorguogrenci->execute(array(":id"=>$ogrenci,":role"=>"teacher"));
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
    else if($_GET["s"] == "classes")
    {
        if(LoginCheckAdmin($DB_con) == true)
        {
            $sorguSay = $DB_con->prepare("SELECT COUNT(id) AS say FROM classes");
            $sorguSay->execute();
            $yazSay = $sorguSay->fetch(PDO::FETCH_ASSOC);

            if($yazSay["say"] > 0)
            {
                $filtre_yazisi = "";
                $prefix = "";

                if(isset($_GET["okul"]))
                {
                    $gelen_filtre_okul = (int)$_GET["okul"];
                    if($gelen_filtre_okul != "" || $gelen_filtre_okul != "0")
                    {
                        $filtre_yazisi .= $prefix . "classes.school = ".$gelen_filtre_okul." ";
                        $prefix = 'AND ';
                    }
                }

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
                    $sorguyazisi = "SELECT count(classes.id) as say FROM classes WHERE $filtre_yazisi";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT COUNT(id) AS say FROM classes";
                }

                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute();
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
                    $sorguyazisi = "SELECT classes.gc_id,classes.id,classes.name,(SELECT group_concat(users.name) FROM users WHERE FIND_IN_SET(users.id, classes.teachers) AND users.role = 'teacher') AS teachersx, schools.name AS okulad FROM classes INNER JOIN schools ON schools.id = classes.school WHERE $filtre_yazisi LIMIT :limit , :sayfada";
                }
                else if($filtre_yazisi == "")
                {
                    $sorguyazisi = "SELECT classes.gc_id,classes.id,classes.name,(SELECT group_concat(users.name) FROM users WHERE FIND_IN_SET(users.id, classes.teachers) AND users.role = 'teacher') AS teachersx, schools.name AS okulad FROM classes INNER JOIN schools ON schools.id = classes.school LIMIT :limit , :sayfada";
                }

                $sorgu = $DB_con->prepare($sorguyazisi);
                $sorgu->execute(array(":limit"=>abs($limit),":sayfada"=>$sayfada));

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
                            <th class="visible-sm visible-md visible-lg">School</th>
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
                                            <strong>School:</strong> <?=$yaz["okulad"]?><br>
                                            <strong>Teachers:</strong> <?=$yaz["teachersx"]?><br>
                                            <strong>Register Type:</strong> <?php if($yaz["gc_id"] != NULL) { echo "Google Classroom"; } else { echo 'Manual'; } ?><br>
                                            <strong>Action: </strong> <a href="javascript:;" data-toggle="modal" data-target="#modal-class" class="label label-info sinif-duzenle" id="<?=$yaz["id"]?>">Edit</a>
                                        </div>
                                    </td>
                                    <td class="visible-sm visible-md visible-lg"><?=$yaz["okulad"]?></td>
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
                <div class='notice notice-danger'><strong>Bilgi: </strong>Henüz sisteme kayıtlı sınıf bulunamadı.</div>
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
    else if($_GET["s"] == "class-infos")
    {
        if(LoginCheckAdmin($DB_con) == false)
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
        $sorguogrenci = $DB_con->prepare("SELECT id,name,school,teachers FROM classes WHERE id = :id");
        $sorguogrenci->execute(array(":id"=>$ogrenci));
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
                $classesQuery->execute(array(":role"=>"teacher",":school"=>$yazogrenci["school"]));
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
	else if($_GET["s"] == "create-school")
	{
		if(LoginCheckAdmin($DB_con) == false)
		{
			echo 0;
			exit();
		}
		$school_name = filter_input(INPUT_POST, 'school_name', FILTER_SANITIZE_STRING);
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
		$create_code = generateRandomString();
		$sorgu = $DB_con->prepare("INSERT INTO schools(name,code) VALUES (:school_name, :code)");
		if($sorgu->execute(array(":school_name"=>$school_name,":code"=>$create_code)))
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
    else if($_GET["s"] == "edit-school")
    {
        if(LoginCheckAdmin($DB_con) == false)
        {
            echo 0;
            exit();
        }
        $school = filter_input(INPUT_POST, 'hidden_school_id', FILTER_VALIDATE_INT);
        if($school === false)
        {
            echo 0;
            exit();
        }
        $sorguSchool = $DB_con->prepare("SELECT id FROM schools WHERE id = :id");
        $sorguSchool->execute(array(":id"=>$school));
        if($sorguSchool->rowCount() != 1)
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
        if($sorgu->execute(array(":name"=>$school_name,":datetype"=>$date_type,":id"=>$school)))
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
    else if($_GET["s"] == "edit-school-admin")
    {
        if(LoginCheckAdmin($DB_con) == false)
        {
            echo 0;
            exit();
        }
        $school_admin = filter_input(INPUT_POST, 'hidden_school_admin_id', FILTER_VALIDATE_INT);
        if($school_admin === false)
        {
            echo 0;
            exit();
        }
        $sorguSchoolAdmin = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND role = :role");
        $sorguSchoolAdmin->execute(array(":id"=>$school_admin,":role"=>"admin"));
        if($sorguSchoolAdmin->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $school_admin_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        if(empty($school_admin_name))
        {
            echo 2;
            exit();
        }
        if(strlen($school_admin_name) < 3 || strlen($school_admin_name) > 64)
        {
            echo 3;
            exit();
        }
        $simdi = date('Y-m-d H:i:s');
        $sorgu = $DB_con->prepare("UPDATE users SET name = :name , update_date = :simdi WHERE id = :id AND role = :role");
        if($sorgu->execute(array(":name"=>$school_admin_name,":simdi"=>$simdi,":id"=>$school_admin,":role"=>"admin")))
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
    else if($_GET["s"] == "edit-student")
    {
        if(LoginCheckAdmin($DB_con) == false)
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
        $sorguStudent = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND role = :role");
        $sorguStudent->execute(array(":id"=>$student,":role"=>"student"));
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
        $sorgu = $DB_con->prepare("UPDATE users SET name = :name , classes = :classes , update_date = :simdi , parent_name = :parentname , parent_email = :parentemail , parent_email2 = :parentemail2 , parent_phone = :parentphone , parent_phone2 = :parentphone2 WHERE id = :id AND role = :role");
        if($sorgu->execute(array(":name"=>$name,":classes"=>$implodeclasses,":simdi"=>$simdi,":parentname"=>$parentname,":parentemail"=>$parentemail,":parentemail2"=>$parentemail2,":parentphone"=>$parentphone,":parentphone2"=>$parentphone2,":id"=>$student,":role"=>"student")))
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
    else if($_GET["s"] == "edit-teacher")
    {
        if(LoginCheckAdmin($DB_con) == false)
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
        $sorguStudent = $DB_con->prepare("SELECT id FROM users WHERE id = :id AND role = :role");
        $sorguStudent->execute(array(":id"=>$student,":role"=>"teacher"));
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
        $sorgu = $DB_con->prepare("UPDATE users SET name = :name , update_date = :simdi WHERE id = :id AND role = :role");
        if($sorgu->execute(array(":name"=>$name,":simdi"=>$simdi,":id"=>$student,":role"=>"teacher")))
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
    else if($_GET["s"] == "edit-class")
    {
        if(LoginCheckAdmin($DB_con) == false)
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
        $sorguStudent = $DB_con->prepare("SELECT id FROM classes WHERE id = :id");
        $sorguStudent->execute(array(":id"=>$student));
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
        $sorgu = $DB_con->prepare("UPDATE classes SET name = :name , teachers = :teachers WHERE id = :id");
        if($sorgu->execute(array(":name"=>$name,":teachers"=>$implodeclasses,":id"=>$student)))
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
	else if($_GET["s"] == "invite-teacher")
	{
		if(LoginCheckAdmin($DB_con) == false)
		{
			echo 0;
			exit();
		}
		$school = filter_input(INPUT_POST, 'school', FILTER_VALIDATE_INT);
		if($school == 0)
		{
			echo 2;
			exit();
		}
		if($school === false)
		{
			echo 0;
			exit();
		}
		$sorguSchool = $DB_con->prepare("SELECT id,name FROM schools WHERE id = :id");
		$sorguSchool->execute(array(":id"=>$school));
		if($sorguSchool->rowCount() != 1)
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
//            copy('https://ui-avatars.com/api/?name='.reset($adparcala).'+'.end($adparcala).'&background='.$renkler[0].'&color=fff&bold=true&size=100',  "../".$destin);
			$now = date('Y-m-d H:i:s');
			$invite_token = bin2hex(random_bytes(32));
			$sorgu = $DB_con->prepare("INSERT INTO users(name,email,schools,role,invite_token,invite_date,avatar) VALUES (:name,:email,:schools,:role,:invitetoken,:invitedate,:avatar)");
			if($sorgu->execute(array(":name"=>$teacher_name,":email"=>$teacher_email,":schools"=>$school,":role"=>"teacher",":invitetoken"=>$invite_token,":invitedate"=>$now,":avatar"=>""))) //$destin
			{
				$mail_encoded = rtrim(strtr(base64_encode($teacher_email), '+/', '-_'), '=');
				$yazSchool = $sorguSchool->fetch(PDO::FETCH_ASSOC);
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
											  <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #585858; font-size: 24px; line-height: 32px;">'.$yazSchool["name"].' adlı okula öğretmen olarak davet edildiniz. Aşağıdaki linke tıklayarak sisteme kayıt olabilirsiniz!</span>
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
				if($admin->SendMail($teacher_email,$teacher_name,$message,$subject))
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
			$yazSchool = $sorguSchool->fetch(PDO::FETCH_ASSOC);
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
										  <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #585858; font-size: 24px; line-height: 32px;">'.$yazSchool["name"].' adlı okula öğretmen olarak davet edildiniz. Aşağıdaki linke tıklayarak sisteme kayıt olabilirsiniz!</span>
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
			if($admin->SendMail($teacher_email,$teacher_name,$message,$subject))
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
    else if($_GET["s"] == "invite-school-admin")
    {
        if(LoginCheckAdmin($DB_con) == false)
        {
            echo 0;
            exit();
        }
        $school = filter_input(INPUT_POST, 'school', FILTER_VALIDATE_INT);
        if($school == 0)
        {
            echo 2;
            exit();
        }
        if($school === false)
        {
            echo 0;
            exit();
        }
        $sorguSchool = $DB_con->prepare("SELECT id,name FROM schools WHERE id = :id");
        $sorguSchool->execute(array(":id"=>$school));
        if($sorguSchool->rowCount() != 1)
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
//            copy('https://ui-avatars.com/api/?name='.reset($adparcala).'+'.end($adparcala).'&background='.$renkler[0].'&color=fff&bold=true&size=100',  "../".$destin);
            $now = date('Y-m-d H:i:s');
            $invite_token = bin2hex(random_bytes(32));
            $sorgu = $DB_con->prepare("INSERT INTO users(name,email,schools,role,invite_token,invite_date,avatar) VALUES (:name,:email,:schools,:role,:invitetoken,:invitedate,:avatar)");
            if($sorgu->execute(array(":name"=>$teacher_name,":email"=>$teacher_email,":schools"=>$school,":role"=>"admin",":invitetoken"=>$invite_token,":invitedate"=>$now,":avatar"=>""))) //$destin
            {
                $mail_encoded = rtrim(strtr(base64_encode($teacher_email), '+/', '-_'), '=');
                $yazSchool = $sorguSchool->fetch(PDO::FETCH_ASSOC);
                $message = '
				<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
				<html>
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
				<title>You have been invited as a school admin - Student Behavior Management</title>
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
											  <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #585858; font-size: 24px; line-height: 32px;">'.$yazSchool["name"].' adlı okulun yöneticisi olarak davet edildiniz. Aşağıdaki linke tıklayarak sisteme kayıt olabilirsiniz!</span>
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
                $subject = "You've been invited as a school admin - Student Behavior Management";
                if($admin->SendMail($teacher_email,$teacher_name,$message,$subject))
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
            if($yazUye["role"] != "admin")
            {
                echo 6;
                exit();
            }
            $mail_encoded = rtrim(strtr(base64_encode($teacher_email), '+/', '-_'), '=');
            $yazSchool = $sorguSchool->fetch(PDO::FETCH_ASSOC);
            $message = '
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
			<title>You have been invited as a school admin - Student Behavior Management</title>
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
										  <span style="font-family: Source Sans Pro, Arial, Tahoma, Geneva, sans-serif; color: #585858; font-size: 24px; line-height: 32px;">'.$yazSchool["name"].' adlı okulun yöneticisi olarak davet edildiniz. Aşağıdaki linke tıklayarak sisteme kayıt olabilirsiniz!</span>
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
            $subject = "You've been invited as a school admin - Student Behavior Management";
            if($admin->SendMail($teacher_email,$teacher_name,$message,$subject))
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
    else if($_GET["s"] == "get-report")
    {
        if(LoginCheckAdmin($DB_con) == false)
        {
            echo 0;
            exit();
        }
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
                    $sorgusinifid = $DB_con->prepare("SELECT id,name FROM classes WHERE id = :id");
                    $sorgusinifid->execute(array(":id"=>$gelensinif));
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
            ?>
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><div class="alert alert-info">Henüz öğrenciye ait herhangi bir sınıf seçmediniz.</div></div>
            <?php
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
        $sorguteachers = $DB_con->prepare("SELECT users.name,users.avatar FROM users INNER JOIN classes ON FIND_IN_SET(users.id, classes.teachers) > 0 WHERE role = :role AND ($sinifsorguyazisi2) GROUP BY users.id");
        $sorguteachers->execute(array(":role"=>"teacher"));
        ?>
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
            <div class="info-box hover-zoom-effect">
                <div class="icon bg-light-blue">
                    <i class="material-icons">school</i>
                </div>
                <div class="content">
                    <div class="text nowrapwithellipsis">TOTAL BEHAVIOR POINT</div>
                    <div class="number"><b class="col-light-blue"><?=$yazpuans["toplampuans"] != NULL ? $yazpuans["toplampuans"] : 0?><small class="font-15"> (W/O Redeem: <?=$yazpuans["toplampuans2"] != NULL ? $yazpuans["toplampuans2"] : 0?>)</small></b></div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
            <div class="info-box">
                <div class="icon">
                    <div class="chart chart-pie" data-chartcolor="orange"><?=$b?>,<?=$a?></div>
                </div>
                <div class="content">
                    <div class="text nowrapwithellipsis">POSITIVE / NEGATIVE BEHAVIOR POINTS</div>
                    <div class="number"><b class="col-green"><?=$b?></b> / <b class="col-red">-<?=$a?></b> Points</div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div class="panel panel-default panel-post">
                <div class="panel-heading">
                    <h4>Teacher(s):</h4>
                </div>
                <?php
                while($yazteachers = $sorguteachers->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <div class="panel-heading">
                        <div class="media">
                            <div class="media-left">
                                <a href="javascript:;"><img src="../<?=$yazteachers["avatar"]?>"></a>
                            </div>
                            <div class="media-body">
                                <h4 class="media-heading">
                                    <a href="javascript:;"><?=$yazteachers["name"]?></a>
                                </h4>
                                Teacher
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <?php
            $sorguHistory = $DB_con->prepare("SELECT id,name,point,type,description,teacher,date,class_id FROM feedbacks_students WHERE ($sinifsorguyazisi) AND student_id = :student $siralama_yazisi ORDER BY id DESC");
            $sorguHistory->execute(array(":student"=>$ogrenciid));
            if($sorguHistory->rowCount() > 0)
            {
                ?>
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
                    <?php
                    while($yazHistory = $sorguHistory->fetch(PDO::FETCH_ASSOC))
                    {
                        $sorguTeacher = $DB_con->prepare("SELECT name FROM users WHERE id = :id AND role = :role");
                        $sorguTeacher->execute(array(":id"=>$yazHistory["teacher"],":role"=>"teacher"));
                        $yazTeacher = $sorguTeacher->fetch(PDO::FETCH_ASSOC);
                        $sorguSinifad = $DB_con->prepare("SELECT name FROM classes WHERE id = :id");
                        $sorguSinifad->execute(array(":id"=>$yazHistory["class_id"]));
                        $yazSinifad = $sorguSinifad->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <tr>
                            <td><?=$yazHistory["name"]?></td>
                            <td><?=$yazHistory["type"] == 1 ? "<b class='col-green'>Positive</b>" : ($yazHistory["type"] == 2 ? "<b class='col-red'>Negative</b>" : ($yazHistory['type'] == 3 ? "<b class='col-blue'>Redeem</b>" : ""))?></td>
                            <td><?=$yazHistory["type"] == 3 ? abs($yazHistory["point"]) : $yazHistory["point"] ?></td>
                            <td><?=$yazSinifad["name"]?></td>
                            <td><?=$yazTeacher["name"]?></td>
                            <td><?=$yazHistory["date"]?></td>
                            <td><?=$yazHistory["description"]?></td>
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
                if($siralama_yazisi == "") {
                    echo '<div class="alert alert-warning">Henüz öğrenciye verilen davranış notu bulunmamakta.</div>';
                }
                else
                {
                    echo '<div class="alert alert-warning">Seçtiğiniz zaman aralığına göre öğrenciye verilen davranış notu bulunamadı.</div>';
                }
            }
            ?>
        </div>
        <?php
    }
    else if($_GET["s"] == "school-infos")
    {
        if(LoginCheckAdmin($DB_con) == false)
        {
            echo 0;
            exit();
        }
        $school = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($school === false)
        {
            echo 0;
            exit();
        }
        $querySchool = $DB_con->prepare("SELECT id,name,date_type FROM schools WHERE id = :id");
        $querySchool->execute(array(":id"=>$school));
        if($querySchool->rowCount() != 1)
        {
            echo 0;
            exit();
        }
        $writeSchool = $querySchool->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="modal-header">
            <h4 class="modal-title">Editing School: <?=$writeSchool["name"]?></h4>
        </div>
        <div class="modal-body">
            <form id="Edit-School-Form">
                <div class="form-group">
                    <label for="name">School Name:</label>
                    <div class="form-line">
                        <input class="form-control" name="name" id="name" type="text" value="<?=$writeSchool["name"]?>">
                    </div>
                </div>
                <?php
                $dateTypeQuery = $DB_con->prepare("SELECT date_type FROM schools WHERE id = :id");
                $dateTypeQuery->execute(array(":id"=>$school));
                $dateType = $dateTypeQuery->fetch(PDO::FETCH_ASSOC);
                ?>
                <div class="form-group">
                    <label for="date_type">Date Display Type:</label>
                    <div class="form-line">
                        <select class="form-control" name="date_type" id="date_type">
                            <option value="1" <?php if ($dateType['date_type'] === 1) { ?> selected <?php } ?>>DD/MM/YYYY H:i:s</option>
                            <option value="2" <?php if ($dateType['date_type'] === 2) { ?> selected <?php } ?>>MM/DD/YYYY H:i:s</option>
                            <option value="3" <?php if ($dateType['date_type'] === 3) { ?> selected <?php } ?>>YYYY/MM/DD H:i:s</option>
                            <option value="4" <?php if ($dateType['date_type'] === 4) { ?> selected <?php } ?>>Month D, Yr H:i:s</option>
                            <option value="5" <?php if ($dateType['date_type'] === 5) { ?> selected <?php } ?>>D Month, Yr H:i:s</option>
                        </select>
                    </div>
                </div>
                <input type="hidden" name="hidden_school_id" id="hidden_school_id" value="<?=$writeSchool["id"]?>">
                <div id="Edit-School-Result"></div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block btn-lg waves-effect Edit-School-Button">Edit School</button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Close</button>
        </div>
        <?php
    }
}
?>