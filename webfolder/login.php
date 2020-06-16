<?php 
	if( isset($_POST['passwd']) ){
		/* Query It */
		include_once('php/sqlconfig.php');
		$result = $db->query("SELECT `key`, `val` FROM config WHERE `key` = 'betreuer_pin' OR `key` = 'kinder_pin' OR `key` = 'presse_pin'");
		if(!$result){
			header("Location: ?login&fail=1&msg=".mysqli_error($db));
			exit;
		}
		/* Check it */
		$pins = array();
		while ($row = $result->fetch_assoc())
			$pins[$row['key']] = $row['val'];
		if($pins['betreuer_pin'] == $pins['kinder_pin']){
			die(html_err(false, "Systemfehler: Pineinstellungen identisch. Bitte wenden Sie ich an Ihren Systemadministrator."));
		}
		if(in_array($_POST['passwd'], $pins)){
			if($_POST['passwd'] == $pins['betreuer_pin']){
				$_SESSION['uid'] = 1;
			}else if($_POST['passwd'] == $pins['presse_pin']){
				$_SESSION['uid'] = 2;
			}else{
				$_SESSION['uid'] = 0;
			}
			if(isset($_GET['mobile']))
				header("Location: transaction.php");
			else
				header("Location: .");
		}else{
			header("Location: ?login&fail=0");
			exit;
		}
	}
	
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $APPNAME. " ".$VERSION; ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link href="css/bootstrap.min.css" rel="stylesheet" />
		<link href="css/login-form.css" rel="stylesheet" />
	</head>
	<body>
		<form class="form-signin" method="post">
			<?php if(!isset($_GET['mobile'])){ ?>
			<h2 class="title"><?php echo $APPNAME; ?> <span class="text-muted"><?php echo $VERSION; ?></span></h2>
			<?php } ?>
			<?php
				if(isset($_GET['loggedout'])){
					echo '<div class="alert alert-info alert-dismissible fade show" role="alert">'.
							'Sie wurden abgemeldet'.
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close">'.
							'<span aria-hidden="true">&times;</span>'.
							'</button>'.
							'</div>';
					
				}else if(isset($_GET['fail'])){ 
					if($_GET['fail']=="0"){
						echo html_err(true, "Benutzername oder Passwort ungültig.");
					}else if($_GET['fail']=="1"){
						echo html_err(true, "Datenbankfehler. Bitte kontakieren sie Ihren Systemadministrator.");
					}else{
						echo html_err(true, "Fehler beim Anmelden.");
					}
				}
			?>
			<h1 class="h3 mb-3 font-weight-normal">Bitte Anmelden</h1>
			<label for="inputPassword" class="sr-only">PIN-Code</label>
			<input name="passwd" type="password" id="inputPassword" class="form-control" placeholder="PIN-Code" required>
			<br/>
			<button name="login" class="btn btn-lg btn-primary btn-block" type="submit">Anmelden</button>
		</form>
		<script src="js/jquery-3.3.1.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
	</body>
</html>
