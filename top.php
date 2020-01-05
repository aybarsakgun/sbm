<?php
$page_request = filter_input(INPUT_GET, 'pr', FILTER_SANITIZE_STRING);
if($page_request == 'sync-gc') {
    $base_request = "gc";
}
require_once 'database.php';
require_once 'functions.php';
SessionStartUser();
if(empty($_SESSION['sbmt'])) {
	$_SESSION['sbmt'] = bin2hex(random_bytes(32));
}
$sbmtoken = $_SESSION['sbmt'];

if(LoginCheckUser($DB_con) == false) 
{
	header("Location: signin");
	exit();
}



$sorguUye = $DB_con->prepare("SELECT id,name,role,schools,email,avatar FROM users WHERE google_id = :gid");
$sorguUye->execute(array(":gid"=>LoginCheckUser($DB_con)));
$yazUye = $sorguUye->fetch(PDO::FETCH_ASSOC);
$uyevtid = $yazUye["id"];
$uyerol = $yazUye["role"];
$uyeokul = $yazUye["schools"];
$uyead = $yazUye["name"];
$uyemail = $yazUye["email"];
$uyeavat = $yazUye["avatar"];

if($page_request == "class") {
    $sinifidasdasd = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $sorgusinifrenk = $DB_con->prepare("SELECT name,color FROM classes WHERE id = :id");
    $sorgusinifrenk->execute(array(":id"=>$sinifidasdasd));
    if($sorgusinifrenk->rowCount() == 1)
    {
        $yazsinifrenk = $sorgusinifrenk->fetch(PDO::FETCH_ASSOC);
        $sinifrenk = $yazsinifrenk["color"];
        $sbmbaslik = $yazsinifrenk["name"];
        $classlink = "exist";
    }
    else
    {
        $sinifrenk = "orange";
        $sbmbaslik = "";
        $classlink = "";
    }
}
else
{
    $sinifrenk = "white";
    $sbmbaslik = "";
    $classlink = "";
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no">
		<meta name="description" content="User panel of Student Behavior Management">
		<meta name="author" content="Student Behavior Management">
		<meta name="sbmtoken" content="<?=$sbmtoken?>">
		<title>Home - Student Behavior Management</title>
		<link href="img/favicon.png" rel="icon" type="image/png">
		<link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

		<link href="plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">

		<link href="plugins/node-waves/waves.min.css" rel="stylesheet" />

		<link href="plugins/animate-css/animate.min.css" rel="stylesheet" />	
		
		<link href="css/style.css" rel="stylesheet">

		<link href="css/all-themes.min.css" rel="stylesheet" />

        <?php if($page_request == "class" || $page_request == "send-messages" || $page_request == "students" || $page_request == "redeem-items" || !isset($page_request)) { ?><link href="plugins/oldsweetalert/sweetalert.min.css" rel="stylesheet" /><?php } ?>

        <?php if($page_request == "report" || $page_request == "class" || $page_request == "redeem-items") { ?><link href="plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.min.css" rel="stylesheet"><link href="plugins/jquery-datatable/skin/bootstrap/css/responsive.bootstrap.min.css" rel="stylesheet"><?php } ?>

        <?php if($page_request == "report") { ?><link href="plugins/jquery-datepicker/datepicker.min.css" rel="stylesheet" /><?php } ?>
	</head>
	<body class="theme-<?=$sinifrenk?>">
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
		<nav class="navbar">
			<div class="container-fluid">
				<div class="navbar-header">
					<a href="javascript:void(0);" class="bars"></a>
                    <?php
                    if($sbmbaslik != "")
                    {
                        ?>
                        <a class="navbar-brand" href="home"><?=$sbmbaslik?></a>
                        <?php
                    }
                    else if($sbmbaslik == "")
                    {
                        ?>
                        <a class="navbar-brand" href="home">SBManagement</a>
                        <?php
                    }
                    ?>
				</div>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="javascript:;" id="fullscreen-toggle" class="hidden-xs hidden-sm"><i class="material-icons">fullscreen</i></a></li>
                    <?php
                    if($uyerol != "student") {
                        if(isset($sinifidasdasd) && $uyerol == "teacher") {
                            ?>
                            <li class="dropdown">
                                <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-expanded="true">
                                    <i class="material-icons">sort</i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:;" class="waves-effect waves-block sortTriggerButton" id="name-sort">By name</a></li>
                                    <li><a href="javascript:;" class="waves-effect waves-block sortTriggerButton" id="lastname-sort">By last name</a></li>
                                    <li><a href="javascript:;" class="waves-effect waves-block sortTriggerButton" id="point-sort">By highest</a></li>
                                </ul>
                            </li>
                            <?php
                        }
                        ?>
                        <li class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-expanded="true">
                                <i class="material-icons">add</i>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if ($uyerol == "admin") {
                                    echo '<li><a href="invite-teacher" class="waves-effect waves-block">Invite Teacher</a></li>';
                                } ?>
                                <li><a href="create-class" class="waves-effect waves-block">Create Class</a></li>
                                <li><a href="add-student" class="waves-effect waves-block">Add Student</a></li>
                                <li><a href="import-student" class="waves-effect waves-block">Import Student</a></li>
                                <li><a href="sync-gc" class="waves-effect waves-block">Sync Google Classroom</a></li>
                                <?php if(isset($sinifidasdasd)) { ?><li><a href="send-messages?id=<?=$sinifidasdasd?>" class="waves-effect waves-block">Send Messages to Parents</a></li><?php } ?>
                                <?php if(isset($classlink) && $classlink != "" && $uyerol == "teacher") { ?><li><a href="edit-class-<?=$sinifidasdasd?>" class="waves-effect waves-block">Edit This Class</a></li><?php } ?>
                            </ul>
                        </li>
                        <?php
                    }
                    if($uyerol != "admin") {
                        ?>
                        <li class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle notification-icon"
                               data-toggle="dropdown" role="button" aria-expanded="true">
                                <i class="material-icons">forum</i>
                                <span class="label-count" style="display:none;"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="header">MESSAGES</li>
                                <li class="body">
                                    <ul class="menu" id="okunmamiskonusmalar" style="list-style:none!important;">

                                    </ul>
                                </li>
                                <li class="footer">
                                    <a href="conversations" class="waves-effect waves-block">View All Conversations</a>
                                </li>
                            </ul>
                        </li>
                        <?php
                    }
                    ?>
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true" style="padding-top: 0;padding-bottom: 0;margin-top: 16px;height:39px;">
                            <img src="<?=$uyeavat?>" width="38" height="38" class="img-circle uye-avatar-yeri" alt="<?=$uyead?>" style="vertical-align:unset!important;"/>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">PROFILE</li>
                            <li class="body">
                                <ul class="menu tasks" style="overflow: hidden; width: auto; height: auto; padding-left:15px; padding-right:15px;">
                                    <li class="text-center" style="list-style:none;">
                                        <img src="<?=$uyeavat?>" width="80" height="80" class="img-circle m-t-10" alt="<?=$uyead?>"/>
                                        <div class="m-t-10 m-b-10"><strong><?=$uyead?></strong></div>
                                        <div class="m-b-10 text-muted"><?=$uyemail?></div>
                                        <div class="m-b-10"><small><b><?php if($uyerol == "student") { echo "Student"; } else if($uyerol == "teacher") { echo "Teacher"; } else if($uyerol == "admin") { echo "School Admin"; } ?></b></small></div>
                                    </li>
                                </ul>
                            </li>
                            <li class="footer">
                                <a href="javascript:;" class="waves-effect waves-block LogOutButton">Log Out</a>
                            </li>
                        </ul>
                    </li>
                </ul>
			</div>
		</nav>
		<section>
			<aside id="leftsidebar" class="sidebar">
				<div class="menu">
					<ul class="list">
						<li>
							<a href="home">
								<i class="material-icons">home</i>
								<span>Home</span>
							</a>
						</li>
						<?php
						if($uyerol == "student")
						{
						    echo '<li><a href="conversations"><i class="material-icons">forum</i><span class="conversations-alert">Conversations</span></a></li><li><a href="redeem-items"><i class="material-icons">shopping_cart</i><span>Redeem Items</span></a></li><li class="header">Classes</li>';
							$sorgux = $DB_con->prepare("SELECT classes.id,classes.name,classes.color,classes.status FROM users INNER JOIN classes ON FIND_IN_SET(classes.id, users.classes) WHERE users.id = :uyeid AND schools = :school ORDER BY classes.id ASC");
							$sorgux->execute(array(":uyeid"=>$uyevtid,":school"=>$uyeokul));
                            while($yaz = $sorgux->fetch(PDO::FETCH_ASSOC))
                            {
                                ?>
                                <li>
                                    <?php if($yaz["status"] == 1) { ?>
                                    <a href="class-<?=seo($yaz["name"])?>-<?=$yaz["id"]?>">
                                        <i class="material-icons col-<?=$yaz["color"]?>">stars</i>
                                        <span><?=$yaz["name"]?></span>
                                    </a>
                                    <?php } else if($yaz["status"] == 2) { ?>
                                    <a href="javascript:;">
                                        <i class="material-icons col-<?=$yaz["color"]?>">stars</i>
                                        <span><?=$yaz["name"]?> (A)</span>
                                    </a>
                                    <?php } ?>
                                </li>
                                <?php
                            }
						}
						else if($uyerol == "teacher")
						{
						    echo '<li><a href="sync-gc"><img src="img/google.svg" width="24" height="24" style="margin-top:4px;"><span>Sync Google Classroom</span></a></li><li><a href="conversations"><i class="material-icons">forum</i><span class="conversations-alert">Conversations</span></a></li><li><a href="send-messages"><i class="material-icons">email</i><span class="conversations-alert">Send Messages to Parents</span></a></li><li><a href="redeem-items"><i class="material-icons">shopping_cart</i><span>Redeem Items</span></a></li><li class="header">Classes</li>';
                            $sorgu = $DB_con->prepare("SELECT id,name,color,status FROM classes WHERE FIND_IN_SET(:uyeid,teachers) AND school = :school ORDER BY id ASC");
                            $sorgu->execute(array(":uyeid"=>$uyevtid,":school"=>$uyeokul));
                            while($yaz = $sorgu->fetch(PDO::FETCH_ASSOC))
                            {
                                ?>
                                <li>
                                    <?php if($yaz["status"] == 1) { ?>
                                        <a href="class-<?=seo($yaz["name"])?>-<?=$yaz["id"]?>">
                                            <i class="material-icons col-<?=$yaz["color"]?>">stars</i>
                                            <span><?=$yaz["name"]?></span>
                                        </a>
                                    <?php } else if($yaz["status"] == 2) { ?>
                                        <a href="javascript:;">
                                            <i class="material-icons col-<?=$yaz["color"]?>">stars</i>
                                            <span><?=$yaz["name"]?> (A)</span>
                                        </a>
                                    <?php } ?>
                                </li>
                                <?php
                            }
						}
						else if($uyerol == "admin")
                        {
                            ?>
                            <li><a href="sync-gc"><img src="img/google.svg" width="24" height="24" style="margin-top:4px;"><span>Sync Google Classroom</span></a></li>
                            <li>
                                <a href="classes">
                                    <i class="material-icons">class</i>
                                    <span>Classes</span>
                                </a>
                            </li>
                            <li>
                                <a href="teachers">
                                    <i class="material-icons">business_center</i>
                                    <span>Teachers</span>
                                </a>
                            </li>
                            <li>
                                <a href="students">
                                    <i class="material-icons">school</i>
                                    <span>Students</span>
                                </a>
                            </li>
                            <li><a href="redeem-items"><i class="material-icons">shopping_cart</i><span>Redeem Items</span></a></li>
                            <?php
                        }
						?>
					</ul>
				</div>
				<div class="legal">
					<div class="copyright">
						&copy; 2019 <a href="javascript:;">SBManagement</a>
					</div>
				</div>
			</aside>
		</section>