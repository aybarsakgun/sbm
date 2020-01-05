<?php
require_once 'class.admin.php';
SessionStartAdmin();
if(empty($_SESSION['sbmt'])) {
	$_SESSION['sbmt'] = bin2hex(random_bytes(32));
}
$sbmtoken = $_SESSION['sbmt'];
if(LoginCheckAdmin($DB_con) == true) 
{
	header("Location: home");
	exit();
}
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
				<img src="../img/sbmlogo.png" width="200">
			</div>
			<div class="card">
				<div class="body">
					<form id="SignInForm" role="form">
						<div class="input-group">
							<span class="input-group-addon">
								<i class="material-icons">email</i>
							</span>
							<div class="form-line">
								<input type="email" class="form-control" name="admin_mail" id="admin_mail" placeholder="E-Mail Address" autofocus required>
							</div>
						</div>
						<div class="input-group">
							<span class="input-group-addon">
								<i class="material-icons">lock</i>
							</span>
							<div class="form-line">
								<input type="password" class="form-control" name="admin_password" id="admin_password" placeholder="Password" required>
							</div>
						</div>
						<div class="input-group" style="margin-bottom:0!important;">
							<button class="btn btn-block bg-orange waves-effect LogInButton" type="submit">Sign In</button>
						</div>
					</form>
				</div>
				<div id="InfoMessage"></div>
			</div>
		</div>
		<script src="../plugins/jquery/jquery.min.js"></script>
		<script src="../plugins/bootstrap/js/bootstrap.min.js"></script>
		<script src="../plugins/node-waves/waves.min.js"></script>
		<script src="../js/sign-in.js"></script>
		<script src="../js/sha.min.js"></script>
		<script>
		jQuery(document).ready(function($)
		{
			$.ajaxSetup({
				headers: { 'sbmtoken': $('meta[name="sbmtoken"]').attr('content') }
			});
			$("#SignInForm").on('submit',(function(e)
			{
				e.preventDefault();
				var admin_password = $("#admin_password").val();
				if($("input#aPass").length)
				{
					$("input#aPass").val(hex_sha512(admin_password));
				}
				else
				{
					$('<input>').attr({
						type: 'hidden',
						id: 'aPass',
						name: 'aPass',
						value: hex_sha512(admin_password)
					}).appendTo(this);
				}
				$("#admin_password").val("");
				$('.LogInButton').prop('disabled', true);
				$('.LogInButton').html("Signing In...");
				$("#InfoMessage").empty();
				$.ajax(
				{
					url: "sign-in",
					type: "POST",
					data:  new FormData(this),
					contentType: false,
					cache: false,
					processData:false,
					success: function(data)
					{
						setTimeout(function()
						{
							$('.LogInButton').prop('disabled', false);
							$('.LogInButton').html("Sign In");
							if(data == 1)
							{
								$("#InfoMessage").html("<div class='alert alert-success mb-0' role='alert'>You have successfully signed in. Redirecting...</div>");
								$("#admin_password").val("");
								$("input#aPass").val("");
								$("#SignInForm").trigger("reset");
								setTimeout(Redirect, 1000);
							}
							if(data == 2)
							{
								$("#InfoMessage").html("<div class='alert alert-danger mb-0' role='alert'>You entered incorrect information. Please try again.</div>");
								$("#admin_password").val("");
								$("input#aPass").val("");
								$("#SignInForm").trigger("reset");
							}
							if(data == 4)
							{
								$("#InfoMessage").html("<div class='alert alert-danger mb-0' role='alert'>Please fill in the form completely.</div>");
							}
							if(data == 3)
							{
								$("#InfoMessage").html("<div class='alert alert-danger mb-0' role='alert'>You have exceeded the incorrect password entry limit. Try again in half an hour.</div>");
								$("#admin_password").val("");
								$("input#aPass").val("");
								$("#SignInForm").trigger("reset");
							}
							if(data == 0)
							{
								$("#InfoMessage").html("<div class='alert alert-danger mb-0' role='alert'>Please enter a valid e-mail address.</div>");
								$("#admin_password").val("");
								$("input#aPass").val("");
								$("#SignInForm").trigger("reset");
							}
						}, 1000);
					}	 						
				});
			}));
			function Redirect() 
			{
				window.location.href = 'home';
			}
		});
		</script>
	</body>
</html>