<?php

/*
autor: Dominik Vladar
prace z predmetu KIV/OP
Zapadoceska univerzita v Plzni, Fakulta aplikovanych ved
*/

require_once("equations.php");


/**
 * Vygeneruje soustavy 2 linearnich rovnic odpovidajici pozadavkum uzivatele, vcetne LateXoveho kodu.
 * @param $difficulty obtiznost.
 * @param $number_of_questions pocet rovnic ke generovani.
 * @param $result_form vyjadruje, v jake forme jsou pripustne reseni rovnice.
 * @return pole s nasledujicim obsahem: [<HTML kod zobrazeny uzivateli>, <Latexovy kod>, <text s vysledky automaticke kontroly>, <shrnute vysledky automaticke kontroly>].
 *  */
function generate_systems_of_equations($difficulty, $number_of_questions, $result_form){
    global $operators, $max_delta;

    $to_return = '<div class="h5">Vyřešte následující soustavy lineárních rovnic v ℝ.</div><div class="row">';
    $left = '<div class="col-6">';
    $right = '<div class="col-6"><br>';
    $latex = "\\section{Vyřešte následující soustavy lineárních rovnic v {\\rm I\\!R}.}<br>";
    $validity_check = "";
    $stats = [0, 0];

    $forms = [];
    foreach(["integer", "fraction", "random"] as $key){
        if($result_form[$key]){
            $forms[] = $key;
        }
    }

    for($k = 0; $k < $number_of_questions; $k++){

        $form = $forms[rand(0, count($forms) - 1)];

        $first_var_index = rand(0, 8);
        $all_vars_arr = ["x", "y", "z", "a", "b", "c", "d", "q", "j", "p"];
        $first_variable_name = $all_vars_arr[$first_var_index];
        $second_variable_name = $all_vars_arr[$first_var_index + 1];
        $left_side = [new Leaf($first_variable_name), new Leaf($second_variable_name)];
        $right_side = [NULL, NULL];
        $vals = [0, 0];
        $max_number = get_max_number($difficulty);
        $right_before_adjust = [NULL, NULL];
        $left_before_adjust = [NULL, NULL];

        for($i = 0; $i < 2; $i++){
            switch($form){
                case "integer":   
                    $vals[$i] =  rand(0, $max_number);
                    $right_side[$i] = new Leaf("".$vals[$i]);
                    $right_before_adjust[$i] = new Leaf("".$vals[$i]);
                    break;
                case "fraction":
                    do{
                        $up = rand(1, $max_number);
                        $down = rand(1, $max_number);
                        $simplified = simplify_fraction($up, $down);
                    }
                    while(is_numeric($simplified));
                    $nums = explode("{", $simplified);
                    $vals[$i] = floatval(explode("}", $nums[1])[0])/floatval(explode("}", $nums[2])[0]);
                    $right_side[$i] = new BinaryOperator(new Leaf("" . explode("}", $nums[1])[0]), new Leaf("" . explode("}", $nums[2])[0]), $operators["DIVIDE"]);
                    $right_before_adjust[$i] = new BinaryOperator(new Leaf("" . explode("}", $nums[1])[0]), new Leaf("" . explode("}", $nums[2])[0]), $operators["DIVIDE"]);
                    break;
                default:
                    $vals[$i] = (rand(1000, $max_number*1000)/1000.0);
                    $right_side[$i] = new Leaf("".$vals[$i]);
                    $right_before_adjust[$i] = new Leaf("".$vals[$i]);
                    break;
            }
        }

        $r_temp = [NULL, NULL];
        $l_temp = [NULL, NULL];
        for($i = 0; $i < 2; $i++){
             if(rand(0, 1) == 0){
                $l_temp[$i] = new BinaryOperator($left_side[$i], $left_side[($i + 1) % 2], $operators["ADD"]);
                if($form == "fraction"){
                    $top = intval($right_side[$i]->operand1->content)*intval($right_side[($i + 1) % 2]->operand2->content) + intval($right_side[($i + 1) % 2]->operand1->content)*intval($right_side[$i]->operand2->content);
                    $bottom = intval($right_side[$i]->operand2->content)*intval($right_side[($i + 1) % 2]->operand2->content);
                    $f = simplify_fraction($top, $bottom);
                    $nums = explode("{", $f);
                    if(is_numeric($f)){
                        $r_temp[$i] = new Leaf($f);
                    }
                    else{

                        $r_temp[$i] = new BinaryOperator(new Leaf("" . explode("}", $nums[1])[0]), new Leaf("" . explode("}", $nums[2])[0]), $operators["DIVIDE"]);
                    }
                }
                else{
                    $r_temp[$i] = new Leaf("" . floatval($right_side[$i]->content) + floatval($right_side[($i + 1) % 2]->content));
                }
            }
            else{
                $l_temp[$i] = new BinaryOperator($left_side[$i], $left_side[($i + 1) % 2], $operators["SUBSTRACT"]);
                if($form == "fraction"){
                    $top = intval($right_side[$i]->operand1->content)*intval($right_side[($i + 1) % 2]->operand2->content) - intval($right_side[($i + 1) % 2]->operand1->content)*intval($right_side[$i]->operand2->content);
                    $bottom = intval($right_side[$i]->operand2->content)*intval($right_side[($i + 1) % 2]->operand2->content);
                    $f = simplify_fraction($top, $bottom);
                    $nums = explode("{", $f);
                    if(is_numeric($f)){
                        $r_temp[$i] = new Leaf($f);
                    }
                    else{

                        $r_temp[$i] = new BinaryOperator(new Leaf("" . explode("}", $nums[1])[0]), new Leaf("" . explode("}", $nums[2])[0]), $operators["DIVIDE"]);
                    }
                }
                else{
                    $r_temp[$i] = new Leaf("" . floatval($right_side[$i]->content) - floatval($right_side[($i + 1) % 2]->content));
                }
            }
        }
        $left_side = $l_temp;
        $right_side = $r_temp;



        $right_inside = $first_variable_name . " = " . stringify_tree($right_before_adjust[0]) . "; " . $second_variable_name . " = " . stringify_tree($right_before_adjust[1]);
        $right .= "<div class='eq-container'>\([" . $right_inside . "]\)</div>";
        $solution = [stringify_tree($right_before_adjust[0]), stringify_tree($right_before_adjust[1])];
        [$temp1, $temp2] = adjust_equation($left_side[0], $right_side[0], $difficulty);
        [$temp3, $temp4] = adjust_equation($left_side[1], $right_side[1], $difficulty);
        $left_side = [$temp1, $temp3];
        $right_side = [$temp2, $temp4];
        $validity_check .= "<div class='h6'>Soustava rovnic \(".no_plus_minus_error(stringify_tree($left_side[0])). "=".no_plus_minus_error(stringify_tree($right_side[0]))."; " . no_plus_minus_error(stringify_tree($left_side[1])). "=".no_plus_minus_error(stringify_tree($right_side[1])) ."\)</div>";
        $solution_unchanged = [$solution[0], $solution[1]];
        for($p = 0; $p < 2; $p++){
            if(str_contains($solution[$p], "frac")){
                $solution[$p] = explode("{", $solution[$p]);
                $solution[$p] = "" . (floatval(explode("}", $solution[$p][1])[0])/floatval(explode("}", $solution[$p][2])[0]));
            }
        }
            $lp = [get_tree_value_two_unknowns($first_variable_name, $solution[0], $second_variable_name, $solution[1], $left_side[0]),
                   get_tree_value_two_unknowns($first_variable_name, $solution[0], $second_variable_name, $solution[1], $left_side[1])];
            $rp = [get_tree_value_two_unknowns($first_variable_name, $solution[0], $second_variable_name, $solution[1], $right_side[0]),
                   get_tree_value_two_unknowns($first_variable_name, $solution[0], $second_variable_name, $solution[1], $right_side[1])];
            $delta = abs(floatval($lp[0]) - floatval($rp[0])) + abs(floatval($lp[1]) - floatval($rp[1]));
            if($delta > $max_delta){
                $mark = '(Δ = '.$delta.' > '.$max_delta.') <i class="fa-solid fa-circle-exclamation text-danger"></i>';
                $stats[0]++;
            }
            else{
                $mark = '(Δ = '.$delta.' <= '.$max_delta.') <i class="fa-solid fa-circle-check text-success"></i>';
                $stats[1]++;
            }
            $validity_check .= "" .$lp[0] . " = " . $rp[0] . "; " . $lp[1] . " = " . $rp[1] . "&nbsp;&nbsp;&nbsp;&nbsp;[\(" . $first_variable_name . "=" . $solution_unchanged[0] . "; " . $second_variable_name . "=" . $solution_unchanged[1] . "\)]&nbsp;&nbsp;&nbsp;&nbsp;" . $mark . "<br>";
        $left_inside = [no_plus_minus_error(stringify_tree($left_side[0])) . " = " . no_plus_minus_error(stringify_tree($right_side[0])),
                        no_plus_minus_error(stringify_tree($left_side[1])) . " = " . no_plus_minus_error(stringify_tree($right_side[1]))
        ];
        $left .= "<div class='eq-container'>I. \(" . $left_inside[0] . "\)<br> II. \(" . $left_inside[1] . "\)</div>";
        $latex .= "$$". $left_inside[0] . "$$<br>$$". $left_inside[1] . " \\quad [". $right_inside ."]$$<br><br>";
    }


    $left .= "</div>";
    $right .= "</div>";
    $to_return .= $left . $right;

    $to_return .= '</div>';

    return [$to_return, $latex, $validity_check, "" . $stats[1] . " správně, " . $stats[0] . " špatně"];
}

?>