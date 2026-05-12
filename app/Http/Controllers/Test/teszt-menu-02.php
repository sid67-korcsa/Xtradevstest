<?php

$menuItems = [
	"Fomenu01" => ["Almenu01"],
	"Fomenu02" => ["Almenu01"],
	"Fomenu03" => ["Almenu01", "Almenu02", "Almenu03"],
	"Fomenu04" => ["Almenu01", "Almenu02"]
];


function delMenus(array $items) : bool {
	//
}

function printMenus(array $items) : bool {
	if($items) {
		
		foreach($items as $menuParent => $subMenu) {
			if(mb_strlen($menuParent) != 0) {
				echo '<ul>'.$menuParent;
				
				if(sizeof($subMenu) > 0) {
					echo '<ul>';
					foreach($subMenu as $key => $value) {
						echo '<li>'.$value.'</li>';
					}
					echo '</ul>';
				}			
			}
			
			echo '</ul>';
		}
		
		return true;	
	}
	
}


if(is_array($menuItems)) {
	printMenus($menuItems);
} else {
	echo "Hibas forrasadat!!<br />";
}