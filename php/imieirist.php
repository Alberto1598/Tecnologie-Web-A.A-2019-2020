<?php
    require_once('sessione.php');
    require_once("addItems.php");
    require_once('connessione.php');
    require_once('ristorante.php');

    $page= (new addItems)->add("../html/imieirist.html");
    $page=str_replace('><a href="imieirist.php?type=0">I miei ristoranti</a>', 'class="active">I miei ristoranti',$page);

    if($_SESSION['logged']==true){
        if($_SESSION['permesso']=='Ristoratore'){
            //3 visualizzazioni

            //tab ristoranti approvati
            $tab='';
            if(isset($_GET['type'])){
                switch($_GET['type']){
                    case 0: {
                        $tab='<span class="tab_item">Approvati</span>
                        <a class="tab_item" href="imieirist.php?type=1">In fase di approvazione</a>
                        <a class="tab_item" href="imieirist.php?type=2">Rifiutati</a>';
                        $stato='Approvato';
                    break;
                    }
                    case 1: {
                        $tab='<a class="tab_item" href="imieirist.php?type=0">Approvati</a>
                        <span class="tab_item">In fase di approvazione</span>
                        <a class="tab_item" href="imieirist.php?type=2">Rifiutati</a>';
                        $stato='In attesa';
                    break;
                    }
                    case 2: {
                        $tab='<a class="tab_item" href="imieirist.php?type=0">Approvati</a>
                        <a class="tab_item" href="imieirist.php?type=1">In fase di approvazione</a>
                        <span class="tab_item">Rifiutati</span>';
                        $stato='Rifiutato';
                    break;
                    }
                }
                $page=str_replace('%TAB_MENU_CONTENT%',$tab,$page);

                $obj_connection=new DBConnection();
                $obj_connection->create_connection();
                $query="SELECT * FROM ristorante WHERE ID_Proprietario=".$_SESSION['ID']." AND Approvato=\"$stato\"";
                if($query_rist=$obj_connection->connessione->query($query)){
                    $array_rist=$obj_connection->queryToArray($query_rist);
                    if(count($array_rist)>0){
                        $list_ristoranti='<dl class="card_list rist_list">';
                        foreach($array_rist as $value){
                            $ristorante=new ristorante($value);
                            $list_ristoranti.=$ristorante->createItemRistorante();
                        }
                        $list_ristoranti.='</dl>';
                    }else{
                        $list_ristoranti='<p>Non sono presenti ristoranti</p>';
                    }
                    $page=str_replace('%LIST%',$list_ristoranti,$page);
                }else{
                    //errore nella query
                }

            }else{
                //reindirizzamento se non è definito il type
                header('location: ../html/404.html');
                exit;
            }

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