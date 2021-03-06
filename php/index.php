<?php
    require_once("sessione.php");
    require_once("connessione.php");
    require_once("addItems.php");
    require_once("ristorante.php");
   
    $page= (new addItems)->add("../html/index.html");
    $page = str_replace('><a href="index.php">Home</a>', ' class="active">Home',$page);
    $error="";
    $list='<dl class="card_list rist_list">';
    $tipologia="";
    $no_error=true;
    $obj_connection = new DBConnection();
    $ricercaText="";
    $ricercaPosONome="";
    $ricercaTipo="";
    $results_per_page = 3;
    $pagesList="";
    if (isset($_GET["pagen"])&&is_numeric($_GET["pagen"])){ 
        $pagen  = $_GET["pagen"]; 
    } 
    elseif(isset($_GET["pagen"])&&!is_numeric($_GET["pagen"])){
        header('location: 404.php');
    }
    else{ 
        $pagen=1; 
    }
    $start_from = ($pagen-1) * $results_per_page;
    if(!$obj_connection->create_connection()){
        $error=$error."<div class=\"msg_box error_box\">Errore di connessione al database</div>";
        $no_error=false;
    }
    if($no_error){
        //Inserisce lista tipologie in menu a tendina
        $queryResult=$obj_connection->connessione->query("SELECT * FROM `categoria`");
        while($row=$queryResult->fetch_array(MYSQLI_ASSOC)){
            $nome=$row['Nome'];
            $tipologia=$tipologia."<option value=\"$nome\">$nome</option>";
        }
        //Mette come suggerimenti i miglior recensiti
        $queryResult=$obj_connection->connessione->query("SELECT ID_Ristorante, AVG(Stelle) AS Media FROM `recensione` GROUP BY ID_Ristorante ORDER BY Media DESC LIMIT 3");//Se ha recensioni è già stato approvato
        while($row=$queryResult->fetch_array(MYSQLI_ASSOC)){
            $id=$row['ID_Ristorante'];
            $queryResult2=$obj_connection->connessione->query("SELECT * FROM `ristorante` WHERE ID=$id");
            while($row2=$queryResult2->fetch_array(MYSQLI_ASSOC)){
                $ristorante = new ristorante($row2);
                $list=$list.$ristorante->createItemRistorante();
            }            
        }
        //Ricerca effettiva
        if(isset($_GET['cerca'])){
            $page = str_replace('Suggerimenti', 'Risultati',$page);
            if(isset($_GET['search'])){
                $ricercaText=$_GET['search'];
            }
            else{
                $error=$error."<div class=\"msg_box error_box\">È necessario inserire un valore da cercare.</div>";
            }
            $ricercaText=$obj_connection->escape_str(trim(htmlentities($ricercaText)));
            $ricercaPosONome=$_GET['filter'];
            $ricercaTipo=$_GET['tipologia'];
            $query="SELECT ID FROM `ristorante` WHERE Approvato='Approvato' AND ";
            if($ricercaPosONome==0){//per posizione
                $query=$query."`Citta` LIKE '%$ricercaText%' ";
            }
            else{//per nome 
                $query=$query."`Nome` LIKE '%$ricercaText%' ";
            }
            if($ricercaTipo!=="qualsiasi"){
                $query=$query."AND `Categoria`='$ricercaTipo'";
            }
            $result = $obj_connection->connessione->query($query);
            $rowcount=mysqli_num_rows($result);
            $total_pages = ceil($rowcount / $results_per_page);
            if($pagen>$total_pages&&$total_pages>0){
                header('location: 404.php');
            }
            $query="SELECT t1.ID, Media\n"
            . "FROM\n"
            . "($query) AS t1\n"
            . "LEFT JOIN\n"
            . "(SELECT ID_Ristorante, AVG(Stelle) AS Media FROM `recensione` GROUP BY ID_Ristorante ) AS t2\n"
            . "ON\n"
            . "(t1.ID=t2.ID_Ristorante)\n"
            . "ORDER BY Media DESC LIMIT $start_from,$results_per_page ";

            $queryResult=$obj_connection->connessione->query($query);
            $list='<dl class="card_list rist_list">';
            if (mysqli_num_rows($queryResult) > 0) {
                while($row=$queryResult->fetch_array(MYSQLI_ASSOC)){
                    $id=$row['ID'];
                    $queryResult2=$obj_connection->connessione->query("SELECT * FROM `ristorante` WHERE ID=$id");
                    while($row2=$queryResult2->fetch_array(MYSQLI_ASSOC)){
                        $ristorante = new ristorante($row2);
                        $list=$list.$ristorante->createItemRistorante();
                    } 
                }
            }
            else{
                $list="<div class=\"msg_box error_box\">Nessun risultato corrispondente ai criteri di ricerca.</div>";
            }
            $i=1;
            $pagesList=" <div class=\"center\"> <div class=\"pagination\">";
            $ind=$_SERVER['REQUEST_URI'];
            if($pagen>1){
                $prec=$pagen-1;
                $ind=clearInd($ind,$total_pages);
                $pagesList= $pagesList."\n<a href=\"$ind&pagen=$prec\">&laquo;Precedente</a>";
            }
            while($i<=$total_pages){                
                $ind=clearInd($ind,$total_pages);
                if($i!=$pagen){
                    $pagesList= $pagesList."\n<a href=\"$ind&pagen=$i\">$i</a>";
                }
                else{
                    $pagesList= $pagesList."<span class=\"active\">$i</span>";
                }
                $i++;
            }
            if($pagen<$total_pages){
                $succ=$pagen+1;
                $pagesList= $pagesList."\n<a href=\"$ind&pagen=$succ\">Successiva&raquo</a>";
            }
            $pagesList= $pagesList."</div></div>";
        }
    }
    $list .= "</dl>";
    $page = str_replace('%ERROR%', $error,$page);
    $page = str_replace('%TIPOLOGIA%', $tipologia,$page);
    $page = str_replace('%PAGESLIST%', $pagesList,$page);
    $page = str_replace('%LIST%', $list,$page);
    echo $page;
    $obj_connection->close_connection();
    function clearInd($ind,$total_pages){
        for($z=1;$z<=$total_pages;$z++){//Evita che nell'url ci siano robe tipo &pagen=2&pagen=1&pagen=3&pagen=2&pagen=4 mettendo solo la pagina corrente e non tutta la history delle pagine visitate
            $ind=str_replace("&pagen=$z","",$ind);
         }
        return $ind;
    }
?>