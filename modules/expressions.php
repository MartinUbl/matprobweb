<?php

/*
autor: Dominik Vladar
prace z predmetu KIV/OP
Zapadoceska univerzita v Plzni, Fakulta aplikovanych ved
*/

require_once "math.php";
/**
 * Prevede vektor ve formatu <vedouci_cislo>*(<promenna1>^<cislo1>)*(<promenna2>^<cislo2>*...)
 * reprezentovane vektorem [<vedouci_cislo>, <cislo1>, <cislo2>, ...] a polem promennych do 
 * spravneho tvaru jako retezec.
 * @param $vector zmineny vektor, napr. pro 5a^2c^5 a promenne [a, b, c] bude tento vektor [5, 2, 0, 5].
 * @param $unknowns pole s nazvy promennych.
 * @return vektor interpretovany v retezcovem formatu.
 */
function string_from_vector($vector, $unknowns){
    $temp = "";
    for($i = 0; $i < count($vector); $i++){
        $val = $vector[$i];
        if($i == 0){
            //vedouci cislo
            $temp .= $vector[$i];             
        }
        elseif($val == 0){
            continue;
        }
        elseif($val == 1){
            $temp .= $unknowns[$i-1];
        }
        else{
            $temp .= $unknowns[$i-1]."^{".$vector[$i]."}";
        }
    }
    return $temp;
}

/**
 * Prevede matici reprezentujici vyraz na retezcovou reprezentaci.
 * @param $matrix matice ve tvaru [[<vedouci_cislo1>,<vedouci_cislo2>,<vedouci_cislo3>,...], [<exponent_promenne1_1>, <exponent_promenne1_2>,...],[<exponent_promenne2_1>, <exponent_promenne2_2>,...],...], napr. matice [[5, 3, 2], [5, 2, 7], [8 , 8, 0]] a promenne [a, b] znazornuji vyraz 5a^{5}b^{8}+3a^{2}b^{8}+2a^{7}.
 * @param $unknowns pole s nazvy promennych.
 * @return vektor predany jako parametr reprezentovany jako retezec.
 */
function string_from_matrix($matrix, $unknowns){
    $temp = "";
    for($j = 0; $j < count($matrix[0]); $j++){
        for($i = 0; $i < count($matrix); $i++){
            $val = $matrix[$i][$j];
            if($i == 0){
                //vedouci cislo
                if(!is_numeric($matrix[$i][$j]) || abs($matrix[$i][$j]) != 1){
                    $temp .= $matrix[$i][$j];             
                }
                else{
                    $broken = FALSE;
                    for($q = 1; $q < count($matrix); $q++){
                        if($matrix[$q][$j] != 0){
                            $broken = TRUE;
                            break;
                        }
                    }
                    if(!$broken){
                        $temp .= $matrix[$i][$j];
                    }
                    elseif($matrix[$i][$j] == -1){
                        $temp .= "-";
                    }
                }
            }
            elseif($val == 0){
                continue;
            }
            elseif($val == 1){
                $temp .= $unknowns[$i-1];
            }
            else{
                $temp .= $unknowns[$i-1]."^{".$matrix[$i][$j]."}";
            }
        }
        if($j+1 < count($matrix[0]) and $matrix[0][$j+1] > 0){
            $temp .= "+";
        }
    }
    return $temp;
}

/**
 * @param $matrix matice.
 * @param $col_index slopec matice, se kterym ostatni kontrolujeme.
 * @return TRUE pokud ma nejaky sloupec matice stejne hodnoty jako sloupec s indexem $col_index, jinak false. Vedouci cleny se nezapocitavaji.
 */
function some_cols_equals($matrix, $col_index){
    $equals = FALSE;
    for($j = 0; $j < count($matrix[0]); $j++){
        if($j == $col_index) continue;
        $equals = TRUE;
        for($i = 1; $i < count($matrix); $i++){
            if($matrix[$i][$j] != $matrix[$i][$col_index]){
                $equals = FALSE;
                break;
            }
        }
        if($equals){ 
            return TRUE;
        }
    }
    return FALSE;
}

/**
 * Cilem je vygenerovat matici, jejiz sloupce jsou odlisne krome prvniho radku, ktery specifikuje vedouci cislo,
 *  abychom nemeli napr. situaci 2a^6b^3 + 5a^6b^3 - zde by bylo mozne oba cleny jednoduse secist - proto musime zkontrolovat odlisnost sloupcu pomoci
 * teto funkce.
 * @param $matrix vygenerovana matice.
 * @param $unknowns pole s nazvy neznamych.
 * @param $max_numbers maximalni velikosti poutivanych cisel.
 * @param $expression_count pocet clenu ve vyrazu.
 * @return budto puvodni nezmenenou matici pokud jsou jeji sloupce bez prvniho radku vzajemne odlisne, jinak znovu vygenerovanou matici splnujici tento pozadavek.
 *  */
function correct_expression($matrix, $unknowns, $max_numbers, $expression_count){
    for($i = 1; $i < count($matrix[0]); $i++){
        if(some_cols_equals($matrix, $i)){
            return generate_polynom($unknowns, $max_numbers, $expression_count)[3];
        }
    }
    return $matrix;
}

/**
 * Vynasobi polynom stanovenym clenem.
 * @param $matrix puvodni vyraz reprezentovany jako matice.
 * @param $vector clen, kterym budeme polynom nasobit reprezentovany jako vektor.
 * @return polynom po vynasobeni reprezentovany jako matice.
 */
function multiply_polynom_by($matrix, $vector){
    for($i = 0; $i < count($matrix); $i++){
        for($j = 0; $j < count($matrix[0]); $j++){
            if($i == 0){
                $matrix[$i][$j] *= $vector[$i];
            }
            else{
                $matrix[$i][$j] += $vector[$i];
            }
        }
    }
    return $matrix;
}

/**
 * Vyhodnoti predany polynom pro stanovene hodnoty neznamych.
 * @param $matrix vyraz k vyhodnoceni.
 * @param $var_values hodnoty neznamych.
 * @return hodnota polynomu po dosazeni neznamych reprezentovana jako retezec.
 */
function evaluate_matrix($matrix, $var_values){
    $result = 0;
    for($j = 0; $j < count($matrix[0]); $j++){
        $temp_res = 0;
        for($i = 0; $i < count($matrix); $i++){
            if($i == 0){
                $temp_res = $matrix[$i][$j];
            }
            else{
                $temp_res *= pow($var_values[$i-1], $matrix[$i][$j]);
            }
        }
        $result += $temp_res;
    }
    return "" . $result;
}

/**
 * Vygeneruje polynom v zavislosti na stanovenych pozadavcich, dale clen kterym tento polynom vynasobime a vysledek tohoto nasobeni.
 * @param $unknowns pole s nazvy neznamych.
 * @param $max_numbers maximalni velikost cisla, ktere je mozne vzhledem k nastavene narocnosti
 * ve vygenerovanem vyrazu pouzit.
 * @param $expression_count pocet clenu v nove vygenerovanem polynomu.
 * @return pole s timto obsahem: [<polynom pred vynasobenim druhym jako retezec>, <polynom kterym nasobime jako retezec>, <vysledek nasobeni jako retezec>, <polynom pred vynasobenim druhym jako matice>, <polynom kterym nasobime jako matice>, <vysledek nasobeni jako matice>].
 */
function generate_polynom($unknowns, $max_numbers, $expression_count){
    $matrix = [];//v matici: 0==<promenna>^0, 1==<promenna>^1,...
    for($j = 0; $j < count($unknowns) + 1; $j++){
        $matrix[$j] = [];
    }
    for($i = 0; $i < $expression_count; $i++){
        for($j = 0; $j < count($unknowns)+1; $j++){
            if($j == 0){
            //vedouci cislo (bez promenne)
                $matrix[$j][$i] = rand(1, $max_numbers);
            }
            else{
                $matrix[$j][$i] = max(rand(-((int)$max_numbers/2), $max_numbers), 0);
            }
        }
    }
    
    $divide = gcd($matrix[0]);
    for($i = 0; $i < count($matrix[0]); $i++){
        $matrix[0][$i] /= $divide;
        if($i != 0 and rand(0, 1) == 0){
            $matrix[0][$i] *= -1;
        }
    }

    for($i = 1; $i < count($matrix); $i++){
        $matrix[$i] = minus_min($matrix[$i]);
    }

    $matrix = correct_expression($matrix, $unknowns, $max_numbers, $expression_count);
    $result = [];

    for($i = 0; $i < count($unknowns) + 1; $i++){
        if($i == 0){
            $result[] = rand(2, $max_numbers);
        }
        else{
            $result[] = max(rand(-((int)$max_numbers/2), $max_numbers), 0);
        }
    }

    $multiplied = multiply_polynom_by($matrix, $result);


    return [string_from_matrix($matrix, $unknowns), string_from_vector($result, $unknowns),
     string_from_matrix($multiplied, $unknowns), $matrix, $result, $multiplied];
}

/**
 * @param $difficulty narocnost definovana uzivatelem.
 * @return pole s nazvy promennych, nejvetsim moznym cislem, ktere je mozne pro generovani pouzit 
 * a poctem clenu ve vygenerovanem polynomu.
 */
function get_parameters_to_difficulty($difficulty){
    if(rand(0, 1) == 1){
        $all_vars = ["a", "b", "c"];
    }
    else{
        $all_vars = ["x", "y", "z"];
    }

    switch($difficulty){
        case 1:
            $var_count = rand(1, 2);
            $max_numbers = 5;
            $expression_count = rand(2, 3);
            break;
        case 2:
            $var_count = rand(1, 2);
            $max_numbers = 10;
            $expression_count = rand(2, 3);
            break;
        case 3:
            $var_count = rand(1, 3);
            $max_numbers = 10;
            $expression_count = rand(3, 4);
            break;
        case 4:
            $var_count = rand(2, 3);
            $max_numbers = 10;
            $expression_count = rand(3, 4);
            break;
        default:
            $var_count = rand(2, 3);
            $max_numbers = 20;
            $expression_count = rand(4, 5);
            break;
    }

    $vars = [];

    for($q = 0; $q < $var_count; $q++){
        $vars[] = $all_vars[$q];
    }
    return [$vars, $max_numbers, $expression_count];
}


/**
 * Vytvori priklad na jeden z nasledujicich vzorecku: (a+b)^2, (a-b)^2, (a+b)*(a-b).
 * @param $difficulty obtiznost.
 * @param $result_form forma, v jake ma byt vysledek: integer, fraction nebo random.
 * @return pole s timto obsahem: [<vzorecek s dosazenymi cisly>, <vysledne roznasobeni>].
 */
function formula($difficulty, $result_form){
    switch($result_form){
        case "integer":
            $num = rand(2, $difficulty < 3? 7:15);
            $representation = "" . $num;
            break;

        case "fraction":
            $num = rand(2, $difficulty < 3? 7:15);
            do{
                $num2 = rand(2, $difficulty < 3? 7:15);
            }
            while(gcd_temp($num2, $num) != 1);
            $representation = "\\frac{" . $num . "}{" . $num2 . "}";
            $old_num = $num;
            $num /= $num2;       
            break;

        case "random":
            $num = rand(1000, $difficulty < 3? 7000:15000)/1000.0;
            $representation = "" . $num;
            break;
    }
    $var = ["a", "q", "w", "x", "y", "z"][rand(0, 5)];
    $var_mult = max(rand(-((int)$difficulty/2), $difficulty), 1);
    $var_mult_representation = $var_mult == 1? "": "" . $var_mult;
    $num_pow = $result_form == "fraction"? "\\frac{" . pow($old_num, 2) . "}{" . pow($num2, 2) . "}" : pow($num, 2);
    switch(rand(0, 2)){
        case 0:
            return [
                "(" . $var_mult_representation . $var . " + " . $representation . ")^{2}",
                ($var_mult == 1? "": pow($var_mult, 2)) . $var . "^{2} + " . ($result_form == "fraction"? simplify_fraction(2*$var_mult*$old_num, $num2) : (2*$var_mult*$num)) . $var . " + " . $num_pow
        ];
            break;

        case 1:
            return [
                "(" . $var_mult_representation . $var . " - " . $representation . ")^{2}",
                ($var_mult == 1? "": pow($var_mult, 2)) . $var . "^{2} - " . ($result_form == "fraction"? simplify_fraction(2*$var_mult*$old_num, $num2) : (2*$var_mult*$num)) . $var . " + " . $num_pow
        ];
            break;

        case 2:
            return ["(" . $var_mult_representation .  $var . " + " . $representation . ") \cdot (" . $var_mult_representation . $var . " - " . $representation . ")", ($var_mult == 1? "": pow($var_mult, 2)) . $var . "^{2} - " . $num_pow];
            break;
    }
}

/**
 * Vygeneruje vyrazy odpovidajici pozadavkum uzivatele, vcetne LateXoveho kodu.
 * @param $difficulty obtiznost.
 * @param $number_of_questions pocet rovnic ke generovani.
 * @param $result_form vyjadruje, v jake forme jsou pripustne reseni rovnice.
 * @return pole s nasledujicim obsahem: [<HTML kod zobrazeny uzivateli>, <Latexovy kod>].
 *  */
function generate_expression($difficulty, $number_of_questions, $result_form){
    global $operators;


    $to_return = '<div class="h5">Vytkněte největší společnou část.</div><div class="row">';
    $left = '<div class="col-6">';
    $right = '<div class="col-6">';
    $latex = "\\section{Vytkněte největší společnou část.}<br>";

    $sections = 5;
    $i = 0;


    for(; $i < $number_of_questions/$sections; $i++){      
        [$vars, $max_numbers, $expression_count] = get_parameters_to_difficulty($difficulty);
        $data = generate_polynom($vars, $max_numbers, $expression_count);
        
        $left .= "\(".$data[2]." \)<br>";
        $right .= "\([".$data[1]. "\\cdot (". $data[0] .")]\)<br>";

        $latex .= "$$" .$data[2]. " \\quad [".$data[1]. "\\cdot (". $data[0] .")]$$<br>";
    }
    $left .= "</div>";
    $right .= "</div>";

    $to_return .= $left . $right . "</div>";

    $left = '<div class="col-6">';
    $right = '<div class="col-6">';

    $forms = [];
    foreach(["integer", "fraction", "random"] as $key){
        if($result_form[$key]){
            $forms[] = $key;
        }
    }

    if($i < (2*$number_of_questions)/$sections){

    $to_return .= '<div class="h5">Roznásobte.</div><div class="row">';
    $latex .= "<br>\\section{Roznásobte.}<br>";

    for(; $i < (2*$number_of_questions)/$sections; $i++){
        [$vars, $max_numbers, $expression_count] = get_parameters_to_difficulty($difficulty);
        $data = generate_polynom($vars, $max_numbers, $expression_count);
        $form_now = $forms[rand(0, count($forms) - 1)];
        $multiplier = $data[1];
        $inside = $data[2];
        $num = 1;
        if($form_now == "fraction"){
            do{
            $num = rand(1, $max_numbers);
            $t = simplify_fraction($data[4][0], $num);
            } while(is_numeric($t));
            $data[4][0] = $t;
            $multiplier = string_from_vector($data[4], $vars);
            for($q = 0; $q < count($data[5][0]); $q++){
                $data[5][0][$q] = simplify_fraction($data[5][0][$q], $num);
                if(str_contains($data[5][0][$q], "-")){
                    $data[5][0][$q] = "-" . str_replace("-", "", $data[5][0][$q]);
                }
            }
            $inside = string_from_matrix($data[5], $vars);
        }
        elseif($form_now == "random"){
            $num = rand(1000, $difficulty < 3? 7000:15000)/1000.0;
            $data[4][0] = $data[4][0]*$num;
            $multiplier = string_from_vector($data[4], $vars);
            for($q = 0; $q < count($data[5][0]); $q++){
                $data[5][0][$q] *= $num;
            }
            $inside = string_from_matrix($data[5], $vars);
        }
        $left .= "\(".$multiplier. "\\cdot (". $data[0] .")\)<br>";
        $right .= "\([".$inside."]\)<br>";
        $latex .= "$$".$multiplier. "\\cdot (". $data[0] .") \\quad [".$inside."]$$<br>";
    }

    $left .= "</div>";
    $right .= "</div>";

    $to_return .= $left . $right . "</div>";
    }

    if($i < (3*$number_of_questions)/$sections){

    $left = '<div class="col-6">';
    $right = '<div class="col-6">';

    $to_return .= '<div class="h5">Rozložte podle vzorců. Výsledný výraz nesmí obsahovat závorky.</div><div class="row">';
    $latex .= "<br>\\section{Rozložte podle vzorců. Výsledný výraz nesmí obsahovat závorky.}<br>";

    for(; $i < (3*$number_of_questions)/$sections; $i++){
        $data = formula($difficulty, $forms[rand(0, count($forms) - 1)]);
        $left .= "\(".$data[0] . "\)<br>";
        $right .= "\([".$data[1] ."]\)<br>";
        $latex .= "$$".$data[0] . " \\quad [".$data[1]."]$$<br>";
    }

    $left .= "</div>";
    $right .= "</div>";
    $to_return .= $left . $right . "</div>";
    }
    if($i < (4*$number_of_questions)/$sections){

    $left = '<div class="col-6">';
    $right = '<div class="col-6">';

    $to_return .= '<div class="h5">Pomocí vzorců převeďte na mocninný nebo součinný tvar.</div><div class="row">';
    $latex .= "<br>\\section{Pomocí vzorců převeďte na mocninný nebo součinný tvar.}<br>";

    for(; $i < (4*$number_of_questions)/$sections; $i++){
        $data = formula($difficulty, $forms[rand(0, count($forms) - 1)]);
        $left .= "\(".$data[1] . "\)<br>";
        $right .= "\([".$data[0] ."]\)<br>";
        $latex .= "$$".$data[1] . " \\quad [".$data[0]."]$$<br>";
    }

    $left .= "</div>";
    $right .= "</div>";
    $to_return .= $left . $right . "</div>";

    }

    if($i < $number_of_questions){

    $left = '<div class="col-6">';
    $right = '<div class="col-6">';

    $to_return .= '<div class="h5">Vyhodnoťte následující výrazy.</div><div class="row">';
    $latex .= "<br>\\section{Vyhodnoťte následující výrazy.}<br>";

    for(; $i < $number_of_questions; $i++){
        [$vars, $max_numbers, $expression_count] = get_parameters_to_difficulty($difficulty);
        $data = generate_polynom($vars, $max_numbers, $expression_count);
        $left .= "Pro \(";
        $latex .= "Pro $";
        $all_vars_values = [];
        $all_vars_values_representations = [];
        $form_now = $forms[rand(0, count($forms) - 1)];
        foreach($vars as $var){
            if($form_now == "fraction"){
                do{
                    $generated = [rand(1, 10), rand(1, 10)];
                    $simplified = simplify_fraction($generated[0], $generated[1]);
                } while(is_numeric($simplified));
                
                $all_vars_values[] = $generated[0]/$generated[1];
                $all_vars_values_representations[] = $simplified;
            }
            else{
                if($form_now == "random"){
                    $generated = rand(1000, $difficulty < 3? 7000:15000)/1000.0;
                }
                else $generated = rand(0, 10);
                $all_vars_values[] = $generated;
                $all_vars_values_representations[] = $generated;
            }
            if(str_contains($data[0], $var)){
                $t = $var . "=". $all_vars_values_representations[count($all_vars_values_representations) - 1] . ", ";
                $left .= $t;
                $latex .= $t;
            }
        }
        $left = rtrim($left, ", ");
        $latex = rtrim($latex, ", ");
        $left .= "\):<br>\(".$data[0]. "\)<br>";
        $evaluated = evaluate_matrix($data[3], $all_vars_values);
        $right .= "<br>\([".$evaluated."]\)<br>";
        $latex .= "$:<br>$$".$data[0] . " \\quad [".$evaluated."]$$<br>";
    }

    $left .= "</div>";
    $right .= "</div>";
    $to_return .= $left . $right . "</div>";

    }


    return [$to_return, $latex];

}
?>