<?php
class Update
{
    public static function updat($dbname, $categorie, $id, $link) //e de ajuns unul dintre link si id, dar o interogare in minus nu strica
    { //aici vom face scraping chiar de pe pagina produsului
        $servername = "localhost";
        $username = "root";
        $password = "";

        $conn = mysqli_connect($servername, $username, $password, $dbname); //ne conectam la bd
        // Check connection
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        $dom = file_get_html($link); // si incepem sa facem scrapping de pe pagina produsului...
        // in principiu se vor schimba doar pretul,rating-ul si disponibilitatea... restul sunt constante
        if (!empty($dom)) {

            //pret
            $pret = $dom->find('.product-new-price', 0)->find('text', 0)->innertext;
            $pret = str_replace("&#46;", "",$pret);
            //nu putem lua pur si simplu cu intval pentru ca ia numai ce e la dreapta punctului...
            //sistemul englez vs cel francez de numerotare... (primul foloseste . ca decimal point si virgula ca separator de grupuri de 3 cifre, al doilea(adica noi) invers)
            $pret = intval($pret);

            //rating
            $rat=$dom->find('span[class=star-rating-text gtm_rp101318]',0);
            if(is_object($rat))
            $rating=floatval($rat->innertext);
            else
            $rating=floatval(0);

            //disponibilitate
            if ($dom->find('div[class=product-highlight product-page-pricing]', 0)->children(2)->tag == "span") //la stoc limitat mai este un adaugat un tag a
                $disp = $dom->find('div[class=product-highlight product-page-pricing]', 0)->children(2);
            else
                $disp = $dom->find('div[class=product-highlight product-page-pricing]', 0)->children(3);

            $disponibilitate = $disp->innertext;
            $sql = "UPDATE $categorie SET rating=?,pret=?,disponibilitate=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("disi", $rating, $pret, $disponibilitate,$id);
            $stmt->execute();
        }
        $conn->close();
    }
}
