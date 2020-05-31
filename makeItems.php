<?php
function makeItems()
{
  if (isset($_GET["nume-produs"]) && isset($_GET["nr-produse"])) {
    //butonul de cautare
    include VIEW . 'produse/search_db.phtml';
    include VIEW . 'produse/search_ebay.phtml';
  } else if (isset($_GET["categorie"])) {
    //
    require_once('core/Scrapping.php');

    $produse_de_afisat_emag = Select::scrapping_emag($_GET["categorie"]);
    //aici am pus if-ul in caz ca ceva esueaza la scraping si intorci false
    if ($produse_de_afisat_emag == false)
      header("Location:/index.php?scrap_esuat=1");
    else {
      foreach ($produse_de_afisat_emag as $produs) {
        display($produs['id'], $produs['nume'], $produs['link'], $produs['imagine'], $produs['rating'], $produs['pret'], $produs['disponibilitate'], $produs['updated']);
      }
    }
  }
  /* //aici ar trebui sa fie un buton. dar nu face nimic la apasare desi linkul este corect. ???
    $URL_current="$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $URL_current=substr($URL_current, 0, -1); //stergem ultimul caracter, numarul paginii
    $URL_current.=++$_GET['page']; //si il incrementam;
    echo $URL_current;
    ?>
    <a href="<?php echo $URL_current; ?>">Link</a>;
    <?php
    */
}
function display($id, $nume, $link, $imagine, $rating, $pret, $disponibilitate, $updated) //generam pentru fiecare 
{
  echo '<div class="grid-item">
                <img src=' . $imagine . '></img>
                <div class="butoane">
                  <button class="refresh" type ="button" title="Refresh">
                    <span class="tooltip">Actualizeaza</span>
                  </button>
                  <button class="compara" type="button" title="Compara">
                    <span class="tooltip">Compara</span>
                  </button>
                </div>
                <br>
                <a class="titlu" href=' . $link . '> ' . $nume . '</a>
                <br><br>
                <a class="review" href=' . $link . '/#reviews-section' . '> Catre review-uri </a>
                <div class="rating" style="--rating:' . $rating . ';"></div>
                <p class="pret">' . $pret . ' lei</p>';
  if (strpos($disponibilitate, 'ultimul') !== false || strpos($disponibilitate, 'ultimele') !== false) {
    echo '<p class="ultimul">' . $disponibilitate . '</p>';
  } else {
    echo '<p class=' . str_replace(" ", "-", $disponibilitate) . '>' . $disponibilitate . '</p>';
  }
  echo '<p class="update">Actualizat la ' . $updated . '</p>';
  echo "</div>";
}
