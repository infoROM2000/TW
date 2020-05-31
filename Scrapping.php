<?php
require_once 'update.php';
define('minute_update', 240); //la cat timp vrem sa actualizam, in minute; 
class Select
{
  public static function scrapping_altex($categorie)
  {
    //select from categorie ;
  }
  public static function scrapping_emag($categorie)
  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "produse_emag";
    $page = 1;
    $nr_produse_pagina = 30;
    $clauza_where = ""; //pentru filtre
    $clauza_order = ""; //pentru crescator/descrescator
    $con = new mysqli($servername, $username, $password, $dbname); //cream conexiunea

    if ($con->connect_error) {
      die("Nu s-a reusit conectarea la baza de date: " . $con->connect_error);
    }
    if (isset($_GET['page'])) {
      $page = $_GET['page'];
    }
    if (isset($_GET['order'])) { //se poate ordona dupa o categorie; preferabil nume/rating/pret/updated
      if(strpos($_GET['order'],"nume") !==false || strpos($_GET['order'],"rating") !==false || strpos($_GET['order'],"pret") !==false || strpos($_GET['order'],"updated") !==false )
        $clauza_order = str_replace("_"," ",$_GET['order']);
    }
    //se pune in get argumente de tipul order=updated_desc
    /*
    if (isset($_GET['nr_produse_pagina'])) {
      // daca exista, atunci e schimbat defaultul de 30
      $nr_produse_pagina = $_GET['nr_produse_pagina'];
    }
    if (isset($_GET['rating_minim'])) {
      $clauza_where = $clauza_where . "rating>" . $_GET['rating_minim'];
    }
    if ($clauza_where != "")
      $clauza_where = " WHERE " . $clauza_where;
  */
    if($clauza_order!="")
      $clauza_order=" ORDER BY ".$clauza_order;
    $inceput = ($page - 1) * $nr_produse_pagina;
    $stmt = $con->prepare("SELECT id,link,updated FROM " . $categorie . $clauza_where . $clauza_order . " LIMIT ?,?"); //luam din tabela cu numele transmis prin get
    $stmt->bind_param("ii", $inceput, $nr_produse_pagina);
    $stmt->execute();
    $rezultat = $stmt->get_result();
    $output = $rezultat->fetch_all(MYSQLI_ASSOC);
    date_default_timezone_set("Europe/Bucharest"); //altfel e cu 3 ore in urma
    foreach ($output as $element) {
      print "\n";
      $minute = (time() - strtotime($element['updated'])) / 60; //luam diferenta intre timpul curent si data ultimei updatari, in minute
      if ($minute > minute_update) //daca nu a mai fost actualizat de 2 ore, actualizam
        Update::updat($dbname, $categorie, $element['id'], $element['link']);
    }
    $stmt = $con->prepare("SELECT * FROM " . $categorie . $clauza_where . $clauza_order . " LIMIT ?,?"); //luam din tabela cu numele transmis prin get
    $stmt->bind_param("ii", $inceput, $nr_produse_pagina);
    $stmt->execute();
    $rezultat = $stmt->get_result();
    $output = $rezultat->fetch_all(MYSQLI_ASSOC);
    return $output;
  }
}
/*
Preferam ca la update sa accesam pagina produsului, si nu paginile de pe care am luat initial, din mai multe motive:
1. E destul de greu de gasit pagina de pe care am luat produsul, initial, fiind foarte posibil sa:
2. Nici nu mai fie acolo, pentru ca paginile sunt generate dinamic si in general produsele care nu mai sunt in stoc nu mai apar
3. Link-ul produsului ramane cam acelasi.
4. Intr-o aplicatie reala, un update la 2 ore pentru fiecare nu e chiar asa de mult, pentru ca ar fi multi utilizatori si fiecare ar prelua o parte din update-uri
5. Am putea face si un buton de update 
*/