<?php 
	//Do not call this file directly!
	check_auth ();
	if($_SESSION['uid'] != 0 && $_SESSION['uid'] != 1)
		die("Zugriff Verweigert.");

	$PAGE = -1;
	if(isset($_GET['p']))
		$PAGE = (int)$_GET['p'];

	$LOGGING_ENABLED = true;
?>
<!DOCTYPE HTML>
<html>
	<head>
		<!--
			Developed by Dargen_ @ wpo-systems.de
			13.07.2019 01:18
		 -->
		<title><?php echo $APPNAME; ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<link href="css/bootstrap.min.css" rel="stylesheet" />
		<link href="css/fontawesome.css" rel="stylesheet" />
		<script src="js/jquery-3.3.1.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/notify.js"></script>
		<script src="js/metisMenu.js"></script>
		<script type="text/javascript" charset="utf8" src="js/jquery.dataTables.js"></script>
		<script src="js/dataTables.bootstrap4.min.js"></script>
		<style>
			.noselect {
			-webkit-touch-callout: none; /* iOS Safari */
				-webkit-user-select: none; /* Safari */
				-khtml-user-select: none; /* Konqueror HTML */
				-moz-user-select: none; /* Firefox */
					-ms-user-select: none; /* Internet Explorer/Edge */
						user-select: none; /* Non-prefixed version, currently
											supported by Chrome and Opera */
			}
			table{
				width:100%;
			}
			#data-table_filter{
				float:right;
			}
			#data-table_paginate{
				float:right;
			}
			@media only print {
				#data-table_length,
				#data-table_paginate,
				#data-table_filter{
					display: none;
				}
			}

			label {
				display: inline-flex;
				margin-bottom: .5rem;
				margin-top: .5rem;
			
			}
		</style>
	</head>
	<body <?php if($_SESSION['uid']!=1) echo 'class="noselect"'; ?>>
		<!-- Hi schatzie, hier geht SQL-Injection. Have fun :3 -->
		<nav class="navbar d-print-none navbar-expand-lg navbar-dark <?php if($_SESSION['uid'] == 1) echo "bg-dark"; else echo "bg-primary"; ?>">
			<a class="navbar-brand" href="#"><?php echo $APPNAME; if($_SESSION['uid'] == 1) echo " <span class='text-muted'>Betreuermodus</span>"; ?></a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse justify-content-end" id="navbarNav">
				<ul class="navbar-nav">
					<li class="nav-item">
						<a class="nav-link" href="?logout">Abmelden</a>
					</li>
				</ul>
			</div>
		</nav>
		<div class="container-fluid">
			<br/>
			<!-- begin php init-->
			<?php 

			/* DB Connection */
			include_once('php/sqlconfig.php');
			if($dberror)
				die(html_err(false, "Fehler beim Verbinden mit der Datenbank: ". $dberror));
			

			/* Pack die in SQL definierten Gruppen in ein fucking array du hurensohn */
			$GRUPPEN = array();
			$result = $db->query("DESCRIBE accounts type");
			if(!$result)
				die(html_err(false, "Datenbankfehler: ". mysqli_error($db)));
			$result = explode("','", substr( $result->fetch_assoc()['Type'], 6, -2));
			for($i = 0; $i<count($result); $i++){
				array_push($GRUPPEN, $result[$i]);
			}

			/* Lade die Einstellungen */
			$CONFIG = array();
			$result = $db->query("SELECT `key`, `val` FROM config");
			if(!$result)
				die(html_err(false, "Datenbankfehler: ". mysqli_error($db)));
			while($row = $result->fetch_assoc())
				$CONFIG[$row['key']] = $row['val'];
			
			/* Ban */
			if(isset($_GET['ban']) && is_numeric($_GET['ban']) && $_SESSION['uid'] == 1){
				$result = $db->query("UPDATE `accounts` SET `banned` = '1' WHERE `accounts`.`ID` = ".(int)$_GET['ban']);
				if($result)
					echo "<script>$.notify('Konto #".sprintf('%05d', (int)$_GET['ban'])." ist jetzt gesperrt.', 'success');</script>";
				else
					echo "<script>$.notify('Fehler: Konnte Konto #".sprintf('%05d', (int)$_GET['ban'])." nicht sperren.');</script>";
				if($LOGGING_ENABLED){
					$result = $db->query("INSERT INTO `log` (`typ`, `von`, `an`, `betrag`, `verwendungszweck`) VALUES ('Sperren', 0, ".$_GET['ban'].", 0, 'Dieses Konto wurde gesperrt.')");
					if(!$result){
						echo "<!-- LOGGING FAILED: ".mysqli_error($db)." -->";
					}
				}
			}
			/* Unban die bitch */
			if(isset($_GET['unban']) && is_numeric($_GET['unban']) && $_SESSION['uid'] == 1){
				$result = $db->query("UPDATE `accounts` SET `banned` = '0' WHERE `accounts`.`ID` = ".(int)$_GET['unban']);
				if($result)
					echo "<script>$.notify('Konto #".sprintf('%05d', (int)$_GET['unban'])." ist jetzt entsperrt.', 'success');</script>";
				else
					echo "<script>$.notify('Fehler: Konnte Konto #".sprintf('%05d', (int)$_GET['unban'])." nicht entsperren.');</script>";
				if($LOGGING_ENABLED){
					$result = $db->query("INSERT INTO `log` (`typ`, `von`, `an`, `betrag`, `verwendungszweck`) VALUES ('Entsperren', 0, ".$_GET['unban'].", 0, 'Dieses Konto wurde entsperrt.')");
					if(!$result) echo "<!-- LOGGING FAILED: ".mysqli_error($db)." -->";
					
				}
			}
			/* Ein und Auszahlen */
			$amt = 0;
			if(isset($_POST['amt']))
				$amt = (int)$_POST['amt'];
			//Auszahlen
			if( isset($_POST['withdraw']) && is_numeric($_POST['withdraw']) && $amt > 0 ){ 
				if($_SESSION['uid'] != 1 && $amt > (int)$CONFIG['withdraw_limit']){
					echo "<script>$.notify('Zugriff Verweigert: Auszahlungen höher als ".$CONFIG['withdraw_limit']." nicht erlaubt. Bitte sprich einen Betreuer an.');</script>";
				}else{
					$result = $db->query("SELECT balance, banned FROM accounts WHERE ID = ".(int)$_POST['withdraw']);
					if(!$result){
						echo "<script>$.notify('Fehler bei Auszahlung: Konto #".sprintf('%05d', (int)$_POST['withdraw'])." nicht lesbar.');</script>";
					}else{
						$acc = $result->fetch_assoc();
						if($acc['banned'] == 1){
							echo "<script>$.notify('Auszahlung Verweigert: Konto ist gesperrt.');</script>";
						}else{
							$balance = $acc['balance'];
							if($amt > $balance){
								echo "<script>$.notify('Auszahlung abgelehnt: Unzureichender Kontostand: ".$balance.$CURRENCY."');</script>";
							}else{
								$result = $db->query("UPDATE accounts SET balance = balance-".$amt." WHERE ID = ".(int)$_POST['withdraw']);
								if(!$result){
									die(html_err(false, "Datenbankfehler: ". mysqli_error($db)));
								}else{
									echo "<script>$.notify('Auszahlung erfolgt: ".$amt.$CURRENCY." von Konto #".sprintf('%05d', (int)$_POST['withdraw'])." ausgezahlt', 'success');</script>";
									if($LOGGING_ENABLED){
										$result = $db->query("INSERT INTO `log` (`typ`, `von`, `an`, `betrag`, `verwendungszweck`) VALUES ('Auszahlung', ".(int)$_POST['withdraw'].", 0, ".$amt.", 'Von dem Konto wurde Geld abgehoben.')");
										if(!$result) echo "<!-- LOGGING FAILED: ".mysqli_error($db)." -->";
									}
								}
							}
						}
					}
				}
			}
			//Einzahlen
			if(isset($_POST['deposit']) && is_numeric($_POST['deposit']) && $amt > 0){
				$result = $db->query("SELECT banned FROM accounts WHERE ID = ".(int)$_POST['deposit']);
				if(!$result){
					echo "<script>$.notify('Fehler bei Einzahlung: Konto #".sprintf('%05d', (int)$_POST['deposit'])." nicht lesbar.');</script>";
				}else{
					$acc = $result->fetch_assoc();
					if($acc['banned'] == 1){
						echo "<script>$.notify('Einzahlung Verweigert: Konto ist gesperrt.');</script>";
					}else{
						$result = $db->query("UPDATE accounts SET balance = balance+".$amt." WHERE ID = ".(int)$_POST['deposit']);
						if(!$result){
							die(html_err(false, "Datenbankfehler: ". mysqli_error($db)));
						}else{
							echo "<script>$.notify('Einzahlung erfolgt: ".$amt.$CURRENCY." auf Konto #".sprintf('%05d', (int)$_POST['deposit'])." eingezahlt', 'success');</script>";
							if($LOGGING_ENABLED){
								$result = $db->query("INSERT INTO `log` (`typ`, `von`, `an`, `betrag`, `verwendungszweck`) VALUES ('Einzahlung', 0, '".(int)$_POST['deposit']."', '".$amt."', 'Auf das Konto wurde Geld einbezahlt.');");
								if(!$result) echo "<!-- LOGGING FAILED: ".mysqli_error($db)." -->";
							}
						}
					}
				}
			}
			?>
			<!-- end php init-->
			<div class="row">
				<div class="col-2 d-print-none">
					<button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#transactModal">
						<i class="fas fa-money-check-alt"></i> Überweisung
					</button>
					<br/>
					<!-- Transact Modal -->
					<div class="modal fade" id="transactModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
						<div class="modal-dialog" role="document">
							<?php $modal = true; include_once("transaction.php"); ?>
						</div>
					</div>
					<!-- Navliste -->
					<div class="jumbotron" style="padding: 1em;">
						<ul id="metismenu" class="nav flex-column nav-pills">
							<li <?php if($PAGE==-1){ echo 'class="mm-active"'; } ?>>
								<a href="?p=-1" aria-expanded="false" class="nav-link<?php if($PAGE==-1)echo " active"; ?>"><i class="fas fa-chart-line"></i> Kontenübersicht</a>
							</li>
							<li <?php if($PAGE==0){ echo 'class="mm-active"'; } ?>>
								<a href="?p=0" aria-expanded="true" class="nav-link<?php if($PAGE==0)echo " active"; ?>"><i class="fas fa-building"></i> Betriebskonten</a>
							</li>
							<li <?php if($PAGE==1){ echo 'class="mm-active"'; } ?>>
								<a href="?p=1" aria-expanded="false" class="nav-link<?php if($PAGE==1)echo " active"; ?>"><i class="fas fa-user-shield"></i> Betreuerkonten</a>
							</li>
							<li <?php if($PAGE==2){ echo 'class="mm-active"'; } ?>>
								<a href="?p=-2" aria-expanded="false" class="nav-link<?php if($PAGE==-2 && !isset($_GET['g']))echo " active"; ?>"><i class="fas fa-users"></i> Privatkonten</a>
								<ul style="padding-left: 2em;"  class="nav flex-column nav-pills">
									<?php 
										for($i = 0; $i<count($GRUPPEN); $i++){
											if($i < 2) //Die ersten beiden sind Crap.
												continue;
											echo '<li><a href="?p=-2&g='.$GRUPPEN[$i].'" class="nav-link';
											if(isset($_GET['g']) && $_GET['g']==$GRUPPEN[$i])echo " active";
											echo '">'.$GRUPPEN[$i].'</a></li>';
										}
									?>
								</ul>
							</li>
							<li>
								<a href="?druck" class="nav-link<?php if($PAGE==-3)echo " active"; ?>"><i class="fas fa-scroll"></i> Kontoauszüge</a>
							</li>
						</ul>
						<script>
							$("#metismenu").metisMenu();
						</script>
					</div>
				</div>
				<div class="col-10">
					<?php if($PAGE > -3){ ?>
					<button class="btn btn-outline-primary d-print-none" type="button" data-toggle="modal" data-target="#einzahlenModal">
						<i class="fas fa-download"></i> Einzahlen
					</button>
					<button class="btn btn-outline-primary d-print-none" type="button" data-toggle="modal" data-target="#auszahlenModal">
						<i class="fas fa-upload"></i> Auszahlen
					</button>
					<br/><br/>
					<?php } ?>
					<!-- Deposit Modal -->
					<div class="modal fade" id="einzahlenModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
						<div class="modal-dialog" role="document">
							<form action="." method="POST" class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-download"></i> Geld Einzahlen</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<label for="deposit">Kontonummer</label>
									<input name="deposit" class="form-control js-ids" type="text" pattern="\d*"/>
									<label for="amt">Betrag</label>
									<input name="amt" class="form-control js-ids" type="text" pattern="\d*"/>
								</div>
								<div class="modal-footer">
									<button type="submit" class="btn btn-primary">Einzahlen</button>
								</div>
							</form>
						</div>
					</div>
					<!-- Withdrawal Modal -->
					<div class="modal fade" id="auszahlenModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
						<div class="modal-dialog" role="document">
							<form action="." method="POST" class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-download"></i> Geld Abheben</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<label for="withdraw">Kontonummer</label>
									<input name="withdraw" class="form-control js-ids" type="text" pattern="\d*"/>
									<label for="amt">Betrag</label>
									<input name="amt" class="form-control js-ids" type="text" pattern="\d*"/>
								</div>
								<div class="modal-footer">
									<button type="submit" class="btn btn-primary">Abheben</button>
								</div>
							</form>
						</div>
					</div>
					<!-- WPO ONLY: <script>
						var selected_ids = [];

						function removeArrySenpai(arr) {
							var what, a = arguments, L = a.length, ax;
							while (L > 1 && arr.length) {
								what = a[--L];
								while ((ax= arr.indexOf(what)) !== -1) {
									arr.splice(ax, 1);
								}
							}
							return arr;
						}

						function rowCheck(checkBox, id){
							if(checkBox.checked){
								selected_ids[selected_ids.lenght] = id;
							}else{
								removeArrySenpai(selected_ids, id);
							}
						}

						function einzahlen(){
							$(".js-ids").attr("disabled", "");
							$(".js-ids").attr("value", selected_ids.toString());
							alert("selected: "+selected_ids.toString());
							$('#einzahlenModal').modal('show');
						}
						function auszahlen(){$(".js-ids").attr("disabled", "");
							$(".js-ids").attr("value", selected_ids.toString());
							alert("selected: "+selected_ids.toString());
							$('#auszahlenModal').modal('show');
						}
					</script>-->
					<?php if($PAGE>-3){ //Alle Accounts / Übersicht ?>
					<table id="data-table" class="table table-striped table-sm">
						<thead>
							<tr>
								<th>Kontonr.#</th>
								<th>Inhaber</th>
								<th>Gruppe</th>
								<th>Kontostand</th>
								<th>Status</th>
								<th></th>
							</tr>
						</thead>
						<tbody id="content">
							<?php 
								include_once("php/sqlconfig.php");
								if(!$dberror){
									if($PAGE == -1)
										$result = $db->query("SELECT * FROM accounts");
									else if($PAGE == 0)
										$result = $db->query("SELECT * FROM accounts WHERE `type` = 'Betrieb'");
									else if($PAGE == 1)
										$result = $db->query("SELECT * FROM accounts WHERE `type` = 'Betreuer'");
									else if($PAGE == -2){
										if(!isset($_GET['g']))
											$result = $db->query("SELECT * FROM accounts WHERE `type` != 'Betreuer' AND `type` != 'Betrieb' ORDER BY `type`");
										else{
											$result = $db->query("SELECT * FROM accounts WHERE `type` = '".$_GET['g']."'");
										}
									}
										
									if(!$result){
										echo html_err(false, "Datenbankfehler: ". mysqli_error($db));
									}else{
										while($row = $result->fetch_assoc()){
											echo "<tr>";
												echo "<td>".sprintf('%05d', $row['ID'])."</td>";
												echo "<td>".$row['owner']."</td>";
												echo "<td>".$row['type']."</td>";
												echo "<td><i>".$row['balance'].$CURRENCY."</i></td>";
												if($row['banned']==0){
													echo "<td><span class='badge badge-light'>Aktiv</span></td>";
													echo '<td>';
													if($_SESSION['uid'] == 1)
														echo '<a href="?ban='.$row['ID'].'" class="btn btn-sm d-print-none"><i class="fas fa-lock"></i></a>';
													echo '</td>';
												}else{
													echo "<td><span class='badge badge-danger'>Eingefroren</span></td>";
													echo '<td>';
													if($_SESSION['uid'] == 1)
														echo '<a href="?unban='.$row['ID'].'" class="btn btn-sm d-print-none"><i class="fas fa-unlock"></i></a>';
													echo '</td>';
												}
											echo "</tr>";
										}
									}
								}else{
									echo html_err(false, $dberror);
								}
								
							?>
						</tbody>
					</table>
					<?php }else if($PAGE==-3){  ?>
					
					<?php }else if($PAGE==3){  ?>
					
					<?php } ?>
				</div>
			</div>
		</div>
		<script>
			$(document).ready(function() {
				$('#data-table').DataTable(
				{     
					"aLengthMenu": [[15, 30, 50, 100, -1], [15, 30, 50, 100, "Alle"]],
						"iDisplayLength": 30
					} 
				);
			} );
			function checkAll(bx) {
				var cbs = document.getElementsByTagName('input');
				for(var i=0; i < cbs.length; i++) {
					if(cbs[i].type == 'checkbox') {
					cbs[i].checked = bx.checked;
					}
				}
			}
		</script>
		<!-- 8====> -->
	</body>
</html>
