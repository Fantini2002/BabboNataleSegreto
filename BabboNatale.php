<?php
$folder = "";

//connessione al db
$conn = new mysqli("HOST", "USERNAME", "PASSWORD", "DB_NAME", 3306);
if ($conn->connect_errno) {
    error_log("SQL Error:Failed to connect to MySQL: (" . $conn->connect_errno . ") " . $conn->connect_error, 0);
    echo "Non riesco a connettermi al database, riprova più tardi. Se il problema persiste contatta l'amministratore";
    die;
}

function mysqlToArray($conn, $query){
    $oggetto = mysqli_query($conn, $query);
    $arrayOggetto = array();
    while($r = mysqli_fetch_array($oggetto)){
        $arrayOggetto[] = $r[0];
    }
    return $arrayOggetto;
}

$ip = $_SERVER['REMOTE_ADDR'];

//estratto il proprio nome
if($_POST["estratto"]){
    $postNome = $_POST["estratto"];
    $postIP = $_POST["ip"];
    //segno come non estratto
    mysqli_query($conn, "UPDATE nomi SET estratto = 0 WHERE nome = '$postNome'");
    //elimino ip da elenco
    mysqli_query($conn, "DELETE FROM utenti WHERE ip = '$postIP'");
    //elimino cookie
    setcookie("BN_estratto", "", strtotime("-1 day"), "/$folder");
    unset($_COOKIE['BN_estratto']);
    //creo variabile per evitare troppe riestrazione
    $riestratto = true;
}

//estrazione
if($_POST["sorteggia"]){
    //è stato premuto il pulsante "estrai"
    if(mysqli_num_rows(mysqli_query($conn, "SELECT * FROM utenti WHERE ip = '$ip'")) == 0 && !$_COOKIE['BN_estratto']){
        //non ha mai estratto o sta riestraendo
        $query = "SELECT nome FROM nomi WHERE estratto = 0";
        if($riestratto)
            $query .= " AND nome != '$postNome'"; //se riestrae non può apparire lo stesso

        $nomi = mysqlToArray($conn, $query);

        if($nomi != []){
            //c'è qualcuno ancora da estrarre
            $estratto = $nomi[array_rand($nomi, 1)]; //nome estratto
        
            mysqli_query($conn, "UPDATE nomi SET estratto = 1 WHERE nome = '$estratto'"); //segno come estratto

            mysqli_query($conn, "INSERT INTO utenti(ip) VALUES('$ip')"); //memorizzo ip

            setcookie("BN_estratto", $estratto, strtotime("25 December 2020"), "/$folder"); //memorizzo cookie

            $caso = 1;
        }
        else
            $caso = 3; //sono già stati estratti tutti
    }
    else{
        //ha già estartto
        if($_COOKIE["BN_estratto"]) //se non è nel db memorizzo l'ip
            if(mysqli_num_rows(mysqli_query($conn, "SELECT * FROM utenti WHERE ip = '$ip'")) == 0)
                mysqli_query($conn, "INSERT INTO utenti(ip) VALUES('$ip')"); //memorizzo ip

        $caso = 2;
    }

}
else if($_POST["inviaPresentazione"]){

    if(mysqli_num_rows(mysqli_query($conn, "SELECT * FROM utentiInvio WHERE ip = '$ip'")) == 0 && !$_COOKIE['BN_invio']){
        //prendo estratto da cookie
        if($_COOKIE["BN_estratto"])
            $estratto = $_COOKIE["BN_estratto"];

        $caso = 4;
    }
    else{
        //ha già estartto
        if($_COOKIE["BN_invio"]){ //se non è nel db memorizzo l'ip
            if(mysqli_num_rows(mysqli_query($conn, "SELECT * FROM utentiInvio WHERE ip = '$ip'")) == 0)
                mysqli_query($conn, "INSERT INTO utenti(ip) VALUES('$ip')"); //memorizzo ip
        }
        else //se non c'è cookie lo creo
            setcookie("BN_invio", true, strtotime("30 December 2020"), "/$folder"); //memorizzo cookie

        $caso = 9;
    }
}
else if($_POST["nome"] && $_POST["testo"]){
    $nome=trim(stripslashes($_POST["nome"]));
    $testo=str_replace("\n", "<br>", trim(stripslashes($_POST["testo"]))); //sostituisco \n con <br> per a capo

    $database = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM mail WHERE nome = '$nome'"), MYSQLI_ASSOC);
    
    if($database != []){
        if(!$database["inviato"]){
            //non è stato inviato
            $to = $database["mail"];
            $subject = "Babbo Natale segreto -  Hai ricevuto un messaggio";
            $body = "Ciao ".$database['nome'].",<br>Ecco il tuo messaggio per il Babbo Natale segreto...<br><br>".$testo;

            $headers = "MIME-Version: 1.0\nContent-type: text/html; charset=UTF-8\nFrom: Babbo Natale segreto <babbonatalesegreto@fantinialex.com>";

            $mail= @mail($to, $subject, $body, $headers);

            if($mail){
                mysqli_query($conn, "UPDATE mail SET inviato = 1 WHERE nome = '$nome'"); //segno come inviato
                //elimino cookie
                setcookie("BN_estratto", "", strtotime("-1 day"), "/$folder");
                unset($_COOKIE['BN_estratto']);

                mysqli_query($conn, "INSERT INTO utentiInvio(ip) VALUES('$ip')"); //memorizzo ip

                setcookie("BN_invio", true, strtotime("30 December 2020"), "/$folder"); //memorizzo cookie
                $caso = 5;
            }
            else
                $caso = 6; //errore invio
        }
        else
            $caso = 7; //è gia stato inviato un messaggio a questa persone
    }
    else
        $caso = 8; //mail non trovata
}
else
    $caso = 0; //presentazione iniziale

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Babbo Natale segreto</title>
    <link href="CSS/style.css?v2" rel="stylesheet" type="text/css"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="script/jquery.redirect.js"></script>
</head>
<body>
    <section class="cover">
        <div class="action">
            <h1>Babbo Natale segreto</h1>
            <div class="center">
                <?php switch($caso){
                    case 0:
                        //presentazione ?>
                        <div class="presentazione">
                                <p>Ormai il "<b>Babbo Natale segreto</b>" è diventata una vera e propria tradizione di classe; quest'anno però, causa covid, diventa molto difficile metterlo in atto come al solito, ecco perché invece di fare un regalo a sorpresa faremo un <b>pensiero a sorpresa</b>.<br>
                                Premi "Estrai" per sorteggiare un tuo compagno di classe e pensa a <b>qualcosa che vorresti dirgli</b>, pochi giorni prima di Natale riceverai istruzioni su come recapitargli questo messaggio. Non avere paura di aprirti, sarai tu a decidere se essere anonimo o meno.
                                <br><br>
                                Per sicurezza <b>fai uno screenshot</b> del nome che ti appare perché non sempre sarà possibile recuperarlo, solo tu sai a chi devi farlo. Nel caso in cui ti appaia il tuo nome, e solo in questo caso, premi il pulsante "Ho estratto il mio nome".
                                <br><br>
                                Premendo su "Estrai" acconsenti alla memorizzazione del tuo ip nel database del sito e di un cookie sul tuo dispositivo con scadenza 25 dicembre 2020.
                                <br>
                                Premendo su "Invia" acconsenti alla memorizzazione del tuo ip nel database del sito e di un cookie sul tuo dispositivo con scadenza 30 dicembre 2020.</p>
                            </div>
                            <div class="btns">
                                <div class="btn" onClick="estrai()">Estrai</div>
                                <div class="btn" onClick="invia()">Invia</div>
                            </div>
                        <?php
                        break;
                    case 1:
                        //estrazione ?>
                        <p>Dovrai scrivere un pensiero per...
                        <br>
                        <b><?php echo $estratto; ?></b></p>
                        <?php if(!$riestratto){
                            ?>
                                <div class="btn" onClick="riestrai()">Ho estratto il mio nome</div>
                            <?php
                        }
                        break;
                    case 2:
                        //già sorteggiato precedentemente ?>
                        <p>Hai già una persona assegnata...
                        <br>
                        <?php if($_COOKIE['BN_estratto']) echo "<b>".$_COOKIE['BN_estratto']."</b>"; else echo "Purtroppo non riesco a trovare il nome, apri questa pagina dallo stesso dispositivo con cui hai eseguito l'estrazione (e con lo stesso browser)" ?></p>
                        <?php
                        break;
                    case 3:
                        //non ci sono più persone da estrarre ?>
                        <p>Tutte le <?php if($riestratto) echo "altre "; ?>persone sono già state estratte</p>
                        <?php
                        break;
                    case 4:
                    case 6:
                    case 8:
                        //presentazione invio ?>
                        <p>Compila i seguenti campi, il destinatario riceverà il messaggio via mail.<br>
                        Se non vuoi essere anonimo firma il tuo messaggio<br>
                        <?php if($caso == 6){ ?> <br><font color="#FF0000"><b>Errore, la mail non è stata inviata. Per favore riprova</b></font> <?php } ?>
                        <?php if($caso == 8){ ?> <br><font color="#FF0000"><b>Errore, non è stato trovato nessuno con questo nome</b></font> <?php } ?></p>
                        <p><?php if($estratto){
                            ?>
                            Il tuo messaggio verrà consegnato a <b><?php echo $estratto; ?></b>
                        <?php
                        }else{
                        ?>
                            Invia il messaggio <b>solo alla persona estratta</b>
                        <?php } ?></p>
                        <form name="form1" method="post" action="BabboNatale">
                            <div class="nome">
                                <?php if($estratto){ ?> 
                                    <input type="hidden" name="nome" id="nome" value="<?php echo $estratto ?>" required>
                                <?php }else{ ?>
                                    <label for="nome">Cognome e nome</label>
                                    <input type="text" name="nome" id="nome" required>
                                <?php } ?>
                            </div>
                            <div class="messaggio">
                                <label for="messaggio">Messaggio</label>
                                    <textarea name="testo" cols="40" rows="10" id="messaggio" required></textarea>
                            </div>
                            <input type="submit" name="Submit" value="Invia" class="btn" id="form_button">
                        </form>
                        <?php
                        break;
                    case 9:
                        ?>
                        <p>Hai già inviato un messaggio...</p>
                        <?php
                        break;
                    case 5:
                        //mail inviata ?>
                        <p>Mail inviata con successo!<br>
                            <br>
                            <b>Buon Natale</b></p>
                        <?php
                        break;
                    case 7:
                        //è gia stato inviato un messaggio a questa persone ?>
                        <p>È già stato inviato un messaggio a questa persona...</p>
                        <?php
                        break;
                }?>
            </div>
        </div>
    </section>
</body>
<script>

function estrai(){
    $.redirect('BabboNatale', {'sorteggia': 'true'});
}

function riestrai(){
    $.redirect('BabboNatale', {'sorteggia': 'true', 'estratto': '<?php echo $estratto; ?>', 'ip': '<?php echo $ip; ?>'});
}

function invia(){
    $.redirect('BabboNatale', {'inviaPresentazione': 'true'});
}

</script>
</html>