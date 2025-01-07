<?php

/*
autor: Dominik Vladar
prace z predmetu KIV/OP
Zapadoceska univerzita v Plzni, Fakulta aplikovanych ved
*/

/**
 * Vygeneruje funkce odpovidajici pozadavkum uzivatele.
 * @param $difficulty obtiznost.
 * @param $number_of_questions pocet rovnic ke generovani.
 * @param $result_form vyjadruje, v jake forme jsou pripustne reseni rovnice.
 * @return pole s jedinym prvkem: [<HTML kod zobrazeny uzivateli>].
 *  */
function generate_functions($difficulty, $number_of_charts, $result_form){
    $to_return = '<script src="https://cdn.plot.ly/plotly-2.34.0.min.js" charset="utf-8"></script><div class="h5">U následujících funkcí určete definiční obor, obor hodnot, monotonii, symetrii a zda je funkce prostá.</div><div class="row">';

    for($i = 0; $i < $number_of_charts; $i++){
        $to_return .= '
        <div id="myChart'.$i.'"></div>
        <div id="solution'.$i.'" class="solution"></div>';
    }

    $forms = "";
    foreach(["integer", "fraction", "random"] as $key){
        if($result_form[$key]){
            if($forms != ""){
                $forms .= ", ";
            }
            $forms .= "'". $key . "'";
        }
    }

    $to_return .= 
    '<script>
    const number_of_charts = '.$number_of_charts.';
    const difficulty = '.$difficulty.';
    const output_types = ['.$forms.'];
    </script><script src="modules/function_generator.js"></script>';


    return [$to_return];
}
?>