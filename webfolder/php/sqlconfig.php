<?php
	/* MySQL Zugangsdaten */
	$db_ip = "localhost";
	$db_user = "oacbank";
	$db_pass = "bank";
	$dbname = "oacbank";
	
	$db = new mysqli($db_ip, $db_user, $db_pass, $dbname);
	$dberror = false;
	if($db->connect_error){
		$dberror = "<p>Konnte keine verbindung mit der Datenbank aufbauen.<br>".$db->connect_error."</p>";
	}else
	if(!$db->query("SET NAMES 'utf8'")){
		$dberror = "<p>Fehler beim Setzen des Datenbankcharsets.</p>";
	}
	
?> 
