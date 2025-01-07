<?php
/*
autor: Dominik Vladar
prace z predmetu KIV/OP
Zapadoceska univerzita v Plzni, Fakulta aplikovanych ved
*/

/**
 * Vypise hlavicku HTML dokumentu.
 * @param $title obsah <title> HTML elementu.
 * @param $primary_color primarni barva, ve ktere ma byt stranka obarvena.
 */
function write_header($title, $primary_color = NULL){
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
    <!--MathJax-->
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <!-- konec importu knihoven -->
    <link rel="stylesheet" href="css.css">
    <!-- favicon -->
<link rel="icon" type="image/x-icon" href="favicon.ico">
    <title><?php echo $title; ?></title>
  </head>
  <body>
    <div id="header" class="sticky-top" <?php if($primary_color != NULL){ echo 'style="background: '.$primary_color.';"';} ?>>
        <?php echo $title; ?>
        <a href="./" id="home">
            <i class="fa-solid fa-house"></i>
        </a>
    </div>
    <div class="container">
<?php
}
/**
 * Vypise paticku HTML stranky.
 */
function write_footer(){
    ?>
</div>
<footer class="p-3">
    &copy; Dominik Vladař
</footer>
  </body>
</html>    
    <?php
}

/**
 * Vypise obsah uvodni stranky.
 * @param $topics vsechny definovana temata pro generovani ukolu.
 */
function write_main_page_content($topics){
?>
    <div class="selection_header">
        <div class="mt-3">
            Výsledky<sup>1</sup>: 
            <span class="selectable">
                <input type="checkbox" id="integer" checked onchange="change_form_param(this.checked?1:0, 'result_form_integer')"> <label for="integer">Celočíselně</label>
            </span>
            <span class="selectable">
                <input type="checkbox" id="fraction" checked onchange="change_form_param(this.checked?1:0, 'result_form_fraction')"> <label for="fraction">Zlomky</label>
            </span>
            <span class="selectable">
                <input type="checkbox" id="random" checked onchange="change_form_param(this.checked?1:0, 'result_form_random')"> <label for="random">Náhodná čísla</label>
            </span>
        </div>

        <div class="my-2">
            Počet otázek: <input type="number" id="number_of_questions" value="10" min="1" step="1" class="ml-2" onchange="change_form_param(parseInt(this.value), 'number_of_questions')" oninput="if(this.value.includes('.')){this.value=this.value.split('.')[0];} if(parseInt(this.value) < 1){this.setAttribute('style', 'outline: 2px solid var(--danger) !important;');set_submit_disabled(true);} else if(this.hasAttribute('style')){this.removeAttribute('style');set_submit_disabled(false);}">
        </div>

        <div>
            Obtížnost<sup>1</sup>: <input type="range" id="difficulty" min="1" max="5" step="1" value="2" onchange="change_form_param(parseInt(this.value), 'difficulty')">
        </div>
        <div class="text-secondary mb-3 mt-1"><sup>1</sup> Pokud to má smysl.</div>
    </div>

    <script>
        /**
         * Zmeni hodnotu parametru ulozenou do cookies.
         * @param new_value nova hodnota parametru.
         * @param param_name nazev parametru.
         */
        function change_form_param(new_value, param_name){
            for(let elem of document.getElementsByName(param_name)){
                elem.value = new_value;
            }
            if(document.cookie != ""){
                const arr = param_name.split("_");
                set_cookie(arr.length == 1? param_name : arr[2], new_value);
            }
        }

        /**
         * Znemozni/umozni spustit generovani s timto nastavenim parametru.
         * @param disabled: true, pokud chceme zakazat spusteni, jinak false.
         */
        function set_submit_disabled(disabled){
            for(let elem of document.getElementsByClassName("my_submit")){
                elem.disabled = disabled;
            }
        }

        /**
         * Nastavi specifikovanou cookie.
         * @param name nazev cookie.
         * @param value hodnota teto cookie.
         */
        function set_cookie(name, value){
            const d = new Date();
            d.setTime(d.getTime() + 365*24*60*60*1000);//1 rok
            document.cookie = `${name}=${value}; expires=${d.toUTCString()}; path=/`;
        }

        /**
         * Smaze vsechny ulozene cookies.
         */
        function delete_all_cookies(){
            const all_cookies = ["integer", "fraction", "random", "questions", "difficulty"];
            for(let c of all_cookies){
                document.cookie = `${c}=none; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/`;
            }
        }

        /**
         * Umozni/zakaze ukladat data do cookies, vyresi zmenu tlacitka pro ukladani cookies.
         * @param btn tlacitko pro povoleni/zakazani cookies.
         */
        function cookies(btn){
            if(btn.innerText == "Povolit"){
                btn.setAttribute("class", "btn btn-outline-dark mt-3 mb-5");
                btn.innerText = "Zakázat";
                const d = new Date();
                d.setTime(d.getTime() + 365*24*60*60*1000);
                for(let element of ["integer", "fraction", "random"]){
                    document.cookie = `${element}=${document.getElementById(element).checked?1:0}; expires=${d.toUTCString()}; path=/`;
                }
                document.cookie = `questions=${document.getElementById("number_of_questions").value}; expires=${d.toUTCString()}; path=/`;
                document.cookie = `difficulty=${document.getElementById("difficulty").value}; expires=${d.toUTCString()}; path=/`;
            }
            else{
                btn.setAttribute("class", "btn btn-dark mt-3 mb-5");
                btn.innerText = "Povolit";
                delete_all_cookies();
            }
        }

        setTimeout(() =>{
            if(document.cookie != ""){
                for(let cookie_pair of document.cookie.split("; ")){
                    let [name, value] = cookie_pair.split("=");
                    switch(name){
                        case "questions":
                            document.getElementById("number_of_questions").value = parseInt(value);
                            change_form_param(document.getElementById("number_of_questions").value, "number_of_questions");
                            break;
                        case "difficulty":
                            document.getElementById("difficulty").value = parseInt(value);
                            change_form_param(document.getElementById("difficulty").value, "difficulty");
                            break;
                        default:
                            document.getElementById(name).checked = parseInt(value)==1;
                            change_form_param(document.getElementById(name).checked?1:0, `result_form_${name}`);
                            break;
                    }
                    cookies(document.getElementById("cookies_btn"));
                }
            }
        }, 50);
    </script>
    
    <?php
    foreach($topics as $topic){
    ?>
    <div class="emphasize mb-3" style="border-right: 10px solid <?php echo $topic->color ?>;">
        <div class="container">
            <div class="h1"><?php echo $topic->name ?></div>
            <p class="lead"><?php echo $topic->description ?>.</p>
            <form method="POST">
            <input type="hidden" name="result_form_integer" value="1">
            <input type="hidden" name="result_form_fraction" value="1">
            <input type="hidden" name="result_form_random" value="1">
            <input type="hidden" name="number_of_questions" value="10">
            <input type="hidden" name="difficulty" value="2">
            <input type="hidden" name="topic" value="<?php echo $topic->name ?>">
            <input type="submit" class="btn btn-primary btn-lg my_submit" value="Spustit">
            </form>
        </div>
    </div>
<?php
    }
    ?>
    <hr>
    <section>
        <div class="h1">Cookies <i class="fa-solid fa-cookie-bite"></i></div>
        <div class="w-75">
            Pokud chcete, aby Vámi nastavené parametry na stránce nezmizely do dalšího načtení, můžete <b>povolit automatické ukládání</b>. 
            Souhlasíte s tím, že za tímto účelem budou na Vašem zařízení uchovány soubory cookies.</div>
            <div class="text-center w-75">
                <button id="cookies_btn" class="btn btn-dark mt-3 mb-5" onclick="cookies(this);">Povolit</button>
        </div>
    </section>
    <?php
}

?>