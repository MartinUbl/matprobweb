<?php

/*
autor: Dominik Vladar
prace z predmetu KIV/OP
Zapadoceska univerzita v Plzni, Fakulta aplikovanych ved
*/

require_once "math.php";

$max_delta = 0.0001;

/**
 * Pricte k jedne strane rovnice hodnotu $r*$sign.
 * @param $side strana rovnice, ke ktere chceme pricist/odecist konstantni hodnotu.
 * @param $r hodnota.
 * @param $sign 1, pokud se ma jednat o pricteni; -1, pokud jde o odcitani.
 * @return upravena strana rovnice.
 */
function modify_content($side, $r, $sign) {
    global $operators;
    if ($side instanceof Leaf && is_numeric($side->content)) {
        $side->content = "" . (floatval($side->content) + $sign * $r);
    } elseif ($side instanceof BinaryOperator && ($side->operator->representation == "+" || $side->operator->representation == "-")) {
        $is_plus = $side->operator->representation == "+";
        if ($side->operand2 instanceof Leaf && is_numeric($side->operand2->content)) {
            $side->operand2->content = "" . (floatval($side->operand2->content) + $sign * ($is_plus ? 1 : -1) * $r);//a-5 /+5
        } else {
            $side = new BinaryOperator($side, new Leaf("" . $r), ($sign > 0 ? $operators["ADD"] : $operators["SUBSTRACT"]));
        }
    } else {
        $side = new BinaryOperator($side, new Leaf("" . $r), ($sign > 0 ? $operators["ADD"] : $operators["SUBSTRACT"]));
    }
    return $side;
}

/**
 * Do funkce vstupuje rovnice typicky v jednoduchem tvaru "x = <hodnota>" apod. Funkce postupne upravuje rovnici na
 * ekvivalentni tvary, cimz vznikne rovnice komplikovanejsi pro reseni.
 * @param $left_side leva strana rovnice.
 * @param $right_side prava strana rovnice.
 * @param $difficulty obtiznost nastavena uzivatelem; na ni zavisi pocet a zpusob uprav.
 * @return pole obsahujici levou ([0]) a pravou ([1]) stranu rovnice po uprave.
 *  */
function adjust_equation($left_side, $right_side, $difficulty){
    global $operators;

    if($left_side instanceof Leaf){
        $var_name = $left_side->content;
    }
    else if($left_side instanceof BinaryOperator && $left_side->operand1 instanceof Leaf){
        $var_name = $left_side->operand1->content;
    }
    else{
        $var_name = $left_side->operand1->operand2->operand1->content;
    }

    for($i = 0; $i < $difficulty*2; $i++){
        $ran = rand(0, 5);
        switch($ran){
            case 0:
                //vynasobeni konstantou
                if($left_side instanceof BinaryOperator && $left_side->operator->representation == "/" && $left_side->operand2 instanceof Leaf && is_numeric($left_side->operand2->content)){
                    $mult = floatval($left_side->operand2->content);
                    $left_side = $left_side->operand1;
                    $right_side = new BinaryOperator(new Leaf("" . $mult), $right_side, $operators["MULTIPLE"]);
                }
                elseif($right_side instanceof BinaryOperator && $right_side->operator->representation == "/" && $right_side->operand2 instanceof Leaf && is_numeric($right_side->operand2->content)){
                    $mult = floatval($right_side->operand2->content);
                    $right_side = $right_side->operand1;
                    $left_side = new BinaryOperator(new Leaf("" . $mult), $left_side, $operators["MULTIPLE"]);
                }
                else {
                    $mult = rand(2, 10);
                }
            
                if($left_side instanceof BinaryOperator){
                    if($left_side->operator->representation == "/") {
                        $left_side = new BinaryOperator(new Leaf("" . $mult), $left_side->operand1, $operators["MULTIPLE"]);
                    } 
                    else {
                        $left_side = new BinaryOperator(new Leaf("" . $mult), $left_side, $operators["MULTIPLE"]);
                    }
                }
                elseif($left_side instanceof Leaf && is_numeric($left_side->content)){
                    $left_side->content = "" . (floatval($left_side->content) * $mult);
                }
                else{
                    $left_side = new BinaryOperator(new Leaf("" . $mult), $left_side, $operators["MULTIPLE"]);
                }
                
                if($right_side instanceof BinaryOperator){
                    if($right_side->operator->representation == "/") {
                        $right_side = new BinaryOperator(new Leaf("" . $mult), $right_side->operand1, $operators["MULTIPLE"]);
                    } 
                    else {
                        $right_side = new BinaryOperator(new Leaf("" . $mult), $right_side, $operators["MULTIPLE"]);
                    }
                }
                elseif($right_side instanceof Leaf && is_numeric($right_side->content)){
                    $right_side->content = "" . (floatval($right_side->content) * $mult);
                }
                else{
                    $right_side = new BinaryOperator(new Leaf("" . $mult), $right_side, $operators["MULTIPLE"]);
                }
            
                break;            
            case 1:
                //prohodime levou a pravou stranu
                if(rand(0, 3) != 1){
                    break;
                }
                [$left_side, $right_side] = [$right_side, $left_side];
                break;
            case 2:
                //pricteme/odecteme nasobek promenne k oboum stranam
                $r = rand(1, 5);
                $mult = new BinaryOperator(new Leaf("". $r), new Leaf($var_name), $operators["MULTIPLE"]);
                $s = rand(0, 1) == 0;
                if($left_side instanceof Leaf && $left_side->content == $var_name){
                    if($r == 1 && !$s){
                        $left_side = new Leaf("0");
                    }
                    else{
                        $left_side = new BinaryOperator(new Leaf("". (1 +($s?1:-1)*$r)), new Leaf($var_name), $operators["MULTIPLE"]);
                    }
                }
                elseif($left_side instanceof BinaryOperator && str_contains($left_side->operator->representation, "cdot") && $left_side->operand1 instanceof Leaf && $left_side->operand2 instanceof Leaf && ($left_side->operand1->content == $var_name || $left_side->operand2->content == $var_name)){
                    if($left_side->operand1->content == $var_name){
                        $num = floatval($left_side->operand2->content);
                        $left_side->operand2->content = "". (($s?1:-1)*$r + $num);
                    }
                    else{
                        $num = floatval($left_side->operand1->content);
                        $left_side->operand1->content = "". (($s?1:-1)*$r + $num);
                    }
                }
                elseif($left_side instanceof BinaryOperator && ($left_side->operator->representation == "+" || 
                $left_side->operator->representation == "-") && $left_side->operand1 instanceof BinaryOperator && 
                str_contains($left_side->operand1->operator->representation, "cdot") && $left_side->operand1->operand1 instanceof Leaf
                && $left_side->operand1->operand2 instanceof Leaf && !is_numeric($left_side->operand1->operand2->content)
                && is_numeric($left_side->operand1->operand1->content)){
                    //napr. 3a + 5 nebo 3a - 5a
                    $left_side->operand1->operand1->content = "" . (floatval($left_side->operand1->operand1->content) + ($s? $r : -$r));
                }
                elseif($left_side instanceof BinaryOperator && ($left_side->operator->representation == "+" || 
                $left_side->operator->representation == "-") && $left_side->operand2 instanceof BinaryOperator &&
                str_contains($left_side->operand2->operator->representation, "cdot") && $left_side->operand2->operand1 instanceof Leaf
                && $left_side->operand2->operand2 instanceof Leaf && !is_numeric($left_side->operand2->operand2->content)
                && is_numeric($left_side->operand2->operand1->content)){
                    //napr. 3 + 5a, 3 - 5a
                    $is_plus = $left_side->operator->representation == "+";
                    $sign = 1;
                    if(!$is_plus) $sign *= -1;
                    if(!$s) $sign *= -1;
                    $left_side->operand2->operand1->content = "". (floatval($left_side->operand2->operand1->content) + $sign * $r);
                }
                else{
                    $left_side = new BinaryOperator($left_side, $mult, $s? $operators["ADD"] : $operators["SUBSTRACT"]);
                }
                if($right_side instanceof Leaf && $right_side->content == $var_name){
                    if($r == 1 && !$s){
                        $right_side = new Leaf("0");
                    }
                    else{
                        $right_side = new BinaryOperator(new Leaf("". (1 +($s?1:-1)*$r)), new Leaf($var_name), $operators["MULTIPLE"]);
                    }
                }
                elseif($right_side instanceof BinaryOperator && str_contains($right_side->operator->representation, "cdot") && $right_side->operand1 instanceof Leaf && $right_side->operand2 instanceof Leaf && ($right_side->operand1->content == $var_name || $right_side->operand2->content == $var_name)){
                    if($right_side->operand1->content == $var_name){
                        $num = floatval($right_side->operand2->content);
                        $right_side->operand2->content = "". (($s?1:-1)*$r + $num);
                    }
                    else{
                        $num = floatval($right_side->operand1->content);
                        $right_side->operand1->content = "". (($s?1:-1)*$r + $num);
                    }
                }
                elseif($right_side instanceof BinaryOperator && ($right_side->operator->representation == "+" || 
                $right_side->operator->representation == "-") && $right_side->operand1 instanceof BinaryOperator && 
                str_contains($right_side->operand1->operator->representation, "cdot") && $right_side->operand1->operand1 instanceof Leaf
                && $right_side->operand1->operand2 instanceof Leaf && !is_numeric($right_side->operand1->operand2->content)
                && is_numeric($right_side->operand1->operand1->content)){
                    $right_side->operand1->operand1->content = "" . (floatval($right_side->operand1->operand1->content) + ($s? $r : -$r));
                }
                elseif($right_side instanceof BinaryOperator && ($right_side->operator->representation == "+" || 
                $right_side->operator->representation == "-") && $right_side->operand2 instanceof BinaryOperator &&
                str_contains($right_side->operand2->operator->representation, "cdot") && $right_side->operand2->operand1 instanceof Leaf
                && $right_side->operand2->operand2 instanceof Leaf && !is_numeric($right_side->operand2->operand2->content)
                && is_numeric($right_side->operand2->operand1->content)){
                    $is_plus = $right_side->operator->representation == "+";
                    $sign = 1;
                    if(!$is_plus) $sign *= -1;
                    if(!$s) $sign *= -1;
                    $right_side->operand2->operand1->content = "". (floatval($right_side->operand2->operand1->content) + $sign * $r);
                }
                else{
                    $right_side = new BinaryOperator($right_side, $mult, $s? $operators["ADD"] : $operators["SUBSTRACT"]);
                }
                break;
                case 3:
                    // Pricteni/odecteni konstanty
                    $r = rand(1, 5); // hodnota pro pricteni/odecteni
                    $s = rand(0, 1) == 0; // pravdepodobnost pro + nebo -
                    $sign = $s ? 1 : -1;
                
                    // Upravime levou i pravou stranu stejne
                    $left_side = modify_content($left_side, $r, $sign);
                    $right_side = modify_content($right_side, $r, $sign);
                
                    break;
        }
    }
    return [$left_side, $right_side];
}

/**
 * @param $difficulty obtiznost nastavena uzivatelem.
 * @return maximalni velikost cisel, ktera se budou objevovat ve vypoctu v zavislosti na obtiznosti.
 */
function get_max_number($difficulty){
    switch($difficulty){
        case 1:
            return 5;
        case 2:
            return 10;
        case 3:
            return 20;
        case 4:
            return 50;
        default:
            return 100;           
    }
}

/**
 * Vygeneruje linearni a kvadraticke rovnice odpovidajici pozadavkum uzivatele, vygeneruje LateXovy kod a automaticky vygenerovane rovnice overi.
 * @param $difficulty obtiznost.
 * @param $number_of_questions pocet rovnic ke generovani.
 * @param $result_form vyjadruje, v jake forme jsou pripustne reseni rovnice.
 * @return pole s nasledujicim obsahem: [<HTML kod zobrazeny uzivateli>, <Latexovy kod>, <text s vysledky automaticke kontroly>, <shrnute vysledky automaticke kontroly>].
 *  */
function generate_equations($difficulty, $number_of_questions, $result_form){
    global $operators;

    $to_return = '<div class="h5">Vyřešte následující lineární rovnice v ℝ.</div><div class="row">';
    $left = '<div class="col-6">';
    $right = '<div class="col-6">';
    $latex = "\\section{Vyřešte následující lineární rovnice v {\\rm I\\!R}.}<br>";
    $validity_check = "";
    $stats = [0, 0];

    $forms = [];
    foreach(["integer", "fraction", "random"] as $key){
        if($result_form[$key]){
            $forms[] = $key;
        }
    }

    for($i = 0; $i < $number_of_questions/2; $i++){
        global $max_delta;

        $form = $forms[rand(0, count($forms) - 1)];

        $variable_name = ["x", "y", "z", "a", "b", "c", "d", "q", "j", "p"][rand(0, 9)];
        $left_side = new Leaf($variable_name);
        $max_number = get_max_number($difficulty);
        switch($form){
            case "integer":    
                $right_side = new Leaf("".rand(0, $max_number));
                break;
            case "fraction":
                do{
                    $up = rand(1, $max_number);
                    $down = rand(1, $max_number);
                    $simplified = simplify_fraction($up, $down);
                }
                while(is_numeric($simplified));//is_numeric je true, pokud je vysledkem cele cislo
                $nums = explode("{", $simplified);
                $right_side = new BinaryOperator(new Leaf("" . explode("}", $nums[1])[0]), new Leaf("" . explode("}", $nums[2])[0]), $operators["DIVIDE"]);
                break;
            default:
                $right_side = new Leaf("".(rand(1000, $max_number*1000)/1000.0));
        }

        $right_inside = stringify_tree($left_side) . " = " . stringify_tree($right_side);
        $right .= "\([" . $right_inside . "]\)<br>";
        $solution = stringify_tree($right_side);
        [$left_side, $right_side] = adjust_equation($left_side, $right_side, $difficulty);
        $validity_check .= "<div class='h6'>Lineární rovnice \(".no_plus_minus_error(stringify_tree($left_side)). "=".no_plus_minus_error(stringify_tree($right_side))."\)</div>";
        $solution_unchanged = $solution;
            if(str_contains($solution, "frac")){
                $solution = explode("{", $solution);
                $solution = "" . (floatval(explode("}", $solution[1])[0])/floatval(explode("}", $solution[2])[0]));
            }
            $lp = get_tree_value($variable_name, $solution, $left_side);
            $rp = get_tree_value($variable_name, $solution, $right_side);
            $delta = abs(floatval($lp) - floatval($rp));
            if($delta > $max_delta){
                $mark = '(Δ = '.$delta.' > '.$max_delta.') <i class="fa-solid fa-circle-exclamation text-danger"></i>';
                $stats[0]++;
            }
            else{
                $mark = '(Δ = '.$delta.' <= '.$max_delta.') <i class="fa-solid fa-circle-check text-success"></i>';
                $stats[1]++;
            }
            $validity_check .= "" .$lp . " = " . $rp . "&nbsp;&nbsp;&nbsp;&nbsp;[" . $variable_name . "=\(" . $solution_unchanged . "\)]&nbsp;&nbsp;&nbsp;&nbsp;" . $mark . "<br>";
        $left_inside = no_plus_minus_error(stringify_tree($left_side)) . " = " . no_plus_minus_error(stringify_tree($right_side));
        $left .= "\(" . $left_inside . "\)<br>";
        $latex .= "$$". $left_inside . " \\quad [". $right_inside ."]$$<br>";
    }


    $left .= "</div>";
    $right .= "</div>";
    $to_return .= $left . $right;

    if($i < $number_of_questions){
    $to_return .= '</div><div class="h5">Vyřešte následující kvadratické rovnice v ℝ.</div><div class="row">';
    $left = '<div class="col-6">';
    $right = '<div class="col-6">';
    $latex .= "<br>\\section{Vyřešte následující kvadratické rovnice v {\\rm I\\!R}.}<br>";

    for(; $i < $number_of_questions; $i++){

        $form = $forms[rand(0, count($forms) - 1)];

        $variable_name = ["x", "y", "z", "a", "b", "c", "d", "q", "j", "p"][rand(0, 9)];
        $max_number = get_max_number($difficulty);
        $number_of_solutions = rand(0, 2);
        $all_nums = [];
        switch($form){
            case "integer":   
                for($j = 0; $j < $number_of_solutions; $j++){
                    $all_nums[] = new Leaf("".rand(0, $max_number));
                }
                break;
            case "fraction":
                for($j = 0; $j < $number_of_solutions; $j++){
                    do{
                        $up = rand(1, $max_number);
                        $down = rand(1, $max_number);
                        $simplified = simplify_fraction($up, $down);
                    }
                    while(is_numeric($simplified));//is_numeric je true, pokud je vysledkem cele cislo
                    $nums = explode("{", $simplified);
                    $all_nums[] = new BinaryOperator(new Leaf("" . explode("}", $nums[1])[0]), new Leaf("" . explode("}", $nums[2])[0]), $operators["DIVIDE"]);
                }
                break;
            default:
                for($j = 0; $j < $number_of_solutions; $j++){
                    $all_nums[] = new Leaf("".(rand(1000, $max_number*1000)/1000.0));
                }
                break;
        }
        if($number_of_solutions == 1){
            $all_nums[] = $all_nums[0];
        }
        //(x-x1)(x-x2) = x**2 -x2*x - x1*x +x1*x2
        if($number_of_solutions == 0){
            //D < 0; b**2 - 4ac < 0
            if($form == "integer"){
            $b = rand(0, $max_number);
            $dis = 0;
            do{
                    $a = rand(1, $max_number);
                    $c = rand(1, $max_number);
                } while($b*$b -4*$a*$c >= -0.001);
            }
            elseif($form == "fraction"){
                do{
                    $my_nums = [];
                    for($w = 0; $w < 3; $w++){
                        do{
                            $up = rand(1, $max_number);
                            $down = rand(1, $max_number);
                            $simplified = simplify_fraction($up, $down);
                        }
                        while(is_numeric($simplified));
                        $nums = explode("{", $simplified);
                        $my_nums[] = new BinaryOperator(new Leaf("" . explode("}", $nums[1])[0]), new Leaf("" . explode("}", $nums[2])[0]), $operators["DIVIDE"]);
                    }
                    $a = floatval($my_nums[0]->operand1->content)/floatval($my_nums[0]->operand2->content);
                    $b = floatval($my_nums[1]->operand1->content)/floatval($my_nums[1]->operand2->content);
                    $c = floatval($my_nums[2]->operand1->content)/floatval($my_nums[2]->operand2->content);
                } while($b*$b -4*$a*$c >= -0.001);
                $a = $my_nums[0];
                $b = $my_nums[1];
                $c = $my_nums[2];
            }
            else{
                $b = rand(1000, $max_number*1000)/1000.0;
                do{
                    $a = rand(1000, $max_number*1000)/1000.0;
                    $c = rand(1000, $max_number*1000)/1000.0;
                } while($b*$b -4*$a*$c >= -0.001);
            }
            if($form != "fraction"){
                $a = new Leaf("" . $a);
                $b = new Leaf("" . $b);
                $c = new Leaf("-" . $c);
            }
        }
        else{
            $a = new Leaf("1");
            if($form == "fraction"){
                $c = new BinaryOperator(new Leaf("-" . (floatval($all_nums[0]->operand1->content)*floatval($all_nums[1]->operand1->content))), 
                new Leaf("" . (floatval($all_nums[0]->operand2->content)*floatval($all_nums[1]->operand2->content))), $operators["DIVIDE"]);
                $fraction = simplify_fraction((intval($all_nums[0]->operand1->content)*intval($all_nums[1]->operand2->content)
                + intval($all_nums[1]->operand1->content)*intval($all_nums[0]->operand2->content)),
                   intval($all_nums[0]->operand2->content)*intval($all_nums[1]->operand2->content));
                if(is_numeric($fraction)){
                    $b = new Leaf("-" . $fraction);
                }
                else{
                    $b = new BinaryOperator(new Leaf("-" . explode("}", explode("{", $fraction)[1])[0]), 
                        new Leaf(explode("}", explode("{", $fraction)[2])[0]),
                        $operators["DIVIDE"]
                    );
                }
            }
            else{
                $c = new Leaf("-" . (floatval($all_nums[0]->content) * floatval($all_nums[1]->content)));
                $b = new Leaf("-".(floatval($all_nums[0]->content) + floatval($all_nums[1]->content)));
            }
        }
        $right_side = $c;
        $left_side = new BinaryOperator(
            new BinaryOperator($a, new BinaryOperator(new Leaf("" . $variable_name), new Leaf("2"), $operators["POWER"]), $operators["MULTIPLE"]),
            new BinaryOperator($b, new Leaf("" . $variable_name), $operators["MULTIPLE"]),
            $operators["ADD"]
        );

        $right_inside = "";
        if($number_of_solutions == 2 && stringify_tree($all_nums[0]) == stringify_tree($all_nums[1])){
            //pokusili jsme se o 2 reseni, ale vypadlo jen 1
            $number_of_solutions--;
        }
        $solutions = [];
        for($q = 0; $q < $number_of_solutions; $q++){
            if($q > 0){
                $right_inside .= ", ";
            }
            $right_inside .= $variable_name . (($number_of_solutions==1? ""  : ("_". ($q+1)))) . "=" . stringify_tree($all_nums[$q]);
            $solutions[] = stringify_tree($all_nums[$q]);
        }
        if($number_of_solutions == 0){
            $right_inside = "∅";
        }
        $right .= "\([" . $right_inside . "]\)<br>";
        [$left_side, $right_side] = adjust_equation($left_side, $right_side, $difficulty);
        $validity_check .= "<div class='h6'>Kvadratická rovnice \(".no_plus_minus_error(stringify_tree($left_side)). "=".no_plus_minus_error(stringify_tree($right_side))."\)</div>";
        $is_problem = FALSE;
        foreach($solutions as $solution){
            $solution_unchanged = $solution;
            if(str_contains($solution, "frac")){
                $solution = explode("{", $solution);
                $solution = "" . (floatval(explode("}", $solution[1])[0])/floatval(explode("}", $solution[2])[0]));
            }
            $lp = get_tree_value($variable_name, $solution, $left_side);
            $rp = get_tree_value($variable_name, $solution, $right_side);
            $delta = abs(floatval($lp) - floatval($rp));
            if($delta > $max_delta){
                $mark = '(Δ = '.$delta.' > '.$max_delta.') <i class="fa-solid fa-circle-exclamation text-danger"></i>';
                $is_problem = TRUE;
            }
            else{
                $mark = '(Δ = '.$delta.' <= '.$max_delta.') <i class="fa-solid fa-circle-check text-success"></i>';
            }
            $validity_check .= "" .$lp . " = " . $rp . "&nbsp;&nbsp;&nbsp;&nbsp;[" . $variable_name . "=\(" . $solution_unchanged . "\)]&nbsp;&nbsp;&nbsp;&nbsp;" . $mark . "<br>";
        }
        if($number_of_solutions == 0){
            $validity_check .= "<div class='text-warning'>Neexistenci řešení je třeba zkontrolovat ručním výpočtem.</div>";
        }
        else if($is_problem){
            $stats[0]++;
        }
        else{
            $stats[1]++;
        }
        $left_inside = no_plus_minus_error(stringify_tree($left_side)) . " = " . no_plus_minus_error(stringify_tree($right_side));
        $left .= "\(" . $left_inside . "\)<br>";
        $latex .= "$$". $left_inside . " \\quad [". $right_inside ."]$$<br>";
    }

    $left .= "</div>";
    $right .= "</div>";

    $to_return .= $left . $right . "</div>";

    }

    return [$to_return, $latex, $validity_check, "" . $stats[1] . " správně, " . $stats[0] . " špatně, " . ($number_of_questions - $stats[0] - $stats[1]) . " vyžaduje ruční kontrolu"];
}

?>