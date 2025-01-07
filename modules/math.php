<?php

/*
autor: Dominik Vladar
prace z predmetu KIV/OP
Zapadoceska univerzita v Plzni, Fakulta aplikovanych ved
*/

/**
 * Trida pro reprezentaci operace.
 * @property $representation reprezentace operatoru.
 * @property $same_function funkce, pomoci ktere je mozne vyhodnotit operaci.
 * @property $priority priorita operace.
 */
class Operation{
    public $representation, $same_function, $priority;

    function __construct($representation, $same_function, $priority){
        $this->representation = $representation;
        $this->same_function = $same_function;
        $this->priority = $priority;
    }
}

/**
 * Reprezentuje unarni operator jakozto prvek stromu znazornujiciho vyraz.
 * @property $operand prvek stromu, ktery je operandem v operaci.
 * @property $operator operator.
 */
class UnaryOperator{
    public $operand, $operator;

    function __construct($operand, $operator){
        $this->operand = $operand;
        $this->operator = $operator;
    }
}

/**
 * Binarni operator jakozto prvek stromu vyrazu.
 * @property $operand1 prvek stromu, ktery je prvnim operandem v operaci.
 * @property $operand2 prvek stromu, ktery je druhym operandem v operaci.
 * @property $operator operator.
 */

class BinaryOperator{
    public $operand1, $operand2, $operator;

    function __construct($operand1, $operand2, $operator){
        $this->operand1 = $operand1;
        $this->operand2 = $operand2;
        $this->operator = $operator;
    }
}

/**
 * List stromu, jedna se o cislo nebo neznamou.
 * @property $content obsah listu.
 */
class Leaf{
    public $content;

    function __construct($content){
        $this->content = $content;
    }
}


$operators = array(
    "ADD" => new Operation("+", function($a, $b){return $a  + $b;}, 1),
    "SUBSTRACT" => new Operation("-", function($a, $b){return $a - $b;}, 1),
    "MULTIPLE" => new Operation("\\cdot ", function($a, $b){return $a*$b;}, 2),
    "DIVIDE" => new Operation("/", function($a, $b){return $a/$b;}, 2),/* $representation je nahrazena az pri zpracovani */
    "LN" => new Operation("\\ln", function($a){return log($a);}, 3),
    "LOG10" => new Operation("\\log", function($a){return log10($a);}, 3),
    "POWER" => new Operation("^", function($a, $b){return pow($a, $b);}, 3)
);

/**
 * @param $var_name jmeno nezname, za kterou dosazujeme.
 * @param $var_value hodnota teto nezname.
 * @param $tree_node vrchol stromu.
 * @return hodnota stromu po dosazeni za neznamou.
 */
function get_tree_value($var_name, $var_value, $tree_node) {
    if ($tree_node instanceof Leaf) {
        if (is_numeric($tree_node->content)) {
            return floatval($tree_node->content);
        }
        return floatval(str_replace($var_name, "" . $var_value, $tree_node->content));
    }
    elseif ($tree_node instanceof UnaryOperator) {
        return call_user_func($tree_node->operator->same_function, get_tree_value($var_name, $var_value, $tree_node->operand));
    }
    return call_user_func($tree_node->operator->same_function, get_tree_value($var_name, $var_value, $tree_node->operand1), get_tree_value($var_name, $var_value, $tree_node->operand2));
}

/**
 * @param $var_name1 jmeno prvni nezname, za kterou dosazujeme.
 * @param $var_value1 hodnota prvni nezname.
 * @param $var_name2 jmeno druhe nezname, za kterou dosazujeme.
 * @param $var_value1 hodnota druhe nezname.
 * @param $tree_node vrchol stromu.
 * @return hodnota stromu po dosazeni za obe nezname.
 */
function get_tree_value_two_unknowns($var_name1, $var_value1, $var_name2, $var_value2, $tree_node) {
    if ($tree_node instanceof Leaf) {
        if (is_numeric($tree_node->content)) {
            return floatval($tree_node->content);
        }
        return floatval(str_replace($var_name2, $var_value2, str_replace($var_name1, "" . $var_value1, $tree_node->content)));
    }
    elseif ($tree_node instanceof UnaryOperator) {
        return call_user_func($tree_node->operator->same_function, get_tree_value_two_unknowns($var_name1, $var_value1, $var_name2, $var_value2, $tree_node->operand));
    }
    return call_user_func($tree_node->operator->same_function, get_tree_value_two_unknowns($var_name1, $var_value1, $var_name2, $var_value2, $tree_node->operand1), get_tree_value_two_unknowns($var_name1, $var_value1, $var_name2, $var_value2, $tree_node->operand2));
}

/**
 * @param $node prvek, ktery chceme prevest na retezec, typicky vrchol stromu.
 * @return predany strom prevedeny na string v Latex formatu.
 */
function stringify_tree($node){
    if($node == NULL){ return ""; }

    elseif($node instanceof Leaf){
        return $node->content;
    }
    elseif ($node instanceof UnaryOperator) {
        return $node->operator->representation."(". stringify_tree($node->operand).")";
    }
    else{
        //BinaryOperator
        $left_part = stringify_tree($node->operand1);
        $right_part = stringify_tree($node->operand2);
        if($node->operand1 instanceof BinaryOperator && ($node->operator->priority > $node->operand1->operator->priority || $node->operator->priority == 3)){
            $left_part = "(".$left_part.")";
        }

        if($node->operand2 instanceof BinaryOperator && ($node->operator->priority > $node->operand2->operator->priority || $node->operator->priority == 3)){
            $right_part = "(".$right_part.")";
        }

        if($node->operator->representation == "/"){
            $sign = "";
            if($left_part[0] == '-'){
                $left_part = substr($left_part, 1);
                $sign = "-";
            }
            elseif($right_part[0] == '-'){
                $right_part = substr($right_part, 1);
                $sign = "-";
            }
            return "" . $sign . "\\frac{".$left_part."}{".$right_part."}";
        }
        elseif($node->operator->representation == "^"){
            return $left_part . $node->operator->representation . "{" .$right_part. "}";
        }
        elseif($node->operator->representation == "+"){
            if($left_part == "0"){
                return $right_part;
            }
            elseif($right_part == "0"){
                return $left_part;
            }
        }
        elseif($node->operator->representation == "-"){
            if($left_part == "0"){
                if(strlen($right_part) > 0 && $right_part[0] == '-'){
                    return substr($right_part, 1);
                }
                return "-" . $right_part;
            }
            elseif($right_part == "0"){
                return $left_part;
            }
        }
        elseif(str_contains($node->operator->representation, "cdot")){
            if($left_part == "0" || $right_part == "0"){
                return "0";
            }
            elseif($left_part == "1"){
                return $right_part;
            }
            elseif($right_part == "1"){
                return $left_part;
            }
            else if($left_part == "-1"){
                return "-" . $right_part;
            }
            else if($right_part == "-1"){
                return "-" . $left_part;
            }
        }
        if(str_contains($node->operator->representation, "cdot") && ($node->operand2 instanceof Leaf && !is_numeric($node->operand2->content))
        || ($node->operand2 instanceof BinaryOperator && $node->operand2->operand1 instanceof Leaf && !is_numeric($node->operand2->operand1->content))){
            return $left_part . $right_part;
        }

        return $left_part . $node->operator->representation . $right_part;
    }
}

/**
 * Ze vsech prvku pole odecte minimum nalezene v tomto poli.
 * @param $arr pole.
 * @return pole po odecteni minima od kazdeho prvku.
 */
function minus_min($arr){
    $min = min($arr);
    for($i = 0; $i < count($arr); $i++){
        $arr[$i] -= $min;
    }
    return $arr;
}

/**
 * @param $a prvni cislo.
 * @param $b druhe cislo.
 * @return nejvetsi spolecny delitel cisla $a a $b.
 */
function gcd_temp ($a, $b) {
    return $b ? gcd_temp($b, $a % $b) : $a;
}
/**
 * @param $arr pole cisel.
 * @return nejvetsi spolecny delitel vsech cisel.
 */
function gcd($arr){
    foreach($arr as $item){
        if($item == 0) return -1;
    }
    return array_reduce($arr, 'gcd_temp');
}

/**
 * @param $numerator citatel.
 * @param $denumerator delitel.
 * @return zlomek prevedeny na zakladni tvar v Latexovem formatu, pripadne cislo, pokud je po uprave delitel == 1.
 */
function simplify_fraction($numerator, $denumerator){
    $g = gcd_temp($numerator, $denumerator);
    $numerator /= $g;
    $denumerator /= $g;
    if($denumerator == 1){
        return "" . $numerator;
    }
    return "\\frac{" . $numerator . "}{" . $denumerator . "}";
}

/**
 * Odstrani z retezce, ktery reprezentuje vyraz znamenkove features/chyby, napr. odcitani zaporne hodnoty: 1--a -> 1+a apod.
 * @param $string retezec.
 * @return retezec po odstraneni znamenkovych chyb.
 */
function no_plus_minus_error($string){
    $string_before = $string;
    do{
        $string_before = $string;
        $string = str_replace("+-", "-", str_replace("--", "+", str_replace("+-", "-", $string)));
    }
    while($string != $string_before);
    if(str_starts_with($string, "-0")){
        $string = substr($string, 1);
    }
    return $string;
}

?>