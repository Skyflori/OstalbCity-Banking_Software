<?php
    if(!isset($modal)){
        session_start(); if(!isset($_SESSION['uid'])) die("Access Denied"); //Auth checker 3000

        include_once('php/sqlconfig.php');
        if($dberror)
            die(html_err(false, "Fehler beim Verbinden mit der Datenbank: ". $dberror));
    }
    if(!isset($CONFIG)){
        /* Lade die Einstellungen */
        $CONFIG = array();
        $result = $db->query("SELECT `key`, `val` FROM config");
        if(!$result)
            die(html_err(false, "Datenbankfehler: ". mysqli_error($db)));
        while($row = $result->fetch_assoc())
            $CONFIG[$row['key']] = $row['val'];
    }

    function headDick($loc){
        if(isset($_POST['mobile'])){
            header("Location: ".$loc);
            exit;
        }else{
            header("Location: .".$loc);
            exit;
        }
    }

    if(isset($_POST['acc']) && isset($_POST['target']) && isset($_POST['amt']) 
        && is_numeric($_POST['acc']) && is_numeric($_POST['target']) && is_numeric($_POST['amt']) && isset($_POST['msg'])){
        
        // Get Info
        $result = $db->query("SELECT ID, balance, banned FROM accounts WHERE ID = ".(int)$_POST['acc']." OR ID = ".$_POST['target']);
        if(!$result){
            if(isset($_POST['mobile'])){
                header("Location: ?transfail=db1");
                exit;
            }else{
                header("Location: .?transfail=db1");
                exit;
            }
        }
        $acc_von = array();
        $acc_an = array();
        while($row = $result->fetch_assoc()){
            if($row['ID'] == $_POST['acc'])
                $acc_von = $row;
            if($row['ID'] == $_POST['target'])
                $acc_an = $row;
        }

        // Check the Info
        if($acc_von['ID'] == $acc_an['ID']){
            headDick("?transfail=self");
        }else if((int)$acc_von['banned'] == 1){ // Check ban status VON
            headDick("?transfail=banned_von");
        }else if((int)$acc_an['banned'] == 1){ // Check ban status AN
            headDick("?transfail=banned_an");
        }else if((int)$acc_von['balance'] < (int)$_POST['amt']){ // Check sender balance
            headDick("?transfail=balance");
        }else if($_SESSION['uid']!=1 && (int)$_POST['amt'] > (int)$CONFIG['transaction_limit'])
            headDick("?transfail=limit");

        // DO IT!!!!
        $result = $db->multi_query(
            "UPDATE accounts SET balance = balance-".(int)$_POST['amt']." WHERE ID = ".$acc_von['ID']."; 
            UPDATE accounts SET balance = balance+".(int)$_POST['amt']." WHERE ID = ".$acc_an['ID'].";
            INSERT INTO `log` (`typ`, `von`, `an`, `betrag`, `verwendungszweck`) VALUES ('Transaktion', '".$acc_von['ID']."', '".$acc_an['ID']."', '".(int)$_POST['amt']."', '".$_POST['msg']."');");
        if(!$result){
            headDick("?transfail=db2&errmsg=".base64_encode(mysqli_error($db)));
        }else{
            headDick("?transsucc&errmsg=".base64_encode(mysqli_error($db)));
            $result->free();
        }

        
    }

    function trans_frontend(){
        if(isset($_GET['transfail'])) {
            if($_GET['transfail'] == "banned_von")
                echo "<script>$.notify('Überweisung Verweigert: Senderkonto ist gesperrt.');</script>";
            else if($_GET['transfail'] == "banned_an")
                echo "<script>$.notify('Überweisung Verweigert: Empfängerkonto ist gesperrt.');</script>";
            else if($_GET['transfail'] == "balance")
                echo "<script>$.notify('Überweisung Verweigert: Unzureichender Kontostand.');</script>";
            else if($_GET['transfail'] == "self")
                echo "<script>$.notify('Damit machst du dich auch nicht reicher :P');</script>";
            else if($_GET['transfail'] == "limit")
                echo "<script>$.notify('Überweisung Verweigert: Überweisungslimit von ".$CONFIG['transaction_limit']." überschritten. Bitte sprich einen Betreuer an.');</script>";
            else
                echo "<script>$.notify('Überweisung Fehlgeschlagen.');</script>";
            if(isset($_GET['errmsg'])){
                echo "<!-- DEBUG ERR MSG: ".base64_decode($_GET['errmsg'])." -->";
            }
        }else if(isset($_GET['transsucc'])){
            echo "<script>$.notify('Überweisung erfolgt.', 'success');</script>";
        }
    }
?>
<?php if(isset($modal)){ ?>
<form action="transaction.php" method="POST" class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-money-check-alt"></i> Überweisung tätigen</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-sm">
                <label for="acc">Von Kontonr.#</label>
                <input class="form-control" type="number" name="acc" id="acc" placeholder="XXXXX" />
            </div>
            <div class="col-sm">
                <label for="target">Empfänger Kontonr.#</label>
                <input class="form-control" type="number" name="target" id="target" />
            </div>
        </div>
        <label for="amt">Betrag</label>
        <input class="form-control" type="number" name="amt" id="amt" placeholder="XX G">
        <label for="msg">Verwendungszweck</label>
        <textarea class="form-control" name="msg" id="msg" cols="30" rows="3" maxlenght="100"></textarea>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Abbruch</button>
        <button type="submit" class="btn btn-primary">Überweisen</button>
    </div>
    <?php trans_frontend(); ?>
</form>
<?php }else{ ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Überweisungsterminal</title>
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <script src="js/jquery-3.3.1.min.js"></script><!-- scheiss drecks overused jQuery -->
        <script src="js/notify.js"></script>
    </head>
    <body>
        <?php trans_frontend(); ?>
        <br/>
        <div class="container">
            <h2>OstalbCity Bankterminal <?php if($_SESSION['uid']==1) echo '<span class="text-muted">Betreuermodus</span>'; ?></h2>
            <br/>
            <form action="transaction.php" method="post">
                <input type="hidden" name="mobile" value="true">
                <div class="row">
                    <div class="col-sm">
                        <label for="acc">Von Kontonr.#</label>
                        <input class="form-control" type="number" name="acc" id="acc" placeholder="XXXXX" />
                    </div>
                    <div class="col-sm">
                        <label for="target">Empfänger Kontonr.#</label>
                        <input class="form-control" type="number" name="target" id="target" />
                    </div>
                </div>
                
                
                <label for="amt">Betrag</label>
                <input class="form-control" type="number" name="amt" id="amt" placeholder="XX G">
                <input type="hidden" name="msg" value="Mobilterminal">
                <br/>
                <button class="btn btn-block btn-primary btn-lg" type="submit">
                    Überweisen
                </button>
            </form>
            <br/>
            <a href=".?logout&mobile" class="btn btn-block btn-outline-secondary">Abmelden</a>
        </div>
    </body>
</html>
<?php } ?>