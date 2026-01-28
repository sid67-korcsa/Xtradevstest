<?php
/*
 * - tervezési elv, ahol egy osztály egy másik osztály objektumát tartalmazza tagváltozóként, "has-a" (birtokol) kapcsolatot létrehozva.
 * - rugalmasabb, mint az öröklődés, mivel a funkcionalitás összetevők (komponensek) összeállításával jön létre, elősegítve a kód újrafelhasználását és a lazább csatolást
*/

class Motor {

    public function indito() {
        return "Motor elinditasa.";
    }

}

class Auto {

    private $motor;
    private $name;

    public function __construct($name) {
        // a kompozíció lényege ebben az esetben: az Auto létrehozza a Motor objektumot
        $this->motor = new Motor();
        if(mb_strlen($name) > 0) {
            $this->name = $name;
        } else {
            $this->name = "Nevtelen";
        }

    }

    public function elindul() {
        return $this->motor->indito() . $this->name ." auto elindult.\n";
    }

}

$auto = new Auto("Opel");

echo $auto->elindul(); // OUTPUT: Motor inditasa. Auto elindult.

?>
