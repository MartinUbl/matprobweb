<?php

/*
autor: Dominik Vladar
prace z predmetu KIV/OP
Zapadoceska univerzita v Plzni, Fakulta aplikovanych ved
*/

/**
 * Defínuje tema ukolu.
 * @property $name nazev tematu.
 * @property $description popis tematu.
 * @property $color primarni barva prirazena k tematu, v teto barve pak bude vykreslena stranka, na ktere jsou generovany problemy z tematu.
 */
class Topic{
    public $name;
    public $description;
    public $color;

    function __construct($name, $description, $color){
        $this->name = $name;
        $this->description = $description;
        $this->color = $color;
    }
}

$topics = [
    new Topic("Výrazy", "Sčítání, odčítání, násobení, vytýkání, vzorce", "var(--warning)"),
    new Topic("Vlastnosti funkcí", "Základní vlastnosti funkcí - monotonie, obory, ...", "var(--danger)"),
    new Topic("Rovnice", "Lineární a kvadratické", "yellowgreen"),
    new Topic("Soustavy rovnic", "Soustavy lineárních rovnic", "midnightblue")
];

/**
 * @param $by_name nazev tematu.
 * @return NULL, pokud nebylo tema nalezeno, jinak informace o tematu.
 */
function get_topic($by_name){
    global $topics;
    foreach($topics as $t){
        if($t->name == $by_name){
            return $t;
        }
    }
    return NULL;
}
?>