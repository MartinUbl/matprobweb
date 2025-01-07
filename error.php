<?php

/*
autor: Dominik Vladar
prace z predmetu KIV/OP
Zapadoceska univerzita v Plzni, Fakulta aplikovanych ved
*/

/**
 * Zkontroluje, jestli jsou spravne nastaveny parametry generovani.
 * @param $topic informace o tematu ke generovani.
 * @return TRUE, pokud jsou parametry v poradku, jinak FALSE.
 */
function input_fine($topic){
    if(is_null($topic)) {
        show_error();
        return FALSE;
    }
    $at_least_one_defined = FALSE;
    foreach([$_POST["result_form_integer"], $_POST["result_form_fraction"], $_POST["result_form_random"]] as $param){
        if($param != "0" and $param != "1") {
            show_error();
            return FALSE;
        }
        elseif($param == "1"){
            $at_least_one_defined = TRUE;
        }
    }
    if(!$at_least_one_defined){
        show_error();
        return FALSE;
    }
    try{
        $difficulty = (int)$_POST["difficulty"];
        $number_of_questions = (int) $_POST["number_of_questions"];

        if($number_of_questions < 1 or $difficulty < 1 or $difficulty > 5){
            show_error();
            return FALSE;
        }
        if($number_of_questions > 500 || ($number_of_questions > 50 && $topic->name == "Funkce")){
            show_error("Je možné generovat jen 500 problémů najednou a pouze 50 funkcí.");
            return FALSE;
        }
    }
    catch(Exception $e){
        show_error();
        return FALSE;
    }
    return TRUE;
}

/**
 * Zobrazi na strance informaci, ze nastala chyba.
 * @param msg zprava, kterou chceme ukazat uzivateli.
 */
function show_error($msg = "Zadaným parametrům neodpovídá žádný generátor problémů."){
?>
<!DOCTYPE HTML>
<html lang="cs">
  <head>
    <meta charset="utf-8">
    <meta name="author" content="Dominik Vladař">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Generátor problémů středoškolské matematiky.">
    <!--import knihoven-->
    <!-- Bootstrap import -->        
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <!-- konec importu knihoven -->
    <link rel="stylesheet" href="css.css">
    <!-- favicon -->
<link rel="icon" type="image/x-icon" href="favicon.ico">
    <title>Nenalezeno</title>
  </head>
  <body class="bg-danger text-center my-5 py-5 text-light">
    <h1><?php echo $msg; ?></h1>
    <div><a href="./" class="text-light" style="text-decoration: underline;">
            Zpět na hlavní stránku
        </a></div>
  </body>
</html>
<?php
}

?>