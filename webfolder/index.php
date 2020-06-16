<?php
	/* Config */
	$APPNAME = "OstalbCity Banksystem";
	$VERSION = "v1.0a";
	$CURRENCY = " G";
	include_once("php/html_helpers.php");
	session_start();

	$index = true;
	if( isset($_SESSION['uid']) ){
		//Eingeloggt
		function check_auth (){
			if(!isset($_SESSION['uid']))
				die("Access Denied");
		}
		if( isset($_GET['logout']) ){
			session_unset(); 
			session_destroy(); 
			if(isset($_GET['mobile']))
				header("Location: ?login&mobile&loggedout");
			else
				header("Location: ?login&loggedout");
		}else if(isset($_GET['login'])){
			header("Location: .");
		}else if(isset($_GET['mobile'])){
			header("Location: transaction.php");
		}else if($_SESSION['uid']==2 || isset($_GET['druck'])){
			include("druck.php");
		}else{
			include("admin.php");
		}
	}else{
		//Nicht eingeloggt
		if( isset($_GET['login']) ){
			include("login.php");
		}else{
			header("Location: ?login");
		}
	}
?>