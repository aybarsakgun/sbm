<?php
if(!defined('VAL1')) {
   die('Security');
}
require_once 'class.admin.php';
SessionStartAdmin();
if(empty($_SESSION['sbmt'])) {
	$_SESSION['sbmt'] = bin2hex(random_bytes(32));
}
$sbmtoken = $_SESSION['sbmt'];
if(LoginCheckAdmin($DB_con) == false) 
{
	header("Location: signin");
	exit();
}
$page_request = filter_input(INPUT_GET, 'pr', FILTER_SANITIZE_STRING);
$base_request = filter_input(INPUT_GET, 'br', FILTER_SANITIZE_STRING);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="Admin panel of Student Behavior Management">
		<meta name="author" content="Student Behavior Management">
		<meta name="sbmtoken" content="<?=$sbmtoken?>">
		<title>Admin Panel - Student Behavior Management</title>
		<link href="../img/favicon.png" rel="icon" type="image/png">
		<link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

		<link href="../plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">

		<link href="../plugins/node-waves/waves.min.css" rel="stylesheet" />

		<link href="../plugins/animate-css/animate.min.css" rel="stylesheet" />	
		
		<link href="../css/style.css" rel="stylesheet">

		<link href="../css/theme-orange.min.css" rel="stylesheet" />

        <?php if($page_request == "report") { ?><link href="../plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.min.css" rel="stylesheet"><link href="../plugins/jquery-datatable/skin/bootstrap/css/responsive.bootstrap.min.css" rel="stylesheet"><?php } ?>
	</head>
	<body class="theme-orange">
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
		<nav class="navbar" style="z-index: 11!important;">
			<div class="container-fluid">
				<div class="navbar-header">
					<a href="javascript:void(0);" class="bars"></a>
					<a class="navbar-brand" href="home">SBManagement</a>
				</div>
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button"
                           aria-expanded="true">
                            <i class="material-icons">add</i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="create-school" class="waves-effect waves-block">Create School</a></li>
                            <li><a href="invite-school-admin" class="waves-effect waves-block">Invite School Admin</a></li>
                            <li><a href="invite-teacher" class="waves-effect waves-block">Invite Teacher</a></li>
                        </ul>
                    </li>
                </ul>
			</div>
		</nav>
		<section>
			<aside id="leftsidebar" class="sidebar" style="top: 70px!important;height:calc(100vh - 70px);">
				<div class="user-info">
					<div class="image">
						<img src="../img/user.png" width="48" height="48" alt="User" />
					</div>
					<div class="info-container">
                        <div class="name">SBM Admin</div>
                        <div class="name"><small>System Admin</small></div>
						<div class="email"><?=$_SESSION['admin_mail']?></div>
					</div>
				</div>
				<div class="menu">
					<ul class="list">
						<li>
							<a href="javascript:void(0);" class="menu-toggle">
								<i class="material-icons">verified_user</i>
								<span>Profile</span>
							</a>
							<ul class="ml-menu">
								<li>
									<a href="javascript:;" class="LogOutButton">Log Out</a>
								</li>
							</ul>
						</li>
						<li>
							<a href="home">
								<i class="material-icons">home</i>
								<span>Home</span>
							</a>
						</li>
                        <li>
                            <a href="security">
                                <i class="material-icons">security</i>
                                <span>Security</span>
                            </a>
                        </li>
                        <li>
                            <a href="schools">
                                <i class="material-icons">business</i>
                                <span>Schools</span>
                            </a>
                        </li>
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
					</ul>
				</div>
				<div class="legal">
					<div class="copyright">
						&copy; 2020 <a href="javascript:;">SBManagement</a>
					</div>
				</div>
			</aside>
		</section>