<?php 

    require_once("sessione.php");
    require_once('connessione.php');
    require_once("reg_ex.php");
    require_once('addItems.php');
    require_once("imgUpload.php");
    if($_SESSION['logged']==true){
        header('location:index.php');
        exit();
    }

    /*Aggiunta header,menu e footer*/
    $page=addItems('../html/registrazione.html');

    $tipo=0;
    $mail='';
    $nome='';
    $cognome='';
    $sesso='ns';
    $datan='';
    $pwd='';
    $pwd2='';
    $piva=NULL;
    $rsoc=NULL;
    $id_foto=1;
    $no_error=true;
    $error="";
    $filePath="../img/Utenti/";
    /* se ci sono valori in _POST cerca di fare registrazione o stampa errore */
    if(isset($_POST['registrati'])){
        if(isset($_POST['tipo_utente'])){
            $tipo=$_POST['tipo_utente'];
        }
        if(isset($_POST['email'])){
            $mail=$_POST['email'];
        }
        if(isset($_POST['nome'])){
            $nome=$_POST['nome'];
        }
        if(isset($_POST['cognome'])){
            $cognome=$_POST['cognome'];
        }
        if(isset($_POST['sesso'])){
            $sesso=$_POST['sesso'];
        }
        if(isset($_POST['nascita'])){
            $datan=$_POST['nascita'];
        }
        if(isset($_POST['password'])){
            $pwd=$_POST['password'];
        }
        if(isset($_POST['repeatpassword'])){
            $pwd2=$_POST['repeatpassword'];
        }
        if(isset($_POST['piva'])){
            $piva=$_POST['piva'];
        }
        if(isset($_POST['rsoc'])){
            $rsoc=$_POST['rsoc'];
        }

        //connessione db
        $obj_connection = new DBConnection();
        if(!$obj_connection->create_connection()){
            $error=$error."<div class=\"msg_box error_box\">Errore di connessione al database</div>";
            $no_error=false;
        }
        //controllo input
        if(!check_email($mail)){
            $error=$error."<div class=\"msg_box error_box\">'La mail inserita non è valida.</div>";
            $no_error=false;
        }
        if($obj_connection->queryDB("SELECT * FROM utente WHERE Mail='".$mail."'")){
            $error=$error."<div class=\"msg_box error_box\">Questa mail è già in uso.</div>";
            $no_error=false;
        }
        if(!check_nome($nome)){
            $error=$error."<div class=\"msg_box error_box\">Il nome deve avere lunghezza minima di 2 caratteri e non può presentare numeri al proprio interno.</div>";
            $no_error=false;
        }
        if(!check_nome($cognome)){
            $error=$error."<div class=\"msg_box error_box\">Il cognome deve avere lunghezza minima di 2 caratteri e non può presentare numeri al proprio interno.</div>";
            $no_error=false;
        }
        if($pwd!=$pwd2){
            $error=$error."<div class=\"msg_box error_box\">Il cognome deve avere lunghezza minima di 2 caratteri e non può presentare numeri al proprio interno.</div>";
            $no_error=false;
        }
        if(!check_pwd($pwd)){
            $error=$error."<div class=\"msg_box error_box\">La password deve essere lunga almeno 8 caratteri, contenere almeno una lettera maiuscola una minuscola e un numero.</div>";
            $no_error=false;
        }
        //calcola età
        //date in mm/dd/yyyy format; or it can be in other formats as well
        //explode the date to get month, day and year
        //$birthDate = explode("/", $datan);
        //get age from date or birthdate
        /*$age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md")
            ? ((date("Y") - $birthDate[2]) - 1)
            : (date("Y") - $birthDate[2]));
        if($age<12){
            $error="<div class=\"msg_box error_box\">Per poterti registrare devi avere almeno 12 anni.</div>";
            $no_error=false;
        }*/
        if($no_error){//Controllo non ci siano errori prima di caricare l'immagine
            if($_FILES['fileToUpload']['size'] != 0){
                $uploadResult = uploadImage("Utenti/");
                if($uploadResult['error']==""){
                    $filePath=$uploadResult['path'];
                }
                else{
                    $error=$error.$uploadResult['error'];
                    $no_error=false;
                }
            }
            if($filePath!=="../img/Utenti/"&&$uploadResult['error']==""){//è stato caricato qualcosa
                $obj_connection->insertDB("INSERT INTO `foto` (`ID`, `Path`) VALUES (NULL, \"$filePath\")");
                $result=$obj_connection->queryDB("SELECT * FROM foto WHERE Path='".$filePath."'");
                $id_foto=$result[0]['ID'];
            }
        }
        if($no_error){
            
            $tipo=$obj_connection->escape_str(trim($tipo));
            $mail=$obj_connection->escape_str(trim($mail));
            $nome=$obj_connection->escape_str(trim($nome));
            $cognome=$obj_connection->escape_str(trim($cognome));
            $sesso=$obj_connection->escape_str(trim($sesso));
            $datan=$obj_connection->escape_str(trim($datan));
            $hashed_pwd=hash("sha256",$obj_connection->escape_str(trim($pwd)));
            $piva=$obj_connection->escape_str(trim($piva));
            $rsoc=$obj_connection->escape_str(trim($rsoc));

            if($tipo==0){//utente
                $permessi="Utente";
                $obj_connection->insertDB("INSERT INTO `utente` (`ID`, `PWD`, `Mail`, `Nome`, `Cognome`, `Data_Nascita`, `ID_Foto`, `Ragione_Sociale`, `P_IVA`, `Permessi`, `Sesso`) VALUES (NULL,\"$hashed_pwd\", \"$mail\", \"$nome\", \"$cognome\", \"$datan\", \"$id_foto\", NULL, NULL, \"$permessi\", \"$sesso\")");
            }else if($tipo==1){//ristoratore
                $permessi="Ristoratore";
                $obj_connection->insertDB("INSERT INTO `utente` (`ID`, `PWD`, `Mail`, `Nome`, `Cognome`, `Data_Nascita`, `ID_Foto`, `Ragione_Sociale`, `P_IVA`, `Permessi`, `Sesso`) VALUES (NULL,\"$hashed_pwd\", \"$mail\", \"$nome\", \"$cognome\", \"$datan\", \"$id_foto\", \"$rsoc\", \"$piva\", \"$permessi\", \"$sesso\")");         
            }   

            //check dati inseriti
            if(!$obj_connection->queryDB("SELECT * FROM utente WHERE Mail='".$mail."'")){
                $error="<div class=\"msg_box error_box\">Errore nell'inserimento dei dati</div>";
            }else{
                $obj_connection->close_connection();
                header('location: login.php');
                exit;
            }
            $obj_connection->close_connection();
        }       

    }
    if($tipo==1){
        $page=str_replace('checked="%REC_CHECKED%"',"",$page);
        $page=str_replace('checked="%RIST_CHECKED%"',"checked=\"checked\"",$page);
    }
    else{
        $page=str_replace('checked="%REC_CHECKED%"',"checked=\"checked\"",$page);
        $page=str_replace('checked="%RIST_CHECKED%"',"",$page);
    }
    $page=str_replace("%MAIL_VALUE%",$mail,$page);
    $page=str_replace("%NOME_VALUE%",$nome,$page);
    $page=str_replace("%COGNOME_VALUE%",$cognome,$page);
    $page=str_replace("%DATAN_VALUE%",$datan,$page);
    if($sesso==="ns"){
        $page=str_replace('selected="%SEL_NS%"',"selected=\"selected\"",$page);
        $page=str_replace('selected="%SEL_UOMO%"',"",$page);
        $page=str_replace('selected="%SEL_DONNA%"',"",$page);
        $page=str_replace('selected="%SEL_ALTRO%"',"",$page);
    }else if($sesso==="Uomo"){
        $page=str_replace('selected="%SEL_NS%"',"",$page);
        $page=str_replace('selected="%SEL_UOMO%"',"selected=\"selected\"",$page);
        $page=str_replace('selected="%SEL_DONNA%"',"",$page);
        $page=str_replace('selected="%SEL_ALTRO%"',"",$page);
    }else if($sesso==="Donna"){
        $page=str_replace('selected="%SEL_NS%"',"",$page);
        $page=str_replace('selected="%SEL_UOMO%"',"",$page);
        $page=str_replace('selected="%SEL_DONNA%"',"selected=\"selected\"",$page);
        $page=str_replace('selected="%SEL_ALTRO%"',"",$page);
    }else if($sesso==="Altro"){
        $page=str_replace('selected="%SEL_NS%"',"",$page);
        $page=str_replace('selected="%SEL_UOMO%"',"",$page);
        $page=str_replace('selected="%SEL_DONNA%"',"",$page);
        $page=str_replace('selected="%SEL_ALTRO%"',"selected=\"selected\"",$page);
    }
    $page=str_replace("%PWD1_VALUE%",$pwd,$page);
    $page=str_replace("%PWD2_VALUE%",$pwd2,$page);
    $page=str_replace("%PIVA_VALUE%",$piva,$page);
    $page=str_replace("%RSOC_VALUE%",$rsoc,$page);
    $page=str_replace("%ERROR%",$error,$page);
    echo $page;
?>