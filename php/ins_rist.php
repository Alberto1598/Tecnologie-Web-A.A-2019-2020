<?php

    require_once("sessione.php");

    //check se loggato
    if($_SESSION['logged']==false){
        header('location: login.php');
        exit;
    }

    require_once("addItems.php");
    $page=addItems('../html/ins_rist.html');

    if($_SESSION['permesso']=='Ristoratore'){

        $errors=array("nome"=>"",
                            "desc"=>"",
                            "tel"=>"",
                            "email"=>"",
                            "civ"=>"",
                            "cap"=>"");
        $num_errori=0;

        $nome='';
        $desc='';
        $categoria='';
        $tel='';
        $email='';
        $sito='';
        $ora_ap='';
        $ora_chiu='';
        $arr_giorno=array("lun"=>"",
                    "mar"=>"",
                    "mer"=>"",
                    "gio"=>"",
                    "ven"=>"",
                    "sab"=>"",
                    "dom"=>"");
        $via='';
        $civico='';
        $cap='';
        $citta='';
        $nazione='';

        /* controllo se è stato fatto il submit */
        if(isset($_POST['nome'])){
            $nome=$_POST['nome'];

            if(isset($_POST['b_descrizione'])){
                $desc=$_POST['b_descrizione'];
            }

            if(isset($_POST['categoria'])){
                $categoria=$_POST['categoria'];
            }

            if(isset($_POST['telefono'])){
                $tel=$_POST['telefono'];
            }

            if(isset($_POST['email'])){
                $email=$_POST['email'];
            }

            if(isset($_POST['sito'])){
                $sito=$_POST['sito'];
            }

            if(isset($_POST['o_apertura'])){
                $ora_ap=$_POST['o_apertura'];
            }

            if(isset($_POST['o_chiusura'])){
                $ora_chiu=$_POST['o_chiusura'];
            }
            
            if(isset($_POST['free_day'])){
                $giorno=$_POST['free_day'];
                $arr_giorno["$giorno"]='checked="checked"';
            }else{
                $giorno='';
            }

            if(isset($_POST['via'])){
                $via=$_POST['via'];
            }
            if(isset($_POST['civico'])){
                $civico=$_POST['civico'];
            }

            if(isset($_POST['citta'])){
                $citta=$_POST['citta'];
            }
            if(isset($_POST['cap'])){
                $cap=$_POST['cap'];
            }

            if(isset($_POST['nazione'])){
                $nazione=$_POST['nazione'];
            }
            /*
                FARE PER IMMAGINI
            */
            require_once('ristorante.php');
            $ristorante=new ristorante($nome, $desc, $categoria, $tel, $email, $sito, $ora_ap, $ora_chiu,$giorno, $via, $civico,
                             $cap, $citta, $nazione);

            $errors=$ristorante->getErrors();
            $num_errori=$ristorante->numErrors($errors);
           
        }   
        if($num_errori>0){
            $err="[Sono presenti $num_errori campi compilati non correttamente]";
        }else{
            $err='';
        }

        $page=str_replace('%NUM_ERRORI%',$err,$page);
        $page=str_replace('%ERR_NOME%',$errors['nome'],$page);
        $page=str_replace('%ERR_DESC%',$errors['desc'],$page);
        $page=str_replace('%ERR_TEL%',$errors['tel'],$page);
        $page=str_replace('%ERR_EMAIL%',$errors['email'],$page);
        $page=str_replace('%ERR_CIV%',$errors['civ'],$page);
        $page=str_replace('%ERR_CAP%',$errors['cap'],$page);

        $page=str_replace('[','<p class="msg_box err_box">',$page);
        $page=str_replace(']','</p>',$page);

        $page=str_replace('%VALUE_NOME%',$nome,$page);
        $page=str_replace('%VALUE_DESC%',$desc,$page);
        
        require_once("categoria.php");
        $page=str_replace('%CATEGORIA%',$list_categorie,$page);
        foreach($cat_result as $row){
            foreach($row as $value){
                if ($value!=$categoria){
                    $page=str_replace("%VALUE_".strtoupper($value)."_CAT%","",$page);
                }else{
                    $page=str_replace("%VALUE_".strtoupper($value)."_CAT%",'selected="selected"',$page);
                }
            }
        }

        $page=str_replace('%VALUE_CAT%',$categoria,$page);
        $page=str_replace('%VALUE_TEL%',$tel,$page);
        $page=str_replace('%VALUE_EMAIL%',$email,$page);
        $page=str_replace('%VALUE_SITO%',$sito,$page);
        $page=str_replace('%VALUE_OR_AP%',$ora_ap,$page);
        $page=str_replace('%VALUE_OR_CH%',$ora_chiu,$page);
        $page=str_replace('%LUN%',$arr_giorno['lun'],$page);
        $page=str_replace('%MAR%',$arr_giorno['mar'],$page);
        $page=str_replace('%MER%',$arr_giorno['mer'],$page);
        $page=str_replace('%GIO%',$arr_giorno['gio'],$page);
        $page=str_replace('%VEN%',$arr_giorno['ven'],$page);
        $page=str_replace('%SAB%',$arr_giorno['sab'],$page);
        $page=str_replace('%DOM%',$arr_giorno['dom'],$page);
        $page=str_replace('%VALUE_VIA%',$via,$page);
        $page=str_replace('%VALUE_CIV%',$civico,$page);
        $page=str_replace('%VALUE_CITTA%',$citta,$page);
        $page=str_replace('%VALUE_CAP%',$cap,$page);
        $page=str_replace('%VALUE_NAZ%',$nazione,$page);
        

    }else{
        header('location: access_denied.php');
        exit;
    }

    echo $page;
?>