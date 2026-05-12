<?php

// A menü szerkezete
$menu = [
    "Főmenü 1" => ["Almenü 1"],
    "Főmenü 2" => ["Almenü 1"],
    "Főmenü 3" => ["Almenü 1", "Almenü 2", "Almenü 3"],
];

// Menü generáló függvény
function renderMenu(array $items)
{
    echo "<ul>";

    foreach ($items as $main => $subItems) {
        echo "<li>".$main;
        //print_r($main);
		  //print_r($subItems);

        if (!empty($subItems)) {
            echo "<ul>";
            foreach ($subItems as $sub) {
                echo "<li>".$sub."</li>";
            }
            echo "</ul>";
        }

        echo "</li>";
    }

    echo "</ul>";
}

// Menü kiíratása
renderMenu($menu);

?>
