<?php 
    check_auth ();
?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title><?php if(isset($_GET['druck'])) echo "OAC-Bank Kontoauszüge von Konto #".sprintf('%05d', (int)$_GET['druck']); ?></title>
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/fontawesome.css">
        <script src="js/bootstrap.min.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-light d-print-none">
            <div class="container">
                <a class="navbar-brand" href="#"><?php echo $APPNAME;?> <span class="text-muted">Kontoauszugsdrucker</span></a>
            </div>
        </nav>
        <nav class="navbar navbar-expand-lg navbar-light bg-light d-print-none">
            <div class="container">
                <form method="GET" class="navbar-nav mr-auto">
                    <input class="form-control mr-sm-2" type="number" name="druck" placeholder="Konto Nr.#" aria-label="Konto Nr.#" <?php if(isset($_GET['druck'])) echo 'value="'.$_GET['druck'].'"'; ?>>
                    <button class="btn btn-secondary my-2 my-sm-0" type="submit"><i class="fas fa-search"></i></button>
                </form>    
                <div class="form-inline my-2 my-lg-0">
                    <?php if($_SESSION['uid'] != 2) echo '<a href="." class="btn">Zurück</a>'; else echo '<a href="?logout" class="btn">Abmelden</a>'; ?>
                    <button class="btn btn-lg btn-primary" onclick="print();" <?php if(!isset($_GET['druck'])  || !is_numeric($_GET['druck']) || (int)$_GET['druck'] == 0) echo 'style="display: none;"'; ?>><i class="fas fa-print"></i> Drucken</button>
                </div>
            </div>
        </nav>
        <div class="container">
            <br class="d-print-none"/>
            <?php 
                /* DB Connection */
			    include_once('php/sqlconfig.php');
			    if($dberror)
                    die(html_err(false, "Fehler beim Verbinden mit der Datenbank: ". $dberror));
                
                /* Load that shitty Config */
                $CONFIG = array();
                $result = $db->query("SELECT `key`, `val` FROM config");
                if(!$result)
                    die(html_err(false, "Datenbankfehler: ". mysqli_error($db)));
                while($row = $result->fetch_assoc())
                    $CONFIG[$row['key']] = $row['val'];

                /* Kontoauszug */
                if(isset($_GET['druck']) && is_numeric($_GET['druck']) && $_GET['druck'] != 0){
                    $result = $db->query("SELECT * FROM accounts WHERE ID = ".(int)$_GET['druck']);
                    if(!$result)
                        die(html_err(false, "Datenbankfehler<br/>Bitte Kontaktiere einen Betreuer<br/><br/><small>". mysqli_error($db)."</small>"));
                    $acc = $result->fetch_assoc();

                    $query = $db->multi_query(
                        "SELECT `typ`, `betrag`, `verwendungszweck`, `time` FROM `log` WHERE an = ".(int)$_GET['druck']." UNION SELECT `typ`, `betrag`, `verwendungszweck`, `time` FROM `log` WHERE von = ".(int)$_GET['druck']."; ".
                        "INSERT INTO `log` (`typ`, `von`, `an`, `betrag`, `verwendungszweck`) VALUES ('Druckansicht', '".(int)$_GET['druck']."', 0, ".$CONFIG['cost_kontoauszug'].", 'Der Kontoauszug für das Konto wurde für den Druck vorbereitet.');"
                    );
                    if(!$query){
                        echo html_err(false, "Datenbankfehler: ". mysqli_error($db));
                    }else{
                        $result = $db->store_result();
                        ?>
                        <h1>OstalbCity Bank<br/><br/><small class="text-muted">Kontoauszüge</small></h1>
                        <br/>
                        <p><?php echo $acc['owner']; ?><br>
                        <?php echo $acc['type'] ?></p>
                        
                        <hr>
                        <table>
                            <tr>
                                <td>Stand</td>
                                <td><?php echo date("d-m-Y H:i:s"); ?></td>
                            </tr>
                            <tr>
                                <td>Kontonummer</td>
                                <td>#<?php echo sprintf('%05d', (int)$_GET['druck']); ?></td>
                            </tr>
                            <tr>
                                <td>Druckpreis</td>
                                <td><?php echo $CONFIG['cost_kontoauszug'].$CURRENCY; ?></td>
                            </tr>
                            <tr>
                                <td><br/></td>
                            </tr>
                            <tr>
                                <td>Aktueller Kontostand&nbsp;&nbsp;&nbsp;</td>
                                <td><?php echo $acc['balance'].$CURRENCY; ?></td>
                            </tr>
                        </table>
                        <br/>
                        <?php 
                        echo '<table class="table table-bordered table-sm">';
                        while($row = $result->fetch_assoc()){
                            echo "<tr>";
                                echo "<td>".$row['time']."</td>";
                                echo "<td>".$row['typ']."</td>";
                                echo "<td><i>".$row['betrag'].$CURRENCY."</i></td>";
                                echo "<td>".$row['verwendungszweck']."</td>";
                            echo "</tr>";
                        }
                        echo '</table>';
                        $result->free();
                    }
                }else{
                    echo "Bitte Kontonummer eingeben";
                }
            ?>
        </div>
    </body>
</html>
