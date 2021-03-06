<?php 
    require_once('sessione.php');
    require_once('addItems.php');
    require_once('connessione.php');
    require_once('indirizzo.php');
    require_once('recensione.php');
    require_once('errore.php');
    $page= (new addItems)->add("../html/ins_recensione.html");   

    if($_SESSION['logged']==true){
        if($_SESSION['permesso']=='Utente'){
            //gestione errori
            $errors=array("titolo"=>"",
                            "contenuto"=>"");
            $num_errors=0;

            $obj_connection=new DBConnection();

            $titolo='';
            $contenuto='';
            $stelle=1;
            if(isset($_POST['titolo_recensione'])){
                $titolo=$_POST['titolo_recensione'];

                if(isset($_POST['contenuto_recensione'])){
                    $contenuto=$_POST['contenuto_recensione'];
                }

                if(isset($_POST['n_stelle'])){
                    $stelle=$_POST['n_stelle'];
                }

                date_default_timezone_set("Europe/Rome");
                $data=date("Y-m-d H");
                $rec_fields=array("Data"=> $data,
                                    "Stelle"=>$stelle,
                                    "Oggetto"=>$titolo,
                                    "Descrizione"=>$contenuto,
                                    "ID_Utente"=>$_SESSION['ID'],
                                    "ID_Ristorante"=>$_POST['id_ristorante']);
                $recensione=new recensione($rec_fields);

                $errors=$recensione->getErrors();
                $num_errori=$recensione->numErrors($errors);

                if($num_errori==0){
                    if($insert=$recensione->insertIntoDB()){
                        header('location: dettaglioristorante.php?id='.$_POST['id_ristorante']);
                        exit;
                    }else{
                        $err='[Inserimento fallito]';
                    }
                }
            }
            if($num_errors>0){
                $err="[Sono presenti $num_errori campi compilati non correttamente]";
            }else{
                $err='';
            }
            //reperimento dati ristorante
            $nome_rist='';
            $img_path='';
            $indirizzo='';
            if($obj_connection->create_connection()){
                $query="SELECT f.Path AS Percorso, r.Nome AS Nome, r.Via AS Via, r.Civico AS Civ, r.Citta AS Citta, r.CAP AS CAP, r.Nazione AS Nazione
                         FROM ristorante AS r, corrispondenza AS c, foto AS f 
                         WHERE r.ID=".$_POST['id_ristorante']." AND r.ID=c.ID_Ristorante AND c.ID_Foto=f.ID ";
                if($query_rist=$obj_connection->connessione->query($query)){
                    $array_rist=$obj_connection->queryToArray($query_rist);
                    if(count($array_rist)>0){
                        $nome_rist=$array_rist[0]['Nome'];
                        $img_path=$array_rist[0]['Percorso'];
                        $indirizzo=(new indirizzo($array_rist[0]['Via'],$array_rist[0]['Civ'],$array_rist[0]['Citta'],$array_rist[0]['CAP'],$array_rist[0]['Nazione']))->getIndirizzo();
                    }
                    $query_rist->close();
                }else{
                    //query fallita
                    $page= (new addItems)->add("../html/base.html");
                    $page=str_replace('%PATH%','Ricerca',$page);
                    $page=str_replace('%MESSAGGIO%',(new errore('query'))->printHTMLerror(),$page);
                    echo $page;
                    exit;
                }

                $obj_connection->close_connection();
            }else{
                //connessione fallita
                $page= (new addItems)->add("../html/base.html");
                $page=str_replace('%PATH%','Ricerca',$page);
                $page=str_replace('%MESSAGGIO%',(new errore('DBConnection'))->printHTMLerror(),$page);
                echo $page;
                exit;
            }
            for($i=1;$i<=5;$i++){
                if($i==$stelle){
                    $page=str_replace('%VALUE_'.$i.'%','selected="selected"',$page);
                }else{
                    $page=str_replace('%VALUE_'.$i.'%','',$page);
                }
            }

            $page=str_replace('%NOME_RISTORANTE%',$nome_rist,$page);
            $page=str_replace('%URL_IMG%',$img_path,$page);
            $page=str_replace('%INDIRIZZO_RISTORANTE%',$indirizzo,$page);
            $page=str_replace('%ID_RIST%',$_POST['id_ristorante'],$page);
    
            $page=str_replace('%MESSAGGIO%',$err,$page);
            $page=str_replace('%ERR_TITOLO%',$errors['titolo'],$page);
            $page=str_replace('%ERR_CONTENUTO%',$errors['contenuto'],$page);

            $page=str_replace('[','<p class="msg_box error_box">',$page);
            $page=str_replace(']','</p>',$page);

            $page=str_replace('%TITOLO_RECENSIONE%',$titolo,$page);
            $page=str_replace('%CONTENUTO_RECENSIONE%',$contenuto,$page);

        }else{
            header('location: access_denied.php');
            exit;
        }
    }else{
        header('location: index.php');
        exit;
    }

    echo $page;
?>