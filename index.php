<?php

/*
autor: Dominik Vladar
prace z predmetu KIV/OP
Zapadoceska univerzita v Plzni, Fakulta aplikovanych ved
*/

require_once "content.php";
require_once "data.php";
require_once "modules/expressions.php";
require_once "modules/functions.php";
require_once "modules/equations.php";
require_once "modules/systems_of_equations.php";
require_once "error.php";

if(isset($_POST["result_form_integer"]) and isset($_POST["result_form_fraction"]) and isset($_POST["result_form_random"]) and isset($_POST["number_of_questions"]) and isset($_POST["difficulty"]) and isset($_POST["topic"])){
    $topic = get_topic($_POST["topic"]);
    if(!input_fine($topic)){
        exit(1);
    }
    $result_form = array(
        "integer" => $_POST["result_form_integer"] == "1", 
        "fraction" => $_POST["result_form_fraction"] == "1",
        "random" => $_POST["result_form_random"] == "1"
    );
    $difficulty = (int) $_POST["difficulty"];
    $number_of_questions = (int) $_POST["number_of_questions"];
    write_header($_POST["topic"] . " - " . $topic->description, $topic->color);
    switch($topic->name){
        case "Výrazy":
            $expression = generate_expression($difficulty, $number_of_questions, $result_form);
            break;
        case "Funkce":
            $expression = generate_functions($difficulty, $number_of_questions, $result_form);
            break;
        case "Rovnice":
            $expression = generate_equations($difficulty, $number_of_questions, $result_form);
            break;
        case "Soustavy rovnic":
            $expression = generate_systems_of_equations($difficulty, $number_of_questions, $result_form);
            break;
    }
    
    echo $expression[0];
    if($topic->name != "Funkce"){
    echo '<div class="mt-3 mb-5 w-100" id="code" style="background: color-mix(in srgb, '.$topic->color.' 30%, transparent);"><div id="code_header" class="px-3 py-1"> \( \LaTeX\)ový kód <button id="show_latex" onclick="show_latex();" class="btn btn-light"><i class="fa-solid fa-chevron-down"></i></button> </div><div class="p-4" id="actual_code" style="display: none;">'. str_replace('$', "{dollar}", $expression[1]) .'</div></div>';
    }
    if($topic->name == "Rovnice" || $topic->name == "Soustavy rovnic"){
        ?>
        <div class="mb-5 w-100" style="background: #EEE;">
            <div id="checks_header" class="px-3 py-1">
                <b>Automatická kontrola:</b> <?php echo $expression[3]; ?>
                <button class="btn btn-light" id="show_checks" onclick="show_checks();">
                <i class="fa-solid fa-chevron-down"></i>
                </button>
            </div>
            <div class="mt-3 mb-5 w-100 p-4" style="display: none;" id="checks">
                <?php echo $expression[2]; ?>
            </div>
        </div>
        <?php
    }
    ?>
    <script>
        /**
         * Zobrazi kontrolu rovnic.
         */
        function show_checks(){
            document.getElementById("checks").removeAttribute("style");
            let button = document.getElementById("show_checks");
            button.innerHTML = '<i class="fa-solid fa-chevron-up"></i>';
            button.setAttribute("onclick", "hide_checks();");
        }

        /**
         * Skryje kontrolu rovnic.
         */
        function hide_checks(){
            document.getElementById("checks").setAttribute("style", "display: none;");
            let button = document.getElementById("show_checks");
            button.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
            button.setAttribute("onclick", "show_checks();");
        }

        /**
         * Ukaze latexovy kod.
         */
        function show_latex() {
            //musime zabranit vykresleni obsahu jako Latex kodu - na serveru nahradime vsechny $ za {dollar}
            let code_container = document.getElementById("code");
            code_container.innerHTML = code_container.innerHTML.replaceAll("{dollar}", "&dollar;");
            document.getElementById("actual_code").removeAttribute("style");
            let button = document.getElementById("show_latex");
            button.innerHTML = '<i class="fa-solid fa-chevron-up"></i>';
            button.setAttribute("onclick", "hide_latex();");
        };

        /**
         * Skryje latexovy kod.
         */
        function hide_latex(){
            document.getElementById("actual_code").setAttribute("style", "display: none;");
            let button = document.getElementById("show_latex");
            button.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
            button.setAttribute("onclick", "show_latex();");
        }
    </script>
    <?php
}
else{
    write_header("Generátor problémů středoškolské matematiky");
    write_main_page_content($topics);
}
write_footer();
?>